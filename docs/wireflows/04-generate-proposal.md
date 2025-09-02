# Generate Proposal Wireflow

## User Journey: AI Proposal Generation
**Entry Point**: Funding Picker → Generate Proposal  
**Goal**: Configure and monitor AI proposal generation with error recovery  
**API Endpoints**: `POST /api/proposals/generate` (with idempotency & rate limiting)

---

## Generation Configuration Screen

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ ← Back to Picker                NGO Copilot - Generate Proposal                 │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Ready to generate your AI-powered proposal                                     │
│                                                                                 │
│ ┌─ Selected Opportunity ────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ 🏛️ Gates Foundation - Education Innovation Grant                            │ │
│ │ Amount: $50,000 - $200,000  |  Deadline: March 15, 2025  |  Match: 92%     │ │
│ │                                                                             │ │
│ │ Supporting innovative approaches to primary education in sub-Saharan       │ │
│ │ Africa with focus on rural communities and girls' education.              │ │
│ │                                                                             │ │
│ │                                                    [Change Selection]      │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ⏰ Estimated completion time: 2-3 minutes                                       │
│                                                                                 │
│ ┌─ Generation Options ──────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Tone                                                                        │ │
│ │ ●Professional   ○Conversational   ○Academic   ○Persuasive                   │ │
│ │                                                                             │ │
│ │ Length                                                                      │ │
│ │ ○Standard (3-5 pages)   ●Detailed (6-8 pages)   ○Comprehensive (9-12)      │ │
│ │                                                                             │ │
│ │ Custom Instructions (optional)                                              │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ Any specific requirements, emphasis areas, or unique aspects to         │ │ │
│ │ │ highlight...                                                            │ │ │
│ │ │                                                                         │ │ │
│ │ │ Please emphasize our 5-year track record in girls' education and       │ │ │
│ │ │ highlight our partnership with local schools. Include specific data    │ │ │
│ │ │ from our 2023 impact report showing 85% school completion rates.       │ │ │
│ │ │                                                                         │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │ 📝 245/2000 characters                                                      │ │
│ │                                                                             │ │
│ │ ▼ Advanced Options                                                          │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ Advanced Options (Expanded) ─────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Idempotency Key (prevents duplicates)                                      │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ gen_2025_01_gates_edu_v1_a7b9c4d2                                       │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │ ℹ️ This prevents duplicate proposals if you retry. Change only if creating │ │
│ │   a different version.                                                      │ │
│ │                                                                             │ │
│ │ ☐ Save as template for similar opportunities                               │ │
│ │ ☐ Send email notification when complete                                    │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│                                      [Save Draft]  [Generate Proposal]         │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Generation Progress States

