<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/Auth.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$baseUrl = rtrim($protocol . $host . $scriptPath, '/') . '/';

$translation = new Translation();
$auth = new Auth();

$message = '';
$messageType = '';

if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $message = $translation->t('ui.auth.reset.password_reset_success');
    $messageType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = $translation->t('ui.auth.enter_credentials');
        $messageType = 'error';
    } else {
        $userData = $auth->authenticate($username, $password);
        if ($userData) {
            $auth->login($userData);
            header('Location: index.php');
            exit;
        } else {
            $message = $translation->t('ui.auth.invalid_credentials');
            $messageType = 'error';
        }
    }
}

if ($auth->isAuthenticated()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $translation->getLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translation->t('ui.auth.login_tab') ?> - <?= $translation->t('ui.header.title') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1><?= $translation->t('ui.header.title') ?></h1>
            <p class="login-subtitle"><?= $translation->t('ui.auth.login_tab') ?></p>

            <?php if ($message): ?>
                <div class="notification <?= $messageType ?> show" style="position: static; transform: none; margin-bottom: 20px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label><?= $translation->t('ui.auth.username') ?></label>
                    <input type="text" name="username" required autofocus placeholder="<?= $translation->t('ui.auth.username') ?>">
                </div>
                <div class="form-group">
                    <label><?= $translation->t('ui.auth.password') ?></label>
                    <input type="password" name="password" required placeholder="<?= $translation->t('ui.auth.password') ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="log-in"></i>
                    <?= $translation->t('ui.auth.login') ?>
                </button>
            </form>

            <div class="login-links">
                <a href="reset_request.php"><?= $translation->t('ui.auth.reset.title') ?></a>
                <span style="color: var(--border); margin: 0 8px;">|</span>
                <a href="index.php"><?= $translation->t('ui.auth.back_to_home') ?></a>
            </div>

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
