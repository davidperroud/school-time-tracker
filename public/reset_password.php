<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/User.php';

$translation = new Translation();
$user = new User();

$message = '';
$messageType = '';
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$pin = $_GET['pin'] ?? $_POST['pin'] ?? '';
$userId = null;
$tokenValid = false;

// Valider par token
if (!empty($token)) {
    $tokenData = $user->validateResetToken($token);
    if ($tokenData) {
        $userId = $tokenData['user_id'];
        $tokenValid = true;
    }
}

// Valider par PIN
if (!$userId && !empty($pin)) {
    $pinData = $user->validateResetPin($pin);
    if ($pinData) {
        $userId = $pinData['user_id'];
        $token = $pinData['token'];
        $tokenValid = true;
    }
}

// Traitement du formulaire de nouveau mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $token = $_POST['token'] ?? '';
    $pin = $_POST['pin'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Essayer de valider par token ou PIN
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
        // Mettre à jour le mot de passe
        if ($user->updatePassword($userId, $newPassword)) {
            // Marquer le token comme utilisé
            $user->markTokenAsUsed($token);

            // Rediriger vers login avec message de succès
            header('Location: login.php?reset=success');
            exit;
        } else {
            $message = $translation->t('ui.auth.reset.password_update_error');
            $messageType = 'error';
        }
    }
}

// Rediriger si déjà connecté
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
    <title>
        <?= $translation->t('ui.auth.reset.new_password') ?> - <?= $translation->t('ui.header.title') ?>
    </title>
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
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary dark:text-secondary mb-2">
                    <?= $translation->t('ui.header.title') ?>
                </h1>
                <div class="language-selector mb-4">
                    <form method="get" class="inline">
                        <select name="lang" onchange="this.form.submit()" class="px-2 py-1 rounded border">
                            <?php foreach ($translation->getAvailableLanguages() as $code => $label): ?>
                                <option value="<?= $code ?>" <?= $translation->getLang() === $code ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="mb-4 p-4 rounded <?= $messageType === 'error' ? 'bg-red-100 text-red-700 border border-red-400' : 'bg-green-100 text-green-700 border border-green-400' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!$tokenValid): ?>
                <!-- Formulaire de saisie PIN si pas de token -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold mb-2">
                            <?= $translation->t('ui.auth.reset.enter_pin') ?>
                        </h2>
                    </div>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="reset_password">

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                <?= $translation->t('ui.auth.reset.pin_code') ?>
                            </label>
                            <input type="text" name="pin" required maxlength="6" pattern="[0-9]{6}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700 text-center font-mono text-xl tracking-widest">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                <?= $translation->t('ui.auth.password') ?>
                            </label>
                            <input type="password" name="password" required minlength="6" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                <?= $translation->t('ui.auth.confirm_password') ?>
                            </label>
                            <input type="password" name="confirm_password" required minlength="6" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                        </div>

                        <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary">
                            <?= $translation->t('ui.auth.reset.change_password') ?>
                        </button>
                    </form>

                    <div class="text-center mt-6">
                        <a href="reset_request.php" class="text-primary hover:underline">
                            ← <?= $translation->t('ui.auth.reset.request_new_code') ?>
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Formulaire de nouveau mot de passe avec token valide -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold mb-2">
                            <?= $translation->t('ui.auth.reset.new_password') ?>
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            <?= $translation->t('ui.auth.reset.enter_new_password_desc') ?>
                        </p>
                    </div>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="reset_password">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                <?= $translation->t('ui.auth.password') ?>
                            </label>
                            <input type="password" name="password" required minlength="6" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                <?= $translation->t('ui.auth.confirm_password') ?>
                            </label>
                            <input type="password" name="confirm_password" required minlength="6" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                        </div>

                        <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary">
                            <?= $translation->t('ui.auth.reset.change_password') ?>
                        </button>
                    </form>

                    <div class="text-center mt-6">
                        <a href="login.php" class="text-primary hover:underline">
                            ← <?= $translation->t('ui.auth.back_to_login') ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
