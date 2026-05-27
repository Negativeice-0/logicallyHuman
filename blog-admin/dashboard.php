<?php
require "connection.php";
requireAuth();

$db = getDB();
$stats = [];

$stmt = $db->query("SELECT COUNT(*) as count FROM users");
$stats["users"] = $stmt->fetch(PDO::FETCH_ASSOC)["count"];

$stmt = $db->query("SELECT COUNT(*) as count FROM photos");
$stats["photos"] = $stmt->fetch(PDO::FETCH_ASSOC)["count"];

$stmt = $db->query(
    "SELECT COUNT(*) as count FROM photos WHERE created_at >= NOW() - INTERVAL '7 days'",
);
$stats["recent_photos"] = $stmt->fetch(PDO::FETCH_ASSOC)["count"];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span>🎬</span>
                <span>Admin Dashboard</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="usermanage.php"><i class="fas fa-users"></i> Users</a>
                <a href="photo.php"><i class="fas fa-images"></i> Photos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-hero">
            <h1>Welcome, <?php echo htmlspecialchars(
                $_SESSION["admin_email"],
            ); ?>!</h1>
            <p>Admin Control Center - Manage your blog efficiently</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats["users"]; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats["photos"]; ?></div>
                <div class="stat-label">Total Photos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats[
                    "recent_photos"
                ]; ?></div>
                <div class="stat-label">Recent Photos (7d)</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>User Management</h3>
                <p>Manage user accounts and permissions</p>
                <a href="usermanage.php" class="card-link">
                    <i class="fas fa-cog"></i> Manage Users
                </a>
            </div>

            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-images"></i>
                </div>
                <h3>Photo Management</h3>
                <p>Upload and manage gallery photos</p>
                <a href="photo.php" class="card-link">
                    <i class="fas fa-upload"></i> Manage Photos
                </a>
            </div>
        </div>
    </div>
</body>
</html>
