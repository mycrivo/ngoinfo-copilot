from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from pydantic import BaseModel
from db import get_db_session
from services.usage_service import UsageService
from utils.auth import get_current_user_id
import logging

logger = logging.getLogger(__name__)

router = APIRouter()


class UsageSummaryResponse(BaseModel):
    """Schema for usage summary response"""
    plan: str
    monthly_limit: int
    used: int
    remaining: int
    reset_at: str


@router.get("/summary", response_model=UsageSummaryResponse)
async def get_usage_summary(
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Get current month usage summary for authenticated user"""
    try:
        usage_service = UsageService(db)
        summary = await usage_service.get_usage_summary(current_user_id)
        
        return UsageSummaryResponse(**summary)
        
    except Exception as e:
        logger.error(f"Error getting usage summary for user {current_user_id}: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )
