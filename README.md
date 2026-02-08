# Round Up - WordPress Golf App

A WordPress-based golf round tracking and analytics application.

## 🏌️ Features

- **Course Directory**: Browse Western Australian golf courses with detailed information
- **Round Tracking**: Log your golf rounds with hole-by-hole scoring
- **Performance Analytics**: Track your handicap and scoring trends
- **Friends & Leaderboards**: Connect with other golfers and compare scores
- **Booking Integration**: Direct links to course booking systems

## 📋 Tech Stack

- **Platform**: WordPress 6.x
- **Theme**: Custom theme (`teed-up-migration`)
- **Plugins**: Advanced Custom Fields (ACF)
- **Database**: MySQL
- **Hosting**: cPanel

## 🚀 Deployment

### Production
- **URL**: https://roundup.lakehouse.holdings
- **Server**: cPanel hosting

### Local Development
Uses Docker Compose for local development environment.

```bash
docker compose up -d
```

Access at: http://localhost:8080

## 📁 Repository Structure

```
wordpress-migration/
├── wp-content/
│   ├── themes/
│   │   └── teed-up-migration/    # Custom theme (tracked in Git)
│   ├── plugins/                   # WordPress plugins (not tracked)
│   └── uploads/                   # Media uploads (tracked)
├── .gitignore                     # WordPress-specific ignores
├── cpanel-setup-guide.md          # Deployment guide
└── README.md                      # This file
```

## 🔧 Development Workflow

### Making Changes

1. **Edit locally** in your development environment
2. **Test** at http://localhost:8080
3. **Commit** your changes:
   ```bash
   git add .
   git commit -m "Description of changes"
   git push origin main
   ```

### Deploying to Production

After pushing to GitHub, SSH into your server and pull changes:

```bash
ssh -p 2683 lakehous@SYN121.SYD2.hostyourservices.net
cd ~/roundup.lakehouse.holdings
git pull origin main
```

Or use the deployment scripts in the repository.

## 📝 Custom Theme

The custom theme is located at `wp-content/themes/teed-up-migration/` and includes:

- **Course Management**: Custom post type for golf courses
- **Round Logging**: Interface for tracking rounds
- **Analytics Dashboard**: Performance metrics and charts
- **User Profiles**: Friend connections and social features

## 🗄️ Database

The database includes custom tables for:
- Golf courses with ACF fields (slope, rating, pars per hole)
- User rounds and scores
- Friend relationships
- Performance analytics

## 🔐 Security

- `wp-config.php` is excluded from Git (contains sensitive credentials)
- Database backups are not tracked in Git
- Use environment-specific configuration files

## 📚 Documentation

- [cPanel Setup Guide](cpanel-setup-guide.md) - Full deployment instructions
- [Migration Quick Start](MIGRATION-QUICKSTART.md) - Quick reference

## 🤝 Contributing

This is a private project. For questions or issues, contact the development team.

## 📄 License

Proprietary - All rights reserved
