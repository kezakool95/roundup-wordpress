# 🚀 Quick Start - cPanel Migration

## Ready to migrate? Follow these steps:

### 1️⃣ Export Your Data (5 minutes)

```bash
cd /Users/kieronsandhu/Documents/Antigravity/Teed\ Up/wordpress-migration

# Export database
./export-database.sh

# Prepare files
./prepare-files.sh
```

**What this does:**
- Exports your WordPress database
- Prepares all files for upload
- Creates a ZIP archive
- Generates configuration templates

### 2️⃣ Gather Your cPanel Info

You'll need:
- cPanel login URL
- Your domain name
- FTP credentials (optional)

### 3️⃣ Follow the Detailed Guide

Open and follow: **`cpanel-setup-guide.md`**

This guide includes:
- ✅ Step-by-step instructions
- ✅ Screenshots references
- ✅ Troubleshooting tips
- ✅ Security best practices

---

## 📦 What Gets Created

After running the scripts, you'll find in `cpanel-migration/`:

```
cpanel-migration/
├── wordpress_db_[timestamp].sql          # Original database export
├── wordpress_db_clean_[timestamp].sql    # Cleaned for production
├── wordpress-files/                       # All WordPress files
│   ├── wp-content/                       # Your theme, plugins, uploads
│   ├── wp-admin/                         # WordPress admin
│   ├── wp-includes/                      # WordPress core
│   ├── wp-config-template.php            # Configuration template
│   └── .htaccess                         # Permalink rules
└── wordpress-files-[timestamp].zip       # Compressed archive
```

---

## ⏱️ Estimated Time

- **Export & Prepare:** 5 minutes
- **cPanel Setup:** 15 minutes
- **Upload Files:** 10-30 minutes (depending on connection)
- **Database Import:** 2-5 minutes
- **Configuration:** 10 minutes
- **Testing:** 15 minutes

**Total:** ~1-2 hours

---

## 🆘 Need Help?

1. Check `cpanel-setup-guide.md` for detailed instructions
2. See troubleshooting section for common issues
3. Contact your hosting provider's support

---

## ⚠️ Important Notes

- **Keep your local Docker environment running** until migration is verified
- **Test everything** before decommissioning local environment
- **Set up backups** immediately after migration
- **Enable SSL/HTTPS** for security

---

## 🎯 Success Checklist

After migration, verify:
- [ ] Site loads at your domain
- [ ] Can log in to WordPress admin
- [ ] All pages work (courses, stats, dashboard, etc.)
- [ ] Images display correctly
- [ ] Booking links work
- [ ] SSL certificate is active
- [ ] Backups are configured

---

Ready? Run the export scripts and open `cpanel-setup-guide.md`! 🚀
