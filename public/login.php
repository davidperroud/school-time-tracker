<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/Auth.php';

$translation = new Translation();
$auth = new Auth();

$message = '';
$messageType = '';

// Message de succès après réinitialisation du mot de passe
if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $message = $translation->t('ui.auth.reset.password_reset_success');
    $messageType = 'success';
}

// Traitement du formulaire de connexion
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

// Registration disabled - only admins can create users

// Rediriger si déjà connecté
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
    <title>
        <?= $translation->t('ui.auth.login_tab') ?> - <?= $translation->t('ui.header.title') ?>
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

            <!-- Formulaire de connexion -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="login">

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            <?= $translation->t('ui.auth.username') ?>
                        </label>
                        <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            <?= $translation->t('ui.auth.password') ?>
                        </label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-700">
                    </div>

                    <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary">
                        <?= $translation->t('ui.auth.login') ?>
                    </button>
                </form>
            </div>

            <div class="text-center mt-6">
                <a href="index.php" class="text-primary hover:underline">
                    ← <?= $translation->t('ui.auth.back_to_home') ?>
                </a>
            </div>

            <div class="text-center mt-4">
                <a href="reset_request.php" class="text-sm text-gray-500 hover:text-primary">
                    <?= $translation->t('ui.auth.reset.title') ?>
                </a>
            </div>
        </div>
    </div>


</body>
</html>
