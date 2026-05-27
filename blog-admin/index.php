<?php
require "connection.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!empty($email) && !empty($password)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([":email" => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $password == $user["password"]) {
                $_SESSION["admin_id"] = $user["id"];
                $_SESSION["admin_email"] = $user["email"];
                $_SESSION["role"] = $user["role"] ?? "user";
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-login-container">
    <div class="login-glass-card">
        <div class="login-header">
            <h1><i class="fas fa-film"></i> Admin Login</h1>
            <p>Access the blog administration dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars(
                $error,
            ); ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-login-form">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" id="email" name="email" required placeholder="admin@blog.com">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password:</label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
            </div>

            <button type="submit" class="netflix-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
    </div>
</body>
</html>
