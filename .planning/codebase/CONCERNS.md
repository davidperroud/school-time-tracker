# Codebase Concerns

**Analysis Date:** 2026-02-09

## Security Considerations

**Password Storage:**
- Risk: Uses `password_hash()` with PASSWORD_DEFAULT (currently bcrypt)
- Files: `src/User.php` lines 14, 90
- Current mitigation: Strong hashing algorithm
- Recommendations: 
  - Consider explicitly specifying `PASSWORD_BCRYPT` for forward compatibility
  - Add password strength validation (minimum length check exists at 6 chars)

**Session Security:**
- Risk: Session cookie configuration uses dynamic HTTPS detection
- Files: `src/Session.php` line 10
- Current mitigation: `httponly`, `samesite=Strict` flags set
- Recommendations: 
  - Hardcode `session.cookie_secure` based on deployment environment
  - Consider adding session fingerprinting for additional security

**SQL Injection Prevention:**
- Files: `src/Database.php`, `src/User.php`, `src/ApiController.php`
- Current mitigation: PDO prepared statements with `?` placeholders
- Issue: `ApiController::getDateCondition()` interpolates variables into SQL strings (lines 337-357)
- Risk: Potential SQL injection if date parameters are malformed
- Fix approach: Refactor to use parameterized queries

**XSS Prevention:**
- Files: `public/index.php`, `public/login.php`, `public/setup.php`
- Current mitigation: `htmlspecialchars()` used in templates
- Risk: JavaScript `translations` object not escaped (line 274 in `public/index.php`)
- Fix approach: Ensure all dynamic content is escaped

## Tech Debt

**API Structure:**
- Issue: No dedicated request/response classes
- Files: `src/ApiController.php`
- Impact: Hard to add validation, versioning, or middleware
- Fix approach: Create Request/Response/DTO classes

**Missing Input Validation:**
- Issue: Server-side validation is minimal
- Files: `public/index.php` lines 17-217
- Impact: Malformed data could reach database
- Fix approach: Add server-side validation layer

**Database Schema Management:**
- Issue: Migration files exist but no migration runner
- Files: `init.sql`, `add_admin_column.sql`
- Impact: Schema changes must be applied manually
- Fix approach: Implement database migration system

**Frontend JavaScript:**
- Issue: 969-line `app.js` file with mixed concerns
- Files: `public/js/app.js`
- Impact: Difficult to maintain, no code organization
- Fix approach: Modularize into separate files by feature

## Known Limitations

**No Rate Limiting:**
- Files: All public entry points
- Issue: No protection against brute force attacks
- Impact: Login page vulnerable to password guessing
- Fix approach: Implement rate limiting on authentication

**No CSRF Protection:**
- Files: All form handlers in `public/index.php`
- Issue: Forms lack CSRF tokens
- Impact: Vulnerable to cross-site request forgery
- Fix approach: Add CSRF token generation and validation

**Single Admin Requirement:**
- Files: `public/setup.php`, `public/login.php`
- Issue: Self-registration disabled, only admins can create users
- Impact: No user self-service capabilities
- Fix approach: Consider admin-initiated user creation workflow

**No Data Export Beyond PDF:**
- Files: `src/ApiController.php`, `public/export_pdf.php`
- Issue: Only PDF export available
- Impact: Users cannot export raw data (CSV, JSON)
- Fix approach: Add CSV/JSON export endpoints

## Performance Concerns

**Session Regeneration Frequency:**
- Files: `src/Session.php` lines 17-22
- Issue: Session ID regenerated every 5 minutes on every request
- Impact: Unnecessary I/O for session files, potential concurrent access issues
- Fix approach: Check time delta before regenerating

**No Database Indexes on Foreign Keys:**
- Files: `init.sql`
- Issue: Foreign key columns not indexed
- Impact: Slow queries on `time_entries.subject_id`, `subjects.category_id`
- Fix approach: Add indexes on foreign key columns

**Translation Loading:**
- Files: `src/Translation.php`
- Issue: Language file loaded on every Translation instantiation
- Impact: Unnecessary I/O for each API request
- Fix approach: Cache translations or use singleton pattern

## Test Coverage Gaps

**Untested Areas:**
- `src/ApiController.php` - No unit tests for API endpoints
- `src/Translation.php` - No tests for language fallback logic
- `public/js/app.js` - No JavaScript tests
- Database queries - No integration tests

**Risk:** Changes to API or business logic could break functionality unnoticed

## Fragile Areas

**Date Handling:**
- Files: `src/ApiController.php` lines 337-357
- Why fragile: String interpolation of dates into SQL, timezone assumptions
- Safe modification: Use DateTime objects, parameterized queries
- Test coverage: Manual browser testing only

**PDF Generation:**
- Files: `src/ApiController.php` lines 193-312
- Why fragile: Complex TCPDF method calls, hardcoded styling
- Safe modification: Extract to dedicated PDF service class
- Test coverage: Manual visual verification only

**JavaScript DOM Manipulation:**
- Files: `public/js/app.js`
- Why fragile: Direct DOM queries, no virtual DOM or framework
- Safe modification: Use data attributes consistently, add null checks

---

*Concerns audit: 2026-02-09*
