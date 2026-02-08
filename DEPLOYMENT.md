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

### 5. Create ACF Field Groups

*If you haven't already done this, you need to set up the data fields:*

#### Field Group: Round Details

**Location:** Post Type is equal to Round

**Fields:**
- **Course** (Post Object)
  - Return Format: Post Object
- **Date** (Date Picker)
  - Return Format: `Y-m-d`
- **Score** (Number)
- **Holes Played** (Number, Default: 18)
- **Hole Scores** (Repeater or Text)

---

## 🐛 Troubleshooting

### "Critical Error"?
- Run `git pull` again to ensure you have the latest fix for `page-friends.php`.

### Pages still 404?
- If automatic fixing didn't work, just visit **Settings → Permalinks** in wp-admin and click **"Save Changes"**.

---

**🎉 Your golf app is ready!**
