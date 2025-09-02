# Dashboard Wireflow

## User Journey: Main Hub & Overview
**Entry Point**: Post-onboarding or login  
**Goal**: Quick access to key features and status overview  
**API Endpoints**: `GET /api/usage/summary`, `GET /api/proposals/`

---

## Main Dashboard Layout

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ WordPress Admin                                    👤 Admin | 🔔 Notifications │
├─────────────────────────────────────────────────────────────────────────────────┤
│ 📊 Dashboard  📝 Generate  📋 Proposals  📤 Exports  ⚙️ Settings                │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Welcome back, Kenya Education Alliance! 🎯 Profile Complete ✅                  │
│                                                                                 │
│ ┌─── Quick Actions ────────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │     🚀 Generate New Proposal           📚 Browse Funding Opportunities      │ │
│ │                                                                             │ │
│ │     📋 View All Proposals              📤 Export History                    │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─── Usage Status ───────────────────────┐ ┌─── Recent Proposals ─────────────┐ │
│ │                                       │ │                                 │ │
│ │ 📊 Free Plan                          │ │ 📝 Climate Action in Rural Kenya │ │
│ │                                       │ │    Draft • 2 days ago           │ │
│ │ Proposals this month:                 │ │    [View] [Edit] [Export]        │ │
│ │ ████████░░ 8/10                       │ │                                 │ │
│ │                                       │ │ 📝 Water Access Project         │ │
│ │ 2 remaining                           │ │    Finalized • 1 week ago       │ │
│ │ Resets on February 1, 2025            │ │    [View] [Edit] [Export]        │ │
│ │                                       │ │                                 │ │
│ │ [View Detailed Usage]                 │ │ 📝 Education Initiative         │ │
│ │                                       │ │    Submitted • 2 weeks ago      │ │
│ └───────────────────────────────────────┘ │    [View] [Export]              │ │
│                                           │                                 │ │
│                                           │ [View All Proposals]            │ │
│                                           └─────────────────────────────────┘ │
│                                                                                 │
│ ┌─── Profile Health ─────────────────────┐ ┌─── Recent Activity ─────────────┐ │
│ │                                       │ │                                 │ │
│ │ ✅ Organization Details Complete       │ │ • Proposal exported as PDF      │ │
│ │ ✅ Project History Complete            │ │   2 hours ago                   │ │
│ │ ✅ Financial Info Complete             │ │                                 │ │
│ │                                       │ │ • New proposal generated         │ │
│ │ 💡 Suggestion: Add team member bios   │ │   Yesterday at 3:42 PM          │ │
│ │    for stronger proposals             │ │                                 │ │
│ │                                       │ │ • Profile updated               │ │
│ │ [Update Profile]                      │ │   3 days ago                    │ │
│ │                                       │ │                                 │ │
│ └───────────────────────────────────────┘ │ [View All Activity]             │ │
│                                           └─────────────────────────────────┘ │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Usage Widget States

### Normal Usage (< 80%)
```
┌─── Usage Status ───────────────────────┐
│                                       │
│ 📊 Free Plan                          │
│                                       │
│ Proposals this month:                 │
│ ████████░░ 8/10                       │
│                                       │
│ 2 remaining                           │
│ Resets on February 1, 2025            │
│                                       │
│ [View Detailed Usage]                 │
│                                       │
└───────────────────────────────────────┘
```
**API Call**: `GET /api/usage/summary`  
**Update Frequency**: Every page load + every 5 minutes

### Warning State (80-95%)
```
┌─── Usage Status ───────────────────────┐
│                                       │
│ ⚠️ Free Plan - Nearly at Limit        │
│                                       │
│ Proposals this month:                 │
│ █████████▲ 9/10                       │
│                                       │
│ 1 remaining - consider upgrading      │
│ Resets on February 1, 2025            │
│                                       │
│ [Upgrade Plan] [View Usage]           │
│                                       │
└───────────────────────────────────────┘
```

### Limit Reached (100%)
```
┌─── Usage Status ───────────────────────┐
│                                       │
│ ❌ Monthly Limit Reached               │
│                                       │
│ Proposals this month:                 │
│ ██████████ 10/10                      │
│                                       │
│ Upgrade to continue generating        │
│ Resets on February 1, 2025            │
│                                       │
│ [Upgrade Now] [View Plans]            │
│                                       │
└───────────────────────────────────────┘
```

---

## Profile Completeness States

### Complete Profile (100%)
```
┌─── Profile Health ─────────────────────┐
│                                       │
│ ✅ Organization Details Complete       │
│ ✅ Project History Complete            │
│ ✅ Financial Info Complete             │
│                                       │
│ 🎯 Your profile is optimized for      │
│    high-quality AI proposals!         │
│                                       │
│ [View Profile]                        │
│                                       │
└───────────────────────────────────────┘
```

### Incomplete Profile (< 100%)
```
┌─── Profile Health ─────────────────────┐
│                                       │
│ ✅ Organization Details Complete       │
│ ⚠️ Project History Needs Attention     │
│ ❌ Financial Info Missing              │
│                                       │
│ 📈 Complete your profile for better   │
│    proposal quality (currently 60%)   │
│                                       │
│ [Complete Profile]                    │
│                                       │
└───────────────────────────────────────┘
```

