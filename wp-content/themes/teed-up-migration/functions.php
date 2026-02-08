<?php
// Teed Up Migration Functions

add_action('init', 'teed_up_register_cpts');
function teed_up_register_cpts()
{
    // Register Course CPT
    register_post_type('course', [
        'labels' => [
            'name' => 'Courses',
            'singular_name' => 'Course',
        ],
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-location',
        'supports' => ['title', 'editor', 'thumbnail'],
    ]);

    // Register Round CPT
    register_post_type('round', [
        'labels' => [
            'name' => 'Rounds',
            'singular_name' => 'Round',
        ],
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'author'],
    ]);

    // Register Practice Session CPT
    register_post_type('practice_session', [
        'labels' => [
            'name' => 'Practice Sessions',
            'singular_name' => 'Practice Session',
        ],
        'public' => true,
        'has_archive' => false,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-performance',
        'supports' => ['title', 'author', 'editor'],
    ]);
}

// Register Menus
add_action('after_setup_theme', 'teed_up_register_menus');
function teed_up_register_menus()
{
    register_nav_menus([
        'primary_menu' => 'Primary Menu',
    ]);
}

// Register ACF Blocks
add_action('acf/init', 'teed_up_register_blocks');
function teed_up_register_blocks()
{
    if (function_exists('acf_register_block_type')) {
        acf_register_block_type(array(
            'name' => 'handicap-card',
            'title' => __('Handicap Card'),
            'description' => __('A block to display the user\'s current handicap.'),
            'render_template' => 'blocks/handicap-card/handicap-card.php',
            'category' => 'formatting',
            'icon' => 'chart-bar',
            'keywords' => array('golf', 'handicap', 'stats'),
        ));
    }
}

// Register ACF Fields (PHP Export equivalent)
if (function_exists('acf_add_local_field_group')):

    // Handicap Block Fields
    acf_add_local_field_group(array(
        'key' => 'group_handicap_block',
        'title' => 'Handicap Settings',
        'fields' => array(
                array(
                'key' => 'field_handicap_value',
                'label' => 'Current Handicap',
                'name' => 'current_handicap',
                'type' => 'number',
                'step' => '0.1',
            ),
                array(
                'key' => 'field_handicap_trend',
                'label' => 'Trend',
                'name' => 'handicap_trend',
                'type' => 'select',
                'choices' => array(
                    'improving' => 'Improving',
                    'stable' => 'Stable',
                    'declining' => 'Declining',
                ),
            ),
        ),
        'location' => array(
                array(
                    array(
                    'param' => 'block',
                    'operator' => '==',
                    'value' => 'acf/handicap-card',
                ),
            ),
        ),
    ));

    // Course Fields
    acf_add_local_field_group(array(
        'key' => 'group_course_details',
        'title' => 'Course Details',
        'fields' => array(
                array(
                'key' => 'field_course_location',
                'label' => 'Location',
                'name' => 'location',
                'type' => 'text',
            ),
                array(
                'key' => 'field_course_rating',
                'label' => 'Rating',
                'name' => 'rating',
                'type' => 'number',
                'step' => '0.1',
            ),
                array(
                'key' => 'field_course_par',
                'label' => 'Total Par',
                'name' => 'par',
                'type' => 'number',
                'default_value' => 72,
            ),
                array(
                'key' => 'field_course_slope',
                'label' => 'Slope Rating',
                'name' => 'slope',
                'type' => 'number',
                'default_value' => 113,
            ),
                array(
                'key' => 'field_course_booking_url',
                'label' => 'Booking URL',
                'name' => 'booking_url',
                'type' => 'url',
            ),
                array(
                'key' => 'field_course_image_url',
                'label' => 'External Image URL',
                'name' => 'external_image_url',
                'type' => 'url',
            ),
                array(
                'key' => 'field_course_image_source',
                'label' => 'Image Source URL',
                'name' => 'image_source_url',
                'type' => 'url',
            ),
                array(
                'key' => 'field_course_holes',
                'label' => 'Holes',
                'name' => 'holes',
                'type' => 'number',
                'default_value' => 18,
            ),
                array(
                'key' => 'field_course_pars',
                'label' => 'Pars per Hole',
                'name' => 'pars_per_hole',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => 'Add Hole',
                'sub_fields' => array(
                        array(
                        'key' => 'field_hole_par',
                        'label' => 'Par',
                        'name' => 'par',
                        'type' => 'number',
                        'min' => 3,
                        'max' => 5,
                    ),
                ),
            ),
        ),
        'location' => array(
                array(
                    array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'course',
                ),
            ),
        ),
    ));

    // Round Fields
    acf_add_local_field_group(array(
        'key' => 'group_round_details',
        'title' => 'Round Details',
        'fields' => array(
                array(
                'key' => 'field_round_course',
                'label' => 'Course',
                'name' => 'course',
                'type' => 'post_object',
                'post_type' => array('course'),
                'return_format' => 'id',
            ),
                array(
                'key' => 'field_round_date',
                'label' => 'Date',
                'name' => 'date',
                'type' => 'date_time_picker',
            ),
                array(
                'key' => 'field_round_score',
                'label' => 'Total Score',
                'name' => 'score',
                'type' => 'number',
            ),
                array(
                'key' => 'field_round_holes_played',
                'label' => 'Holes Played',
                'name' => 'holes_played',
                'type' => 'number',
                'default_value' => 18,
            ),
        ),
        'location' => array(
                array(
                    array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'round',
                ),
            ),
        ),
    ));

    // User Availability Fields
    acf_add_local_field_group(array(
        'key' => 'group_user_availability',
        'title' => 'Weekly Availability',
        'fields' => array(
                array(
                'key' => 'field_availability_repeater',
                'label' => 'Standard Weekly Schedule',
                'name' => 'weekly_schedule',
                'type' => 'repeater',
                'button_label' => 'Add Slot',
                'sub_fields' => array(
                        array(
                        'key' => 'field_avail_day',
                        'label' => 'Day',
                        'name' => 'day',
                        'type' => 'select',
                        'choices' => array(
                            'Mon' => 'Monday',
                            'Tue' => 'Tuesday',
                            'Wed' => 'Wednesday',
                            'Thu' => 'Thursday',
                            'Fri' => 'Friday',
                            'Sat' => 'Saturday',
                            'Sun' => 'Sunday',
                        ),
                    ),
                        array(
                        'key' => 'field_avail_time_start',
                        'label' => 'Start Time',
                        'name' => 'start_time',
                        'type' => 'time_picker',
                    ),
                        array(
                        'key' => 'field_avail_time_end',
                        'label' => 'End Time',
                        'name' => 'end_time',
                        'type' => 'time_picker',
                    ),
                ),
            ),
        ),
        'location' => array(
                array(
                    array(
                    'param' => 'user_form',
                    'operator' => '==',
                    'value' => 'all',
                ),
            ),
        ),
    ));

