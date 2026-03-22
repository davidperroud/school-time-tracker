# Technology Stack

**Analysis Date:** 2026-02-09

## Languages

**Primary:**
- **PHP 8.x** - Server-side scripting, API controllers, authentication
- **SQLite** - Embedded relational database
- **JavaScript (ES6+)** - Client-side interactivity
- **HTML5** - Page structure with embedded PHP templates
- **CSS3** - Styling with Tailwind CSS CDN

**Secondary:**
- **JSON** - Translation files, API responses
- **SQL** - Database schema and queries

## Runtime

**Environment:**
- **PHP 8.x** with PDO SQLite extension
- **MAMP** - Local development server (Apache/Nginx + PHP)

**Package Manager:**
- **Composer** 2.x for PHP dependencies
- Lockfile: `composer.lock` present

## Frameworks

**Core:**
- **Vanilla PHP** - No framework, custom MVC-like structure
- **Tailwind CSS CDN** - Utility-first CSS framework for styling

**Testing:**
- **Manual testing** via browser
- **No formal test framework** configured

**Build/Dev:**
- **No build step** required (interpreted PHP)
- **Tailwind via CDN** (no local build)

## Key Dependencies

**Critical:**
- **tecnickcom/tcpdf** (^6.10) - PDF generation for reports
  - Used in: `src/ApiController.php` lines 219-311
  - Purpose: Generate study time reports as PDF downloads

**Infrastructure:**
- **PDO SQLite** - PHP database abstraction
  - Built-in PHP extension
  - Database file: `data/study_tracker.db`

## Configuration

**Environment:**
- SQLite database auto-created in `data/study_tracker.db`
- Session configuration via `ini_set()` in `src/Session.php`
- No `.env` file - configuration hardcoded in PHP

**Build:**
- No build configuration files
- No TypeScript compilation
- No CSS preprocessing

## Platform Requirements

**Development:**
- PHP 8.x with PDO and SQLite extensions
- MAMP/XAMPP or similar web server
- Web browser for testing
- Composer for dependency management

**Production:**
- PHP 8.x hosting with SQLite support
- Web server (Apache/Nginx)
- Write access to `data/` directory
- Composer for TCPDF installation

---

*Stack analysis: 2026-02-09*
