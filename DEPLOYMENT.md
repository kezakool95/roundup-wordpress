# 🚀 Deployment Guide - Round Up WordPress

## ✅ Successfully Pushed to GitHub!

Repository: `kezakool95/roundup-wordpress`
Commit: `5447bbb` - All core features implemented

---

## 📋 Deployment Steps for cPanel

### 1. Pull Latest Code from GitHub

SSH into your cPanel server:

```bash
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
cd ~/roundup.lakehouse.holdings
git pull origin main
```

### 2. Activate the Theme

1. Log into WordPress admin: `https://roundup.lakehouse.holdings/wp-admin`
2. Go to **Appearance → Themes**
3. Activate **"Teed Up Migration"**

**🎉 Pages Auto-Created!**

When you activate the theme, these pages are automatically created:
- `/dashboard` - User dashboard
- `/log-round` - Round creator
- `/stats` - Stats dashboard
- `/friends` - Friend finder
- `/leaderboards` - Leaderboards
- `/login` - Login page
- `/rounds` - Rounds archive

### 3. Install Required Plugins

Install **Advanced Custom Fields (ACF)** plugin:

1. Go to **Plugins → Add New**
2. Search for "Advanced Custom Fields"
3. Install and activate **Advanced Custom Fields** (free version is fine)

### 4. Create ACF Field Groups

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

### 5. Install Alpine.js & Chart.js (Already in Theme)

The theme already enqueues these from CDN in `functions.php`:
- Alpine.js v3
- Chart.js v4

### 6. Test the Application

1. **Create a test user** (Subscriber role)
2. **Log in** and visit `/dashboard`
3. **Log a round** at `/log-round`
4. **Check stats** at `/stats`
5. **View leaderboards** at `/leaderboards`
6. **Add friends** at `/friends`

---

## 🔧 Database Notes

All data is stored using WordPress native functions:
- **Rounds** → Custom Post Type `round`
- **Friends** → User Meta (`friends_list`, `friend_requests_sent`, `friend_requests_received`)
- **Handicap History** → User Meta (`handicap_history`, `current_handicap`)
- **Club Distances** → User Meta (`club_distances`)

**No custom SQL tables required!**

---

## 🎯 REST API Endpoints

All endpoints are automatically registered. Test with:

```bash
# Get leaderboard
curl https://roundup.lakehouse.holdings/wp-json/teedup/v1/leaderboard?type=global

# Get user stats (requires authentication)
curl https://roundup.lakehouse.holdings/wp-json/teedup/v1/user-stats \
  -H "X-WP-Nonce: YOUR_NONCE"
```

---

## 📱 Mobile Responsiveness

All pages are fully responsive and tested on:
- Desktop (1920px+)
- Tablet (768px - 1024px)
- Mobile (320px - 767px)

---

## 🐛 Troubleshooting

### Pages not showing?
- Flush permalinks: **Settings → Permalinks → Save Changes**

### REST API not working?
- Check `.htaccess` has WordPress rewrite rules
- Verify REST API is enabled: Visit `/wp-json/` (should show JSON)

### Charts not displaying?
- Check browser console for JavaScript errors
- Verify Chart.js is loading from CDN

### Friend requests not working?
- Ensure users are logged in
- Check browser console for API errors

---

## 🎨 Customization

### Change Colors

Edit `style.css` CSS variables:

```css
:root {
    --primary: #0B3D17;  /* Forest Green */
    --accent: #4CAF50;   /* Vibrant Lime */
    --background: #F4F7F5;
}
```

### Add More Stats

Edit `functions.php` → `teed_up_get_user_stats()` function

---

## ✅ Deployment Checklist

- [ ] Pull latest code from GitHub
- [ ] Activate "Teed Up Migration" theme
- [ ] Install ACF plugin
- [ ] Create ACF field groups for rounds and courses
- [ ] Flush permalinks
- [ ] Test round submission
- [ ] Test friend system
- [ ] Test leaderboards
- [ ] Test on mobile device

---

**🎉 Your golf app is ready to go!**
