# NGOInfo-Copilot Deployment Guide

## Railway Deployment

### Required Environment Variables

Set these in your Railway dashboard:

```bash
# Database Configuration
DATABASE_URL=postgresql+asyncpg://username:password@hostname:5432/database_name

# OpenAI Configuration
OPENAI_API_KEY=your-openai-api-key-here

# JWT Configuration (for authentication)
JWT_SECRET_KEY=your-super-secret-jwt-key-here-change-in-production
JWT_ALGORITHM=HS256
JWT_ACCESS_TOKEN_EXPIRE_MINUTES=1440

# Environment
ENVIRONMENT=production
```

### Database Setup

1. **Option 1: New PostgreSQL Plugin**
   - Add PostgreSQL plugin in Railway
   - Copy the DATABASE_URL from plugin settings

2. **Option 2: Link to Existing ngoinfo-requirement-agent DB**
   - Use the same DATABASE_URL as your requirement-agent project
   - Ensure both apps can access the same database

### Deployment Steps

1. Push code to GitHub repository
2. Create new Railway project
3. Connect to GitHub repository
4. Add PostgreSQL plugin or configure external database
5. Set environment variables in Railway dashboard
6. Deploy automatically

### Port Configuration

Railway automatically sets the PORT environment variable. The app is configured to use:
- Railway's PORT if available
- Fallback to port 8000

### Health Check

Once deployed, test these endpoints:
- `GET /` - API information
- `GET /healthcheck` - Health status
- `GET /docs` - API documentation 