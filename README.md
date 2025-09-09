# NGOInfo Copilot FastAPI Backend

A FastAPI-based backend service that provides AI-powered proposal generation services for NGOs. This service integrates with the NGOInfo Copilot WordPress plugin to deliver comprehensive proposal generation capabilities.

## Features

- **🤖 AI-Powered Generation**: Advanced proposal creation using GPT-4
- **🔒 Authentication**: Secure JWT-based user authentication  
- **📊 Usage Tracking**: Monitor API usage and limits
- **⚡ Rate Limiting**: Prevent abuse with configurable rate limits
- **🔄 Idempotency**: Duplicate request protection with idempotency keys
- **📄 Export Options**: PDF and DOCX format exports
- **🎯 Smart Matching**: Funding opportunity alignment scoring
- **💚 Health Monitoring**: Robust health checks with database connectivity
- **🔧 Database Resilience**: Multiple environment variable support with SSL enforcement

## Requirements

- Python 3.8 or higher
- PostgreSQL 12 or higher
- Access to OpenAI API
- SSL certificate for production use

## Environment Variables

### Required Database Configuration

The application supports multiple database URL environment variables with fallback priority:

1. `DATABASE_URL` (highest priority)
2. `COPILOT_DATABASE_URL`
3. `POSTGRES_URL`
4. `DATABASE_CONNECTION_STRING` (lowest priority)

**Database URL Rules:**
- Must be a valid PostgreSQL connection string
- If URL lacks a driver, automatically coerced to `postgresql+psycopg2://`
- If URL lacks `sslmode`, automatically appends `?sslmode=require`
- Supports both `postgresql+asyncpg://` (for async operations) and `postgresql+psycopg2://` (for sync operations)

**Railway Deployment Note:** Set the `DATABASE_URL` on the app service; ensure it uses `postgresql+asyncpg://` scheme.

## Railway ENV Checklist

**Required Environment Variables for Railway Deployment:**

```bash
# Database Configuration (use postgresql+asyncpg:// scheme)
DATABASE_URL=postgresql+asyncpg://USER:PASS@HOST:PORT/DB

# OpenAI Configuration  
OPENAI_API_KEY=sk-your-openai-api-key-here

# JWT Security (MUST be set for production)
JWT_SECRET_KEY=your-strong-32-plus-character-random-hex-secret

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com

# Environment
ENV=production

# SSL Configuration
REQUIRE_DB_SSL=true
```

**Critical Security Notes:**
- `JWT_SECRET_KEY` must be a strong 32+ character random secret (NOT the default placeholder)
- `DATABASE_URL` must use `postgresql+asyncpg://` scheme (no `sslmode=require` in URL)
- `ENV=production` triggers strict security validation
- `REQUIRE_DB_SSL=true` enforces SSL for database connections

### Other Required Variables

- `OPENAI_API_KEY`: Your OpenAI API key
- `JWT_SECRET_KEY`: Secret key for JWT token signing (32+ chars, no placeholders in prod)
- `CORS_ALLOWED_ORIGINS`: Comma-separated list of allowed origins (optional, defaults to ngoinfo.org)

### Optional Variables

- `APP_NAME`: Application name (defaults to "NGOInfo-Copilot")
- `ENV`: Environment name (defaults to "development")
- `SENTRY_DSN`: Sentry error tracking (optional)

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```
3. Set up environment variables (see above)
4. Run database migrations:
   ```bash
   python -m alembic upgrade head
   ```
5. Start the application:
   ```bash
   python main.py
   ```

## Database Setup

### Automatic Migration (Recommended)

The application includes a startup migration script that runs automatically:

```bash
python scripts/run_migrations.py
```

This script runs Alembic migrations but doesn't block the application startup if migrations fail.

### Manual Migration

For manual control over migrations:

```bash
# Create a new migration
python -m alembic revision --autogenerate -m "Description"

# Apply migrations
python -m alembic upgrade head

