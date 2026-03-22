# Study Time Tracker

A modern web application to track and analyze your study time. Built with PHP featuring an elegant user interface using Tailwind CSS and Chart.js.

## Features

- 🌍 **Full multi-language support** with 4 languages (French, English, German, Italian)
- 📊 **Interactive dashboard** with real-time statistics and charts
- 📝 **Time tracking** by subject and category
- 📈 **Detailed reports** (daily, weekly, monthly)
- 🏷️ **Category management** and study subjects
- 🌙 **Dark/light theme** with preference saving
- 📱 **Responsive design** for all devices
- 🔐 **Secure authentication** with HTTP Basic Auth
- 🗃️ **Lightweight SQLite database**
- 📄 **PDF export** of study reports
- 🔄 **Edit modals** for inline modification

## Requirements

- **Web server** (Apache, Nginx) with PHP support
- **PHP 7.4+** with PDO SQLite extension enabled
- **Modern web browser** with JavaScript enabled
- **Composer** for dependency management

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/davidperroud/school-time-tracker.git
   cd school-time-tracker
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Deploy to your web server:**
   - Copy all files to your web server directory
   - Ensure PHP can write to the `data/` folder

4. **Initialize the database:**
   - Access `http://your-domain.com/public/init.php`
   - The database will be created automatically with sample data

5. **Access the application:**
   - Main URL: `http://your-domain.com/`
   - User interface: `http://your-domain.com/public/`

## Authentication

The application uses HTTP Basic authentication. Default credentials:

- **Username:** `admin`
- **Password:** `admin123`

To modify, edit `auth.php`.

## Usage

### Getting Started

1. **Log in** with the configured credentials
2. **Create categories** (e.g., Mathematics, Science, Languages)
3. **Add subjects** in each category
4. **Start tracking** your study time!

### Main Interface

The application offers 5 main tabs:

- **Dashboard** - Day statistics, pie/bar charts, activity overview
- **New Entry** - Quick form to add study time
- **Entries** - Complete view of all entries with search/filter
- **Management** - Create and modify categories/subjects
- **Reports** - Daily, weekly, monthly reports with PDF export

## Multi-language Support

The application supports **4 languages**: French, English, German, and Italian.

| Code | Language | Status |
|------|----------|--------|
| `fr` | French | ✓ Default |
| `en` | English | ✓ Complete |
| `de` | German | ✓ Complete |
| `it` | Italian | ✓ Complete |

Change language using the dropdown in the header or by adding `?lang=en`, `?lang=de`, or `?lang=it` to the URL.

## License

MIT

---

**Developed with ❤️ to optimize your study time**
