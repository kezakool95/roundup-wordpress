<?php
/*
 Template Name: Friend Finder
 */

get_header();

// Handle "Add Friend" action (Mock)
if (isset($_GET['add_friend'])) {
    $friend_id = intval($_GET['add_friend']);
    // In a real app, we'd add to a 'friends' meta array.
    // For Alpha, we'll just show a success message.
    $success_msg = "Friend request sent!";
}

$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
?>

<div class="friends-container container" x-data="{ search: '<?php echo esc_js($search); ?>' }">
    <header class="page-header center mt-2 mb-2">
        <h1>Find Golf Partners</h1>
        <p class="text-muted">Connect with other players to schedule rounds.</p>
    </header>

    <?php if (isset($success_msg)): ?>
    <div class="alert-success glass-card mb-2 text-center">
        ✅
        <?php echo $success_msg; ?>
    </div>
    <?php
endif; ?>

    <!-- Search Bar -->
    <form action="" method="get" class="friend-search mb-2">
        <input type="text" name="s" placeholder="Search by name..." value="<?php echo esc_attr($search); ?>">
        <button type="submit" class="btn-primary">Search</button>
    </form>

    <!-- Results Grid -->
    <div class="friends-grid grid-2">
        <?php
$args = array(
    'role' => 'subscriber',
    'orderby' => 'user_nicename',
    'order' => 'ASC',
    'number' => 20,
    'search' => '*' . $search . '*'
);
$user_query = new WP_User_Query($args);

if (!empty($user_query->get_results())) {
    foreach ($user_query->get_results() as $user) {
        // Skip current user
        if ($user->ID === get_current_user_id())
            continue;

        $avatar = get_avatar_url($user->ID);
        $name = $user->display_name;
        $handicap = get_field('current_handicap', 'user_' . $user->ID) ?: 'NH';
?>
        <div class="friend-card glass-card slide-up">
            <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($name); ?>" class="friend-avatar">
            <div class="friend-info">
                <h3>
                    <?php echo esc_html($name); ?>
                </h3>
                <p class="text-muted">Handicap: <strong>
                        <?php echo esc_html($handicap); ?>
                    </strong></p>
                <a href="?add_friend=<?php echo $user->ID; ?>" class="btn-secondary small">Add Friend</a>
            </div>
        </div>
        <?php
    }
}
else {
    echo '<p class="text-center text-muted col-span-2">No golfers found.</p>';
}
?>
    </div>
</div>

<style>
    .friends-container {
        max-width: 800px;
        padding-bottom: 4rem;
    }

    .friend-search {
        display: flex;
        gap: 1rem;
    }

    .friend-search input {
        flex-grow: 1;
    }

    .friend-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .friend-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary);
    }

    .friend-info {
        flex-grow: 1;
    }

    .friend-info h3 {
        margin-bottom: 0.2rem;
        font-size: 1.2rem;
    }

    .friend-info p {
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .alert-success {
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
        padding: 1rem;
    }
</style>

<?php get_footer(); ?>