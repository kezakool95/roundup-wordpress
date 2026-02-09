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

    // Availability Route
    register_rest_route('teedup/v1', '/availability/check', [
        'methods' => 'POST',
        'callback' => 'teed_up_check_availability',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/availability/save', [
        'methods' => 'POST',
        'callback' => 'teed_up_save_availability',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/friends/availability', [
        'methods' => 'GET',
        'callback' => 'teed_up_get_friends_availability',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/subscription/update', [
        'methods' => 'POST',
        'callback' => 'teed_up_update_subscription',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/friends/history', [
        'methods' => 'GET',
        'callback' => 'teed_up_get_shared_history',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    // Booking System Routes
    register_rest_route('teedup/v1', '/booking/create', [
        'methods' => 'POST',
        'callback' => 'teed_up_create_booking',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/booking/respond', [
        'methods' => 'POST',
        'callback' => 'teed_up_respond_to_booking',
        'permission_callback' => function () {
        return is_user_logged_in();
    }
    ]);

    register_rest_route('teedup/v1', '/booking/list', [
        'methods' => 'GET',
        'callback' => 'teed_up_get_bookings_list',
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

    $round_id = isset($params['round_id']) ? intval($params['round_id']) : 0;
    $course_id = intval($params['course_id']);
    $date = sanitize_text_field($params['date']);
    $holes_played = intval($params['holes_played']) ?: 18;
    $scores = array_map('intval', $params['scores']); // Array of hole scores
    $total_score = array_sum($scores);
    $is_practice = isset($params['is_practice']) ? (bool)$params['is_practice'] : false;

    // Get course name for title
    $course_name = get_the_title($course_id);

    if ($round_id) {
        // Update existing booking/round
        wp_update_post([
            'ID' => $round_id,
            'post_title' => ($is_practice ? 'Practice: ' : 'Round at ') . $course_name,
        ]);
    } else {
        // Create the round post
        $post_type = $is_practice ? 'practice_session' : 'round';
        $round_id = wp_insert_post([
            'post_type' => $post_type,
            'post_title' => ($is_practice ? 'Practice: ' : 'Round at ') . $course_name,
            'post_status' => 'publish',
            'post_author' => $user_id,
        ]);
    }

    if (is_wp_error($round_id) || !$round_id) {
        return new WP_Error('save_failed', 'Failed to save round', ['status' => 500]);
    }

    // Save ACF fields
    update_field('course', $course_id, $round_id);
    update_field('date', $date, $round_id);
    update_field('score', $total_score, $round_id);
    update_field('holes_played', $holes_played, $round_id);
    update_field('hole_scores', $scores, $round_id);
    update_field('round_status', 'completed', $round_id);

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

/**
 * Check overlapping availability for multiple users
 */
function teed_up_check_availability($request)
{
    $params = $request->get_json_params();
    $user_ids = array_map('intval', (array)($params['user_ids'] ?? []));
    $duration_mins = intval($params['duration_mins'] ?? 150); // Default 2.5h
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    // Include current user if not in list
    $current_user_id = get_current_user_id();
    if (!in_array($current_user_id, $user_ids)) {
        $user_ids[] = $current_user_id;
    }

    $all_availability = [];
    foreach ($user_ids as $uid) {
        $all_availability[$uid] = get_field('weekly_schedule', 'user_' . $uid) ?: [];
    }

    $matching_slots = [];

    foreach ($days as $day) {
        // Check every 30 mins from 06:00 to 18:00
        for ($h = 6; $h <= 18; $h++) {
            for ($m = 0; $m < 60; $m += 30) {
                $start_time = sprintf('%02d:%02d', $h, $m);

                // Check if this slot is available for ALL users
                $all_ready = true;
                foreach ($user_ids as $uid) {
                    $user_ready = false;
                    foreach ($all_availability[$uid] as $slot) {
                        if (empty($slot['start_time']) || empty($slot['end_time'])) continue;
                        
                        // Normalize to HH:MM for robust comparison
                        $slot_start = date('H:i', strtotime($slot['start_time']));
                        $slot_end = date('H:i', strtotime($slot['end_time']));

                        if ($slot['day'] === $day && $slot_start <= $start_time && $slot_end > $start_time) {
                            $user_ready = true;
                            break;
                        }
                    }
                    if (!$user_ready) {
                        $all_ready = false;
                        break;
                    }
                }

                if ($all_ready) {
                    $matching_slots[$day][] = $start_time;
                }
            }
        }
    }

    // Filter for contiguous blocks
    $final_options = [];
    $slots_needed = ceil($duration_mins / 30);

    foreach ($matching_slots as $day => $times) {
        if (count($times) < $slots_needed) continue;

        for ($i = 0; $i <= count($times) - $slots_needed; $i++) {
            $is_contiguous = true;
            for ($j = 0; $j < $slots_needed - 1; $j++) {
                $curr = strtotime('2026-01-01 ' . $times[$i + $j]);
                $next = strtotime('2026-01-01 ' . $times[$i + $j + 1]);
                if ($next - $curr !== 1800) { // 30 mins
                    $is_contiguous = false;
                    break;
                }
            }
            if ($is_contiguous) {
                $final_options[] = [
                    'day' => $day,
                    'time' => $times[$i],
                    'label' => $day . ' @ ' . $times[$i]
                ];
            }
        }
    }

    return [
        'success' => true,
        'options' => $final_options
    ];
}

/**
 * Save user availability schedule
 */
function teed_up_save_availability($request)
{
    $user_id = get_current_user_id();
    $params = $request->get_json_params();
    $new_schedule = $params['schedule'] ?? [];

    // Map to ACF repeater format
    $acf_value = [];
    foreach ($new_schedule as $slot) {
        $start = !empty($slot['start_time']) ? date('H:i', strtotime($slot['start_time'])) : '';
        $end = !empty($slot['end_time']) ? date('H:i', strtotime($slot['end_time'])) : '';
        
        $acf_value[] = [
            'day' => sanitize_text_field($slot['day']),
            'start_time' => $start,
            'end_time' => $end
        ];
    }

    update_field('weekly_schedule', $acf_value, 'user_' . $user_id);

    return ['success' => true, 'message' => 'Schedule saved successfully!'];
}

/**
 * Get summary of friends availability for the next 48 hours
 */
function teed_up_get_friends_availability()
{
    $user_id = get_current_user_id();
    $friends = get_user_meta($user_id, 'friends_list', true) ?: [];
    
    if (empty($friends)) {
        return ['friends' => []];
    }

    $today = date('D');
    $tomorrow = date('D', strtotime('+1 day'));
    
    $results = [];
    foreach ($friends as $fid) {
        $f_data = get_userdata($fid);
        if (!$f_data) continue;

        $schedule = get_field('weekly_schedule', 'user_' . $fid) ?: [];
        $availability = [
            'today' => [],
            'tomorrow' => []
        ];

        foreach ($schedule as $slot) {
            if ($slot['day'] === $today) {
                $availability['today'][] = $slot['start_time'] . ' - ' . $slot['end_time'];
            } elseif ($slot['day'] === $tomorrow) {
                $availability['tomorrow'][] = $slot['start_time'] . ' - ' . $slot['end_time'];
            }
        }

        $results[] = [
            'id' => $fid,
            'name' => $f_data->display_name,
            'avatar' => get_avatar_url($fid),
            'availability' => $availability
        ];
    }

    return ['friends' => $results];
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
add_action('admin_post_teed_up_register_user', 'teed_up_handle_registration'); // Also for logged in if needed

function teed_up_handle_registration()
{
    if (!isset($_POST['registration_nonce']) || !wp_verify_nonce($_POST['registration_nonce'], 'teed_up_user_registration')) {
        wp_redirect(site_url('/login?registration=failed&reason=security_check'));
        exit;
    }

    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        wp_redirect(site_url('/login?registration=failed&reason=missing_fields'));
        exit;
    }

    $username = sanitize_user($_POST['email']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $fullname = isset($_POST['fullname']) ? sanitize_text_field($_POST['fullname']) : '';
    $plan = isset($_POST['membership_plan']) ? sanitize_text_field($_POST['membership_plan']) : 'free';

    // Validation
    if (!is_email($email)) {
        wp_redirect(site_url('/login?registration=failed&reason=invalid_email'));
        exit;
    }

    if (email_exists($email)) {
        wp_redirect(site_url('/login?registration=failed&reason=email_exists'));
        exit;
    }

    // Create User
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        wp_redirect(site_url('/login?registration=failed&reason=' . urlencode($user_id->get_error_message())));
        exit;
    }

    // Update Meta
    update_user_meta($user_id, 'first_name', $fullname);
    update_user_meta($user_id, 'membership_plan', $plan);

    // WooCommerce Customer Integration
    if (class_exists('WooCommerce')) {
        // Ensure user is marked as a customer
        $user = new WP_User($user_id);
        $user->add_role('customer');

        // You could also auto-populate billing info here if collected
        update_user_meta($user_id, 'billing_first_name', $fullname);
        update_user_meta($user_id, 'billing_email', $email);
    }

    // Auto Login
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_redirect(site_url('/dashboard?registration=success'));
    exit;
}

/**
 * Ensure Membership Products exist in WooCommerce
 */
function teed_up_sync_membership_products()
{
    if (!class_exists('WooCommerce'))
        return;

    $plans = teed_up_get_membership_plans();

    foreach ($plans as $key => $data) {
        if ($key === 'free')
            continue; // Don't need a product for free plan usually

        $product_id = get_option("teed_up_product_id_{$key}");

        if (!$product_id || !get_post($product_id)) {
            $new_product_id = wp_insert_post([
                'post_title' => "Membership: " . $data['name'],
                'post_content' => implode(', ', $data['features']),
                'post_status' => 'publish',
                'post_type' => 'product',
            ]);

            if ($new_product_id) {
                update_option("teed_up_product_id_{$key}", $new_product_id);

                // Set WC Product Data
                wp_set_object_terms($new_product_id, 'simple', 'product_type');
                update_post_meta($new_product_id, '_regular_price', $data['price']);
                update_post_meta($new_product_id, '_price', $data['price']);
                update_post_meta($new_product_id, '_virtual', 'yes');
                update_post_meta($new_product_id, '_downloadable', 'no');
                update_post_meta($new_product_id, '_membership_plan_key', $key);
            }
        }
    }
}
add_action('init', 'teed_up_sync_membership_products');

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

/**
 * Handle Profile Update from Dashboard
 */
add_action('admin_post_teed_up_update_profile', 'teed_up_handle_profile_update');
function teed_up_handle_profile_update()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_redirect(site_url('/login'));
        exit;
    }

    if (!isset($_POST['profile_nonce']) || !wp_verify_nonce($_POST['profile_nonce'], 'teed_up_update_profile')) {
        wp_redirect(site_url('/dashboard?profile_update=failed&reason=security_check'));
        exit;
    }

    $fullname = isset($_POST['fullname']) ? sanitize_text_field($_POST['fullname']) : '';
    $email = sanitize_email($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    $user = get_userdata($user_id);

    // Update Email and Name
    $user_data = [
        'ID' => $user_id,
        'user_email' => $email,
        'first_name' => $fullname
    ];

    // Password Update
    if (!empty($new_password)) {
        if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
            wp_redirect(site_url('/dashboard?profile_update=failed&reason=incorrect_password'));
            exit;
        }
        $user_data['user_pass'] = $new_password;
    }

    $updated_user_id = wp_update_user($user_data);

    if (is_wp_error($updated_user_id)) {
        wp_redirect(site_url('/dashboard?profile_update=failed&reason=' . urlencode($updated_user_id->get_error_message())));
    }
    else {
        wp_redirect(site_url('/dashboard?profile_update=success'));
    }
    exit;
}

// ============================================
// AUTO-CREATE PAGES ON THEME ACTIVATION
// ============================================

add_action('after_switch_theme', 'teed_up_create_pages');
add_action('init', 'teed_up_check_pages_created');

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

    // Mark that pages have been created and flush rules
    if ($created_count > 0 || !get_option('teed_up_pages_created')) {
        update_option('teed_up_pages_created', true);
        flush_rewrite_rules();
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

// ============================================
// MEMBERSHIP SYSTEM
// ============================================

/**
 * Define membership plans
 */
function teed_up_get_membership_plans()
{
    return [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'features' => ['Basic Tracking', 'Social Profile'],
        ],
        'scratch' => [
            'name' => 'Scratch',
            'price' => 0,
            'features' => ['Advanced Analytics', 'Unlimited Rounds', 'Priority Support'],
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 0,
            'features' => ['Coach Connectivity', 'Tournament Hosting', 'Insights+'],
        ],
    ];
}

/**
 * Get user's current plan
 */
function teed_up_get_user_plan($user_id)
{
    $plan = get_user_meta($user_id, 'membership_plan', true);
    return $plan ?: 'free';
}

/**
 * Add custom columns to User list
 */
add_filter('manage_users_columns', 'teed_up_add_user_plan_column');
function teed_up_add_user_plan_column($columns)
{
    $columns['membership_plan'] = 'Plan';
    return $columns;
}

/**
 * Display plan in User list
 */
add_filter('manage_users_custom_column', 'teed_up_show_user_plan_column', 10, 3);
function teed_up_show_user_plan_column($output, $column_name, $user_id)
{
    if ($column_name === 'membership_plan') {
        $plan_key = teed_up_get_user_plan($user_id);
        $plans = teed_up_get_membership_plans();
        $plan_name = isset($plans[$plan_key]) ? $plans[$plan_key]['name'] : 'Free';

        $color = '#666';
        if ($plan_key === 'scratch')
            $color = '#2271b1';
        if ($plan_key === 'pro')
            $color = '#d63638';

        return '<span style="color:' . $color . '; font-weight:bold;">' . esc_html($plan_name) . '</span>';
    }
    return $output;
}

/**
 * Add plan field to user profile
 */
add_action('show_user_profile', 'teed_up_user_plan_fields');
add_action('edit_user_profile', 'teed_up_user_plan_fields');
function teed_up_user_plan_fields($user)
{
    $current_plan = teed_up_get_user_plan($user->ID);
    $plans = teed_up_get_membership_plans();
?>
<h3>Membership Information</h3>
<table class="form-table">
    <tr>
        <th><label for="membership_plan">Current Plan</label></th>
        <td>
            <select name="membership_plan" id="membership_plan">
                <?php foreach ($plans as $key => $plan): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($current_plan, $key); ?>>
                    <?php echo esc_html($plan['name']); ?>
                </option>
                <?php
    endforeach; ?>
            </select>
        </td>
    </tr>
</table>
<?php
}

/**
 * Save plan field from user profile
 */
add_action('personal_options_update', 'teed_up_save_user_plan_fields');
add_action('edit_user_profile_update', 'teed_up_save_user_plan_fields');
function teed_up_save_user_plan_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    if (isset($_POST['membership_plan'])) {
        update_user_meta($user_id, 'membership_plan', sanitize_text_field($_POST['membership_plan']));
    }
}

// ============================================
// ADMIN ACCESS CONTROL
// ============================================

/**
 * Restrict wp-admin access to Administrators only
 */
add_action('admin_init', 'teed_up_restrict_admin_access');
function teed_up_restrict_admin_access()
{
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    if (!current_user_can('administrator')) {
        wp_redirect(site_url('/dashboard'));
        exit;
    }
}

/**
 * Hide admin bar for non-administrators
 */
add_filter('show_admin_bar', function($show) {
    if (!current_user_can('administrator')) {
        return false;
    }
    return $show;
});

// ============================================
// ADMIN FRIEND MANAGEMENT
// ============================================

/**
 * Register Manage Friendships admin page
 */
add_action('admin_menu', 'teed_up_register_friend_management_page');
function teed_up_register_friend_management_page()
{
    add_users_page(
        'Manage Friendships',
        'Manage Friendships',
        'administrator',
        'manage-friendships',
        'teed_up_render_friend_management_page'
    );
}

/**
 * Render Manage Friendships admin page
 */
function teed_up_render_friend_management_page()
{
    if (!current_user_can('administrator')) {
        wp_die('Unauthorized access');
    }

    // Handle Actions
    if (isset($_GET['action']) && isset($_GET['user_a']) && isset($_GET['user_b'])) {
        $action = $_GET['action'];
        $ua = intval($_GET['user_a']);
        $ub = intval($_GET['user_b']);
        $type = $_GET['meta_type'] ?? 'friends_list';

        check_admin_referer('manage_friendships_action');

        if ($action === 'delete') {
            // Remove B from A
            $list_a = get_user_meta($ua, $type, true) ?: [];
            $list_a = array_diff($list_a, [$ub]);
            update_user_meta($ua, $type, array_unique(array_values($list_a)));

            // If it was a friendship (not just a request), remove A from B too
            if ($type === 'friends_list') {
                $list_b = get_user_meta($ub, 'friends_list', true) ?: [];
                $list_b = array_diff($list_b, [$ua]);
                update_user_meta($ub, 'friends_list', array_unique(array_values($list_b)));
            } elseif ($type === 'friend_requests_sent') {
                $list_b = get_user_meta($ub, 'friend_requests_received', true) ?: [];
                $list_b = array_diff($list_b, [$ua]);
                update_user_meta($ub, 'friend_requests_received', array_unique(array_values($list_b)));
            }

            echo '<div class="notice notice-success is-dismissible"><p>Relationship removed.</p></div>';
        } elseif ($action === 'approve' && $type === 'friend_requests_sent') {
            // User A sent, User B received. Approve means both are friends.
            
            // 1. Remove from requests
            $sent_a = get_user_meta($ua, 'friend_requests_sent', true) ?: [];
            update_user_meta($ua, 'friend_requests_sent', array_unique(array_values(array_diff($sent_a, [$ub]))));

            $rec_b = get_user_meta($ub, 'friend_requests_received', true) ?: [];
            update_user_meta($ub, 'friend_requests_received', array_unique(array_values(array_diff($rec_b, [$ua]))));

            // 2. Add to friends_list
            $friends_a = get_user_meta($ua, 'friends_list', true) ?: [];
            $friends_a[] = $ub;
            update_user_meta($ua, 'friends_list', array_unique(array_values($friends_a)));

            $friends_b = get_user_meta($ub, 'friends_list', true) ?: [];
            $friends_b[] = $ua;
            update_user_meta($ub, 'friends_list', array_unique(array_values($friends_b)));

            echo '<div class="notice notice-success is-dismissible"><p>Friend request approved!</p></div>';
        }
    }

    // Get all users to build map
    $users = get_users(['fields' => ['ID', 'display_name']]);
    $user_map = [];
    foreach ($users as $u) {
        $user_map[$u->ID] = $u->display_name;
    }

    echo '<div class="wrap"><h1>Manage All Friendships</h1>';
    echo '<p>View and manage all established friendships and pending requests across the platform.</p>';

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>User A</th><th>Relationship</th><th>User B</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    foreach ($users as $u) {
        $user_id = $u->ID;
        
        // Established Friends
        $friends = get_user_meta($user_id, 'friends_list', true) ?: [];
        foreach ($friends as $fid) {
            if ($user_id < $fid) { // Only show once per pair
                echo '<tr>';
                echo '<td><strong>' . esc_html($user_map[$user_id]) . '</strong></td>';
                echo '<td><span class="dashicons dashicons-groups"></span> Friends</td>';
                echo '<td><strong>' . esc_html($user_map[$fid] ?? 'Unknown ('.$fid.')') . '</strong></td>';
                $del_url = wp_nonce_url(admin_url('users.php?page=manage-friendships&action=delete&user_a='.$user_id.'&user_b='.$fid.'&meta_type=friends_list'), 'manage_friendships_action');
                echo '<td><a href="' . $del_url . '" class="button button-link-delete" onclick="return confirm(\'Are you sure you want to end this friendship?\')">End Friendship</a></td>';
                echo '</tr>';
            }
        }

        // Pending Requests (Sent)
        $sent = get_user_meta($user_id, 'friend_requests_sent', true) ?: [];
        foreach ($sent as $sid) {
            echo '<tr>';
            echo '<td>' . esc_html($user_map[$user_id]) . '</td>';
            echo '<td><span class="dashicons dashicons-share-alt"></span> Pending Request</td>';
            echo '<td>' . esc_html($user_map[$sid] ?? 'Unknown ('.$sid.')') . '</td>';
            $del_url = wp_nonce_url(admin_url('users.php?page=manage-friendships&action=delete&user_a='.$user_id.'&user_b='.$sid.'&meta_type=friend_requests_sent'), 'manage_friendships_action');
            $app_url = wp_nonce_url(admin_url('users.php?page=manage-friendships&action=approve&user_a='.$user_id.'&user_b='.$sid.'&meta_type=friend_requests_sent'), 'manage_friendships_action');
            echo '<td>';
            echo '<a href="' . $app_url . '" class="button button-primary" style="margin-right:5px;">Approve Request</a>';
            echo '<a href="' . $del_url . '" class="button button-link-delete">Cancel Request</a>';
            echo '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table></div>';
}

/**
 * Create a new booking (pending round)
 */
function teed_up_create_booking($request)
{
    $user_id = get_current_user_id();
    $params = $request->get_json_params();

    $course_id = intval($params['course_id']);
    $date = sanitize_text_field($params['date']);
    $holes_played = intval($params['holes_played'] ?: 18);
    $invited_friends = array_map('intval', $params['invited_friends'] ?? []);

    $course_name = get_the_title($course_id);

    $round_id = wp_insert_post([
        'post_type' => 'round',
        'post_title' => 'Booking at ' . $course_name,
        'post_status' => 'publish',
        'post_author' => $user_id,
    ]);

    if (is_wp_error($round_id)) {
        return new WP_Error('booking_failed', 'Failed to create booking', ['status' => 500]);
    }

    // ACF Fields
    update_field('course', $course_id, $round_id);
    update_field('date', $date, $round_id);
    update_field('holes_played', $holes_played, $round_id);
    update_field('round_status', 'pending', $round_id);

    // Save invitations
    update_post_meta($round_id, 'invited_friends', $invited_friends);
    foreach ($invited_friends as $friend_id) {
        update_post_meta($round_id, 'booking_status_' . $friend_id, 'pending');
    }

    return [
        'success' => true,
        'round_id' => $round_id,
        'message' => 'Booking created! Invitations sent.'
    ];
}

/**
 * Get bookings for the current user (sent or received)
 */
function teed_up_get_bookings_list($request)
{
    $user_id = get_current_user_id();

    // 1. Bookings created by user
    $sent_args = [
        'post_type' => 'round',
        'author' => $user_id,
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'round_status',
                'value' => ['pending', 'confirmed'],
                'compare' => 'IN'
            ]
        ]
    ];
    $sent_query = new WP_Query($sent_args);
    $sent_bookings = [];

    foreach ($sent_query->posts as $post) {
        $invited = get_post_meta($post->ID, 'invited_friends', true) ?: [];
        $invite_details = [];
        foreach ($invited as $fid) {
            $invite_details[] = [
                'user_id' => $fid,
                'name' => get_userdata($fid)->display_name,
                'status' => get_post_meta($post->ID, 'booking_status_' . $fid, true)
            ];
        }

        $sent_bookings[] = [
            'id' => $post->ID,
            'course' => get_the_title(get_field('course', $post->ID)),
            'date' => get_field('date', $post->ID),
            'status' => get_field('round_status', $post->ID),
            'invites' => $invite_details
        ];
    }

    // 2. Bookings received by user
    $received_args = [
        'post_type' => 'round',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'invited_friends',
                'value' => $user_id,
                'compare' => 'LIKE' // Simplified for array storage or serialized
            ],
            [
                'key' => 'round_status',
                'value' => ['pending', 'confirmed'],
                'compare' => 'IN'
            ]
        ]
    ];
    
    // Actually LIKE might fail if stored as array. Better to use meta_query with value in array if supported or just filter.
    // Let's use a more robust check for invitations
    $all_pending = new WP_Query([
        'post_type' => 'round',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'round_status',
                'value' => ['pending', 'confirmed'],
                'compare' => 'IN'
            ]
        ]
    ]);

    $received_bookings = [];
    foreach ($all_pending->posts as $post) {
        if ($post->post_author == $user_id) continue;

        $invited = get_post_meta($post->ID, 'invited_friends', true) ?: [];
        if (in_array($user_id, $invited)) {
            $received_bookings[] = [
                'id' => $post->ID,
                'creator' => get_userdata($post->post_author)->display_name,
                'course' => get_the_title(get_field('course', $post->ID)),
                'date' => get_field('date', $post->ID),
                'status' => get_field('round_status', $post->ID),
                'my_status' => get_post_meta($post->ID, 'booking_status_' . $user_id, true)
            ];
        }
    }

    return [
        'success' => true,
        'sent' => $sent_bookings,
        'received' => $received_bookings
    ];
}

