from fastapi import HTTPException, status, Depends, Request
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from typing import Optional, Dict, Any
from datetime import datetime, timedelta
from jose import jwt, JWTError
from passlib.context import CryptContext
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy import select
from db import get_db_session
from models.users import User
import os
import logging

logger = logging.getLogger(__name__)

# Optional security for Bearer tokens
security = HTTPBearer(auto_error=False)

# Password hashing
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

# JWT settings
SECRET_KEY = os.getenv("JWT_SECRET_KEY", "your-super-secret-jwt-key-here-change-in-production")
ALGORITHM = os.getenv("JWT_ALGORITHM", "HS256")
ACCESS_TOKEN_EXPIRE_MINUTES = int(os.getenv("JWT_ACCESS_TOKEN_EXPIRE_MINUTES", "1440"))

# Environment check - default to development unless explicitly production
ENVIRONMENT = os.getenv("ENVIRONMENT", "development").lower()
IS_DEVELOPMENT = ENVIRONMENT != "production"


def verify_password(plain_password: str, hashed_password: str) -> bool:
    """Verify a password against its hash"""
    return pwd_context.verify(plain_password, hashed_password)


def get_password_hash(password: str) -> str:
    """Hash a password"""
    return pwd_context.hash(password)


def create_access_token(data: Dict[str, Any], expires_delta: Optional[timedelta] = None) -> str:
    """Create a JWT access token"""
    to_encode = data.copy()
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt


def verify_token(token: str) -> Optional[Dict[str, Any]]:
    """Verify and decode a JWT token"""
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        return payload
    except JWTError:
        return None


async def get_user_by_email(db: AsyncSession, email: str) -> Optional[User]:
    """Get user by email"""
    result = await db.execute(
        select(User).where(User.email == email, User.is_active == True)
    )
    return result.scalar_one_or_none()


async def get_user_by_id(db: AsyncSession, user_id: str) -> Optional[User]:
    """Get user by ID"""
    result = await db.execute(
        select(User).where(User.id == user_id, User.is_active == True)
    )
    return result.scalar_one_or_none()


async def authenticate_user(db: AsyncSession, email: str, password: str) -> Optional[User]:
    """Authenticate user with email and password"""
    user = await get_user_by_email(db, email)
    if not user:
        return None
    if not verify_password(password, str(user.password_hash)):
        return None
    return user


def debug_auth_headers(request: Request) -> None:
    """Debug authentication headers and cookies (development only)"""
    if not IS_DEVELOPMENT:
        return
    
    logger.info("🔍 AUTH DEBUG - Headers received:")
    logger.info(f"  Environment: {ENVIRONMENT}")
    logger.info(f"  IS_DEVELOPMENT: {IS_DEVELOPMENT}")
    logger.info(f"  Request Path: {request.url.path}")
    
    auth_header = request.headers.get("authorization")
    if auth_header:
        logger.info(f"  Authorization: {auth_header[:20]}...")
    else:
        logger.info("  Authorization: None")
    
    cookies = request.cookies
    if cookies:
        logger.info(f"  Cookies: {list(cookies.keys())}")
        for key, value in cookies.items():
            if 'session' in key.lower() or 'token' in key.lower():
                logger.info(f"    {key}: {value[:20]}...")
    else:
        logger.info("  Cookies: None")


