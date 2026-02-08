<?php
/*
 Template Name: Stats Dashboard
 */

get_header();

$user_id = get_current_user_id();
$handicap = teed_up_get_handicap_index($user_id);

// Get counts
$rounds_query = new WP_Query([
    'post_type' => 'round',
    'author' => $user_id,
    'posts_per_page' => -1
]);
$rounds_played = $rounds_query->found_posts;

// Calculate average
$total_score = 0;
$scores_array = [];
$labels_array = [];

if ($rounds_query->have_posts()) {
    while ($rounds_query->have_posts()) {
        $rounds_query->the_post();
        $s = get_field('score');
        $total_score += $s;
        $scores_array[] = $s;
        $labels_array[] = get_field('date') ? date('M d', strtotime(get_field('date'))) : get_the_date('M d');
    }
    wp_reset_postdata();
}

$avg_score = $rounds_played > 0 ? round($total_score / $rounds_played, 1) : '-';

// Reverse for chart (left to right = oldest to newest)
$scores_array = array_reverse($scores_array);
$labels_array = array_reverse($labels_array);
?>

<div class="stats-dashboard-app container">
    <header class="page-header center mt-2">
        <h1>Performance Analytics</h1>
        <p class="text-muted">Tracking your progress over time.</p>
    </header>

    <div class="stats-grid">
        <div class="stat-block glass-card text-center">
            <h3>Handicap Index</h3>
            <div class="big-stat">
                <?php echo esc_html($handicap); ?>
            </div>
            <p class="trend">USGA Verified Formula</p>
        </div>

        <div class="stat-block glass-card text-center">
            <h3>Scoring Average</h3>
            <div class="big-stat">
                <?php echo esc_html($avg_score); ?>
            </div>
            <p class="trend">Based on
                <?php echo $rounds_played; ?> rounds
            </p>
        </div>

        <div class="stat-block full-width glass-card">
            <h3>Scoring Trend</h3>
            <canvas id="scoringChart"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('scoringChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels_array); ?>,
            datasets: [{
                label: 'Score',
                data: <?php echo json_encode($scores_array); ?>,
                borderColor: '#0B3D17',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(11, 61, 23, 0.05)',
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
                suggestedMin: 70,
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: { grid: { display: false } }
        }
    }
        });
    });
</script>

<style>
    .stats-dashboard-app {
        padding: 2rem 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .big-stat {
        font-size: 3rem;
        font-weight: 800;
        color: var(--wp--preset--color--primary);
    }

    .trend {
        color: var(--wp--preset--color--text-muted);
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--wp--preset--shadow);
    }
</style>

<?php get_footer(); ?>