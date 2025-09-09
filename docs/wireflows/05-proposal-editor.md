# Proposal Editor Wireflow

## User Journey: Proposal Editing & Enhancement
**Entry Point**: Generate Success → Edit Proposal OR Dashboard → View Proposal  
**Goal**: Review, edit, and enhance AI-generated proposal with section-based validation  
**API Endpoints**: `GET /api/proposals/{id}`, `PUT /api/proposals/{id}`, AI enhancement calls

---

## Main Editor Interface

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ ← Back to Dashboard          Education Innovation for Rural Kenya    [Auto-save ✅] │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ 📝 Education Innovation for Rural Kenya                         Status: Draft  │
│ Last saved: 2 minutes ago                                                      │
│                                                                                 │
│ ┌─ Section Tabs ─────────────────────────────────────────────────────────────┐ │
│ │ ✅ Executive Summary │ ⚠️ Project Desc │ ❌ Budget │ ✅ Timeline │ ⚠️ Impact │ │
│ │                     │               │          │            │             │ │
│ │ ✅ Organization Info │ 📎 Attachments │ 📊 Review │ 📤 Export  │ ⚙️ Settings │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ EXECUTIVE SUMMARY ─────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Validation: ✅ Section Complete (387 words - optimal length)                │ │
│ │                                                                             │ │
│ │ ┌─ Rich Text Editor ───────────────────────────────────────────────────────┐ │ │
│ │ │ [B] [I] [U] │ [H1] [H2] [H3] │ [•] [1.] │ [Link] [Quote] │ [↶] [↷]     │ │ │
│ │ ├─────────────────────────────────────────────────────────────────────────┤ │ │
│ │ │                                                                         │ │ │
│ │ │ **Transforming Education Through Innovation in Rural Kenya**            │ │ │
│ │ │                                                                         │ │ │
│ │ │ The Kenya Education Alliance proposes an innovative 18-month program   │ │ │
│ │ │ to revolutionize primary education delivery in rural communities       │ │ │
│ │ │ through digital learning solutions and community engagement            │ │ │
│ │ │ strategies. This initiative will directly impact 2,500 students        │ │ │
│ │ │ across 15 rural schools, with particular emphasis on improving         │ │ │
│ │ │ educational outcomes for girls.                                        │ │ │
│ │ │                                                                         │ │ │
│ │ │ **Problem Statement**                                                   │ │ │
│ │ │ Rural primary schools in Kenya face significant challenges including   │ │ │
│ │ │ limited access to quality educational resources, teacher shortages,    │ │ │
│ │ │ and inadequate infrastructure. Our baseline assessment reveals that    │ │ │
│ │ │ only 62% of rural students complete primary education, compared to     │ │ │
│ │ │ 84% in urban areas. Girls are disproportionately affected, with       │ │ │
│ │ │ completion rates of just 58%.                                          │ │ │
│ │ │                                                                         │ │ │
│ │ │ [Continue editing...]                                                   │ │ │
│ │ │                                                                         │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │ 📝 387 words │ Recommended: 250-400 words ✅                                │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ AI Assistance Panel ──────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ 🤖 AI Suggestions for Executive Summary                        [Minimize]   │ │
│ │                                                                             │ │
│ │ ✨ Strengthen impact statement                                              │ │
│ │ "Add specific percentage improvements expected"                             │ │
│ │                                                  [Preview] [Apply]         │ │
│ │                                                                             │ │
│ │ ✨ Enhance call to action                                                   │ │
│ │ "Include urgency and funding timeline"                                     │ │
│ │                                                  [Preview] [Apply]         │ │
│ │                                                                             │ │
│ │ 💡 Add quantifiable outcomes                                               │ │
│ │ "Include specific metrics and targets"                                     │ │
│ │                                                  [Preview] [Apply]         │ │
│ │                                                                             │ │
│ │                                           [Get More Suggestions]           │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│                        [Save Draft] [Finalize Section] [Export Preview]       │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Tab States & Validation

### Section Tab Indicators
```
Tab Validation Legend:
✅ Complete     - All requirements met, high quality score
⚠️ Needs Work   - Missing elements or quality improvements needed  
❌ Incomplete   - Required information missing
🔄 Processing   - AI enhancement in progress
📝 Draft        - User is actively editing
```

