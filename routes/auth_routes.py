from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.exc import IntegrityError
from pydantic import BaseModel, Field, EmailStr
from typing import Optional
from db import get_db_session
from models.users import User
from utils.auth import (
    get_password_hash, 
    authenticate_user, 
    create_access_token,
    get_current_user
)
import logging

logger = logging.getLogger(__name__)

router = APIRouter()


class UserRegistration(BaseModel):
    """Schema for user registration"""
    name: str = Field(..., min_length=1, max_length=255)
    email: EmailStr
    password: str = Field(..., min_length=8, max_length=100)
    is_admin: Optional[bool] = False


class UserLogin(BaseModel):
    """Schema for user login"""
    email: EmailStr
    password: str


class Token(BaseModel):
    """Schema for token response"""
    access_token: str
    token_type: str
    user: dict


class UserResponse(BaseModel):
    """Schema for user response"""
    id: str
    name: str
    email: str
    is_admin: bool
    is_active: bool
    created_at: str


@router.post("/register", response_model=UserResponse, status_code=status.HTTP_201_CREATED)
async def register_user(
    user_data: UserRegistration,
    db: AsyncSession = Depends(get_db_session)
):
    """Register a new user"""
    try:
        # Hash the password
        password_hash = get_password_hash(user_data.password)
        
        # Create new user
        new_user = User(
            name=user_data.name,
            email=user_data.email,
            password_hash=password_hash,
            is_admin=user_data.is_admin or False
        )
        
        db.add(new_user)
        await db.commit()
        await db.refresh(new_user)
        
        logger.info(f"Created new user: {user_data.email}")
        return UserResponse(**new_user.to_dict())
        
    except IntegrityError as e:
        await db.rollback()
        logger.warning(f"Registration failed for {user_data.email}: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Email already registered"
        )
    except Exception as e:
        await db.rollback()
        logger.error(f"Unexpected error during registration: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.post("/login", response_model=Token)
async def login_user(
    user_credentials: UserLogin,
    db: AsyncSession = Depends(get_db_session)
):
    """Authenticate user and return access token"""
    try:
        # Authenticate user
        user = await authenticate_user(db, user_credentials.email, user_credentials.password)
        
        if not user:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Incorrect email or password",
                headers={"WWW-Authenticate": "Bearer"},
            )
        
        # Create access token
        access_token = create_access_token(data={"sub": str(user.id)})
        
        logger.info(f"User logged in: {user.email}")
        return Token(
            access_token=access_token,
            token_type="bearer",
            user=user.to_dict()
        )
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Login error: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        )


@router.get("/me", response_model=UserResponse)
async def get_current_user_info(
    current_user: User = Depends(get_current_user)
):
    """Get current user information"""
    return UserResponse(**current_user.to_dict())


@router.post("/refresh", response_model=Token)
async def refresh_token(
    current_user: User = Depends(get_current_user)
):
    """Refresh access token"""
    try:
        # Create new access token
        access_token = create_access_token(data={"sub": str(current_user.id)})
        
        return Token(
            access_token=access_token,
            token_type="bearer",
            user=current_user.to_dict()
        )
        
    except Exception as e:
        logger.error(f"Token refresh error: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Internal server error"
        ) 