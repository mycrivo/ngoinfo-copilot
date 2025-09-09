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

# -------------------------------------------------------------------
# Environment and Secret Management
# -------------------------------------------------------------------
def _validate_jwt_secret() -> str:
    """Validate and return JWT secret key from environment"""
    secret_key = os.getenv("JWT_SECRET")  # ✅ use Railway's variable
    environment = os.getenv("ENV", "development").lower()

    if not secret_key:
        if environment in ("development", "dev", "local", "test"):
            secret_key = "dev-placeholder-secret"
            logger.warning("⚠️ Using default JWT secret in development")
        else:
            raise RuntimeError(
                "JWT_SECRET is not set in Railway environment variables. "
                "Add it before deploying to production."
            )

    placeholder_values = [
        "dev-placeholder-secret",
        "your-secret-key",
        "secret",
        "changeme",
        "default"
    ]
    if environment == "production" and secret_key.lower() in placeholder_values:
        raise RuntimeError(
            "JWT_SECRET is set to a weak/placeholder value in production. "
            "Use a strong 32+ character random secret."
        )

    return secret_key


SECRET_KEY = _validate_jwt_secret()
ALGORITHM = os.getenv("JWT_ALGORITHM", "HS256")
ACCESS_TOKEN_EXPIRE_MINUTES = int(os.getenv("JWT_ACCESS_TOKEN_EXPIRE_MINUTES", "1440"))

ENVIRONMENT = os.getenv("ENV", "development").lower()
IS_PRODUCTION = ENVIRONMENT == "production"
IS_DEVELOPMENT = not IS_PRODUCTION

# -------------------------------------------------------------------
# Password Utilities
# -------------------------------------------------------------------
def verify_password(plain_password: str, hashed_password: Optional[str]) -> bool:
    """Verify a password against its hash"""
    if not hashed_password:
        return False
    return pwd_context.verify(plain_password, hashed_password)


def get_password_hash(password: str) -> str:
    """Hash a password"""
    return pwd_context.hash(password)

# -------------------------------------------------------------------
# JWT Utilities
# -------------------------------------------------------------------
def create_access_token(data: Dict[str, Any], expires_delta: Optional[timedelta] = None) -> str:
    """Create a JWT access token with enforced `sub` claim"""
    if "sub" not in data:
        raise ValueError("JWT payload must include a 'sub' claim (user_id)")

    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta or timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES))
    to_encode.update({"exp": expire})

    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt


def verify_token(token: str) -> Optional[Dict[str, Any]]:
    """Verify and decode a JWT token"""
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        return payload
    except JWTError as e:
        logger.warning(f"JWT verification failed: {e}")
        return None

# -------------------------------------------------------------------
# User Retrieval
# -------------------------------------------------------------------
async def get_user_by_email(db: AsyncSession, email: str) -> Optional[User]:
    result = await db.execute(
        select(User).where(User.email == email, User.is_active == True)
    )
    return result.scalar_one_or_none()


async def get_user_by_id(db: AsyncSession, user_id: str) -> Optional[User]:
    result = await db.execute(
        select(User).where(User.id == user_id, User.is_active == True)
    )
    return result.scalar_one_or_none()


async def authenticate_user(db: AsyncSession, email: str, password: str) -> Optional[User]:
    user = await get_user_by_email(db, email)
    if not user or not verify_password(password, getattr(user, "password_hash", None)):
        return None
    return user

# -------------------------------------------------------------------
# Debug Utilities
# -------------------------------------------------------------------
def debug_auth_headers(request: Request) -> None:
    """Log authentication headers (development only)"""
    if not IS_DEVELOPMENT:
        return

    logger.info("🔍 AUTH DEBUG")
    logger.info(f"  Path: {request.url.path}")
    logger.info(f"  Environment: {ENVIRONMENT}")

    auth_header = request.headers.get("authorization")
    if auth_header:
        logger.info(f"  Authorization: {auth_header[:20]}...")
    else:
        logger.info("  Authorization: None")

    cookies = request.cookies
    if cookies:
        logger.info(f"  Cookies present: {list(cookies.keys())}")
    else:
        logger.info("  Cookies: None")

# -------------------------------------------------------------------
# Authentication Dependencies
# -------------------------------------------------------------------
async def get_current_user_id_flexible(
    request: Request,
    credentials: Optional[HTTPAuthorizationCredentials] = Depends(security),
    db: AsyncSession = Depends(get_db_session)
) -> str:
    """
    Flexible authentication:
    - Tries Bearer token
    - Falls back to session cookies
    - In dev mode, allows fallback user for admin/testing
    """
    debug_auth_headers(request)
    user_id = None

    # Bearer token
    if credentials and credentials.credentials:
        payload = verify_token(credentials.credentials)
        if payload and payload.get("sub"):
            user_id = payload["sub"]
            logger.info(f"✅ Authenticated via Bearer token: {user_id}")
            return user_id

    # Session cookie
    session_token = request.cookies.get("session_token") or request.cookies.get("access_token")
    if session_token:
        payload = verify_token(session_token)
        if payload and payload.get("sub"):
            user_id = payload["sub"]
            logger.info(f"✅ Authenticated via session cookie: {user_id}")
            return user_id

    # Dev fallback
    if IS_DEVELOPMENT:
        admin_paths = ["/admin", "/api/proposals/generate"]
        if any(request.url.path.startswith(path) for path in admin_paths):
            dev_user_id = "admin_user_dev"
            logger.warning(f"🔧 DEV MODE: Using fallback user '{dev_user_id}'")
            return dev_user_id

    logger.error(f"❌ Authentication failed for path {request.url.path}")
    raise HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Not authenticated",
        headers={"WWW-Authenticate": "Bearer"},
    )


async def get_current_user_id(
    credentials: HTTPAuthorizationCredentials = Depends(security)
) -> str:
    """Strict authentication from Bearer JWT only"""
    if not credentials or not credentials.credentials:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Not authenticated",
            headers={"WWW-Authenticate": "Bearer"},
        )

    payload = verify_token(credentials.credentials)
    if not payload or not payload.get("sub"):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Not authenticated",
            headers={"WWW-Authenticate": "Bearer"},
        )

    return payload["sub"]


async def get_current_user(
    credentials: HTTPAuthorizationCredentials = Depends(security),
    db: AsyncSession = Depends(get_db_session)
) -> User:
    """Return the current User object"""
    user_id = await get_current_user_id(credentials)
    user = await get_user_by_id(db, user_id)

    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Not authenticated",
            headers={"WWW-Authenticate": "Bearer"},
        )
    return user


async def get_optional_user_id(
    request: Request,
    credentials: Optional[HTTPAuthorizationCredentials] = Depends(security)
) -> Optional[str]:
    """Return user_id if authenticated, else None"""
    debug_auth_headers(request)

    if credentials and credentials.credentials:
        payload = verify_token(credentials.credentials)
        if payload and payload.get("sub"):
            return payload["sub"]

    session_token = request.cookies.get("session_token") or request.cookies.get("access_token")
    if session_token:
        payload = verify_token(session_token)
        if payload and payload.get("sub"):
            return payload["sub"]

    return None
