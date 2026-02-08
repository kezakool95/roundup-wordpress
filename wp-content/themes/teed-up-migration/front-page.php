<?php get_header(); ?>

<div class="landing-page">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg-pattern"></div>
        <div class="container hero-content">
            <div class="hero-text slide-up">
                <span class="hero-badge">🏌️ Western Australia's Golf Community</span>
                <h1>Track. Improve.<br /><span class="gradient-text">Play Better Golf.</span></h1>
                <p class="hero-subtext">
                    The ultimate platform for social golfers. Log your rounds, track your handicap,
                    find playing partners, and watch your game improve over time.
                </p>
                <div class="hero-actions">
                    <?php if (is_user_logged_in()): ?>
                    <a href="<?php echo site_url('/dashboard'); ?>" class="btn-primary large">Go to Dashboard</a>
                    <a href="<?php echo site_url('/log-round'); ?>" class="btn-secondary large">Log a Round</a>
                    <?php
else: ?>
                    <a href="<?php echo site_url('/login'); ?>" class="btn-primary large">Get Started Free</a>
                    <a href="<?php echo get_post_type_archive_link('course'); ?>" class="btn-secondary large">Explore
                        Courses</a>
                    <?php
endif; ?>
                </div>
            </div>
            <div class="hero-visual slide-up" style="animation-delay: 0.2s;">
                <div class="phone-mockup">
                    <div class="phone-screen">
                        <div class="mock-stat">
                            <span class="mock-label">Your Handicap</span>
                            <span class="mock-value">12.4</span>
                            <span class="mock-trend">↓ 2.1 this month</span>
                        </div>
                        <div class="mock-chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 45C840 60 960 90 1080 97.5C1200 105 1320 90 1380 82.5L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                    fill="white" />
            </svg>
        </div>
    </section>

    <!-- Stats Counter -->
    <section class="stats-counter container">
        <div class="counter-item">
            <span class="counter-value" data-target="<?php echo wp_count_posts('course')->publish; ?>">0</span>
            <span class="counter-label">WA Courses</span>
        </div>
        <div class="counter-item">
            <span class="counter-value" data-target="<?php echo wp_count_posts('round')->publish; ?>">0</span>
            <span class="counter-label">Rounds Logged</span>
        </div>
        <div class="counter-item">
            <span class="counter-value" data-target="<?php
$users = count_users();
echo $users['total_users'];
?>">0</span>
            <span class="counter-label">Active Golfers</span>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features container">
        <header class="section-header center">
            <h2>Everything You Need to Improve</h2>
            <p>Powerful features designed for golfers who want to get better</p>
        </header>

        <div class="features-grid">
            <div class="feature-card glass-card slide-up">
                <div class="feature-icon">📊</div>
                <h3>Smart Analytics</h3>
                <p>Track every round, visualize your progress, and identify patterns in your game with beautiful charts.
                </p>
            </div>
            <div class="feature-card glass-card slide-up" style="animation-delay: 0.1s;">
                <div class="feature-icon">🎯</div>
                <h3>Handicap Tracking</h3>
                <p>Automatic handicap calculation using official formulas. Watch your index improve as you log more
                    rounds.</p>
            </div>
            <div class="feature-card glass-card slide-up" style="animation-delay: 0.2s;">
                <div class="feature-icon">🤝</div>
                <h3>Find Partners</h3>
                <p>Connect with golfers of similar skill levels. Schedule rounds together and grow your golf network.
                </p>
            </div>
            <div class="feature-card glass-card slide-up" style="animation-delay: 0.3s;">
                <div class="feature-icon">🏆</div>
                <h3>Leaderboards</h3>
                <p>Compete with friends or see where you rank globally. Course-specific leaderboards show who's best at
                    your home course.</p>
            </div>
            <div class="feature-card glass-card slide-up" style="animation-delay: 0.4s;">
                <div class="feature-icon">⛳</div>
                <h3>Course Directory</h3>
                <p>Comprehensive database of Western Australian courses with ratings, par data, and slope information.
                </p>
            </div>
            <div class="feature-card glass-card slide-up" style="animation-delay: 0.5s;">
                <div class="feature-icon">📱</div>
                <h3>Mobile Ready</h3>
                <p>Log rounds on the go, right from the clubhouse. Works perfectly on any device.</p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <header class="section-header center">
                <h2>Get Started in Minutes</h2>
                <p>Three simple steps to better golf</p>
            </header>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Create Account</h3>
                    <p>Sign up for free and set up your golfer profile. Add your home course and playing preferences.
                    </p>
                </div>
                <div class="step-arrow">→</div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Log Your Rounds</h3>
                    <p>Enter your scores hole-by-hole or just the total. Your handicap updates automatically.</p>
                </div>
                <div class="step-arrow">→</div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Track Progress</h3>
                    <p>Watch your handicap improve, compare with friends, and celebrate your golf journey.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial / Social Proof -->
    <section class="social-proof container">
        <div class="testimonial-card glass-card">
            <div class="quote-icon">"</div>
            <p class="quote-text">Round Up has completely changed how I approach my golf game. Being able to see my
                trends and compare with mates keeps me motivated to practice and improve.</p>
            <div class="quote-author">
                <div class="author-avatar">👤</div>
                <div>
                    <strong>Local Golfer</strong>
                    <span>Joondalup Country Club</span>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container center">
            <h2>Ready to Lower Your Handicap?</h2>
            <p>Join hundreds of WA golfers already tracking their progress</p>
            <?php if (!is_user_logged_in()): ?>
            <a href="<?php echo site_url('/login'); ?>" class="btn-primary large">Start Tracking Free</a>
            <?php
