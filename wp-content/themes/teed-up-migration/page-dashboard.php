<?php
/*
 Template Name: Dashboard
 */

get_header();

$user_id = get_current_user_id();
$user_info = get_userdata($user_id);
?>

<div class="dashboard-container container mt-2" x-data="dashboardApp()">
    <!-- Alerts -->
    <?php if (isset($_GET['profile_update'])): ?>
    <div
        class="alert <?php echo $_GET['profile_update'] === 'success' ? 'alert-success' : 'alert-error'; ?> slide-down mb-2">
        <?php
    if ($_GET['profile_update'] === 'success')
        echo 'Profile updated successfully!';
    else
        echo 'Profile update failed: ' . esc_html($_GET['reason'] ?? 'Unknown error');
?>
    </div>
    <?php
endif; ?>

    <?php if (isset($_GET['registration'])): ?>
    <div class="alert alert-success slide-down mb-2">
        Welcome to Round Up! Your account has been created.
    </div>
    <?php
endif; ?>

    <div class="dashboard-nav mb-2">
        <button class="nav-tab" :class="currentTab === 'overview' ? 'active' : ''"
            @click="currentTab = 'overview'">Overview</button>
        <button class="nav-tab" :class="currentTab === 'profile' ? 'active' : ''"
            @click="currentTab = 'profile'">Profile & Settings</button>
    </div>

    <!-- Overview Tab -->
    <div x-show="currentTab === 'overview'" class="tab-content fade-in">
        <div class="welcome-section">
            <h1>Welcome back,
                <?php echo esc_html($user_info->first_name ?: $user_info->user_login); ?>! 👋
            </h1>
            <p>You're currently playing at a <strong>
                    <?php echo teed_up_get_handicap_index($user_id); ?>
                </strong> handicap.</p>
        </div>

        <!-- Quick Actions Grid -->
        <div class="quick-actions grid-2 mt-2">
            <a href="<?php echo site_url('/log-round'); ?>" class="action-card glass-card slide-up"
                style="animation-delay: 0.1s;">
                <span class="icon">⛳️</span>
                <h3>Log Round</h3>
                <p>Record a new score or book a tee time.</p>
            </a>

            <a href="<?php echo site_url('/schedule'); ?>" class="action-card glass-card slide-up"
                style="animation-delay: 0.2s;">
                <span class="icon">📅</span>
                <h3>My Schedule</h3>
                <p>Manage your availability for the week.</p>
            </a>

            <a href="<?php echo site_url('/stats'); ?>" class="action-card glass-card slide-up"
                style="animation-delay: 0.3s;">
                <span class="icon">📊</span>
                <h3>Analytics</h3>
                <p>Check your handicap trend and stats.</p>
            </a>

            <div class="action-card glass-card slide-up" style="animation-delay: 0.4s; cursor: pointer;"
                @click="showPracticeModal = true">
                <span class="icon">🏌️‍♂️</span>
                <h3>Practice</h3>
                <p>Log a driving range session.</p>
            </div>
        </div>

        <!-- Booking Requests -->
        <template x-if="bookings.received.length > 0">
            <div class="mt-4 slide-up">
                <h2 class="premium-heading mb-1">Booking Requests</h2>
                <div class="grid-2">
                    <template x-for="booking in bookings.received" :key="booking.id">
                        <div class="glass-card p-1-5 flex-between">
                            <div>
                                <h4 x-text="booking.course"></h4>
                                <p class="text-muted small">
                                    <span x-text="booking.creator"></span> invited you • <span x-text="new Date(booking.date).toLocaleDateString(undefined, {weekday:'short', month:'short', day:'numeric', hour:'numeric', minute:'numeric'})"></span>
                                </p>
                            </div>
                            <div class="flex gap-0-5" x-show="booking.my_status === 'pending'">
                                <button class="btn-primary small" @click="respondToBooking(booking.id, 'accepted')">Accept</button>
                                <button class="btn-secondary small" @click="respondToBooking(booking.id, 'declined')">Decline</button>
                            </div>
                            <div x-show="booking.my_status !== 'pending'">
                                <span class="badge" :class="booking.my_status" x-text="booking.my_status.charAt(0).toUpperCase() + booking.my_status.slice(1)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Upcoming Rounds -->
        <template x-if="bookings.sent.length > 0 || bookings.received.filter(b => b.my_status === 'accepted').length > 0">
            <div class="mt-4 slide-up">
                <h2 class="premium-heading mb-1">Upcoming Rounds</h2>
                <div class="glass-card overflow-hidden">
                    <template x-for="round in [...bookings.sent, ...bookings.received.filter(b => b.my_status === 'accepted')]" :key="round.id">
                        <div class="activity-item">
                            <div class="activity-icon">📅</div>
                            <div class="activity-details">
                                <strong x-text="round.course"></strong>
                                <span class="text-muted" x-text="new Date(round.date).toLocaleDateString(undefined, {weekday:'short', month:'short', day:'numeric', hour:'numeric', minute:'numeric'})"></span>
                            </div>
                            <div>
                                <a :href="'<?php echo site_url('/log-round'); ?>?round_id=' + round.id" class="btn-primary small">Add Scores</a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Recent Activity -->
        <div class="recent-activity mt-4 slide-up" style="animation-delay: 0.5s;">
            <div class="flex-between mb-1">
                <h2>Recent Activity</h2>
                <a href="<?php echo site_url('/rounds'); ?>" class="text-link">View All</a>
            </div>

            <div class="glass-card">
                <?php
