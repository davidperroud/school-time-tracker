<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/Auth.php';

$translation = new Translation();
$db = Database::getInstance();
$auth = new Auth();

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

        case 'change_language':
            $lang = $_POST['lang'] ?? 'fr';
            $auth->updateLanguagePreference($lang);
            // Redirect to refresh the page with new language
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;

        case 'add_user':
            if ($auth->isAdmin()) {
                $user = new User();
                $userId = $user->createUser(
                    $_POST['username'],
                    $_POST['password'],
                    $_POST['language'] ?? 'fr',
                    !empty($_POST['is_admin'])
                );
                if ($userId && $isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
            }
            break;

        case 'update_user':
            if ($auth->isAdmin()) {
                $user = new User();
                $result = $user->updateUser(
                    $_POST['id'],
                    $_POST['username'],
                    $_POST['language'] ?? 'fr',
                    isset($_POST['is_admin']) ? !empty($_POST['is_admin']) : null
                );
                if ($result && $isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
            }
            break;

        case 'delete_user':
            if ($auth->isAdmin() && $_POST['id'] != $auth->getUserId()) { // Prevent self-deletion
                $user = new User();
                $result = $user->deleteUser($_POST['id']);
                if ($result && $isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
            }
            break;

        case 'update_password':
            if ($auth->isAdmin() || $_POST['id'] == $auth->getUserId()) {
                $user = new User();
                $result = $user->updatePassword($_POST['id'], $_POST['password']);
                if ($result && $isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
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
<html lang="<?= $translation->getLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translation->t('ui.header.title') ?></title>
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
        <header class="flex justify-between items-center py-6">
            <h1 class="text-4xl font-bold text-primary dark:text-secondary"><?= $translation->t('ui.header.title') ?></h1>
            <div class="header-controls flex gap-2 items-center">
                <?php if ($auth->isAuthenticated()): ?>
                    <span class="text-gray-700 dark:text-gray-300 mr-2"><?= htmlspecialchars($auth->getUsername()) ?></span>
                <?php endif; ?>

                <div class="language-selector">
                    <form method="post" id="langForm" class="inline">
                        <input type="hidden" name="action" value="change_language">
                        <select name="lang" onchange="changeLanguage(this.value)" class="px-2 py-1 rounded border">
                            <?php foreach ($translation->getAvailableLanguages() as $code => $label): ?>
                                <option value="<?= $code ?>" <?= $translation->getLang() === $code ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <button id="themeToggle" class="px-2 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-400 dark:border-gray-600 transition text-xl">🌙</button>

                <?php if ($auth->isAuthenticated()): ?>
                    <a href="logout.php" class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-600 transition">
                        <?= $translation->t('ui.auth.logout') ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="px-4 py-2 rounded bg-green-500 text-white hover:bg-green-600 transition">
                        <?= $translation->t('ui.auth.login') ?>
                    </a>
                <?php endif; ?>
            </div>
        </header>
        <nav class="tabs flex gap-2 mb-6 border-b-2 border-gray-300 dark:border-gray-700">
            <button class="tab active" data-tab="dashboard"><?= $translation->t('ui.navigation.dashboard') ?></button>
            <button class="tab" data-tab="entry"><?= $translation->t('ui.navigation.new_entry') ?></button>
            <button class="tab" data-tab="entries"><?= $translation->t('ui.navigation.entries') ?></button>
            <button class="tab" data-tab="manage"><?= $translation->t('ui.navigation.manage') ?></button>
            <button class="tab" data-tab="reports"><?= $translation->t('ui.navigation.reports') ?></button>
            <?php if ($auth->isAdmin()): ?>
            <button class="tab" data-tab="admin">Admin</button>
            <?php endif; ?>
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
                            <label><?= $translation->t('ui.forms.subject') ?></label>
                            <select name="subject_id" required>
                                <option value=""><?= $translation->t('ui.placeholders.select_subject') ?></option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>">
                                        <?= htmlspecialchars($subject['category_name']) ?> - <?= htmlspecialchars($subject['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><?= $translation->t('ui.forms.duration') ?></label>
                            <input type="number" name="duration_minutes" min="1" required>
                        </div>

                        <div class="form-group">
                            <label><?= $translation->t('ui.forms.date') ?></label>
                            <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label><?= $translation->t('ui.forms.notes') ?></label>
                            <textarea name="notes" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary"><?= $translation->t('ui.buttons.save') ?></button>
                    </form>
                </div>

                <div class="entry-list-column">
                    <div class="recent-entries">
                        <h3><?= $translation->t('ui.auth.recent_entries') ?></h3>
                        <div id="recentEntries"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toutes les entrées -->
        <div class="tab-content" id="entries">
            <div class="entries-controls">
                <div class="filter-controls">
                    <label><?= $translation->t('ui.search.placeholder') ?></label>
                    <input type="text" id="entriesSearch" placeholder="<?= $translation->t('ui.search.placeholder') ?>" value="">
                    <label><?= $translation->t('ui.forms.date') ?></label>
                    <input type="date" id="entriesDateFilter" value="">
                    <button onclick="clearDateFilter()" class="btn btn-secondary"><?= $translation->t('ui.buttons.all') ?></button>
                </div>
            </div>

            <div id="allEntriesList"></div>
        </div>

        <!-- Gestion -->
        <div class="tab-content" id="manage">
            <div class="management-grid">
                <div class="form-card">
                    <h3><?= $translation->t('ui.forms.category') ?>s</h3>
                    <form method="POST" class="category-form">
                        <input type="hidden" name="action" value="add_category">
                        <input type="hidden" name="ajax" value="1">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="<?= $translation->t('ui.forms.name') ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="color" name="color" value="#3b82f6">
                        </div>
                        <button type="submit" class="btn btn-small"><?= $translation->t('ui.buttons.add') ?></button>
                    </form>

                    <div class="list" id="categoriesList"></div>
                </div>

                <div class="form-card">
                    <h3><?= $translation->t('ui.forms.subject') ?>s</h3>
                    <form method="POST" class="subject-form">
                        <input type="hidden" name="action" value="add_subject">
                        <input type="hidden" name="ajax" value="1">
                        <div class="form-group">
                            <select name="category_id" required>
                                <option value=""><?= $translation->t('ui.placeholders.category') ?></option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" name="name" placeholder="<?= $translation->t('ui.placeholders.subject_name') ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="description" placeholder="<?= $translation->t('ui.forms.description') ?>">
                        </div>
                        <button type="submit" class="btn btn-small"><?= $translation->t('ui.buttons.add') ?></button>
                    </form>

                    <div class="list" id="subjectsList"></div>
                </div>
            </div>
        </div>

        <!-- Administration -->
        <?php if ($auth->isAdmin()): ?>
        <div class="tab-content" id="admin">
            <div class="management-grid">
                <div class="form-card">
                    <h3><?= $translation->t('ui.manage.users') ?></h3>
                    <form method="POST" class="user-form">
                        <input type="hidden" name="action" value="add_user">
                        <input type="hidden" name="ajax" value="1">
                        <div class="form-group">
                            <input type="text" name="username" placeholder="<?= $translation->t('ui.auth.username') ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" placeholder="<?= $translation->t('ui.auth.password') ?>" required>
                        </div>
                        <div class="form-group">
                            <select name="language">
                                <option value="fr">Français</option>
                                <option value="en">English</option>
                                <option value="de">Deutsch</option>
                                <option value="it">Italiano</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_admin" value="1">
                                <span class="checkmark"></span>
                                <?= $translation->t('ui.manage.admin') ?>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-small"><?= $translation->t('ui.buttons.add') ?></button>
                    </form>

                    <div class="list" id="usersList"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rapports -->
        <div class="tab-content" id="reports">
            <div class="report-controls">
                <select id="reportPeriod">
                    <option value="day"><?= $translation->t('ui.reports.day') ?></option>
                    <option value="week"><?= $translation->t('ui.reports.week') ?></option>
                    <option value="month"><?= $translation->t('ui.reports.month') ?></option>
                </select>

                <!-- Contrôles pour jour et semaine -->
                <div id="dateInputContainer">
                    <label><?= $translation->t('ui.reports.start_date') ?></label>
                    <input type="date" id="reportDate" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Contrôles pour mois (cachés par défaut) -->
                <div id="monthInputContainer" style="display: none;">
                    <label><?= $translation->t('ui.reports.month_label') ?></label>
                    <select id="reportMonth">
                        <?php foreach ($translation->t('ui.date.months') as $i => $month): ?>
                            <option value="<?= $i+1 ?>"><?= $month ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label><?= $translation->t('ui.reports.year') ?></label>
                    <select id="reportYear">
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= $year == $currentYear ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button onclick="loadReport()" class="btn btn-primary"><?= $translation->t('ui.buttons.generate') ?></button>
                <button onclick="exportReportPDF()" class="btn btn-secondary" id="exportPdfBtn" style="display: none;"><?= $translation->t('ui.buttons.export_pdf') ?></button>
            </div>

            <div id="reportContent"></div>
        </div>

        <!-- Modales d'édition (déplacées en dehors des onglets pour être accessibles partout) -->
        <div id="editCategoryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?= $translation->t('ui.modals.edit_category') ?></h3>
                    <button class="modal-close">&times;</button>
                </div>
                <form method="POST" class="edit-category-form">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="id" id="editCategoryId">
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.name') ?></label>
                        <input type="text" name="name" id="editCategoryName" required>
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.color') ?></label>
                        <input type="color" name="color" id="editCategoryColor">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary modal-cancel"><?= $translation->t('ui.buttons.cancel') ?></button>
                        <button type="submit" class="btn btn-primary"><?= $translation->t('ui.buttons.edit') ?></button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editSubjectModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?= $translation->t('ui.modals.edit_subject') ?></h3>
                    <button class="modal-close">&times;</button>
                </div>
                <form method="POST" class="edit-subject-form">
                    <input type="hidden" name="action" value="update_subject">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="id" id="editSubjectId">
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.category') ?></label>
                        <select name="category_id" id="editSubjectCategoryId" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.subject_name') ?></label>
                        <input type="text" name="name" id="editSubjectName" required>
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.description') ?></label>
                        <input type="text" name="description" id="editSubjectDescription">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary modal-cancel"><?= $translation->t('ui.buttons.cancel') ?></button>
                        <button type="submit" class="btn btn-primary"><?= $translation->t('ui.buttons.edit') ?></button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editEntryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?= $translation->t('ui.modals.edit_entry') ?></h3>
                    <button class="modal-close">&times;</button>
                </div>
                <form method="POST" class="edit-entry-form">
                    <input type="hidden" name="action" value="update_entry">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="id" id="editEntryId">
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.subject') ?></label>
                        <select name="subject_id" id="editEntrySubjectId" required>
                            <option value=""><?= $translation->t('ui.placeholders.select_subject') ?></option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['id'] ?>">
                                    <?= htmlspecialchars($subject['category_name']) ?> - <?= htmlspecialchars($subject['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.duration') ?></label>
                        <input type="number" name="duration_minutes" id="editEntryDuration" min="1" required>
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.date') ?></label>
                        <input type="date" name="entry_date" id="editEntryDate" required>
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.forms.notes') ?></label>
                        <textarea name="notes" id="editEntryNotes" rows="3"></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary modal-cancel"><?= $translation->t('ui.buttons.cancel') ?></button>
                        <button type="submit" class="btn btn-primary"><?= $translation->t('ui.buttons.edit') ?></button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editUserModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?= $translation->t('ui.modals.edit_user') ?></h3>
                    <button class="modal-close">&times;</button>
                </div>
                <form method="POST" class="edit-user-form">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="id" id="editUserId">
                    <div class="form-group">
                        <label><?= $translation->t('ui.auth.username') ?></label>
                        <input type="text" name="username" id="editUserUsername" required>
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.auth.preferred_language') ?></label>
                        <select name="language" id="editUserLanguage">
                            <option value="fr">Français</option>
                            <option value="en">English</option>
                            <option value="de">Deutsch</option>
                            <option value="it">Italiano</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_admin" id="editUserIsAdmin" value="1">
                            <span class="checkmark"></span>
                            <?= $translation->t('ui.manage.admin') ?>
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary modal-cancel"><?= $translation->t('ui.buttons.cancel') ?></button>
                        <button type="submit" class="btn btn-primary"><?= $translation->t('ui.buttons.edit') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <script src="js/app.js"></script>
</body>
</html>
