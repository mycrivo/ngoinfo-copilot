# NGOInfo Copilot Grantpilot Generator - QA Testing Guide

## Overview
This document outlines manual testing steps for the Grantpilot generator feature in the NGOInfo Copilot WordPress plugin.

## Prerequisites
- WordPress site with NGOInfo Copilot plugin installed
- MemberPress plugin installed and active
- Admin access to configure plugin settings
- Test user accounts with different MemberPress membership levels

## Test Environment Setup

### 1. Plugin Configuration
1. Go to **Settings > NGOInfo Copilot**
2. Configure the following settings:
   - **API Base URL**: `https://grantpilot.ngoinfo.org`
   - **JWT Issuer**: `ngoinfo-wp`
   - **JWT Audience**: `grantpilot-api`
   - **JWT Secret**: Generate a strong secret (32+ characters with mixed case, numbers, symbols)
   - **MemberPress Free Plan IDs**: `2268`
   - **MemberPress Growth Plan IDs**: `2259,2271`
   - **MemberPress Impact Plan IDs**: `2272,2273`
   - **HTTP Timeout**: `60`
   - **Rate Limit Cooldown**: `60`

### 2. MemberPress Setup
1. Create MemberPress memberships with the configured IDs:
   - Free Plan: ID 2268
   - Growth Plan: IDs 2259, 2271
   - Impact Plan: IDs 2272, 2273
2. Create test users:
   - User A: Active Growth or Impact member
   - User B: Free plan member
   - User C: Non-member or different membership level
   - User D: Logged out user

## Test Cases

### Test Case 1: Successful Proposal Generation
**Objective**: Verify that active Grantpilot members can generate proposals successfully.

**Steps**:
1. Log in as User A (Growth/Impact member)
2. Navigate to a page containing `[ngoinfo_copilot_generate]` shortcode
3. Fill out the form with valid data:
   - Donor Organization: "Test Foundation"
   - Theme/Focus Area: "Education"
   - Country: "United States"
   - Project Title: "Test Education Project"
   - Budget: "50000"
   - Duration: "12"
4. Click "Generate Proposal"
5. Wait for the request to complete

**Expected Results**:
- Form shows loading state with spinner
- Success message appears with proposal ID
- Proposal preview is displayed
- Form is cleared for next use
- Generation appears in user history (check `[ngoinfo_copilot_usage]` shortcode)

### Test Case 2: Non-Member Access Denied
**Objective**: Verify that non-members cannot access the generator.

**Steps**:
1. Log in as User C (non-member)
2. Navigate to a page containing `[ngoinfo_copilot_generate]` shortcode
3. Attempt to fill out and submit the form

**Expected Results**:
- Form shows message: "Grantpilot membership required. Please upgrade your membership to access this feature."
- Form fields are not displayed

### Test Case 3: Logged Out User Access Denied
**Objective**: Verify that logged-out users cannot access the generator.

**Steps**:
1. Log out of WordPress
2. Navigate to a page containing `[ngoinfo_copilot_generate]` shortcode
3. Attempt to access the form

**Expected Results**:
- Form shows message: "Please log in to use Grantpilot."
- Form fields are not displayed

### Test Case 4: Rate Limiting
**Objective**: Verify that rate limiting prevents rapid successive requests.

**Steps**:
1. Log in as User A (Growth/Impact member)
2. Generate a proposal successfully (Test Case 1)
3. Immediately attempt to generate another proposal

**Expected Results**:
- AJAX request returns error: `{ ok:false, code:'rate', msg:'Please wait a moment before trying again.' }`
- Error message is displayed to user
- After cooldown period (60 seconds), generation is allowed again

### Test Case 5: Form Validation
**Objective**: Verify that form validation works correctly.

**Steps**:
1. Log in as User A (Growth/Impact member)
2. Navigate to the generator form
3. Test various invalid inputs:
   - Empty required fields
   - Budget with negative values
   - Duration outside 1-60 range
   - Text fields exceeding 200 characters

**Expected Results**:
- HTML5 validation prevents submission of invalid forms
- Server-side validation catches any bypassed validation
- Appropriate error messages are displayed

