-- ============================================
-- COMPREHENSIVE SQL INITIALIZATION SCRIPT
-- For: Logical Human Social Platform
-- Matches ALL your PHP files (config.php, home.php, etc.)
-- ============================================

-- -------------------------------------------------
-- 1. CONNECT TO THE DATABASE:
-- -------------------------------------------------
-- psql -U blite_user -d blite_db -f init_complete.sql
-- or connect manually: \c blite_db

-- -------------------------------------------------
-- 2. DROP EXISTING TABLES (if needed - CAREFUL!)
-- -------------------------------------------------
-- DROP TABLE IF EXISTS comments CASCADE;
-- DROP TABLE IF EXISTS posts CASCADE;
-- DROP TABLE IF EXISTS users CASCADE;

-- -------------------------------------------------
-- 3. CREATE TABLES - MATCHING YOUR PHP CODE
-- -------------------------------------------------

-- Users table (matches registration.php and config.php)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    dob DATE,
    gender VARCHAR(10),
    country VARCHAR(100),
    user_bio TEXT,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Posts table (matches home.php and posting.php requirements)
CREATE TABLE IF NOT EXISTS posts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(500),
    category VARCHAR(100),
    views INTEGER DEFAULT 0,
    likes INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Comments table (matches home.php comments section)
