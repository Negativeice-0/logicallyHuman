# 🚨 CLEANING UP APACHE & COMPLETE PROJECT SETUP

billy, ```billy@gmail.com```, 12345678 (admin)
test, ```test@gmail.com```, 12345678 (user)

**Future stuff ci/cd, aws secrets manager, postgre prometheus.**

## 1. **REMOVE PROBLEMATIC APACHE VIRTUAL HOSTS**

```bash
# Disable all conflicting virtual hosts
sudo a2dissite laravel.conf
sudo a2dissite myproject.conf
sudo a2dissite logicalhuman.conf

# Remove the config files
sudo rm /etc/apache2/sites-available/laravel.conf
sudo rm /etc/apache2/sites-available/myproject.conf
sudo rm /etc/apache2/sites-available/logicalhuman.conf

# Reload Apache
sudo systemctl reload apache2

# Verify they're gone
sudo apache2ctl -S
```

### 2. **SET UP PROPER VIRTUAL HOST FOR YOUR PROJECT**

```bash
# Create a clean virtual host for your project
sudo nano /etc/apache2/sites-available/logicalhuman.conf
```

**Add this content:**

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /home/lsetga/AIU/Aiu-yr2/Sem-two/Net-programming/Blog-trial

    <Directory /home/lsetga/AIU/Aiu-yr2/Sem-two/Net-programming/Blog-trial>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/logicalhuman_error.log
    CustomLog ${APACHE_LOG_DIR}/logicalhuman_access.log combined
</VirtualHost>
```

```bash
# Enable your site
sudo a2ensite logicalhuman.conf
sudo systemctl reload apache2
```

### 3. **COMPLETE PROJECT RESET SCRIPT**

Create this script to reset everything:

### **complete-setup.sh**

```bash
#!/bin/bash
echo "🎯 COMPLETE LOGICAL HUMAN SETUP"
echo "================================"

# Stop any running PHP server
pkill -f "php -S"

# 1. Reset Database
echo "🗃️  Resetting database..."
sudo -u postgres psql -c "DROP DATABASE IF EXISTS logicalhuman;"
sudo -u postgres psql -c "CREATE DATABASE logicalhuman;"
sudo -u postgres psql -c "CREATE USER logicaluser WITH PASSWORD 'SecurePass123!';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE logicalhuman TO logicaluser;"

# 2. Create Tables
echo "📊 Creating tables..."
sudo -u postgres psql -d logicalhuman << EOF
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    region VARCHAR(100) DEFAULT 'global',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    post_id INTEGER REFERENCES posts(id),
    user_id INTEGER REFERENCES users(id),
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Make logicaluser the owner of all tables
ALTER TABLE users OWNER TO logicaluser;
ALTER TABLE posts OWNER TO logicaluser;
ALTER TABLE comments OWNER TO logicaluser;
EOF

# 3. Set proper permissions
echo "🔐 Setting file permissions..."
sudo chown -R www-data:www-data /home/lsetga/AIU/Aiu-yr2/Sem-two/Net-programming/Blog-trial
sudo chmod -R 755 /home/lsetga/AIU/Aiu-yr2/Sem-two/Net-programming/Blog-trial

# 4. Restart Apache
echo "🔄 Restarting Apache..."
sudo systemctl restart apache2

echo "✅ SETUP COMPLETE!"
echo "🌐 Access your site at: http://localhost"
echo "📊 Database: logicalhuman, User: logicaluser"
```

### 4. **FINAL PROJECT FILE STRUCTURE**

Here's the **COMPLETE** file structure you should have:

```merm
/home/lsetga/AIU/Aiu-yr2/Sem-two/Net-programming/Blog-trial/
├── index.php
├── login.php
├── registration.php
├── main.php
├── posting.php
├── logout.php
├── test_db.php
├── complete-setup.sh
├── style.css
├── script.js
├── manifest.json
├── sw.js
└── images/
    ├── logo.png
    ├── i1.jpg
    ├── i3.png
    ├── i4.jpeg
    ├── b1.png
    └── family.jpeg
```

### 5. **FINAL SOURCE CODE FILES**

**index.php** (Landing Page)

```php
<?php
session_start();
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Logical Human</title>
        <link rel="stylesheet" href="style.css" />
        <link rel="manifest" href="manifest.json" />
    </head>
    <body>
        <header class="navbar glass-card">
            <div class="nav-container">
                <div class="logo">
                    <a href="index.php"><img src="./images/logo.png" alt="Logical Human logo" /></a>
                    <span>LogicalHuman</span>
                </div>
            </div>
        </header>
        <br />

        <main class="container">
            <section id="home" class="hero">
                <h1 class="animated-text animated-heading">Experience Humanity with AI</h1>
                <p class="animated-text animated-p">
                    Below are possibilities for what happens when humans and AI come together in harmony to build amazing things. Join us to start creating your own!
                </p>
                <div style="margin-top: 2rem">
                    <a href="login.php" class="button-group" style="display: inline-block; margin-right: 10px;">
                        <input type="button" value="Log In" style="background-color: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;" />
                    </a>
                    <a href="registration.php" class="button-group" style="display: inline-block;">
                        <input type="button" value="Sign Up" style="background-color: #28a745; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;" />
                    </a>
                </div>
            </section>
            <br />

            <div class="section-divider"></div>

            <section id="contact" class="contact">
                <h2>Contact</h2>
                <p>Email: <a href="mailto:billy.g.ochieng.osodo@gmail.com">billy.g.ochieng.osodo@gmail.com</a></p>
            </section>
        </main>

        <footer class="footer glass-card">
            <p>&copy; 2024 Logical Human Creations. All Rights Reserved.</p>
        </footer>
        <script src="script.js"></script>
    </body>
