# NGOInfo Copilot WordPress Plugin - UX Design Specification

**Version:** 1.0  
**Date:** January 2025  
**Phase:** 2 (Design Documentation)  
**Target:** WordPress Plugin MVP Integration with FastAPI Backend

---

## 📋 **Overview**

This document specifies the complete user experience design for the NGOInfo Copilot WordPress plugin. The plugin integrates with the hardened FastAPI backend (Phase 0-1 complete) to provide AI-powered proposal generation services for NGOs directly within their WordPress admin dashboard.

### **Design Principles**
- **Progressive Disclosure**: Complex features revealed as users advance
- **Contextual Help**: Guidance appears when and where needed
- **Error Recovery**: Clear paths to resolve issues with request IDs for support
- **Accessibility First**: WCAG 2.1 AA compliance throughout
- **Performance**: Minimal API calls with intelligent caching

---

## 🎯 **Plugin Architecture & Navigation**

### **Main Menu Structure**
```
WordPress Admin Menu:
├── NGO Copilot (main)
│   ├── Dashboard
│   ├── Generate Proposal
│   ├── My Proposals
│   ├── Exports
│   └── Settings
```

### **User Journey Flow**
1. **First Time**: Onboarding Wizard → Profile Setup → Dashboard
2. **Regular Use**: Dashboard → Funding Picker → Generate → Editor → Export
3. **Admin**: Settings → API Health → User Management

---

# 📱 **Screen Specifications**

## 1. **Onboarding Wizard**

### **Purpose**
Guide new users through initial setup to create a complete NGO profile for optimal AI proposal generation.

### **Steps Overview**
- **Step 1**: Organization Basics (2 minutes)
- **Step 2**: Projects & Focus Areas (3 minutes)  
- **Step 3**: Operations & Financials (2 minutes)

---

### **Step 1: Organization Basics**

#### **Fields**
- **Organization Name*** (text, 200 chars)
- **Mission Statement*** (textarea, 1000 chars)
- **Founded Year** (number, 1800-current year)
- **Organization Type*** (select: NGO, Nonprofit, Foundation, Social Enterprise, Other)
- **Website URL** (url validation)
- **Registration Number** (text, 50 chars)

#### **Validation Rules**
- Required fields marked with *
- Mission statement minimum 50 characters
- Website URL format validation
- Real-time character count displays

#### **Actions**
- **Continue** (validates required fields, advances to Step 2)
- **Skip for Now** (advances with incomplete profile warning)

#### **Error States**
- Empty required fields: Red outline + "This field is required"
- Invalid URL: "Please enter a valid website URL"
- Mission too short: "Mission statement needs at least 50 characters to help our AI understand your organization"

#### **Success State**
- Green checkmarks appear next to completed fields
- Progress bar shows 33% complete

#### **Loading State**
- Continue button shows spinner + "Saving your organization details..."

#### **Microcopy**
- **Header**: "Tell us about your organization"
- **Subheader**: "This helps our AI create proposals that perfectly match your mission and goals"
- **Mission Help**: "Describe what your organization does and the impact you aim to achieve. Be specific about your focus areas."

---

### **Step 2: Projects & Focus Areas**

#### **Fields**
- **Primary Sectors*** (multi-select checkboxes: Education, Health, Environment, Human Rights, Economic Development, Emergency Relief, Other)
- **Geographic Focus*** (multi-select: Local, National, Regional, Global + country/region picker)
- **Target Beneficiaries*** (checkboxes: Children, Women, Elderly, Refugees, Rural Communities, Urban Poor, Other)
- **Past Projects Description*** (textarea, 1500 chars)
- **Key Achievements** (textarea, 1000 chars)

#### **Validation Rules**
- At least 1 sector required
- At least 1 geographic area required
- Past projects minimum 100 characters
- Maximum 3 primary sectors for focus

#### **Actions**
- **Back** (returns to Step 1, preserves data)
- **Continue** (validates + advances to Step 3)
- **Skip for Now** (advances with warning)

#### **Error States**
- No sectors selected: "Please select at least one sector where your organization works"
- Geographic focus empty: "Please specify where your organization operates"
- Past projects too short: "Please describe at least one past project (minimum 100 characters)"
- Too many sectors: "Please select up to 3 primary sectors for better proposal targeting"

#### **Success State**
- Selected items highlighted in green
- Progress bar shows 66% complete

#### **Microcopy**
- **Header**: "What areas do you focus on?"
- **Subheader**: "This helps us find the most relevant funding opportunities for you"
- **Past Projects Help**: "Describe 1-3 recent projects with outcomes, beneficiaries reached, and impact achieved. This strengthens your proposals."

---

### **Step 3: Operations & Financials**

#### **Fields**
- **Annual Budget Range*** (select: <$10K, $10K-50K, $50K-250K, $250K-1M, $1M-5M, >$5M)
- **Staff Size*** (select: 1-5, 6-20, 21-50, 51-200, >200)
- **Funding Sources** (checkboxes: Government Grants, Private Foundations, Corporate Donations, Individual Donors, Earned Revenue, Other)
- **Grant Writing Experience*** (select: Beginner, Intermediate, Advanced)
- **Typical Proposal Value Range** (select: <$5K, $5K-25K, $25K-100K, $100K-500K, >$500K)

#### **Validation Rules**
- All required fields must be selected
- At least 1 funding source recommended