### Test Case 6: API Error Handling
**Objective**: Verify that API errors are handled gracefully.

**Steps**:
1. Configure plugin with invalid API Base URL (e.g., `https://invalid-api.example.com`)
2. Log in as User A (Growth/Impact member)
3. Attempt to generate a proposal

**Expected Results**:
- Error message: "Service error. Please try again later."
- Error is logged in diagnostics
- User can retry after fixing configuration

### Test Case 7: JWT Authentication Error
**Objective**: Verify that JWT authentication errors are handled.

**Steps**:
1. Configure plugin with incorrect JWT secret
2. Log in as User A (Growth/Impact member)
3. Attempt to generate a proposal

**Expected Results**:
- Error message: "Authentication failed. Please contact support."
- Error is logged in diagnostics
- Admin can check diagnostics for troubleshooting

### Test Case 8: Settings Not Configured
**Objective**: Verify behavior when required settings are missing.

**Steps**:
1. Clear API Base URL or JWT Secret in settings
2. Log in as User A (Growth/Impact member)
3. Navigate to generator form

**Expected Results**:
- Admin users see: "Admin Notice: Grantpilot settings are not configured..."
- Regular users see: "Service temporarily unavailable. Please try again later."

### Test Case 9: Diagnostics Functionality
**Objective**: Verify that diagnostics capture generation attempts.

**Steps**:
1. Log in as admin
2. Go to **Settings > NGOInfo Copilot > Diagnostics**
3. Generate a proposal (as User A)
4. Check diagnostics section

**Expected Results**:
- Last API call status shows recent attempt
- Request and response details are captured
- Copy JSON button works for support purposes
- Sensitive data is redacted in logs

### Test Case 10: Asset Loading Optimization
**Objective**: Verify that assets are only loaded when needed.

**Steps**:
1. Create a page without the shortcode
2. Visit the page and check page source
3. Create a page with the shortcode
4. Visit the page and check page source

**Expected Results**:
- Generator assets (CSS/JS) only load on pages with shortcode
- Basic plugin assets load on all pages
- No unnecessary asset loading

## Error Scenarios

### Network Timeout
- Test with very slow network connection
- Verify timeout handling and user feedback

### Server Errors (5xx)
- Test with API returning 500, 502, 503, 504 errors
- Verify appropriate error messages

### Invalid JSON Response
- Test with API returning malformed JSON
- Verify graceful error handling

## Performance Testing

### Load Testing
- Generate multiple proposals in succession
- Monitor server response times
- Check for memory leaks or performance degradation

### Concurrent Users
- Test with multiple users generating proposals simultaneously
- Verify rate limiting works per user
- Check for race conditions

## Security Testing

### XSS Prevention
- Test form inputs with script tags
- Verify all output is properly escaped

### CSRF Protection
- Verify nonce validation works
- Test with invalid or missing nonces

### Input Sanitization
- Test with various malicious inputs
- Verify server-side validation

## Browser Compatibility

### Desktop Browsers
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

### Mobile Browsers
- iOS Safari
- Android Chrome
- Test responsive design

## Accessibility Testing

### Keyboard Navigation
- Verify all form elements are keyboard accessible
- Test tab order and focus management

### Screen Reader Compatibility
- Test with screen reader software
- Verify proper labels and ARIA attributes

### Color Contrast
- Verify sufficient contrast ratios
- Test with high contrast mode

## Regression Testing

### Existing Functionality
- Verify `[ngoinfo_copilot_usage]` shortcode still works
- Test existing settings and health checks
- Ensure no breaking changes to existing features

### Plugin Updates
- Test plugin activation/deactivation
- Verify settings persistence across updates

## Reporting Issues

When reporting issues, include:
1. Test case that failed
2. Steps to reproduce
3. Expected vs actual results
4. Browser and version
5. WordPress version
6. Plugin version
7. Screenshots or error messages
8. Diagnostics JSON (if applicable)

## Success Criteria

The Grantpilot generator is considered ready for production when:
- All test cases pass
- No critical security vulnerabilities
- Performance is acceptable under normal load
- Error handling is robust and user-friendly
- Documentation is complete and accurate
