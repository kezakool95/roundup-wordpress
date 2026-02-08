# 🚀 Automated Deployment - Quick Start

## Prerequisites

Before running the deployment script, you need to:

### 1. Install `sshpass` (for automated SSH)

```bash
# On macOS
brew install hudochenkov/sshpass/sshpass

# Verify installation
sshpass -V
```

### 2. Create Database in cPanel

Follow these steps in your cPanel:

1. **Log in to cPanel** at your hosting provider
2. **Go to MySQL® Databases**
3. **Create Database:**
   - Database Name: `roundup_db`
   - Click "Create Database"
   - **Note the full name** (e.g., `lakehous_roundup_db`)

4. **Create User:**
   - Username: `roundup_user`
   - Click "Generate Password" and **save it**
   - Click "Create User"
   - **Note the full username** (e.g., `lakehous_roundup_user`)

5. **Add User to Database:**
   - Select user and database
   - Click "Add"
   - Check "ALL PRIVILEGES"
   - Click "Make Changes"

6. **Save these credentials:**
   ```
   Database Name: lakehous_roundup_db
   Database User: lakehous_roundup_user
   Database Password: [generated password]
   Database Host: localhost
   ```

---

## Running the Deployment

### Step 1: Make Script Executable

```bash
cd /Users/kieronsandhu/Documents/Antigravity/Teed\ Up/wordpress-migration
chmod +x deploy-to-cpanel.sh
```

### Step 2: Run Deployment

```bash
./deploy-to-cpanel.sh
```

The script will prompt you for:
- Database credentials (from cPanel setup above)
- SSH password

### Step 3: What the Script Does

The automated deployment will:

1. ✅ Export database from Docker
2. ✅ Update all URLs (localhost → roundup.lakehouse.holdings)
3. ✅ Generate secure wp-config.php with your database credentials
4. ✅ Generate new WordPress security keys
5. ✅ Create .htaccess with HTTPS redirect
6. ✅ Test SSH connection
7. ✅ Upload and import database
8. ✅ Upload all WordPress files via rsync
9. ✅ Set correct file permissions (755/644)
10. ✅ Verify deployment

**Estimated time:** 10-15 minutes (depending on upload speed)

---

## After Deployment

Once the script completes:

1. **Visit your site:** https://roundup.lakehouse.holdings
2. **Log in to admin:** https://roundup.lakehouse.holdings/wp-admin
3. **Re-save permalinks:**
   - Go to Settings → Permalinks
   - Click "Save Changes" (don't change anything)
4. **Test everything:**
   - [ ] Homepage loads
   - [ ] Course pages work
   - [ ] Stats page works
   - [ ] Dashboard works
   - [ ] User login works
   - [ ] Images display correctly

---

## Troubleshooting

### If sshpass is not available:

The script requires `sshpass` for automated SSH. Install it with:
```bash
brew install hudochenkov/sshpass/sshpass
```

### If SSH connection fails:

- Verify SSH credentials
- Check that port 2683 is correct
- Try manual SSH first:
  ```bash
  ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
  ```

### If database import fails:

- Verify database credentials are correct
- Check that database exists in cPanel
- Ensure user has ALL PRIVILEGES

### If files don't upload:

- Check disk space on server
- Verify you have write permissions to public_html
- Try manual rsync to test connection

---

## Manual Rollback

If something goes wrong:

1. Your local Docker environment is still running
2. Deployment files are saved in `cpanel-deployment-[timestamp]/`
3. You can re-run the script after fixing issues
4. Contact your hosting support if needed

---

## Security Notes

- Script uses secure password prompts (not stored in files)
- Generates fresh WordPress security keys
- Sets restrictive file permissions
- Creates HTTPS redirect in .htaccess
- Disables file editing in WordPress admin

---

Ready to deploy? Run:
```bash
./deploy-to-cpanel.sh
```
