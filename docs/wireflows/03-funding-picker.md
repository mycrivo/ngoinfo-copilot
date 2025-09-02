# Funding Picker Wireflow

## User Journey: Opportunity Selection
**Entry Point**: Dashboard → Generate New Proposal  
**Goal**: Select funding opportunity or create custom brief for proposal generation  
**API Endpoints**: `GET /api/funding-opportunities/search`, `POST /api/proposals/generate`

---

## Main Funding Picker Interface

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ ← Back to Dashboard                    NGO Copilot - Funding Picker             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Choose how you'd like to create your proposal                                  │
│                                                                                 │
│ ┌─ Browse Opportunities ─┐ ┌─ Custom Brief ──────┐                             │
│ │ Find curated funding   │ │ Describe any funding │                             │
│ │ from our database      │ │ source yourself      │                             │
│ └────────────────────────┘ └──────────────────────┘                             │
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ 🔍 Search opportunities...                                      [Search]    │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ Filters ──────────────────────────────────────────────────────────────────┐ │
│ │ Sector: ☑️ Education ☑️ Health ☐ Environment [Clear]                       │ │
│ │ Amount: $5K ████████████████████████████████████████████ $500K             │ │
│ │ Deadline: ○ Next 30 days ●Next 60 days ○ Next 90 days                    │ │
│ │ Region: ☑️ Kenya ☑️ East Africa ☐ Global                                  │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ Found 23 opportunities matching your profile                                   │
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ 🏛️ Gates Foundation - Education Innovation Grant                            │ │
│ │ Amount: $50,000 - $200,000  |  Deadline: March 15, 2025  |  Match: 92% ✅   │ │
│ │                                                                             │ │
│ │ Supporting innovative approaches to primary education in sub-Saharan        │ │
│ │ Africa with focus on rural communities and girls' education.               │ │
│ │                                                                             │ │
│ │ Best fit: Education sector, rural focus, proven track record              │ │
│ │                                                                             │ │
│ │                                      [Save for Later] [Generate Proposal]  │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ 🌍 UNESCO - Community Learning Centers                                      │ │
│ │ Amount: $25,000 - $75,000  |  Deadline: Feb 28, 2025  |  Match: 87% ✅     │ │
│ │                                                                             │ │
│ │ Establishing community learning centers in rural areas with focus on       │ │
│ │ adult literacy and vocational training programs.                           │ │
│ │                                                                             │ │
│ │ Best fit: Community focus, education, capacity building                    │ │
│ │                                                                             │ │
│ │                                      [Save for Later] [Generate Proposal]  │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ ⚠️ Ford Foundation - Social Justice Initiative                              │ │
│ │ Amount: $100,000 - $500,000  |  Deadline: Jan 31, 2025  |  Match: 65% ⚠️   │ │
│ │                                                                             │ │
│ │ Supporting organizations working on social justice and human rights        │ │
│ │ advocacy in developing countries.                                          │ │
│ │                                                                             │ │
│ │ Lower match: Human rights focus differs from your education background    │ │
│ │                                                                             │ │
│ │                                      [Save for Later] [Generate Proposal]  │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│                                           [Load More] [Page: 1 of 3]           │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Custom Brief Tab

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ ← Back to Dashboard                    NGO Copilot - Custom Brief               │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ ┌─ Browse Opportunities ─┐ ┌─ Custom Brief ──────┐                             │
│ │ Find curated funding   │ │ Describe any funding │ ← ACTIVE                   │
│ │ from our database      │ │ source yourself      │                             │
│ └────────────────────────┘ └──────────────────────┘                             │
│                                                                                 │
│ Don't see the right opportunity? Describe any funding source and we'll create  │
│ a tailored proposal just for you.                                              │
│                                                                                 │
│ ┌─ Option 1: Detailed Brief (Recommended) ────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Funding Source URL (optional)                                              │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ https://example.org/grants/community-development                       │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │ Custom Brief *                                                              │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ Describe the funding opportunity, requirements, focus areas, and any    │ │ │
│ │ │ specific guidelines...                                                  │ │ │
│ │ │                                                                         │ │ │
│ │ │ The XYZ Foundation seeks proposals for community development projects   │ │ │
│ │ │ in East Africa focusing on education and healthcare. Priority given    │ │ │
│ │ │ to organizations with proven track record in rural areas. Grants       │ │ │
│ │ │ range from $25,000 to $100,000 for 18-month projects. Applications     │ │ │
│ │ │ must include detailed budget, timeline, and impact measurement plan.   │ │ │
│ │ │ Deadline: March 30, 2025.                                              │ │ │
│ │ │                                                                         │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │ 📝 420/5000 characters (minimum 200)                                       │ │
│ │                                                                             │ │
│ │                                                    [Generate from Brief]    │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ Option 2: Quick Fields ─────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ For faster setup - fill in key details:                                    │ │
│ │                                                                             │ │
│ │ Grant Amount ($)          Application Deadline                             │ │
│ │ ┌─────────────────────┐   ┌─────────────────────────────────────────────┐   │ │
│ │ │ $50,000             │   │ March 30, 2025                             │   │ │
│ │ └─────────────────────┘   └─────────────────────────────────────────────┘   │ │
│ │                                                                             │ │
│ │ Focus Areas (tags)                                                          │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ [Education] [Healthcare] [Community Development] [Add tag...]           │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │ Eligibility Criteria                                                        │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ Registered NGOs in East Africa, 3+ years experience                    │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │ Special Requirements                                                        │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ Must include detailed impact measurement plan                           │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │                                               [Generate from Quick Fields]  │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ⚠️ Important: Use either detailed brief OR quick fields, not both               │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Opportunity Selection Flow

