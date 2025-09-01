"""
Centralized error handling for FastAPI application
"""
from fastapi import Request, HTTPException
from fastapi.responses import JSONResponse
from fastapi.exceptions import RequestValidationError
from starlette.exceptions import HTTPException as StarletteHTTPException
from utils.logging_config import get_logger, request_id_ctx_var
import traceback
from typing import Dict, Any


logger = get_logger(__name__)


def get_request_id() -> str:
    """Get current request ID from context"""
    return request_id_ctx_var.get("")


def create_error_response(
    code: str,
    message: str,
    status_code: int = 500,
    details: Dict[str, Any] = None
) -> JSONResponse:
    """Create standardized error response"""
    response_data = {
        "code": code,
        "message": message,
        "request_id": get_request_id()
    }
    
    if details:
        response_data["details"] = details
    
    return JSONResponse(
        status_code=status_code,
        content=response_data
    )


async def http_exception_handler(request: Request, exc: HTTPException) -> JSONResponse:
    """Handle HTTPException instances"""
    logger.warning(
        "HTTP exception",
        status_code=exc.status_code,
        detail=exc.detail,
        path=str(request.url.path),
        method=request.method
    )
    
    return create_error_response(
        code=f"HTTP_{exc.status_code}",
        message=str(exc.detail),
        status_code=exc.status_code
    )


async def starlette_http_exception_handler(request: Request, exc: StarletteHTTPException) -> JSONResponse:
    """Handle Starlette HTTPException instances"""
    logger.warning(
        "Starlette HTTP exception",
        status_code=exc.status_code,
        detail=exc.detail,
        path=str(request.url.path),
        method=request.method
    )
    
    return create_error_response(
        code=f"HTTP_{exc.status_code}",
        message=str(exc.detail),
        status_code=exc.status_code
    )


async def validation_exception_handler(request: Request, exc: RequestValidationError) -> JSONResponse:
    """Handle request validation errors"""
    logger.warning(
        "Validation error",
        errors=exc.errors(),
        path=str(request.url.path),
        method=request.method
    )
    
    # Format validation errors for user consumption
    validation_details = []
    for error in exc.errors():
        field = ".".join(str(loc) for loc in error["loc"])
        validation_details.append({
            "field": field,
            "message": error["msg"],
            "type": error["type"]
        })
    
    return create_error_response(
        code="VALIDATION_ERROR",
        message="Request validation failed",
        status_code=422,
        details={"validation_errors": validation_details}
    )


async def general_exception_handler(request: Request, exc: Exception) -> JSONResponse:
    """Handle unexpected exceptions"""
    logger.error(
        "Unexpected exception",
        error=str(exc),
        error_type=exc.__class__.__name__,
        path=str(request.url.path),
        method=request.method,
        traceback=traceback.format_exc()
    )
    
    # Don't expose internal error details in production
    import os
    environment = os.getenv("ENV", "development").lower()
    
    if environment == "development":
        details = {
            "error_type": exc.__class__.__name__,
            "traceback": traceback.format_exc()
        }
    else:
        details = None
    
    return create_error_response(
        code="INTERNAL_ERROR",
        message="An unexpected error occurred",
        status_code=500,
        details=details
    )


def setup_error_handlers(app):
    """Set up all error handlers for the FastAPI app"""
    app.add_exception_handler(HTTPException, http_exception_handler)
    app.add_exception_handler(StarletteHTTPException, starlette_http_exception_handler)
    app.add_exception_handler(RequestValidationError, validation_exception_handler)
    app.add_exception_handler(Exception, general_exception_handler)
