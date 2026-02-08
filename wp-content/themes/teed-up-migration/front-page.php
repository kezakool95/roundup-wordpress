<?php get_header(); ?>

<div class="landing-page">
    <div class="hero">
        <div class="container hero-content">
            <h1>Perfect Your Scheduling, <br />Focus on Your Swing.</h1>
            <p class="hero-subtext">
                The premium platform for social golfers to coordinate rounds,
                discover new courses, and track their handicap with ease.
            </p>
            <div class="hero-actions">
                <!-- If logged in, show Dashboard link. If not, show Get Started -->
                <?php if (is_user_logged_in()): ?>
                <a href="<?php echo site_url('/dashboard'); ?>" class="btn-primary">Go to Dashboard</a>
                <?php
else: ?>
                <a href="<?php echo site_url('/login'); ?>" class="btn-primary">Get Started</a>
                <?php
endif; ?>
                <a href="<?php echo get_post_type_archive_link('course'); ?>" class="btn-secondary">Explore Courses</a>
            </div>
        </div>
        <div class="hero-image-overlay"></div>
    </div>

    <section class="features container">
        <div class="feature-card glass-card">
            <span class="feature-icon">🗓️</span>
            <h3>Smart Scheduling</h3>
            <p>Sync your calendar and find the perfect tee time that works for everyone.</p>
        </div>
        <div class="feature-card glass-card">
            <span class="feature-icon">🤝</span>
            <h3>Find Partners</h3>
            <p>Connect with golfers of similar skill levels and expand your social circle.</p>
        </div>
        <div class="feature-card glass-card">
            <span class="feature-icon">📈</span>
            <h3>Stat Tracking</h3>
            <p>Keep a detailed record of your rounds and watch your handicap improve.</p>
        </div>
    </section>
</div>

<style>
    .landing-page {
        padding-bottom: 4rem;
    }

    .hero {
        position: relative;
        padding: 6rem 0;
        background: linear-gradient(135deg, var(--wp--preset--color--primary) 0%, #082e11 100%);
        color: white;
        margin-bottom: 2rem;
        border-radius: 0 0 40px 40px;
        overflow: hidden;
    }

    .hero h1 {
        color: white;
        font-size: 3rem;
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }

    .hero-subtext {
        font-size: 1.25rem;
        opacity: 0.9;
        max-width: 600px;
        margin-bottom: 2rem;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
    }

    .hero .btn-secondary {
        color: white;
        border-color: white;
    }

    .hero .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: -3rem;
        position: relative;
        z-index: 10;
    }

    .feature-card {
        padding: 2rem;
        text-align: center;
        background: rgba(255, 255, 255, 0.9);
    }

    .feature-icon {
        font-size: 3rem;
        display: block;
        margin-bottom: 1rem;
    }
</style>

<?php get_footer(); ?>