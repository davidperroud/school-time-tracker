# Password Recovery System Implementation

**Started:** 2026-02-09
**Status:** Planning

## Overview

Add a password recovery system to the School Time Tracker application, allowing users to reset forgotten passwords via a secure token-based mechanism with PIN fallback.

## Scope

### In Scope
- Password reset request page (username input)
- Token generation and storage (unique, expires in 1 hour)
- Reset link page (new password input)
- PIN code fallback for environments without email
- Database table for password reset tokens
- Integration with login page
- Multi-language support (fr, en, de, it)

### Out of Scope
- Email sending via SMTP (using PIN display instead)
- Password strength enforcement beyond existing validation
- Account lockout policies
- Two-factor authentication

## Implementation Plan

### Phase 1: Database Schema ✅ DONE
Create the password_resets table to store reset tokens.

**Tasks:**
- [x] Design and create `password_resets` table with columns:
  - [x] `id` (INTEGER PRIMARY KEY)
  - [x] `user_id` (INTEGER, FK to users)
  - [x] `token` (TEXT, UNIQUE)
  - [x] `pin` (TEXT, for fallback)
  - [x] `expires_at` (DATETIME)
  - [x] `used_at` (DATETIME, nullable)
  - [x] `created_at` (DATETIME)
- [x] Add indexes for efficient lookups
- [x] Write migration SQL or update init_db.php
- [x] Test table creation

**Success Criteria:**
- [x] Table exists with correct schema
- [x] Foreign key constraint to users table works
- [x] Indexes allow fast token lookups

### Phase 2: User Model Extensions
Add password reset methods to User class.

**Tasks:**
- [ ] Add `createPasswordResetToken($userId)` method
  - Generate unique 32-character token
  - Generate 6-digit PIN
  - Store in database with 1-hour expiration
  - Return array with token and PIN info
- [ ] Add `validateResetToken($token)` method
  - Look up token in database
  - Check expiration
  - Check if already used
  - Return user_id if valid, false otherwise
- [ ] Add `markTokenAsUsed($token)` method
- [ ] Add `cleanupExpiredTokens()` method
- [ ] Write unit tests for new methods

**Success Criteria:**
- Token generation creates unique tokens
- Validation checks expiration and usage
- Token can be marked as used

### Phase 3: Request Page
Create the password reset request page.

**Tasks:**
- [ ] Create `public/reset_request.php`
  - Username input field
  - Language selection
  - Submit button
  - Error/success message display
- [ ] Handle form submission
  - Validate username exists
  - Create reset token
  - Display PIN (fallback, no email)
  - Show reset link option
- [ ] Add translations for:
  - "Forgot your password?"
  - "Enter your username"
  - "Send reset link"
  - "Reset code: {PIN}"
  - "Enter the code from your email or above"
- [ ] Style with Tailwind CSS to match existing login.php
- [ ] Add link from login.php

**Success Criteria:**
- Page loads and displays correctly
- Form validates username exists
- Token created and PIN displayed
- Link to reset page works

### Phase 4: Reset Page
Create the password reset page with token/PIN validation.

**Tasks:**
- [ ] Create `public/reset_password.php`
  - Token or PIN input field
  - New password field
  - Confirm password field
  - Submit button
- [ ] Handle form submission
  - Validate token OR PIN
  - Validate password meets requirements (6+ chars)
  - Update user password
  - Mark token as used
  - Redirect to login with success message
- [ ] Add translations
- [ ] Style with Tailwind CSS
- [ ] Handle expired/invalid tokens

**Success Criteria:**
- Token validation works
- PIN validation works
- Password updated in database
- Token marked as used
- Success message displayed

### Phase 5: Login Integration
Integrate password recovery flow with login page.

**Tasks:**
- [ ] Add "Forgot your password?" link below login form
- [ ] Add translated text for the link
- [ ] Test navigation between pages
- [ ] Add flash messages for success/error feedback

**Success Criteria:**
- Link visible on login page
- Navigation works correctly

### Phase 6: Cleanup & Security
Add cleanup and security hardening.

**Tasks:**
- [ ] Implement `cleanupExpiredTokens()` method
- [ ] Add cron job or scheduled task to call cleanup
- [ ] Rate limiting on reset requests (max 3 per hour per IP)
- [ ] Security audit of new code
- [ ] Documentation of the password reset flow

**Success Criteria:**
- Expired tokens are cleaned up
- Rate limiting prevents abuse
- No security vulnerabilities found

## Dependencies

- PHP 8.0+
- SQLite 3
- Existing User class
- Existing Translation class
- Existing login.php styling

## Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| No SMTP server for emails | Medium | PIN display fallback implemented |
| Token brute force | Medium | Rate limiting, token complexity |
| Expired tokens not cleaned | Low | Automated cleanup job |
| Race conditions | Low | Database transactions |

## Files to Create/Modify

**New Files:**
- `public/reset_request.php`
- `public/reset_password.php`

**Modified Files:**
- `src/User.php` - Add reset methods
- `public/login.php` - Add forgot password link
- `lang/fr.json` - Add French translations
- `lang/en.json` - Add English translations
- `lang/de.json` - Add German translations
- `lang/it.json` - Add Italian translations
- `init_db.php` or migration - Create password_resets table

## Testing Plan

- [ ] Test token generation (unique, correct format)
- [ ] Test token validation (valid, expired, used)
- [ ] Test password reset flow end-to-end
- [ ] Test PIN fallback flow
- [ ] Test invalid username handling
- [ ] Test rate limiting
- [ ] Test multi-language support
- [ ] Test database cleanup

---
*Plan created: 2026-02-09*
