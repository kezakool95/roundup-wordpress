# 🚀 Deployment Guide - Round Up WordPress

## ✅ Successfully Pushed to GitHub!

Repository: `kezakool95/roundup-wordpress`
Commit: `[latest]` - Fixes critical error & adds auto-pages

---

## 📋 Deployment Steps for cPanel

### 1. Pull Latest Code from GitHub

SSH into your cPanel server:

```bash
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
cd ~/roundup.lakehouse.holdings
git pull origin main
```

### 2. That's it! 

Just visit your website homepage.
- The theme will **automatically detect** missing pages and create them.
- It will **automatically fix** any 404 errors by flushing permalinks.
- No need to log in to admin or do anything manual!

### 3. Verify Pages Exist

Check that these links work:
- [Dashboard](/dashboard)
- [Log Round](/log-round) (requires login)
- [Stats](/stats)
- [Friends](/friends)
- [Leaderboards](/leaderboards)

### 4. Install Required Plugins (If not already)

- **Advanced Custom Fields (ACF)** is required for data storage.

### 5. Import ACF Field Groups (Fast Way)

Instead of creating fields manually, you can now import them in seconds:

1. In WordPress Admin, go to **ACF → Tools**.
2. Under "Import Field Groups", click **"Choose File"**.
3. Select the `teed-up-acf-export.json` file from the theme root.
4. Click **"Import JSON"**.

*🎉 All fields for Rounds and Courses are now set up!*

---

## 🐛 Troubleshooting

### "Critical Error"?
- Run `git pull` again to ensure you have the latest fix for `page-friends.php`.

### Pages still 404?
- If automatic fixing didn't work, just visit **Settings → Permalinks** in wp-admin and click **"Save Changes"**.

---

**🎉 Your golf app is ready!**