### Selection Confirmation
```
┌─────────────────────────────────────────────────────────────────┐
│ ✅ Opportunity Selected                                          │
│                                                                 │
│ Gates Foundation - Education Innovation Grant                   │
│ Amount: $50,000 - $200,000                                     │
│ Deadline: March 15, 2025                                       │
│ Match Score: 92%                                                │
│                                                                 │
│ Ready to generate your AI-powered proposal?                    │
│                                                                 │
│              [Change Selection]  [Generate Proposal]           │
└─────────────────────────────────────────────────────────────────┘
```

### Custom Brief Confirmation
```
┌─────────────────────────────────────────────────────────────────┐
│ ✅ Custom Brief Ready                                            │
│                                                                 │
│ Brief: XYZ Foundation community development...                 │
│ Amount: $25,000 - $100,000                                     │
│ Deadline: March 30, 2025                                       │
│                                                                 │
│ Ready to generate your custom proposal?                        │
│                                                                 │
│              [Edit Brief]  [Generate Proposal]                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Error States

### No Results Found
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ 🔍 No funding opportunities match your criteria                                 │
│                                                                                 │
│ Try:                                                                            │
│ • Adjusting your filters (fewer sectors, wider date range)                     │
│ • Using broader search terms                                                   │
│ • Creating a custom brief instead                                              │
│                                                                                 │
│                    [Clear Filters]  [Try Custom Brief]                         │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### API Error - Opportunities List
```
┌─────────────────────────────────────────────────────────────────┐
│ ❌ Couldn't load funding opportunities                           │
│                                                                 │
│ Request ID: abc123def456                                        │
│                                                                 │
│ Please try refreshing or use the Custom Brief option instead.  │
│                                                                 │
│                    [Refresh]  [Try Custom Brief]               │
└─────────────────────────────────────────────────────────────────┘
```

### Validation Error - Custom Brief
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚠️ Please check your input                                       │
│                                                                 │
│ • Custom brief needs at least 200 characters                   │
│ • Please provide either a brief OR quick fields, not both      │
│                                                                 │
│                              [Fix and Continue]                │
└─────────────────────────────────────────────────────────────────┘
```

---

## Loading States

