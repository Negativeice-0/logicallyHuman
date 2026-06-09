# i opted to keep php un dockerised for easier updates

## 📸 How to handle images with Docker PostgreSQL

First, a clarification: **PostgreSQL does not store images directly** (unless you use `BYTEA`, which is inefficient). Your schema stores **metadata** (filename, filepath, url). The actual image files (`.jpg`, `.png`) live in the **file system** – typically inside an `uploads/` folder in your project.

When you move to Docker, you have two options:

---

### Option 1 – Keep images on the host (PHP runs on host, PostgreSQL in Docker)

If your PHP still runs on your **host machine** (not inside Docker), and only PostgreSQL is containerised:

- **Images stay on the host** – nothing changes. Your PHP code reads images from `./uploads/` on the host.
- Docker PostgreSQL only needs the metadata – it doesn’t touch the image files.
- **No action needed** – just make sure the `uploads/` folder exists and has the right permissions (755).

---

### Option 2 – PHP also runs inside Docker (full containerisation)

If you later move PHP into a Docker container, you must **make the image files available inside the container**.

#### 2.1 Copy images into the Docker image (for static assets)

Add this to your `Dockerfile`:

```dockerfile
COPY ./uploads /var/www/html/uploads
```

Then rebuild the image. This is good for images that rarely change.

#### 2.2 Use a Docker volume (for dynamic uploads)

If users upload new images, you need **persistent storage** so images survive container restarts.

In `docker-compose.yml`:

```yaml
services:
  web:
    image: php:apache
    volumes:
      - ./uploads:/var/www/html/uploads   # mount host folder into container
  db:
    image: postgres:15
    volumes:
      - postgres_data:/var/lib/postgresql/data
volumes:
  postgres_data:
```

Now any image uploaded by PHP goes into `./uploads` on the host, and the container sees them.

---

## 🔁 How to “ferry” images from GitHub to your Docker environment

You already uploaded the images to GitHub. When you **clone the repository** on any machine (including the one running Docker), the images are already there (if they are tracked by Git). Then:

1. **If PHP runs on host** – just clone the repo, images appear in the right folder.
2. **If PHP runs in Docker** – mount that folder as a volume (see above) or copy them during build.

**Important:** Git is not ideal for large binary files (images). For production, store images on cloud storage (S3, Cloudinary) and keep only URLs in the database. But for development, tracking images in Git is fine.

---

## ✅ Your current situation (PHP on host, PostgreSQL in Docker)

Since you only moved PostgreSQL to Docker, and PHP still runs on your host:

- **Images are already on your host** (cloned from GitHub).
- The `photos` table already has `filepath` and `url` pointing to `uploads/...`.
- Docker PostgreSQL never sees the images – only the PHP application does.

**No extra step needed.** Just ensure:

- The `uploads/` directory exists in your project root.
- It contains the images you listed in `init_complete.sql` (e.g., `abstract_mindscape.jpg`).
- File permissions allow PHP to read them.

If images are missing from the `uploads/` folder, copy them from GitHub (they should be there if you committed them). If you didn’t commit the actual image files, you’ll need to add them manually.

---

## 🧪 Quick test

Visit `http://localhost:8000/photo.php` (or your gallery page). If images load, everything is fine. If not, check the file paths in the database and the actual files on disk.

Let me know if you need to adjust the image file paths or move images into the Docker container for full containerisation.
