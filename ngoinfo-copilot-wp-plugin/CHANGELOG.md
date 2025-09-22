# Changelog

All notable changes to the NGOInfo Copilot WordPress plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2024-01-XX

### Added

#### Core Plugin
- Initial WordPress plugin scaffold with proper plugin header
- Autoloader for NGOInfo\Copilot namespace classes
- Plugin activation/deactivation hooks with default option creation
- Proper WordPress plugin structure following WP coding standards

#### Settings Management
- Admin settings page under Settings → NGOInfo Copilot
- API Base URL configuration with URL validation
- Environment selection (staging/production) with HTTPS enforcement for production
- JWT configuration with issuer, audience, and expiry settings
- JWT signing secret with encrypted storage using WordPress salts
- Secret strength validation (32+ chars, mixed case, numbers, symbols)
- Real-time status card showing configuration state
- Settings form validation with field-level error handling

#### Health Monitoring
- Dedicated Health tab in admin settings
- Real-time API health check functionality via AJAX
- Backend `/healthcheck` endpoint testing
- Response time measurement and HTTP status code display
- JSON response parsing and formatted display
- Error handling with request ID extraction
- Configuration requirement checking
- CORS information and troubleshooting guide

#### JWT Authentication
- Secure JWT token minting for API authentication
- HS256 algorithm implementation using WordPress built-in functions
- Standard JWT claims: sub, email, plan_tier, iat, exp, iss, aud, nonce
- Encrypted secret storage with WordPress salt-based encryption
- Authorization header injection for API requests
- JWT configuration validation and status reporting
- Automatic secret redaction in logs and error messages

#### API Client
- HTTP request wrapper using WordPress HTTP API
- Automatic JWT authentication header injection
- Standard error response normalization
- Request ID extraction from backend error responses
- Usage summary endpoint integration (`GET /api/usage/summary`)
- Connection status monitoring and reporting
- Request/response logging with sensitive data redaction
- Timeout and error handling with user-friendly messages

#### Usage Widget
- `[ngoinfo_copilot_usage]` shortcode implementation
- Real-time usage data display (used, remaining, reset date)
- Usage progress bar with status-based color coding
- Multiple widget themes (default, compact, minimal)
- Automatic data caching with configurable TTL (default: 5 minutes)
- Manual refresh functionality via AJAX
- Login prompt for unauthenticated users
- Error display with request ID and retry functionality
- Auto-refresh every 5 minutes for active widgets

#### Security Features
- All admin pages protected with `manage_options` capability checks
- WordPress nonces for all AJAX requests and form submissions
- Complete input sanitization and output escaping
- JWT secrets encrypted at rest using WordPress salts
- Sensitive data redaction in logs and error messages
- No direct file access protection
- CSRF protection for all admin actions

#### User Interface
- Responsive admin interface with modern WordPress styling
- Tab-based settings navigation (Settings/Health)
- Real-time form validation with inline error messages
- Loading states and progress indicators
- Status badges and icons for visual feedback
- Mobile-optimized layouts and touch-friendly controls
- Accessibility features (ARIA labels, keyboard navigation)

#### Assets & Styling
- Complete CSS framework for admin and public interfaces
- Status-based color coding (normal/warning/limit_reached)
- Smooth animations and transitions
- Professional dashboard-style admin interface
- Responsive design for all screen sizes
- Dark/light theme compatibility

#### Error Handling
- Comprehensive error logging to WordPress debug log
- Request ID propagation for backend error tracing
- User-friendly error messages with technical details
- Automatic retry functionality for failed requests
- Network error detection and appropriate messaging
- Timeout handling with configurable limits

#### Performance
- Efficient data caching with automatic invalidation
- Minimal resource usage with lazy loading
- Optimized AJAX requests with proper timeout handling
- CSS/JS minification ready structure
- Database query optimization

### Technical Details

#### Requirements Met
- WordPress 5.0+ compatibility
- PHP 7.4+ compatibility with OpenSSL extension
- WordPress Coding Standards compliance
- Secure coding practices implementation
- CORS compatibility with Phase 0 hardened backend

#### Integration Points
- NGOInfo Copilot FastAPI backend API
- WordPress HTTP API for secure requests
- WordPress Options API for settings storage
- WordPress Transients API for caching
- WordPress AJAX API for dynamic functionality

#### File Structure
```
ngoinfo-copilot-wp/
├── ngoinfo-copilot.php          # Main plugin file
├── includes/                    # Core PHP classes
├── admin/views/                 # Admin HTML templates
├── public/views/                # Public HTML templates
├── assets/css/                  # Stylesheets
├── assets/js/                   # JavaScript files
├── README.md                    # Documentation
└── CHANGELOG.md                 # This file
```

### Security Considerations
- JWT secrets encrypted using WordPress salts
- All API communications use HTTPS in production
- Sensitive data automatically redacted from logs
- Complete input/output sanitization
- WordPress security best practices followed

### Known Limitations
- Manual installation required (WordPress.org repository submission planned)
- Plan tier management placeholder (full implementation in future phases)
- Basic usage widget themes (advanced customization planned)

### Development Notes
- Ready for Phase 3.2 (Profile Wizard) integration
- Ready for Phase 3.3 (Funding Picker) integration
- Extensible architecture for future enhancements
- Complete logging and debugging support
- Full WordPress multisite compatibility

---

**Note**: This is the initial release of the NGOInfo Copilot WordPress plugin, providing the foundation for secure integration with the NGOInfo Copilot backend API. Future releases will add proposal generation workflows, advanced user management, and enhanced reporting features.








