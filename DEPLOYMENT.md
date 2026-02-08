# 🚀 Deployment Guide - Round Up WordPress

## ✅ Successfully Pushed to GitHub!

Repository: `kezakool95/roundup-wordpress`
Commit: `f89eb2f` - Fixes critical error & adds auto-pages

---

## 📋 Deployment Steps for cPanel

### 1. Pull Latest Code from GitHub

SSH into your cPanel server:

```bash
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
cd ~/roundup.lakehouse.holdings
git pull origin main
```

### 2. Activate Changes & Fix Pages

Since your theme is **already active**, you must do this:

1. **Log in to WordPress Admin** (`/wp-admin`)
   - This triggers the automatic page creation script.
   - You should see a green notice: "Round Up: Required pages have been automatically created!"

2. **Flush Permalinks** (Fixes 404 Errors)
   - Go to **Settings → Permalinks**
   - Scroll down and click **"Save Changes"** (no need to change anything)
   - This fixes any "Page Not Found" errors on `/friends/`, `/leaderboards/`, etc.

### 3. Verify Pages Exist

Check **Pages → All Pages** to ensure these exist:
- Dashboard (`/dashboard`)
- Log Round (`/log-round`)
- Stats (`/stats`)
- Friends (`/friends`)
- Leaderboards (`/leaderboards`)
- Login (`/login`)
- Rounds (`/rounds`)

### 4. Install Required Plugins (If not already)

- **Advanced Custom Fields (ACF)** is required for data storage.

### 5. Create ACF Field Groups

#### Field Group: Round Details

**Location:** Post Type is equal to Round

**Fields:**
- **Course** (Post Object)
  - Post Type: `course`
  - Return Format: Post Object
  
- **Date** (Date Picker)
  - Display Format: `d/m/Y`
  - Return Format: `Y-m-d`
  
- **Score** (Number)
  - Min: 1
  
- **Holes Played** (Number)
  - Default: 18
  - Choices: 9, 18
  
- **Hole Scores** (Text)
  - Instructions: "JSON array of hole scores"

#### Field Group: Course Details

**Location:** Post Type is equal to Course

**Fields:**
- **Par** (Number)
  - Default: 72
  
- **Slope Rating** (Number)
  - Default: 113
  
- **Course Rating** (Number)
  - Default: 72.0

---

## 🐛 Troubleshooting

### "Critical Error" on `/friends/`?
- I have fixed the PHP syntax error in `page-friends.php`. A `git pull` will resolve this.

### Pages returning 404?
- Visit **Settings → Permalinks** and click **Save Changes**.

### API returning 404?
- Check if `mod_rewrite` is enabled on server (usually is).
- Visit Permalinks settings to flush rewrite rules.

---

**🎉 Your golf app is ready!**
