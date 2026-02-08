<?php
/*
 Template Name: Stats Dashboard
 */

get_header();

$user_id = get_current_user_id();
$handicap = teed_up_get_handicap_index($user_id);
$handicap_history = get_user_meta($user_id, 'handicap_history', true) ?: [];

// Get rounds data
$rounds_query = new WP_Query([
    'post_type' => 'round',
    'author' => $user_id,
    'posts_per_page' => -1,
    'meta_key' => 'date',
    'orderby' => 'meta_value',
    'order' => 'DESC'
]);

$rounds_played = $rounds_query->found_posts;
$total_score = 0;
$scores_array = [];
$labels_array = [];
$best_round = null;
$worst_round = null;
$course_ids = [];

if ($rounds_query->have_posts()) {
    while ($rounds_query->have_posts()) {
        $rounds_query->the_post();
        $s = get_field('score');
        $d = get_field('date');
        $c = get_field('course');

        if ($s) {
            $total_score += $s;
            $scores_array[] = $s;
            $labels_array[] = $d ? date('M d', strtotime($d)) : get_the_date('M d');

            if (!$best_round || $s < $best_round['score']) {
                $best_round = ['score' => $s, 'course' => get_the_title($c), 'date' => $d];
            }
            if (!$worst_round || $s > $worst_round['score']) {
                $worst_round = ['score' => $s, 'course' => get_the_title($c), 'date' => $d];
            }

            if ($c && !in_array($c, $course_ids))
                $course_ids[] = $c;
        }
    }
    wp_reset_postdata();
}

$avg_score = $rounds_played > 0 ? round($total_score / $rounds_played, 1) : '-';
$courses_played = count($course_ids);

// Reverse for chart (oldest to newest)
$scores_array = array_reverse($scores_array);
$labels_array = array_reverse($labels_array);

// Handicap history for trend chart
$hcp_dates = array_map(fn($h) => $h['date'] ?? '', $handicap_history);
$hcp_values = array_map(fn($h) => floatval($h['handicap'] ?? 0), $handicap_history);
?>

<div class="stats-dashboard-app container" x-data="statsApp()">
    <header class="page-header center mt-2">
        <h1>📊 Performance Analytics</h1>
        <p class="text-muted">Track your progress and identify areas for improvement</p>
    </header>

    <!-- Quick Stats Row -->
    <div class="stats-grid-compact mt-2">
        <div class="stat-mini glass-card">
            <span class="stat-icon">🎯</span>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo esc_html($handicap); ?>
                </span>
                <span class="stat-label">Handicap</span>
            </div>
        </div>
        <div class="stat-mini glass-card">
            <span class="stat-icon">🏌️</span>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo $rounds_played; ?>
                </span>
                <span class="stat-label">Rounds</span>
            </div>
        </div>
        <div class="stat-mini glass-card">
            <span class="stat-icon">📈</span>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo esc_html($avg_score); ?>
                </span>
                <span class="stat-label">Avg Score</span>
            </div>
        </div>
        <div class="stat-mini glass-card">
            <span class="stat-icon">⛳</span>
            <div class="stat-content">
                <span class="stat-value">
                    <?php echo $courses_played; ?>
                </span>
                <span class="stat-label">Courses</span>
            </div>
        </div>
    </div>

    <!-- Best/Worst Rounds -->
    <?php if ($best_round): ?>
    <div class="highlight-row mt-2">
        <div class="highlight-card glass-card best">
            <span class="highlight-icon">🏆</span>
            <div class="highlight-content">
                <strong>Best Round</strong>
                <span class="highlight-score">
                    <?php echo $best_round['score']; ?>
                </span>
                <span class="highlight-detail">
                    <?php echo esc_html($best_round['course']); ?>
                </span>
            </div>
        </div>
        <?php if ($worst_round): ?>
        <div class="highlight-card glass-card worst">
            <span class="highlight-icon">📉</span>
            <div class="highlight-content">
                <strong>Worst Round</strong>
                <span class="highlight-score">
                    <?php echo $worst_round['score']; ?>
                </span>
                <span class="highlight-detail">
                    <?php echo esc_html($worst_round['course']); ?>
                </span>
            </div>
        </div>
        <?php
    endif; ?>
    </div>
    <?php
endif; ?>

    <!-- Charts Section -->
    <div class="charts-section mt-3">
        <div class="chart-card glass-card">
            <h3>Scoring Trend</h3>
            <canvas id="scoringChart"></canvas>
            <?php if (empty($scores_array)): ?>
            <div class="no-data">
                <p>No rounds logged yet. <a href="<?php echo site_url('/log-round'); ?>">Log your first round!</a></p>
            </div>
            <?php