/**
 * Respond to a booking invitation
 */
function teed_up_respond_to_booking($request)
{
    $user_id = get_current_user_id();
    $params = $request->get_json_params();

    $round_id = intval($params['round_id']);
    $response = sanitize_text_field($params['response']); // accepted or declined

    $invited = get_post_meta($round_id, 'invited_friends', true) ?: [];
    if (!in_array($user_id, $invited)) {
        return new WP_Error('not_invited', 'You are not invited to this round.', ['status' => 403]);
    }

    update_post_meta($round_id, 'booking_status_' . $user_id, $response);

    // If all accepted (or at least one), mark round as confirmed
    if ($response === 'accepted') {
        update_field('round_status', 'confirmed', $round_id);
    }

    return [
        'success' => true,
        'message' => 'Response recorded!'
    ];
}

/**
 * Update user subscription plan
 */
function teed_up_update_subscription($request)
{
    $user_id = get_current_user_id();
    $params = $request->get_json_params();
    $plan = sanitize_text_field($params['plan']); // 'free', 'scratch', 'pro'

    if (!in_array($plan, ['free', 'scratch', 'pro'])) {
        return new WP_Error('invalid_plan', 'Invalid subscription plan', ['status' => 400]);
    }

    // Update user meta
    update_user_meta($user_id, 'subscription_plan', $plan);
    update_user_meta($user_id, 'subscription_status', 'active');
    update_user_meta($user_id, 'subscription_updated', current_time('mysql'));

    // In a real scenario, this would trigger Stripe/payment logic here

    return [
        'success' => true,
        'plan' => $plan,
        'message' => 'Subscription updated successfully to ' . ucfirst($plan) . '!'
    ];
}

