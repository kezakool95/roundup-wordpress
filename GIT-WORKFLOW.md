# 🔄 Git Workflow for WordPress Deployment

## Overview

Your WordPress site is now version-controlled with Git! This allows you to:
- Track all changes to your custom theme
- Deploy updates from your local machine to production
- Collaborate with others
- Roll back changes if needed

---

## 📁 What's Tracked in Git

✅ **Tracked** (committed to Git):
- Custom theme: `wp-content/themes/teed-up-migration/`
- Uploads: `wp-content/uploads/`
- Migration guides and documentation
- `.gitignore` and `README.md`

❌ **Not Tracked** (excluded):
- WordPress core files (`wp-admin`, `wp-includes`, etc.)
- `wp-config.php` (contains sensitive credentials)
- Database files (`.sql`)
- Deployment scripts and archives
- Third-party plugins

---

## 🚀 Deployment Workflow

### 1. Make Changes Locally

Edit files in your local development environment:

```bash
cd /Users/kieronsandhu/Documents/Antigravity/Teed\ Up/wordpress-migration
```

Test changes at: http://localhost:8080

### 2. Commit Changes

```bash
git add .
git commit -m "Description of your changes"
git push origin main
```

### 3. Deploy to Production Server

SSH into your server and pull the changes:

```bash
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net

cd ~/roundup.lakehouse.holdings
git pull origin main
```

That's it! Your changes are now live.

---

## 🔧 One-Time Server Setup

You only need to do this once to set up Git on your production server:

### Step 1: SSH into Server

```bash
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
```

### Step 2: Navigate to WordPress Directory

```bash
cd ~/roundup.lakehouse.holdings
```

### Step 3: Initialize Git (if not already done)

```bash
git init
git remote add origin https://github.com/kezakool95/roundup-wordpress.git
git fetch origin
git checkout -b main origin/main
```

### Step 4: Configure Git to Ignore Local Changes to wp-config.php

```bash
git update-index --assume-unchanged wp-config.php
```

This prevents your production `wp-config.php` from being overwritten.

---

## 📝 Common Workflows

### Update Theme Styles

```bash
# Local
cd /Users/kieronsandhu/Documents/Antigravity/Teed\ Up/wordpress-migration
# Edit wp-content/themes/teed-up-migration/style.css
git add wp-content/themes/teed-up-migration/style.css
git commit -m "Update theme styles"
git push origin main

# Server
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
cd ~/roundup.lakehouse.holdings
git pull origin main
```

### Add New Template File

```bash
# Local
# Create new file in wp-content/themes/teed-up-migration/
git add wp-content/themes/teed-up-migration/page-new-template.php
git commit -m "Add new page template"
git push origin main

# Server
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
cd ~/roundup.lakehouse.holdings
git pull origin main
```

### Update Functions.php

```bash
# Local
# Edit wp-content/themes/teed-up-migration/functions.php
git add wp-content/themes/teed-up-migration/functions.php
git commit -m "Add new custom post type"
git push origin main

# Server
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
cd ~/roundup.lakehouse.holdings
git pull origin main
```

---

## ⚠️ Important Notes

### Database Changes
Git does **not** sync database changes. If you:
- Add new courses
- Create new users
- Change settings

These are stored in the database, not in Git. To sync database changes, you need to:
1. Export database from local: `./export-database.sh`
2. Import to production via phpMyAdmin or SSH

### Plugin Updates
Third-party plugins are not tracked in Git. To update plugins:
- Update in WordPress admin on production server
- Or manually upload plugin files via FTP/SSH

### Media Uploads
New media uploads ARE tracked in Git (in `wp-content/uploads/`). When you add images via WordPress admin locally, commit and push them.

---

## 🔍 Checking Status

### On Local Machine

```bash
cd /Users/kieronsandhu/Documents/Antigravity/Teed\ Up/wordpress-migration
git status  # See what files have changed
git log     # See commit history
```

### On Production Server

```bash
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
cd ~/roundup.lakehouse.holdings
git status  # Check if server is up to date
git log     # See commit history
```

---

## 🆘 Troubleshooting

### "Your local changes would be overwritten"

If you get this error when pulling on the server:

```bash
# Stash local changes
git stash

# Pull updates
git pull origin main

# Reapply local changes (if needed)
git stash pop
```

### Accidentally Committed wp-config.php

If you accidentally committed sensitive files:

```bash
git rm --cached wp-config.php
git commit -m "Remove wp-config.php from tracking"
git push origin main
```

### Need to Undo Last Commit

```bash
git reset --soft HEAD~1  # Undo commit, keep changes
# or
git reset --hard HEAD~1  # Undo commit, discard changes
```

---

## 📚 Quick Reference

| Action | Command |
|--------|---------|
| Check status | `git status` |
| Add all changes | `git add .` |
| Commit changes | `git commit -m "message"` |
| Push to GitHub | `git push origin main` |
| Pull from GitHub | `git pull origin main` |
| View history | `git log --oneline` |
| Discard local changes | `git checkout -- filename` |

---

## 🎯 Best Practices

1. **Commit often** with descriptive messages
2. **Test locally** before pushing to production
3. **Pull before push** to avoid conflicts
4. **Never commit** sensitive data (passwords, API keys)
5. **Backup database** before major changes
6. **Use branches** for experimental features (optional)

---

## 🔗 Repository

**GitHub:** https://github.com/kezakool95/roundup-wordpress

**Clone URL:** `https://github.com/kezakool95/roundup-wordpress.git`

---

Happy deploying! 🚀