$args = array(
    'post_type' => ['round', 'practice_session'],
    'posts_per_page' => 5,
    'author' => $user_id,
    'orderby' => 'date',
    'order' => 'DESC'
);
$activities = new WP_Query($args);

if ($activities->have_posts()):
    while ($activities->have_posts()):
        $activities->the_post();
        $type = get_post_type();
        $date = get_the_date('M j');
?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <?php echo $type === 'round' ? '🏆' : '🎯'; ?>
                    </div>
                    <div class="activity-details">
                        <strong>
                            <?php the_title(); ?>
                        </strong>
                        <span class="text-muted">
                            <?php echo $date; ?>
                        </span>
                    </div>
                    <?php if ($type === 'round'): ?>
                    <div class="activity-score">
                        <?php echo get_field('score'); ?>
                    </div>
                    <?php
        endif; ?>
                </div>
                <?php
    endwhile;
    wp_reset_postdata(); ?>
                <?php
else: ?>
                <p class="text-muted text-center" style="padding: 2rem;">No recent activity. <a
                        href="<?php echo site_url('/log-round'); ?>">Get started now!</a></p>
                <?php
endif; ?>
            </div>
        </div>

        <!-- Subscription Section -->
        <div class="subscription-section mt-4 slide-up" style="animation-delay: 0.6s;">
            <div class="flex-between mb-1">
                <h2 class="premium-heading">My Membership</h2>
            </div>
            <div class="glass-card membership-status-card">
                <?php
$plan_key = teed_up_get_user_plan($user_id);
$plans = teed_up_get_membership_plans();
$current_plan = $plans[$plan_key];
?>
                <div class="membership-info-grid">
                    <div class="membership-tier">
                        <div class="tier-icon">
                            <?php
if ($plan_key === 'free')
    echo '⛳️';
elseif ($plan_key === 'scratch')
    echo '🚀';
elseif ($plan_key === 'pro')
    echo '🏆';
