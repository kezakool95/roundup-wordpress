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
        'public' => true,
        'has_archive' => true,
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

// ============================================
// REST API ENDPOINTS
// ============================================

add_action('rest_api_init', 'teed_up_register_rest_routes');
function teed_up_register_rest_routes()
{
    // Submit Round with hole-by-hole scores
    register_rest_route('teedup/v1', '/submit-round', [
        'methods' => 'POST',
        'callback' => 'teed_up_submit_round',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    // Friend System Routes
    register_rest_route('teedup/v1', '/friends/request', [
        'methods' => 'POST',
        'callback' => 'teed_up_send_friend_request',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/friends/respond', [
        'methods' => 'POST',
        'callback' => 'teed_up_respond_friend_request',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/friends/list', [
        'methods' => 'GET',
        'callback' => 'teed_up_get_friends_list',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/friends/remove', [
        'methods' => 'POST',
        'callback' => 'teed_up_remove_friend',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    // Leaderboard Route
    register_rest_route('teedup/v1', '/leaderboard', [
        'methods' => 'GET',
        'callback' => 'teed_up_get_leaderboard',
        'permission_callback' => '__return_true'
    ]);

    // User Stats Route
    register_rest_route('teedup/v1', '/user-stats', [
        'methods' => 'GET',
        'callback' => 'teed_up_get_user_stats',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    // Club Distances Route
    register_rest_route('teedup/v1', '/club-distances', [
        'methods' => ['GET', 'POST'],
        'callback' => 'teed_up_club_distances',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);
}

/**
 * Submit Round with hole-by-hole scores
 */
function teed_up_submit_round($request)
{
    $user_id = get_current_user_id();
    $params = $request->get_json_params();

    $course_id = intval($params['course_id']);
    $date = sanitize_text_field($params['date']);
    $holes_played = intval($params['holes_played']) ?: 18;
    $scores = array_map('intval', $params['scores']); // Array of hole scores
    $total_score = array_sum($scores);
    $is_practice = isset($params['is_practice']) ? (bool)$params['is_practice'] : false;

    // Get course name for title
    $course_name = get_the_title($course_id);

    // Create the round post
    $post_type = $is_practice ? 'practice_session' : 'round';
    $round_id = wp_insert_post([
        'post_type' => $post_type,
        'post_title' => ($is_practice ? 'Practice: ' : 'Round at ') . $course_name,
        'post_status' => 'publish',
        'post_author' => $user_id,
    ]);

    if (is_wp_error($round_id)) {
        return new WP_Error('insert_failed', 'Failed to create round', ['status' => 500]);
    }

    // Save ACF fields
    update_field('course', $course_id, $round_id);
    update_field('date', $date, $round_id);
    update_field('score', $total_score, $round_id);
    update_field('holes_played', $holes_played, $round_id);
    update_field('hole_scores', $scores, $round_id);

    // Recalculate handicap if not practice
    if (!$is_practice) {
        $new_handicap = teed_up_calculate_and_update_handicap($user_id);
    }

    return [
        'success' => true,
        'round_id' => $round_id,
        'total_score' => $total_score,
        'handicap' => $new_handicap ?? teed_up_get_handicap_index($user_id),
        'message' => $is_practice ? 'Practice session logged!' : 'Round recorded successfully!'
    ];
}

/**
 * Calculate and update user handicap
 */
function teed_up_calculate_and_update_handicap($user_id)
{
    $handicap = teed_up_get_handicap_index($user_id);
    update_user_meta($user_id, 'current_handicap', $handicap);

    // Store handicap history for trend tracking
    $history = get_user_meta($user_id, 'handicap_history', true) ?: [];
    $history[] = [
        'date' => current_time('Y-m-d'),
        'handicap' => $handicap
    ];
    // Keep last 50 entries
    $history = array_slice($history, -50);
    update_user_meta($user_id, 'handicap_history', $history);

    return $handicap;
}

// ============================================
// FRIEND SYSTEM
// ============================================

/**
 * Send friend request
 */
function teed_up_send_friend_request($request)
{
    $user_id = get_current_user_id();
    $params = $request->get_json_params();
    $friend_id = intval($params['friend_id']);

    if ($friend_id === $user_id) {
        return new WP_Error('invalid_request', 'Cannot friend yourself', ['status' => 400]);
    }

    // Check if already friends or request pending
    $friends = get_user_meta($user_id, 'friends_list', true) ?: [];
    $sent_requests = get_user_meta($user_id, 'friend_requests_sent', true) ?: [];

    if (in_array($friend_id, $friends)) {
        return new WP_Error('already_friends', 'Already friends with this user', ['status' => 400]);
    }

    if (in_array($friend_id, $sent_requests)) {
        return new WP_Error('request_pending', 'Friend request already sent', ['status' => 400]);
    }

    // Add to sent requests for current user
    $sent_requests[] = $friend_id;
    update_user_meta($user_id, 'friend_requests_sent', array_unique($sent_requests));

    // Add to received requests for target user
    $received = get_user_meta($friend_id, 'friend_requests_received', true) ?: [];
    $received[] = $user_id;
    update_user_meta($friend_id, 'friend_requests_received', array_unique($received));

    return [
        'success' => true,
        'message' => 'Friend request sent!'
    ];
}

/**
 * Respond to friend request (accept/decline)
 */
function teed_up_respond_friend_request($request)
{
    $user_id = get_current_user_id();
    $params = $request->get_json_params();
    $requester_id = intval($params['requester_id']);
    $action = sanitize_text_field($params['action']); // 'accept' or 'decline'

    // Get received requests
    $received = get_user_meta($user_id, 'friend_requests_received', true) ?: [];

    if (!in_array($requester_id, $received)) {
        return new WP_Error('no_request', 'No friend request from this user', ['status' => 400]);
    }

    // Remove from pending requests
    $received = array_diff($received, [$requester_id]);
    update_user_meta($user_id, 'friend_requests_received', array_values($received));

    // Remove from requester's sent list
    $sent = get_user_meta($requester_id, 'friend_requests_sent', true) ?: [];
    $sent = array_diff($sent, [$user_id]);
    update_user_meta($requester_id, 'friend_requests_sent', array_values($sent));

    if ($action === 'accept') {
        // Add to both users' friends lists
        $my_friends = get_user_meta($user_id, 'friends_list', true) ?: [];
        $my_friends[] = $requester_id;
        update_user_meta($user_id, 'friends_list', array_unique($my_friends));

        $their_friends = get_user_meta($requester_id, 'friends_list', true) ?: [];
        $their_friends[] = $user_id;
        update_user_meta($requester_id, 'friends_list', array_unique($their_friends));

        return ['success' => true, 'message' => 'Friend request accepted!'];
    }

    return ['success' => true, 'message' => 'Friend request declined'];
}

/**
 * Get friends list with details
 */
function teed_up_get_friends_list($request)
{
    $user_id = get_current_user_id();

    $friends_ids = get_user_meta($user_id, 'friends_list', true) ?: [];
    $pending_received = get_user_meta($user_id, 'friend_requests_received', true) ?: [];
    $pending_sent = get_user_meta($user_id, 'friend_requests_sent', true) ?: [];

    $format_user = function ($id) {
        $user = get_userdata($id);
        if (!$user)
            return null;
        return [
        'id' => $id,
        'name' => $user->display_name,
        'avatar' => get_avatar_url($id),
        'handicap' => teed_up_get_handicap_index($id)
        ];
    };

    return [
        'friends' => array_values(array_filter(array_map($format_user, $friends_ids))),
        'pending_received' => array_values(array_filter(array_map($format_user, $pending_received))),
        'pending_sent' => array_values(array_filter(array_map($format_user, $pending_sent)))
    ];
}

/**
 * Remove friend
 */
function teed_up_remove_friend($request)
{
    $user_id = get_current_user_id();
    $params = $request->get_json_params();
    $friend_id = intval($params['friend_id']);

    // Remove from both users
    $my_friends = get_user_meta($user_id, 'friends_list', true) ?: [];
    $my_friends = array_diff($my_friends, [$friend_id]);
    update_user_meta($user_id, 'friends_list', array_values($my_friends));

    $their_friends = get_user_meta($friend_id, 'friends_list', true) ?: [];
    $their_friends = array_diff($their_friends, [$user_id]);
    update_user_meta($friend_id, 'friends_list', array_values($their_friends));

    return ['success' => true, 'message' => 'Friend removed'];
}

// ============================================
// LEADERBOARDS
// ============================================

/**
 * Get leaderboard data
 */
function teed_up_get_leaderboard($request)
{
    $type = $request->get_param('type') ?: 'global'; // global, friends, course
    $course_id = $request->get_param('course_id');
    $user_id = get_current_user_id();
    $limit = intval($request->get_param('limit')) ?: 20;

    $leaderboard = [];

    if ($type === 'global') {
        // Get all users with handicaps
        $users = get_users(['role__in' => ['subscriber', 'administrator']]);
        foreach ($users as $user) {
            $handicap = teed_up_get_handicap_index($user->ID);
            if ($handicap !== 'NH') {
                $leaderboard[] = [
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'avatar' => get_avatar_url($user->ID),
                    'handicap' => floatval($handicap),
                    'rounds_played' => teed_up_get_rounds_count($user->ID)
                ];
            }
        }
    }
    elseif ($type === 'friends' && $user_id) {
        $friends_ids = get_user_meta($user_id, 'friends_list', true) ?: [];
        $friends_ids[] = $user_id; // Include self

        foreach ($friends_ids as $fid) {
            $user = get_userdata($fid);
            if (!$user)
                continue;
            $handicap = teed_up_get_handicap_index($fid);
            if ($handicap !== 'NH') {
                $leaderboard[] = [
                    'id' => $fid,
                    'name' => $user->display_name,
                    'avatar' => get_avatar_url($fid),
                    'handicap' => floatval($handicap),
                    'rounds_played' => teed_up_get_rounds_count($fid),
                    'is_current_user' => $fid === $user_id
                ];
            }
        }
    }
    elseif ($type === 'course' && $course_id) {
        // Best scores at specific course
        $args = [
            'post_type' => 'round',
            'posts_per_page' => -1,
            'meta_query' => [
                ['key' => 'course', 'value' => $course_id]
            ]
        ];
        $rounds = get_posts($args);

        $user_best = [];
        foreach ($rounds as $round) {
            $uid = $round->post_author;
            $score = get_field('score', $round->ID);
            if (!isset($user_best[$uid]) || $score < $user_best[$uid]) {
                $user_best[$uid] = $score;
            }
        }

        foreach ($user_best as $uid => $best_score) {
            $user = get_userdata($uid);
            if (!$user)
                continue;
            $leaderboard[] = [
                'id' => $uid,
                'name' => $user->display_name,
                'avatar' => get_avatar_url($uid),
                'best_score' => $best_score,
                'handicap' => teed_up_get_handicap_index($uid)
            ];
        }

        // Sort by best score
        usort($leaderboard, fn($a, $b) => $a['best_score'] - $b['best_score']);
    }

    // Sort by handicap (lower is better) for global/friends
    if ($type !== 'course') {
        usort($leaderboard, fn($a, $b) => $a['handicap'] - $b['handicap']);
    }

    // Add rank
    foreach ($leaderboard as $i => &$entry) {
        $entry['rank'] = $i + 1;
    }

    return array_slice($leaderboard, 0, $limit);
}

/**
 * Get user rounds count
 */
function teed_up_get_rounds_count($user_id)
{
    $query = new WP_Query([
        'post_type' => 'round',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    return $query->found_posts;
}

// ============================================
// USER STATS
// ============================================

/**
 * Get comprehensive user stats
 */
function teed_up_get_user_stats($request)
{
    $user_id = get_current_user_id();

    // Get all rounds
    $rounds = get_posts([
        'post_type' => 'round',
        'author' => $user_id,
        'posts_per_page' => -1,
        'meta_key' => 'date',
        'orderby' => 'meta_value',
        'order' => 'DESC'
    ]);

    $stats = [
        'handicap' => teed_up_get_handicap_index($user_id),
        'handicap_history' => get_user_meta($user_id, 'handicap_history', true) ?: [],
        'rounds_played' => count($rounds),
        'scores' => [],
        'dates' => [],
        'best_round' => null,
        'worst_round' => null,
        'avg_score' => 0,
        'courses_played' => [],
        'recent_trend' => 'stable'
    ];

    if (empty($rounds))
        return $stats;

    $total = 0;
    $course_ids = [];

    foreach ($rounds as $round) {
        $score = get_field('score', $round->ID);
        $date = get_field('date', $round->ID);
        $course_id = get_field('course', $round->ID);

        if ($score) {
            $stats['scores'][] = $score;
            $stats['dates'][] = $date ? date('M d', strtotime($date)) : get_the_date('M d', $round);
            $total += $score;

            if (!$stats['best_round'] || $score < $stats['best_round']['score']) {
                $stats['best_round'] = [
                    'score' => $score,
                    'course' => get_the_title($course_id),
                    'date' => $date
                ];
            }
            if (!$stats['worst_round'] || $score > $stats['worst_round']['score']) {
                $stats['worst_round'] = [
                    'score' => $score,
                    'course' => get_the_title($course_id),
                    'date' => $date
                ];
            }

            if ($course_id && !in_array($course_id, $course_ids)) {
                $course_ids[] = $course_id;
            }
        }
    }

    $stats['avg_score'] = round($total / count($stats['scores']), 1);
    $stats['courses_played'] = count($course_ids);

    // Reverse for chart display (oldest to newest)
    $stats['scores'] = array_reverse($stats['scores']);
    $stats['dates'] = array_reverse($stats['dates']);

    // Calculate trend (compare last 5 to previous 5)
    if (count($rounds) >= 10) {
        $recent_avg = array_sum(array_slice($stats['scores'], -5)) / 5;
        $previous_avg = array_sum(array_slice($stats['scores'], -10, 5)) / 5;
        if ($recent_avg < $previous_avg - 1) {
            $stats['recent_trend'] = 'improving';
        }
        elseif ($recent_avg > $previous_avg + 1) {
            $stats['recent_trend'] = 'declining';
        }
    }

    return $stats;
}

// ============================================
// CLUB DISTANCES
// ============================================

/**
 * Get or update club distances
 */
function teed_up_club_distances($request)
{
    $user_id = get_current_user_id();

    if ($request->get_method() === 'GET') {
        $distances = get_user_meta($user_id, 'club_distances', true) ?: teed_up_default_club_distances();
        return $distances;
    }

    // POST - update
    $params = $request->get_json_params();
    $distances = $params['distances'];

    update_user_meta($user_id, 'club_distances', $distances);

    return ['success' => true, 'distances' => $distances];
}

/**
 * Default club distances
 */
function teed_up_default_club_distances()
{
    return [
        ['club' => 'Driver', 'distance' => 230, 'unit' => 'm'],
        ['club' => '3 Wood', 'distance' => 210, 'unit' => 'm'],
        ['club' => '5 Wood', 'distance' => 195, 'unit' => 'm'],
        ['club' => '4 Iron', 'distance' => 180, 'unit' => 'm'],
        ['club' => '5 Iron', 'distance' => 170, 'unit' => 'm'],
        ['club' => '6 Iron', 'distance' => 160, 'unit' => 'm'],
        ['club' => '7 Iron', 'distance' => 150, 'unit' => 'm'],
        ['club' => '8 Iron', 'distance' => 140, 'unit' => 'm'],
        ['club' => '9 Iron', 'distance' => 130, 'unit' => 'm'],
        ['club' => 'PW', 'distance' => 115, 'unit' => 'm'],
        ['club' => 'SW', 'distance' => 90, 'unit' => 'm'],
        ['club' => 'LW', 'distance' => 70, 'unit' => 'm'],
    ];
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

// ============================================
// AUTO-CREATE PAGES ON THEME ACTIVATION
// ============================================

add_action('after_switch_theme', 'teed_up_create_pages');
add_action('admin_init', 'teed_up_check_pages_created');

function teed_up_create_pages()
{
    // Define pages to create
    $pages = [
        [
            'title' => 'Dashboard',
            'slug' => 'dashboard',
            'template' => 'page-dashboard.php'
        ],
        [
            'title' => 'Log Round',
            'slug' => 'log-round',
            'template' => 'page-round-creator.php'
        ],
        [
            'title' => 'Stats',
            'slug' => 'stats',
            'template' => 'page-stats.php'
        ],
        [
            'title' => 'Friends',
            'slug' => 'friends',
            'template' => 'page-friends.php'
        ],
        [
            'title' => 'Leaderboards',
            'slug' => 'leaderboards',
            'template' => 'page-leaderboards.php'
        ],
        [
            'title' => 'Login',
            'slug' => 'login',
            'template' => 'page-auth.php'
        ],
        [
            'title' => 'Rounds',
            'slug' => 'rounds',
            'template' => 'archive-round.php'
        ]
    ];

    $created_count = 0;
    foreach ($pages as $page_data) {
        // Check if page already exists
        $existing = get_page_by_path($page_data['slug']);

        if (!$existing) {
            // Create the page
            $page_id = wp_insert_post([
                'post_title' => $page_data['title'],
                'post_name' => $page_data['slug'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => ''
            ]);

            // Set the page template
            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                $created_count++;
            }
        }
    }

    // Mark that pages have been created
    if ($created_count > 0) {
        update_option('teed_up_pages_created', true);
    }
}

/**
 * Check if pages need to be created (for existing installations)
 */
function teed_up_check_pages_created()
{
    // If pages haven't been created yet, create them
    if (!get_option('teed_up_pages_created')) {
        teed_up_create_pages();
    }
}

/**
 * Admin notice to inform about page creation
 */
add_action('admin_notices', 'teed_up_pages_notice');
function teed_up_pages_notice()
{
    if (get_transient('teed_up_pages_just_created')) {
?>
<div class="notice notice-success is-dismissible">
    <p><strong>Round Up:</strong> Required pages have been automatically created!</p>
</div>
<?php
        delete_transient('teed_up_pages_just_created');
    }
}