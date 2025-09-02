from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager
import os
from db import init_db

# Import route modules
from routes import proposal_routes, profile, auth_routes, admin_ui, usage_routes

# Import configuration modules
from utils.logging_config import configure_logging, RequestIDMiddleware
from utils.error_handlers import setup_error_handlers
from utils.sentry_config import setup_sentry


# Initialize configuration
configure_logging()
setup_sentry()


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Application lifespan events"""
    # Startup
    await init_db()
    yield
    # Shutdown
    pass


app = FastAPI(
    title="NGOInfo-Copilot",
    description="AI-powered proposal generation service for NGOs",
    version="1.0.0",
    lifespan=lifespan,
)

# Add middleware in correct order
app.add_middleware(RequestIDMiddleware)

# Set up error handlers
setup_error_handlers(app)


# CORS middleware - configured from environment variables
def get_cors_origins() -> list:
    """Get CORS origins from environment variable with development fallback"""
    cors_origins = os.getenv("CORS_ALLOWED_ORIGINS", "")
    origins = [origin.strip() for origin in cors_origins.split(",") if origin.strip()]

    # Add localhost in development environment
    environment = os.getenv("ENV", "development").lower()
    if environment == "development":
        dev_origins = ["http://localhost:3000", "http://localhost:8000"]
        origins.extend(dev_origins)

    # Fallback to secure defaults if no origins specified
    if not origins:
        origins = ["https://ngoinfo.org", "https://www.ngoinfo.org"]
        if environment == "development":
            origins.extend(["http://localhost:3000", "http://localhost:8000"])

    return origins


app.add_middleware(
    CORSMiddleware,
    allow_origins=get_cors_origins(),
    allow_credentials=True,  # Enable credentials for session cookies
    allow_methods=["GET", "POST", "PUT", "DELETE", "OPTIONS", "HEAD"],
    allow_headers=[
        "Authorization",
        "Content-Type",
        "Accept",
        "Origin",
        "X-Requested-With",
        "X-CSRF-Token",
        "Cookie",
        "Set-Cookie",
    ],
    expose_headers=["Set-Cookie", "Authorization"],
)

# Include routers
app.include_router(auth_routes.router, prefix="/api/auth", tags=["authentication"])
app.include_router(profile.router, prefix="/api/profile", tags=["profile"])
app.include_router(proposal_routes.router, prefix="/api/proposals", tags=["proposals"])
app.include_router(usage_routes.router, prefix="/api/usage", tags=["usage"])
app.include_router(admin_ui.router, prefix="/admin", tags=["admin"])


@app.get("/healthcheck")
async def healthcheck():
    """Health check endpoint with database connectivity check"""
    from datetime import datetime
    from fastapi import status
    from fastapi.responses import JSONResponse
    from sqlalchemy import text
    import logging

    logger = logging.getLogger(__name__)

    app_name = os.getenv("APP_NAME", "NGOInfo-Copilot")
    version = "1.0.0"
    timestamp = datetime.utcnow().isoformat()

    # Test database connectivity with fresh connection
    db_status = "down"
    overall_status = "degraded"
    status_code = status.HTTP_503_SERVICE_UNAVAILABLE
    db_error = None

    try:
        from db import engine

        # Use a fresh connection for health check
        async with engine.connect() as conn:
            result = await conn.execute(text("SELECT 1 as health_check"))
            result.scalar_one()
        db_status = "up"
        overall_status = "ok"
        status_code = status.HTTP_200_OK
        logger.info("Database health check successful")
    except Exception as e:
        db_error = f"{type(e).__name__}: {str(e)}"
        logger.error(f"Database health check failed: {db_error}")

    response_data = {
        "status": overall_status,
        "service": app_name,
        "version": version,
        "timestamp": timestamp,
        "db": db_status,
    }

    # Include error details in response for debugging
    if db_error:
        response_data["db_error"] = db_error

    return JSONResponse(content=response_data, status_code=status_code)


@app.get("/")
async def root():
    """Root endpoint with basic info"""
    return {
        "service": "NGOInfo-Copilot",
        "description": "AI-powered proposal generation service for NGOs",
        "version": "1.0.0",
        "status": "operational",
        "endpoints": {
            "docs": "/docs",
            "openapi": "/docs/openapi.json",
            "health": "/healthcheck",
            "auth": "/api/auth/*",
            "profiles": "/api/profile",
            "proposals": "/api/proposals/*",
            "usage": "/api/usage/*",
        },
    }


@app.get("/docs/openapi.json")
async def get_openapi_spec():
    """Export OpenAPI specification as JSON"""
    from fastapi.openapi.utils import get_openapi

    if not app.openapi_schema:
        app.openapi_schema = get_openapi(
            title="NGOInfo-Copilot API",
            version="1.0.0",
            description="""
