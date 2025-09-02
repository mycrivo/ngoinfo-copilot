# Onboarding Wizard Wireflow

## User Journey: First-time Setup
**Entry Point**: New user activates plugin  
**Goal**: Complete organization profile for optimal AI proposal generation  
**API Endpoints**: `POST /api/profile`

---

## Step 1: Organization Basics

```
┌─────────────────────────────────────────────────────────────────┐
│ NGO Copilot Setup - Step 1 of 3                       [Skip] [X]│
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Tell us about your organization                                │
│  This helps our AI create proposals that perfectly match        │
│  your mission and goals                                         │
│                                                                 │
│  Progress: ████████░░░░░░░░░░░░░░░░░░░░ 33%                     │
│                                                                 │
│  Organization Name *                                            │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ [Text Input - 200 chars max]                           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  Mission Statement *                                            │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ [Textarea - 1000 chars]                                │   │
│  │ Describe what your organization does and the impact     │   │
│  │ you aim to achieve. Be specific about focus areas.     │   │
│  └─────────────────────────────────────────────────────────┘   │
│  📝 50/1000 characters minimum                                  │
│                                                                 │
│  Founded Year          Organization Type *                      │
│  ┌─────────────┐       ┌─────────────────────────────────────┐ │
│  │ [Year: 2010]│       │ [Dropdown: NGO ▼]                  │ │
│  └─────────────┘       └─────────────────────────────────────┘ │
│                                                                 │
│  Website URL           Registration Number                      │
│  ┌─────────────────┐   ┌─────────────────────────────────────┐ │
│  │ [URL input]     │   │ [Text - 50 chars]                  │ │
│  └─────────────────┘   └─────────────────────────────────────┘ │
│                                                                 │
│                               [Skip for Now]  [Continue ➤]     │
└─────────────────────────────────────────────────────────────────┘
```

### Error States
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚠️ Please check the following:                                  │
│ • Organization Name is required                                 │
│ • Mission Statement needs at least 50 characters               │
│ • Please enter a valid website URL                             │
└─────────────────────────────────────────────────────────────────┘
```

### Flow Actions
- **Continue**: Validate required fields → Step 2
- **Skip for Now**: Show warning → Step 2 with incomplete flag
- **X/Close**: Confirm exit → WordPress dashboard

---

## Step 2: Projects & Focus Areas

```
┌─────────────────────────────────────────────────────────────────┐
│ NGO Copilot Setup - Step 2 of 3                   [Back] [Skip]│
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  What areas do you focus on?                                    │
│  This helps us find the most relevant funding opportunities     │
│                                                                 │
│  Progress: ████████████████░░░░░░░░░░░░ 66%                     │
│                                                                 │
│  Primary Sectors * (select up to 3)                            │
│  ☑️ Education      ☑️ Health         ☐ Environment            │
│  ☐ Human Rights    ☐ Economic Dev    ☐ Emergency Relief        │
│  ☐ Other: [________________]                                   │
│                                                                 │
│  Geographic Focus *                                             │
│  ☑️ Local         ☑️ National       ☐ Regional                 │
│  ☐ Global                                                      │
│                                                                 │
│  Countries/Regions: [Kenya ▼] [Add Region +]                   │
│                                                                 │
│  Target Beneficiaries *                                         │
│  ☑️ Children       ☐ Women          ☐ Elderly                  │
│  ☑️ Rural Communities ☐ Urban Poor  ☐ Refugees                │
│                                                                 │
│  Past Projects Description *                                    │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ [Textarea - 1500 chars]                                │   │
│  │ Describe 1-3 recent projects with outcomes,            │   │
│  │ beneficiaries reached, and impact achieved.            │   │
│  └─────────────────────────────────────────────────────────┘   │
│  📝 150/1500 characters (minimum 100)                          │
│                                                                 │
│  Key Achievements                                               │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ [Textarea - 1000 chars]                                │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│                         [⬅ Back]  [Skip]  [Continue ➤]         │
└─────────────────────────────────────────────────────────────────┘
```

### Flow Actions  
- **Back**: Return to Step 1, preserve all data
- **Continue**: Validate selections → Step 3
- **Skip**: Warning dialog → Step 3 with incomplete flag

---

## Step 3: Operations & Financials

```
┌─────────────────────────────────────────────────────────────────┐
│ NGO Copilot Setup - Step 3 of 3                   [Back] [Save]│
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Tell us about your operations                                  │
│  This final step helps us suggest the right funding amounts     │
│                                                                 │
│  Progress: ████████████████████████████████ 100%               │
│                                                                 │
│  Annual Budget Range *                                          │
│  ○ <$10K  ○ $10K-50K  ●$50K-250K  ○ $250K-1M  ○ >$5M          │
│                                                                 │
│  Staff Size *                                                   │
│  ○ 1-5  ●6-20  ○ 21-50  ○ 51-200  ○ >200                       │
│                                                                 │
│  Current Funding Sources                                        │
│  ☑️ Government Grants    ☑️ Private Foundations                 │
│  ☐ Corporate Donations   ☑️ Individual Donors                   │
│  ☐ Earned Revenue        ☐ Other                               │
│                                                                 │
│  Grant Writing Experience *                                     │
│  ○ Beginner  ●Intermediate  ○ Advanced                          │
│                                                                 │
│  Typical Proposal Value Range                                   │
│  ○ <$5K  ●$5K-25K  ○ $25K-100K  ○ $100K-500K  ○ >$500K        │
│                                                                 │
│                                                                 │
│                    [⬅ Back]  [Save as Draft]  [Complete Setup] │
└─────────────────────────────────────────────────────────────────┘
```

### Success Flow
```
┌─────────────────────────────────────────────────────────────────┐
│                          ✅ Profile Created Successfully!        │
│                                                                 │
│        🎉 Great! Your profile is ready. You can now generate    │
│            professional proposals tailored to your             │
│                        organization.                            │
│                                                                 │
│              Taking you to your dashboard in 3... 2... 1...    │
│                                                                 │
│                        ████████████████████                    │
│                       Creating your workspace...               │
└─────────────────────────────────────────────────────────────────┘
```

**API Call**: `POST /api/profile` with complete profile data  
**On Success**: Redirect to Dashboard  
**On Error**: Show error with Request ID, allow retry

### Flow Actions
- **Complete Setup**: Validate → API call → Success/Error handling
- **Save as Draft**: Store locally, can resume later
- **Back**: Return to Step 2, preserve data

---

## Error Handling

### API Error State
```
┌─────────────────────────────────────────────────────────────────┐
│ ❌ We couldn't save your profile right now                      │
│                                                                 │
│    Request ID: a1b2c3d4-e5f6-7890-abcd-ef1234567890           │
│                                                                 │
│    Please try again or contact support if the problem          │
│    continues. Your information has been saved locally.         │
│                                                                 │
│                          [Try Again]  [Contact Support]        │
└─────────────────────────────────────────────────────────────────┘
```

### Skip Warning Dialog
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚠️ Skip this step?                                              │
│                                                                 │
│    Your profile will be incomplete, which may result in        │
│    lower-quality proposal generation. You can complete         │
│    this information later in Settings.                         │
│                                                                 │
│                              [Go Back]  [Skip Anyway]          │
└─────────────────────────────────────────────────────────────────┘
```

---

## Mobile Responsive Considerations

### Mobile Layout (< 768px)
- Single column layout
- Larger touch targets (44px minimum)
- Simplified multi-select interactions
- Collapsible help text
- Sticky progress bar and navigation

### Tablet Layout (768px - 1024px)
- Two-column layout for form fields
- Side-by-side checkboxes
- Expanded help text areas