### Project Description Tab (Needs Work)
```
┌─ PROJECT DESCRIPTION ──────────────────────────────────────────────────────────┐
│                                                                                │
│ Validation: ⚠️ Section Needs Attention (3 issues to address)                   │
│                                                                                │
│ ❌ Missing methodology details                                                 │
│ ⚠️ Need more evidence/statistics                                              │
│ ⚠️ Innovation aspect could be stronger                                        │
│                                                                                │
│ ┌─ Rich Text Editor ─────────────────────────────────────────────────────────┐ │
│ │ [Content editing area with current project description]                   │ │
│ │                                                                            │ │
│ │ Our innovative approach combines digital learning platforms with          │ │
│ │ community engagement to address educational gaps in rural Kenya...        │ │
│ │                                                                            │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ 📝 1,247 words │ Recommended: 800-1,500 words ✅                               │
│                                                                                │
│ ┌─ Section Templates ────────────────────────────────────────────────────────┐ │
│ │ [+ Problem Statement] [+ Proposed Solution] [+ Methodology] [+ Innovation] │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Fix Gaps Panel ───────────────────────────────────────────────────────────┐ │
│ │                                                                            │ │
│ │ 🔧 AI-Powered Gap Analysis                                [Fix All Issues] │ │
│ │                                                                            │ │
│ │ Issue 1: Missing methodology details                                      │ │
│ │ Suggestion: Add step-by-step implementation approach                      │ │
│ │                                                   [Preview] [Apply Fix]   │ │
│ │                                                                            │ │
│ │ Issue 2: Need supporting evidence                                         │ │
│ │ Suggestion: Include research citations and baseline data                  │ │
│ │                                                   [Preview] [Apply Fix]   │ │
│ │                                                                            │ │
│ │ Issue 3: Strengthen innovation angle                                      │ │
│ │ Suggestion: Highlight unique aspects vs. traditional approaches           │ │
│ │                                                   [Preview] [Apply Fix]   │ │
│ │                                                                            │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

### Budget Tab (Incomplete)
```
┌─ BUDGET ───────────────────────────────────────────────────────────────────────┐
│                                                                                │
│ Validation: ❌ Section Incomplete (Budget total doesn't match grant amount)    │
│                                                                                │
│ ❌ Total budget: $87,500 │ Grant amount: $125,000 │ Difference: $37,500       │
│ ❌ Missing indirect costs calculation                                          │
│ ⚠️ Personnel costs seem low for scope                                         │
│                                                                                │
│ ┌─ Budget Table Editor ──────────────────────────────────────────────────────┐ │
│ │                                                                            │ │
│ │ Category                    Year 1      Year 2      Total       %         │ │
│ │ ├─────────────────────────────────────────────────────────────────────────┤ │
│ │ │ Personnel                 $35,000    $25,000     $60,000      69%       │ │
│ │ │ ├ Project Manager (0.5)   $20,000    $15,000     $35,000               │ │
│ │ │ ├ Field Coordinators (2)   $15,000    $10,000     $25,000               │ │
│ │ │                                                                         │ │
│ │ │ Equipment & Technology    $15,000     $2,000     $17,000      19%       │ │
│ │ │ ├ Tablets (50 units)      $12,000        $0     $12,000               │ │
│ │ │ ├ Solar charging stations  $3,000        $0      $3,000               │ │
│ │ │ ├ Maintenance                  $0     $2,000      $2,000               │ │
│ │ │                                                                         │ │
│ │ │ Travel & Transportation    $5,000     $3,000      $8,000       9%       │ │
│ │ │                                                                         │ │
│ │ │ Training & Capacity        $2,000       $500      $2,500       3%       │ │
│ │ │                                                                         │ │
│ │ │ Indirect Costs (15%)           $0        $0          $0       0% ❌     │ │
│ │ │                                                                         │ │
│ │ │ TOTAL                     $57,000    $30,500     $87,500     100%       │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │
│ │                                                                            │ │
│ │ [+ Add Category] [+ Add Line Item] [Recalculate] [Budget Templates]       │ │
│ │                                                                            │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ⚠️ Grant Amount Mismatch: Budget total ($87,500) is $37,500 less than         │
│    available grant funding ($125,000). Consider adding:                       │ │
│    • Indirect costs (typically 10-25%)                                        │
│    • Additional personnel or consultant fees                                  │
│    • Enhanced training programs                                               │
│    • Monitoring & evaluation costs                                            │
│                                                                                │
│ [Auto-Balance Budget] [Fix Common Issues]                                     │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## AI Enhancement Features

### "Fix Gaps" Enhancement Process
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          🔧 AI Gap Analysis Results                             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Found 3 opportunities to strengthen your Project Description section:          │
│                                                                                 │
│ ┌─ Enhancement 1: Add Methodology Details ──────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Current: "Our innovative approach combines digital learning..."             │ │
│ │                                                                             │ │
│ │ Enhanced: "Our innovative approach combines digital learning platforms     │ │
│ │ with community engagement through a three-phase methodology:               │ │
│ │                                                                             │ │
│ │ Phase 1: Community Assessment and Stakeholder Engagement (Months 1-3)     │ │
│ │ - Conduct baseline assessments in all 15 target schools                    │ │
│ │ - Engage with school committees, teachers, and parent groups               │ │
│ │ - Establish community learning champions network                           │ │
│ │                                                                             │ │
│ │ Phase 2: Digital Infrastructure Setup and Training (Months 4-8)           │ │
│ │ - Install solar-powered learning stations in each school                   │ │
│ │ - Train 45 teachers on digital learning methodologies..."                  │ │
│ │                                                                             │ │
│ │                                                    [Apply] [Modify] [Skip] │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ Enhancement 2: Add Supporting Evidence ──────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Suggested additions:                                                        │ │
│ │ • Cite recent UNESCO report on digital education gaps                      │ │
│ │ • Reference successful similar projects in Ghana and Nigeria               │ │
│ │ • Include baseline data from your 2023 impact assessment                   │ │
│ │                                                                             │ │
│ │ "According to UNESCO's 2024 Global Education Monitoring Report,           │ │
│ │ sub-Saharan Africa faces the largest education technology gap globally,    │ │
│ │ with only 28% of rural schools having access to basic digital resources.  │ │
│ │ Our approach builds on successful models implemented in Ghana's Northern   │ │
│ │ Region, which achieved a 23% improvement in learning outcomes..."          │ │
│ │                                                                             │ │
│ │                                                    [Apply] [Modify] [Skip] │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│                                      [Apply All] [Review Individual] [Cancel]  │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Q&A Pack Feature
```
┌─ Q&A PREPARATION PACK ─────────────────────────────────────────────────────────┐
│                                                                                │
│ 🎯 Common Funder Questions & AI-Generated Answers              [Expand All]   │
│                                                                                │
│ ▼ How will you measure the success of this project?                           │
│ ┌────────────────────────────────────────────────────────────────────────────┐ │
│ │ We will employ a comprehensive monitoring and evaluation framework with    │ │
│ │ both quantitative and qualitative metrics:                                │ │
│ │                                                                            │ │
│ │ Quantitative Indicators:                                                   │ │
│ │ • Student completion rates (target: increase from 62% to 78%)             │ │
│ │ • Learning assessment scores (target: 25% improvement)                    │ │
│ │ • Girls' enrollment and retention (target: 85% completion rate)           │ │
│ │ • Teacher confidence in digital tools (baseline vs. endline surveys)      │ │
│ │                                                                            │ │
│ │ Qualitative Measures:                                                      │ │
│ │ • Student engagement and motivation levels                                │ │
│ │ • Community satisfaction and ownership                                    │ │
│ │ • Teacher testimonials and case studies...                                │ │
│ │                                                                            │ │
│ │                                               [Edit] [Copy] [Add to Doc]  │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ▼ What makes your approach unique or innovative?                               │
│ ▼ How will this project be sustainable beyond the funding period?             │
│ ▼ What risks do you anticipate and how will you mitigate them?                │
│ ▼ How does this project align with national education priorities?             │
│ ▶ What partnerships do you have in place?                                     │
│ ▶ How will you ensure community ownership?                                    │
│                                                                                │
│                                           [Generate More Questions]           │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Version History & Collaboration

### Version History Sidebar
```
┌─ VERSION HISTORY ──────────────────────────────────────────────┐
│                                                               │
│ 🕐 Current: Draft v1.3                          [Compare]     │
│ Last edited: 5 minutes ago                                    │
│ Auto-saved: ✅ All changes saved                               │
│                                                               │
│ ─────────────────────────────────────────────────────────────  │
│                                                               │
│ 📌 v1.3 - Current                                5 min ago    │
│ Enhanced methodology section, added evidence                  │
│                                        [View] [Set as Base]  │
│                                                               │
│ 📝 v1.2                                        2 hours ago    │
│ Applied AI suggestions to executive summary                   │
│                              [View] [Restore] [Compare]      │
│                                                               │
│ 💾 v1.1 - Manual Save                         Yesterday       │
│ Initial review and customization                             │
│                              [View] [Restore] [Compare]      │
│                                                               │
│ 🤖 v1.0 - AI Generated                        2 days ago     │
│ Original AI-generated proposal                               │
│                              [View] [Restore] [Compare]      │
│                                                               │
│ ─────────────────────────────────────────────────────────────  │
│                                                               │
│ 💡 Tip: Major changes are auto-saved as versions              │
│    Click [Compare] to see what changed                       │
│                                                               │
│                                        [Create Manual Save]  │
│                                                               │
└───────────────────────────────────────────────────────────────┘
```

### Comments & Review Mode
```
┌─ PROJECT DESCRIPTION ──────────────────────────────────────────────────────────┐
│                                                     Review Mode: ON [Toggle]   │
│                                                                                │
│ ┌─ Rich Text Editor (Read-Only) ─────────────────────────────────────────────┐ │
│ │                                                                            │ │
│ │ Our innovative approach combines digital learning platforms with          │ │
│ │ community engagement to address educational gaps in rural Kenya.          │ │
│ │                                                          💬[2 comments]   │ │
│ │                                                                            │ │
│ │ The project will implement a three-phase methodology:                     │ │
│ │                                                                            │ │
│ │ Phase 1: Community Assessment and Stakeholder Engagement...               │ │
│ │                                                          💬[1 comment]    │ │
│ │                                                                            │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌─ Comments Panel ───────────────────────────────────────────────────────────┐ │
│ │                                                                            │ │
│ │ 💬 Sarah M. (Program Director)                           2 hours ago       │ │
│ │ "The three-phase approach looks solid. Can we add more details about      │ │
│ │ the community engagement strategy in Phase 1?"                            │ │
│ │                                                          [Reply] [Resolve] │ │
│ │                                                                            │ │
│ │ 💬 John K. (Finance Manager)                             1 hour ago        │ │
│ │ "Budget alignment looks good. The methodology section supports            │ │
│ │ our cost estimates well."                                                 │ │
│ │                                                          [Reply] [Resolve] │ │
│ │                                                                            │ │
│ │ ┌──────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ Add comment: [Type your comment here...]                            │ │ │
│ │ │                                               [Cancel] [Post Comment] │ │ │
│ │ └──────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                            │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Error States

### Auto-save Failed
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚠️ Auto-save Failed                                              │
│                                                                 │
│ Your changes couldn't be saved automatically.                  │
│ Request ID: abc123def456                                        │
│                                                                 │
│ Your work is stored locally and won't be lost. Please try      │
│ saving manually or check your connection.                      │
│                                                                 │
│                    [Save Now] [Check Connection] [Work Offline] │
└─────────────────────────────────────────────────────────────────┘
```

### AI Enhancement Error
```
┌─────────────────────────────────────────────────────────────────┐
│ ❌ AI Enhancement Unavailable                                    │
│                                                                 │
│ AI suggestions aren't available right now.                     │
│ Request ID: abc123def456                                        │
│                                                                 │
│ You can continue editing manually. AI features will be         │
│ restored automatically when the service is available.          │
│                                                                 │
│                                    [Continue Editing] [Retry]   │
└─────────────────────────────────────────────────────────────────┘
```

### Validation API Error
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚠️ Quality Check Unavailable                                     │
│                                                                 │
│ Proposal validation is temporarily unavailable.                │
│ Request ID: abc123def456                                        │
│                                                                 │
│ Your content is saved, but quality scores may be outdated.     │
│ Continue editing - validation will resume automatically.       │
│                                                                 │
│                                            [OK] [Retry Check]   │
└─────────────────────────────────────────────────────────────────┘
```

---

## Loading States

### Tab Loading
```
┌─ PROJECT DESCRIPTION ──────────────────────────────────────────────────────────┐
│                                                                                │
│ Loading section content... ⏳                                                  │
│                                                                                │
│ ████████████████████████████████████████████████████████████████                │
│                                                                                │
│ Please wait while we load your project description...                         │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

### AI Enhancement Loading
```
┌─ AI Assistance Panel ──────────────────────────────────────────────────────────┐
│                                                                                │
│ 🤖 Generating AI suggestions... ⏳                                              │
│                                                                                │
│ ████████████████████████████████████████████████████████████████                │
│                                                                                │
│ Analyzing your content for improvement opportunities...                       │ │
│                                                                                │
│                                                                [Cancel]       │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

### Version Comparison Loading
```
┌─ VERSION COMPARISON ───────────────────────────────────────────────────────────┐
│                                                                                │
│ Loading comparison between v1.2 and v1.3... ⏳                                 │
│                                                                                │
│ ████████████████████████████████████████████████████████████████                │
│                                                                                │
│ Analyzing changes and highlighting differences...                             │ │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Success States

### Section Completed
```
┌─────────────────────────────────────────────────────────────────┐
│ ✅ Executive Summary Complete!                                   │
│                                                                 │
│ Quality Score: 94% (Excellent)                                 │
│ All validation criteria met ✅                                  │
│                                                                 │
│ Your executive summary is well-structured and compelling.       │
│ Ready to move to the next section?                             │
│                                                                 │
│                              [Next Section] [Keep Editing]     │
└─────────────────────────────────────────────────────────────────┘
```

### AI Enhancement Applied
```
┌─────────────────────────────────────────────────────────────────┐
│ ✨ Enhancement Applied Successfully!                             │
│                                                                 │
│ Added methodology details to Project Description                │
│ Quality Score improved: 78% → 89%                              │
│                                                                 │
│ The section now includes detailed implementation phases         │
│ and clearer timelines. Changes have been auto-saved.           │
│                                                                 │
│                                      [Undo] [Continue Editing]  │
└─────────────────────────────────────────────────────────────────┘
```

### Full Proposal Finalized
```
┌─────────────────────────────────────────────────────────────────┐
│ 🎉 Proposal Finalized!                                          │
│                                                                 │
│ All sections complete with high quality scores:                │
│ • Executive Summary: 94% ✅                                     │
│ • Project Description: 89% ✅                                   │
│ • Budget: 91% ✅                                                │
│ • Timeline: 88% ✅                                              │
│ • Impact: 92% ✅                                                │
│                                                                 │
│ Your proposal is ready for submission!                         │
│                                                                 │
│                          [Export] [Share] [Submit] [Continue]   │
└─────────────────────────────────────────────────────────────────┘
```

---

## Navigation Flow

### From Editor Actions
```
Editor Actions:
├── Tab Navigation → Load different sections
├── Save Draft → Auto-save + manual save points
├── Finalize Section → Mark section complete + validation
├── Export Preview → Generate preview document
├── AI Enhancement → Enhancement workflow
├── Version History → Compare/restore versions
├── Comments → Collaboration mode
├── Back to Dashboard → Save changes + return to dashboard
└── Share/Export → Export panel
```

### API Calls Triggered
- **Section Load**: `GET /api/proposals/{id}` (specific sections)
- **Auto-save**: `PUT /api/proposals/{id}` (every 2 minutes)
- **Manual Save**: `PUT /api/proposals/{id}` (immediate)
- **AI Enhancement**: `POST /api/proposals/{id}/enhance` (section-specific)
- **Validation**: `GET /api/proposals/{id}/validate` (real-time)
- **Comments**: `POST /api/proposals/{id}/comments` (if collaboration enabled)

---

## Mobile Responsive Layout

### Mobile Editor (< 768px)
```
┌─────────────────────────────────────────┐
│ ← Back   Edit Proposal      💾 Saved    │
├─────────────────────────────────────────┤
│                                         │
│ Education Innovation for Rural Kenya    │
│ Status: Draft                           │
│                                         │
│ ┌─ Sections ────────────────────────────┐ │
│ │ ✅ Executive    ⚠️ Project     ❌ Budget │ │
│ │ ✅ Timeline     ⚠️ Impact      ✅ Org    │ │
│ └───────────────────────────────────────┘ │
│                                         │
│ Current: Executive Summary ✅           │
│ Quality Score: 94% (Excellent)          │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ [B][I][U] [H1][•] [Link] [↶][↷]     │ │
│ ├─────────────────────────────────────┤ │
│ │                                     │ │
│ │ **Transforming Education Through    │ │
│ │ Innovation in Rural Kenya**         │ │
│ │                                     │ │
│ │ The Kenya Education Alliance        │ │
│ │ proposes an innovative 18-month     │ │
│ │ program to revolutionize primary    │ │
│ │ education delivery...               │ │
│ │                                     │ │
│ │ [Continue editing on mobile]        │ │
│ │                                     │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ 📝 387 words | Target: 250-400 ✅       │
│                                         │
│ ┌─ Quick Actions ───────────────────────┐ │
│ │ [🤖 AI Help] [💡 Templates] [👁 Preview] │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ [Previous Section] [Next Section]       │
│                                         │
└─────────────────────────────────────────┘
```

### Mobile AI Assistant (< 768px)
```
┌─────────────────────────────────────────┐
│ 🤖 AI Assistant                    [×]  │
├─────────────────────────────────────────┤
│                                         │
│ 3 suggestions for Executive Summary:    │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ ✨ Strengthen Impact Statement       │ │
│ │                                     │ │
│ │ Add specific percentage             │ │
│ │ improvements expected               │ │
│ │                                     │ │
│ │ [Preview] [Apply] [Skip]            │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ ✨ Enhance Call to Action            │ │
│ │                                     │ │
│ │ Include urgency and funding         │ │
│ │ timeline details                    │ │
│ │                                     │ │
│ │ [Preview] [Apply] [Skip]            │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ [Apply All] [Get More] [Close]          │
│                                         │
└─────────────────────────────────────────┘
```




