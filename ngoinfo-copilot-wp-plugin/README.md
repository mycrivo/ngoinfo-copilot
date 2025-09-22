# NGOInfo Copilot WordPress Plugin

A secure WordPress plugin that integrates with the NGOInfo Copilot FastAPI backend to provide AI-powered proposal generation for NGOs.

## Features

- **Secure API Integration**: JWT-based authentication with encrypted secret storage
- **Health Monitoring**: Real-time API health checks and connection status
- **Usage Tracking**: Display monthly usage limits and remaining proposals
- **Admin Dashboard**: Complete settings management and configuration
- **CORS Compliant**: Designed to work with Phase 0 hardened backend
- **WordPress Standards**: Follows WordPress coding standards and security practices

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- OpenSSL extension for JWT encryption
- NGOInfo Copilot backend API (staging or production)

## Installation

1. Download the plugin ZIP file from the [Releases page](../../releases)
2. In your WordPress admin, go to **Plugins > Add New > Upload Plugin**
3. Choose the downloaded ZIP file and click **Install Now**
4. Activate the plugin
5. Go to **Settings > NGOInfo Copilot** to configure

## Configuration

### API Settings

1. **API Base URL**: Enter your NGOInfo Copilot backend URL
   - Staging: `https://staging-api.ngoinfo.org`
   - Production: `https://api.ngoinfo.org`

2. **Environment**: Select staging or production to match your API

3. **JWT Signing Secret**: Enter a strong secret (32+ characters with mixed case, numbers, and symbols)
   - This secret must match the one configured in your backend
   - The plugin stores this encrypted using WordPress salts

### CORS Requirements

Your NGOInfo Copilot backend must allow your WordPress site's origin:

- **Production**: `https://ngoinfo.org`, `https://www.ngoinfo.org`
- **Staging**: `https://staging.ngoinfo.org`
- **Development**: `http://localhost:3000`, `http://localhost:8000`

This was configured in Phase 0 of the backend hardening.

## Usage

### Usage Widget

Display your current proposal usage anywhere with the shortcode:

```
[ngoinfo_copilot_usage]
```

**Shortcode Attributes:**
- `theme="default"` - Widget theme (default, compact, minimal)
- `show_refresh="true"` - Show refresh button
- `cache_time="300"` - Cache time in seconds (default: 5 minutes)

**Examples:**
```
[ngoinfo_copilot_usage theme="compact"]
[ngoinfo_copilot_usage show_refresh="false" cache_time="600"]
```

### Health Monitoring

- Go to **Settings > NGOInfo Copilot > Health** tab
- Click **Run Health Check** to test API connectivity
- View response times, status codes, and error details
- Check configuration requirements

## JWT Authentication

The plugin uses JWT tokens for secure API authentication:

- **Claims**: User ID, email, plan tier, issued/expiry times, issuer, audience, nonce
- **Algorithm**: HS256 (HMAC-SHA256)
- **Security**: Secrets are encrypted at rest, never logged or displayed
- **Expiry**: Configurable (default: 15 minutes)

## Security Notes

- All admin pages require `manage_options` capability
- All forms use WordPress nonces for CSRF protection
- JWT secrets are encrypted using WordPress salts
- Sensitive data is redacted from logs
- All outputs are escaped, all inputs are sanitized
- No direct file access allowed

## API Endpoints Used

The plugin communicates with these backend endpoints:

- `GET /healthcheck` - API health status
- `GET /api/usage/summary` - User usage statistics

## Error Handling

All API errors include request IDs for debugging:

- Connection failures show network error details
- HTTP errors display status codes and messages
- Backend errors include request_id for tracing
- Rate limit errors (429) show retry information

## Caching

- Usage data is cached for 5 minutes by default
- Cache can be manually refreshed using widget buttons
- Cache is cleared automatically on successful API calls
- Cache keys are user-specific for security

## Logging

The plugin logs important events to WordPress debug log when `WP_DEBUG_LOG` is enabled:

- Health check results
- API connection errors
- JWT token generation failures
- Configuration changes

Sensitive information is automatically redacted from logs.

## Compatibility

- **WordPress**: 5.0 - 6.4+
- **PHP**: 7.4 - 8.3
- **Browsers**: Modern browsers with ES6 support
- **Mobile**: Responsive design for all screen sizes

## Development

### File Structure

```
ngoinfo-copilot-wp/
├── ngoinfo-copilot.php          # Main plugin file
├── includes/                    # Core classes
│   ├── class-settings.php       # Settings management
│   ├── class-health.php         # Health checks
│   ├── class-auth.php           # JWT authentication
│   ├── class-api-client.php     # API communication
│   ├── class-usage-widget.php   # Usage widget
│   └── helpers.php              # Utility functions
├── admin/views/                 # Admin templates
│   ├── settings-page.php        # Settings page
│   └── health-panel.php         # Health panel
├── public/views/                # Public templates
│   └── usage-widget.php         # Widget template
├── assets/                      # CSS and JavaScript
│   ├── css/admin.css            # Admin styles
│   ├── css/public.css           # Public styles
│   ├── js/admin.js              # Admin scripts
│   └── js/public.js             # Public scripts
├── README.md                    # Documentation
└── CHANGELOG.md                 # Version history
```

### Local Development

1. Clone the repository to your WordPress plugins directory
2. Ensure your backend API is running with CORS configured
3. Configure the plugin with your local API URL
4. Enable WordPress debug logging for detailed error information

## Support

For issues and support:

1. Check the Health panel for configuration problems
2. Review WordPress debug logs for error details
3. Verify CORS settings on your backend
4. Ensure your JWT secret matches the backend configuration

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.





# ngoinfo-copilot-wp-plugin