async def get_current_user_id_flexible(
    request: Request,
    credentials: Optional[HTTPAuthorizationCredentials] = Depends(security),
    db: AsyncSession = Depends(get_db_session)
) -> str:
    """
    Flexible authentication that supports both Bearer tokens and session cookies
    """
    # Debug logging in development
    debug_auth_headers(request)
    
    # Development mode debug logging
    if IS_DEVELOPMENT:
        logger.info(f"🔧 DEV MODE: Environment={ENVIRONMENT}, Path={request.url.path}")
    
    user_id = None
    
    # Try Bearer token first
    if credentials and credentials.credentials:
        try:
            token = credentials.credentials
            payload = verify_token(token)
            if payload:
                user_id = payload.get("sub")
                if user_id:
                    logger.info(f"✅ Authenticated via Bearer token: user {user_id}")
                    return user_id
        except Exception as e:
            logger.warning(f"Bearer token validation failed: {str(e)}")
    
    # Try session cookie authentication
    session_token = request.cookies.get("session_token") or request.cookies.get("access_token")
    if session_token:
        try:
            payload = verify_token(session_token)
            if payload:
                user_id = payload.get("sub")
                if user_id:
                    logger.info(f"✅ Authenticated via session cookie: user {user_id}")
                    return user_id
        except Exception as e:
            logger.warning(f"Session cookie validation failed: {str(e)}")
    
    # Development mode fallback for admin UI and API testing
    if IS_DEVELOPMENT:
        # Allow fallback for admin UI and proposal generation API
        admin_paths = ["/admin", "/api/proposals/generate"]
        current_path = request.url.path
        
        if any(current_path.startswith(path) for path in admin_paths):
            dev_user_id = "admin_user_dev"
            logger.warning(f"🔧 DEV MODE: Using fallback user '{dev_user_id}' for path '{current_path}'")
            logger.warning(f"🔧 DEV MODE: Environment={ENVIRONMENT}, IS_DEVELOPMENT={IS_DEVELOPMENT}")
            return dev_user_id
    
    # No valid authentication found
    logger.error(f"❌ No valid authentication found for path: {request.url.path}")
    logger.error(f"❌ Environment: {ENVIRONMENT}, IS_DEVELOPMENT: {IS_DEVELOPMENT}")
    raise HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Not authenticated",
        headers={"WWW-Authenticate": "Bearer"},
    )


async def get_current_user_id(
    credentials: HTTPAuthorizationCredentials = Depends(security)
) -> str:
    """
    Extract and validate user ID from JWT token (strict authentication)
    """
    try:
        if not credentials or not credentials.credentials:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Invalid authentication credentials",
                headers={"WWW-Authenticate": "Bearer"},
            )
        
        token = credentials.credentials
        
        # Verify JWT token
        payload = verify_token(token)
        if payload is None:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Invalid authentication credentials",
                headers={"WWW-Authenticate": "Bearer"},
            )
        
        user_id = payload.get("sub")
        if user_id is None:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Invalid authentication credentials",
                headers={"WWW-Authenticate": "Bearer"},
            )
        
        return user_id
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Authentication error: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid authentication credentials",
            headers={"WWW-Authenticate": "Bearer"},
        )


async def get_current_user(
    credentials: HTTPAuthorizationCredentials = Depends(security),
    db: AsyncSession = Depends(get_db_session)
) -> User:
    """
    Get current user from JWT token
    """
    user_id = await get_current_user_id(credentials)
    user = await get_user_by_id(db, user_id)
    
    if user is None:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="User not found",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    return user


async def get_optional_user_id(
    request: Request,
    credentials: Optional[HTTPAuthorizationCredentials] = Depends(security)
) -> Optional[str]:
    """
    Extract user ID from authentication token or session (optional)
    Returns None if no valid token is provided
    """
    debug_auth_headers(request)
    
    # Try Bearer token
    if credentials and credentials.credentials:
        try:
            token = credentials.credentials
            payload = verify_token(token)
            if payload:
                user_id = payload.get("sub")
                if user_id:
                    return user_id
        except Exception:
            pass
    
    # Try session cookie
    session_token = request.cookies.get("session_token") or request.cookies.get("access_token")
    if session_token:
        try:
            payload = verify_token(session_token)
            if payload:
                user_id = payload.get("sub")
                if user_id:
                    return user_id
        except Exception:
            pass
    
    return None


# TODO: Add additional auth utilities as needed:
# - Role-based access control
# - Permission checking
# - Token refresh
# - User session management 