?>
                        </div>
                        <div class="tier-details">
                            <span class="tier-label <?php echo $current_plan['class']; ?>">
                                <?php echo $current_plan['name']; ?> Tier
                            </span>
                            <div class="tier-status"><?php echo ucfirst($plan_status); ?> Subscription</div>
                        </div>
                    </div>
                    <div class="membership-features">
                        <ul class="benefit-list-v2">
                            <?php foreach ($current_plan['features'] as $feature): ?>
                            <li><span class="dot">•</span>
                                <?php echo esc_html($feature); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="membership-action">
                        <button class="btn-premium-outline" @click="showPlanModal = true">Upgrade / Change</button>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End Overview Tab -->

    <!-- Profile & Settings Tab -->
    <div x-show="currentTab === 'profile'" class="tab-content fade-in" style="display: none;">
        <div class="profile-settings-grid grid-2 mt-2">
            <!-- Account Details Card -->
            <div class="glass-card profile-card slide-up">
                <?php
                // Get Membership Status from Meta
                $plan_key = get_user_meta($user_id, 'subscription_plan', true) ?: 'free';
                $plan_status = get_user_meta($user_id, 'subscription_status', true) ?: 'active';
                
                // Define Plans
                $available_plans = [
                    'free' => ['name' => 'Free', 'class' => 'free', 'price' => '0', 'period' => 'forever', 'features' => ['Basic Handicap', 'Standard Booking', 'Community Access']],
                    'scratch' => ['name' => 'Scratch', 'class' => 'scratch', 'price' => '9', 'period' => '/mo', 'features' => ['Official Handicap', 'Advanced Stats', 'Priority Booking']],
                    'pro' => ['name' => 'Pro', 'class' => 'pro', 'price' => '19', 'period' => '/mo', 'features' => ['All Scratch Features', 'Pro Shop Discounts', 'Event Access']]
                ];
                $current_plan = $available_plans[$plan_key] ?? $available_plans['free'];
                ?>
                <div class="mb-2">
                    <h2 class="premium-heading">Account Settings</h2>
                    <p class="text-muted">Manage your personal information and email.</p>
                </div>

                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <input type="hidden" name="action" value="teed_up_update_profile">
                    <?php wp_nonce_field('teed_up_update_profile', 'profile_nonce'); ?>

                    <div class="form-group mb-1">
                        <label for="prof_fullname">Full Name</label>
                        <input type="text" name="fullname" id="prof_fullname" class="input-large"
                            value="<?php echo esc_attr($user_info->first_name); ?>" required>
                    </div>

                    <div class="form-group mb-2">
                        <label for="prof_email">Email Address</label>
                        <input type="email" name="email" id="prof_email" class="input-large"
                            value="<?php echo esc_attr($user_info->user_email); ?>" required>
                    </div>

                    <div class="password-change-box pt-1 border-top mt-2">
                        <h3 class="mb-1">Security</h3>
                        <p class="text-muted mb-1">Update your password (leave blank to keep current).</p>

                        <div class="form-group mb-1">
                            <label for="prof_current_password">Current Password</label>
                            <input type="password" name="current_password" id="prof_current_password"
                                class="input-large">
                        </div>

                        <div class="form-group mb-2">
                            <label for="prof_new_password">New Password</label>
                            <input type="password" name="new_password" id="prof_new_password" class="input-large">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary full-width mt-1">Save Changes</button>
                </form>
            </div>

            <!-- Profile Summary / Stats Recap -->
            <div class="profile-sidebar slide-up" style="animation-delay: 0.2s;">
                <div class="glass-card text-center mb-2">
                    <div class="user-avatar-large mb-1">
                        <div class="avatar-placeholder">
                            <?php echo strtoupper(substr($user_info->user_login, 0, 1)); ?>
                        </div>
                    </div>
                    <h3>
                        <?php echo esc_html($user_info->display_name); ?>
                    </h3>
                    <p class="text-muted">Member since
                        <?php echo date('M Y', strtotime($user_info->user_registered)); ?>
                    </p>
                </div>

                <div class="glass-card">
                    <h3>Account Type</h3>
                    <div class="membership-type-badge mt-1 <?php echo esc_attr($plan_key); ?>">
                        <span class="icon">
                            <?php
if ($plan_key === 'free')
    echo '⛳️';
elseif ($plan_key === 'scratch')
    echo '🚀';
elseif ($plan_key === 'pro')
    echo '🏆';
?>
                        </span>
                        <span>
                            <?php echo esc_html($current_plan['name']); ?> Plan
                        </span>
                    </div>
                    <p class="text-muted mt-1 small">Your membership is active and managed via WooCommerce.</p>
                    <?php if (function_exists('wc_get_page_permalink')): ?>
                    <a href="<?php echo wc_get_page_permalink('myaccount'); ?>"
                        class="btn-premium-outline full-width mt-2 text-center" style="display: block;">Manage in
                        Store</a>
                    <?php
endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Selection Modal -->
    <div class="modal-wrapper" x-show="showPlanModal" style="display: none;">
        <div class="modal-backdrop" @click="showPlanModal = false" x-transition.opacity></div>
        <div class="modal-dialog glass-card wide-modal" x-transition:enter="transition ease-out duration-300">
            <div class="modal-header">
                <h3 class="premium-heading">Elevate Your Game</h3>
                <button @click="showPlanModal = false" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="plan-options-grid">
                    <?php foreach ($available_plans as $key => $plan): 
                        $is_current = ($key === $plan_key);
                    ?>
                    <div class="plan-option-v2 <?php echo $is_current ? 'is-current' : ''; ?>" 
                         @click="<?php echo !$is_current ? "updatePlan('$key')" : ''; ?>">
                        <?php if ($is_current): ?>
                        <div class="active-tag">CURRENT PLAN</div>
                        <?php endif; ?>
                        
                        <div class="option-header">
                            <div class="option-icon"><?php echo $key === 'pro' ? '🏆' : ($key === 'scratch' ? '⛳' : '🏌️‍♂️'); ?></div>
                            <h4><?php echo $plan['name']; ?></h4>
                        </div>
                        <div class="option-price">
                            $<?php echo $plan['price']; ?><span><?php echo $plan['period']; ?></span>
                        </div>
                        <ul class="option-features">
                            <?php foreach ($plan['features'] as $feature): ?>
                            <li>
                                <?php echo esc_html($feature); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <button class="btn-select-plan <?php echo $is_current ? 'current' : ''; ?>"
                            :disabled="<?php echo $is_current ? 'true' : 'false'; ?>">
                            <?php echo $is_current ? 'Active' : 'Select ' . $plan['name']; ?>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Practice Modal -->
    <div class="modal-wrapper" x-show="showPracticeModal" style="display: none;">
        <div class="modal-backdrop" @click="showPracticeModal = false" x-transition.opacity></div>
        <div class="modal-dialog glass-card" x-transition:enter="transition ease-out duration-300">
            <div class="modal-header">
                <h3>Log Practice Session</h3>
                <button @click="showPracticeModal = false" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-1">
                    <label>Duration (minutes)</label>
                    <input type="number" x-model="practice.duration" class="input-large">
                </div>
                <div class="form-group mb-2">
                    <label>Focus / Notes</label>
                    <textarea x-model="practice.notes" placeholder="Focusing on tempo and wedge distance..."
                        class="input-large"></textarea>
                </div>
                <button class="btn-primary full-width" @click="submitPractice()" :disabled="submitting">
                    <span x-show="!submitting">Save Session</span>
                    <span x-show="submitting">Saving...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function dashboardApp() {
        return {
            currentTab: 'overview',
            showPracticeModal: false,
            showPlanModal: false,
            submitting: false,
            practice: {
                duration: 60,
                notes: ''
            },
            bookings: { sent: [], received: [] },
            
            init() {
                this.loadBookings();
            },

            loadBookings() {
                fetch('/wp-json/teedup/v1/booking/list', {
                    headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
                })
                .then(res => res.json())
                .then(data => {
                    this.bookings = data;
                });
            },

            respondToBooking(roundId, response) {
                fetch('/wp-json/teedup/v1/booking/respond', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    body: JSON.stringify({ round_id: roundId, response: response })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.loadBookings();
                    } else {
                        alert(data.message || 'Error responding to booking.');
                    }
                });
            },
            updatePlan(planKey) {
                if (!confirm('Are you sure you want to switch to the ' + planKey + ' plan?')) return;

                fetch('/wp-json/teedup/v1/subscription/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    body: JSON.stringify({ plan: planKey })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || 'Error updating plan');
                    }
                });
            },
            submitPractice() {
                this.submitting = true;
                const payload = {
                    title: 'Practice Session (' + this.practice.duration + 'm)',
                    content: this.practice.notes,
                    status: 'publish'
                };

                fetch('/wp-json/wp/v2/practice_session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(data => {
                        this.submitting = false;
                        if (data.id) {
                            this.showPracticeModal = false;
                            location.reload(); // Refresh to show new activity
                        } else {
                            alert('Error saving practice session.');
                        }
                    });
            }
        }
    }
</script>