else: ?>
            <a href="<?php echo site_url('/log-round'); ?>" class="btn-primary large">Log Your First Round</a>
            <?php
endif; ?>
        </div>
    </section>
</div>

<script>
    // Counter animation
    document.addEventListener('DOMContentLoaded', function () {
        const counters = document.querySelectorAll('.counter-value');
        const speed = 100;

        const animateCounter = (counter) => {
            const target = +counter.getAttribute('data-target');
            let count = 0;
            const increment = target / speed;

            const updateCount = () => {
                if (count < target) {
                    count += increment;
                    counter.innerText = Math.ceil(count);
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        };

        // Intersection Observer for animation trigger
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => observer.observe(counter));
    });
</script>

<style>
    .landing-page {
        padding-bottom: 0;
    }

    /* Hero */
    .hero {
        position: relative;
        padding: 6rem 0 8rem;
        background: linear-gradient(135deg, var(--wp--preset--color--primary) 0%, #082e11 100%);
        color: white;
        overflow: hidden;
    }

    .hero-bg-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
    }

    .hero-content {
        position: relative;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
    }

    @media (max-width: 768px) {
        .hero-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .hero-actions {
            justify-content: center;
        }

        .hero-visual {
            display: none;
        }
    }

    .hero-badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.15);
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .hero h1 {
        color: white;
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        line-height: 1.15;
    }

    .gradient-text {
        background: linear-gradient(90deg, #90EE90, #98FB98);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtext {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 500px;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
    }

    .btn-primary.large,
    .btn-secondary.large {
        padding: 1rem 2rem;
        font-size: 1.1rem;
    }

    .hero .btn-secondary {
        color: white;
        border-color: rgba(255, 255, 255, 0.5);
        background: transparent;
    }

    .hero .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
    }

    /* Phone Mockup */
    .phone-mockup {
        width: 280px;
        height: 500px;
        background: #1a1a1a;
        border-radius: 40px;
        padding: 15px;
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
        margin: 0 auto;
    }

    .phone-screen {
        width: 100%;
        height: 100%;
        background: linear-gradient(180deg, #0B3D17, #082e11);
        border-radius: 28px;
        padding: 2rem 1.5rem;
    }

    .mock-stat {
        text-align: center;
        margin-bottom: 2rem;
    }

    .mock-label {
        display: block;
        font-size: 0.9rem;
        opacity: 0.7;
        margin-bottom: 0.5rem;
    }

    .mock-value {
        display: block;
        font-size: 4rem;
        font-weight: 800;
    }

    .mock-trend {
        display: block;
        color: #90EE90;
        font-size: 0.9rem;
    }

    .mock-chart {
        height: 150px;
        background:
            linear-gradient(90deg, transparent 50%, rgba(255, 255, 255, 0.1) 50%);
        background-size: 20px 100%;
        border-radius: 12px;
        position: relative;
    }

    .mock-chart::after {
        content: '';
        position: absolute;
        bottom: 20%;
        left: 10%;
        right: 10%;
        height: 3px;
        background: linear-gradient(90deg, #90EE90, white);
        border-radius: 2px;
    }

    .hero-wave {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }

    /* Stats Counter */
    .stats-counter {
        display: flex;
        justify-content: center;
        gap: 4rem;
        padding: 3rem 0;
        flex-wrap: wrap;
    }

    .counter-item {
        text-align: center;
    }

    .counter-value {
        display: block;
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary);
    }

    .counter-label {
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    /* Features */
    .section-header {
        margin-bottom: 3rem;
    }

    .section-header h2 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .section-header p {
        color: var(--text-muted);
        font-size: 1.1rem;
    }

    .features {
        padding: 5rem 0;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .feature-card {
        padding: 2rem;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .feature-card h3 {
        margin-bottom: 0.75rem;
    }

    .feature-card p {
        color: var(--text-muted);
        line-height: 1.6;
    }

    /* How It Works */
    .how-it-works {
        background: linear-gradient(180deg, #f8f9fa, white);
        padding: 5rem 0;
    }

    .steps-grid {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .step-card {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        text-align: center;
        max-width: 280px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .step-number {
        width: 50px;
        height: 50px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0 auto 1rem;
    }

    .step-arrow {
        font-size: 2rem;
        color: var(--primary);
    }

    @media (max-width: 768px) {
        .step-arrow {
            display: none;
        }
    }

    /* Social Proof */
    .social-proof {
        padding: 4rem 0;
    }

    .testimonial-card {
        max-width: 700px;
        margin: 0 auto;
        padding: 3rem;
        text-align: center;
    }

    .quote-icon {
        font-size: 5rem;
        line-height: 1;
        color: var(--primary);
        opacity: 0.3;
    }

    .quote-text {
        font-size: 1.3rem;
        line-height: 1.7;
        font-style: italic;
        margin: 1rem 0 2rem;
    }

    .quote-author {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .author-avatar {
        width: 50px;
        height: 50px;
        background: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .quote-author strong {
        display: block;
    }

    .quote-author span {
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, var(--primary), #082e11);
        color: white;
        padding: 5rem 0;
    }

    .cta-section h2 {
        color: white;
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .cta-section p {
        opacity: 0.9;
        margin-bottom: 2rem;
        font-size: 1.2rem;
    }

    .cta-section .btn-primary {
        background: white;
        color: var(--primary);
    }

    .cta-section .btn-primary:hover {
        background: #f0f0f0;
    }
</style>

<?php get_footer(); ?>