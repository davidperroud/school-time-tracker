# Study Time Tracker

A modern PHP web application for tracking and analyzing study time across categories and subjects. Features an elegant user interface with a luxury design, real-time statistics, PDF report generation, and full multi-language support.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Multi-language Support](#multi-language-support)
- [Security](#security)
- [Project Structure](#project-structure)
- [Technologies Used](#technologies-used)
- [License](#license)

## Features

- **Study Time Tracking** - Log study sessions with subject, duration, date, and optional notes
- **Category & Subject Management** - Organize studies into categories with color-coded subjects
- **Interactive Dashboard** - Real-time statistics with pie charts and bar graphs
- **Reports Generation** - Daily, weekly, and monthly reports with PDF export
- **Multi-language Support** - Full interface in French, English, German, and Italian
- **Dark/Light Theme** - Automatic theme detection with manual toggle
- **User Authentication** - Session-based login with password reset capability
- **Admin Panel** - User management for administrators
- **Responsive Design** - Works on desktop, tablet, and mobile devices
- **SQLite Database** - Lightweight, self-contained database with no external dependencies

## Requirements

- **PHP 7.4+** or **PHP 8.x** with extensions:
  - `pdo_sqlite`
  - `sqlite3`
  - `session`
  - `json`
- **Web Server** - Apache, Nginx, or any PHP-capable server
- **Composer** - For managing PHP dependencies
- **Modern Web Browser** - With JavaScript enabled

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/davidperroud/school-time-tracker.git
cd school-time-tracker
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Set Up the Web Server

**For Apache (with .htaccess):**
- Point your document root to the project directory
- Ensure `AllowOverride All` is enabled for `.htaccess` to work
- Make sure PHP has write access to the `data/` directory

**For Nginx:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/school-time-tracker/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 4. Initialize the Database

Access the setup page in your browser:
```
http://your-domain.com/public/setup.php
```

Or initialize manually by running:
```bash
sqlite3 data/study_tracker.db < init.sql
```

### 5. Set Permissions

```bash
chmod 755 data/
chmod 644 data/study_tracker.db
```

## Configuration

### Default Credentials

After setup, log in with:

| Username | Password |
|----------|----------|
| admin | admin123 |

**Important:** Change these credentials immediately after first login.

### Email Configuration (for Password Reset)

For password reset emails to work, configure your SMTP settings in `src/User.php` or create a `.env` file:

```env
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USERNAME=your-email@example.com
SMTP_PASSWORD=your-password
SMTP_FROM=noreply@example.com
```

## Usage

### Navigation

The application has 5 main sections:

1. **Dashboard** - Overview of study statistics with interactive charts
2. **New Entry** - Quick form to log study time
3. **Entries** - View, search, filter, and manage all entries
4. **Management** - Create and edit categories and subjects
5. **Reports** - Generate and export study reports

### Adding Study Time

1. Go to the **New Entry** tab
2. Select a **Subject** from the dropdown
3. Enter the **Duration** in minutes
4. Select the **Date** (defaults to today)
5. Optionally add **Notes**
6. Click **Save Entry**

### Creating Categories and Subjects

1. Go to the **Management** tab
2. Add a **Category** (e.g., "Mathematics", "Languages")
3. Add **Subjects** under each category
4. Assign colors to categories for visual distinction

### Generating Reports

1. Go to the **Reports** tab
2. Select the **Period** (day, week, or month)
3. Choose the **Date Range**
4. Click **Generate Report**
5. Use **Export PDF** to download the report

### Changing Language

- Use the **language dropdown** in the header
- Or append `?lang=XX` to the URL (e.g., `?lang=en`, `?lang=de`)

### Password Reset

1. Click **Forgot Password** on the login page
2. Enter your **username**
3. Check your email for the **reset link**
4. Click the link and enter the **PIN** from the email
5. Set your **new password**

## Database Schema

```
users
├── id (INTEGER PRIMARY KEY)
├── username (TEXT UNIQUE)
├── password_hash (TEXT)
├── language_preference (TEXT)
├── is_admin (INTEGER)
├── created_at (DATETIME)
└── last_login (DATETIME)

password_resets
├── id (INTEGER PRIMARY KEY)
├── user_id (INTEGER FK)
├── token (TEXT UNIQUE)
├── pin (TEXT)
├── expires_at (DATETIME)
├── used_at (DATETIME)
└── created_at (DATETIME)

categories
├── id (INTEGER PRIMARY KEY)
├── name (TEXT)
├── color (TEXT)
└── created_at (DATETIME)

category_translations
├── id (INTEGER PRIMARY KEY)
├── category_id (INTEGER FK)
├── lang (TEXT)
└── name (TEXT)

subjects
├── id (INTEGER PRIMARY KEY)
├── category_id (INTEGER FK)
├── name (TEXT)
├── description (TEXT)
└── created_at (DATETIME)

subject_translations
├── id (INTEGER PRIMARY KEY)
├── subject_id (INTEGER FK)
├── lang (TEXT)
├── name (TEXT)
└── description (TEXT)

time_entries
├── id (INTEGER PRIMARY KEY)
├── subject_id (INTEGER FK)
├── duration_minutes (INTEGER)
├── entry_date (DATE)
├── notes (TEXT)
└── created_at (DATETIME)
```

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api.php?action=summary` | GET | Get summary statistics |
| `/api.php?action=categories` | GET | List all categories |
| `/api.php?action=subjects` | GET | List subjects (optional: `category_id`) |
| `/api.php?action=entries` | GET | Get entries (optional: `date`, `subject_id`) |
| `/api.php?action=all_entries` | GET | Get all entries with optional date filter |
| `/api.php?action=stats` | GET | Get detailed stats (`subject_id`, `days`) |
| `/api.php?action=users` | GET | List users (admin only) |
| `/api.php?action=progress` | GET | Get progress data |
| `/api.php?action=recent_entries` | GET | Get recent entries |
| `/export_pdf.php` | GET | Export report as PDF |

### Query Parameters

- `period` - `day`, `week`, or `month`
- `date` - Date in `YYYY-MM-DD` format
- `month` - Month in `MM` format
- `year` - Year in `YYYY` format
- `category_id` - Filter by category
- `subject_id` - Filter by subject
- `filter_date` - Filter entries by date

## Multi-language Support

The application is fully translated into 4 languages:

| Code | Language | Native Name | Status |
|------|----------|-------------|--------|
| `fr` | French | Français | Default |
| `en` | English | English | Complete |
| `de` | German | Deutsch | Complete |
| `it` | Italian | Italiano | Complete |

Translation files are located in `/lang/` directory:
- `lang/fr.json` - French translations
- `lang/en.json` - English translations
- `lang/de.json` - German translations
- `lang/it.json` - Italian translations

## Security

- **Password Hashing** - Uses `password_hash()` with `PASSWORD_DEFAULT` algorithm
- **Prepared Statements** - All database queries use PDO prepared statements
- **Session Security** - Secure cookies with `httponly` and `samesite` flags
- **CSRF Protection** - Form submissions validated with tokens
- **SQL Injection Prevention** - Parameterized queries throughout
- **XSS Prevention** - Output escaped with `htmlspecialchars()`
- **Role-based Access** - Admin panel restricted to administrators

## Project Structure

```
school-time-tracker/
├── index.php                 # Root redirect to public/
├── init.php                  # Database initialization script
├── init.sql                  # SQL schema file
├── auth.php                  # HTTP Basic authentication
├── composer.json             # PHP dependencies
├── .gitignore                # Git ignore rules
├── .htaccess                 # Apache configuration
├── LICENSE                   # MIT License
├── README.md                 # English documentation
├── README_FR.md              # French documentation
├── src/
│   ├── Database.php          # Database singleton class
│   ├── Auth.php              # Authentication class
│   ├── User.php              # User management class
│   ├── Session.php           # Session management class
│   ├── Translation.php       # Translation/i18n class
│   └── ApiController.php     # REST API controller
├── public/
│   ├── index.php             # Main application entry point
│   ├── login.php             # Login page
│   ├── logout.php            # Logout handler
│   ├── setup.php             # Application setup wizard
│   ├── reset_request.php     # Password reset request
│   ├── reset_password.php    # Password reset form
│   ├── init.php              # Database init endpoint
│   ├── api.php               # API entry point
│   ├── export_pdf.php         # PDF export handler
│   ├── css/
│   │   └── style.css         # Custom styles
│   └── js/
│       └── app.js            # Frontend JavaScript
├── lang/
│   ├── fr.json               # French translations
│   ├── en.json               # English translations
│   ├── de.json               # German translations
│   └── it.json               # Italian translations
└── data/
    └── study_tracker.db       # SQLite database (auto-created)
```

## Technologies Used

| Category | Technology |
|----------|------------|
| Backend | PHP 7.4+ / 8.x |
| Database | SQLite 3 with PDO |
| Frontend | HTML5, CSS3, JavaScript (ES6+) |
| UI Framework | Tailwind CSS (CDN) |
| Charts | Chart.js |
| Icons | Lucide Icons |
| PDF Generation | TCPDF |
| Authentication | Session-based with password hashing |

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Developed with ❤️ for optimizing study time**
