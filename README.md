# NGOInfo-Copilot 🤖

AI-powered proposal generation service for NGOs, enabling automated creation of high-quality grant proposals tailored to specific funding opportunities.

## 🚀 Features

- **🤖 AI-Powered Proposal Generation**: Uses OpenAI GPT-4 to generate comprehensive, professional grant proposals
- **👤 User Authentication**: JWT-based authentication with secure user management
- **📄 Document Export**: Export proposals to DOCX and PDF formats
- **🎯 Donor-Specific Templates**: Tailored proposal templates for major donors (Gates Foundation, Ford Foundation, etc.)
- **📊 Quality Scoring**: Multi-dimensional scoring system for proposal quality assessment
- **🔄 Version Control**: Track proposal edits and maintain version history
- **⭐ Rating System**: User feedback and rating system for generated proposals
- **📱 RESTful API**: Complete API with FastAPI and automatic documentation

## 🏗️ Tech Stack

- **Backend**: FastAPI, Python 3.11
- **Database**: PostgreSQL with AsyncPG
- **AI**: OpenAI GPT-4 API
- **Authentication**: JWT with bcrypt password hashing
- **Document Generation**: python-docx, fpdf2
- **Deployment**: Railway, Docker
- **Testing**: pytest, pytest-asyncio

## 📋 API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `GET /api/auth/me` - Get current user
- `POST /api/auth/refresh` - Refresh token

### Profile Management
- `GET /api/profile/` - Get NGO profile
- `POST /api/profile/` - Create/update NGO profile

### Proposal Management
- `POST /api/proposals/generate` - Generate AI proposal
- `GET /api/proposals/` - List user proposals
- `GET /api/proposals/{id}` - Get specific proposal
- `PUT /api/proposals/{id}` - Update proposal
- `POST /api/proposals/{id}/rate` - Rate proposal
- `DELETE /api/proposals/{id}/archive` - Archive proposal
- `GET /api/proposals/{id}/export/{format}` - Export proposal (PDF/DOCX)

### Health & Documentation
- `GET /healthcheck` - Health check
- `GET /docs` - API documentation

## 🛠️ Installation & Development

### Prerequisites
- Python 3.11+
- PostgreSQL database
- OpenAI API key

### Environment Variables
```bash
DATABASE_URL=postgresql+asyncpg://user:pass@host:port/dbname
OPENAI_API_KEY=your-openai-api-key
JWT_SECRET_KEY=your-secret-key
JWT_ALGORITHM=HS256
JWT_ACCESS_TOKEN_EXPIRE_MINUTES=1440
ENVIRONMENT=development
```

### Local Development
```bash
# Clone the repository
git clone https://github.com/yourusername/ngoinfo-copilot.git
cd ngoinfo-copilot

# Install dependencies
pip install -r requirements.txt

# Set up environment variables
cp .env.example .env  # Edit with your values

# Run the application
uvicorn main:app --reload
```

## 🚀 Deployment

### Railway Deployment

1. **Push to GitHub**
   ```bash
   git push origin main
   ```

2. **Create Railway Project**
   - Go to [railway.app/new](https://railway.app/new)
   - Connect to your GitHub repository
   - Add PostgreSQL plugin
   - Set environment variables in Railway dashboard

3. **Environment Variables to Set**
   - `DATABASE_URL` (from PostgreSQL plugin)
   - `OPENAI_API_KEY`
   - `JWT_SECRET_KEY`
   - `ENVIRONMENT=production`

### Docker Deployment
```bash
# Build image
docker build -t ngoinfo-copilot .

# Run container
docker run -p 8000:8000 --env-file .env ngoinfo-copilot
```

## 📊 Database Schema

### Core Models
- **Users**: Authentication and user management
- **NGO Profiles**: Organization information and capabilities
- **Proposals**: Generated proposals with metadata
- **Funding Opportunities**: External funding data (from ReqAgent)

## 🤖 AI Prompt System

### Dynamic Prompt Generation
- **Organization Profile Formatting**: Structured NGO data for AI context
- **Funding Opportunity Analysis**: Donor requirements and priorities
- **Donor-Specific Templates**: Customized guidelines for major funders
- **Quality Scoring**: Multi-factor assessment of generated content

### Supported Donors
- Gates Foundation
- Ford Foundation
- Open Society Foundations
- USAID
- European Union
- World Bank
- United Nations
- Default templates for other donors

## 📈 Quality Metrics

### Proposal Scoring
- **Confidence Score**: Content quality and structure assessment
- **Alignment Score**: Match with funding opportunity requirements
- **Completeness Score**: Presence of key proposal sections

### Profile Scoring
- **Completeness**: Organization information coverage (0-100%)
- **Quality Indicators**: Mission clarity, project history, capacity demonstration

## 🔒 Security Features

- **JWT Authentication**: Secure token-based authentication
- **Password Hashing**: bcrypt with salt for secure password storage
- **Environment Variables**: Sensitive data protection
- **Input Validation**: Pydantic schemas for request validation
- **SQL Injection Protection**: SQLAlchemy ORM with parameterized queries

## 📚 Documentation

- **API Documentation**: Available at `/docs` when running
- **Health Monitoring**: `/healthcheck` endpoint for system status
- **Deployment Guide**: See `DEPLOYMENT.md` for detailed instructions

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Check the API documentation at `/docs`
- Review the deployment guide in `DEPLOYMENT.md`

---

**Built with ❤️ for NGOs worldwide** 