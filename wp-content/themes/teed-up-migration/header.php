<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="site-header" :class="{ 'scrolled': window.scrollY > 10 }" x-data="{ mobileMenuOpen: false }">
        <div class="container header-container">
            <!-- Logo -->
            <a href="<?php echo home_url(); ?>" class="site-logo">
                <span class="logo-icon">⛳️</span>
                <span class="logo-text">Round Up</span>
            </a>

            <!-- Desktop Navigation -->
            <nav class="desktop-nav">
                <?php if (is_user_logged_in()): 
                    $current_user = wp_get_current_user();
                    
                    wp_nav_menu([
                        'theme_location' => 'primary_menu',
                        'container'      => false,
                        'menu_class'     => 'nav-menu-wrapper',
                        'items_wrap'     => '%3$s',
                        'fallback_cb'    => false,
                    ]);
                ?>
                    <div class="header-actions">
                        <a href="<?php echo site_url('/log-round'); ?>" class="btn-primary small">Log Round</a>
                        <div class="user-menu" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="user-menu-trigger">
                                <?php echo get_avatar($current_user->ID, 32); ?>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </button>
                            <div class="user-dropdown" x-show="open" x-transition>
                                <a href="<?php echo site_url('/dashboard'); ?>">Dashboard</a>
                                <a href="<?php echo wp_logout_url(home_url()); ?>" class="text-danger">Sign Out</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo site_url('/login'); ?>" class="nav-link">Log In</a>
                    <a href="<?php echo site_url('/login'); ?>" class="btn-primary small">Get Started</a>
                <?php endif; ?>
            </nav>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-toggle" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Toggle Menu">
                <span class="hamburger-icon">☰</span>
            </button>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div class="mobile-nav-drawer" x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2" style="display: none;">
            <nav class="mobile-nav-links">
                <?php if (is_user_logged_in()): 
                    wp_nav_menu([
                        'theme_location' => 'primary_menu',
                        'container'      => false,
                        'menu_class'     => 'mobile-menu-wrapper',
                        'items_wrap'     => '%3$s',
                        'fallback_cb'    => false,
                        'add_li_class'   => 'mobile-link' // Note: This requires a filter normally, but we'll adapt classes via CSS if needed
                    ]);
                ?>
                <div class="mobile-actions mt-1">
                    <a href="<?php echo site_url('/log-round'); ?>" class="btn-primary full-width mb-1">Log Round</a>
                </div>
                <a href="<?php echo site_url('/dashboard'); ?>" class="mobile-link">Dashboard</a>
                <a href="<?php echo site_url('/stats'); ?>" class="mobile-link">My Stats</a>
                <a href="<?php echo site_url('/friends'); ?>" class="mobile-link">Friends</a>
                <a href="<?php echo site_url('/schedule'); ?>" class="mobile-link">Availability</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="mobile-link text-danger">Sign Out</a>
                <?php else: ?>
                <a href="<?php echo site_url('/login'); ?>" class="mobile-link">Log In</a>
                <a href="<?php echo site_url('/login'); ?>" class="mobile-link highlight">Get Started</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main id="primary" class="site-main">