## AI-Powered Proposal Generation for NGOs

This API provides comprehensive proposal generation services for Non-Governmental Organizations (NGOs).

### Key Features
- 🤖 **AI-Powered Generation**: Advanced proposal creation using GPT-4
- 🔒 **Authentication**: Secure JWT-based user authentication  
- 📊 **Usage Tracking**: Monitor API usage and limits
- ⚡ **Rate Limiting**: Prevent abuse with configurable rate limits
- 🔄 **Idempotency**: Duplicate request protection with idempotency keys
- 📄 **Export Options**: PDF and DOCX format exports
- 🎯 **Smart Matching**: Funding opportunity alignment scoring

### Rate Limits
- **Generate**: 5 requests per minute (configurable)
- **Export**: 10 requests per minute (configurable)

### Idempotency
Use the `Idempotency-Key` header for generate requests to ensure duplicate protection.

### Error Format
All errors follow a standardized format:
```json
{
  "code": "ERROR_CODE",
  "message": "Human readable message",
  "request_id": "uuid",
  "details": {}
}
```
            """.strip(),
            routes=app.routes,
        )

        # Add custom examples and headers to OpenAPI schema
        if "paths" in app.openapi_schema:
            # Add idempotency header to generate endpoint
            generate_path = "/api/proposals/generate"
            if generate_path in app.openapi_schema["paths"]:
                post_schema = app.openapi_schema["paths"][generate_path]["post"]

                # Add Idempotency-Key header
                if "parameters" not in post_schema:
                    post_schema["parameters"] = []

                post_schema["parameters"].append(
                    {
                        "name": "Idempotency-Key",
                        "in": "header",
                        "required": False,
                        "schema": {"type": "string"},
                        "description": "Optional idempotency key to prevent duplicate requests",
                    }
                )

                # Add 422 response example
                if "responses" not in post_schema:
                    post_schema["responses"] = {}

                post_schema["responses"]["422"] = {
                    "description": "Validation Error",
                    "content": {
                        "application/json": {
                            "example": {
                                "code": "VALIDATION_ERROR",
                                "message": "Must provide exactly one of: funding_opportunity_id OR (custom_brief OR quick_fields)",
                                "request_id": "123e4567-e89b-12d3-a456-426614174000",
                                "details": {
                                    "input_validation": "Must provide exactly one of: funding_opportunity_id OR (custom_brief OR quick_fields)"
                                },
                            }
                        }
                    },
                }

                # Add 429 response example
                post_schema["responses"]["429"] = {
                    "description": "Rate Limit Exceeded",
                    "content": {
                        "application/json": {
                            "example": {
                                "code": "RATE_LIMIT_EXCEEDED",
                                "message": "Rate limit exceeded. Maximum 5 requests per minute for proposal generation.",
                                "request_id": "123e4567-e89b-12d3-a456-426614174000",
                                "details": {"limit": 5, "action": "generate"},
                            }
                        }
                    },
                }

    return app.openapi_schema


if __name__ == "__main__":
    import uvicorn

    # Get port from environment variable (Railway sets this)
    port = int(os.getenv("PORT", 8000))

    uvicorn.run("main:app", host="0.0.0.0", port=port, reload=False)
