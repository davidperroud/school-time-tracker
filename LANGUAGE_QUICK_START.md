# Multi-Language Support - Quick Start Guide

## Overview
The School Time Tracker now supports **4 languages**:
- 🇫🇷 **FR** - Français (French)
- 🇬🇧 **EN** - English
- 🇩🇪 **DE** - Deutsch (German)
- 🇮🇹 **IT** - Italiano (Italian)

---

## How to Change Language

### Method 1: Language Selector Dropdown (Recommended)
1. Open the application: `http://your-domain/school-time-tracker/public/index.php`
2. Look for the **language dropdown** in the header (below the app title)
3. Click and select your preferred language:
   - Français
   - English
   - Deutsch
   - Italiano
4. The page will reload and display in the selected language
5. Your choice is automatically saved in browser storage

### Method 2: URL Parameter
Add `?lang=xx` to the URL where `xx` is the language code:

```
http://your-domain/public/index.php?lang=fr  # French
http://your-domain/public/index.php?lang=en  # English
http://your-domain/public/index.php?lang=de  # German
http://your-domain/public/index.php?lang=it  # Italian
```

### Method 3: Browser Language Detection (Automatic)
- If you don't specify a language, the system automatically detects from:
  1. Your browser's language settings
  2. If not recognized, defaults to French

---

## What Gets Translated

### User Interface Elements ✓
- Page headers and titles
- Navigation tabs
- Form labels
- Button labels
- Placeholder text
- Modal dialogs
- Table headers
- Error/warning messages
- Empty state messages

### Reports & PDFs ✓
- Report titles
- Table headers
- Totals and summaries
- Generated date/time
- Footer text

### Dashboard & Charts ✓
- Chart titles
- Statistic labels
- Category names
- Subject names
- Session information

---

## Language Persistence

- **Automatic Saving:** Your language choice is saved in browser storage
- **Survives Reloads:** Language preference persists across page refreshes
- **Per-Browser:** Each browser maintains its own language preference
- **No Account Required:** Language preference works even without login

---

## Adding New Categories/Subjects

### When Adding in Default Language (French)
1. Navigate to **Gestion** (Management) tab
2. Enter category or subject name in French
3. Save normally

### Adding Translations
Currently, translations for new categories/subjects must be added via database:

```sql
-- Add English translation for a new category
INSERT INTO category_translations (category_id, lang, name)
VALUES (5, 'en', 'New Category Name');

-- Add German translation
INSERT INTO category_translations (category_id, lang, name)
VALUES (5, 'de', 'Neue Kategoriename');

-- Add Italian translation
INSERT INTO category_translations (category_id, lang, name)
VALUES (5, 'it', 'Nuovo Nome Categoria');
```

*Future versions will include a translation management UI.*

---

## Testing Multi-Language Support

### Quick Test Script
Run the included test script to verify all languages:

```bash
php test_translations.php
```

This will display:
- All 4 languages and their status
- Sample translations for each language
- Database initialization status
- Translation file validation

### Manual Testing Checklist
- [ ] Switch to English - verify all buttons/labels change
- [ ] Switch to German - check special characters (ü, ö, ä)
- [ ] Switch to Italian - verify accented characters (à, è, é)
- [ ] Generate a PDF report in French, then in English
- [ ] Add a new entry in German and verify it displays correctly
- [ ] Verify month names change with language selection

---

## File Structure

```
lang/
├── fr.json          # 90 French translation strings
├── en.json          # 90 English translation strings
├── de.json          # 90 German translation strings
└── it.json          # 90 Italian translation strings

src/
└── Translation.php  # Core translation system

public/
├── index.php        # Language selector dropdown
└── js/app.js        # JavaScript translation functions
```

---

## Technical Details

### Translation System Architecture
```
Browser Request
    ↓
Language Detection (URL → Cookie → Browser → Default)
    ↓
Translation Class loads JSON file
    ↓
Templates use t('key') to fetch strings
    ↓
User sees translated interface
```

### Supported Translation Formats

#### PHP (Backend)
```php
$translation = new Translation('en');
echo $translation->t('ui.buttons.save');  // "Save"
```

#### JavaScript (Frontend)
```javascript
const text = t('ui.navigation.dashboard');  // "Dashboard"
```

#### Database Queries (Multi-language Data)
```php
$categories = $db->getCategoriesWithTranslations('de');
```

---

## Language Codes Reference

| Code | Language | Native Name | Status |
|------|----------|------------|--------|
| `fr` | French | Français | ✓ Default |
| `en` | English | English | ✓ Complete |
| `de` | German | Deutsch | ✓ Complete |
| `it` | Italian | Italiano | ✓ Complete |

---

## Troubleshooting

### Language Selector Not Appearing
- Clear browser cache
- Ensure JavaScript is enabled
- Check browser console for errors (F12)

### Translations Not Loading
- Verify `lang/` folder exists with JSON files
- Check that JSON files are not corrupted
- Ensure proper file permissions (644 or 755)

### PDF Export in Wrong Language
- Verify language parameter is sent to export function
- Check `src/ApiController.php` has Translation support
- Verify TCPDF library is properly installed

### Special Characters Not Displaying
- Ensure database uses UTF-8 encoding
- Check HTML meta charset is UTF-8
- Verify JSON files are saved as UTF-8 without BOM

---

## Future Enhancements

Planned improvements for future versions:
- [ ] Admin panel for managing translations
- [ ] Translation import/export functionality
- [ ] Right-to-left (RTL) language support
- [ ] Additional languages (Spanish, Portuguese, Japanese)
- [ ] User-contributed translations
- [ ] Pluralization rules
- [ ] Context-aware translations

---

## Support

For issues or questions about multi-language support:
1. Check `MULTI_LANGUAGE_IMPLEMENTATION.md` for detailed documentation
2. Review `test_translations.php` for validation
3. Run `verify_implementation.sh` to check system status
4. Consult application logs for error details

---

## Summary

Multi-language support is now **fully integrated** into the School Time Tracker. Users can seamlessly switch between French, English, German, and Italian with one click, and their preference is automatically saved.

**Enjoy using your application in your preferred language!** 🌍
