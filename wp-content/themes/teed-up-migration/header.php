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
                <?php if (is_user_logged_in()): ?>
                <a href="<?php echo site_url('/dashboard'); ?>" class="nav-link">Dashboard</a>
                <a href="<?php echo site_url('/log-round'); ?>" class="nav-link">Log Round</a>
                <a href="<?php echo site_url('/rounds'); ?>" class="nav-link">Rounds</a>
                <a href="<?php echo site_url('/friends'); ?>" class="nav-link">Friends</a>
                <a href="<?php echo site_url('/stats'); ?>" class="nav-link">Stats</a>
                <a href="<?php echo site_url('/leaderboards'); ?>" class="nav-link">Leaderboards</a>
                <a href="<?php echo site_url('/my-schedule'); ?>" class="nav-link">Schedule</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn-secondary small">Sign Out</a>
                <?php
else: ?>
                <a href="<?php echo site_url('/login'); ?>" class="nav-link">Log In</a>
                <a href="<?php echo site_url('/login'); ?>" class="btn-primary small">Get Started</a>
                <?php
endif; ?>
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
                <?php if (is_user_logged_in()): ?>
                <a href="<?php echo site_url('/dashboard'); ?>" class="mobile-link">Dashboard</a>
                <a href="<?php echo site_url('/log-round'); ?>" class="mobile-link">Log Round</a>
                <a href="<?php echo site_url('/rounds'); ?>" class="mobile-link">Rounds</a>
                <a href="<?php echo site_url('/friends'); ?>" class="mobile-link">Friends</a>
                <a href="<?php echo site_url('/stats'); ?>" class="mobile-link">Stats</a>
                <a href="<?php echo site_url('/leaderboards'); ?>" class="mobile-link">Leaderboards</a>
                <a href="<?php echo site_url('/schedule'); ?>" class="mobile-link">Schedule</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="mobile-link highlight">Sign Out</a>
                <?php
else: ?>
                <a href="<?php echo site_url('/login'); ?>" class="mobile-link">Log In</a>
                <a href="<?php echo site_url('/login'); ?>" class="mobile-link highlight">Get Started</a>
                <?php
endif; ?>
            </nav>
        </div>
    </header>

    <main id="primary" class="site-main">