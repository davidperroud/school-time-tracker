<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../src/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Lire et exécuter le script SQL d'initialisation
    $initSql = file_get_contents(__DIR__ . '/../init.sql');

    if ($initSql === false) {
        die("Erreur : Impossible de lire le fichier init.sql");
    }

    // Exécuter le script SQL
    $pdo->exec($initSql);

    echo "Base de données initialisée avec succès !";

} catch (Exception $e) {
    die("Erreur lors de l'initialisation : " . $e->getMessage());
}

?>
