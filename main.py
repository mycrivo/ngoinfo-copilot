from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager
import os
from db import init_db

# Import route modules
from routes import proposal_routes, profile, auth_routes, admin_ui

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
    lifespan=lifespan
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
        "Set-Cookie"
    ],
    expose_headers=[
        "Set-Cookie",
        "Authorization"
    ]
)

# Include routers
app.include_router(auth_routes.router, prefix="/api/auth", tags=["authentication"])
app.include_router(profile.router, prefix="/api/profile", tags=["profile"])
app.include_router(proposal_routes.router, prefix="/api/proposals", tags=["proposals"])
app.include_router(admin_ui.router, prefix="/admin", tags=["admin"])


@app.get("/healthcheck")
async def healthcheck():
    """Health check endpoint with database connectivity check"""
    from datetime import datetime
    from fastapi import status
    from fastapi.responses import JSONResponse
    
    app_name = os.getenv("APP_NAME", "NGOInfo-Copilot")
    version = "1.0.0"
    timestamp = datetime.utcnow().isoformat()
    
    # Test database connectivity
    db_status = "ok"
    overall_status = "ok"
    status_code = status.HTTP_200_OK
    
    try:
        from db import AsyncSessionLocal
        async with AsyncSessionLocal() as session:
            # Simple database connectivity test
            result = await session.execute("SELECT 1 as health_check")
            result.scalar_one()
        db_status = "ok"
    except Exception as e:
        db_status = "down"
        overall_status = "degraded"
        status_code = status.HTTP_503_SERVICE_UNAVAILABLE
    
    response_data = {
        "status": overall_status,
        "service": app_name,
        "version": version,
        "timestamp": timestamp,
        "db": db_status
    }
    
    return JSONResponse(content=response_data, status_code=status_code)


@app.get("/")
async def root():
    """Root endpoint with basic info"""
    return {
        "message": "NGOInfo-Copilot API",
        "docs": "/docs",
        "health": "/healthcheck"
    }


if __name__ == "__main__":
    import uvicorn
    
    # Get port from environment variable (Railway sets this)
    port = int(os.getenv("PORT", 8000))
    
    uvicorn.run("main:app", host="0.0.0.0", port=port, reload=False) 