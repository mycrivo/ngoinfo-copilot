#!/usr/bin/env bash
set -e

echo "Running database migrations..."

# Check if DATABASE_URL is set
if [ -z "$DATABASE_URL" ]; then
    echo "ERROR: DATABASE_URL environment variable is not set"
    exit 1
fi

# Run migrations
alembic upgrade head

echo "Migrations applied successfully."
