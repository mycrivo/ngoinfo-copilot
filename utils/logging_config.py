"""
Structured logging configuration with request context
"""
import os
import sys
import uuid
import structlog
from typing import Dict, Any
from contextvars import ContextVar
from fastapi import Request, Response
from starlette.middleware.base import BaseHTTPMiddleware
import time


# Context variable for request ID
request_id_ctx_var: ContextVar[str] = ContextVar("request_id", default="")


class RequestIDMiddleware(BaseHTTPMiddleware):
    """Middleware to generate and inject request IDs"""
    
    async def dispatch(self, request: Request, call_next):
        # Generate request ID
        request_id = str(uuid.uuid4())
        request_id_ctx_var.set(request_id)
        
        # Add to request state for easy access
        request.state.request_id = request_id
        
        # Track request timing
        start_time = time.time()
        
        # Process request
        response = await call_next(request)
        
        # Calculate latency
        latency_ms = round((time.time() - start_time) * 1000, 2)
        
        # Add request ID to response headers
        response.headers["X-Request-ID"] = request_id
        
        # Log request completion
        logger = structlog.get_logger()
        logger.info(
            "Request completed",
            method=request.method,
            path=str(request.url.path),
            status_code=response.status_code,
            latency_ms=latency_ms,
            user_agent=request.headers.get("user-agent", ""),
        )
        
        return response


def add_request_id(logger, method_name, event_dict):
    """Add request ID to log entries"""
    request_id = request_id_ctx_var.get("")
    if request_id:
        event_dict["request_id"] = request_id
    return event_dict


def configure_logging():
    """Configure structured logging with JSON output"""
    log_level = os.getenv("LOG_LEVEL", "INFO").upper()
    
    # Configure structlog
    structlog.configure(
        processors=[
            structlog.stdlib.filter_by_level,
            structlog.stdlib.add_logger_name,
            structlog.stdlib.add_log_level,
            structlog.stdlib.PositionalArgumentsFormatter(),
            add_request_id,
            structlog.processors.TimeStamper(fmt="iso"),
            structlog.processors.StackInfoRenderer(),
            structlog.processors.format_exc_info,
            structlog.processors.UnicodeDecoder(),
            structlog.processors.JSONRenderer()
        ],
        context_class=dict,
        logger_factory=structlog.stdlib.LoggerFactory(),
        wrapper_class=structlog.stdlib.BoundLogger,
        cache_logger_on_first_use=True,
    )
    
    # Configure standard library logging
    import logging
    logging.basicConfig(
        format="%(message)s",
        stream=sys.stdout,
        level=getattr(logging, log_level, logging.INFO),
    )


def get_logger(name: str = None):
    """Get a structured logger instance"""
    return structlog.get_logger(name)


def log_exception(logger, exc: Exception, context: Dict[str, Any] = None):
    """Log an exception with context"""
    context = context or {}
    logger.error(
        "Exception occurred",
        error=str(exc),
        error_type=exc.__class__.__name__,
        **context
    )
