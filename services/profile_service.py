from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select, update, delete
from sqlalchemy.exc import IntegrityError
from typing import Optional, List, Dict, Any
from models.ngo_profiles import NGOProfile
from utils.scoring import calculate_profile_completeness
import logging

logger = logging.getLogger(__name__)


class ProfileService:
    """Service for managing NGO profiles"""

    def __init__(self, db_session: AsyncSession):
        self.db_session = db_session

    async def create_profile(
        self, user_id: str, profile_data: Dict[str, Any]
    ) -> NGOProfile:
        """Create a new NGO profile"""
        try:
            # Calculate profile completeness score
            completeness_score = calculate_profile_completeness(profile_data)

            profile = NGOProfile(
                user_id=user_id,
                organization_name=profile_data.get("organization_name", ""),
                mission_statement=profile_data.get("mission_statement", ""),
                focus_areas=profile_data.get("focus_areas", []),
                geographic_scope=profile_data.get("geographic_scope", []),
                founded_year=profile_data.get("founded_year"),
                organization_type=profile_data.get("organization_type"),
                registration_number=profile_data.get("registration_number"),
                website=profile_data.get("website"),
                contact_person=profile_data.get("contact_person"),
                contact_email=profile_data.get("contact_email"),
                contact_phone=profile_data.get("contact_phone"),
                address=profile_data.get("address"),
                programs_services=profile_data.get("programs_services", []),
                target_beneficiaries=profile_data.get("target_beneficiaries", []),
                annual_budget_range=profile_data.get("annual_budget_range"),
                staff_size=profile_data.get("staff_size"),
                past_projects=profile_data.get("past_projects", []),
                partnerships=profile_data.get("partnerships", []),
                awards_recognition=profile_data.get("awards_recognition", []),
                funding_sources=profile_data.get("funding_sources", []),
                grant_experience=profile_data.get("grant_experience", []),
                profile_completeness_score=completeness_score,
                ai_optimization_notes=profile_data.get("ai_optimization_notes"),
            )

            self.db_session.add(profile)
            await self.db_session.commit()
            await self.db_session.refresh(profile)

            logger.info(f"Created profile for user {user_id}")
            return profile

        except IntegrityError as e:
            await self.db_session.rollback()
            logger.error(f"Profile creation failed for user {user_id}: {str(e)}")
            raise ValueError(f"Profile already exists for user {user_id}")
        except Exception as e:
            await self.db_session.rollback()
            logger.error(
                f"Unexpected error creating profile for user {user_id}: {str(e)}"
            )
            raise

    async def get_profile_by_user_id(self, user_id: str) -> Optional[NGOProfile]:
        """Get NGO profile by user ID"""
        try:
            result = await self.db_session.execute(
                select(NGOProfile).where(
                    NGOProfile.user_id == user_id, NGOProfile.is_active == True
                )
            )
            profile = result.scalar_one_or_none()
            return profile
        except Exception as e:
            logger.error(f"Error fetching profile for user {user_id}: {str(e)}")
            raise

    async def update_profile(
        self, user_id: str, profile_data: Dict[str, Any]
    ) -> Optional[NGOProfile]:
        """Update existing NGO profile"""
        try:
            profile = await self.get_profile_by_user_id(user_id)
            if not profile:
                return None

            # Update fields if provided
            for field, value in profile_data.items():
                if hasattr(profile, field):
                    setattr(profile, field, value)

            # Recalculate completeness score
            profile.profile_completeness_score = calculate_profile_completeness(
                profile_data
            )

            await self.db_session.commit()
            await self.db_session.refresh(profile)

            logger.info(f"Updated profile for user {user_id}")
            return profile

        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error updating profile for user {user_id}: {str(e)}")
            raise

    async def delete_profile(self, user_id: str) -> bool:
        """Soft delete NGO profile"""
        try:
            result = await self.db_session.execute(
                update(NGOProfile)
                .where(NGOProfile.user_id == user_id)
                .values(is_active=False)
            )

            if result.rowcount == 0:
                return False

            await self.db_session.commit()
            logger.info(f"Deleted profile for user {user_id}")
            return True

        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error deleting profile for user {user_id}: {str(e)}")
            raise

    async def get_all_profiles(
        self, limit: int = 100, offset: int = 0
    ) -> List[NGOProfile]:
        """Get all active NGO profiles (admin function)"""
        try:
            result = await self.db_session.execute(
                select(NGOProfile)
                .where(NGOProfile.is_active == True)
                .limit(limit)
                .offset(offset)
                .order_by(NGOProfile.created_at.desc())
            )
            profiles = result.scalars().all()
            return list(profiles)
        except Exception as e:
            logger.error(f"Error fetching all profiles: {str(e)}")
            raise

    async def verify_profile(self, user_id: str) -> bool:
        """Mark profile as verified"""
        try:
            result = await self.db_session.execute(
                update(NGOProfile)
                .where(NGOProfile.user_id == user_id)
                .values(is_verified=True)
            )

            if result.rowcount == 0:
                return False

            await self.db_session.commit()
            logger.info(f"Verified profile for user {user_id}")
            return True

        except Exception as e:
            await self.db_session.rollback()
            logger.error(f"Error verifying profile for user {user_id}: {str(e)}")
            raise