# Check current migration status
python -m alembic current
```

## Health Check

The application provides a robust health check endpoint at `/healthcheck` that:

- Tests database connectivity with a fresh connection
- Returns detailed status information
- Includes error details for debugging
- Reports JSON response with the following structure:

```json
{
  "status": "ok|degraded",
  "service": "NGOInfo-Copilot",
  "version": "1.0.0",
  "timestamp": "2024-01-01T12:00:00.000Z",
  "db": "up|down",
  "db_error": "Error details (if applicable)"
}
```

**Status Codes:**
- `200 OK`: Service healthy, database up
- `503 Service Unavailable`: Service degraded, database down

## API Endpoints

### Core Endpoints
- `GET /healthcheck` - System health verification
- `GET /docs` - Interactive API documentation
- `GET /` - Root endpoint with service information

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/refresh` - Token refresh

### Profiles
- `GET /api/profile` - Get user profile
- `PUT /api/profile` - Update user profile

### Proposals
- `POST /api/proposals/generate` - Generate new proposal
- `GET /api/proposals/{id}` - Get proposal details
- `PUT /api/proposals/{id}` - Update proposal
- `DELETE /api/proposals/{id}` - Delete proposal

### Usage
- `GET /api/usage/summary` - Get usage statistics

## Development

### Running Tests

```bash
# Run all tests
pytest

# Run specific test file
pytest tests/test_db_config.py

# Run with coverage
pytest --cov=.

# Run only unit tests
pytest -m unit
```

### Code Formatting

```bash
# Format code with black
black .

# Check code style with flake8
flake8 .

# Type checking with mypy
mypy .
```

### Database Configuration Testing

The application includes comprehensive tests for database URL resolution:

```bash
# Test database URL resolver
pytest tests/test_db_config.py -v

# Test healthcheck endpoint
pytest tests/test_healthcheck.py -v
```

## Deployment

### Railway Deployment

1. Connect your GitHub repository to Railway
2. Set the required environment variables in Railway dashboard
3. Ensure `DATABASE_URL` includes `sslmode=require`
4. Deploy automatically on push to main branch

### Docker Deployment

```bash
# Build the image
docker build -t ngoinfo-copilot .

# Run the container
docker run -p 8000:8000 \
  -e DATABASE_URL="postgresql://user:pass@host:5432/db?sslmode=require" \
  -e OPENAI_API_KEY="your-key" \
  -e JWT_SECRET_KEY="your-secret" \
  ngoinfo-copilot
```

## Monitoring

### Health Check Monitoring

Monitor the `/healthcheck` endpoint to ensure service health:

```bash
# Check health status
curl https://your-app.railway.app/healthcheck

# Expected response for healthy service:
{
  "status": "ok",
  "service": "NGOInfo-Copilot",
  "version": "1.0.0",
  "timestamp": "2024-01-01T12:00:00.000Z",
  "db": "up"
}
```

### Logs

The application provides detailed logging for:
- Database connection issues
- Health check failures
- Environment variable resolution
- Migration status

## Troubleshooting

### Database Connection Issues

1. **Check Environment Variables**: Ensure one of the supported database URL variables is set
2. **Verify SSL Mode**: Ensure `sslmode=require` is in your database URL
3. **Check Network**: Verify database host is accessible from your deployment environment
4. **Review Logs**: Check application logs for specific error messages

### Health Check Failures

1. **Database Down**: Check database service status
2. **Connection Timeout**: Verify network connectivity and firewall settings
3. **SSL Issues**: Ensure SSL certificates are valid and properly configured
4. **Pool Exhaustion**: Check connection pool settings and database load

## Security

- All database connections use SSL encryption
- JWT tokens are signed with secure keys
- CORS is properly configured for production domains
- Input validation and sanitization on all endpoints
- Rate limiting prevents abuse

## License

MIT License - see [LICENSE](LICENSE) for details.

## Contributing

This service is part of the NGOInfo Copilot project. For development coordination, please coordinate with the main project maintainers.