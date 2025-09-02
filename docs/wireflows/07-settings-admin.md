# Settings (Admin) Wireflow

## User Journey: Administrative Configuration & Monitoring
**Entry Point**: Dashboard → Settings OR WordPress Admin → NGO Copilot Settings  
**Goal**: Configure API connection, monitor health, manage users, and control features  
**API Endpoints**: `GET /healthcheck`, user management, feature flags

---

## Main Settings Interface

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ ← Back to Dashboard                  NGO Copilot - Settings                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Configure your NGOInfo Copilot integration                                     │
│                                                                                 │
│ ┌─ Settings Tabs ────────────────────────────────────────────────────────────┐ │
│ │ ● API Config │ Health Panel │ User Management │ Feature Flags │ Cache Mgmt │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ API CONFIGURATION ────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Backend URL *                                                               │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ https://api.ngoinfo.org                                                 │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │ ℹ️ Must be HTTPS URL for production environments                            │ │
│ │                                                                             │ │
│ │ API Key * [👁️ Show]                                                         │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ ●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●                     │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │ Status: ✅ Valid (Last verified: 2 minutes ago)                             │ │
│ │                                                                             │ │
│ │ Environment                                                                 │ │
│ │ ●Production   ○Staging   ○Development                                       │ │
│ │                                                                             │ │
│ │ Timeout Settings                                                            │ │
│ │ Request Timeout: ████████████████████████████████░░░░ 120 seconds          │ │
│ │ Retry Attempts:  ████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 3 attempts            │ │
│ │                                                                             │ │
│ │ Connection Status                                                           │ │
│ │ Last Test: January 9, 2025 at 6:47 PM                                      │ │
│ │ Response Time: 245ms ✅                                                     │ │
│ │ Status: 🟢 Healthy                                                          │ │
│ │                                                                             │ │
│ │                                  [Test Connection]  [Reset to Defaults]     │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│                                          [Save Configuration]  [Cancel Changes] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Health Panel Tab

```
┌─ HEALTH PANEL ─────────────────────────────────────────────────────────────────┐
│                                                                                │
│ Real-time API monitoring and system health                                    │
│                                                                                │
│ ┌─ Current Status ───────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ 🟢 API Status: Healthy                    Last Check: 30 seconds ago        │ │
│ │ 🟢 Database: Connected                    Response Time: 245ms              │ │
│ │ 🟢 Authentication: Working               Service Version: 1.0.0             │ │
│ │                                                                             │ │
│ │ Performance Metrics (Last 24 Hours)                                        │ │
│ │ • Average Response Time: 312ms                                             │ │
│ │ • Uptime: 99.8%                                                            │ │
│ │ • Total Requests: 1,247                                                    │ │
│ │ • Error Rate: 0.2%                                                         │ │
│ │                                                                             │ │
│ │                                                     [Refresh Now] [Details] │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Recent Activity Log ──────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ ✅ 6:47 PM - Health check successful (245ms)                                │ │
│ │ ✅ 6:46 PM - Proposal generated successfully                               │ │
│ │ ✅ 6:45 PM - Export created (PDF, 2.3MB)                                   │ │
│ │ ⚠️ 6:42 PM - Slow response detected (1,250ms)                               │ │
│ │ ✅ 6:40 PM - User profile updated                                           │ │
│ │ ✅ 6:38 PM - Authentication successful                                      │ │
│ │ ❌ 6:35 PM - Rate limit exceeded (429) - Request ID: abc123                │ │
│ │ ✅ 6:33 PM - Proposal validation completed                                  │ │
│ │                                                                             │ │
│ │                                                          [View Full Log]    │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Error Tracking ───────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Recent Errors (Last 7 Days)                                                │ │
│ │                                                                             │ │
│ │ January 9, 6:35 PM - Rate Limit Exceeded                                   │ │
│ │ Request ID: abc123def456 | Status: Resolved                                │ │
│ │                                                                             │ │
│ │ January 8, 2:15 PM - Export Generation Failed                              │ │
│ │ Request ID: xyz789abc123 | Status: Resolved                                │ │
│ │                                                                             │ │
│ │ January 7, 4:22 PM - Database Connection Timeout                           │ │
│ │ Request ID: def456ghi789 | Status: Resolved                                │ │
│ │                                                                             │ │
│ │                                                     [View All Errors]      │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ Auto-refresh: ●On ○Off (every 30 seconds)        [Manual Refresh] [Export Log] │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## User Management Tab

```
┌─ USER MANAGEMENT ──────────────────────────────────────────────────────────────┐
│                                                                                │
│ Manage user access and monitor usage                                          │
│                                                                                │
│ ┌─ Current User Info ────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Organization: Kenya Education Alliance                                      │ │
│ │ WordPress User: admin (John Doe)                                           │ │
│ │ User Role: Administrator                                                    │ │
│ │ Plan Type: Free Plan (10 proposals/month)                                  │ │
│ │ Account Created: December 15, 2024                                         │ │
│ │ Last Login: January 9, 2025 at 6:30 PM                                     │ │
│ │                                                                             │ │
│ │                                              [Edit Profile] [Change Plan]   │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Impersonation Panel (Admin Only) ─────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ ⚠️ Admin Feature: View plugin as another user for support/testing           │ │
│ │                                                                             │ │
│ │ Search User                                                                 │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ Start typing username... [Search suggestions dropdown]                  │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │ ┌─ Selected User: sarah_manager ─────────────────────────────────────────┐ │ │
│ │ │ Name: Sarah Manager                                                     │ │ │
│ │ │ Role: Editor                                                            │ │ │
│ │ │ Last Active: January 8, 2025                                            │ │ │
│ │ │ Proposals: 3/10 this month                                              │ │ │
│ │ │                                                                         │ │ │
│ │ │                                               [Impersonate User]        │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │ Currently Impersonating: None                                               │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Usage Analytics ──────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Organization Usage (Last 30 Days)                                          │ │
│ │                                                                             │ │
│ │ Total API Calls: 1,247                                                     │ │
│ │ Proposals Generated: 8                                                     │ │
│ │ Exports Created: 15                                                        │ │
│ │ Profile Updates: 3                                                         │ │
│ │                                                                             │ │
│ │ Top Users by Activity:                                                      │ │
│ │ 1. admin (John Doe) - 742 calls                                            │ │
│ │ 2. sarah_manager - 312 calls                                               │ │
│ │ 3. project_lead - 193 calls                                                │ │
│ │                                                                             │ │
│ │ Error Rate by User:                                                         │ │
│ │ • admin: 0.1% (1 error)                                                    │ │
│ │ • sarah_manager: 0.3% (1 error)                                            │ │
│ │ • project_lead: 0% (0 errors)                                              │ │
│ │                                                                             │ │
│ │                                                        [Detailed Report]    │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Feature Flags Tab

