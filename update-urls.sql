-- WordPress URL Update Script for cPanel Migration
-- Run this in phpMyAdmin after importing your database

-- INSTRUCTIONS:
-- 1. Replace 'https://yourdomain.com' with your actual domain (include https:// if using SSL)
-- 2. Run this script in phpMyAdmin SQL tab
-- 3. Verify the updates by checking a few posts/pages

-- Update site URL and home URL
UPDATE wp_options SET option_value = 'https://yourdomain.com' WHERE option_name = 'siteurl';
UPDATE wp_options SET option_value = 'https://yourdomain.com' WHERE option_name = 'home';

-- Update post content (replace localhost:8080 with your domain)
UPDATE wp_posts SET post_content = REPLACE(post_content, 'http://localhost:8080', 'https://yourdomain.com');
UPDATE wp_posts SET guid = REPLACE(guid, 'http://localhost:8080', 'https://yourdomain.com');

-- Update post meta (for ACF fields and other meta)
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'http://localhost:8080', 'https://yourdomain.com');

-- Update user meta
UPDATE wp_usermeta SET meta_value = REPLACE(meta_value, 'http://localhost:8080', 'https://yourdomain.com');

-- Update comments
UPDATE wp_comments SET comment_content = REPLACE(comment_content, 'http://localhost:8080', 'https://yourdomain.com');
UPDATE wp_comments SET comment_author_url = REPLACE(comment_author_url, 'http://localhost:8080', 'https://yourdomain.com');

-- Verification queries (run these to check the updates)
-- SELECT * FROM wp_options WHERE option_name IN ('siteurl', 'home');
-- SELECT post_title, post_content FROM wp_posts WHERE post_content LIKE '%localhost%' LIMIT 10;