endif; ?>
        </div>

        <div class="chart-card glass-card">
            <h3>Handicap History</h3>
            <canvas id="handicapChart"></canvas>
            <?php if (empty($handicap_history)): ?>
            <div class="no-data">
                <p>Log more rounds to see your handicap trend</p>
            </div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Club Distances Section -->
    <div class="club-distances-section mt-3 glass-card">
        <div class="section-header">
            <h3>🏌️ Club Distances</h3>
            <button class="btn-secondary small" @click="editingClubs = !editingClubs"
                x-text="editingClubs ? 'Done' : 'Edit'"></button>
        </div>

        <div class="club-grid">
            <template x-for="(club, index) in clubDistances" :key="index">
                <div class="club-item">
                    <span class="club-name" x-text="club.club"></span>
                    <template x-if="!editingClubs">
                        <span class="club-distance" x-text="club.distance + 'm'"></span>
                    </template>
                    <template x-if="editingClubs">
                        <input type="number" x-model.number="club.distance" class="club-input"
                            @change="saveClubDistances()">
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="quick-links mt-3">
        <a href="<?php echo site_url('/log-round'); ?>" class="btn-primary">Log New Round</a>
        <a href="<?php echo site_url('/leaderboards'); ?>" class="btn-secondary">View Leaderboards</a>
    </div>
</div>

<script>
    function statsApp() {
        return {
            clubDistances: [],
            editingClubs: false,

            init() {
                this.loadClubDistances();
                this.initCharts();
            },

            loadClubDistances() {
                fetch('/wp-json/teedup/v1/club-distances', {
                    headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
                })
                    .then(res => res.json())
                    .then(data => this.clubDistances = data);
            },

            saveClubDistances() {
                fetch('/wp-json/teedup/v1/club-distances', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                    body: JSON.stringify({ distances: this.clubDistances })
                });
            },

            initCharts() {
                // Scoring Chart
                const scoringCtx = document.getElementById('scoringChart');
                if (scoringCtx) {
                    const scoresData = <?php echo json_encode($scores_array); ?>;
                    const labelsData = <?php echo json_encode($labels_array); ?>;

                    if (scoresData.length > 0) {
                        new Chart(scoringCtx, {
                            type: 'line',
                            data: {
                                labels: labelsData,
                                datasets: [{
                                    label: 'Score',
                                    data: scoresData,
                                    borderColor: '#0B3D17',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    backgroundColor: 'rgba(11, 61, 23, 0.1)',
                                    pointBackgroundColor: '#0B3D17',
                                    pointRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        grid: { color: 'rgba(0,0,0,0.05)' }
                                    },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    }
                }

                // Handicap Chart
                const hcpCtx = document.getElementById('handicapChart');
                if (hcpCtx) {
                    const hcpDates = <?php echo json_encode($hcp_dates); ?>;
                    const hcpValues = <?php echo json_encode($hcp_values); ?>;

                    if (hcpValues.length > 0) {
                        new Chart(hcpCtx, {
                            type: 'line',
                            data: {
                                labels: hcpDates,
                                datasets: [{
                                    label: 'Handicap',
                                    data: hcpValues,
                                    borderColor: '#ff9800',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    backgroundColor: 'rgba(255, 152, 0, 0.1)',
                                    pointBackgroundColor: '#ff9800',
                                    pointRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        reverse: true, // Lower handicap = better
                                        grid: { color: 'rgba(0,0,0,0.05)' }
                                    },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    }
                }
            }
        }
    }
</script>

<style>
    .stats-dashboard-app {
        padding: 2rem 0;
        max-width: 900px;
    }

    .stats-grid-compact {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .stats-grid-compact {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stat-mini {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
    }

    .stat-icon {
        font-size: 2rem;
    }

    .stat-content {
        display: flex;
        flex-direction: column;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
    }

    .stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .highlight-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .highlight-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
    }

    .highlight-card.best {
        border-left: 4px solid #4caf50;
    }

    .highlight-card.worst {
        border-left: 4px solid #ff9800;
    }

    .highlight-icon {
        font-size: 2rem;
    }

    .highlight-content {
        display: flex;
        flex-direction: column;
    }

    .highlight-score {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
    }

    .highlight-detail {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .chart-card {
        padding: 1.5rem;
    }

    .chart-card h3 {
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .no-data {
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
    }

    .club-distances-section {
        padding: 1.5rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .club-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.75rem;
    }

    .club-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.75rem;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 12px;
    }

    .club-name {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 0.25rem;
    }

    .club-distance {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
    }

    .club-input {
        width: 60px;
        text-align: center;
        padding: 0.25rem;
        border: 1px solid var(--primary);
        border-radius: 6px;
        font-weight: 600;
    }

    .quick-links {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
</style>

<?php get_footer(); ?>