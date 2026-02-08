<?php get_header(); ?>

<div class="rounds-archive container">
    <header class="page-header center mt-2 mb-2">
        <h1>Your Round History</h1>
        <p class="text-muted">A complete log of your golfing journey.</p>
    </header>

    <div class="rounds-list">
        <?php if (have_posts()):
    while (have_posts()):
        the_post();
        $course_id = get_field('course');
        $course = get_post($course_id);
        $date = get_field('date');
        $score = get_field('score');
        $holes = get_field('holes_played') ?: 18;
?>
        <article class="round-card glass-card slide-up">
            <div class="round-date">
                <span class="day">
                    <?php echo date('d', strtotime($date)); ?>
                </span>
                <span class="month">
                    <?php echo date('M', strtotime($date)); ?>
                </span>
            </div>

            <div class="round-details">
                <h3>
                    <?php echo esc_html($course->post_title); ?>
                </h3>
                <div class="meta">
                    <span>
                        <?php echo esc_html($holes); ?> Holes
                    </span>
                    <?php if ($score): ?>
                    <span class="separator">•</span>
                    <span>Score: <strong>
                            <?php echo esc_html($score); ?>
                        </strong></span>
                    <?php
        endif; ?>
                </div>
            </div>

            <div class="round-actions">
                <a href="<?php the_permalink(); ?>" class="btn-secondary small">View Scorecard</a>
            </div>
        </article>
        <?php
    endwhile;
else: ?>
        <div class="empty-state glass-card text-center">
            <p>No rounds found.</p>
            <a href="<?php echo site_url('/log-round'); ?>" class="btn-primary">Log Your First Round</a>
        </div>
        <?php
endif; ?>
    </div>
</div>

<style>
    .rounds-archive {
        max-width: 800px;
        padding-bottom: 4rem;
    }

    .rounds-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .round-card {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        gap: 1.5rem;
    }

    .round-date {
        display: flex;
        flex-direction: column;
        align-items: center;
        background: var(--primary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        min-width: 70px;
    }

    .round-date .day {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .round-date .month {
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 500;
    }

    .round-details {
        flex-grow: 1;
    }

    .round-details h3 {
        margin-bottom: 0.3rem;
        font-size: 1.2rem;
    }

    .round-details .meta {
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .separator {
        margin: 0 0.5rem;
    }

    @media (max-width: 600px) {
        .round-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .round-date {
            flex-direction: row;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
            padding: 0.3rem;
        }

        .round-actions {
            width: 100%;
        }

        .round-actions .btn-secondary {
            width: 100%;
            text-align: center;
        }
    }
</style>

<?php get_footer(); ?>