CREATE TABLE IF NOT EXISTS comments (
    id SERIAL PRIMARY KEY,
    post_id INTEGER NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    likes INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Photos table (for photo gallery - matches photo.php)
CREATE TABLE IF NOT EXISTS photos (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------
-- 4. CREATE INDEXES FOR PERFORMANCE
-- -------------------------------------------------
-- Users indexes
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Posts indexes
CREATE INDEX IF NOT EXISTS idx_posts_user ON posts(user_id);
CREATE INDEX IF NOT EXISTS idx_posts_created ON posts(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_posts_category ON posts(category);

-- Comments indexes
CREATE INDEX IF NOT EXISTS idx_comments_post ON comments(post_id);
CREATE INDEX IF NOT EXISTS idx_comments_user ON comments(user_id);
CREATE INDEX IF NOT EXISTS idx_comments_created ON comments(created_at DESC);

-- Photos indexes
CREATE INDEX IF NOT EXISTS idx_photos_created ON photos(created_at DESC);

-- -------------------------------------------------
-- 5. INSERT DEFAULT/SEED DATA
-- -------------------------------------------------

-- Default admin user (matches your registration form)
-- Password: "password123" (hashed with password_hash())
INSERT INTO users (username, email, password, role, dob, gender, country, user_bio) 
VALUES (
    'admin', 
    'admin@blog.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'admin',
    '1990-01-01',
    'male',
    'United States',
    'Platform administrator with full privileges.'
) ON CONFLICT (email) DO NOTHING;

-- Additional test users (from your country list)
INSERT INTO users (username, email, password, dob, gender, country, user_bio) 
VALUES 
    ('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1985-05-15', 'male', 'United States', 'Technology enthusiast and blogger.'),
    ('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1992-08-22', 'female', 'United Kingdom', 'Digital artist and content creator.'),
    ('alex_wang', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1988-11-30', 'male', 'Canada', 'AI researcher and developer.'),
    ('sarah_k', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1995-03-10', 'female', 'Australia', 'Web designer and UX specialist.')
ON CONFLICT (email) DO NOTHING;

-- Sample posts (matches home.php featured posts)
INSERT INTO posts (user_id, title, content, category) 
SELECT 
    u.id,
    post_data.title,
    post_data.content,
    post_data.category
FROM users u
CROSS JOIN (VALUES
    ('The Rise of Human-AI Collaboration', 'Exploring how algorithms and human creativity merge to define future design and art. The synergy between artificial intelligence and human intuition is creating unprecedented opportunities in creative industries.', 'Technology'),
    ('Designing for Mobile-First PWA', 'Deep dive into philosophy and technical requirements for building fast, reliable mobile-first Progressive Web Applications that provide native app experiences.', 'Web Development'),
    ('Vanilla JS: Why it Still Matters', 'Forget frameworks. Explore the power, performance, and simplicity of plain JavaScript in modern web development.', 'Programming'),
    ('The Future of Digital Art', 'How AI tools are transforming artistic expression and enabling new forms of creativity that were previously unimaginable.', 'Art & Design'),
    ('Building Community Platforms', 'Technical and social considerations for creating engaging online communities that foster meaningful interactions.', 'Social Media')
) AS post_data(title, content, category)
WHERE u.username = 'admin'
ON CONFLICT DO NOTHING;

-- Sample comments (for home.php comments section)
INSERT INTO comments (post_id, user_id, content)
SELECT 
    p.id,
    u.id,
    comment_data.content
FROM posts p
JOIN users u ON u.username = 'john_doe'
CROSS JOIN (VALUES
    ('Great insights on AI collaboration! Looking forward to more articles on this topic.'),
    ('The mobile-first approach really makes sense in todays world.'),
    ('Vanilla JS is definitely underrated. Thanks for highlighting its importance.')
) AS comment_data(content)
WHERE p.title LIKE '%AI Collaboration%'
LIMIT 3
ON CONFLICT DO NOTHING;

-- Sample photos (for gallery/photo management)
INSERT INTO photos (title, description, filename, filepath, url) 
VALUES 
    ('Abstract Mindscape', 'A vibrant abstract composition exploring digital aesthetics.', 'abstract_mindscape.jpg', 'uploads/abstract_mindscape.jpg', 'uploads/abstract_mindscape.jpg'),
    ('AI Enhanced Portrait', 'A professional headshot enhanced with AI algorithms.', 'ai_portrait.jpg', 'uploads/ai_portrait.jpg', 'uploads/ai_portrait.jpg'),
    ('Digital Nature', 'A landscape with digital elements blending reality and technology.', 'digital_nature.jpg', 'uploads/digital_nature.jpg', 'uploads/digital_nature.jpg'),
    ('Urban Geometry', 'Architectural photography with geometric patterns.', 'urban_geometry.jpg', 'uploads/urban_geometry.jpg', 'uploads/urban_geometry.jpg'),
    ('Data Visualization Art', 'Artistic representation of data flows and connections.', 'data_art.jpg', 'uploads/data_art.jpg', 'uploads/data_art.jpg')
ON CONFLICT DO NOTHING;

-- -------------------------------------------------
-- 6. CREATE FUNCTIONS FOR AUTO-UPDATED TIMESTAMPS
-- -------------------------------------------------
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- -------------------------------------------------
-- 7. CREATE TRIGGERS
-- -------------------------------------------------
-- Users trigger
DROP TRIGGER IF EXISTS update_users_updated_at ON users;
CREATE TRIGGER update_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Posts trigger
DROP TRIGGER IF EXISTS update_posts_updated_at ON posts;
CREATE TRIGGER update_posts_updated_at
    BEFORE UPDATE ON posts
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Comments trigger
DROP TRIGGER IF EXISTS update_comments_updated_at ON comments;
CREATE TRIGGER update_comments_updated_at
    BEFORE UPDATE ON comments
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Photos trigger
DROP TRIGGER IF EXISTS update_photos_updated_at ON photos;
CREATE TRIGGER update_photos_updated_at
    BEFORE UPDATE ON photos
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- -------------------------------------------------
-- 8. GRANT PERMISSIONS (if using separate user)
-- -------------------------------------------------
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO blite_user;
-- GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO blite_user;

-- -------------------------------------------------
-- 9. VERIFICATION QUERIES
-- -------------------------------------------------

-- Count records
SELECT 'users' as table_name, COUNT(*) as record_count FROM users
UNION ALL
SELECT 'posts', COUNT(*) FROM posts
UNION ALL
SELECT 'comments', COUNT(*) FROM comments
UNION ALL
SELECT 'photos', COUNT(*) FROM photos
ORDER BY table_name;

-- Check user roles
SELECT username, email, role, country FROM users ORDER BY role, username;

-- Check posts with author info
SELECT p.title, u.username, p.created_at 
FROM posts p 
JOIN users u ON p.user_id = u.id 
ORDER BY p.created_at DESC 
LIMIT 5;

-- -------------------------------------------------
-- HOW TO RUN THIS SCRIPT:
-- -------------------------------------------------

-- Method 1: Using psql command line
-- psql -U blite_user -d blite_db -f init_complete.sql

-- Method 2: Run from PostgreSQL terminal
-- 1. Connect: sudo -u postgres psql
-- 2. Connect to db: \c blite_db
-- 3. Run script: \i /path/to/init_complete.sql

-- Method 3: From PHP (for first-time setup)
-- You can call setupDatabase() from config.php

-- -------------------------------------------------
-- TROUBLESHOOTING:
-- -------------------------------------------------

-- If you get permission errors:
-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO blite_user;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO blite_user;

-- To reset everything (CAREFUL - deletes all data):
-- DROP TABLE IF EXISTS comments CASCADE;
-- DROP TABLE IF EXISTS posts CASCADE;
-- DROP TABLE IF EXISTS photos CASCADE;
-- DROP TABLE IF EXISTS users CASCADE;
-- Then run this script again

-- -------------------------------------------------
-- IMPORTANT NOTES FOR YOUR PHP FILES:
-- -------------------------------------------------

-- 1. Your config.php already uses password_hash() - GOOD!
-- 2. The seed users have password "password" (hashed)
-- 3. Login with: admin@blog.com / password
-- 4. Registration form matches the users table structure
-- 5. home.php queries match these table relationships
-- 6. The photos table matches your photo.php requirements
-- 7. The comments table matches your home.php comments section

-- -------------------------------------------------
-- POST-INITIALIZATION STEPS:
-- -------------------------------------------------

-- 1. Create uploads directory:
-- mkdir -p uploads && chmod 755 uploads

-- 2. Test your application:
-- - Visit index.php
-- - Register a new user or login with admin@blog.com / password
-- - Check home.php loads posts and comments
-- - Test the photo gallery

-- 3. Run test_connection.php to verify everything works:
-- php -S localhost:8000 test_connection.php
