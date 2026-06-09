# Logical Human – Social Content Platform

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-4169E1?logo=postgresql)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](https://docker.com)
[![License](https://img.shields.io/badge/License-Proprietary-red)](LICENSE)

> A production‑ready social blogging platform with glass‑morphism UI, real‑time comments, admin roles, and a fully containerised PostgreSQL backend – designed for easy cloud migration.

## 📦 Overview

Logical Human is a community platform where users share posts, comment, and engage through a modern PWA‑ready interface. The stack prioritises:

- **Security** – Hashed passwords (bcrypt), CSRF tokens, prepared statements, role‑based access control.
- **Performance** – Indexed PostgreSQL tables, lazy‑loaded image slider, static asset caching.
- **Developer Experience** – Dockerised database, portable configuration, clear separation of concerns.
- **Cloud Readiness** – PostgreSQL container can be swapped for any cloud managed service (RDS, Cloud SQL, etc.).

---

## 🏗 Architecture

```diag
┌─────────────────┐      ┌─────────────────┐
│   PHP Front     │      │  Docker Engine  │
│   (Apache / CLI)│◄────►│  ┌───────────┐  │
│                 │      │  │PostgreSQL │  │
│ - config.php    │      │  │   15      │  │
│ - home.php      │      │  │ :5432     │  │
│ - admin.php     │      │  └───────────┘  │
│ - PDO driver    │      └─────────────────┘
└─────────────────┘            ▲
        │                       │
        └─────── volume ────────┘
          (persistent data)
```

- **PHP** handles business logic, sessions, and templating.
- **PostgreSQL** runs inside a Docker container – isolated, reproducible, and easy to migrate.
- **No ORM** – direct PDO with prepared statements for full control and minimal overhead.

---

## 🚀 Quick Start (for CTOs & DevOps)

### Prerequisites

- Docker Engine 20.10+
- PHP 8.1+ (with `pdo_pgsql` extension) – *only if PHP runs natively*
- Git

### 1. Clone the repository

```bash
git clone https://github.com/your-org/logical-human.git
cd logical-human
```

### 2. Start PostgreSQL via Docker Compose

```bash
docker compose up -d
```

This launches PostgreSQL 15 with the schema and seed data (from `init_complete.sql`) automatically.

### 3. Configure PHP connection (no changes needed)

`config.php` already points to `localhost:5432` – the container exposes the port to the host.

> **For production:** replace `DB_HOST` with your cloud database endpoint and use environment variables.

### 4. Run the PHP built‑in server (or use Apache/Nginx)

```bash
php -S 0.0.0.0:8000
```

Then visit `http://localhost:8000`.

### 5. Log in as administrator

- **Email:** `admin@blog.com`
- **Password:** `admin123`  
  *(change immediately in production)*

---

## 🐳 Docker Configuration

The `docker-compose.yml` (added in commit `Add Docker PostgreSQL support`) provides:

```yaml
services:
  db:
    image: postgres:15
    environment:
      POSTGRES_DB: blite_db
      POSTGRES_USER: blite_user
      POSTGRES_PASSWORD: ${DB_PASS}   # use .env file
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./init_complete.sql:/docker-entrypoint-initdb.d/init.sql
```

- **Volume** for persistent data – survives container restarts.
- **Init script** runs only on first creation.
- **Environment variables** can be moved to `.env` for secrets management.

To stop the local PostgreSQL service (legacy) and switch entirely to Docker:
```bash
sudo systemctl stop postgresql   # Linux
# or disable the service if desired
```

---

## 🔐 Security & Compliance

| Area                 | Implementation                                                                 |
|----------------------|--------------------------------------------------------------------------------|
| **Authentication**   | `password_hash()` with bcrypt, session regeneration on login.                 |
| **CSRF**             | Token stored in session, validated on all state‑changing requests.            |
| **SQL Injection**    | 100% PDO prepared statements – no concatenated queries.                       |
| **XSS**              | Outputs escaped with `htmlspecialchars()`.                                    |
| **Role‑Based Access**| `user` / `admin` roles; `requireAdmin()` middleware protects admin endpoints. |
| **Session Security** | `httponly`, `samesite=Lax`, secure flag when HTTPS.                           |

**Recommended production hardening:**
- Enable HTTPS + set `secure` cookie flag.
- Store database credentials in environment variables (e.g., `$_ENV` or `.env`).
- Run PHP-FPM with least privilege user.
- Regularly rotate the `csrf_token` and session IDs.

---

## 📁 Key Files & Their Responsibilities

| File               | Purpose                                                                 |
|--------------------|-------------------------------------------------------------------------|
| `config.php`       | DB connection, session setup, auth helpers, CSRF token, `setupDatabase()` |
| `init_complete.sql`| Full schema, indexes, triggers, default admin, sample posts/comments.   |
| `home.php`         | Dashboard, post listing, comments, image slider, admin welcome banner.  |
| `admin.php`        | (implied) User/post/comment management – requires admin role.           |
| `docker-compose.yml`| Containerised PostgreSQL for local dev and cloud staging.               |
| `style.css`        | Glass‑morphism design, responsive layout, PWA styling.                  |
| `manifest.json`    | PWA manifest for “install as app” functionality.                        |

---

## ☁️ Path to Cloud Deployment

Because PostgreSQL is containerised, migrating to a cloud managed service is trivial:

1. **Export local data** (if needed):
   ```bash
   docker exec -t blite-postgres pg_dump -U blite_user blite_db > backup.sql
   ```

2. **Provision** a cloud database (AWS RDS, Google Cloud SQL, Azure Database for PostgreSQL).

3. **Update** `config.php`:
   ```php
   define("DB_HOST", "your-cloud-db-endpoint");
   define("DB_USER", "cloud-user");
   define("DB_PASS", getenv("DB_PASSWORD")); // from environment
   ```

4. **Deploy the PHP application** to any PaaS (Heroku, Platform.sh, or a simple EC2 instance).  
   *No code changes required* – the database abstraction is complete.

5. **Scale horizontally** by adding read replicas and using connection pooling (PgBouncer).

---

## 🧪 Testing & Validation

Run the built‑in verification queries (inside the container or via `psql`):

```bash
docker exec -it logical-human-db-1 psql -U blite_user -d blite_db -c "SELECT table_name, record_count FROM ..."
```

Or use the PHP test script:

```bash
php test_connection.php   # included in the project
```

---

## 🔄 Development Workflow (Git Best Practices)

We follow **trunk‑based development** with feature branches:

```bash
git checkout -b feature/docker-postgres   # already done
git add docker-compose.yml
git commit -m "Add Docker PostgreSQL support ..."
git push origin feature/docker-postgres
```

- Main branch (`main`) always reflects production‑ready state.
- Database migrations are committed as `.sql` files – never manual changes.

## 📄 License

Proprietary – all rights reserved. Contact the maintainer for licensing terms.

---

## 👤 Maintainer

**Billy G. Ochieng Osodo**  
[billy.g.ochieng.osodo@gmail.com](mailto:billy.g.ochieng.osodo@gmail.com)

---

*Last updated: June 2026 – Docker PostgreSQL migration complete.*
