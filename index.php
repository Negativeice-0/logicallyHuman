<?php require_once "config.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logical Human - Content Platform</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <header class="navbar glass-card">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php"><img src="./images/logo.png" alt="Logical Human logo"></a>
                <span>LogicalHuman</span>
            </div>
        </div>
    </header>

    <main class="container">
        <section id="home" class="hero">
            <h1 class="animated-text animated-heading">Experience Humanity with AI</h1>
            <p class="animated-text animated-p">
                Discover what happens when humans and AI collaborate to build amazing things.
                Join our platform to create, share, and grow together.
            </p>
            <div style="margin-top: 2rem">
                <a href="login.php" class="button-group" style="display: inline-block; margin-right: 10px;">
                    <input type="button" value="Log In" style="background-color: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
                </a>
                <a href="registration.php" class="button-group" style="display: inline-block;">
                    <input type="button" value="Sign Up" style="background-color: #28a745; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
                </a>
            </div>
        </section>

        <div class="section-divider"></div>

        <section id="features" class="glass-card" style="padding: 2rem; margin: 2rem 0;">
            <h2 style="text-align: center; color: #ff0050; margin-bottom: 2rem;">Platform Features</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div style="text-align: center;">
                    <h3 style="color: white;">💬 Real-time Comments</h3>
                    <p style="color: #ccc;">Engage with community through instant commenting</p>
                </div>
                <div style="text-align: center;">
                    <h3 style="color: white;">🎨 Glass Design</h3>
                    <p style="color: #ccc;">Beautiful glass morphism interface</p>
                </div>
                <div style="text-align: center;">
                    <h3 style="color: white;">📱 PWA Ready</h3>
                    <p style="color: #ccc;">Install as app on any device</p>
                </div>
            </div>
        </section>

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
