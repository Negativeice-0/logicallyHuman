<?php
session_start();

// Database configuration
define("DB_HOST", "localhost");
define("DB_NAME", "blite_db");
define("DB_USER", "blite_user");
define("DB_PASS", "bliss254");

function getDB()
{
    try {
        $db = new PDO(
            "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

function requireAuth()
{
    if (!isset($_SESSION["admin_id"])) {
        header("Location: index.php");
        exit();
    }
}
?>
