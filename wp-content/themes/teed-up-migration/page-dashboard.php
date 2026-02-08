<?php
/*
 Template Name: Dashboard
 */

get_header();

$user_id = get_current_user_id();
$user_info = get_userdata($user_id);
?>

<div class="dashboard-container container mt-2" x-data="dashboardApp()">
    <div class="welcome-section fade-in">
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
        $title = get_the_title();
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
    wp_reset_postdata();
else: ?>
            <p class="text-muted text-center" style="padding: 2rem;">No recent activity. <a
                    href="<?php echo site_url('/log-round'); ?>">Get started now!</a></p>
            <?php
endif; ?>
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
            showPracticeModal: false,
            submitting: false,
            practice: {
                duration: 60,
                notes: ''
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
</style>

<?php get_footer(); ?>