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
                <p class="login-submit">
                    <input type="submit" name="wp-submit" class="button button-primary" value="Create Account">
                </p>
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
</style>

<?php get_footer(); ?>