#### **Actions**
- **Back** (returns to Step 2)
- **Complete Setup** (saves profile, calls `/api/profile` POST, redirects to Dashboard)
- **Save as Draft** (saves locally, can complete later)

#### **Error States**
- Missing required selections: "Please select your [field name] to help us customize your experience"
- API error: "We couldn't save your profile right now (Request ID: {request_id}). Please try again or contact support."

#### **Success State**
- Completion animation: "✓ Profile Created Successfully!"
- Progress bar shows 100%
- Redirect countdown: "Taking you to your dashboard in 3... 2... 1..."

#### **Loading State**
- Complete Setup button: Spinner + "Creating your profile..."

#### **Microcopy**
- **Header**: "Tell us about your operations"
- **Subheader**: "This final step helps us suggest the right funding amounts and opportunities"
- **Completion Message**: "Great! Your profile is ready. You can now generate professional proposals tailored to your organization."

---

## 2. **Dashboard**

### **Purpose**
Central hub showing profile status, usage metrics, recent activity, and quick access to key features.

### **Layout Sections**

#### **Header Bar**
- Welcome message: "Welcome back, [Organization Name]"
- Profile completeness badge (see below)
- Quick action: "Generate New Proposal" button

#### **Profile Completeness Badge**
- **100%**: Green badge "Profile Complete ✓"
- **75-99%**: Yellow badge "Profile Nearly Complete" + "Complete Profile" link
- **50-74%**: Orange badge "Profile Needs Attention" + "Update Profile" link
- **<50%**: Red badge "Profile Incomplete" + "Complete Setup" link

#### **Usage Widget**
Displays data from `GET /api/usage/summary`

**Fields Displayed:**
- Plan name (e.g., "Free Plan", "Pro Plan")
- Usage bar: "[used]/[monthly_limit] proposals this month"
- Visual progress bar (green if <80%, yellow if 80-95%, red if >95%)
- Reset date: "Resets on [reset_at formatted date]"
- Remaining count: "[remaining] proposals remaining"

**States:**
- **Normal**: Green progress, "You have X proposals remaining"
- **Warning**: Yellow progress, "You have X proposals remaining - consider upgrading"
- **Limit Reached**: Red progress, "Monthly limit reached - upgrade to continue" + "Upgrade Plan" button

#### **Recent Proposals Widget**
- List of last 5 proposals with:
  - Title (truncated to 60 chars)
  - Creation date (relative: "2 days ago")
  - Status badge (Draft, In Review, Finalized, Submitted)
  - Quick actions: View, Edit, Export

#### **Quick Actions Panel**
- **Generate New Proposal** (primary button)
- **Browse Funding Opportunities** 
- **View All Proposals**
- **Export History**

### **Error States**
- **API Unavailable**: "Unable to load usage data (Request ID: {request_id}). Some features may be limited."
- **Profile Load Error**: "Couldn't load your profile. Please refresh or contact support (Request ID: {request_id})"

### **Loading States**
- Usage widget: Skeleton loader for bars and numbers
- Recent proposals: List skeleton with placeholder rows

### **Microcopy**
- **Empty Proposals**: "You haven't created any proposals yet. Generate your first AI-powered proposal to get started!"
- **Usage Help**: "Your monthly proposal limit resets automatically. Need more? Upgrade your plan anytime."

---

## 3. **Funding Picker**

### **Purpose**
Allow users to select funding opportunities or create custom proposals through two distinct paths.

### **Main Options (Tabs)**

#### **Tab 1: Browse Opportunities**
Connected to ReqAgent funding database via backend.

**Fields:**
- Search bar (text search in titles/descriptions)
- Filters sidebar:
  - Sector (matches user profile)
  - Amount range (sliders)
  - Deadline (upcoming 30/60/90 days)
  - Geographic focus
  - Organization type eligibility

**Opportunity Cards Display:**
- Funding organization name
- Opportunity title
- Amount range ($X - $Y)
- Deadline (highlighted if <30 days)
- Match score (0-100% based on profile alignment)
- "Generate Proposal" button

**Actions:**
- **Filter/Search** (updates results via backend)
- **Generate Proposal** (proceeds to generation with `funding_opportunity_id`)
- **Save for Later** (bookmarks opportunity)

#### **Tab 2: Custom Brief**
For opportunities not in database or custom proposals.

**Fields:**
- **Funding Source URL** (optional, for reference)
- **Custom Brief*** (textarea, 5000 chars)
  - Placeholder: "Describe the funding opportunity, requirements, focus areas, and any specific guidelines..."
- **Quick Fields Section** (optional, for structured data):
  - Grant amount ($)
  - Application deadline
  - Focus areas (tags)
  - Eligibility criteria
  - Special requirements

**Actions:**
- **Generate from Brief** (calls API with `custom_brief`)
- **Generate from Quick Fields** (calls API with `quick_fields`)
- **Save as Template** (saves for reuse)

### **Validation Rules**
- Custom brief minimum 200 characters
- Cannot use both brief and quick fields simultaneously (backend enforces this)
- URL validation if provided

### **Error States**
- **No Results**: "No funding opportunities match your criteria. Try adjusting your filters or use the Custom Brief option."
- **Backend Error**: "Couldn't load opportunities right now (Request ID: {request_id}). Try refreshing or use Custom Brief instead."
- **Validation Error**: "Please provide either a custom brief OR quick fields, not both"