### Opportunities Loading
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ 🔍 Search opportunities...                                      [Search]       │
│                                                                                 │
│ Loading funding opportunities... ⏳                                             │
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ ████████████████████████████████                                           │ │
│ │ ████████████████████████████████                                           │ │
│ │ ████████████████████████████████                                           │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ ████████████████████████████████                                           │ │
│ │ ████████████████████████████████                                           │ │
│ │ ████████████████████████████████                                           │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ Finding the best matches for your organization...                              │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Search in Progress
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ 🔍 climate education rural                                      [Searching...] │
│                                                                                 │
│ Searching funding opportunities... ⏳                                           │
│                                                                                 │
│ ████████████████████████████████████████████████████████████████                │
│                                                                                 │
│ This may take a few seconds as we search thousands of opportunities...         │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Navigation Flows

### From Funding Picker
```
Funding Picker Actions:
├── Select Opportunity → Generate Proposal (with funding_opportunity_id)
├── Custom Brief → Generate Proposal (with custom_brief)
├── Quick Fields → Generate Proposal (with quick_fields)
├── Save for Later → Add to bookmarks (local storage)
├── Back to Dashboard → Dashboard
└── Change Selection → Clear selection, stay on picker
```

### API Calls Triggered
- **Page Load**: `GET /api/funding-opportunities/search` (with user profile context)
- **Filter Changes**: `GET /api/funding-opportunities/search?sector=X&amount=Y`
- **Search**: `GET /api/funding-opportunities/search?q={search_term}`
- **Generate Selection**: `POST /api/proposals/generate` with selected parameters

---

## Mobile Responsive Layout

### Mobile Opportunities List (< 768px)
```
┌─────────────────────────────────────────┐
│ ← Back     Funding Picker               │
├─────────────────────────────────────────┤
│                                         │
│ ┌─ Browse ─┐ ┌─ Custom ─┐               │
│ │ Funding  │ │ Brief   │               │
│ └──────────┘ └─────────┘               │
│                                         │
│ 🔍 [Search box]                         │
│                                         │
│ [Filters ▼]                             │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 🏛️ Gates Foundation                  │ │
│ │ Education Innovation                │ │
│ │                                     │ │
│ │ $50K - $200K                        │ │
│ │ Due: Mar 15, 2025                   │ │
│ │ Match: 92% ✅                       │ │
│ │                                     │ │
│ │ Supporting innovative approaches    │ │
│ │ to primary education...             │ │
│ │                                     │ │
│ │ [💾] [Generate Proposal]            │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 🌍 UNESCO                           │ │
│ │ Community Learning Centers          │ │
│ │                                     │ │
│ │ $25K - $75K                         │ │
│ │ Due: Feb 28, 2025                   │ │
│ │ Match: 87% ✅                       │ │
│ │                                     │ │
│ │ [💾] [Generate Proposal]            │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ [Load More]                             │
│                                         │
└─────────────────────────────────────────┘
```

### Mobile Custom Brief (< 768px)
```
┌─────────────────────────────────────────┐
│ ← Back     Custom Brief                 │
├─────────────────────────────────────────┤
│                                         │
│ Don't see the right opportunity?        │
│ Describe any funding source.            │
│                                         │
│ Funding URL (optional)                  │
│ ┌─────────────────────────────────────┐ │
│ │ [URL input]                         │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ Custom Brief *                          │
│ ┌─────────────────────────────────────┐ │
│ │ [Large textarea]                    │ │
│ │ Describe the funding opportunity,   │ │
│ │ requirements, focus areas...        │ │
│ │                                     │ │
│ │                                     │ │
│ │                                     │ │
│ │                                     │ │
│ └─────────────────────────────────────┘ │
│ 📝 245/5000 chars (min 200)            │
│                                         │
│ [Generate from Brief]                   │
│                                         │
│ ── OR ──                                │
│                                         │
│ [Use Quick Fields Instead]              │
│                                         │
└─────────────────────────────────────────┘
```


