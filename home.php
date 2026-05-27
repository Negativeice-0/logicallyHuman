<?php
require_once "config.php";

// Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    error_log("No user_id in session, redirecting to login");
    header("Location: login.php");
    exit();
}

// NEW: Show admin welcome message if this is the first login as admin
$show_admin_welcome = false;
if (isset($_SESSION["admin_welcome"]) && $_SESSION["admin_welcome"] === true) {
    $show_admin_welcome = true;
    $_SESSION["admin_welcome"] = false; // Clear the flag
}

$db = getDB();
$user_id = $_SESSION["user_id"];

// Get user's posts
$posts_stmt = $db->prepare("
    SELECT p.*, u.username
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
");
$posts_stmt->execute();
$posts = $posts_stmt->fetchAll();

// Get comments for posts
$comments = [];
foreach ($posts as $post) {
    $comment_stmt = $db->prepare("
        SELECT c.*, u.username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at DESC
    ");
    $comment_stmt->execute([$post["id"]]);
    $comments[$post["id"]] = $comment_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logical Human - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <header class="navbar glass-card">
        <div class="nav-container">
            <div class="logo">
                <a href="home.php"><img src="./images/logo.png" alt="Logical Human logo"></a>
                <span>LH</span>
            </div>
            <nav class="nav-links">
                <a href="#home">Home</a>
                <a href="#featured">Featured</a>
                <a href="#gallery">Gallery</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
                <a href="#comments">Comments</a>
            </nav>
            <div class="social-icons">
                          <!-- FIXED: Proper social media icon sizes -->
                          <a href="https://facebook.com" target="_blank">
                              <img src="./images/facebook.png" alt="Facebook" />
                          </a>
                          <a href="https://twitter.com" target="_blank">
                              <img src="./images/twitter.png" alt="Twitter" />
                          </a>
                          <a href="https://linkedin.com" target="_blank">
                              <img src="./images/linkedin.png" alt="LinkedIn" />
                          </a>
                          <a href="logout.php" class="logout-btn" title="Logout">
                              <svg class="logout-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                  <polyline points="16,17 21,12 16,7"></polyline>
                                  <line x1="21" y1="12" x2="9" y2="12"></line>
                              </svg>
                          </a>
                      </div>
        </div>
    </header>

    <main class="container">

        <?php if ($show_admin_welcome): ?>
                  <div class="glass-card" style="background: linear-gradient(45deg, #ff0050, #ff6b00); color: white; padding: 20px; margin-bottom: 2rem; border-radius: 12px; text-align: center; border: 2px solid gold;">
                      <h2>🎉 Welcome, Platform Administrator!</h2>
                      <p>As the first user, you've been granted <strong>Admin privileges</strong>. You can now access the <a href="admin.php" style="color: white; text-decoration: underline; font-weight: bold;">Admin Panel</a> to manage users, posts, and comments.</p>
                  </div>
              <?php endif; ?>

              <section id="home" class="hero">
                  <h1 class="animated-text animated-heading">Welcome to Logical Human</h1>
                  <p class="animated-text animated-p">
                      Hello <?php echo $_SESSION["username"]; ?>!
              </section>

        <div class="section-divider"></div>

        <section id="featured" class="featured-posts-section">
            <h2>Community Posts</h2>
            <div id="featured-posts-container" class="featured-posts-grid">
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                    <div class="featured-post glass-card">
                        <div class="post-content">
                            <h3><?php echo htmlspecialchars(
                                $post["title"],
                            ); ?></h3>
                            <p class="excerpt"><?php echo htmlspecialchars(
                                $post["content"],
                            ); ?></p>
                            <small>By <?php echo htmlspecialchars(
                                $post["username"],
                            ); ?> on <?php echo $post["created_at"]; ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                           <?php else: ?>
                               <p>No posts found.</p>
                           <?php endif; ?>
            </div>
        </section>

        <div class="section-divider"></div>

        <section id="gallery" class="gallery">
            <h2>Our Creations</h2>
            <div class="slider-container glass-card">
                <div class="slider" id="image-slider">
                    <!-- Images will be loaded by JavaScript -->
                </div>
                <button class="slider-button prev" id="prevBtn">&#10094;</button>
                <button class="slider-button next" id="nextBtn">&#10095;</button>
            </div>
        </section>

        <div class="section-divider"></div>

        <section id="about" class="about-section">
            <div class="card text-card">
                <h2>About Logical Human</h2>
                <p>
                    Logical Human is a platform where technology meets human creativity.
                    We believe in the power of collaboration between human intelligence
                    and artificial intelligence to create amazing digital experiences.
                </p>
                <p>
                    Our mission is to build a community where creators, thinkers, and
                    innovators can share ideas, learn from each other, and push the
                    boundaries of what's possible.
                </p>
            </div>
            <div class="card image-card">
                <img src="./images/b1.png" alt="My professional Photo">
            </div>
        </section>

        <div class="section-divider"></div>

        <section id="contact" class="contact">
            <h2>Contact</h2>
            <p>Email: <a href="mailto:billy.g.ochieng.osodo@gmail.com">billy.g.ochieng.osodo@gmail.com</a></p>
        </section>
    </main>


    <div class="section-divider"></div>

    <section id="comments" class="glass-card" style="padding: 2rem; margin: 2rem 0;">
        <h2 style="text-align: center; color: #ff0050; margin-bottom: 2rem;">Community Comments</h2>

        <div class="comment-form-container">
            <form id="comment-form">
                <div class="form-group">
                    <label for="comment-name">Your Name:</label>
                    <input type="text" id="comment-name" name="name" required
                           style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: rgba(255, 255, 255, 0.1); color: white;">
                </div>

                <div class="form-group">
                    <label for="comment-text">Your Comment:</label>
                    <textarea id="comment-text" name="text" rows="4" required
                              style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: rgba(255, 255, 255, 0.1); color: white;"></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        Post Comment
                    </button>
                </div>
            </form>
        </div>

        <div id="comments-list" class="comments-list">
            <!-- Comments will be loaded by JavaScript -->
        </div>
    </section>

    <footer class="footer glass-card">
        <p>&copy; 2024 Logical Human Creations. All Rights Reserved.</p>
    </footer>

    <script>
        // Image slider functionality
        const galleryItems = [
            {
                title: "Abstract Mindscape",
                description: "A vibrant abstract composition.",
                imageUrl: "./images/family.jpeg",
            },
            {
                title: "AI Enhanced Portrait",
                description: "A professional headshot.",
                imageUrl: "./images/b1.png",
            },
            {
                title: "Digital Nature",
                description: "A landscape with digital elements.",
                imageUrl: "./images/i4.jpeg",
            },
        ];

        const slider = document.getElementById("image-slider");
        const prevBtn = document.getElementById("prevBtn");
        const nextBtn = document.getElementById("nextBtn");
        let currentIndex = 0;

        function renderSlides() {
            slider.innerHTML = "";
            galleryItems.forEach((item) => {
                const slide = document.createElement("div");
                slide.className = "slide";
                slide.innerHTML = `
                    <img src="${item.imageUrl}" alt="${item.title}" class="slide-image" />
                    <div class="text-center">
                        <h3>${item.title}</h3>
                        <p>${item.description}</p>
                    </div>
                `;
                slider.appendChild(slide);
            });
        }

        function updateSlider() {
            const offset = -currentIndex * 100;
            slider.style.transform = `translateX(${offset}%)`;
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % galleryItems.length;
            updateSlider();
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + galleryItems.length) % galleryItems.length;
            updateSlider();
        }

        // Initialize
        renderSlides();
        updateSlider();
        prevBtn.addEventListener("click", prevSlide);
        nextBtn.addEventListener("click", nextSlide);
    </script>
    <script src="script.js"></script>
</body>
</html>
