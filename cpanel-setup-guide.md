# cPanel Migration Guide - Round Up WordPress

## Step-by-Step Instructions

This guide will walk you through migrating your WordPress site from Docker to cPanel hosting.

---

## Prerequisites Checklist

Before starting, ensure you have:

- [ ] cPanel login URL and credentials
- [ ] FTP/SFTP credentials (or access to cPanel File Manager)
- [ ] Domain name configured and pointing to your server
- [ ] SSL certificate (recommended - usually free via cPanel/Let's Encrypt)

---

## Phase 1: Export from Local Environment

### Step 1: Export Database

```bash
cd /Users/kieronsandhu/Documents/Antigravity/Teed\ Up/wordpress-migration
chmod +x export-database.sh
./export-database.sh
```

This will create:
- `cpanel-migration/wordpress_db_[timestamp].sql` - Original database
- `cpanel-migration/wordpress_db_clean_[timestamp].sql` - Cleaned version

### Step 2: Prepare Files

```bash
chmod +x prepare-files.sh
./prepare-files.sh
```

This will create:
- `cpanel-migration/wordpress-files/` - All WordPress files
- `cpanel-migration/wordpress-files-[timestamp].zip` - Compressed archive
- `cpanel-migration/wordpress-files/wp-config-template.php` - Configuration template

---

## Phase 2: cPanel Database Setup

### Step 1: Create MySQL Database

1. Log in to cPanel
2. Navigate to **MySQL® Databases**
3. Under "Create New Database":
   - Database Name: `roundup_db` (or your choice)
   - Click **Create Database**
4. Note the full database name (usually `cpanelusername_roundup_db`)

### Step 2: Create Database User

1. Scroll to "MySQL Users" section
2. Under "Add New User":
   - Username: `roundup_user` (or your choice)
   - Password: Generate a strong password
   - Click **Create User**
3. Note the full username (usually `cpanelusername_roundup_user`)

### Step 3: Add User to Database

1. Scroll to "Add User To Database"
2. Select your user and database
3. Click **Add**
4. On privileges page, select **ALL PRIVILEGES**
5. Click **Make Changes**

### Step 4: Note Your Credentials

```
Database Name: cpanelusername_roundup_db
Database User: cpanelusername_roundup_user
Database Password: [your generated password]
Database Host: localhost
```

---

## Phase 3: Upload Files

### Option A: Using cPanel File Manager (Recommended for beginners)

1. Log in to cPanel
2. Open **File Manager**
3. Navigate to `public_html` (or your domain's directory)
4. Click **Upload** button
5. Upload `wordpress-files-[timestamp].zip`
6. Right-click the ZIP file → **Extract**
7. Move all files from `wordpress-files/` to `public_html/`
8. Delete the empty `wordpress-files/` folder and ZIP file

### Option B: Using FTP/SFTP

1. Connect to your server using FileZilla or similar FTP client
   - Host: Your domain or server IP
   - Username: Your cPanel username
   - Password: Your cPanel password
   - Port: 21 (FTP) or 22 (SFTP)
2. Navigate to `public_html/`
3. Upload all files from `cpanel-migration/wordpress-files/`

### Step 5: Set File Permissions

In cPanel File Manager:
1. Select all files and folders
2. Click **Permissions**
3. Set:
   - Directories: `755`
   - Files: `644`
4. Check "Recurse into subdirectories"
5. Click **Change Permissions**

---

## Phase 4: Configure WordPress

### Step 1: Update wp-config.php

1. In File Manager, locate `wp-config-template.php`
2. Right-click → **Edit**
3. Update database credentials:

```php
define( 'DB_NAME', 'cpanelusername_roundup_db' );
define( 'DB_USER', 'cpanelusername_roundup_user' );
define( 'DB_PASSWORD', 'your_database_password' );
define( 'DB_HOST', 'localhost' );
```

4. Generate new security keys:
   - Visit: https://api.wordpress.org/secret-key/1.1/salt/
   - Copy all 8 lines
   - Replace the placeholder keys in wp-config.php

5. **Save Changes**
6. Rename file from `wp-config-template.php` to `wp-config.php`

### Step 2: Import Database

1. In cPanel, open **phpMyAdmin**
2. Select your database from the left sidebar
3. Click **Import** tab
4. Click **Choose File**
5. Select `wordpress_db_clean_[timestamp].sql`
6. Scroll down and click **Go**
7. Wait for import to complete (you should see "Import has been successfully finished")

### Step 3: Update URLs

1. Still in phpMyAdmin, click **SQL** tab
2. Open `update-urls.sql` in a text editor
3. Replace `https://yourdomain.com` with your actual domain
4. Copy the entire SQL script
5. Paste into phpMyAdmin SQL tab
6. Click **Go**

---

## Phase 5: Final Configuration

### Step 1: Update Permalinks

1. Visit `https://yourdomain.com/wp-admin`
2. Log in with your WordPress credentials
3. Go to **Settings → Permalinks**
4. Click **Save Changes** (don't change anything, just save)

### Step 2: Verify .htaccess

1. In cPanel File Manager, check if `.htaccess` exists in `public_html/`
2. If not, it should be created automatically when you saved permalinks
3. If still missing, create it with this content:

```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

### Step 3: Enable SSL (Highly Recommended)

1. In cPanel, go to **SSL/TLS Status**
2. Select your domain
3. Click **Run AutoSSL** (if available)
4. Or install Let's Encrypt certificate

### Step 4: Force HTTPS

Add to the top of `.htaccess` (before WordPress rules):

```apache
# Force HTTPS
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

---

## Phase 6: Testing

### Test Checklist

- [ ] Homepage loads correctly
- [ ] Can log in to `/wp-admin`
- [ ] Course archive page displays (`/course/`)
- [ ] Individual course pages load with images
- [ ] Stats page works (`/stats/`)
- [ ] Dashboard page works (`/dashboard/`)
- [ ] My Schedule page works (`/my-schedule/`)
- [ ] Friends page works (`/friends/`)
- [ ] Booking links work
- [ ] User registration/login works
- [ ] All images display correctly
- [ ] No broken links

### Common Issues and Solutions

#### Issue: "Error establishing database connection"
**Solution:** Double-check database credentials in `wp-config.php`

#### Issue: Permalinks not working (404 errors)
**Solution:** 
1. Re-save permalinks in WordPress admin
2. Check `.htaccess` file exists and is readable
3. Verify mod_rewrite is enabled (contact host if needed)

#### Issue: Images not displaying
**Solution:**
1. Check file permissions (should be 644 for files, 755 for directories)
2. Verify URLs were updated correctly in database
3. Check `wp-content/uploads/` folder uploaded correctly

#### Issue: "Too many redirects"
**Solution:** Check for conflicting redirect rules in `.htaccess`

#### Issue: White screen / 500 error
**Solution:**
1. Check PHP error logs in cPanel
2. Verify PHP version compatibility (7.4+ recommended)
3. Increase PHP memory limit in cPanel

---

## Post-Migration Optimization

### Recommended cPanel Settings

1. **PHP Version:** 8.0 or 8.1 (in cPanel → MultiPHP Manager)
2. **PHP Memory Limit:** 256M minimum (in cPanel → MultiPHP INI Editor)
3. **Max Execution Time:** 300 seconds
4. **Upload Max Filesize:** 64M

### Security Hardening

1. Change WordPress admin username from "admin" if using default
2. Use strong passwords for all accounts
3. Install security plugin (Wordfence or similar)
4. Enable automatic WordPress updates
5. Regular backups (use cPanel backup or plugin)

### Performance Optimization

1. Install caching plugin (WP Super Cache or W3 Total Cache)
2. Enable Gzip compression
3. Optimize images
4. Use CDN if needed

---

## Backup Strategy

Set up automatic backups:

1. **cPanel Backups:** 
   - Go to cPanel → Backup
   - Set up automatic backups if available

2. **WordPress Plugin:**
   - Install UpdraftPlus or BackupBuddy
   - Configure automatic backups to cloud storage

---

## Support Resources

- **cPanel Documentation:** https://docs.cpanel.net/
- **WordPress Codex:** https://wordpress.org/support/
- **Your Hosting Provider:** Contact support for server-specific issues

---

## Migration Complete! 🎉

Your WordPress site should now be live on cPanel hosting. Remember to:

- Keep your local Docker environment as a backup for a few weeks
- Monitor the site for any issues in the first few days
- Set up regular backups
- Keep WordPress and plugins updated

If you encounter any issues, refer to the troubleshooting section or contact your hosting provider's support team.
