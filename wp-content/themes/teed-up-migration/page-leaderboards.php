<?php
/*
 Template Name: Leaderboards
 */

get_header();
?>

<div class="leaderboards-app container" x-data="leaderboardApp()">
    <header class="page-header center mt-2 mb-2">
        <h1>🏆 Leaderboards</h1>
        <p class="text-muted">See how you rank against other golfers</p>
    </header>

    <!-- Tab Navigation -->
    <div class="tab-nav mb-2">
        <button class="tab-btn" :class="{'active': activeTab === 'global'}" @click="loadLeaderboard('global')">
            🌍 Global
        </button>
        <button class="tab-btn" :class="{'active': activeTab === 'friends'}" @click="loadLeaderboard('friends')">
            👥 Friends
        </button>
        <button class="tab-btn" :class="{'active': activeTab === 'course'}"
            @click="activeTab = 'course'; showCourseSelector = true">
            ⛳ By Course
        </button>
    </div>

    <!-- Course Selector (for course tab) -->
    <div x-show="activeTab === 'course'" class="course-selector glass-card mb-2" x-transition>
        <label>Select Course</label>
        <select x-model="selectedCourseId" @change="loadLeaderboard('course')" class="input-large">
            <option value="">Choose a course...</option>
            <template x-for="course in courses" :key="course.id">
                <option :value="course.id" x-text="course.title.rendered"></option>
            </template>
        </select>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="loading-state text-center glass-card">
        <div class="spinner"></div>
        <p>Loading rankings...</p>
    </div>

    <!-- Leaderboard Table -->
    <div x-show="!loading && leaderboard.length > 0" class="leaderboard-container glass-card">
        <template x-for="(player, index) in leaderboard" :key="player.id">
            <div class="leaderboard-row" :class="{'current-user': player.is_current_user, 'top-3': player.rank <= 3}">
                <div class="rank-badge" :class="'rank-' + player.rank">
                    <span x-text="player.rank <= 3 ? ['🥇','🥈','🥉'][player.rank-1] : player.rank"></span>
                </div>
                <img :src="player.avatar" :alt="player.name" class="player-avatar">
                <div class="player-info">
                    <strong x-text="player.name"></strong>
                    <span class="rounds-count" x-text="(player.rounds_played || 0) + ' rounds'"></span>
                </div>
                <div class="player-stat">
                    <template x-if="activeTab !== 'course'">
                        <div>
                            <span class="stat-value" x-text="player.handicap"></span>
                            <span class="stat-label">HCP</span>
                        </div>
                    </template>
                    <template x-if="activeTab === 'course'">
                        <div>
                            <span class="stat-value" x-text="player.best_score"></span>
                            <span class="stat-label">Best</span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="!loading && leaderboard.length === 0" class="empty-state text-center glass-card">
        <div class="empty-icon">🏌️</div>
        <h3 x-text="activeTab === 'friends' ? 'No friends with handicaps yet' : 'No rankings available'"></h3>
        <p class="text-muted"
            x-text="activeTab === 'friends' ? 'Add some friends and log rounds together!' : 'Start logging rounds to appear on the leaderboard.'">
        </p>
        <template x-if="activeTab === 'friends'">
            <a href="<?php echo site_url('/friends'); ?>" class="btn-primary mt-1">Find Friends</a>
        </template>
    </div>

    <!-- Your Position (if not in visible list) -->
    <div x-show="!loading && yourPosition && !leaderboard.find(p => p.is_current_user)"
        class="your-position glass-card mt-2">
        <p>Your current position: <strong x-text="'#' + yourPosition.rank"></strong> with handicap <strong
                x-text="yourPosition.handicap"></strong></p>
    </div>
</div>

<script>
    function leaderboardApp() {
        return {
            activeTab: 'global',
            loading: true,
            leaderboard: [],
            courses: [],
            selectedCourseId: '',
            showCourseSelector: false,
            yourPosition: null,

            init() {
                this.loadCourses();
                this.loadLeaderboard('global');
            },

            loadCourses() {
                fetch('/wp-json/wp/v2/course?per_page=100')
                    .then(res => res.json())
                    .then(data => this.courses = data);
            },

            loadLeaderboard(type) {
                this.activeTab = type;
                this.loading = true;

                let url = '/wp-json/teedup/v1/leaderboard?type=' + type;
                if (type === 'course' && this.selectedCourseId) {
                    url += '&course_id=' + this.selectedCourseId;
                }

                fetch(url, {
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
                })
                    .then(res => res.json())
                    .then(data => {
                        this.leaderboard = data;
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
            }
        }
    }
</script>

<style>
    .leaderboards-app {
        max-width: 700px;
        padding-bottom: 4rem;
    }

    .tab-nav {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid #ddd;
        background: white;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        border-color: var(--primary);
    }

    .tab-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .course-selector {
        padding: 1rem;
    }

    .leaderboard-container {
        padding: 0;
        overflow: hidden;
    }

    .leaderboard-row {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        gap: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
    }

    .leaderboard-row:last-child {
        border-bottom: none;
    }

    .leaderboard-row:hover {
        background: rgba(0, 0, 0, 0.02);
    }

    .leaderboard-row.current-user {
        background: linear-gradient(90deg, rgba(11, 61, 23, 0.1), transparent);
        border-left: 4px solid var(--primary);
    }

    .leaderboard-row.top-3 {
        background: linear-gradient(90deg, rgba(255, 215, 0, 0.1), transparent);
    }

    .rank-badge {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        border-radius: 50%;
        background: #f5f5f5;
    }

    .rank-1 {
        background: linear-gradient(135deg, #ffd700, #ffeb3b);
    }

    .rank-2 {
        background: linear-gradient(135deg, #c0c0c0, #e0e0e0);
    }

    .rank-3 {
        background: linear-gradient(135deg, #cd7f32, #deb887);
    }

    .player-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .player-info {
        flex-grow: 1;
    }

    .player-info strong {
        display: block;
        font-size: 1.1rem;
    }

    .rounds-count {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .player-stat {
        text-align: center;
    }

    .stat-value {
        display: block;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
    }

    .stat-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .loading-state {
        padding: 3rem;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #eee;
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .empty-state {
        padding: 3rem;
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .your-position {
        text-align: center;
        padding: 1rem;
    }
</style>

<?php get_footer(); ?>