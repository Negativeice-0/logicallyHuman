<?php
require "connection.php";
requireAuth();

$message = "";
$error = "";

if (!isset($_GET["edit_id"])) {
    header("Location: photo.php");
    exit();
}

$photo_id = $_GET["edit_id"];
$db = getDB();
$stmt = $db->prepare("SELECT * FROM photos WHERE id = :id");
$stmt->execute([":id" => $photo_id]);
$photo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$photo) {
    header("Location: photo.php");
    exit();
}

// Handle photo update
if (isset($_POST["updatephoto"])) {
    $title = $_POST["title"] ?? "";
    $description = $_POST["description"] ?? "";
    $new_photo = $_FILES["photo"];

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        $db = getDB();

        // Check if new photo uploaded
        if (!empty($new_photo["name"])) {
            // Validate file type
            $allowed_types = [
                "image/jpeg",
                "image/jpg",
                "image/png",
                "image/gif",
                "image/webp",
            ];
            $file_type = mime_content_type($new_photo["tmp_name"]);

            if (!in_array($file_type, $allowed_types)) {
                $error = "Only JPG, PNG, GIF, and WebP files are allowed.";
            } else {
                // Get original filename
                $original_filename = basename($new_photo["name"]);
                $file_extension = pathinfo(
                    $original_filename,
                    PATHINFO_EXTENSION,
                );

                // Generate unique filename
                $unique_filename =
                    uniqid("photo_", true) . "." . $file_extension;

                // Create uploads directory if it doesn't exist
                $upload_dir = "uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $target_file = $upload_dir . $unique_filename;

                if (move_uploaded_file($new_photo["tmp_name"], $target_file)) {
                    // Delete old photo file
                    if (file_exists($photo["url"])) {
                        unlink($photo["url"]);
                    }

                    // Update with new photo
                    $stmt = $db->prepare(
                        "UPDATE photos SET title = :title, description = :description, filename = :filename, filepath = :filepath, url = :url WHERE id = :id",
                    );
                    $stmt->execute([
                        ":title" => $title,
                        ":description" => $description,
                        ":filename" => $unique_filename,
                        ":filepath" => $target_file,
                        ":url" => $target_file,
                        ":id" => $photo_id,
                    ]);

                    $message = "Photo updated successfully!";
                    $photo["url"] = $target_file;
                } else {
                    $error = "Failed to upload new photo.";
                }
            }
        } else {
            // Just update title and description
            $stmt = $db->prepare(
                "UPDATE photos SET title = :title, description = :description WHERE id = :id",
            );
            $stmt->execute([
                ":title" => $title,
                ":description" => $description,
                ":id" => $photo_id,
            ]);
            $message = "Photo details updated successfully!";
            $photo["title"] = $title;
            $photo["description"] = $description;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Photo</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span>✏️</span>
                <span>Edit Photo</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="usermanage.php"><i class="fas fa-users"></i> Users</a>
                <a href="photo.php" class="active"><i class="fas fa-images"></i> Photos</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container edit-container">
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

        <div class="edit-form-container">
            <h1><i class="fas fa-edit"></i> Edit Photo</h1>

            <!-- Current Photo -->
            <div class="current-photo">
                <h3><i class="fas fa-image"></i> Current Photo</h3>
                <img src="<?php echo htmlspecialchars($photo["url"]); ?>"
                     alt="<?php echo htmlspecialchars(
                         $photo["title"] ?? "Photo",
                     ); ?>"
                     onerror="this.src='https://via.placeholder.com/600x400/333/fff?text=Image+Not+Found'">

                <div class="photo-info-grid">
                    <div class="info-item">
                        <span class="info-label">Photo ID</span>
                        <span class="info-value">#<?php echo htmlspecialchars(
                            $photo["id"],
                        ); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Uploaded</span>
                        <span class="info-value">
                            <?php if (isset($photo["created_at"])) {
                                echo date(
                                    "F j, Y",
                                    strtotime($photo["created_at"]),
                                );
                            } ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <form class="edit-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars(
                    $photo["id"],
                ); ?>">

                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Photo Title:</label>
                    <input type="text" id="title" name="title"
                           value="<?php echo htmlspecialchars(
                               $photo["title"] ?? "",
                           ); ?>"
                           required
                           placeholder="Enter photo title">
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description:</label>
                    <textarea id="description" name="description"
                              placeholder="Photo description..."><?php echo htmlspecialchars(
                                  $photo["description"] ?? "",
                              ); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="photo"><i class="fas fa-camera"></i> Change Photo (optional):</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <div class="file-note">
                        Leave empty to keep current photo. Max 5MB.
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="updatephoto" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Photo
                    </button>
                    <a href="photo.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
