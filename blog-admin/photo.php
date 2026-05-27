<?php
require "connection.php";
requireAuth();

$message = "";
$error = "";

// Handle photo upload
if (isset($_POST["addphoto"])) {
    $title = $_POST["title"] ?? "";
    $description = $_POST["description"] ?? "";
    $photo = $_FILES["photo"];

    if (empty($title)) {
        $error = "Title is required.";
    } elseif ($photo["error"] !== UPLOAD_ERR_OK) {
        $error = "File upload error: " . $photo["error"];
    } else {
        // Validate file type
        $allowed_types = [
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/gif",
            "image/webp",
        ];
        $file_type = mime_content_type($photo["tmp_name"]);

        if (!in_array($file_type, $allowed_types)) {
            $error =
                "Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.";
        } else {
            // Get original filename
            $original_filename = basename($photo["name"]);
            $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);

            // Generate unique filename
            $unique_filename = uniqid("photo_", true) . "." . $file_extension;

            // Create uploads directory if it doesn't exist
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $target_file = $upload_dir . $unique_filename;

            if (move_uploaded_file($photo["tmp_name"], $target_file)) {
                $db = getDB();

                // Insert with all required columns from your database
                $stmt = $db->prepare(
                    "INSERT INTO photos (title, description, filename, filepath, url) VALUES (:title, :description, :filename, :filepath, :url)",
                );
                $stmt->execute([
                    ":title" => $title,
                    ":description" => $description,
                    ":filename" => $unique_filename, // filename column
                    ":filepath" => $target_file, // filepath column
                    ":url" => $target_file, // url column
                ]);

                $message = "Photo uploaded successfully!";
            } else {
                $error = "Failed to upload file. Check directory permissions.";
            }
        }
    }
}

// Handle delete
if (isset($_GET["delete_id"])) {
    $photo_id = $_GET["delete_id"];
    $db = getDB();

    // Get photo URL first
    $stmt = $db->prepare("SELECT url FROM photos WHERE id = :id");
    $stmt->execute([":id" => $photo_id]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($photo) {
        // Delete file from server
        if (file_exists($photo["url"])) {
            unlink($photo["url"]);
        }

        // Delete from database
        $stmt = $db->prepare("DELETE FROM photos WHERE id = :id");
        $stmt->execute([":id" => $photo_id]);
        $message = "Photo deleted successfully!";
    }
}

// Get all photos
$db = getDB();
$stmt = $db->query("SELECT * FROM photos ORDER BY created_at DESC");
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Photo Management</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span>🖼️</span>
                <span>Photo Management</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="usermanage.php"><i class="fas fa-users"></i> Users</a>
                <a href="photo.php" class="active"><i class="fas fa-images"></i> Photos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Photo Management</h1>

        <?php if ($message): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(
                    $message,
                ); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars(
                    $error,
                ); ?>
            </div>
        <?php endif; ?>

        <!-- Upload Section -->
        <div class="upload-section glass-card">
            <h2><i class="fas fa-upload"></i> Upload New Photo</h2>
            <form class="upload-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Photo Title:</label>
                    <input type="text" id="title" name="title" required placeholder="Enter photo title">
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description:</label>
                    <textarea id="description" name="description" placeholder="Enter photo description"></textarea>
                </div>

                <div class="form-group">
                    <label for="photo"><i class="fas fa-camera"></i> Choose Photo:</label>
                    <input type="file" id="photo" name="photo" accept="image/*" required>
                    <div class="file-note">
                        Max file size: 5MB. Supported: JPEG, PNG, GIF, WebP.
                    </div>
                </div>

                <button type="submit" name="addphoto" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Photo
                </button>
            </form>
        </div>

        <!-- Photos Grid -->
        <h2 class="mt-4"><i class="fas fa-images"></i> Photo Gallery</h2>
        <div class="photos-grid">
            <?php if (empty($photos)): ?>
                <div class="no-data">
                    <i class="fas fa-image"></i>
                    <p>No photos uploaded yet. Upload your first photo!</p>
                </div>
            <?php else: ?>
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-card">
                        <div class="photo-image">
                            <img src="<?php echo htmlspecialchars(
                                $photo["url"],
                            ); ?>"
                                 alt="<?php echo htmlspecialchars(
                                     $photo["title"] ?? "Photo",
                                 ); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x300/333/fff?text=Image+Not+Found'">
                        </div>
                        <div class="photo-info">
                            <h4><?php echo htmlspecialchars(
                                $photo["title"] ?? "Untitled",
                            ); ?></h4>
                            <?php if (!empty($photo["description"])): ?>
                                <p class="photo-description"><?php echo htmlspecialchars(
                                    $photo["description"],
                                ); ?></p>
                            <?php endif; ?>
                            <div class="photo-meta">
                                <span><i class="far fa-calendar"></i>
                                    <?php echo date(
                                        "M j, Y",
                                        strtotime($photo["created_at"]),
                                    ); ?>
                                </span>
                            </div>
                            <div class="photo-actions">
                                <a href="editphoto.php?edit_id=<?php echo $photo[
                                    "id"
                                ]; ?>" class="action-btn edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="photo.php?delete_id=<?php echo $photo[
                                    "id"
                                ]; ?>"
                                   class="action-btn delete"
                                   onclick="return confirm('Delete this photo?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
