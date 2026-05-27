<?php
require_once "config.php";

try {
    $db = getDB();
    echo "✅ Database connection successful!<br>";

    // Test if tables exist
    $tables = ["users", "posts", "comments"];
    foreach ($tables as $table) {
        $result = $db->query(
            "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table')",
        );
        $exists = $result->fetch()["exists"] ? "✅" : "❌";
        echo "$exists Table '$table' exists: " .
            ($exists ? "YES" : "NO") .
            "<br>";
    }
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>
