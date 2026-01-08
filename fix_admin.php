<?php
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

// Update existing admin user to have admin privileges
$result = $db->execute(
    "UPDATE users SET is_admin = 1 WHERE username = 'admin'",
    []
);

if ($result) {
    echo "Admin user updated successfully. The admin user now has administrator privileges.\n";
    echo "You can now access user management in the 'Manage' tab.\n";
} else {
    echo "Failed to update admin user.\n";
}
?>