### State 1: Initiating (0-10%)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          🚀 Generating Your Proposal                            │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Gates Foundation - Education Innovation Grant                                   │
│                                                                                 │
│ ████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 10%                             │
│                                                                                 │
│ 🔄 Preparing your proposal request...                                           │
│                                                                                 │
│ ⏰ Estimated time remaining: 2-3 minutes                                        │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│                                                        [Cancel]                │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### State 2: Analyzing (10-40%)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          🧠 Analyzing Your Context                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Gates Foundation - Education Innovation Grant                                   │
│                                                                                 │
│ ████████████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 40%                             │
│                                                                                 │
│ 🔍 Analyzing your organization and funding opportunity...                       │
│                                                                                 │
│ • Reviewing your organization profile ✅                                       │
│ • Matching funding requirements ✅                                              │
│ • Analyzing successful proposals 🔄                                             │
│ • Preparing content strategy...                                                │
│                                                                                 │
│ ⏰ Estimated time remaining: 1-2 minutes                                        │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│                                                        [Cancel]                │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### State 3: Generating (40-90%)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          ✍️ AI Crafting Your Proposal                           │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Gates Foundation - Education Innovation Grant                                   │
│                                                                                 │
│ ██████████████████████████████████████░░░░░░░░░░ 85%                            │
│                                                                                 │
│ 🤖 AI is crafting your proposal content...                                      │
│                                                                                 │
│ • Executive summary ✅                                                          │
│ • Project description ✅                                                        │
│ • Budget planning ✅                                                            │
│ • Impact metrics 🔄                                                             │
│ • Organization background...                                                   │
│                                                                                 │
│ ⏰ Estimated time remaining: 30 seconds                                         │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│                                                        [Cancel]                │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### State 4: Finalizing (90-100%)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          🎯 Adding Final Touches                                │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Gates Foundation - Education Innovation Grant                                   │
│                                                                                 │
│ ███████████████████████████████████████████████░ 95%                            │
│                                                                                 │
│ ✨ Adding final touches and scoring...                                          │
│                                                                                 │
│ • Content quality check ✅                                                      │
│ • Alignment scoring ✅                                                          │
│ • Completeness review 🔄                                                        │
│ • Final formatting...                                                          │
│                                                                                 │
│ ⏰ Almost done...                                                                │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Success State

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          ✅ Proposal Generated Successfully!                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ 📄 Education Innovation for Rural Kenya                                     │ │
│ │                                                                             │ │
│ │ Created: January 9, 2025 at 6:42 PM                                        │ │
│ │ Word Count: 2,847 words (8 pages)                                          │ │
│ │ Confidence Score: 89% ✅                                                    │ │
│ │ Alignment Score: 92% ✅                                                     │ │
│ │                                                                             │ │
│ │ "A comprehensive proposal focusing on innovative digital learning          │ │
│ │ solutions for rural primary schools, emphasizing girls' education and      │ │
│ │ community engagement..."                                                    │ │
│ │                                                                             │ │
│ │                                        [Quick Preview]                     │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ 🎉 Your AI-powered proposal is ready for review and customization!             │
│                                                                                 │
│                                                                                 │
│                            [Edit Proposal]  [Generate Another]                 │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Error States

### Validation Error (422)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               ⚠️ Input Validation Error                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Please check your input:                                                       │
│                                                                                 │
│ • Must provide exactly one of: funding_opportunity_id OR (custom_brief OR      │
│   quick_fields). Multiple inputs were provided.                                │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│ Need help? Reference this ID when contacting support.                          │
│                                                                                 │
│                                                         [Fix and Retry]        │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Rate Limit Error (429)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               ⏱️ Rate Limit Reached                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ You've reached the generation limit (5 per minute).                            │
│ Please wait before trying again.                                               │
│                                                                                 │
│ ⏳ Try again in: 0:45                                                           │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│ 💡 Tip: This limit prevents server overload and ensures quality generation.    │ │
│                                                                                 │
│                                          [Wait and Retry]  [View Usage Limits] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Server Error (500)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               ❌ Generation Failed                               │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Something went wrong during generation. Don't worry - your request wasn't      │
│ counted against your limit.                                                    │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│ This helps our team investigate the issue. Please try again or contact         │
│ support if the problem continues.                                              │
│                                                                                 │
│                                    [Retry Generation]  [Contact Support]       │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Network Error
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               🌐 Connection Lost                                │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Connection lost during generation. Checking if your proposal was created...    │
│                                                                                 │
│ Idempotency Key: gen_2025_01_gates_edu_v1_a7b9c4d2                             │
│                                                                                 │
│ 🔄 Auto-checking status...                                                      │
│                                                                                 │
│ We're using your idempotency key to check if the proposal was already          │
│ generated before the connection was lost.                                      │
│                                                                                 │
│                                            [Check Status]  [Start Over]        │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Loading State Recovery

### Page Refresh During Generation
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          🔄 Checking Generation Status                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Checking if your proposal was already created...                               │
│                                                                                 │
│ Idempotency Key: gen_2025_01_gates_edu_v1_a7b9c4d2                             │
│                                                                                 │
│ ████████████████████████████████████████████████                               │
│                                                                                 │
│ If a proposal with this key already exists, we'll take you to it.              │
│ Otherwise, you can restart the generation process.                             │
│                                                                                 │
│                                                        [Cancel Check]          │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Proposal Found (Idempotency)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          ✅ Proposal Already Generated                           │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ We found an existing proposal created with the same parameters:                │
│                                                                                 │
│ 📄 Education Innovation for Rural Kenya                                         │
│ Created: January 9, 2025 at 6:42 PM                                            │
│ Idempotency Key: gen_2025_01_gates_edu_v1_a7b9c4d2                             │
│                                                                                 │
│ This prevents duplicate proposals and saves your usage quota.                  │
│                                                                                 │
│                                                                                 │
│                            [View Proposal]  [Generate New Version]             │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Usage Quota Warning

