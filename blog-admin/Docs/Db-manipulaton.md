# 🔁 Reset admin password to `admin123` (or any password you choose)

## 1️⃣ Generate a fresh bcrypt hash for your new password

Run this command **on your host** (where PHP is installed):

```bash
php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
```

It will output something like:  
`$2y$10$...` (length 60 characters). **Copy that entire hash**.

> You can replace `admin123` with any password you want.

### 2️⃣ Connect to your Docker PostgreSQL container

```bash
docker exec -it blite-postgres psql -U blite_user -d blite_db
```

### 3️⃣ Update the admin password

Run this SQL, **replacing `'PASTE_YOUR_HASH_HERE'`** with the hash you just generated:

```sql
UPDATE users SET password = 'PASTE_YOUR_HASH_HERE' WHERE email = 'admin@blog.com';
```

If you get `UPDATE 0`, the admin user doesn’t exist. Then insert it first:

```sql
INSERT INTO users (username, email, password, role, dob, gender, country, user_bio)
VALUES (
    'admin',
    'admin@blog.com',
    'PASTE_YOUR_HASH_HERE',
    'admin',
    '1990-01-01',
    'male',
    'United States',
    'Platform administrator.'
) ON CONFLICT (email) DO UPDATE SET password = EXCLUDED.password;
```

### 4️⃣ Exit psql

```sql
\q
```

### 5️⃣ Test login

Go to `http://localhost:8000/login.php`, enter:

- **Email:** `admin@blog.com`
- **Password:** `admin123` (or whatever you chose)

Now you should be redirected to `home.php` (or `/blog-admin/dashboard` if you already implemented role‑based redirect).

---

## ❓ Why no restart is needed

- `docker compose restart` or `docker restart` only reboots the container – **it does not reload data**.  
- Your SQL `UPDATE` writes directly to the database files on disk. The PostgreSQL process picks up the change instantly.  
- The only reason to restart would be if you changed `docker-compose.yml` environment variables (like `POSTGRES_PASSWORD`), but that’s not the case here.

---

## ✅ Optional: Verify the update worked

From psql:

```sql
SELECT email, role, length(password) as hash_length FROM users WHERE email = 'admin@blog.com';
```

You should see `hash_length = 60`.

---

**Now you’re set.** Log in with the new password. If you still face issues, check that your `login.php` is using the same `password_verify()` correctly – but from the code you shared, it’s fine.
