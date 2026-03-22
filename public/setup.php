<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/Auth.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$baseUrl = rtrim($protocol . $host . $scriptPath, '/') . '/';

$db = Database::getInstance();
$pdo = $db->getConnection();

$initSql = file_get_contents(__DIR__ . '/../init.sql');
if ($initSql !== false) {
    $pdo->exec($initSql);
}

$translation = new Translation();
$auth = new Auth();

$message = '';
$messageType = '';

if ($auth->hasUsers()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'setup_admin') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $language = $_POST['language'] ?? 'fr';

    if (empty($username) || empty($password)) {
        $message = $translation->t('ui.auth.enter_credentials');
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = $translation->t('ui.auth.passwords_no_match');
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = $translation->t('ui.auth.password_min_length');
        $messageType = 'error';
    } elseif ((new User())->usernameExists($username)) {
        $message = $translation->t('ui.auth.username_exists');
        $messageType = 'error';
    } else {
        $user = new User();
        $userId = $user->createUser($username, $password, $language, true);
        if ($userId) {
            $userData = $user->getUserById($userId);
            $auth->login($userData);
            header('Location: index.php');
            exit;
        } else {
            $message = $translation->t('ui.auth.account_creation_error');
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $translation->getLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translation->t('ui.auth.setup_admin') ?> - <?= $translation->t('ui.header.title') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1><?= $translation->t('ui.header.title') ?></h1>
            <p class="login-subtitle"><?= $translation->t('ui.auth.setup_description') ?></p>

            <?php if ($message): ?>
                <div class="notification <?= $messageType ?> show" style="position: static; transform: none; margin-bottom: 20px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="setup_admin">
                <div class="form-group">
                    <label><?= $translation->t('ui.auth.username') ?></label>
                    <input type="text" name="username" required autofocus placeholder="<?= $translation->t('ui.auth.username') ?>">
                </div>
                <div class="form-group">
                    <label><?= $translation->t('ui.auth.password') ?></label>
                    <input type="password" name="password" required minlength="6" placeholder="••••••">
                </div>
                <div class="form-group">
                    <label><?= $translation->t('ui.auth.confirm_password') ?></label>
                    <input type="password" name="confirm_password" required minlength="6" placeholder="••••••">
                </div>
                <div class="form-group">
                    <label><?= $translation->t('ui.auth.preferred_language') ?></label>
                    <select name="language">
                        <?php foreach ($translation->getAvailableLanguages() as $code => $label): ?>
                            <option value="<?= $code ?>" <?= $translation->getLang() === $code ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="user-plus"></i>
                    <?= $translation->t('ui.auth.create_admin') ?>
                </button>
            </form>

            <div style="margin-top: 24px; display: flex; justify-content: center;">
                <div class="language-selector">
                    <form method="get">
                        <select name="lang" onchange="this.form.submit()">
                            <?php foreach ($translation->getAvailableLanguages() as $code => $label): ?>
                                <option value="<?= $code ?>" <?= $translation->getLang() === $code ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer>
        School Time Tracker — created with love by <a href="https://davidperroud.com" target="_blank" rel="noopener">DavidPerroud.com</a>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</body>
</html>
