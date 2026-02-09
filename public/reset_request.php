<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/User.php';

$translation = new Translation();
$user = new User();
$auth = new Auth();

$message = '';
$messageType = '';
$resetData = null;

// Traitement du formulaire de demande de réinitialisation
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
        <?= $translation->t('ui.auth.reset.title') ?> - <?= $translation->t('ui.header.title') ?>
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

            <?php if ($resetData): ?>
                <!-- Affichage du code PIN après demande réussie -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold mb-2">
                            <?= $translation->t('ui.auth.reset.success_title') ?>
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            <?= $translation->t('ui.auth.reset.success_description') ?>
                        </p>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-800 dark:text-blue-200 mb-2">
                            <?= $translation->t('ui.auth.reset.pin_label') ?>
                        </p>
                        <div class="text-3xl font-mono font-bold text-center tracking-widest text-blue-700 dark:text-blue-300">
                            <?= htmlspecialchars($resetData['pin']) ?>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="reset_password.php?token=<?= urlencode($resetData['token']) ?>" class="block w-full bg-primary text-white py-3 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary mb-3">
                            <?= $translation->t('ui.auth.reset.enter_new_password') ?>
                        </a>
                        <a href="login.php" class="text-primary hover:underline">
                            ← <?= $translation->t('ui.auth.back_to_login') ?>
                        </a>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <?= $translation->t('ui.auth.reset.token_link_below') ?>
                    </p>
                    <code class="block bg-gray-100 dark:bg-gray-800 p-2 rounded text-xs break-all">
                        reset_password.php?token=<?= htmlspecialchars($resetData['token']) ?>
                    </code>
                </div>

            <?php else: ?>
                <!-- Formulaire de demande de réinitialisation -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold mb-2">
                            <?= $translation->t('ui.auth.reset.title') ?>
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            <?= $translation->t('ui.auth.reset.description') ?>
                        </p>
                    </div>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="reset_request">

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                <?= $translation->t('ui.auth.username') ?>
                            </label>
                            <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                        </div>

                        <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary">
                            <?= $translation->t('ui.auth.reset.send_code') ?>
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
