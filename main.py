from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager
import os
from db import init_db

# Import route modules
from routes import proposal_routes, profile, auth_routes, admin_ui


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

# CORS middleware - configured for WordPress frontend and admin UI
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://ngoinfo.org",  # WordPress frontend
        "https://www.ngoinfo.org",  # WordPress frontend with www
        "http://localhost:3000",  # Local development
        "http://localhost:8000",  # Local API server
        "https://*.railway.app",  # Railway deployment
        "*"  # Allow all origins (remove in production if needed)
    ],
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
    """Health check endpoint"""
    return {
        "status": "healthy",
        "service": "NGOInfo-Copilot",
        "version": "1.0.0"
    }


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