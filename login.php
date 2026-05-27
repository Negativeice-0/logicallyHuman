<?php
session_start();
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([":email" => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            // Update last login
            $updateStmt = $db->prepare(
                "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :user_id",
            );
            $updateStmt->execute([":user_id" => $user["id"]]);

            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } catch (PDOException $e) {
        $error = "Login error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Logical Human</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar glass-card">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php"><img src="images/logo.png" alt="Logical Human Logo"></a>
                <span>LogicalHuman</span>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="registration-form-container glass-card">
            <h2>Login to Your Account</h2>

            <?php if ($error): ?>
                <div style="color: red; padding: 10px; margin-bottom: 15px; border: 1px solid red; border-radius: 5px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[
                    "csrf_token"
                ]; ?>">

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required value="<?php echo $_POST[
                        "email"
                    ] ?? ""; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="button-group">
                    <input type="submit" value="Login" style="background: #007bff; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; width: 100%;">
                </div>
            </form>

            <p style="text-align: center; margin-top: 15px; color: white;">
                Don't have an account? <a href="registration.php" style="color: #ffd700;">Register here</a>
            </p>
        </section>
    </main>

    <footer class="footer glass-card">
        <p>&copy; 2024 Logical Human Creations. All Rights Reserved.</p>
    </footer>
</body>
</html>
