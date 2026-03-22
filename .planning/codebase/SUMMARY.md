# Summary - School Time Tracker

**Analysis Date:** 2026-02-09

## What This Application Does

The **School Time Tracker** is a PHP-based web application that helps students and learners track their study time across different subjects and categories. It provides a visual dashboard, reporting capabilities, and multi-language support.

## Core Features

**Time Tracking:**
- Log study sessions with duration, date, subject, and optional notes
- Track time across multiple categories (e.g., Mathematics, Sciences, Languages)
- View recent entries and all historical entries
- Edit and delete existing entries

**Dashboard & Analytics:**
- View daily study summary (total minutes, categories tracked, sessions count)
- Visual charts showing study distribution by category
- Progress tracking over weekly periods
- Real-time statistics updates

**Category & Subject Management:**
- Create and manage study categories with custom colors
- Add subjects within categories for detailed tracking
- Categories organize subjects hierarchically

**Reporting:**
- Generate reports by day, week, or month
- Export reports as PDF documents
- View statistics with time breakdown by category
- Percentage-based distribution analysis

**User Management:**
- Secure authentication with password hashing
- Admin role for user administration
- Multiple user support (each with own time entries)
- User-specific language preferences

**Multi-Language Support:**
- French, English, German, and Italian translations
- Browser language detection
- User preference overrides
- Full UI and PDF report translations

## Technical Overview

**Architecture:**
- Server-rendered PHP with embedded HTML templates
- RESTful API endpoints for dynamic JavaScript content
- Session-based authentication
- Single SQLite database file

**Frontend:**
- Vanilla JavaScript with Chart.js for visualizations
- Tailwind CSS via CDN for styling
- AJAX forms for seamless updates
- Dark mode support

**Database Schema:**
- 7 tables: `users`, `categories`, `subjects`, `time_entries`, `category_translations`, `subject_translations`, `users`
- Foreign key relationships with cascade deletes
- Indexed foreign key columns for query performance

## Key Files

**Entry Points:**
- `public/index.php` - Main application page
- `public/api.php` - REST API endpoint
- `public/login.php` - Authentication page
- `public/setup.php` - Initial admin setup
- `public/export_pdf.php` - PDF report generation

**Core Classes:**
- `src/Database.php` - PDO singleton wrapper
- `src/Auth.php` - Authentication facade
- `src/Session.php` - Session management
- `src/User.php` - User CRUD operations
- `src/ApiController.php` - API request handlers
- `src/Translation.php` - i18n service

**Data:**
- `data/study_tracker.db` - SQLite database
- `lang/*.json` - Translation files (fr, en, de, it)
- `init.sql` - Database schema

## Authentication Flow

1. First visit redirects to setup page to create admin account
2. Subsequent visits redirect to login if not authenticated
3. Successful login creates session with secure cookies
4. Session expires after 7 days of inactivity
5. Session ID regenerated every 5 minutes

## API Endpoints

All endpoints accessed via `public/api.php?action=...`:
- `summary` - Get study statistics for a period
- `categories` - List all categories with stats
- `subjects` - List subjects (optionally filtered by category)
- `entries` - Get entries for a specific date
- `all_entries` - Get all entries (optionally filtered by date)
- `users` - List all users (admin only)
- `stats` - Get daily breakdown for a subject over time

## Use Cases

**Student Self-Tracking:**
- Individual students track personal study time
- View progress and identify time allocation patterns
- Generate reports for parents or teachers

**Tutoring Centers:**
- Multiple users can share the same installation
- Each user has isolated data
- Admin manages user accounts

**Language Learners:**
- Create categories per language
- Track time spent on each language
- Monitor study consistency

## Deployment

**Requirements:**
- PHP 8.x with PDO SQLite extension
- Web server (Apache/Nginx)
- Composer for TCPDF dependency
- Write access to `data/` directory

**Setup:**
1. Place files in web root
2. Navigate to application
3. Complete admin account setup
4. Start tracking study time

---

*Summary analysis: 2026-02-09*
