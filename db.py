from sqlalchemy.ext.asyncio import AsyncSession, create_async_engine, async_sessionmaker
from sqlalchemy.orm import DeclarativeBase
import os
from typing import AsyncGenerator
from dotenv import load_dotenv
from utils.db_config import get_database_config
import logging

# Load environment variables from .env file
load_dotenv()

logger = logging.getLogger(__name__)


class Base(DeclarativeBase):
    """Base class for all database models"""

    pass


# Get database configuration with robust URL resolution
try:
    db_config = get_database_config()
    DATABASE_URL = db_config["url"]
    logger.info("Database configuration loaded successfully")
except Exception as e:
    logger.error(f"Failed to load database configuration: {e}")
    raise

# Create async engine with enhanced configuration
engine = create_async_engine(
    DATABASE_URL, **{k: v for k, v in db_config.items() if k != "url"}
)

# Create async session factory
AsyncSessionLocal = async_sessionmaker(
    engine,
    class_=AsyncSession,
    expire_on_commit=False,
)


async def get_db_session() -> AsyncGenerator[AsyncSession, None]:
    """Dependency to get database session"""
    async with AsyncSessionLocal() as session:
        try:
            yield session
        except Exception:
            await session.rollback()
            raise
        finally:
            await session.close()


async def init_db():
    """Initialize database tables"""
    async with engine.begin() as conn:
        # Import all models to ensure they're registered
        from models import ngo_profiles, proposals, funding_opportunities, users

        # Create tables (only creates if they don't exist)
        await conn.run_sync(Base.metadata.create_all)
