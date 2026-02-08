<?php
/*
 Template Name: Authentication
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(site_url('/dashboard'));
    exit;
}

get_header();
?>

<div class="auth-container container" x-data="{ isLogin: true }">
    <div class="auth-card glass-card">
        <div class="auth-header text-center">
            <h2 x-text="isLogin ? 'Welcome Back' : 'Join Round Up'"></h2>
            <p class="text-muted" x-text="isLogin ? 'Sign in to your account' : 'Start your golfing journey today'"></p>

            <?php if (isset($_GET['registration']) && $_GET['registration'] === 'failed'): ?>
            <div class="alert alert-error mt-1">
                <?php echo esc_html($_GET['reason'] ?? 'Registration failed. Please try again.'); ?>
            </div>
            <?php
endif; ?>

            <?php if (isset($_GET['login']) && $_GET['login'] === 'failed'): ?>
            <div class="alert alert-error mt-1">
                Invalid email or password.
            </div>
            <?php
endif; ?>
        </div>

        <!-- Login Form -->
        <div x-show="isLogin" class="auth-form-wrapper fade-in">
            <?php
$args = array(
    'redirect' => site_url('/dashboard'),
    'form_id' => 'loginform',
    'label_username' => 'Email Address',
    'label_password' => 'Password',
    'label_remember' => 'Remember Me',
    'label_log_in' => 'Sign In',
    'remember' => true
);
wp_login_form($args);
?>
        </div>

        <!-- Registration Form (Mock for Alpha) -->
        <div x-show="!isLogin" class="auth-form-wrapper fade-in" style="padding: 1rem 0;">
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="teed_up_register_user">
                <?php wp_nonce_field('teed_up_user_registration', 'registration_nonce'); ?>

                <div class="registration-steps" x-data="{ step: 1 }">
                    <!-- Step 1: Base Info -->
                    <div x-show="step === 1">
                        <p class="login-username">
                            <label for="reg_fullname">Full Name</label>
                            <input type="text" name="fullname" id="reg_fullname" class="input" required>
                        </p>
                        <p class="login-username">
                            <label for="reg_email">Email Address</label>
                            <input type="email" name="email" id="reg_email" class="input" required>
                        </p>
                        <p class="login-password">
                            <label for="reg_password">Password</label>
                            <input type="password" name="password" id="reg_password" class="input" required>
                        </p>
                        <button type="button" class="button button-primary mt-2" @click="step = 2">Choose Your
                            Plan</button>
                    </div>

                    <!-- Step 2: Plan Selection -->
                    <div x-show="step === 2" class="plan-selection-step fade-in">
                        <div class="text-center mb-2">
                            <h2 class="premium-heading">Choose Your Path</h2>
                            <p class="text-muted">Unlock the full potential of your game.</p>
                        </div>

                        <div class="plan-cards-v2">
                            <?php
$plans = teed_up_get_membership_plans();
foreach ($plans as $key => $plan):
    $is_popular = ($key === 'scratch');
?>
                            <label
                                class="plan-card-v2 <?php echo esc_attr($key); ?> <?php echo $is_popular ? 'is-popular' : ''; ?>">
                                <input type="radio" name="membership_plan" value="<?php echo esc_attr($key); ?>" <?php
                                    checked($key, 'free' ); ?> class="plan-radio">
                                <div class="plan-card-surface">
                                    <?php if ($is_popular): ?>
                                    <div class="popular-ribbon">Most Popular</div>
                                    <?php
    endif; ?>

                                    <div class="plan-header-v2">
                                        <span class="plan-symbol">
                                            <?php
    if ($key === 'free')
        echo '⛳️';
    elseif ($key === 'scratch')
        echo '🚀';
    elseif ($key === 'pro')
        echo '🏆';
?>
                                        </span>
                                        <h4 class="plan-title">
                                            <?php echo esc_html($plan['name']); ?>
                                        </h4>
                                        <div class="plan-cost">
                                            <span class="currency">$</span>
                                            <span class="price-val">
                                                <?php echo esc_html($plan['price']); ?>
                                            </span>
                                            <span class="frequency">/mo</span>
                                        </div>
                                    </div>

                                    <ul class="plan-features-v2">
                                        <?php foreach ($plan['features'] as $feature): ?>
                                        <li>
                                            <span class="check-mark">✓</span>
                                            <?php echo esc_html($feature); ?>
                                        </li>
                                        <?php
    endforeach; ?>
                                    </ul>
                                </div>
                            </label>
                            <?php
endforeach; ?>
                        </div>
                        <div class="auth-actions-v2 mt-3">
                            <button type="button" class="btn-back" @click="step = 1">
                                <span class="arrow">←</span> Back
                            </button>
                            <input type="submit" name="wp-submit" class="btn-premium-submit"
                                value="Complete Registration">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="auth-footer text-center mt-2">
            <p>
                <span x-text="isLogin ? 'Don\'t have an account? ' : 'Already have an account? '"></span>
                <button class="btn-link" @click="isLogin = !isLogin" x-text="isLogin ? 'Sign Up' : 'Log In'"></button>
            </p>
        </div>
    </div>
</div>

<style>
    .auth-container {
        max-width: 500px;
        padding-top: 4rem;
        padding-bottom: 4rem;
    }

    .alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-top: 1rem;
    }

    .alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .auth-card {
        padding: 2.5rem;
    }

    .auth-header {
        margin-bottom: 2rem;
    }

    .btn-link {
        background: none;
        border: none;
        color: var(--wp--preset--color--primary);
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        font-size: inherit;
    }

    .btn-link:hover {
        text-decoration: underline;
    }

    /* WP Login Form Styling Overrides */
    .login-username,
    .login-password {
        margin-bottom: 1rem;
    }

    .login-username label,
    .login-password label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .input {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
    }

    .login-remember {
        margin-bottom: 1rem;
    }

    .button-primary {
        width: 100%;
        background: var(--wp--preset--color--primary) !important;
        border-color: var(--wp--preset--color--primary) !important;
        color: white !important;
        padding: 0.8rem !important;
        height: auto !important;
        font-size: 1rem !important;
        border-radius: 12px !important;
        text-shadow: none !important;
    }

    /* Premium Membership Styles */
    .premium-heading {
        font-weight: 800;
        letter-spacing: -0.02em;
        background: linear-gradient(135deg, #111 0%, #444 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }

    .plan-cards-v2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
        margin: 2rem 0;
    }

    .plan-card-v2 {
        cursor: pointer;
        position: relative;
        display: block;
    }

    .plan-card-surface {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 20px;
        padding: 1.75rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        position: relative;
    }

    .plan-card-v2.is-popular .plan-card-surface {
        border: 2px solid var(--wp--preset--color--primary);
        background: rgba(255, 255, 255, 0.9);
    }

    .popular-ribbon {
        position: absolute;
        top: 0;
        right: 0;
        background: var(--wp--preset--color--primary);
        color: white;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 0.25rem 1rem;
        border-bottom-left-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        z-index: 10;
    }

    .plan-header-v2 {
        margin-bottom: 1.5rem;
    }

    .plan-symbol {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    .plan-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
        color: #111;
    }

    .plan-cost {
        display: flex;
        align-items: baseline;
        gap: 4px;
        margin-top: 0.25rem;
    }

    .plan-cost .currency {
        font-size: 1.1rem;
        font-weight: 600;
        color: #666;
    }

    .plan-cost .price-val {
        font-size: 2rem;
        font-weight: 800;
        color: #111;
    }

    .plan-cost .frequency {
        font-size: 0.9rem;
        color: #888;
    }

    .plan-features-v2 {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .plan-features-v2 li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        color: #444;
    }

    .check-mark {
        color: #10b981;
        font-weight: 900;
    }

    /* Selection State */
    .plan-radio:checked+.plan-card-surface {
        background: white;
        border-color: var(--wp--preset--color--primary);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        transform: translateY(-5px) scale(1.02);
    }

    .plan-card-v2:hover .plan-card-surface {
        border-color: rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
    }

    .plan-radio {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* Actions */
    .auth-actions-v2 {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-back {
        background: transparent;
        border: none;
        color: #666;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-back:hover {
        color: #111;
        transform: translateX(-3px);
    }

    .btn-premium-submit {
        background: var(--wp--preset--color--primary);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }

    .btn-premium-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        filter: brightness(1.1);
    }

    @media (min-width: 600px) {
        .plan-cards-v2 {
            grid-template-columns: repeat(3, 1fr);
            max-width: 900px;
            margin-left: -200px;
            margin-right: -200px;
        }

        .auth-card {
            max-width: 1000px;
            width: 100%;
        }
    }
</style>

<?php get_footer(); ?>