<style>
    .dashboard-container {
        padding-bottom: 4rem;
    }

    /* Tabs Navigation */
    .dashboard-nav {
        display: flex;
        gap: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding-bottom: 0.5rem;
    }

    .nav-tab {
        background: none;
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        position: relative;
        transition: all 0.3s;
    }

    .nav-tab.active {
        color: var(--wp--preset--color--primary);
    }

    .nav-tab.active::after {
        content: '';
        position: absolute;
        bottom: -0.5rem;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--wp--preset--color--primary);
        border-radius: 3px 3px 0 0;
    }

    /* Alerts */
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    /* Profile Specific */
    .user-avatar-large {
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }

    .avatar-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--wp--preset--color--primary) 0%, #3b82f6 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 800;
        border-radius: 30px;
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
    }

    .membership-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.25rem;
        border-radius: 16px;
        font-weight: 700;
        width: 100%;
        justify-content: center;
    }

    .membership-type-badge.free {
        background: #f8fafc;
        color: #64748b;
    }

    .membership-type-badge.scratch {
        background: #eff6ff;
        color: #1e40af;
    }

    .membership-type-badge.pro {
        background: #fef2f2;
        color: #991b1b;
    }

    .border-top {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .pt-1 {
        padding-top: 1rem;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
    }

    .action-card {
        padding: 1.5rem;
        border-radius: 20px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .action-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.9);
        border-color: var(--primary);
    }

    .action-card .icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        display: block;
    }

    .action-card h3 {
        margin-bottom: 0.5rem;
    }

    .action-card p {
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 1.25rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        font-size: 1.5rem;
        margin-right: 1.25rem;
        background: var(--background);
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .activity-details {
        flex-grow: 1;
    }

    .activity-score {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
    }

    .modal-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .modal-dialog {
        position: relative;
        width: 90%;
        max-width: 500px;
        background: white;
        padding: 2.5rem;
        border-radius: 24px;
        box-shadow: var(--shadow-lg);
    }

    .modal-dialog.wide-modal {
        max-width: 1000px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-muted);
    }

    .flex-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    textarea.input-large {
        min-height: 120px;
        resize: vertical;
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid #ddd;
        width: 100%;
    }

    /* Premium Membership Dashboard Styles */
    .premium-heading {
        font-weight: 800;
        letter-spacing: -0.02em;
        background: linear-gradient(135deg, #111 0%, #444 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }

    .membership-status-card {
        padding: 2rem;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 24px;
        margin-top: 1rem;
    }

    .membership-info-grid {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        gap: 2rem;
        align-items: center;
    }

    @media (max-width: 768px) {
        .membership-info-grid {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .membership-features {
            display: inline-block;
            text-align: left;
            margin: 1rem 0;
        }
    }

    .membership-tier {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .tier-icon {
        font-size: 2.5rem;
        background: white;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }

    .tier-label {
        padding: 0.35rem 1rem;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .tier-label.free {
        background: #f1f5f9;
        color: #475569;
    }

    .tier-label.scratch {
        background: #dbeafe;
        color: #1e40af;
    }

    .tier-label.pro {
        background: #fee2e2;
        color: #991b1b;
    }

    .tier-status {
        font-size: 0.85rem;
        color: #888;
        font-weight: 500;
        margin-top: 0.25rem;
    }

    .benefit-list-v2 {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .benefit-list-v2 li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
        color: #444;
        font-weight: 500;
    }

    .benefit-list-v2 .dot {
        color: var(--wp--preset--color--primary);
        font-weight: 900;
    }

    .btn-premium-outline {
        background: transparent;
        border: 2px solid var(--wp--preset--color--primary);
        color: var(--wp--preset--color--primary);
        padding: 0.8rem 1.5rem;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
    }

    .btn-premium-outline:hover {
        background: var(--wp--preset--color--primary);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    /* Modal Grid */
    .plan-options-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    @media (max-width: 992px) {
        .plan-options-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .plan-options-grid {
            grid-template-columns: 1fr;
        }
        
        .modal-dialog.wide-modal {
            padding: 1.5rem;
            max-height: 90vh;
            overflow-y: auto;
        }
    }

    .plan-option-v2 {
        background: #f8fafc;
        border: 2px solid transparent;
        border-radius: 20px;
        padding: 1.75rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .plan-option-v2:hover {
        background: white;
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
    }

    .plan-option-v2.is-current {
        border-color: var(--wp--preset--color--primary);
        background: white;
    }

    .active-tag {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--wp--preset--color--primary);
        color: white;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 0.25rem 0.75rem;
        border-radius: 10px;
        z-index: 10;
    }

    .option-header h4 {
        font-size: 1.25rem;
        margin: 0.5rem 0;
        font-weight: 800;
        color: #111;
    }

    .option-icon {
        font-size: 2rem;
    }

    .option-price {
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 1rem;
        color: #111;
    }

    .option-price span {
        font-size: 0.9rem;
        color: #888;
        font-weight: 500;
    }

    .option-features {
        list-style: none;
        padding: 0;
        margin: 1.5rem 0;
        text-align: left;
        font-size: 0.9rem;
        min-height: 120px;
    }

    .option-features li {
        margin-bottom: 0.5rem;
        padding-left: 1.25rem;
        position: relative;
        color: #444;
    }

    .option-features li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: #10b981;
        font-weight: 900;
    }

    .btn-select-plan {
        width: 100%;
        padding: 0.75rem;
        border-radius: 12px;
        border: none;
        background: #e2e8f0;
        color: #64748b;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .plan-option-v2:not(.is-current) .btn-select-plan {
        background: var(--wp--preset--color--primary);
        color: white;
    }

    .btn-select-plan.current {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: default;
    }

    .plan-option-v2:not(.is-current):hover .btn-select-plan {
        filter: brightness(1.1);
    }
    .p-1-5 {
        padding: 1.5rem;
    }

    .gap-0-5 {
        gap: 0.5rem;
    }

    .flex {
        display: flex;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge.accepted {
        background: #ecfdf5;
        color: #059669;
    }

    .badge.declined {
        background: #fef2f2;
        color: #dc2626;
    }

    .badge.pending {
        background: #fffbeb;
        color: #d97706;
    }
</style>

<?php get_footer(); ?>