### **Success State**
- Opportunity selected: "Selected: [Opportunity Title] - Ready to generate proposal"
- Custom brief ready: "Custom brief prepared - Ready to generate proposal"

### **Microcopy**
- **Browse Tab**: "Find funding opportunities that match your organization's profile and mission"
- **Custom Tab**: "Don't see the right opportunity? Describe any funding source and we'll create a tailored proposal"
- **Match Score Help**: "This score shows how well the opportunity aligns with your organization's profile"

---

## 4. **Generate Proposal**

### **Purpose**
Configure and initiate AI proposal generation with progress tracking and error recovery.

### **Configuration Panel**

#### **Selected Opportunity Display**
- Opportunity/brief summary
- Estimated completion time: "~2-3 minutes"
- "Change Selection" link (returns to Funding Picker)

#### **Generation Options**
- **Tone** (radio buttons):
  - Professional (default)
  - Conversational
  - Academic
  - Persuasive
- **Length** (radio buttons):
  - Standard (3-5 pages)
  - Detailed (6-8 pages)
  - Comprehensive (9-12 pages)
- **Custom Instructions** (textarea, 2000 chars)
  - Placeholder: "Any specific requirements, emphasis areas, or unique aspects to highlight..."

#### **Advanced Options** (collapsible)
- **Idempotency Key** (auto-generated, editable)
  - Help text: "This prevents duplicate proposals if you retry. Change only if creating a different version."
- **Save as Template** (checkbox)
- **Email Notification** (checkbox)

### **Actions**
- **Generate Proposal** (primary button, calls `/api/proposals/generate`)
- **Save Draft** (saves configuration without generating)

### **Generation Progress States**

#### **State 1: Initiating (0-10%)**
- Progress bar with animation
- Message: "Preparing your proposal request..."
- Estimated time: "2-3 minutes remaining"

#### **State 2: Analyzing (10-40%)**
- Message: "Analyzing your organization and funding opportunity..."
- Estimated time: "1-2 minutes remaining"

#### **State 3: Generating (40-90%)**
- Message: "AI is crafting your proposal content..."
- Estimated time: "30 seconds remaining"

#### **State 4: Finalizing (90-100%)**
- Message: "Adding final touches and scoring..."
- Estimated time: "Almost done..."

### **Error States**

#### **Validation Error (422)**
- Message: "Please check your input: [error details from backend]"
- Request ID displayed: "Need help? Reference ID: {request_id}"
- "Fix and Retry" button

#### **Rate Limit Error (429)**
- Message: "You've reached the generation limit (5 per minute). Please wait before trying again."
- Countdown timer: "Try again in: 0:45"
- Request ID: "Reference ID: {request_id}"

#### **Server Error (500)**
- Message: "Something went wrong during generation. Don't worry - your request wasn't counted against your limit."
- Request ID: "Reference ID: {request_id}"
- "Retry Generation" button
- "Contact Support" link

#### **Network Error**
- Message: "Connection lost during generation. Checking if your proposal was created..."
- Auto-retry logic with idempotency key
- Manual "Check Status" button

### **Success State**
- Progress: 100% with green checkmark
- Message: "Proposal generated successfully!"
- Preview card with title, word count, confidence score
- "Edit Proposal" button (primary)
- "Generate Another" button (secondary)

### **Loading State Recovery**
- If user refreshes/leaves during generation:
  - Check for existing proposal with same idempotency key
  - Resume if found, restart if not
  - Message: "Checking if your proposal was already created..."

### **Microcopy**
- **Start**: "Ready to create your AI-powered proposal? This typically takes 2-3 minutes."
- **Progress**: "Our AI is analyzing thousands of successful proposals to craft yours..."
- **Error Recovery**: "Don't worry - issues like this are rare. Try again or contact our support team."

---

## 5. **Proposal Editor**

### **Purpose**
Comprehensive editing interface with section-based tabs, AI assistance, and validation feedback.

### **Editor Layout**

#### **Top Bar**
- Proposal title (editable inline)
- Status indicator (Draft, In Review, Finalized)
- Auto-save indicator: "Last saved: 2 minutes ago"
- Actions: Save Draft, Finalize, Export, Share

#### **Tab Navigation**
Each tab shows validation badge:
- ✅ **Executive Summary** (complete)
- ⚠️ **Project Description** (needs attention)
- ❌ **Budget** (missing required info)
- ✅ **Timeline** (complete)
- ✅ **Impact** (complete)
- ⚠️ **Organization** (could be improved)

### **Tab Content Structure**

#### **Executive Summary Tab**
- Rich text editor with formatting toolbar
- AI suggestions panel (side drawer):
  - "Strengthen impact statement"
  - "Add quantifiable outcomes"
  - "Improve call to action"
- Validation alerts:
  - Length recommendation: "Executive summaries are most effective at 250-400 words (currently: 180)"
  - Missing elements: "Consider adding: problem statement, solution overview, expected impact"

#### **Project Description Tab**
- Rich text editor
- Section templates (insertable):
  - Problem Statement
  - Proposed Solution
  - Methodology
  - Innovation/Uniqueness
- AI assistance:
  - "Expand on methodology"
  - "Add evidence/statistics"
  - "Strengthen problem statement"

