from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from typing import List, Optional
from pydantic import BaseModel, Field
from db import get_db_session
from services.profile_service import ProfileService
from utils.auth import get_current_user_id
import logging

logger = logging.getLogger(__name__)

router = APIRouter()


class ProfileCreate(BaseModel):
    """Schema for creating NGO profile"""
    organization_name: str = Field(..., min_length=1, max_length=500)
    mission_statement: str = Field(..., min_length=1)
    focus_areas: List[str] = Field(default_factory=list)
    geographic_scope: List[str] = Field(default_factory=list)
    founded_year: Optional[int] = Field(None, ge=1800, le=2024)
    organization_type: Optional[str] = Field(None, max_length=100)
    registration_number: Optional[str] = Field(None, max_length=100)
    website: Optional[str] = Field(None, max_length=500)
    contact_person: Optional[str] = Field(None, max_length=255)
    contact_email: Optional[str] = Field(None, max_length=255)
    contact_phone: Optional[str] = Field(None, max_length=50)
    address: Optional[str] = None
    programs_services: List[str] = Field(default_factory=list)
    target_beneficiaries: List[str] = Field(default_factory=list)
    annual_budget_range: Optional[str] = Field(None, max_length=100)
    staff_size: Optional[str] = Field(None, max_length=100)
    past_projects: List[dict] = Field(default_factory=list)
    partnerships: List[str] = Field(default_factory=list)
    awards_recognition: List[str] = Field(default_factory=list)
    funding_sources: List[str] = Field(default_factory=list)
    grant_experience: List[dict] = Field(default_factory=list)


class ProfileUpdate(BaseModel):
    """Schema for updating NGO profile"""
    organization_name: Optional[str] = Field(None, min_length=1, max_length=500)
    mission_statement: Optional[str] = Field(None, min_length=1)
    focus_areas: Optional[List[str]] = None
    geographic_scope: Optional[List[str]] = None
    founded_year: Optional[int] = Field(None, ge=1800, le=2024)
    organization_type: Optional[str] = Field(None, max_length=100)
    registration_number: Optional[str] = Field(None, max_length=100)
    website: Optional[str] = Field(None, max_length=500)
    contact_person: Optional[str] = Field(None, max_length=255)
    contact_email: Optional[str] = Field(None, max_length=255)
    contact_phone: Optional[str] = Field(None, max_length=50)
    address: Optional[str] = None
    programs_services: Optional[List[str]] = None
    target_beneficiaries: Optional[List[str]] = None
    annual_budget_range: Optional[str] = Field(None, max_length=100)
    staff_size: Optional[str] = Field(None, max_length=100)
    past_projects: Optional[List[dict]] = None
    partnerships: Optional[List[str]] = None
    awards_recognition: Optional[List[str]] = None
    funding_sources: Optional[List[str]] = None
    grant_experience: Optional[List[dict]] = None


class ProfileResponse(BaseModel):
    """Schema for profile response"""
    id: str
    user_id: str
    organization_name: str
    mission_statement: str
    focus_areas: List[str]
    geographic_scope: List[str]
    profile_completeness_score: int
    is_verified: bool
    created_at: str
    updated_at: str


@router.post("/", response_model=ProfileResponse, status_code=status.HTTP_201_CREATED)
async def create_profile(
    profile_data: ProfileCreate,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Create a new NGO profile"""
    try:
        profile_service = ProfileService(db)
        profile = await profile_service.create_profile(
            user_id=current_user_id,
            profile_data=profile_data.dict()
        )
        return ProfileResponse(**profile.to_dict())
    except ValueError as e:
        logger.warning(f"Profile creation failed: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=str(e)
        )
    except Exception as e:
        logger.error(f"Unexpected error creating profile: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.get("/", response_model=Optional[ProfileResponse])
async def get_profile(
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Get current user's NGO profile"""
    try:
        profile_service = ProfileService(db)
        profile = await profile_service.get_profile_by_user_id(current_user_id)
        
        if not profile:
            return None
        
        return ProfileResponse(**profile.to_dict())
    except Exception as e:
        logger.error(f"Error fetching profile: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.put("/", response_model=ProfileResponse)
async def update_profile(
    profile_data: ProfileUpdate,
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Update current user's NGO profile"""
    try:
        profile_service = ProfileService(db)
        
        # Filter out None values
        update_data = {k: v for k, v in profile_data.dict().items() if v is not None}
        
        profile = await profile_service.update_profile(
            user_id=current_user_id,
            profile_data=update_data
        )
        
        if not profile:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Profile not found"
            )
        
        return ProfileResponse(**profile.to_dict())
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error updating profile: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.delete("/", status_code=status.HTTP_204_NO_CONTENT)
async def delete_profile(
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Delete current user's NGO profile"""
    try:
        profile_service = ProfileService(db)
        success = await profile_service.delete_profile(current_user_id)
        
        if not success:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Profile not found"
            )
        
        return None
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error deleting profile: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.get("/completeness")
async def get_profile_completeness(
    db: AsyncSession = Depends(get_db_session),
    current_user_id: str = Depends(get_current_user_id)
):
    """Get profile completeness analysis"""
    try:
        profile_service = ProfileService(db)
        profile = await profile_service.get_profile_by_user_id(current_user_id)
        
        if not profile:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="Profile not found"
            )
        
        return {
            "completeness_score": profile.profile_completeness_score,
            "missing_fields": _get_missing_fields(profile),
            "recommendations": _get_completion_recommendations(profile)
        }
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Error getting profile completeness: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


def _get_missing_fields(profile) -> List[str]:
    """Get list of missing profile fields"""
    missing_fields = []
    
    if not profile.founded_year:
        missing_fields.append("founded_year")
    if not profile.organization_type:
        missing_fields.append("organization_type")
    if not profile.website:
        missing_fields.append("website")
    if not profile.contact_person:
        missing_fields.append("contact_person")
    if not profile.contact_email:
        missing_fields.append("contact_email")
    if not profile.programs_services:
        missing_fields.append("programs_services")
    if not profile.target_beneficiaries:
        missing_fields.append("target_beneficiaries")
    if not profile.annual_budget_range:
        missing_fields.append("annual_budget_range")
    if not profile.staff_size:
        missing_fields.append("staff_size")
    if not profile.past_projects:
        missing_fields.append("past_projects")
    
    return missing_fields


def _get_completion_recommendations(profile) -> List[str]:
    """Get recommendations for profile completion"""
    recommendations = []
    
    if profile.profile_completeness_score < 50:
        recommendations.append("Complete basic organization information")
    if profile.profile_completeness_score < 70:
        recommendations.append("Add program details and target beneficiaries")
    if profile.profile_completeness_score < 90:
        recommendations.append("Include past projects and partnerships")
    if not profile.is_verified:
        recommendations.append("Submit profile for verification")
    
    return recommendations 