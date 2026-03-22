# Architecture

**Analysis Date:** 2026-02-09

## Pattern Overview

**Overall:** Server-Side Rendered (SSR) PHP with Vanilla JavaScript Frontend

**Key Characteristics:**
- Traditional PHP application with embedded HTML templates
- RESTful API endpoints via `public/api.php` for dynamic content
- Session-based authentication with secure cookie configuration
- SQLite database with PDO abstraction layer
- Multi-language support with JSON-based translation files
- PDF report generation using TCPDF library
- Vanilla JavaScript with Chart.js for data visualization

## Layers

**Presentation Layer (Public Assets):**
- Purpose: Serve HTML, CSS, and JavaScript to users
- Location: `public/`
- Contains:
  - `index.php` - Main application page with embedded PHP/HTML
  - `login.php` - Authentication page
  - `logout.php` - Session termination
  - `setup.php` - Initial admin account creation
  - `api.php` - REST API endpoint dispatcher
  - `export_pdf.php` - PDF report generation
  - `translations.php` - Translation JSON endpoint
  - `js/app.js` - Client-side application logic
  - `css/style.css` - Styling
- Depends on: Session, Auth, Database, Translation
- Used by: All browser clients

**Business Logic Layer (Src Classes):**
- Purpose: Core application functionality
- Location: `src/`
- Contains:
  - `Database.php` - PDO singleton with query builders
  - `Auth.php` - Authentication facade
  - `Session.php` - Session management with security features
  - `User.php` - User CRUD operations
  - `ApiController.php` - API request handlers
  - `Translation.php` - i18n service
- Depends on: PDO, PHP sessions
- Used by: Public entry points

**Data Access Layer:**
- Purpose: Database abstraction and queries
- Location: `src/Database.php`
- Contains: PDO wrapper with custom query methods
- Depends on: SQLite database file
- Used by: All business logic classes

## Data Flow

**Authentication Flow:**

1. User accesses `public/index.php`
2. `auth.php` middleware checks authentication
3. If no users exist → redirect to `public/setup.php`
4. If not authenticated → redirect to `public/login.php`
5. User submits credentials via POST to `public/login.php`
6. `Auth->authenticate()` validates against `users` table
7. `Session->login()` stores user data in session
8. Session regenerated every 5 minutes for security
9. Redirect back to `public/index.php`

**API Request Flow:**

1. Client POSTs to `public/api.php` with `action` parameter
2. `ApiController->handleRequest()` routes to method
3. Method queries `Database` for data
4. Results encoded as JSON and returned
5. Client JavaScript updates DOM

**Time Entry Creation Flow:**

1. User selects subject, duration, date on `public/index.php`
2. Form POSTs to `public/index.php` with `action=add_entry`
3. `public/index.php` processes form (no auth check in file itself, handled by `auth.php`)
4. `Database->execute()` inserts into `time_entries` table
5. If AJAX request → return JSON response
6. Else → redirect to page
7. JavaScript reloads dashboard via `api.php?action=summary`

**PDF Export Flow:**

1. User configures report period on reports tab
2. Clicks "Export PDF" button
3. JavaScript opens `public/export_pdf.php` in new window
4. `export_pdf.php` includes `ApiController`
5. `ApiController->generatePDF()` creates TCPDF document
6. PDF streamed to browser as download

## Key Abstractions

**Database Singleton Pattern:**
- Purpose: Single database connection instance
- Examples: `src/Database.php`
- Pattern: Classic singleton with private constructor

**Translation Service:**
- Purpose: Multi-language support
- Examples: `src/Translation.php`
- Pattern: Service with dot-notation key lookup

**API Controller Strategy Pattern:**
- Purpose: Handle different API actions
- Examples: `src/ApiController.php`
- Pattern: Switch-based action routing

## Entry Points

**Main Application:**
- Location: `public/index.php`
- Triggers: Browser navigation to app root
- Responsibilities:
  - Load authentication state
  - Process form submissions
  - Render HTML with embedded PHP
  - Include JavaScript application

**Authentication:**
- Location: `public/login.php`
- Triggers: Unauthenticated user access
- Responsibilities:
  - Display login form
  - Validate credentials
  - Create session on success

**Setup:**
- Location: `public/setup.php`
- Triggers: First-time application access (no users exist)
- Responsibilities:
  - Create initial admin user
  - Auto-login after creation

**API:**
- Location: `public/api.php`
- Triggers: AJAX requests from JavaScript
- Responsibilities:
  - Route action parameter
  - Return JSON responses

**PDF Export:**
- Location: `public/export_pdf.php`
- Triggers: Report export button click
- Responsibilities:
  - Generate PDF report
  - Stream as download

## Error Handling

**Strategy:** Graceful degradation with user-friendly messages

**Patterns:**
- PDOException caught in Database, returns false from query methods
- JSON API responses include `success: false` and `error` message
- PHP fatal errors show French user messages
- Form validation errors displayed above forms
- Session expiration redirects to login

## Cross-Cutting Concerns

**Logging:** `error_log()` for database errors only

**Validation:** 
- Server-side: Basic PHP isset/empty checks
- Client-side: HTML5 required/min attributes

**Authentication:** 
- Session-based with secure cookie flags
- Password hashing with `password_hash()` and `password_verify()`
- Session regeneration every 5 minutes
- 7-day session lifetime

**Authorization:**
- Admin flag in `users` table
- Admin-only routes via `Auth->isAdmin()` checks

**Internationalization:**
- JSON translation files per language
- Dot-notation keys for organization
- Browser language detection with user preference override

---

*Architecture analysis: 2026-02-09*
