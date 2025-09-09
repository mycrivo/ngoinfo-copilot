# Exports Panel Wireflow

## User Journey: Document Export & Management
**Entry Point**: Proposal Editor → Export OR Dashboard → Exports  
**Goal**: Generate professional documents and manage export history  
**API Endpoints**: `GET /api/proposals/{id}/export/{format}` (with rate limiting)

---

## Main Exports Interface

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ ← Back to Dashboard                  NGO Copilot - Exports                      │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Export your proposals as professional documents                                 │
│                                                                                 │
│ ┌─ Current Export Limit ─────────────────────────────────────────────────────────┐ │
│ │ Exports this minute: ████████░░ 3/10                                        │ │
│ │ 7 exports remaining • Limit resets every minute                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ New Export ───────────────────────────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Select Proposal                                                             │ │
│ │ ┌─────────────────────────────────────────────────────────────────────────┐ │ │
│ │ │ Education Innovation for Rural Kenya ▼                                  │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │ Format Selection                                                            │ │
│ │ ●PDF (Professional)    ○DOCX (Editable)                                     │ │
│ │                                                                             │ │
│ │ ┌─ PDF Preview ──────────────────────────────────────────────────────────┐ │ │
│ │ │ 📄 Letter-sized, professional formatting                               │ │ │
│ │ │ • Clean typography with your organization branding                     │ │ │
│ │ │ • Page numbers and headers/footers                                     │ │ │
│ │ │ • Table of contents and section dividers                               │ │ │
│ │ │ • Charts and tables optimized for print                                │ │ │
│ │ └─────────────────────────────────────────────────────────────────────────┘ │ │
│ │                                                                             │ │
│ │ ▼ Customization Options                                                     │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│ ┌─ Customization Options (Expanded) ─────────────────────────────────────────┐ │
│ │                                                                             │ │
│ │ Include Sections                                                            │ │
│ │ ☑️ Executive Summary        ☑️ Project Description                          │ │
│ │ ☑️ Budget Tables           ☑️ Timeline Charts                               │ │
│ │ ☑️ Organization Info       ☐ Appendices                                    │ │
│ │                                                                             │ │
│ │ Branding                                                                    │ │
│ │ Organization Logo: [Choose File] [kenya_alliance_logo.png] [Remove]        │ │
│ │                                                                             │ │
│ │ Header Text: Kenya Education Alliance                                      │ │
│ │ Footer Text: Confidential Proposal - Education Innovation Grant            │ │
│ │                                                                             │ │
│ │ Color Scheme: ○Professional  ●Modern  ○Classic                              │ │
│ │                                                                             │ │
│ │ Document Settings                                                           │ │
│ │ File Name: Kenya-Education-Innovation-2025-01-09                           │ │
│ │ Watermark: ○Draft  ●Final  ○Confidential  ○None                            │ │
│ │ ☐ Password Protection: [_______________]                                    │ │
│ │                                                                             │ │
│ │ ☑️ Save these settings as template                                          │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│                                    [Preview Export]  [Generate Export]         │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Export Generation Process

### Generation in Progress
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                             📄 Generating PDF Export                            │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Education Innovation for Rural Kenya                                            │
│                                                                                 │
│ ████████████████████████████████████████████████░░░ 85%                        │
│                                                                                 │
│ 🔄 Formatting document with your branding...                                    │
│                                                                                 │
│ • Content processing ✅                                                         │
│ • Layout formatting ✅                                                          │
│ • Branding application 🔄                                                       │
│ • Final PDF generation...                                                      │
│                                                                                 │
│ ⏰ Estimated time: 15 seconds                                                   │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│                                                        [Cancel Export]         │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Export Complete
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            ✅ Export Generated Successfully!                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ 📄 Kenya-Education-Innovation-2025-01-09.pdf                                    │
│                                                                                 │
│ File Size: 2.3 MB                                                              │
│ Pages: 12                                                                      │
│ Generated: January 9, 2025 at 6:45 PM                                          │
│                                                                                 │
│ ⬇️ Your download should start automatically                                      │
│                                                                                 │
│ ┌─ Document Preview ────────────────────────────────────────────────────────────┐ │
│ │ [Page 1 thumbnail] [Page 2 thumbnail] [Page 3 thumbnail] [...] [Page 12]   │ │
│ │                                                                             │ │
│ │ Quality Score: Professional ✅                                              │ │
│ │ • Clean formatting with organization branding                              │ │
│ │ • All sections included and properly formatted                             │ │
│ │ • Charts and tables optimized for print quality                            │ │
│ │                                                                             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│                      [Download Again]  [Export Another Format]  [Export Another] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Export History Table