#### **Budget Tab**
- Table editor with formulas
- Budget categories (pre-populated):
  - Personnel (salaries, benefits)
  - Equipment
  - Travel
  - Supplies
  - Indirect costs
- Validation:
  - Total must match grant amount
  - Required categories for funding type
  - Percentage limits (e.g., indirect costs <25%)

#### **Timeline Tab**
- Interactive Gantt chart or table
- Milestone markers
- Validation:
  - Must align with grant period
  - Required reporting dates
  - Realistic timeframes

#### **Impact Tab**
- Metrics table (quantitative)
- Outcomes description (qualitative)
- Logic model template
- Validation:
  - SMART criteria check
  - Alignment with funding goals

#### **Organization Tab**
- Auto-populated from profile
- Editable for proposal-specific info
- Team member bios
- Organizational chart
- Past performance data

### **AI Enhancement Features**

#### **"Fix Gaps" Button**
Available on each tab, triggers targeted improvements:
- Analyzes current content
- Suggests specific enhancements
- Shows before/after preview
- Option to accept/reject suggestions

#### **Q&A Pack**
Expandable section with common funder questions:
- "How will you measure success?"
- "What makes your approach unique?"
- "How will this be sustainable?"
- AI generates draft answers based on proposal content

### **Version History**
- Sidebar with timestamped versions
- "Restore this version" option
- Compare versions (diff view)
- Auto-save every 2 minutes
- Manual save points

### **Collaboration Features**
- Comments/notes on sections
- Review mode (read-only with comment ability)
- Export for external review

### **Error States**
- **Auto-save Failed**: "Couldn't save changes (Request ID: {request_id}). Your work is stored locally. Try again or check your connection."
- **AI Assistance Error**: "AI suggestions unavailable right now (Request ID: {request_id}). You can continue editing manually."
- **Validation API Error**: "Couldn't check proposal quality (Request ID: {request_id}). Your content is saved, but validation scores may be outdated."

### **Loading States**
- Tab switching: Skeleton loader for content area
- AI suggestions: "Generating suggestions..." with spinner
- Auto-save: Small spinner next to "Saving..."

### **Success States**
- Save complete: Green checkmark + "All changes saved"
- AI enhancement applied: "Section improved successfully"
- Validation passed: Green badge + "Section complete"

### **Microcopy**
- **Welcome**: "Your AI-generated proposal is ready for editing. Use the tabs to review and customize each section."
- **AI Help**: "Need inspiration? Click 'Fix Gaps' for AI-powered suggestions to strengthen any section."
- **Validation**: "Green badges show complete sections. Yellow and red badges highlight areas that could be improved."

---

## 6. **Exports Panel**

### **Purpose**
Manage proposal exports, download history, and format options.

### **Export Creation**

#### **Format Selection**
- **PDF** (radio button, default)
  - Preview: Letter-sized, professional formatting
  - Options: Include appendices, page numbers, headers/footers
- **DOCX** (radio button)
  - Preview: Microsoft Word compatible
  - Options: Template style, track changes enabled

#### **Customization Options**
- **Include Sections** (checkboxes):
  - Executive Summary
  - Project Description  
  - Budget tables
  - Timeline charts
  - Organization info
  - Appendices
- **Branding**:
  - Organization logo upload
  - Custom header/footer text
  - Color scheme (professional, modern, classic)

#### **Export Settings**
- **File Name** (editable): "[Organization]-[Proposal-Title]-[Date]"
- **Password Protection** (checkbox + password field)
- **Watermark** (Draft, Final, Confidential, None)

### **Actions**
- **Generate Export** (calls `/api/proposals/{id}/export/{format}`)
- **Preview** (shows sample pages)
- **Save Template** (saves customization settings)

### **Export History Table**

#### **Columns**
- Proposal title (linked)
- Format (PDF/DOCX icon)
- Export date
- File size
- Download count
- Actions (Download, Re-generate, Delete)

#### **Filters**
- Date range picker
- Format filter (All, PDF, DOCX)
- Proposal filter (dropdown)

### **Rate Limiting Display**
Shows current export usage:
- "Exports this minute: 3/10"
- Progress bar (green <7, yellow 7-9, red 10)
- If limited: "Export limit reached. Try again in: 0:45"

### **Error States**

#### **Export Generation Failed**
- Message: "Export generation failed (Request ID: {request_id}). This doesn't count against your limit."
- "Retry Export" button
- "Try Different Format" button

#### **Rate Limit Hit (429)**
- Message: "You've reached the export limit (10 per minute). Please wait before trying again."
- Countdown: "Try again in: 0:30"
- Request ID: "Reference ID: {request_id}"

#### **File Not Found**
- Message: "This export file is no longer available. Generate a new export to download."
- "Re-generate" button

### **Loading States**
- Export generation: Progress bar + "Generating [format] export..."
- Download preparation: "Preparing download..."
- History loading: Table skeleton

### **Success States**
- Export ready: "Export generated successfully!" + auto-download
- Download complete: "File downloaded: [filename]"

### **Microcopy**
- **Welcome**: "Export your proposals as professional PDF or editable Word documents."
- **History**: "Access all your previous exports here. Files are stored for 30 days."
- **Rate Limit**: "Export limits prevent server overload. Upgrade your plan for higher limits."

---

## 7. **Settings (Admin)**

### **Purpose**
Administrative configuration, API health monitoring, user management, and feature controls.

### **Settings Sections (Tabs)**