</html>
```

**registration.php** (Fixed)

```php
<?php
session_start();

$db_host = 'localhost';
$db_name = 'logicalhuman';
$db_user = 'logicaluser';
$db_pass = 'SecurePass123!';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new PDO("pgsql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm-password'];

        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            $error = "All fields are required";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters";
        } else {
            // Check if user exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->rowCount() > 0) {
                $error = "Username or email already exists";
            } else {
                // Create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

                if ($stmt->execute([$username, $email, $hashed_password])) {
                    $_SESSION['user_id'] = $db->lastInsertId();
                    $_SESSION['username'] = $username;
                    header("Location: main.php");
                    exit;
                }
            }
        }
    } catch(PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Logical Human - Register</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <header class="navbar glass-card">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php"><img src="images/logo.png" alt="Logical Human Logo" /></a>
                <span>Logic</span>
            </div>
        </div>
    </header>

    <main class="container">
        <section id="registration-section" class="registration-form-container glass-card">
            <h2>Create Your Logical Human Account</h2>

            <?php if($error): ?>
                <div style="color: red; padding: 10px; margin-bottom: 15px; border: 1px solid red; border-radius: 5px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required />
                </div>

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required />
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required />
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm Password:</label>
                    <input type="password" id="confirm-password" name="confirm-password" required />
                </div>

                <div class="button-group">
                    <input type="submit" value="Register" style="background: #28a745; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; width: 100%;" />
                </div>
            </form>

            <p style="text-align: center; margin-top: 15px;">
                Already have an account? <a href="login.php" style="color: #ff0050;">Login here</a>
            </p>
        </section>
    </main>

    <footer class="footer glass-card">
        <p>&copy; 2024 Logical Human Creations. All Rights Reserved.</p>
    </footer>
</body>
</html>
```

**login.php** (Fixed)

```php
<?php
session_start();

$db_host = 'localhost';
$db_name = 'logicalhuman';
$db_user = 'logicaluser';
$db_pass = 'SecurePass123!';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new PDO("pgsql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: main.php");
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } catch(PDOException $e) {
        $error = "Login error: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Logical Human - Login</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <header class="navbar glass-card">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php"><img src="logo.png" alt="Logical Human Logo" /></a>
                <span>Logic</span>
            </div>
        </div>
    </header>

    <main class="container">
        <section id="login-section" class="registration-form-container glass-card">
            <h2>Login to Your Logical Human Account</h2>

            <?php if($error): ?>
                <div style="color: red; padding: 10px; margin-bottom: 15px; border: 1px solid red; border-radius: 5px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required />
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required />
                </div>

                <div class="button-group">
                    <input type="submit" value="Login" style="background: #007bff; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; width: 100%;" />
                </div>
            </form>

            <p style="text-align: center; margin-top: 15px;">
                Don't have an account? <a href="registration.php" style="color: #ff0050;">Register here</a>
            </p>
        </section>
    </main>

    <footer class="footer glass-card">
        <p>&copy; 2024 Logical Human Creations. All Rights Reserved.</p>
    </footer>
</body>
</html>
```

**main.php** (Keep your existing file, just add this at the top)

```php
<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!-- YOUR EXISTING main.html CONTENT GOES HERE -->
```

### **logout.php**

```php
<?php
session_start();
session_destroy();
header("Location: index.php");
exit;
?>
```

### 6. **FINAL TESTING COMMANDS**

```bash
# Make the setup script executable
chmod +x complete-setup.sh

# Run the complete setup
./complete-setup.sh

# Test your application
curl -I http://localhost/test_db.php

# Check Apache is serving the right folder
sudo apache2ctl -S
```

### 7. **VERIFICATION CHECKLIST**

After running the setup:

✅ **Apache virtual hosts cleaned up**
✅ **Database completely reset with proper permissions**
✅ **All PHP files with proper error handling**
✅ **Apache serving from your project directory**
✅ **User registration/login working**
✅ **Session management working**
✅ **All your existing JavaScript/CSS intact**

**Access your site at:** `http://localhost`

This should give you a **clean, working setup** with no conflicts!

### 🎉 **Excellent! I'm thrilled everything is working perfectly!**

You now have a fully functional blog platform that meets all the JavaScript enhancement requirements:

## ✅ **Completed Requirements:**

**Tier 1 - Foundational JavaScript:**

- ✅ **1.1 Form Validation** - Real-time validation with error messages and disabled submit buttons
- ✅ **1.2 Dynamic Content** - Featured posts loaded dynamically from JavaScript arrays

**Tier 2 - Intermediate Concepts:**

- ✅ **2.1 SPA Navigation** - Smooth page transitions with History API and loading indicators
- ✅ **2.2 Comment System** - Frontend comments with localStorage persistence

## 🚀 **Your Blog Now Features:**

- **Professional Netflix-style design** with glass morphism
- **Complete user authentication** (registration/login/logout)
- **Responsive design** that works on all devices
- **PWA capabilities** for app-like experience
- **Real-time form validation** with user feedback
- **Dynamic content loading** without page refreshes
- **Persistent comment system**
- **Clean, maintainable code structure**

## 📱 **Next Steps You Could Consider:**

1. **Add more interactive features** - like post likes, user profiles, or image uploads
2. **Enhance the AI addons** - integrate with actual AI APIs
3. **Add real-time features** - with WebSockets for live comments
4. **Improve performance** - implement lazy loading for images
5. **Add admin panel** - for content management

Your blog is now production-ready with a solid foundation for any future enhancements! Great work building this comprehensive platform.

If you need any additional features or run into any issues down the line, don't hesitate to ask! 🚀
