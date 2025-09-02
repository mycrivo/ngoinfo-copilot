from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from typing import List, Optional
from pydantic import BaseModel, Field
from db import get_db_session
from services.ngo_profile_manager import NGOProfileManager
from utils.auth import get_current_user_id
import logging

logger = logging.getLogger(__name__)

router = APIRouter()


class ProfileCreateRequest(BaseModel):
    """Schema for creating/updating NGO profile"""

    org_name: str = Field(
        ..., min_length=1, max_length=500, description="Organization name"
    )
    mission: str = Field(..., min_length=1, description="Mission statement")
    sectors: List[str] = Field(..., description="Focus sectors/areas")
    countries: List[str] = Field(..., description="Countries of operation")
    past_projects: str = Field(..., description="Description of past projects")
    staffing: str = Field(..., min_length=1, description="Staffing information")


class ProfileResponse(BaseModel):
    """Schema for profile response"""

    user_id: int
    org_name: str
    mission: str
    sectors: List[str]
    countries: List[str]
    past_projects: str
    staffing: str
    copilot_confidence_score: int
    profile_ready: bool


class ProfileCreateResponse(BaseModel):
    """Schema for profile creation response"""

    success: bool
    copilot_confidence_score: int


async def get_profile_manager(
    db: AsyncSession = Depends(get_db_session),
) -> NGOProfileManager:
    """Dependency to get NGOProfileManager instance"""
    return NGOProfileManager(db)


@router.get("/", response_model=Optional[ProfileResponse])
async def get_profile(
    profile_manager: NGOProfileManager = Depends(get_profile_manager),
    current_user_id: str = Depends(get_current_user_id),
):
    """
    Get NGO profile for current user.

    Returns profile data with confidence score and readiness status.
    """
    try:
        # Convert user_id from string to int for the manager
        user_id = (
            int(current_user_id.replace("user_", ""))
            if current_user_id.startswith("user_")
            else int(current_user_id)
        )

        # Get profile data
        profile_data = await profile_manager.get_profile(user_id)

        if not profile_data:
            return None

        # Get confidence score
        confidence_score = await profile_manager.score_profile(user_id)

        # Determine if profile is ready (score >= 60)
        profile_ready = confidence_score >= 60

        response = ProfileResponse(
            user_id=profile_data["user_id"],
            org_name=profile_data["org_name"],
            mission=profile_data["mission"],
            sectors=profile_data["sectors"],
            countries=profile_data["countries"],
            past_projects=profile_data["past_projects"],
            staffing=profile_data["staffing"],
            copilot_confidence_score=confidence_score,
            profile_ready=profile_ready,
        )

        logger.info(
            f"Retrieved profile for user {user_id} with score {confidence_score}"
        )
        return response

    except ValueError as e:
        logger.warning(f"Invalid user ID format: {current_user_id}")
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Invalid user ID format"
        )
    except Exception as e:
        logger.error(f"Error retrieving profile: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error",
        )


@router.post("/", response_model=ProfileCreateResponse)
async def create_or_update_profile(
    profile_data: ProfileCreateRequest,
    profile_manager: NGOProfileManager = Depends(get_profile_manager),
    current_user_id: str = Depends(get_current_user_id),
):
    """
    Create or update NGO profile for current user.

    Accepts profile data and returns success status with confidence score.
    """
    try:
        # Convert user_id from string to int for the manager
        user_id = (
            int(current_user_id.replace("user_", ""))
            if current_user_id.startswith("user_")
            else int(current_user_id)
        )

        # Convert Pydantic model to dict for the manager
        data = {
            "org_name": profile_data.org_name,
            "mission": profile_data.mission,
            "sectors": profile_data.sectors,
            "countries": profile_data.countries,
            "past_projects": profile_data.past_projects,
            "staffing": profile_data.staffing,
        }

        # Create or update profile
        success = await profile_manager.create_or_update_profile(user_id, data)

        if not success:
            raise HTTPException(
                status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
                detail="Failed to create or update profile",
            )

        # Get updated confidence score
        confidence_score = await profile_manager.score_profile(user_id)

        logger.info(
            f"Created/updated profile for user {user_id} with score {confidence_score}"
        )

        return ProfileCreateResponse(
            success=True, copilot_confidence_score=confidence_score
        )

    except ValueError as e:
        logger.warning(f"Invalid user ID format: {current_user_id}")
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Invalid user ID format"
        )
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error creating/updating profile: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error",
        )
