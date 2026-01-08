<?php
require_once 'src/Database.php';

try {
    $db = Database::getInstance();
    $initSql = file_get_contents('init.sql');

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $initSql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->getConnection()->exec($statement);
                echo 'Executed: ' . substr($statement, 0, 50) . '...' . PHP_EOL;
            } catch (Exception $e) {
                echo 'Error executing statement: ' . $e->getMessage() . PHP_EOL;
                echo 'Statement: ' . $statement . PHP_EOL;
            }
        }
    }

    echo 'Database initialization completed successfully.' . PHP_EOL;

} catch (Exception $e) {
    echo 'Database initialization failed: ' . $e->getMessage() . PHP_EOL;
}