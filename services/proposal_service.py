from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, update, delete, and_
from typing import Optional, List, Dict, Any
from models.proposals import Proposal
from models.ngo_profiles import NGOProfile
from models.funding_opportunities import FundingOpportunity
from utils.openai_client import OpenAIClient
from utils.scoring import calculate_proposal_scores
from prompts.prompt_builder import PromptBuilder
import logging
import json

logger = logging.getLogger(__name__)


class ProposalService:
    """Service for generating and managing proposals"""
    
    def __init__(self, db_session: AsyncSession):
        self.db_session = db_session
        self.openai_client = OpenAIClient()
        self.prompt_builder = PromptBuilder()
    
    async def generate_proposal(
        self,
        user_id: str,
        funding_opportunity_id: int,
        custom_instructions: Optional[str] = None
    ) -> Proposal:
        """Generate a new proposal using AI"""
        try:
            # Get NGO profile
            profile = await self._get_user_profile(user_id)
            if not profile:
                raise ValueError(f"No profile found for user {user_id}")
            
            # Get funding opportunity
            funding_opportunity = await self._get_funding_opportunity(funding_opportunity_id)
            if not funding_opportunity:
                raise ValueError(f"No funding opportunity found with ID {funding_opportunity_id}")
            
            # Build prompt
            prompt = self.prompt_builder.build_proposal_prompt(
                profile=profile,
                funding_opportunity=funding_opportunity,
                custom_instructions=custom_instructions
            )
            
            # Get donor-specific template
            donor_template = self.prompt_builder.get_donor_template(
                funding_opportunity.donor_organization
            )
            
            # Generate proposal using OpenAI
            ai_response = await self.openai_client.generate_proposal(prompt)
            
            # Calculate quality scores
            scores = calculate_proposal_scores(
                proposal_content=ai_response["content"],
                funding_opportunity=funding_opportunity,
                ngo_profile=profile
            )
            
            # Create proposal record
            proposal = Proposal(
                user_id=user_id,
                ngo_profile_id=profile.id,
                funding_opportunity_id=funding_opportunity_id,
                title=ai_response.get("title", f"Proposal for {funding_opportunity.title}"),
                content=ai_response["content"],
                executive_summary=ai_response.get("executive_summary"),
                generation_prompt=prompt,
                donor_template_used=donor_template,
                ai_model_used=ai_response.get("model", "gpt-4"),
                confidence_score=scores.get("confidence_score"),
                alignment_score=scores.get("alignment_score"),
                completeness_score=scores.get("completeness_score"),
                funding_opportunity_snapshot=funding_opportunity.to_dict()
            )
            
            self.db_session.add(proposal)
            await self.db_session.commit()
            await self.db_session.refresh(proposal)
            
            logger.info(f"Generated proposal for user {user_id}, funding opportunity {funding_opportunity_id}")
            return proposal
            
        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error generating proposal for user {user_id}: {str(e)}")
            raise
    
    async def get_proposal_by_id(self, proposal_id: str, user_id: str) -> Optional[Proposal]:
        """Get proposal by ID (with user access check)"""
        try:
            result = await self.db_session.execute(
                select(Proposal).where(
                    and_(
                        Proposal.id == proposal_id,
                        Proposal.user_id == user_id,
                        Proposal.is_active == True
                    )
                )
            )
            proposal = result.scalar_one_or_none()
            return proposal
        except Exception as e:
            logger.error(f"Error fetching proposal {proposal_id} for user {user_id}: {str(e)}")
            raise
    
    async def get_user_proposals(
        self,
        user_id: str,
        limit: int = 50,
        offset: int = 0,
        status: Optional[str] = None
    ) -> List[Proposal]:
        """Get all proposals for a user"""
        try:
            query = select(Proposal).where(
                and_(
                    Proposal.user_id == user_id,
                    Proposal.is_active == True
                )
            )
            
            if status:
                query = query.where(Proposal.status == status)
            
            query = query.limit(limit).offset(offset).order_by(Proposal.created_at.desc())
            
            result = await self.db_session.execute(query)
            proposals = result.scalars().all()
            return list(proposals)
        except Exception as e:
            logger.error(f"Error fetching proposals for user {user_id}: {str(e)}")
            raise
    
    async def update_proposal(
        self,
        proposal_id: str,
        user_id: str,
        updates: Dict[str, Any]
    ) -> Optional[Proposal]:
        """Update proposal content and metadata"""
        try:
            proposal = await self.get_proposal_by_id(proposal_id, user_id)
            if not proposal:
                return None
            
            # Track edit history
            edit_history = proposal.edit_history or []
            edit_record = {
                "timestamp": str(proposal.updated_at),
                "changes": updates,
                "version": proposal.version
            }
            edit_history.append(edit_record)
            
            # Update fields
            for field, value in updates.items():
                if hasattr(proposal, field):
                    setattr(proposal, field, value)
            
            # Increment version if content changed
            if "content" in updates:
                proposal.version += 1
            
            proposal.edit_history = edit_history
            
            await self.db_session.commit()
            await self.db_session.refresh(proposal)
            
            logger.info(f"Updated proposal {proposal_id} for user {user_id}")
            return proposal
            
        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error updating proposal {proposal_id} for user {user_id}: {str(e)}")
            raise
    
    async def rate_proposal(
        self,
        proposal_id: str,
        user_id: str,
        rating: int,
        feedback: Optional[str] = None
    ) -> Optional[Proposal]:
        """Rate a proposal (1-5 stars)"""
        try:
            if rating < 1 or rating > 5:
                raise ValueError("Rating must be between 1 and 5")
            
            proposal = await self.get_proposal_by_id(proposal_id, user_id)
            if not proposal:
                return None
            
            proposal.user_rating = rating
            if feedback:
                proposal.user_feedback = feedback
            
            await self.db_session.commit()
            await self.db_session.refresh(proposal)
            
            logger.info(f"Rated proposal {proposal_id} with {rating} stars")
            return proposal
            
        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error rating proposal {proposal_id}: {str(e)}")
            raise
    
    async def archive_proposal(self, proposal_id: str, user_id: str) -> bool:
        """Archive a proposal"""
        try:
            result = await self.db_session.execute(
                update(Proposal)
                .where(
                    and_(
                        Proposal.id == proposal_id,
                        Proposal.user_id == user_id
                    )
                )
                .values(is_archived=True)
            )
            
            if result.rowcount == 0:
                return False
            
            await self.db_session.commit()
            logger.info(f"Archived proposal {proposal_id}")
            return True
            
        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error archiving proposal {proposal_id}: {str(e)}")
            raise
    
    async def _get_user_profile(self, user_id: str) -> Optional[NGOProfile]:
        """Get user's NGO profile"""
        result = await self.db_session.execute(
            select(NGOProfile).where(
                and_(
                    NGOProfile.user_id == user_id,
                    NGOProfile.is_active == True
                )
            )
        )
        return result.scalar_one_or_none()
    
    async def _get_funding_opportunity(self, funding_opportunity_id: int) -> Optional[FundingOpportunity]:
        """Get funding opportunity by ID"""
        result = await self.db_session.execute(
            select(FundingOpportunity).where(
                and_(
                    FundingOpportunity.id == funding_opportunity_id,
                    FundingOpportunity.is_active == True
                )
            )
        )
        return result.scalar_one_or_none()
    
    async def track_export(self, proposal_id: str, format: str) -> bool:
        """Track proposal export"""
        try:
            from datetime import datetime
            result = await self.db_session.execute(
                select(Proposal).where(Proposal.id == proposal_id)
            )
            proposal = result.scalar_one_or_none()
            
            if proposal:
                # Update export tracking
                exported_formats = proposal.exported_formats or []
                if format not in exported_formats:
                    exported_formats.append(format)
                
                proposal.exported_formats = exported_formats
                proposal.export_count += 1
                proposal.last_exported_at = datetime.utcnow()
                
                await self.db_session.commit()
                logger.info(f"Tracked export of proposal {proposal_id} to {format}")
                return True
            
            return False
            
        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error tracking export for proposal {proposal_id}: {str(e)}")
            return False 