```
┌─ Export History ───────────────────────────────────────────────────────────────┐
│                                                                                │
│ Access all your previous exports here. Files are stored for 30 days.          │
│                                                                                │
│ ┌─ Filters ──────────────────────────────────────────────────────────────────┐ │
│ │ Date Range: [Last 7 days ▼]  Format: [All ▼]  Proposal: [All ▼]  [Apply] │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ ┌────────────────────────────────────────────────────────────────────────────┐ │
│ │ Proposal Title                   │ Format │ Date       │ Size │ Downloads  │ │
│ ├──────────────────────────────────┼────────┼────────────┼──────┼────────────┤ │
│ │ Education Innovation for Rur...  │  📄PDF │ 2 hrs ago  │2.3MB │     3      │ │
│ │                                  │        │            │      │ [⬇][🔄][🗑] │ │
│ │                                  │        │            │      │            │ │
│ │ Education Innovation for Rur...  │ 📝DOCX │ 1 day ago  │1.8MB │     1      │ │
│ │                                  │        │            │      │ [⬇][🔄][🗑] │ │
│ │                                  │        │            │      │            │ │
│ │ Water Access Project Proposal    │  📄PDF │ 3 days ago │1.9MB │     5      │ │
│ │                                  │        │            │      │ [⬇][🔄][🗑] │ │
│ │                                  │        │            │      │            │ │
│ │ Healthcare Outreach Initiative   │  📄PDF │ 1 week ago │2.1MB │     2      │ │
│ │                                  │        │            │      │ [⬇][🔄][🗑] │ │
│ │                                  │        │            │      │            │ │
│ │ Community Development Grant      │ 📝DOCX │ 2 weeks ago│1.6MB │     0      │ │
│ │                                  │        │            │      │ [⬇][🔄][🗑] │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ Showing 5 of 12 exports                                    [Previous] [Next]  │
│                                                                                │
│ Legend: [⬇] Download  [🔄] Re-generate  [🗑] Delete                            │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Rate Limiting States

### Normal Usage (< 70%)
```
┌─ Current Export Limit ─────────────────────────────────────────────────────────┐
│ Exports this minute: ████████░░ 3/10                                        │
│ 7 exports remaining • Limit resets every minute                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Warning State (70-90%)
```
┌─ Current Export Limit ─────────────────────────────────────────────────────────┐
│ Exports this minute: ████████▲░ 8/10                                        │ 
│ ⚠️ 2 exports remaining • Please pace your requests                            │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Near Limit (90-99%)
```
┌─ Current Export Limit ─────────────────────────────────────────────────────────┐
│ Exports this minute: █████████▲ 9/10                                        │
│ ⚠️ 1 export remaining • Limit resets in 0:35                                 │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Rate Limit Reached (100%)
```
┌─ Current Export Limit ─────────────────────────────────────────────────────────┐
│ Exports this minute: ██████████ 10/10                                       │
│ 🚫 Export limit reached • Try again in 0:42                                  │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Error States

### Export Generation Failed
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ❌ Export Generation Failed                        │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ We couldn't generate your PDF export right now.                                │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│ This doesn't count against your export limit. Please try again or contact      │ │
│ support if the problem continues.                                              │
│                                                                                 │
│ Possible causes:                                                               │
│ • Document content is too large                                                │
│ • Temporary server issue                                                       │ │
│ • Logo file format not supported                                              │
│                                                                                 │
│                                [Retry Export]  [Try Different Format]  [Support] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Rate Limit Error (429)
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ⏱️ Export Limit Reached                            │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ You've reached the export limit (10 per minute).                               │
│ Please wait before trying again.                                               │
│                                                                                 │
│ ⏳ Try again in: 0:30                                                           │
│                                                                                 │
│ Request ID: abc123def456                                                        │
│                                                                                 │
│ 💡 Export limits prevent server overload and ensure quality document           │
│    generation. Upgrade your plan for higher limits.                           │
│                                                                                 │
│                                          [Wait and Retry]  [Upgrade Plan]      │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### File Not Found Error
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               📄 File No Longer Available                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ This export file is no longer available on our servers.                        │
│                                                                                 │
│ Export files are automatically deleted after 30 days to save storage.         │
│ Generate a new export to download the latest version of your proposal.         │
│                                                                                 │
│ File: Kenya-Education-Innovation-2024-12-09.pdf                                │
│ Original Date: December 9, 2024                                                │
│                                                                                 │
│                                            [Re-generate Export]  [View History] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Logo Upload Error
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               🖼️ Logo Upload Failed                             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ We couldn't process your logo file.                                            │
│                                                                                 │
│ Please check:                                                                  │
│ • File format: PNG, JPG, or SVG only                                          │
│ • File size: Maximum 2MB                                                      │
│ • Image dimensions: Minimum 200x200 pixels                                    │
│                                                                                 │
│ Current file: logo.bmp (unsupported format)                                   │
│                                                                                 │
│                                      [Choose Different File]  [Continue Without Logo] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Format-Specific Options

### PDF Format Selected
```
┌─ PDF Configuration ────────────────────────────────────────────────────────────┐
│                                                                                │
│ ●PDF (Professional)    ○DOCX (Editable)                                        │
│                                                                                │
│ ┌─ PDF Preview ──────────────────────────────────────────────────────────────┐ │
│ │ 📄 Professional PDF Document                                               │ │
│ │ • Letter-sized pages (8.5" x 11")                                          │ │
│ │ • Optimized for printing and digital sharing                               │ │
│ │ • Embedded fonts for consistent display                                    │ │
│ │ • Searchable text and bookmarks                                            │ │
│ │ • High-quality charts and graphics                                         │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ PDF-Specific Options:                                                          │
│ ☑️ Include table of contents with page numbers                                 │
│ ☑️ Add page headers and footers                                                │
│ ☑️ Optimize for print quality                                                  │
│ ☐ Enable copy protection (read-only)                                          │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