#### **API Configuration Tab**

**Fields:**
- **Backend URL*** (text input)
  - Default: "https://api.ngoinfo.org"
  - Validation: URL format, HTTPS required for production
- **API Key*** (password input with show/hide toggle)
  - Validation indicator: ✅ Valid / ❌ Invalid / ⏳ Checking
- **Environment** (radio buttons):
  - Production (default)
  - Staging
  - Development
- **Timeout Settings**:
  - Request timeout (slider: 30-300 seconds)
  - Retry attempts (slider: 0-5)

**Actions:**
- **Test Connection** (calls `/healthcheck`)
- **Save Configuration**
- **Reset to Defaults**

#### **Health Panel**
Real-time API status display:

**Status Indicators:**
- **API Status**: 🟢 Healthy / 🟡 Degraded / 🔴 Down
- **Database**: 🟢 Connected / 🔴 Disconnected  
- **Response Time**: "[X]ms" (color-coded: <500ms green, 500-2000ms yellow, >2000ms red)
- **Last Check**: "2 minutes ago" with auto-refresh

**Recent Errors Log:**
- Timestamp
- Error type
- Request ID
- Status (Resolved/Ongoing)

#### **User Management Tab**

**Current User Info:**
- Organization name
- User role (Admin, Editor, Viewer)
- Plan type and limits
- Account creation date

**Impersonation Panel** (admin only):
- **User Search** (autocomplete)
- **Impersonate User** button
- Active impersonation notice: "Currently viewing as: [User Name]" + "Stop Impersonation"

**Usage Analytics:**
- Monthly usage charts
- Top users by API calls
- Error rate trends

#### **Feature Flags Tab**

**Available Toggles:**
- **Beta Features** (checkbox)
  - Enable experimental features
- **Advanced Editor** (checkbox)
  - Enable additional editing tools
- **Export Branding** (checkbox)
  - Allow logo/branding in exports
- **Collaboration** (checkbox)
  - Enable multi-user editing
- **Debug Mode** (checkbox)
  - Show detailed error messages

**Feature Descriptions:**
Each toggle includes explanation of what it enables/disables.

#### **Cache Management Tab**

**Cache Statistics:**
- Cache hit rate
- Storage used
- Last cleared

**Actions:**
- **Clear Profile Cache**
- **Clear Proposals Cache**
- **Clear All Cache**
- **Refresh API Tokens**

### **Validation Rules**
- Backend URL must be valid HTTPS URL
- API key validation through test connection
- Feature flags may have dependencies (warnings shown)

### **Error States**

#### **Connection Test Failed**
- Message: "Unable to connect to backend API (Request ID: {request_id})"
- Details: HTTP status, error description
- Suggestions: "Check URL, API key, and network connectivity"

#### **Invalid API Key**
- Message: "API key authentication failed"
- Request ID shown
- "Generate New Key" link to backend

#### **Save Failed**
- Message: "Settings couldn't be saved (Request ID: {request_id})"
- "Retry Save" button
- Changes preserved in form

### **Loading States**
- Connection test: "Testing connection..." with spinner
- Settings save: "Saving settings..." with spinner
- Health check: "Refreshing status..." with spinner

### **Success States**
- Connection test: "✅ Connection successful - API is healthy"
- Settings saved: "✅ Settings saved successfully"
- Cache cleared: "✅ Cache cleared - fresh data will be loaded"

### **Microcopy**
- **API Config**: "Configure your connection to the NGOInfo Copilot backend API"
- **Health Panel**: "Monitor API performance and troubleshoot issues"
- **Feature Flags**: "Enable or disable experimental features for your organization"
- **Security Note**: "API keys are encrypted and never stored in plain text"

---

# 🎨 **Design System**

## **Color Palette**

### **Primary Colors**
- **Primary Blue**: #2563eb (action buttons, links)
- **Success Green**: #10b981 (completed states, validation)
- **Warning Yellow**: #f59e0b (attention needed, warnings)
- **Error Red**: #ef4444 (errors, critical states)
- **Info Blue**: #06b6d4 (informational content)

### **Neutral Colors**
- **Text Primary**: #1f2937 (headings, primary text)
- **Text Secondary**: #6b7280 (secondary text, labels)
- **Border**: #d1d5db (form borders, dividers)
- **Background**: #f9fafb (page background)
- **Surface**: #ffffff (cards, panels)

## **Typography**

### **Font Stack**
```css
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
```

### **Scale**
- **H1**: 2.25rem (36px) - Page titles
- **H2**: 1.875rem (30px) - Section headers
- **H3**: 1.5rem (24px) - Subsection headers
- **Body**: 1rem (16px) - Regular text
- **Small**: 0.875rem (14px) - Helper text, labels
- **Caption**: 0.75rem (12px) - Captions, fine print

## **Spacing**

### **Scale** (rem units)
- **xs**: 0.25rem (4px)
- **sm**: 0.5rem (8px)
- **md**: 1rem (16px)
- **lg**: 1.5rem (24px)
- **xl**: 2rem (32px)
- **2xl**: 3rem (48px)

## **Component Specifications**

### **Buttons**

#### **Primary Button**
```css
background: #2563eb;
color: white;
padding: 0.75rem 1.5rem;
border-radius: 0.5rem;
font-weight: 600;
```

