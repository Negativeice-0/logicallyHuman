<?php
session_start();
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $dob = $_POST["dob"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $country = $_POST["country"] ?? "";
    $user_bio = trim($_POST["user_bio"] ?? "");

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        try {
            $db = getDB();

            // Check if user exists - NAMED PLACEHOLDER REFACTOR: :username, :email
            $checkStmt = $db->prepare(
                "SELECT id FROM users WHERE username = :username OR email = :email",
            );
            $checkStmt->execute([":username" => $username, ":email" => $email]);

            if ($checkStmt->rowCount() > 0) {
                $error = "Username or email already exists";
            } else {
                // Create user - NAMED PLACEHOLDER REFACTOR: All ? replaced with named placeholders
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare(
                    "INSERT INTO users (username, email, password, dob, gender, country, user_bio, role)
                     VALUES (:username, :email, :password, :dob, :gender, :country, :user_bio, :role)",
                );

                if (
                    $stmt->execute([
                        ":username" => $username,
                        ":email" => $email,
                        ":password" => $hashed_password,
                        ":dob" => $dob,
                        ":gender" => $gender,
                        ":country" => $country,
                        ":user_bio" => $user_bio,
                        ":role" => "user",
                    ])
                ) {
                    $_SESSION["user_id"] = $db->lastInsertId();
                    $_SESSION["username"] = $username;
                    $_SESSION["email"] = $email;

                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}

// Country list (unchanged)
$countries = [
    "United States",
    "United Kingdom",
    "Canada",
    "Australia",
    "Germany",
    "France",
    "Japan",
    "South Korea",
    "Brazil",
    "India",
    "China",
    "South Africa",
    "Nigeria",
    "Kenya",
    "Egypt",
    "Ghana",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Logical Human</title>
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
            <h2>Create Your Account</h2>


            <?php if ($error): ?>
                <div style="color: red; padding: 10px; margin-bottom: 15px; border: 1px solid red; border-radius: 5px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" name="register">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[
                    "csrf_token"
                ]; ?>">

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter Username" required value="<?php echo $_POST[
                        "username"
                    ] ?? ""; ?>">
                    <span class="error-message" id="username-error"></span>
                </div>

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required value="<?php echo $_POST[
                        "email"
                    ] ?? ""; ?>">
                    <span class="error-message" id="email-error"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <span class="error-message" id="password-error"></span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="error-message" id="confirm_password-error"></span>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" required value="<?php echo $_POST[
                        "dob"
                    ] ?? ""; ?>">
                    <span class="error-message" id="dob-error"></span>
                </div>

                <div class="form-group">
                    <label>Gender:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="male" <?php echo ($_POST[
                                "gender"
                            ] ??
                                "") ===
                            "male"
                                ? "checked"
                                : ""; ?>> Male
                        </label>
                        <label>
                            <input type="radio" name="gender" value="female" <?php echo ($_POST[
                                "gender"
                            ] ??
                                "") ===
                            "female"
                                ? "checked"
                                : ""; ?>> Female
                        </label>
                    </div>
                    <span class="error-message" id="gender-error"></span>
                </div>

                <div class="form-group">
                    <label for="country">Country:</label>
                    <select id="country" name="country" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: black; color: white;">
                        <option value="">Select your country</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country; ?>" <?php echo ($_POST[
    "country"
] ??
    "") ===
$country
    ? "selected"
    : ""; ?>>
                                <?php echo $country; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-message" id="country-error"></span>
                </div>

                <div class="form-group">
                    <label for="user_bio">Bio/Introduction:</label>
                    <textarea id="user_bio" name="user_bio" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars(
                        $_POST["user_bio"] ?? "",
                    ); ?></textarea>
                    <span class="error-message" id="user_bio-error"></span>
                </div>

                <div class="button-group">
                    <button type="button" id="reset-btn" onclick="resetForm()" style="background: #dc3545; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer;">Reset</button>
                    <input type="submit" id="submit-btn" value="Register" style="background: #28a745; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; flex-grow: 1;" disabled>
                </div>
            </form>

            <p style="text-align: center; margin-top: 15px; color: white;">
                Already have an account? <a href="login.php" style="color: #ffd700;">Login here</a>
            </p>
        </section>
    </main>

    <footer class="footer glass-card">
        <p>&copy; 2024 Logical Human Creations. All Rights Reserved.</p>
    </footer>

    <script>
    function resetForm() {
        document.querySelector('form[name="register"]').reset();
        document.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
        });
        document.getElementById('submit-btn').disabled = false;
    }
    </script>
    <script src="script.js"></script>
</body>
</html>
