#!/usr/bin/env python3
"""
Startup migration script for NGOInfo-Copilot.
Runs Alembic migrations on startup but doesn't block the application.
"""

import subprocess
import sys
import logging
from pathlib import Path

logger = logging.getLogger(__name__)


def run_migrations():
    """Run Alembic migrations with error handling."""
    try:
        logger.info("Running database migrations...")

        # Run alembic upgrade head
        result = subprocess.run(
            [sys.executable, "-m", "alembic", "upgrade", "head"],
            capture_output=True,
            text=True,
            cwd=Path(__file__).parent,
        )

        if result.returncode == 0:
            logger.info("Database migrations completed successfully")
            if result.stdout:
                logger.info(f"Migration output: {result.stdout.strip()}")
        else:
            logger.warning(f"Database migrations failed: {result.stderr.strip()}")
            return False

    except Exception as e:
        logger.warning(f"Failed to run database migrations: {e}")
        return False

    return True


if __name__ == "__main__":
    # Configure basic logging
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
    )

    success = run_migrations()
    sys.exit(0 if success else 1)
