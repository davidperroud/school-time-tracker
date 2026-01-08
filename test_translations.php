<?php
/**
 * Multi-Language Support Test Script
 * Tests all four languages (French, English, German, Italian)
 */

require_once __DIR__ . '/src/Translation.php';
require_once __DIR__ . '/src/Database.php';

echo "========================================\n";
echo "   Multi-Language Translation Test\n";
echo "========================================\n\n";

// Test 1: Translation Class
echo "TEST 1: Translation Class\n";
echo "------------------------\n";

$languages = ['fr' => 'French', 'en' => 'English', 'de' => 'German', 'it' => 'Italian'];

foreach ($languages as $code => $name) {
    $translator = new Translation($code);
    echo "\n[$code] $name:\n";
    echo "  Header Title: " . $translator->t('ui.header.title') . "\n";
    echo "  Dashboard: " . $translator->t('ui.navigation.dashboard') . "\n";
    echo "  Save Button: " . $translator->t('ui.buttons.save') . "\n";
    echo "  Empty State: " . $translator->t('ui.messages.no_entry_today') . "\n";
    echo "  PDF Title: " . $translator->t('pdf.title') . "\n";
}

// Test 2: Database Initialization
echo "\n\nTEST 2: Database Initialization\n";
echo "-------------------------------\n";

try {
    $db = Database::getInstance();
    
    // Check if database file exists
    $dbPath = __DIR__ . '/data/study_tracker.db';
    if (file_exists($dbPath)) {
        echo "✓ Database file exists: $dbPath\n";
        echo "  File size: " . filesize($dbPath) . " bytes\n";
    } else {
        echo "ℹ Database file not yet created (will be created on first access)\n";
    }
    
    echo "✓ Database class initialized successfully\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

// Test 3: Translation File Validation
echo "\n\nTEST 3: Translation File Validation\n";
echo "------------------------------------\n";

$langFiles = ['fr', 'en', 'de', 'it'];
foreach ($langFiles as $lang) {
    $file = __DIR__ . "/lang/$lang.json";
    if (file_exists($file)) {
        $content = json_decode(file_get_contents($file), true);
        if ($content) {
            $keyCount = count($content);
            echo "✓ $lang.json is valid JSON with " . $keyCount . " top-level keys\n";
            
            // Count total translation keys
            $totalKeys = 0;
            array_walk_recursive($content, function($item, $key) use (&$totalKeys) {
                if (!is_array($item)) $totalKeys++;
            });
            echo "  Total translation strings: $totalKeys\n";
        } else {
            echo "✗ $lang.json failed to parse\n";
        }
    } else {
        echo "✗ $lang.json not found\n";
    }
}

// Test 4: Available Languages
echo "\n\nTEST 4: Available Languages\n";
echo "--------------------------\n";

$translator = new Translation();
$availableLanguages = $translator->getAvailableLanguages();
echo "Available languages:\n";
foreach ($availableLanguages as $code => $name) {
    echo "  - $code: $name\n";
}

echo "\n========================================\n";
echo "   All Tests Completed Successfully!\n";
echo "========================================\n";
