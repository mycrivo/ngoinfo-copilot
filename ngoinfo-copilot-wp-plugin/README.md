# NGOInfo Copilot WordPress Plugin

A WordPress plugin that integrates with the NGOInfo Copilot FastAPI backend to provide AI-powered proposal generation services for NGOs directly within their WordPress admin dashboard.

## Features

- **🔧 Settings Management**: Configure API connection to staging or production backend
- **💚 Health Monitoring**: Real-time API health checks with connection status
- **🔐 JWT Authentication**: Secure token-based authentication with configurable claims
- **📊 Usage Widget**: Dashboard widget showing proposal generation limits and usage
- **🛡️ Security First**: All inputs sanitized, outputs escaped, nonces for admin actions

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Access to NGOInfo Copilot API (staging or production)
- SSL certificate for production use

## Installation

1. Download the latest release ZIP from [Releases](../../releases)
2. In WordPress Admin, go to Plugins → Add New → Upload Plugin
3. Upload the ZIP file and activate the plugin
4. Go to Settings → NGOInfo Copilot to configure

## Configuration

### API Settings

1. **API Base URL**: Set to your backend environment
   - Staging: `https://staging-api.ngoinfo.org`
   - Production: `https://api.ngoinfo.org`

2. **JWT Configuration**: 
   - **Issuer (iss)**: Default `ngoinfo-wp` (leave default unless instructed)
   - **Audience (aud)**: Default `ngoinfo-copilot` (leave default unless instructed)
   - **Expiry**: Default 15 minutes (recommended)
   - **Signing Secret**: Enter a strong secret key (32+ characters)

3. **Environment**: Select staging or production to match your API base URL

### CORS Requirements

The backend must allow your WordPress site's origin:
- Production: `https://ngoinfo.org` and `https://www.ngoinfo.org`
- Staging: `https://staging.ngoinfo.org`

This was configured in Phase 0 of the backend hardening.

## Usage

### Health Check

1. Go to Settings → NGOInfo Copilot → Health tab
2. Click "Run Health Check" to verify connection
3. Successful response shows `{status: "ok", db: "ok"}` with response time

### Usage Widget

Add the usage widget to any page or post using the shortcode:

```
[ngoinfo_copilot_usage]
```

The widget displays:
- Current usage (proposals generated this month)
- Remaining proposals in quota
- Reset date for monthly limits

**Note**: Widget requires user to be logged in. Unauthenticated users see a login prompt.

## Security

### JWT Implementation

- Uses HS256 algorithm for signing
- Claims include: `sub` (user ID), `email`, `plan_tier`, `iat`, `exp`, `iss`, `aud`, `nonce`
- Signing secret is encrypted and stored in WordPress options
- Secrets are never logged or displayed in error messages

### Data Protection

- All admin pages require `manage_options` capability
- Nonces protect against CSRF attacks
- All user inputs are sanitized
- All outputs are escaped
- No direct file access allowed

### Secret Management

- JWT signing secrets are encrypted using OpenSSL
- Never store secrets in code or version control
- Secrets are entered only through WordPress admin interface
- Failed API calls redact sensitive information from logs

## Error Handling

API errors include request IDs for support tracking:
```
Error: Connection failed (Request ID: abc123-def456)
```

Common issues:
- **Invalid API key**: Check your JWT signing secret
- **CORS error**: Verify your domain is allowed by the backend
- **Connection timeout**: Check API base URL and network connectivity

## Development

### Coding Standards

This plugin follows WordPress Coding Standards:

```bash
# Install dependencies
composer install --dev

# Run PHPCS
./vendor/bin/phpcs

# Fix auto-fixable issues
./vendor/bin/phpcbf
```

### File Structure

```
ngoinfo-copilot-wp/
├── ngoinfo-copilot.php          # Main plugin file
├── includes/                    # Core classes
│   ├── class-autoloader.php     # PSR-4 autoloader
│   ├── class-settings.php       # Settings management
│   ├── class-health.php         # Health check functionality
│   ├── class-auth.php           # JWT authentication
│   ├── class-api-client.php     # API client wrapper
│   ├── class-usage-widget.php   # Usage widget
│   └── helpers.php              # Utility functions
├── admin/                       # Admin interface
│   └── views/                   # Admin templates
├── public/                      # Public interface
│   └── views/                   # Public templates
└── assets/                      # CSS and JS files
```

## API Integration

This plugin integrates with the NGOInfo Copilot FastAPI backend:

### Endpoints Used

- `GET /healthcheck` - System health verification
- `GET /api/usage/summary` - User usage statistics

### Authentication Flow

1. Plugin mints JWT with user information
2. JWT included in `Authorization: Bearer <token>` header
3. Backend validates JWT and processes request
4. Response includes standard error format with request IDs

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Support

For technical support:
1. Check the Health panel for connection issues
2. Note any request IDs from error messages
3. Contact support with your WordPress version, plugin version, and request IDs

## License

MIT License - see [LICENSE](LICENSE) for details.

## Contributing

This plugin is part of the NGOInfo Copilot project. For development coordination, please coordinate with the main project maintainers.