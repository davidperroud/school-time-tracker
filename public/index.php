<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $isAjax = !empty($_POST['ajax']);
    
    switch ($action) {
        case 'add_category':
            $db->execute(
                "INSERT INTO categories (name, color) VALUES (?, ?)",
                [$_POST['name'], $_POST['color']]
            );
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;
            
        case 'add_subject':
            $db->execute(
                "INSERT INTO subjects (category_id, name, description) VALUES (?, ?, ?)",
                [$_POST['category_id'], $_POST['name'], $_POST['description'] ?? '']
            );
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;
            
        case 'add_entry':
            $db->execute(
                "INSERT INTO time_entries (subject_id, duration_minutes, entry_date, notes) VALUES (?, ?, ?, ?)",
                [$_POST['subject_id'], $_POST['duration_minutes'], $_POST['entry_date'], $_POST['notes'] ?? '']
            );
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;
            
        case 'delete_category':
            $db->execute("DELETE FROM categories WHERE id = ?", [$_POST['id']]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;
            
        case 'delete_subject':
            $db->execute("DELETE FROM subjects WHERE id = ?", [$_POST['id']]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;
            
        case 'delete_entry':
            $db->execute("DELETE FROM time_entries WHERE id = ?", [$_POST['id']]);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;

        case 'update_category':
            $db->execute(
                "UPDATE categories SET name = ?, color = ? WHERE id = ?",
                [$_POST['name'], $_POST['color'], $_POST['id']]
            );
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;

        case 'update_subject':
            $db->execute(
                "UPDATE subjects SET category_id = ?, name = ?, description = ? WHERE id = ?",
                [$_POST['category_id'], $_POST['name'], $_POST['description'] ?? '', $_POST['id']]
            );
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;

        case 'update_entry':
            $db->execute(
                "UPDATE time_entries SET subject_id = ?, duration_minutes = ?, entry_date = ?, notes = ? WHERE id = ?",
                [$_POST['subject_id'], $_POST['duration_minutes'], $_POST['entry_date'], $_POST['notes'] ?? '', $_POST['id']]
            );
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            break;
    }
    
    if (!$isAjax) {
        header('Location: index.php');
    }
    exit;
}

$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
$subjects = $db->fetchAll("SELECT s.*, c.name as category_name FROM subjects s JOIN categories c ON s.category_id = c.id ORDER BY c.name, s.name");

// Générer les années disponibles (2024 à 2030)
$currentYear = date('Y');
$years = [];
for ($y = 2024; $y <= $currentYear + 5; $y++) {
    $years[] = $y;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Time Tracker</title>
        <link rel="stylesheet" href="css/style.css">
        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: '#3b82f6',
                            secondary: '#8b5cf6',
                        }
                    }
                }
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="container">
        <header class="flex flex-col items-center justify-center py-6">
            <h1 class="text-4xl font-bold text-primary dark:text-secondary mb-2">Study Time Tracker</h1>
            <div class="date-display mb-2">Date : <?php echo date('d/m/Y'); ?></div>
            <button id="themeToggle" class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-400 dark:border-gray-600 transition">🌙 Thème sombre</button>
        </header>
        <nav class="tabs flex gap-2 mb-6 border-b-2 border-gray-300 dark:border-gray-700">
            <button class="tab active" data-tab="dashboard">Dashboard</button>
            <button class="tab" data-tab="entry">Nouvelle entrée</button>
            <button class="tab" data-tab="entries">Entrées</button>
            <button class="tab" data-tab="manage">Gestion</button>
            <button class="tab" data-tab="reports">Rapports</button>
        </nav>

        <!-- Dashboard -->
        <div class="tab-content active" id="dashboard">
            <div class="stats-grid" id="statsGrid"></div>
            <div class="charts-grid">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="progressChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Nouvelle entrée -->
        <div class="tab-content" id="entry">
            <div class="entry-layout">
                <div class="entry-form-column">
                    <form method="POST" class="form-card entry-form">
                        <input type="hidden" name="action" value="add_entry">
                        <input type="hidden" name="ajax" value="1">
                        <div class="form-group">
                            <label>Sujet</label>
                            <select name="subject_id" required>
                                <option value="">Sélectionner un sujet...</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>">
                                        <?= htmlspecialchars($subject['category_name']) ?> - <?= htmlspecialchars($subject['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Durée (minutes)</label>
                            <input type="number" name="duration_minutes" min="1" required>
                        </div>

                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Notes (optionnel)</label>
                            <textarea name="notes" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>

                <div class="entry-list-column">
                    <div class="recent-entries">
                        <h3>Entrées récentes</h3>
                        <div id="recentEntries"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toutes les entrées -->
        <div class="tab-content" id="entries">
            <div class="entries-controls">
                <div class="filter-controls">
                    <label>Rechercher:</label>
                    <input type="text" id="entriesSearch" placeholder="Sujet, catégorie ou notes..." value="">
                    <label>Filtrer par date:</label>
                    <input type="date" id="entriesDateFilter" value="">
                    <button onclick="clearDateFilter()" class="btn btn-secondary">Tous</button>
                </div>
            </div>

            <div id="allEntriesList"></div>
        </div>

        <!-- Gestion -->
        <div class="tab-content" id="manage">
            <div class="management-grid">
                <div class="form-card">
                    <h3>Catégories</h3>
                    <form method="POST" class="category-form">
                        <input type="hidden" name="action" value="add_category">
                        <input type="hidden" name="ajax" value="1">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Nom" required>
                        </div>
                        <div class="form-group">
                            <input type="color" name="color" value="#3b82f6">
                        </div>
                        <button type="submit" class="btn btn-small">Ajouter</button>
                    </form>
                    
                    <div class="list" id="categoriesList"></div>
                </div>

                <div class="form-card">
                    <h3>Sujets</h3>
                    <form method="POST" class="subject-form">
                        <input type="hidden" name="action" value="add_subject">
                        <input type="hidden" name="ajax" value="1">
                        <div class="form-group">
                            <select name="category_id" required>
                                <option value="">Catégorie...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Nom du sujet" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="description" placeholder="Description">
                        </div>
                        <button type="submit" class="btn btn-small">Ajouter</button>
                    </form>
                    
                    <div class="list" id="subjectsList"></div>
                </div>
            </div>
        </div>

        <!-- Rapports -->
        <div class="tab-content" id="reports">
            <div class="report-controls">
                <select id="reportPeriod">
                    <option value="day">Jour</option>
                    <option value="week">Semaine</option>
                    <option value="month">Mois</option>
                </select>

                <!-- Contrôles pour jour et semaine -->
                <div id="dateInputContainer">
                    <label>Date de début:</label>
                    <input type="date" id="reportDate" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Contrôles pour mois (cachés par défaut) -->
                <div id="monthInputContainer" style="display: none;">
                    <label>Mois:</label>
                    <select id="reportMonth">
                        <option value="1">Janvier</option>
                        <option value="2">Février</option>
                        <option value="3">Mars</option>
                        <option value="4">Avril</option>
                        <option value="5">Mai</option>
                        <option value="6">Juin</option>
                        <option value="7">Juillet</option>
                        <option value="8">Août</option>
                        <option value="9">Septembre</option>
                        <option value="10">Octobre</option>
                        <option value="11">Novembre</option>
                        <option value="12">Décembre</option>
                    </select>
                    <label>Année:</label>
                    <select id="reportYear">
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= $year == $currentYear ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button onclick="loadReport()" class="btn btn-primary">Générer</button>
                <button onclick="exportReportPDF()" class="btn btn-secondary" id="exportPdfBtn" style="display: none;">📄 Exporter PDF</button>
            </div>

            <div id="reportContent"></div>
        </div>

        <!-- Modales d'édition (déplacées en dehors des onglets pour être accessibles partout) -->
        <div id="editCategoryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Modifier la catégorie</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <form method="POST" class="edit-category-form">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="id" id="editCategoryId">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="name" id="editCategoryName" required>
                    </div>
                    <div class="form-group">
                        <label>Couleur</label>
                        <input type="color" name="color" id="editCategoryColor">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary modal-cancel">Annuler</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editSubjectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Modifier le sujet</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <form method="POST" class="edit-subject-form">
                    <input type="hidden" name="action" value="update_subject">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="id" id="editSubjectId">
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="category_id" id="editSubjectCategoryId" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nom du sujet</label>
                        <input type="text" name="name" id="editSubjectName" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" id="editSubjectDescription">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary modal-cancel">Annuler</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editEntryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Modifier l'entrée</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <form method="POST" class="edit-entry-form">
                    <input type="hidden" name="action" value="update_entry">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="id" id="editEntryId">
                    <div class="form-group">
                        <label>Sujet</label>
                        <select name="subject_id" id="editEntrySubjectId" required>
                            <option value="">Sélectionner un sujet...</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['id'] ?>">
                                    <?= htmlspecialchars($subject['category_name']) ?> - <?= htmlspecialchars($subject['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Durée (minutes)</label>
                        <input type="number" name="duration_minutes" id="editEntryDuration" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="entry_date" id="editEntryDate" required>
                    </div>
                    <div class="form-group">
                        <label>Notes (optionnel)</label>
                        <textarea name="notes" id="editEntryNotes" rows="3"></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary modal-cancel">Annuler</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <script src="js/app.js"></script>
</body>
</html>
