<?php
require "connection.php";

$db = getDB();
$stmt = $db->query(
    "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'photos' ORDER BY ordinal_position",
);
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Photos Table Structure:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Column Name</th><th>Data Type</th><th>Nullable</th></tr>";
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>" . $col["column_name"] . "</td>";
    echo "<td>" . $col["data_type"] . "</td>";
    echo "<td>" . $col["is_nullable"] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