---

## Recent Proposals Widget

### With Proposals
```
┌─── Recent Proposals ─────────────────┐
│                                     │
│ 📝 Climate Action in Rural Kenya     │
│    Draft • 2 days ago               │
│    [View] [Edit] [Export]            │
│                                     │
│ 📝 Water Access Project             │
│    Finalized • 1 week ago           │
│    [View] [Edit] [Export]            │
│                                     │
│ 📝 Education Initiative             │
│    Submitted • 2 weeks ago          │
│    [View] [Export]                  │
│                                     │
│ 📝 Healthcare Outreach              │
│    Draft • 3 weeks ago              │
│    [View] [Edit] [Export]            │
│                                     │
│ [View All Proposals]                │
│                                     │
└─────────────────────────────────────┘
```

### Empty State
```
┌─── Recent Proposals ─────────────────┐
│                                     │
│         📝                          │
│                                     │
│    You haven't created any          │
│    proposals yet.                   │
│                                     │
│    Generate your first AI-powered   │
│    proposal to get started!         │
│                                     │
│    [Generate New Proposal]          │
│                                     │
└─────────────────────────────────────┘
```

---

## Error States

### API Connection Error
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚠️ Connection Issue                                              │
│                                                                 │
│ Unable to load usage data (Request ID: abc123). Some features  │
│ may be limited. Please refresh or check your connection.       │
│                                                                 │
│                          [Refresh Page] [Contact Support]      │
└─────────────────────────────────────────────────────────────────┘
```

### Profile Load Error
```
┌─── Profile Health ─────────────────────┐
│                                       │
│ ❌ Couldn't load profile data          │
│                                       │
│ Request ID: xyz789                    │
│                                       │
│ Please refresh the page or contact    │
│ support if this continues.            │
│                                       │
│ [Refresh] [Contact Support]           │
│                                       │
└───────────────────────────────────────┘
```

---

## Loading States

### Dashboard Loading
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ Welcome back! Loading your dashboard...                    [Loading spinner]    │
│                                                                                 │
│ ┌─── Quick Actions ────────────────────────────────────────────────────────────┐ │
│ │ [Skeleton buttons layout]                                                  │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─── Usage Status ───────────────────────┐ ┌─── Recent Proposals ─────────────┐ │
│ │ ████ Loading usage data...            │ │ ████ Loading proposals...        │ │
│ │ ████                                  │ │ ████                             │ │
│ │ ████                                  │ │ ████                             │ │
│ └───────────────────────────────────────┘ └─────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Individual Widget Loading
```
┌─── Usage Status ───────────────────────┐
│                                       │
│ 📊 Free Plan                          │
│                                       │
│ Loading usage data... ⏳              │
│ ████████████████                      │
│                                       │
│ Please wait...                        │
│                                       │
└───────────────────────────────────────┘
```

---

## Navigation Flow

### From Dashboard Actions
```
Dashboard Actions:
├── Generate New Proposal → Funding Picker
├── Browse Funding → Funding Picker (Browse tab)
├── View All Proposals → Proposals List
├── Export History → Exports Panel
├── Complete Profile → Profile Editor
├── Update Profile → Profile Editor
├── Upgrade Plan → External billing page
└── Settings → Settings panel
```

### API Call Triggers
- **Page Load**: 
  - `GET /api/usage/summary`
  - `GET /api/proposals/?limit=5&status=recent`
  - `GET /api/profile` (for profile health check)
- **Auto-refresh**: Usage data every 5 minutes
- **Manual refresh**: User clicks refresh buttons

---

## Mobile Responsive Layout

### Mobile Dashboard (< 768px)
```
┌───────────────────────────────────────┐
│ ☰ Menu    NGO Copilot    🔔 Profile   │
├───────────────────────────────────────┤
│                                       │
│ Welcome back!                         │
│ Kenya Education Alliance              │
│ 🎯 Profile Complete ✅                │
│                                       │
│ ┌─ Quick Actions ──────────────────┐   │
│ │ 🚀 Generate New Proposal         │   │
│ │ 📚 Browse Opportunities          │   │
│ │ 📋 View Proposals                │   │
│ │ 📤 Exports                       │   │
│ └───────────────────────────────────┘   │
│                                       │
│ ┌─ Usage: 8/10 Free Plan ─────────┐   │
│ │ ████████░░                      │   │
│ │ 2 remaining • Resets Feb 1      │   │
│ │ [View Details]                  │   │
│ └───────────────────────────────────┘   │
│                                       │
│ ┌─ Recent Proposals ──────────────┐   │
│ │ Climate Action in Kenya         │   │
│ │ Draft • 2 days ago              │   │
│ │ [View] [Edit]                   │   │
│ │                                 │   │
│ │ Water Access Project            │   │
│ │ Finalized • 1 week ago          │   │
│ │ [View] [Edit]                   │   │
│ │                                 │   │
│ │ [View All]                      │   │
│ └───────────────────────────────────┘   │
│                                       │
└───────────────────────────────────────┘
```

### Tablet Layout (768px - 1024px)
- Two-column grid for widgets
- Compact navigation bar
- Touch-friendly button sizes
- Collapsible sidebar for secondary actions

