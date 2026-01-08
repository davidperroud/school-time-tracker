<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/Auth.php';

$translation = new Translation();
$auth = new Auth();

$message = '';
$messageType = '';

// Vérifier s'il y a déjà des utilisateurs
if ($auth->hasUsers()) {
    header('Location: login.php');
    exit;
}

// Traitement du formulaire de création d'admin
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
        $userId = $user->createUser($username, $password, $language, true); // true for admin
        if ($userId) {
            // Connecter automatiquement l'admin
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
    <title>
        <?= $translation->t('ui.auth.setup_admin') ?> - <?= $translation->t('ui.header.title') ?>
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
                <p class="text-lg mb-4">
                    <?= $translation->t('ui.auth.setup_description') ?>
                </p>
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

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="setup_admin">

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            <?= $translation->t('ui.auth.username') ?> *
                        </label>
                        <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            <?= $translation->t('ui.auth.password') ?> *
                        </label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            <?= $translation->t('ui.auth.confirm_password') ?> *
                        </label>
                        <input type="password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            <?= $translation->t('ui.auth.preferred_language') ?> *
                        </label>
                        <select name="language" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                            <?php foreach ($translation->getAvailableLanguages() as $code => $label): ?>
                                <option value="<?= $code ?>" <?= $translation->getLang() === $code ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary">
                        <?= $translation->t('ui.auth.create_admin') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>