### Near Limit Warning (During Generation)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          ⚠️ Usage Quota Warning                                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ This will be your 9th proposal this month (limit: 10).                        │
│                                                                                 │
│ After this generation, you'll have 1 proposal remaining.                      │
│ Your quota resets on February 1, 2025.                                        │
│                                                                                 │
│ Consider upgrading to Pro for unlimited proposals.                             │
│                                                                                 │
│                              [Continue Anyway]  [Upgrade Plan]  [Cancel]       │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Last Proposal Warning
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          🚨 Last Proposal This Month                            │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ This will be your final proposal for January (10/10).                         │
│                                                                                 │
│ After this generation, you'll need to wait until February 1st or upgrade      │
│ to continue creating proposals.                                                │
│                                                                                 │
│ Make sure this is the proposal you want to generate.                          │
│                                                                                 │
│                              [Generate Final]  [Upgrade Plan]  [Cancel]        │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Navigation Flows

### From Generation Screen
```
Generation Actions:
├── Generate Proposal → Progress tracking → Success/Error states
├── Save Draft → Store configuration locally → Dashboard
├── Change Selection → Back to Funding Picker
├── Cancel (during generation) → Confirm cancel → Dashboard
├── Edit Proposal (success) → Proposal Editor
├── Generate Another (success) → Back to Funding Picker
└── Retry (error states) → Restart generation process
```

### API Calls & Headers
```javascript
POST /api/proposals/generate
Headers:
  Authorization: Bearer {jwt_token}
  Idempotency-Key: {generated_or_custom_key}
  Content-Type: application/json

Body (Funding Opportunity):
{
  "funding_opportunity_id": 123,
  "custom_instructions": "Emphasize girls' education..."
}

Body (Custom Brief):
{
  "custom_brief": "XYZ Foundation seeks proposals...",
  "custom_instructions": "Focus on sustainability..."
}

Body (Quick Fields):
{
  "quick_fields": {
    "amount": 50000,
    "deadline": "2025-03-30",
    "focus_areas": ["education", "healthcare"],
    "eligibility": "Registered NGOs in East Africa"
  },
  "custom_instructions": "Highlight rural experience..."
}
```

---

## Mobile Responsive Layout

### Mobile Generation Config (< 768px)
```
┌─────────────────────────────────────────┐
│ ← Back    Generate Proposal             │
├─────────────────────────────────────────┤
│                                         │
│ ⏰ Est. time: 2-3 minutes               │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 🏛️ Gates Foundation                  │ │
│ │ Education Innovation Grant          │ │
│ │ $50K-$200K | Mar 15 | 92% match    │ │
│ │                                     │ │
│ │ [Change Selection]                  │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ Tone                                    │
│ ●Professional ○Conversational           │
│ ○Academic ○Persuasive                   │
│                                         │
│ Length                                  │
│ ○Standard ●Detailed ○Comprehensive      │
│                                         │
│ Custom Instructions                     │
│ ┌─────────────────────────────────────┐ │
│ │ [Textarea]                          │ │
│ │ Please emphasize our track record   │ │
│ │ in girls' education...              │ │ │
│ └─────────────────────────────────────┘ │
│ 📝 180/2000 chars                      │
│                                         │
│ [▼ Advanced Options]                    │
│                                         │
│ [Save Draft] [Generate Proposal]        │
│                                         │
└─────────────────────────────────────────┘
```

### Mobile Progress (< 768px)
```
┌─────────────────────────────────────────┐
│        🚀 Generating Proposal           │
├─────────────────────────────────────────┤
│                                         │
│ Gates Foundation                        │
│ Education Innovation                    │
│                                         │
│ ██████████████░░░░░░░░░░░░ 65%          │
│                                         │
│ 🤖 AI crafting content...               │
│                                         │
│ • Executive summary ✅                  │
│ • Project description ✅                │
│ • Budget planning 🔄                    │
│ • Impact metrics...                     │
│                                         │
│ ⏰ 45 seconds remaining                  │
│                                         │
│ Request: abc123def456                   │
│                                         │
│              [Cancel]                   │
│                                         │
└─────────────────────────────────────────┘
```


