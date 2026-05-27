<?php
require "connection.php";
requireAuth();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db = getDB();

    if (isset($_POST["delete_user"])) {
        $user_id = $_POST["user_id"];

        if ($user_id == $_SESSION["admin_id"]) {
            $error = "You cannot delete your own account!";
        } else {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([":id" => $user_id]);
            $message = "User deleted successfully!";
        }
    } elseif (isset($_POST["update_role"])) {
        $user_id = $_POST["user_id"];
        $new_role = $_POST["role"];

        $stmt = $db->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([":role" => $new_role, ":id" => $user_id]);
        $message = "User role updated!";
    }
}

$db = getDB();
$stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span>👥</span>
                <span>User Management</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="usermanage.php" class="active"><i class="fas fa-users"></i> Users</a>
                <a href="photo.php"><i class="fas fa-images"></i> Photos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="management-header">
            <h1><i class="fas fa-users-cog"></i> User Management</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars(
                $message,
            ); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars(
                $error,
            ); ?></div>
        <?php endif; ?>

        <div class="users-table-container glass-card">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" class="no-data">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(
                                $user["id"],
                            ); ?></td>
                            <td><?php echo htmlspecialchars(
                                $user["email"],
                            ); ?></td>
                            <td>
                                <form method="POST" class="role-form">
                                    <input type="hidden" name="user_id" value="<?php echo $user[
                                        "id"
                                    ]; ?>">
                                    <select name="role" onchange="this.form.submit()">
                                        <option value="user" <?php echo $user[
                                            "role"
                                        ] == "user"
                                            ? "selected"
                                            : ""; ?>>User</option>
                                        <option value="admin" <?php echo $user[
                                            "role"
                                        ] == "admin"
                                            ? "selected"
                                            : ""; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            </td>
                            <td>
                                <?php if (
                                    $user["id"] != $_SESSION["admin_id"]
                                ): ?>
                                    <form method="POST" class="delete-form" onsubmit="return confirm('Delete this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user[
                                            "id"
                                        ]; ?>">
                                        <button type="submit" name="delete_user" class="delete-btn">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge current-user">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
