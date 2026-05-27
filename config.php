<?php
// For PRODUCTION: Disable error display to users
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(E_ALL); // Still log all errors (usually to a file)

// For DEVELOPMENT: Display all errors for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "domain" => "",
    "secure" => false, // set to true if using HTTPS
    "httponly" => true,
    "samesite" => "Lax",
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define("DB_HOST", "localhost");
define("DB_NAME", "blite_db");
define("DB_USER", "blite_user");
define("DB_PASS", "bliss254");
define("BASE_URL", "http://localhost");

function getDB()
{
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO(
                "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
            );
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $db;
}

function requireAuth()
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin()
{
    if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
        header("Location: login.php");
        exit();
    }

    if ($_SESSION["role"] !== "admin") {
        header("Location: home.php");
        exit();
    }
}

function getBasePath()
{
    return __DIR__;
}

// CSRF Protection
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Get user role
function getUserRole($user_id)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT role FROM users WHERE id = :user_id");
    $stmt->execute([":user_id" => $user_id]);
    $result = $stmt->fetch();
    return $result ? $result["role"] : "user";
}

// NEW: Database setup function for first-time installation
function setupDatabase($db = null)
{
    if (!$db) {
        $db = getDB();
    }

    try {
        // Check if role column exists
        $db->query("SELECT role FROM users LIMIT 1");
    } catch (PDOException $e) {
        // Role column doesn't exist, create it
        $db->exec(
            "ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'",
        );
        error_log("Database: Added role column");
    }
}
?>
