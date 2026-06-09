<?php
session_start();
require_once "config.php";

echo "<h1>Session Debug</h1>";
echo "<pre>";
echo "Session contents:\n";
print_r($_SESSION);
echo "</pre>";

// Check database role for the logged-in user (if any)
if (isset($_SESSION["user_id"])) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $user = $stmt->fetch();
    echo "<h2>Database record for user_id {$_SESSION['user_id']}</h2>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
} else {
    echo "<p style='color:red'>❌ No user logged in (user_id not in session)</p>";
}

// Also check if the admin email/password works directly
echo "<h2>Test password verification for admin@blog.com</h2>";
$stmt = $db->prepare("SELECT password, role FROM users WHERE email = 'admin@blog.com'");
$stmt->execute();
$admin = $stmt->fetch();
if ($admin) {
    echo "Stored role: " . $admin['role'] . "<br>";
    $testPassword = 'admin123'; // change to the password you set
    if (password_verify($testPassword, $admin['password'])) {
        echo "✅ Password 'admin123' is correct for admin@blog.com";
    } else {
        echo "❌ Password 'admin123' does NOT match the stored hash";
    }
} else {
    echo "❌ Admin user not found in database";
}