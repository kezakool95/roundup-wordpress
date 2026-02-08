#!/bin/bash

# Helper function to run WP-CLI commands inside the docker container
wp_cli() {
    docker compose exec -T wordpress wp "$@" --allow-root
}

echo "Starting automated WordPress setup..."

# 1. Create Pages
echo "Creating pages..."

# Home
HOME_ID=$(wp_cli post create --post_type=page --post_title='Home' --post_status=publish --porcelain)
echo "Home Page ID: $HOME_ID"

# Dashboard
DASHBOARD_ID=$(wp_cli post create --post_type=page --post_title='Dashboard' --post_status=publish --page_template='page-dashboard.php' --porcelain)
echo "Dashboard Page ID: $DASHBOARD_ID"

# Log Round
ROUND_ID=$(wp_cli post create --post_type=page --post_title='Log Round' --post_status=publish --page_template='page-round-creator.php' --porcelain)
echo "Log Round Page ID: $ROUND_ID"

# My Schedule
SCHEDULE_ID=$(wp_cli post create --post_type=page --post_title='My Schedule' --post_status=publish --page_template='page-schedule.php' --porcelain)
echo "Schedule Page ID: $SCHEDULE_ID"

# Stats
STATS_ID=$(wp_cli post create --post_type=page --post_title='Stats' --post_status=publish --page_template='page-stats.php' --porcelain)
echo "Stats Page ID: $STATS_ID"

# Login
LOGIN_ID=$(wp_cli post create --post_type=page --post_title='Login' --post_status=publish --page_template='page-auth.php' --porcelain)
echo "Login Page ID: $LOGIN_ID"


# 2. Set Front Page
echo "Setting static front page..."
wp_cli option update show_on_front 'page'
wp_cli option update page_on_front $HOME_ID


# 3. Create Menu
echo "Creating Main Menu..."
MENU_ID=$(wp_cli menu create "Main Menu" --porcelain)

# Add items to menu
wp_cli menu item add-post $MENU_ID $DASHBOARD_ID --title="Dashboard"
wp_cli menu item add-post $MENU_ID $ROUND_ID --title="Log Round"
wp_cli menu item add-post $MENU_ID $STATS_ID --title="Stats"
wp_cli menu item add-post $MENU_ID $SCHEDULE_ID --title="Schedule"
wp_cli post-type list


# 4. Assign Menu to Location (if theme supports it, though our theme doesn't register locations yet explicitly in functions.php, we should add that)
# Checking functions.php... it doesn't have register_nav_menus. I should add that first.

echo "Setup script complete!"
