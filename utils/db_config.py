import os
import logging
from urllib.parse import urlparse, parse_qs, urlencode
from typing import Optional

logger = logging.getLogger(__name__)


def resolve_database_url() -> str:
    """
    Resolve database URL from environment variables with fallback order:
    DATABASE_URL, COPILOT_DATABASE_URL, POSTGRES_URL, DATABASE_CONNECTION_STRING

    Returns the first non-empty URL found and logs which one was used.
    """
    env_vars = [
        "DATABASE_URL",
        "COPILOT_DATABASE_URL",
        "POSTGRES_URL",
        "DATABASE_CONNECTION_STRING",
    ]

    for env_var in env_vars:
        url = os.getenv(env_var)
        if url and url.strip():
            logger.info(f"Using database URL from {env_var}")
            return normalize_database_url(url.strip())

    raise ValueError(
        f"None of the following environment variables contain a database URL: {', '.join(env_vars)}. "
        "Please set one of these variables with a valid PostgreSQL connection string."
    )


def normalize_database_url(url: str) -> str:
    """
    Normalize database URL by:
    1. Coercing to postgresql+asyncpg:// if no driver specified (for async operations)
    2. Stripping sslmode=... from URL (handled via connect_args for asyncpg)
    """
    # Parse the URL
    parsed = urlparse(url)

    # Coerce to postgresql+asyncpg:// if no driver specified (for async operations)
    if parsed.scheme in ["postgres", "postgresql"]:
        scheme = "postgresql+asyncpg"
    elif parsed.scheme == "postgresql+psycopg2":
        # Convert psycopg2 to asyncpg for async operations
        scheme = "postgresql+asyncpg"
    else:
        scheme = parsed.scheme

    # Reconstruct URL with normalized scheme
    normalized_url = f"{scheme}://{parsed.netloc}{parsed.path}"

    # Handle query parameters - strip sslmode for asyncpg compatibility
    query_params = parse_qs(parsed.query)
    
    # Remove sslmode from URL (asyncpg handles SSL via connect_args)
    if "sslmode" in query_params:
        logger.warning("Removing sslmode from URL - asyncpg uses connect_args for SSL")
        del query_params["sslmode"]

    # Reconstruct query string
    if query_params:
        query_string = urlencode(query_params, doseq=True)
        normalized_url += f"?{query_string}"

    # Add fragment if present
    if parsed.fragment:
        normalized_url += f"#{parsed.fragment}"

    logger.info(f"Normalized database URL: {scheme}://{parsed.netloc}{parsed.path}...")
    return normalized_url


def get_database_config() -> dict:
    """
    Get database configuration with connection pool settings.
    """
    url = resolve_database_url()
    
    # Check if SSL is required (default True in production)
    require_ssl = os.getenv("REQUIRE_DB_SSL", "true").lower() in ("true", "1", "yes")
    environment = os.getenv("ENV", "development").lower()
    
    # Default to True in production, False in development
    if environment == "production" and not require_ssl:
        logger.warning("SSL disabled in production environment - this is not recommended")

    # Base configuration for async operations
    config = {
        "url": url,
        "echo": False,
        "pool_size": 20,
        "max_overflow": 0,
        "pool_pre_ping": True,
        "pool_recycle": 1800,  # 30 minutes
    }

    # Add asyncpg-specific connection arguments
    if "asyncpg" in url:
        connect_args = {
            "command_timeout": 5,
            "server_settings": {
                "application_name": "ngoinfo-copilot"
            }
        }
        
        # Only add SSL if required
        if require_ssl:
            connect_args["ssl"] = True
            logger.info("SSL enabled for database connections")
        else:
            logger.warning("SSL disabled for database connections")
            
        config["connect_args"] = connect_args

    return config
