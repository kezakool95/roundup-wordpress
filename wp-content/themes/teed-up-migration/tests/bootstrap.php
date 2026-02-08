<?php
/**
 * Mock WordPress functions for unit testing
 */

// Basic constants
if (!defined('PHPUNIT_RUNNING')) define('PHPUNIT_RUNNING', true);
if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');
if (!defined('SITE_URL')) define('SITE_URL', 'http://example.com');

// Mock function storage
global $wp_mocks;
$wp_mocks = [
    'user_meta' => [],
    'options' => [],
    'posts' => [],
    'users' => []
];

if (!function_exists('get_user_meta')) {
    function get_user_meta($user_id, $key, $single = false) {
        global $wp_mocks;
        return $wp_mocks['user_meta'][$user_id][$key] ?? ($single ? '' : []);
    }
}

if (!function_exists('update_user_meta')) {
    function update_user_meta($user_id, $key, $value) {
        global $wp_mocks;
        $wp_mocks['user_meta'][$user_id][$key] = $value;
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($key, $default = false) {
        global $wp_mocks;
        return $wp_mocks['options'][$key] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($key, $value) {
        global $wp_mocks;
        $wp_mocks['options'][$key] = $value;
        return true;
    }
}

if (!function_exists('wp_create_user')) {
    function wp_create_user($username, $password, $email) {
        global $wp_mocks;
        if (isset($wp_mocks['users'][$email])) {
            return new WP_Error('email_exists', 'Email exists');
        }
        $id = count($wp_mocks['users']) + 1;
        $wp_mocks['users'][$email] = $id;
        return $id;
    }
}

if (!function_exists('email_exists')) {
    function email_exists($email) {
        global $wp_mocks;
        return isset($wp_mocks['users'][$email]) ? $wp_mocks['users'][$email] : false;
    }
}

if (!function_exists('sanitize_user')) { function sanitize_user($user) { return $user; } }
if (!function_exists('sanitize_email')) { function sanitize_email($email) { return $email; } }
if (!function_exists('sanitize_text_field')) { function sanitize_text_field($text) { return $text; } }
if (!function_exists('is_email')) { function is_email($email) { return strpos($email, '@') !== false; } }

if (!function_exists('wp_redirect')) {
    function wp_redirect($location) {
        global $redirect_to;
        $redirect_to = $location;
    }
}

if (!function_exists('site_url')) {
    function site_url($path = '') {
        return 'http://example.com' . $path;
    }
}

if (!function_exists('wp_set_current_user')) { function wp_set_current_user($id) {} }
if (!function_exists('wp_set_auth_cookie')) { function wp_set_auth_cookie($id) {} }
if (!function_exists('add_action')) { function add_action($hook, $callback) {} }
if (!function_exists('add_filter')) { function add_filter($hook, $callback) {} }

if (!class_exists('WP_Error')) {
    class WP_Error {
        public $code;
        public $message;
        public function __construct($code, $message) { $this->code = $code; $this->message = $message; }
        public function get_error_message() { return $this->message; }
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) { return $thing instanceof WP_Error; }
}

if (!class_exists('WP_User')) {
    class WP_User {
        public $ID;
        public function __construct($id = 0) { $this->ID = $id; }
        public function set_role($role) {}
        public function add_role($role) {}
    }
}

if (!function_exists('update_post_meta')) {
    function update_post_meta($id, $key, $val) { return true; }
}

if (!class_exists('WooCommerce')) {
    class WooCommerce {}
}

if (!function_exists('esc_url')) { function esc_url($url) { return $url; } }
if (!function_exists('admin_url')) { function admin_url($path) { return 'http://example.com/wp-admin/' . $path; } }
if (!function_exists('wp_verify_nonce')) { function wp_verify_nonce($nonce, $action) { return true; } }
if (!function_exists('wp_nonce_field')) { function wp_nonce_field($a, $b) {} }

if (!function_exists('get_userdata')) {
    function get_userdata($id) {
        $u = new stdClass();
        $u->user_pass = "hashed_pass";
        $u->user_email = "test@example.com";
        $u->display_name = "Test";
        $u->user_registered = "2026-01-01";
        $u->user_login = "test";
        $u->first_name = "Mock";
        return $u;
    }
}
if (!function_exists('get_current_user_id')) { function get_current_user_id() { return 1; } }
if (!function_exists('wp_update_user')) { function wp_update_user($data) { return $data['ID'] ?? 1; } }
if (!function_exists('wp_check_password')) { function wp_check_password($p, $h, $id) { return true; } }
if (!function_exists('wp_insert_post')) {
    function wp_insert_post($args) {
        global $wp_mocks;
        $id = count($wp_mocks['posts']) + 1000;
        $wp_mocks['posts'][$id] = $args;
        return $id;
    }
}
if (!function_exists('get_post')) {
    function get_post($id) {
        global $wp_mocks;
        return isset($wp_mocks['posts'][$id]) ? (object)$wp_mocks['posts'][$id] : null;
    }
}
if (!function_exists('wp_set_object_terms')) { function wp_set_object_terms($id, $terms, $taxonomy) {} }

// Load the functions file
require_once __DIR__ . '/../functions.php';