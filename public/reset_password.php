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
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$pin = $_GET['pin'] ?? $_POST['pin'] ?? '';
$userId = null;
$tokenValid = false;

if (!empty($token)) {
    $tokenData = $user->validateResetToken($token);
    if ($tokenData) {
        $userId = $tokenData['user_id'];
        $tokenValid = true;
    }
}

if (!$userId && !empty($pin)) {
    $pinData = $user->validateResetPin($pin);
    if ($pinData) {
        $userId = $pinData['user_id'];
        $token = $pinData['token'];
        $tokenValid = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $token = $_POST['token'] ?? '';
    $pin = $_POST['pin'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $userId = null;

    if (!empty($token)) {
        $tokenData = $user->validateResetToken($token);
        if ($tokenData) {
            $userId = $tokenData['user_id'];
        }
    }

    if (!$userId && !empty($pin)) {
        $pinData = $user->validateResetPin($pin);
        if ($pinData) {
            $userId = $pinData['user_id'];
        }
    }

    if (!$userId) {
        $message = $translation->t('ui.auth.reset.invalid_token');
        $messageType = 'error';
    } elseif (empty($newPassword) || strlen($newPassword) < 6) {
        $message = $translation->t('ui.auth.password_min_length');
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = $translation->t('ui.auth.passwords_no_match');
        $messageType = 'error';
    } else {
        if ($user->updatePassword($userId, $newPassword)) {
            $user->markTokenAsUsed($token);
            header('Location: login.php?reset=success');
            exit;
        } else {
            $message = $translation->t('ui.auth.reset.password_update_error');
            $messageType = 'error';
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
    <title><?= $translation->t('ui.auth.reset.new_password') ?> - <?= $translation->t('ui.header.title') ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>css/style.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1><?= $translation->t('ui.header.title') ?></h1>
            <p class="login-subtitle"><?= $translation->t('ui.auth.reset.new_password') ?></p>

            <?php if ($message): ?>
                <div class="notification <?= $messageType ?> show" style="position: static; transform: none; margin-bottom: 20px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!$tokenValid): ?>
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 48px; height: 48px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i data-lucide="key" style="color: #d97706;"></i>
                    </div>
                    <h2 style="font-size: 1.1rem; font-weight: 600;"><?= $translation->t('ui.auth.reset.enter_pin') ?></h2>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    <div class="form-group">
                        <label><?= $translation->t('ui.auth.reset.pin_code') ?></label>
                        <input type="text" name="pin" required maxlength="6" pattern="[0-9]{6}" placeholder="000000" style="text-align: center; font-family: monospace; font-size: 1.5rem; letter-spacing: 0.2em;">
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.auth.password') ?></label>
                        <input type="password" name="password" required minlength="6" placeholder="••••••">
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.auth.confirm_password') ?></label>
                        <input type="password" name="confirm_password" required minlength="6" placeholder="••••••">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="check"></i>
                        <?= $translation->t('ui.auth.reset.change_password') ?>
                    </button>
                </form>

            <?php else: ?>
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="width: 48px; height: 48px; background: var(--accent-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i data-lucide="shield-check" style="color: var(--accent);"></i>
                    </div>
                    <h2 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px;"><?= $translation->t('ui.auth.reset.new_password') ?></h2>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;"><?= $translation->t('ui.auth.reset.enter_new_password_desc') ?></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="form-group">
                        <label><?= $translation->t('ui.auth.password') ?></label>
                        <input type="password" name="password" required minlength="6" placeholder="••••••">
                    </div>
                    <div class="form-group">
                        <label><?= $translation->t('ui.auth.confirm_password') ?></label>
                        <input type="password" name="confirm_password" required minlength="6" placeholder="••••••">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="check"></i>
                        <?= $translation->t('ui.auth.reset.change_password') ?>
                    </button>
                </form>
            <?php endif; ?>

            <div class="login-links">
                <a href="reset_request.php">← <?= $translation->t('ui.auth.reset.request_new_code') ?></a>
                <span style="color: var(--border); margin: 0 8px;">|</span>
                <a href="login.php"><?= $translation->t('ui.auth.login') ?></a>
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