/**
 * Get shared history with a friend
 */
function teed_up_get_shared_history($request)
{
    $user_id = get_current_user_id();
    $friend_id = intval($request->get_param('friend_id'));

    if (!$friend_id) {
        return new WP_Error('missing_friend', 'Friend ID required', ['status' => 400]);
    }

    // Query rounds where both users are involved
    // This is tricky because partners are stored in meta
    $args = [
        'post_type' => 'round',
        'posts_per_page' => 20,
        'post_status' => 'publish',
        'meta_query' => [
            'relation' => 'AND',
            [ // User must be author OR in partners
                'relation' => 'OR',
                ['key' => 'partners', 'value' => $user_id, 'compare' => 'LIKE'],
                ['key' => 'invited_friends', 'value' => $user_id, 'compare' => 'LIKE']
            ],
            // And friend must be involved too... implementation simplification:
            // easier to fetch user's rounds and filter in PHP for the friend
        ]
    ];
    
    // Better approach: Get ALL rounds for current user (author or participant) then filter 
    // where friend was also a participant.
    
    // 1. Rounds where I am author
    $my_rounds = get_posts([
        'post_type' => 'round',
        'author' => $user_id,
        'posts_per_page' => 50,
        'meta_key' => 'date',
        'orderby' => 'meta_value',
        'order' => 'DESC'
    ]);

    // 2. Rounds where I was a partner (requires better meta query or separate fetch)
    // For now, let's focus on rounds user logged themselves that included the friend
    
    $shared_rounds = [];

    foreach ($my_rounds as $post) {
        $partners = get_field('partners', $post->ID) ?: []; // Array of User objects or IDs
        // ACF 'partners' usually returns User Objects if formatted, or IDs. 
        // Let's assume ID array or map it.
        $partner_ids = [];
        if ($partners) {
            foreach ($partners as $p) {
                $partner_ids[] = is_object($p) ? $p->ID : intval($p);
            }
        }
        
        // Also check 'invited_friends' for bookings
        $invited = get_post_meta($post->ID, 'invited_friends', true) ?: [];
        $partner_ids = array_merge($partner_ids, $invited);

        if (in_array($friend_id, $partner_ids)) {
            $course = get_field('course', $post->ID);
            $shared_rounds[] = [
                'id' => $post->ID,
                'date' => get_field('date', $post->ID),
                'course' => get_the_title($course),
                'score' => get_field('score', $post->ID), // My score
                'round_status' => get_field('round_status', $post->ID) ?: 'completed'
            ];
        }
    }

    return ['success' => true, 'history' => $shared_rounds];
}