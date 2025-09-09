# Changelog

All notable changes to the NGOInfo Copilot WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2025-01-09

### Added
- Initial plugin scaffold with WordPress coding standards
- Settings management for API configuration
  - API Base URL configuration (staging/production)
  - JWT authentication settings (issuer, audience, expiry, signing secret)
  - Environment selection with automatic URL detection
- Health monitoring panel
  - Backend API health check functionality
  - Connection status display with response times
  - CORS verification notes
- JWT authentication system
  - Secure token minting with HS256 algorithm
  - User claims integration (sub, email, plan_tier, iat, exp, iss, aud, nonce)
  - Encrypted secret storage in WordPress options
- API client wrapper
  - Integration with NGOInfo Copilot FastAPI backend
  - Standardized error handling with request ID support
  - Authorization header management
- Usage widget functionality
  - Shortcode `[ngoinfo_copilot_usage]` for displaying usage statistics
  - Integration with `/api/usage/summary` endpoint
  - Authentication-aware rendering
- Security features
  - Nonce protection for all admin actions
  - Capability checks (`manage_options`) for admin pages
  - Input sanitization and output escaping
  - No direct file access protection
- Development infrastructure
  - PSR-4 autoloader for classes
  - PHPCS configuration for WordPress coding standards
  - GitHub Actions for automated releases
  - Comprehensive documentation

### Security
- JWT signing secrets encrypted using OpenSSL
- All admin operations protected with WordPress nonces
- User capability verification for administrative functions
- Secure handling of API credentials
- No secrets exposed in error messages or logs

### Documentation
- Comprehensive README with installation and configuration instructions
- Security guidelines and best practices
- API integration documentation
- Development setup and coding standards