```
┌─ FEATURE FLAGS ────────────────────────────────────────────────────────────────┐
│                                                                                │
│ Enable or disable experimental and advanced features                          │
│                                                                                │
│ ┌─ Core Features ────────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ ☑️ Beta Features                                                            │ │
│ │    Enable experimental features before they're officially released         │ │
│ │    ⚠️ May be unstable. Use with caution in production.                     │ │
│ │                                                                             │ │
│ │ ☑️ Advanced Editor                                                          │ │
│ │    Enable additional editing tools and AI enhancement features             │ │
│ │    Includes: Section templates, Q&A pack, advanced formatting              │ │
│ │                                                                             │ │
│ │ ☐ Export Branding                                                          │ │
│ │    Allow custom logos and branding in exported documents                   │ │
│ │    🔒 Requires Pro plan or higher                                          │ │
│ │                                                                             │ │
│ │ ☑️ Collaboration                                                            │ │
│ │    Enable multi-user editing, comments, and version control               │ │
│ │    Includes: Real-time collaboration, review mode, comment system          │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Development Features ─────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ ☐ Debug Mode                                                               │ │
│ │    Show detailed error messages and request IDs in user interface         │ │
│ │    ⚠️ Only enable for troubleshooting. Disable in production.              │ │
│ │                                                                             │ │
│ │ ☐ Verbose Logging                                                          │ │
│ │    Log additional information for debugging and monitoring                 │ │
│ │    ⚠️ May impact performance. Use only when needed.                        │ │
│ │                                                                             │ │
│ │ ☐ API Test Mode                                                            │ │
│ │    Use sandbox/test endpoints instead of production API                    │ │
│ │    ⚠️ Generated proposals will be test data only.                          │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Feature Dependencies ─────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ ⚠️ Dependencies Notice:                                                     │ │
│ │ • "Export Branding" requires a Pro plan subscription                       │ │
│ │ • "Collaboration" needs WordPress user management enabled                  │ │
│ │ • "Debug Mode" should not be used with caching enabled                     │ │
│ │                                                                             │ │
│ │ Current Conflicts: None ✅                                                  │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│                                                [Apply Changes] [Reset to Defaults] │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Cache Management Tab

```
┌─ CACHE MANAGEMENT ─────────────────────────────────────────────────────────────┐
│                                                                                │
│ Manage cached data and optimize performance                                   │
│                                                                                │
│ ┌─ Cache Statistics ─────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Cache Performance (Last 24 Hours)                                          │ │
│ │                                                                             │ │
│ │ Hit Rate: ████████████████████████████████████████░░░░ 87%                 │ │
│ │ Total Requests: 1,247                                                      │ │
│ │ Cache Hits: 1,085                                                          │ │
│ │ Cache Misses: 162                                                          │ │
│ │                                                                             │ │
│ │ Storage Used: 47MB of 100MB limit                                          │ │
│ │ ████████████████████████████████████████████████░░░░░░░░░░░░░░░░░░░░░░░░     │ │
│ │                                                                             │ │
│ │ Last Cleared: January 8, 2025 at 2:30 PM                                   │ │
│ │ Auto-cleanup: Enabled (removes entries older than 5 minutes)               │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Cache Categories ─────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Profile Data Cache                                                          │ │
│ │ Size: 12MB • Entries: 1 • Last Update: 2 hours ago                         │ │
│ │ Your organization profile and settings                                      │ │
│ │                                              [Clear Profile Cache]         │ │
│ │                                                                             │ │
│ │ Proposals Cache                                                             │ │
│ │ Size: 28MB • Entries: 8 • Last Update: 15 minutes ago                      │ │
│ │ Cached proposal content and metadata                                       │ │
│ │                                             [Clear Proposals Cache]        │ │
│ │                                                                             │ │
│ │ API Response Cache                                                          │ │
│ │ Size: 5MB • Entries: 47 • Last Update: 30 seconds ago                      │ │
│ │ Cached API responses for funding opportunities                             │ │
│ │                                               [Clear API Cache]            │ │
│ │                                                                             │ │
│ │ Export Files Cache                                                          │ │
│ │ Size: 2MB • Entries: 3 • Last Update: 1 hour ago                          │ │
│ │ Temporary storage for generated export files                               │ │
│ │                                              [Clear Export Cache]          │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Maintenance Actions ──────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ [Clear All Cache]           Remove all cached data                         │ │
│ │ [Refresh API Tokens]        Re-authenticate with backend                   │ │
│ │ [Reset to Defaults]         Clear cache and reset all settings             │ │
│ │ [Export Cache Report]       Download detailed cache analysis               │ │
│ │                                                                             │ │
│ │ ⚠️ Note: Clearing cache may temporarily slow down the plugin while         │ │
│ │   fresh data is loaded from the API.                                       │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Error States

