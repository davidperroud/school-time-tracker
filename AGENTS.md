# AGENTS.md - School Time Tracker

## Project Overview

This is a PHP-based school time tracking application using SQLite database, Composer for dependencies (TCPDF), and PSR-4 autoloading. The app tracks study time across categories and subjects, generates PDF reports, and supports multi-language (French, English, German, Italian).

## Build, Lint, and Test Commands

### Dependencies
```bash
# Install Composer dependencies
composer install

# Update Composer dependencies
composer update
```

### Database
```bash
# SQLite database is auto-created in data/study_tracker.db
# Run migrations if any exist
php add_admin_column.sql  # Manual SQL migration for admin column
```

### Testing
```bash
# No formal test framework configured
# Manual testing via browser at /public/
# JavaScript testing: node test_date_adjust.js
```

## Code Style Guidelines

### PHP Style
- **Opening tag**: Always use `<?php` (no shorthand `<?`)
- **Closing tag**: Omit `?>` at end of pure PHP files
- **Indentation**: 4 spaces per level (no tabs)

### Naming Conventions
- **Classes**: PascalCase (e.g., `Database`, `ApiController`, `Auth`)
- **Methods and variables**: camelCase (e.g., `getInstance`, `$userId`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `SESSION_LIFETIME`)
- **Private properties**: `$camelCase` with leading `$`
- **Database tables**: snake_case (e.g., `time_entries`, `category_translations`)
- **Files**: Match class name exactly (e.g., `Database.php` for `Database` class)

### File Structure
- **Source files**: `src/` directory
- **Public entry points**: `public/` directory
- **Translations**: `lang/` directory (JSON files per language)
- **Database**: `data/study_tracker.db`
- **Vendor dependencies**: `vendor/` (Composer autoloaded)

### Imports and Autoloading
```php
# Use require_once with __DIR__ for local includes
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';

# Use Composer autoloader for external libraries
require_once __DIR__ . '/../vendor/autoload.php';
```

### Classes
- One class per file (PSR-4 compliant)
- Order: properties, constructor, public methods, private methods
- Use `private` for internal implementation details
- Use singleton pattern sparingly (Database class uses it)
- Document all public methods with docblocks

### Error Handling
```php
# Database errors: catch PDOException, log and return false
try {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
} catch (PDOException $e) {
    error_log("Query error: " . $e->getMessage());
    return false;
}

# Fatal errors: use die() with user-friendly message
catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
```

### Database Operations
- Use PDO with prepared statements (parameterized queries)
- Always use `?` placeholders, never string interpolation
- Set `PDO::ATTR_ERRMODE` to `PDO::ERRMODE_EXCEPTION`
- Enable foreign keys: `PRAGMA foreign_keys = ON`
- Fetch modes: `PDO::FETCH_ASSOC` by default

### Security
- **Passwords**: Use `password_hash()` and `password_verify()`
- **Sessions**: Set secure cookie flags (`httponly`, `secure`, `samesite`)
- **Session regeneration**: Regenerate ID every 5 minutes
- **SQL injection**: Never interpolate user input into SQL

### Internationalization
- Supported languages: French (fr), English (en), German (de), Italian (it)
- Translation keys: dot notation (e.g., `ui.messages.error`)
- Translation files: `lang/{lang}.json`
- Fallback language: French (fr)

### Code Documentation
- French comments for business logic explanations
- English for technical documentation
- PHPDoc for all public methods
- Comment complex SQL queries with column explanations

### Frontend Guidelines
- JavaScript in `public/js/app.js`
- CSS in `public/css/style.css`
- All public assets served from `public/` directory
- Use vanilla JavaScript (no frameworks)
- API responses: JSON with `{success: true/false, data/error: ...}` format

### API Endpoints
- API controller: `public/api.php`
- Auth: `public/login.php`, `public/logout.php`
- Export: `public/export_pdf.php`
- Setup: `public/setup.php`
- Action parameter in GET: `action=summary|categories|subjects|entries|users|stats`

### Git Workflow
- Commit messages in English
- Feature branches: `feature/description`
- Bug fixes: `fix/description`
- No force pushes to main

### Security Rules
- Never commit secrets or credentials
- Use environment variables for sensitive config
- Validate all user inputs
- Check authentication before sensitive operations
- Check admin privileges (`is_admin` field) for admin features
