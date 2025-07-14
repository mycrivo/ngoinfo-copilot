from fastapi import APIRouter, Request, Depends, HTTPException, status
from fastapi.templating import Jinja2Templates
from fastapi.responses import HTMLResponse, JSONResponse
from sqlalchemy.ext.asyncio import AsyncSession
from db import get_db_session
from utils.auth import create_access_token, debug_auth_headers
import os
import logging

logger = logging.getLogger(__name__)

# Initialize Jinja2 templates
templates = Jinja2Templates(directory="templates")

router = APIRouter()


@router.get("/admin-test-ui", response_class=HTMLResponse)
async def admin_test_ui(request: Request):
    """
    Serve the admin test UI for proposal generation testing
    """
    return templates.TemplateResponse("admin-test-ui.html", {"request": request})


@router.post("/test-auth")
async def create_test_token(request: Request):
    """
    Create a test authentication token for admin UI testing
    Only available in development mode
    """
    debug_auth_headers(request)
    
    # Only allow in development
    if os.getenv("ENVIRONMENT", "development") != "development":
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Test authentication only available in development mode"
        )
    
    try:
        # Create a test token for admin user
        test_token = create_access_token(data={"sub": "admin_user_test"})
        
        logger.info("🔧 Created test authentication token for admin UI")
        
        return JSONResponse(content={
            "access_token": test_token,
            "token_type": "bearer",
            "message": "Test token created successfully",
            "usage": "Store this token in localStorage.setItem('access_token', 'TOKEN_VALUE')"
        })
        
    except Exception as e:
        logger.error(f"Error creating test token: {str(e)}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Failed to create test token"
        )


@router.get("/auth-debug")
async def auth_debug(request: Request):
    """
    Debug endpoint to check authentication headers and cookies
    """
    debug_auth_headers(request)
    
    return JSONResponse(content={
        "message": "Authentication debug completed",
        "check_logs": "Check server logs for authentication details",
        "headers": dict(request.headers),
        "cookies": dict(request.cookies)
    }) 