### Connection Test Failed
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               ❌ Connection Test Failed                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Unable to connect to the backend API                                           │
│                                                                                 │
│ Error Details:                                                                 │
│ • HTTP Status: 403 Forbidden                                                  │
│ • Response: Invalid API key                                                   │
│ • Request ID: abc123def456                                                     │
│                                                                                 │
│ Possible Solutions:                                                            │
│ • Check that your API key is correct                                          │
│ • Ensure the backend URL is accessible                                        │
│ • Verify your network connectivity                                            │
│ • Contact support if the problem persists                                     │
│                                                                                 │
│                                  [Retry Test]  [Check API Key]  [Contact Support] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Invalid API Key
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               🔑 API Key Authentication Failed                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ The provided API key is invalid or has expired.                               │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│ To resolve this issue:                                                         │
│ 1. Check that you've copied the API key correctly                             │
│ 2. Ensure there are no extra spaces or characters                             │
│ 3. Verify the key hasn't expired                                              │
│ 4. Generate a new API key if needed                                           │
│                                                                                 │
│                             [Generate New Key]  [Retry Validation]  [Get Help] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Settings Save Failed
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               ⚠️ Settings Save Failed                            │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Your settings couldn't be saved right now.                                    │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│ Your changes are preserved in the form. Please try saving again or            │
│ contact support if the problem continues.                                     │
│                                                                                 │
│ Possible causes:                                                               │
│ • Temporary database issue                                                     │
│ • Permission problem                                                           │
│ • Network connectivity issue                                                   │
│                                                                                 │
│                                          [Retry Save]  [Reset Form]  [Get Help] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Loading States