**States:**
- Hover: background #1d4ed8
- Active: background #1e40af
- Disabled: background #9ca3af, cursor not-allowed
- Loading: spinner + "Loading..." text

#### **Secondary Button**
```css
background: white;
color: #374151;
border: 1px solid #d1d5db;
padding: 0.75rem 1.5rem;
border-radius: 0.5rem;
```

#### **Danger Button**
```css
background: #ef4444;
color: white;
/* other styles same as primary */
```

### **Form Elements**

#### **Text Input**
```css
border: 1px solid #d1d5db;
padding: 0.75rem;
border-radius: 0.375rem;
font-size: 1rem;
```

**States:**
- Focus: border #2563eb, box-shadow
- Error: border #ef4444
- Disabled: background #f3f4f6

#### **Validation Messages**
- **Error**: Red text (#ef4444) + error icon
- **Success**: Green text (#10b981) + check icon
- **Warning**: Yellow text (#f59e0b) + warning icon

### **Progress Indicators**

#### **Progress Bar**
```css
background: #e5e7eb; /* track */
height: 0.5rem;
border-radius: 0.25rem;
```

**Fill colors:**
- Normal: #2563eb
- Warning: #f59e0b  
- Error: #ef4444
- Success: #10b981

#### **Loading Spinner**
- Size: 1rem (16px) for inline, 2rem (32px) for page loading
- Color: inherit from parent or #2563eb
- Animation: smooth rotation

### **Cards & Panels**

#### **Card**
```css
background: white;
border: 1px solid #e5e7eb;
border-radius: 0.5rem;
padding: 1.5rem;
box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
```

#### **Alert/Notice**
```css
padding: 1rem;
border-radius: 0.375rem;
border-left: 4px solid [color];
```

**Types:**
- Info: border-color #06b6d4, background #ecfeff
- Success: border-color #10b981, background #ecfdf5
- Warning: border-color #f59e0b, background #fffbeb
- Error: border-color #ef4444, background #fef2f2

---

# ♿ **Accessibility Guidelines**

## **WCAG 2.1 AA Compliance**

### **Color & Contrast**
- **Text Contrast**: 4.5:1 minimum for normal text, 3:1 for large text
- **UI Elements**: 3:1 contrast for buttons, form controls
- **Never rely on color alone** for conveying information
- **Color blind friendly**: Use icons/patterns with color coding

### **Keyboard Navigation**
- **Tab Order**: Logical flow through interactive elements
- **Focus Indicators**: Visible focus rings on all interactive elements
- **Skip Links**: "Skip to main content" for screen readers
- **Escape Routes**: ESC key closes modals, cancels operations

### **Screen Reader Support**

#### **ARIA Roles**
- `role="main"` for main content area
- `role="navigation"` for menu systems
- `role="alert"` for error messages
- `role="status"` for live updates
- `role="progressbar"` for loading indicators

#### **ARIA Labels**
- `aria-label` for buttons without visible text
- `aria-describedby` for form field help text
- `aria-expanded` for collapsible content
- `aria-live="polite"` for status updates
- `aria-live="assertive"` for errors

#### **ARIA States**
- `aria-disabled="true"` for disabled elements
- `aria-required="true"` for required form fields
- `aria-invalid="true"` for validation errors
- `aria-busy="true"` during loading states

### **Form Accessibility**

#### **Labels & Help Text**
- Every form control has an associated `<label>`
- Required fields marked with `aria-required="true"`
- Help text linked with `aria-describedby`
- Error messages linked with `aria-describedby`

#### **Error Handling**
- Errors announced to screen readers
- Focus moved to first error field
- Clear instructions for fixing errors
- Request IDs provided for support

### **Motion & Animation**
- **Respect `prefers-reduced-motion`**
- Essential animations only
- No auto-playing content
- Pause/stop controls for moving content

---

# 📝 **Microcopy & Content Guidelines**

## **Tone of Voice**
- **Professional but approachable**
- **Encouraging and supportive**
- **Clear and concise**
- **Empathetic to user frustration**

## **Error Message Principles**

### **Structure**
1. **What happened** (clear, non-technical)
2. **Why it happened** (if helpful)
3. **What to do next** (actionable steps)
4. **Request ID** (for support)

### **Examples**

#### **Good Error Messages**
- "We couldn't save your proposal right now. Please try again, or contact support with Request ID: ABC123 if the problem continues."
- "Your API limit is reached (5 requests per minute). Please wait 30 seconds before generating another proposal."
- "This funding opportunity isn't available anymore. Try searching for similar opportunities or use a custom brief instead."

#### **Avoid**
- "Error 500: Internal server error"
- "Request failed"
- "Invalid input"

## **Onboarding Microcopy**

### **Encouraging Progress**
- "Great start! Let's add a few more details..."
- "Almost there! One more step to complete your profile."
- "Perfect! Your profile is looking comprehensive."

### **Help Text**
- **Be specific**: "Add 2-3 recent projects with outcomes achieved"
- **Show examples**: "e.g., 'Provided clean water to 500 families in rural Kenya'"
- **Explain why**: "This helps our AI understand your expertise and write stronger proposals"

## **Success Messages**

### **Completion**
- "Proposal generated successfully! Review and customize it in the editor."
- "Export ready! Your professional PDF has been generated."
- "Profile updated! This will improve your future proposal quality."

### **Progress**
- "Section completed ✓"
- "Changes saved automatically"
- "Validation passed"

## **Upselling & Limits**

### **Quota Warnings**
- **Soft limit** (80%): "You've used 8 of 10 monthly proposals. Consider upgrading for unlimited generation."
- **Hard limit**: "Monthly limit reached. Upgrade to Pro for unlimited proposals and priority support."

### **Feature Promotion**
- "Unlock advanced editing tools with Pro"
- "Get priority email support with your plan upgrade"

---

# 🔄 **State Management**

## **Application States**

### **Authentication States**
- **Logged Out**: Show login form
- **Logging In**: Loading spinner, disable form
- **Logged In**: Show main interface
- **Session Expired**: Auto-logout + message

### **Profile States**
- **No Profile**: Redirect to onboarding
- **Incomplete Profile**: Show completion prompts
- **Complete Profile**: Full feature access
- **Profile Error**: Fallback mode with limited features

### **Connection States**
- **Online**: Normal operation
- **Offline**: Cached data only, disable API features
- **Slow Connection**: Show patience messages
- **API Down**: Graceful degradation

## **Data Caching Strategy**

### **Cache Levels**
1. **Session Cache**: Current proposal edits
2. **Local Storage**: User preferences, drafts
3. **WordPress Transients**: API responses (5 minutes)
4. **Browser Cache**: Static assets

### **Cache Invalidation**
- **Profile changes**: Clear profile cache
- **Proposal updates**: Clear specific proposal cache
- **API errors**: Don't cache error responses
- **Manual refresh**: Clear all API cache

## **Error Recovery**

### **Retry Logic**
- **Network errors**: Auto-retry 3 times with backoff
- **Rate limits**: Wait and retry automatically
- **Server errors**: Manual retry option
- **Validation errors**: No auto-retry, user must fix

### **Offline Handling**
- **Draft saving**: Store locally, sync when online
- **Form data**: Preserve in session storage
- **Error queue**: Retry failed operations when connection restored

---

---

# 📊 **Summary Table: Screens → API Endpoints → Key States**

## **Complete Feature Matrix**

| Screen | Primary API Endpoints | Key Success States | Error States | Rate Limits | Caching |
|--------|----------------------|-------------------|--------------|-------------|---------|
| **Onboarding Wizard** | `POST /api/profile` | Profile created ✅ | API error, validation failure | None | Profile cached 5min |
| **Dashboard** | `GET /api/usage/summary`<br>`GET /api/proposals/?limit=5` | Usage loaded, proposals listed | Connection error, profile load fail | None | Usage 5min, proposals 2min |
| **Funding Picker** | `GET /api/funding-opportunities/search` | Opportunity selected, custom brief ready | No results, search timeout | None | Search results 10min |
| **Generate Proposal** | `POST /api/proposals/generate` | Proposal created, high confidence score | 422 validation, 429 rate limit, 500 server error | **5/minute** | Idempotency TTL 10min |
| **Proposal Editor** | `GET /api/proposals/{id}`<br>`PUT /api/proposals/{id}` | Sections complete, auto-save working | Save failed, AI unavailable | None | Auto-save local, content 5min |
| **Exports Panel** | `GET /api/proposals/{id}/export/{format}` | Export generated, download ready | Generation failed, 429 rate limit, file not found | **10/minute** | Export files 30 days |
| **Settings (Admin)** | `GET /healthcheck` | Connection successful, config saved | Invalid API key, connection timeout | None | Health status 30sec |

## **API Endpoint Usage by Screen**

### **Core Data Endpoints**
| Endpoint | Method | Used By | Frequency | Rate Limited | Cached |
|----------|--------|---------|-----------|--------------|--------|
| `POST /api/profile` | POST | Onboarding | One-time | ❌ No | ✅ 5min |
| `GET /api/profile` | GET | Dashboard, Editor | On load | ❌ No | ✅ 5min |
| `PUT /api/profile` | PUT | Settings | Manual | ❌ No | Clears cache |
| `GET /api/usage/summary` | GET | Dashboard | Auto-refresh | ❌ No | ✅ 5min |

### **Proposal Management**
| Endpoint | Method | Used By | Frequency | Rate Limited | Cached |
|----------|--------|---------|-----------|--------------|--------|
| `POST /api/proposals/generate` | POST | Generate | Manual | ✅ 5/min | Idempotency 10min |
| `GET /api/proposals/` | GET | Dashboard | On load | ❌ No | ✅ 2min |
| `GET /api/proposals/{id}` | GET | Editor | On edit | ❌ No | ✅ 5min |
| `PUT /api/proposals/{id}` | PUT | Editor | Auto-save | ❌ No | Real-time |

### **Export & Search**
| Endpoint | Method | Used By | Frequency | Rate Limited | Cached |
|----------|--------|---------|-----------|--------------|--------|
| `GET /api/proposals/{id}/export/{format}` | GET | Exports | Manual | ✅ 10/min | File storage 30d |
| `GET /api/funding-opportunities/search` | GET | Funding Picker | Search | ❌ No | ✅ 10min |

### **System Health**
| Endpoint | Method | Used By | Frequency | Rate Limited | Cached |
|----------|--------|---------|-----------|--------------|--------|
| `GET /healthcheck` | GET | Settings | Real-time | ❌ No | ✅ 30sec |
| `GET /docs/openapi.json` | GET | Development | Manual | ❌ No | ✅ 1hr |

## **State Management Matrix**

### **Authentication States**
| State | Screens Affected | User Experience | API Behavior |
|-------|------------------|-----------------|--------------|
| **Logged Out** | All | Show login form | Block all requests |
| **Logging In** | All | Loading spinner | Queue requests |
| **Logged In** | All | Full functionality | Normal operation |
| **Session Expired** | All | Auto-logout + message | Return 401, clear cache |

### **Connection States**
| State | Screens Affected | User Experience | Fallback Behavior |
|-------|------------------|-----------------|-------------------|
| **Online** | All | Normal operation | Real-time API calls |
| **Offline** | All | Show offline notice | Use cached data only |
| **Slow Connection** | All | Show patience messages | Extended timeouts |
| **API Down** | All | Graceful degradation | Local features only |

### **Profile Completeness States**
| Completeness | Dashboard Badge | Generation Quality | Editor Features |
|--------------|-----------------|-------------------|-----------------|
| **0-25%** | ❌ Incomplete | Low quality | Basic editor |
| **26-50%** | ⚠️ Needs Attention | Medium quality | Standard editor |
| **51-75%** | ⚠️ Nearly Complete | Good quality | Enhanced editor |
| **76-100%** | ✅ Complete | High quality | Full features |

### **Usage Quota States**
| Usage Level | Dashboard Display | Generation Allowed | Export Allowed | Upgrade Prompts |
|-------------|-------------------|-------------------|----------------|-----------------|
| **0-80%** | Green progress | ✅ Yes | ✅ Yes | None |
| **81-95%** | Yellow progress | ✅ Yes | ✅ Yes | Soft prompt |
| **96-99%** | Red progress | ⚠️ Warning | ✅ Yes | Strong prompt |
| **100%** | Blocked | ❌ No | ✅ Yes | Upgrade required |

## **Error Handling Strategy**

### **Standard Error Response Format**
All API errors follow Phase 0 format:
```json
{
  "code": "ERROR_CODE",
  "message": "Human readable message", 
  "request_id": "uuid-for-support",
  "details": {
    "field_errors": ["specific validation issues"],
    "suggested_actions": ["what user can do"]
  }
}
```

### **Error Recovery Patterns**
| Error Type | Recovery Strategy | User Action | Auto-Retry |
|------------|-------------------|-------------|------------|
| **Network (timeout)** | Auto-retry with backoff | Show progress | ✅ 3x |
| **Rate Limit (429)** | Show countdown timer | Wait and retry | ✅ Auto |
| **Validation (422)** | Highlight field errors | Fix and submit | ❌ Manual |
| **Server Error (500)** | Show request ID | Retry or contact support | ❌ Manual |
| **Auth Error (401)** | Force re-login | Login again | ❌ Manual |

### **Offline Capability Matrix**
| Feature | Offline Support | Sync Strategy | Data Persistence |
|---------|-----------------|---------------|------------------|
| **Profile Editing** | ✅ Yes | Sync on reconnect | LocalStorage |
| **Proposal Drafts** | ✅ Yes | Auto-save + sync | IndexedDB |
| **Generation** | ❌ No | Queue for online | Request queue |
| **Export** | ❌ No | Cache previous exports | File storage |
| **Search** | ⚠️ Cached only | Use cached results | SessionStorage |

## **Performance Targets**

### **Loading Time Requirements**
| Screen | Target Load Time | API Dependency | Progressive Loading |
|--------|------------------|----------------|-------------------|
| **Dashboard** | < 2 seconds | High | ✅ Skeleton UI |
| **Funding Picker** | < 3 seconds | Medium | ✅ Search results |
| **Proposal Editor** | < 1 second | Low | ✅ Tab-based |
| **Generate Progress** | Real-time | High | ✅ Live updates |
| **Exports** | < 5 seconds | High | ✅ Generation status |

### **Accessibility Compliance**
| WCAG 2.1 Requirement | Implementation | Testing Strategy |
|----------------------|----------------|------------------|
| **Keyboard Navigation** | Tab order, focus rings | Manual testing |
| **Screen Reader** | ARIA labels, live regions | Screen reader testing |
| **Color Contrast** | 4.5:1 minimum ratio | Automated testing |
| **Text Scaling** | 200% zoom support | Responsive testing |
| **Error Announcement** | Live regions for errors | Assistive tech testing |

## **Security Considerations**

### **Data Protection**
| Data Type | Storage Location | Encryption | Retention |
|-----------|------------------|------------|-----------|
| **API Keys** | WordPress options | ✅ Hashed | Permanent |
| **Proposal Content** | Local cache | ❌ No | 24 hours |
| **User Sessions** | WordPress cookies | ✅ Yes | 24 hours |
| **Export Files** | Server storage | ✅ Yes | 30 days |

### **API Security**
| Endpoint | Authentication | Rate Limiting | Request Validation |
|----------|----------------|---------------|-------------------|
| **Profile** | JWT required | None | Schema validation |
| **Generate** | JWT required | 5/minute | Input sanitization |
| **Export** | JWT required | 10/minute | File type validation |
| **Health** | Optional | None | Read-only |

---

This completes the comprehensive UX Design specification for the NGOInfo Copilot WordPress plugin. The documentation provides complete coverage of all screens, user flows, API integrations, error handling, accessibility requirements, and technical implementation details needed for Phase 2 development.
