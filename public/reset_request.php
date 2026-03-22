<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/User.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$baseUrl = rtrim($protocol . $host . $scriptPath, '/') . '/';

$translation = new Translation();
$user = new User();
$auth = new Auth();

$message = '';
$messageType = '';
$resetData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_request') {
    $username = trim($_POST['username'] ?? '');

    if (empty($username)) {
        $message = $translation->t('ui.auth.reset.username_required');
        $messageType = 'error';
    } else {
        $userData = $user->getUserByUsername($username);
        if (!$userData) {
            $message = $translation->t('ui.auth.reset.username_not_found');
            $messageType = 'error';
        } else {
            $resetResult = $user->createPasswordResetToken($userData['id']);
            if ($resetResult) {
                $resetData = $resetResult;
            } else {
                $message = $translation->t('ui.auth.reset.error_creating_token');
                $messageType = 'error';
            }
        }
    }
}

if ($auth->isAuthenticated() ?? false) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $translation->getLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translation->t('ui.auth.reset.title') ?> - <?= $translation->t('ui.header.title') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1><?= $translation->t('ui.header.title') ?></h1>
            <p class="login-subtitle"><?= $translation->t('ui.auth.reset.title') ?></p>

            <?php if ($message): ?>
                <div class="notification <?= $messageType ?> show" style="position: static; transform: none; margin-bottom: 20px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($resetData): ?>
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 48px; height: 48px; background: var(--accent-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i data-lucide="check" style="color: var(--accent);"></i>
                    </div>
                    <h2 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px;"><?= $translation->t('ui.auth.reset.success_title') ?></h2>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;"><?= $translation->t('ui.auth.reset.success_description') ?></p>
                </div>

                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; margin-bottom: 24px; text-align: center;">
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 8px;"><?= $translation->t('ui.auth.reset.pin_label') ?></p>
                    <div style="font-size: 2rem; font-weight: 700; letter-spacing: 0.2em; color: var(--accent); font-family: monospace;">
                        <?= htmlspecialchars($resetData['pin']) ?>
                    </div>
                </div>

                <a href="reset_password.php?token=<?= urlencode($resetData['token']) ?>" class="btn btn-primary" style="display: block; text-align: center; margin-bottom: 16px;">
                    <i data-lucide="key"></i>
                    <?= $translation->t('ui.auth.reset.enter_new_password') ?>
                </a>

                <div class="login-links">
                    <a href="login.php">← <?= $translation->t('ui.auth.back_to_login') ?></a>
                </div>

                <div style="margin-top: 20px; padding: 12px; background: var(--surface); border-radius: var(--radius); font-size: 0.75rem; word-break: break-all; color: var(--text-secondary);">
                    <code>reset_password.php?token=<?= htmlspecialchars($resetData['token']) ?></code>
                </div>

            <?php else: ?>
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 48px; height: 48px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i data-lucide="key" style="color: #d97706;"></i>
                    </div>
                    <h2 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px;"><?= $translation->t('ui.auth.reset.title') ?></h2>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;"><?= $translation->t('ui.auth.reset.description') ?></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="reset_request">
                    <div class="form-group">
                        <label><?= $translation->t('ui.auth.username') ?></label>
                        <input type="text" name="username" required autofocus placeholder="<?= $translation->t('ui.auth.username') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="send"></i>
                        <?= $translation->t('ui.auth.reset.send_code') ?>
                    </button>
                </form>

                <div class="login-links">
                    <a href="login.php">← <?= $translation->t('ui.auth.back_to_login') ?></a>
                </div>
            <?php endif; ?>

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