### Connection Testing
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              🔄 Testing Connection                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Testing connection to: https://api.ngoinfo.org                                 │
│                                                                                 │
│ ████████████████████████████████████████████████████████████████                │
│                                                                                 │
│ ⏳ Verifying API endpoint and authentication...                                  │
│                                                                                 │
│ This may take a few seconds depending on your connection.                      │
│                                                                                 │
│                                                        [Cancel Test]           │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Health Panel Loading
```
┌─ HEALTH PANEL ─────────────────────────────────────────────────────────────────┐
│                                                                                │
│ Loading system health data... ⏳                                                │
│                                                                                │
│ ┌─ Current Status ───────────────────────────────────────────────────────────┐ │
│ │ ████████████████████████████████████████████████████████████████████████   │ │
│ │ ████████████████████████████████████████████████████████████████████████   │ │
│ │ ████████████████████████████████████████████████████████████████████████   │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ Fetching latest monitoring data from the backend...                           │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

### Settings Saving
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              💾 Saving Settings                                │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Saving your configuration changes...                                           │
│                                                                                 │
│ ████████████████████████████████████████████████████████████████                │
│                                                                                 │
│ ⏳ Updating API configuration and validating settings...                        │
│                                                                                 │
│ Please don't close this page while saving.                                    │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Success States

### Connection Test Successful
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ✅ Connection Test Successful                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ API connection is working perfectly!                                           │
│                                                                                 │
│ Connection Details:                                                            │
│ • Response Time: 245ms ✅                                                       │
│ • API Status: Healthy                                                         │
│ • Database: Connected                                                          │
│ • Authentication: Valid                                                        │
│ • Service Version: 1.0.0                                                      │
│                                                                                 │
│ Your NGOInfo Copilot plugin is ready to use.                                  │
│                                                                                 │
│                                                        [OK]  [Save Settings]   │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Settings Saved Successfully
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ✅ Settings Saved Successfully                      │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Your configuration has been updated and is now active.                        │
│                                                                                 │
│ Changes Applied:                                                               │
│ • API configuration updated                                                    │
│ • Feature flags applied                                                        │
│ • Cache settings optimized                                                     │
│                                                                                 │
│ All changes take effect immediately.                                           │
│                                                                                 │
│                                                   [OK]  [View Health Panel]    │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Cache Cleared Successfully
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ✅ Cache Cleared Successfully                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ All cached data has been removed.                                             │
│                                                                                 │
│ Cleared:                                                                       │
│ • Profile data cache (12MB)                                                   │
│ • Proposals cache (28MB)                                                      │
│ • API response cache (5MB)                                                    │
│ • Export files cache (2MB)                                                    │
│                                                                                 │
│ Fresh data will be loaded from the API on next use.                           │
│                                                                                 │
│                                                        [OK]  [Back to Settings] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Impersonation Active State

### Currently Impersonating User
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ ⚠️ IMPERSONATION ACTIVE                                                         │
│                                                                                 │
│ You are currently viewing as: Sarah Manager (sarah_manager)                    │
│                                                                                 │
│ All actions will be performed as this user. This affects:                     │
│ • Proposal generation and editing                                              │
│ • Usage limits and quota                                                       │
│ • Profile information                                                          │
│ • Export permissions                                                            │
│                                                                                 │
│                                                      [Stop Impersonation]      │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Navigation Flow

### From Settings Panel
```
Settings Actions:
├── API Configuration → Test connection → Save/Error handling
├── Health Panel → Real-time monitoring → Error logs
├── User Management → Usage analytics → Impersonation
├── Feature Flags → Enable/disable features → Dependency checks  
├── Cache Management → Clear cache → Performance optimization
├── Save Configuration → Validation → Success/Error
├── Test Connection → Health check → Status display
└── Reset to Defaults → Confirmation → Fresh configuration
```

### API Calls Triggered
- **Health Check**: `GET /healthcheck` (real-time monitoring)
- **Connection Test**: `GET /healthcheck` + authentication test
- **User Analytics**: Local WordPress user data + API usage logs
- **Settings Save**: WordPress options table updates
- **Cache Operations**: Local cache management (no API calls)

---

## Mobile Responsive Layout

### Mobile Settings (< 768px)
```
┌─────────────────────────────────────────┐
│ ← Back     Settings                     │
├─────────────────────────────────────────┤
│                                         │
│ ┌─ Tabs ─────────────────────────────────┐ │
│ │ ● API  Health  Users  Flags  Cache    │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ API Configuration                       │
│                                         │
│ Backend URL *                           │
│ ┌─────────────────────────────────────┐ │
│ │ https://api.ngoinfo.org             │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ API Key * [👁️]                          │
│ ┌─────────────────────────────────────┐ │
│ │ ●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●● │ │
│ └─────────────────────────────────────┘ │
│ Status: ✅ Valid                        │
│                                         │
│ Environment                             │
│ ●Production ○Staging ○Development       │
│                                         │
│ [Test Connection]                       │
│                                         │
│ [Save Configuration]                    │
│                                         │
└─────────────────────────────────────────┘
```

### Mobile Health Panel (< 768px)
```
┌─────────────────────────────────────────┐
│ Health Panel                            │
├─────────────────────────────────────────┤
│                                         │
│ Current Status                          │
│ 🟢 API: Healthy                         │
│ 🟢 Database: Connected                  │
│ 🟢 Auth: Working                        │
│                                         │
│ Response Time: 245ms ✅                 │
│ Last Check: 30 sec ago                  │
│                                         │
│ [Refresh] [Full Details]                │
│                                         │
│ ── Recent Activity ──                   │
│                                         │
│ ✅ 6:47 PM - Health check OK            │
│ ✅ 6:46 PM - Proposal generated         │
│ ⚠️ 6:42 PM - Slow response (1.2s)       │
│ ❌ 6:35 PM - Rate limit (429)           │
│                                         │
│ [View Full Log]                         │
│                                         │
└─────────────────────────────────────────┘
```