endif;

// Enqueue Scripts & Styles
add_action('wp_enqueue_scripts', 'teed_up_enqueue_scripts');
function teed_up_enqueue_scripts()
{
    // 1. Google Fonts (Inter & Outfit)
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap', [], null);

    // 2. Alpine.js (for Round Creator interactions)
    wp_enqueue_script('alpine-js', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], null, true);

    // 3. Chart.js (for Stats)
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);

    // 4. Main Theme Styles
    wp_enqueue_style('teed-up-style', get_stylesheet_uri());
}

// Handle Custom Registration
add_action('admin_post_nopriv_teed_up_register_user', 'teed_up_handle_registration');
function teed_up_handle_registration()
{
    if (isset($_POST['email'])) {
        $username = sanitize_user($_POST['email']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $fullname = sanitize_text_field($_POST['fullname']);

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_redirect(site_url('/login?registration=failed'));
            exit;
        }

        update_user_meta($user_id, 'first_name', $fullname);
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        wp_redirect(site_url('/dashboard'));
        exit;
    }
}

/**
 * Calculate USGA Handicap Index
 * Best 8 out of last 20 differentials
 */
function teed_up_get_handicap_index($user_id)
{
    $args = [
        'post_type' => 'round',
        'posts_per_page' => 20,
        'author' => $user_id,
        'meta_key' => 'date',
        'orderby' => 'meta_value',
        'order' => 'DESC'
    ];
    $rounds = get_posts($args);

    if (empty($rounds))
        return 'NH';

    $differentials = [];
    foreach ($rounds as $round) {
        $score = get_field('score', $round->ID);
        $course_id = get_field('course', $round->ID);

        if ($score && $course_id) {
            $rating = get_field('rating', $course_id) ?: 72;
            $slope = get_field('slope', $course_id) ?: 113;
            $diff = ($score - $rating) * 113 / $slope;
            $differentials[] = $diff;
        }
    }

    sort($differentials);
    $count = count($differentials);

    $to_use = 0;
    if ($count >= 20)
        $to_use = 8;
    elseif ($count >= 19)
        $to_use = 7;
    elseif ($count >= 17)
        $to_use = 6;
    elseif ($count >= 15)
        $to_use = 5;
    elseif ($count >= 12)
        $to_use = 4;
    elseif ($count >= 9)
        $to_use = 3;
    elseif ($count >= 7)
        $to_use = 2;
    elseif ($count >= 3)
        $to_use = 1;
    else
        return "NH";

    $subset = array_slice($differentials, 0, $to_use);
    $average = array_sum($subset) / count($subset);

    return number_format($average, 1);
}