### DOCX Format Selected
```
┌─ DOCX Configuration ───────────────────────────────────────────────────────────┐
│                                                                                │
│ ○PDF (Professional)    ●DOCX (Editable)                                        │
│                                                                                │
│ ┌─ DOCX Preview ─────────────────────────────────────────────────────────────┐ │
│ │ 📝 Microsoft Word Document                                                 │ │
│ │ • Fully editable in Word, Google Docs, or LibreOffice                     │ │
│ │ • Preserves formatting, styles, and layout                                │ │
│ │ │ Tables and charts remain editable                                      │ │
│ │ • Comments and track changes enabled                                       │ │
│ │ • Compatible with collaboration tools                                      │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ DOCX-Specific Options:                                                         │
│ ☑️ Enable track changes for collaborative editing                              │
│ ☑️ Include style definitions for consistent formatting                         │
│ ☐ Add placeholder comments for reviewer feedback                              │ │
│ ○ Template: Standard  ●Professional  ○Grant-specific                          │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

---

## Loading States

### Export History Loading
```
┌─ Export History ───────────────────────────────────────────────────────────────┐
│                                                                                │
│ Loading your export history... ⏳                                              │
│                                                                                │
│ ┌────────────────────────────────────────────────────────────────────────────┐ │
│ │ ████████████████████████████████████████████████████████████████████████   │ │
│ │ ████████████████████████████████████████████████████████████████████████   │ │
│ │ ████████████████████████████████████████████████████████████████████████   │ │
│ │ ████████████████████████████████████████████████████████████████████████   │ │
│ └────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                │
│ Please wait while we retrieve your export files...                            │
│                                                                                │
└────────────────────────────────────────────────────────────────────────────────┘
```

### Download Preparation
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              📥 Preparing Download                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Kenya-Education-Innovation-2025-01-09.pdf                                       │
│                                                                                 │
│ ████████████████████████████████████████████████████████████████                │
│                                                                                 │
│ 🔄 Retrieving file from secure storage...                                       │
│                                                                                 │
│ File Size: 2.3 MB                                                              │
│ Download will start automatically when ready.                                  │
│                                                                                 │
│                                                        [Cancel]                │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Success States

### Download Complete
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ✅ Download Complete!                              │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ 📄 Kenya-Education-Innovation-2025-01-09.pdf                                    │
│                                                                                 │
│ File downloaded successfully to your Downloads folder.                         │
│ Size: 2.3 MB • Pages: 12                                                       │
│                                                                                 │
│ 📧 Email this proposal:                                                         │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ To: grants@gatesfoundation.org                                             │ │
│ │ Subject: Education Innovation Grant Application - Kenya Education Alliance  │ │
│ │                                                          [Send] [Compose]   │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
│                                                                                 │
│                            [Download Again]  [Export Another]  [Back to History] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### Re-generation Complete
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            ✅ Export Re-generated Successfully!                  │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ Your proposal export has been updated with the latest content:                 │
│                                                                                 │
│ 📄 Kenya-Education-Innovation-2025-01-09-v2.pdf                                 │
│                                                                                 │
│ Changes from previous version:                                                  │
│ • Updated project timeline                                                     │
│ • Revised budget calculations                                                  │ │
│ • Enhanced impact metrics                                                      │
│ • Fresh formatting with current branding                                      │
│                                                                                 │
│ Previous version remains in your history for reference.                        │
│                                                                                 │
│                                          [Download New Version]  [Compare Versions] │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## Navigation Flow

### From Exports Panel
```
Exports Panel Actions:
├── Generate Export → Export process → Download/Success
├── Download (history) → File retrieval → Download complete
├── Re-generate → Fresh export generation → Updated file
├── Delete → Confirmation → Remove from history
├── Filter History → Updated table view
├── Preview Export → Quick preview modal
├── Email/Share → External sharing options
└── Back to Dashboard → Return to main dashboard
```

### API Calls Triggered
- **Export Generation**: `GET /api/proposals/{id}/export/{format}` (with rate limiting)
- **Export History**: `GET /api/exports/history?user_id={id}&filters={params}`
- **File Download**: Direct download link from secure storage
- **Re-generation**: `GET /api/proposals/{id}/export/{format}?force_refresh=true`
- **Delete Export**: `DELETE /api/exports/{export_id}`

---

## Mobile Responsive Layout

### Mobile Export Creation (< 768px)
```
┌─────────────────────────────────────────┐
│ ← Back     Export Proposal              │
├─────────────────────────────────────────┤
│                                         │
│ Exports: ████████░░ 3/10               │
│ 7 remaining this minute                 │
│                                         │
│ Select Proposal                         │
│ ┌─────────────────────────────────────┐ │
│ │ Education Innovation for Kenya ▼    │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ Format                                  │
│ ●PDF   ○DOCX                           │
│                                         │
│ ┌─ PDF Preview ─────────────────────┐   │
│ │ 📄 Professional format            │   │
│ │ • Letter-sized pages              │   │
│ │ • Print-optimized                 │   │
│ │ • Embedded branding               │   │
│ └───────────────────────────────────┘   │
│                                         │
│ [▼ Options]                             │
│                                         │
│ Include Sections                        │
│ ☑️ Executive  ☑️ Project  ☑️ Budget     │
│ ☑️ Timeline   ☑️ Impact   ☑️ Org Info   │
│                                         │
│ File Name                               │
│ ┌─────────────────────────────────────┐ │
│ │ Kenya-Education-Innovation-2025     │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ [Preview] [Generate Export]             │
│                                         │
└─────────────────────────────────────────┘
```

### Mobile Export History (< 768px)
```
┌─────────────────────────────────────────┐
│ ← Back     Export History               │
├─────────────────────────────────────────┤
│                                         │
│ [Last 7 days ▼] [All formats ▼]        │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 📄 Education Innovation for Kenya   │ │
│ │ PDF • 2 hours ago • 2.3MB          │ │
│ │ Downloads: 3                        │ │
│ │ [⬇] [🔄] [🗑]                       │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 📝 Education Innovation for Kenya   │ │
│ │ DOCX • 1 day ago • 1.8MB           │ │
│ │ Downloads: 1                        │ │
│ │ [⬇] [🔄] [🗑]                       │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 📄 Water Access Project            │ │
│ │ PDF • 3 days ago • 1.9MB           │ │
│ │ Downloads: 5                        │ │
│ │ [⬇] [🔄] [🗑]                       │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ [Load More Exports]                     │
│                                         │
└─────────────────────────────────────────┘
```




