from fastapi import APIRouter, Depends, HTTPException, status, Request, Header
from sqlalchemy.ext.asyncio import AsyncSession
from typing import List, Optional
from pydantic import BaseModel, Field, validator
from db import get_db_session
from services.proposal_service import ProposalService
from services.usage_service import UsageService
from services.idempotency_service import IdempotencyService
from utils.auth import get_current_user_id, get_current_user_id_flexible
from utils.error_handlers import create_error_response
import logging
import os

logger = logging.getLogger(__name__)

router = APIRouter()


class ProposalGenerate(BaseModel):
    """Schema for generating proposal"""
    # Exactly one of these must be provided
    funding_opportunity_id: Optional[int] = Field(None, description="ID of the funding opportunity")
    custom_brief: Optional[str] = Field(None, max_length=5000, description="Custom brief for proposal generation")
    quick_fields: Optional[dict] = Field(None, description="Quick fields for rapid proposal generation")
    
    # Optional for both paths
    custom_instructions: Optional[str] = Field(None, max_length=2000, description="Custom instructions for the proposal")
    
    @validator('*', pre=True, always=True)
    def validate_exactly_one_input(cls, v, values):
        """Ensure exactly one of funding_opportunity_id or {custom_brief, quick_fields} is provided"""
        # This runs for each field, but we only check on the last field
        field_name = cls.__annotations__
        
        if len(values) >= 3:  # We have enough fields to validate
            funding_id = values.get('funding_opportunity_id')
            custom_brief = values.get('custom_brief')
            quick_fields = values.get('quick_fields')
            
            # Count non-None values
            provided_inputs = sum([
                funding_id is not None,
                custom_brief is not None,
                quick_fields is not None
            ])
            
            if provided_inputs == 0:
                raise ValueError("Must provide exactly one of: funding_opportunity_id OR (custom_brief OR quick_fields)")
            elif provided_inputs > 1:
                raise ValueError("Must provide exactly one of: funding_opportunity_id OR (custom_brief OR quick_fields). Multiple inputs provided.")
        
        return v


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
    request: Request,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id_flexible),
    idempotency_key: Optional[str] = Header(None, alias="Idempotency-Key")
):
    """Generate a new proposal using AI with idempotency and rate limiting"""
    try:
        logger.info(f"🚀 Generating proposal for user: {current_user_id}")
        
        # Rate limiting check
        usage_service = UsageService(db)
        rate_limit = int(os.getenv("RATE_LIMIT_GENERATE_PER_MINUTE", "5"))
        
        if not await usage_service.check_rate_limit(current_user_id, "generate", rate_limit):
            logger.warning(f"Rate limit exceeded for user {current_user_id}")
            return create_error_response(
                code="RATE_LIMIT_EXCEEDED",
                message=f"Rate limit exceeded. Maximum {rate_limit} requests per minute for proposal generation.",
                status_code=429,
                details={"limit": rate_limit, "action": "generate"}
            )
        
        # Idempotency check
        if idempotency_key:
            idempotency_service = IdempotencyService(db)
            request_data = generate_data.dict()
            
            cached_response = await idempotency_service.check_idempotency(
                user_id=current_user_id,
                idempotency_key=idempotency_key,
                endpoint="generate_proposal",
                request_data=request_data
            )
            
            if cached_response:
                response_data, status_code = cached_response
                logger.info(f"Returning cached proposal for idempotency key: {idempotency_key}")
                return ProposalResponse(**response_data)
        
        # Validate input using the Pydantic model validation
        # This will automatically trigger our validator and raise ValidationError if invalid
        
        logger.info(f"📋 Request data: {generate_data.dict()}")
        
        proposal_service = ProposalService(db)
        
        # Generate proposal based on input type
        if generate_data.funding_opportunity_id:
            proposal = await proposal_service.generate_proposal(
                user_id=current_user_id,
                funding_opportunity_id=generate_data.funding_opportunity_id,
                custom_instructions=generate_data.custom_instructions
            )
        else:
            # Handle custom brief or quick fields
            proposal = await proposal_service.generate_custom_proposal(
                user_id=current_user_id,
                custom_brief=generate_data.custom_brief,
                quick_fields=generate_data.quick_fields,
                custom_instructions=generate_data.custom_instructions
            )
        
        # Record usage
        await usage_service.record_usage(current_user_id, "generate")
        
        # Prepare response
        response_data = proposal.to_dict()
        
        # Store idempotency record if key provided
        if idempotency_key:
            await idempotency_service.store_response(
                user_id=current_user_id,
                idempotency_key=idempotency_key,
                endpoint="generate_proposal",
                response_data=response_data,
                status_code=201,
                request_data=generate_data.dict()
            )
        
        logger.info(f"✅ Proposal generated successfully for user: {current_user_id}")
        return ProposalResponse(**response_data)
        
    except ValueError as e:
        logger.warning(f"Proposal generation validation failed: {str(e)}")
        return create_error_response(
            code="VALIDATION_ERROR",
            message=str(e),
            status_code=422,
            details={"input_validation": "Must provide exactly one of: funding_opportunity_id OR (custom_brief OR quick_fields)"}
        )
    except Exception as e:
        logger.error(f"Unexpected error generating proposal: {str(e)}")
        return create_error_response(
            code="INTERNAL_ERROR",
            message="An unexpected error occurred during proposal generation",
            status_code=500
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
    """Export proposal in specified format (PDF, DOCX) with rate limiting"""
    try:
        # Rate limiting check
        usage_service = UsageService(db)
        rate_limit = int(os.getenv("RATE_LIMIT_EXPORT_PER_MINUTE", "10"))
        
        if not await usage_service.check_rate_limit(current_user_id, "export", rate_limit):
            logger.warning(f"Export rate limit exceeded for user {current_user_id}")
            return create_error_response(
                code="RATE_LIMIT_EXCEEDED",
                message=f"Rate limit exceeded. Maximum {rate_limit} exports per minute.",
                status_code=429,
                details={"limit": rate_limit, "action": "export"}
            )
        
        if format.lower() not in ["pdf", "docx"]:
            return create_error_response(
                code="INVALID_FORMAT",
                message="Unsupported format. Use 'pdf' or 'docx'",
                status_code=400,
                details={"supported_formats": ["pdf", "docx"]}
            )
        
        proposal_service = ProposalService(db)
        proposal = await proposal_service.get_proposal_by_id(proposal_id, current_user_id)
        
        if not proposal:
            return create_error_response(
                code="PROPOSAL_NOT_FOUND",
                message="Proposal not found",
                status_code=404
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
        
        # Record usage for rate limiting
        await usage_service.record_usage(current_user_id, "export")
        
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