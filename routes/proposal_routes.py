from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from typing import List, Optional
from pydantic import BaseModel, Field
from db import get_db_session
from services.proposal_service import ProposalService
from utils.auth import get_current_user_id
import logging

logger = logging.getLogger(__name__)

router = APIRouter()


class ProposalGenerate(BaseModel):
    """Schema for generating proposal"""
    funding_opportunity_id: int = Field(..., description="ID of the funding opportunity")
    custom_instructions: Optional[str] = Field(None, max_length=2000, description="Custom instructions for the proposal")


class ProposalUpdate(BaseModel):
    """Schema for updating proposal"""
    title: Optional[str] = Field(None, max_length=500)
    content: Optional[str] = Field(None, min_length=1)
    executive_summary: Optional[str] = None
    status: Optional[str] = Field(None, pattern="^(draft|reviewed|finalized|submitted)$")


class ProposalRate(BaseModel):
    """Schema for rating proposal"""
    rating: int = Field(..., ge=1, le=5, description="Rating from 1 to 5 stars")
    feedback: Optional[str] = Field(None, max_length=1000, description="Optional feedback")


class ProposalResponse(BaseModel):
    """Schema for proposal response"""
    id: str
    user_id: str
    funding_opportunity_id: int
    title: str
    content: str
    executive_summary: Optional[str]
    status: str
    version: int
    confidence_score: Optional[float]
    alignment_score: Optional[float]
    completeness_score: Optional[float]
    user_rating: Optional[int]
    created_at: str
    updated_at: str


class ProposalSummary(BaseModel):
    """Schema for proposal summary (for lists)"""
    id: str
    title: str
    status: str
    confidence_score: Optional[float]
    user_rating: Optional[int]
    created_at: str
    updated_at: str


@router.post("/generate", response_model=ProposalResponse, status_code=status.HTTP_201_CREATED)
async def generate_proposal(
    generate_data: ProposalGenerate,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Generate a new proposal using AI"""
    try:
        proposal_service = ProposalService(db)
        proposal = await proposal_service.generate_proposal(
            user_id=current_user_id,
            funding_opportunity_id=generate_data.funding_opportunity_id,
            custom_instructions=generate_data.custom_instructions
        )
        return ProposalResponse(**proposal.to_dict())
    except ValueError as e:
        logger.warning(f"Proposal generation failed: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=str(e)
        )
    except Exception as e:
        logger.error(f"Unexpected error generating proposal: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.get("/", response_model=List[ProposalSummary])
async def get_proposals(
    limit: int = 50,
    offset: int = 0,
    status_filter: Optional[str] = None,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Get all proposals for current user"""
    try:
        proposal_service = ProposalService(db)
        proposals = await proposal_service.get_user_proposals(
            user_id=current_user_id,
            limit=limit,
            offset=offset,
            status=status_filter
        )
        return [ProposalSummary(**proposal.to_summary_dict()) for proposal in proposals]
    except Exception as e:
        logger.error(f"Error fetching proposals: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.get("/{proposal_id}", response_model=ProposalResponse)
async def get_proposal(
    proposal_id: str,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Get specific proposal by ID"""
    try:
        proposal_service = ProposalService(db)
        proposal = await proposal_service.get_proposal_by_id(proposal_id, current_user_id)
        
        if not proposal:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Proposal not found"
            )
        
        return ProposalResponse(**proposal.to_dict())
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error fetching proposal: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.put("/{proposal_id}", response_model=ProposalResponse)
async def update_proposal(
    proposal_id: str,
    update_data: ProposalUpdate,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Update proposal content and metadata"""
    try:
        proposal_service = ProposalService(db)
        
        # Filter out None values
        updates = {k: v for k, v in update_data.dict().items() if v is not None}
        
        proposal = await proposal_service.update_proposal(
            proposal_id=proposal_id,
            user_id=current_user_id,
            updates=updates
        )
        
        if not proposal:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Proposal not found"
            )
        
        return ProposalResponse(**proposal.to_dict())
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error updating proposal: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.post("/{proposal_id}/rate", response_model=ProposalResponse)
async def rate_proposal(
    proposal_id: str,
    rating_data: ProposalRate,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Rate a proposal"""
    try:
        proposal_service = ProposalService(db)
        proposal = await proposal_service.rate_proposal(
            proposal_id=proposal_id,
            user_id=current_user_id,
            rating=rating_data.rating,
            feedback=rating_data.feedback
        )
        
        if not proposal:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Proposal not found"
            )
        
        return ProposalResponse(**proposal.to_dict())
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error rating proposal: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.delete("/{proposal_id}/archive", status_code=status.HTTP_204_NO_CONTENT)
async def archive_proposal(
    proposal_id: str,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Archive a proposal"""
    try:
        proposal_service = ProposalService(db)
        success = await proposal_service.archive_proposal(proposal_id, current_user_id)
        
        if not success:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Proposal not found"
            )
        
        return None
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error archiving proposal: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.get("/{proposal_id}/export/{format}")
async def export_proposal(
    proposal_id: str,
    format: str,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Export proposal in specified format (PDF, DOCX)"""
    try:
        if format.lower() not in ["pdf", "docx"]:
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Unsupported format. Use 'pdf' or 'docx'"
            )
        
        proposal_service = ProposalService(db)
        proposal = await proposal_service.get_proposal_by_id(proposal_id, current_user_id)
        
        if not proposal:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Proposal not found"
            )
        
        # Import export utilities
        from utils.export_utils import generate_docx, generate_pdf
        
        if format.lower() == "docx":
            file_content, filename = generate_docx(proposal)
            media_type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        else:  # pdf
            file_content, filename = generate_pdf(proposal)
            media_type = "application/pdf"
        
        # Update export tracking
        await proposal_service.track_export(proposal_id, format.lower())
        
        # Return file response
        from fastapi.responses import Response
        return Response(
            content=file_content,
            media_type=media_type,
            headers={"Content-Disposition": f"attachment; filename={filename}"}
        )
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error exporting proposal: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        ) 