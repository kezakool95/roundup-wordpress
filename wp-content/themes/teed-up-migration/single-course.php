<?php get_header(); ?>

<div class="single-course-container">
    <?php while (have_posts()):
    the_post();
    $course_id = get_the_ID();
    $location = get_field('location');
    $rating = get_field('rating');
    $par = get_field('par');
    $slope = get_field('slope');
    $holes = get_field('holes');
    $booking_url = get_field('booking_url');
    $external_image = get_field('external_image_url');
    $image_source = get_field('image_source_url');
    $pars_per_hole = get_field('pars_per_hole');
?>

    <div class="course-hero">
        <?php
    $hero_style = "";
    if (has_post_thumbnail()) {
        $hero_style = 'style="background-image: url(\'' . get_the_post_thumbnail_url($course_id, 'full') . '\');"';
    }
    elseif ($external_image) {
        $hero_style = 'style="background-image: url(\'' . esc_url($external_image) . '\');"';
    }
    else {
        $hero_style = 'style="background-color: var(--primary);"';
    }
?>
        <div class="hero-bg" <?php echo $hero_style; ?>></div>

        <div class="container hero-content center pt-4 pb-4">
            <?php if ($image_source): ?>
            <a href="<?php echo esc_url($image_source); ?>" target="_blank" class="image-source-link">Image Source ↗</a>
            <?php
    endif; ?>
            <span class="badge mb-1">WA Premiere Course</span>
            <h1>
                <?php the_title(); ?>
            </h1>
            <p class="location">📍
                <?php echo esc_html($location); ?>
            </p>
        </div>
    </div>

    <div class="container course-body mt-2">
        <div class="course-main-grid">
            <div class="main-column">
                <div class="description-card glass-card mb-2">
                    <h2>Course Overview</h2>
                    <div class="description-content">
                        <?php if (get_the_content()): ?>
                        <?php the_content(); ?>
                        <?php
    else: ?>
                        <p>Discover the unique challenges and breathtaking landscapes of
                            <?php the_title(); ?>. This course offers a premium experience for golfers of all skill
                            levels, featuring meticulously maintained fairways and greens.
                        </p>
                        <?php
    endif; ?>
                    </div>
                </div>

                <!-- Scorecard Table -->
                <div class="scorecard-card glass-card mb-2">
                    <div class="flex-between mb-1">
                        <h2>Official Scorecard</h2>
                        <div class="total-par-badge">Total Par:
                            <?php echo esc_html($par ?: '72'); ?>
                        </div>
                    </div>

                    <?php if (is_array($pars_per_hole) && !empty($pars_per_hole)): ?>
                    <div class="scorecard-scroll">
                        <table class="scorecard-table">
                            <thead>
                                <tr>
                                    <th>Hole</th>
                                    <?php for ($i = 1; $i <= 18; $i++): ?>
                                    <th>
                                        <?php echo $i; ?>
                                    </th>
                                    <?php
        endfor; ?>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Par</strong></td>
                                    <?php
        $calculated_par = 0;
        foreach ($pars_per_hole as $h):
            // Handle both array and object if ACF behaves weirdly
            $p = is_array($h) ? $h['par'] : (isset($h->par) ? $h->par : 0);
            $calculated_par += intval($p);
?>
                                    <td>
                                        <?php echo esc_html($p); ?>
                                    </td>
                                    <?php
        endforeach; ?>
                                    <!-- Fill empty holes if less than 18 -->
                                    <?php for ($i = count($pars_per_hole); $i < 18; $i++): ?>
                                    <td>-</td>
                                    <?php
        endfor; ?>
                                    <td><strong>
                                            <?php echo $calculated_par; ?>
                                        </strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php
    else: ?>
                    <p class="text-muted italic">Scorecard data coming soon for this course.</p>
                    <?php
    endif; ?>
                </div>
            </div>

            <div class="sidebar-column">
                <div class="booking-card glass-card text-center sticky-sidebar">
                    <h3>Book Your Round</h3>
                    <div class="large-stats grid-2 mb-2 mt-1">
                        <div class="stat-item">
                            <span class="label">Course Rating</span>
                            <span class="value">
                                <?php echo esc_html($rating ?: '72.0'); ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="label">Slope Rating</span>
                            <span class="value">
                                <?php echo esc_html($slope ?: '113'); ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($booking_url): ?>
                    <a href="<?php echo esc_url($booking_url); ?>" target="_blank"
                        class="btn-primary full-width mb-1">Book Online ↗</a>
                    <?php
    else: ?>
                    <button class="btn-primary full-width mb-1 opacity-50">Booking Unavailable</button>
                    <?php
    endif; ?>

                    <p class="text-muted small">Prices vary based on tee time and day of week.</p>
                </div>
            </div>
        </div>
    </div>

    <?php
endwhile; ?>
</div>

<style>
    .course-hero {
        position: relative;
        color: white;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        filter: brightness(0.3);
        z-index: -1;
    }

    .badge {
        background: var(--primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .image-source-link {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 100px;
        font-size: 0.75rem;
        text-decoration: none;
        backdrop-filter: blur(10px);
        transition: all 0.2s ease;
        z-index: 10;
    }

    .image-source-link:hover {
        background: rgba(0, 0, 0, 0.8);
        transform: translateY(-2px);
    }

    .course-main-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 2rem;
        align-items: start;
    }

    .sticky-sidebar {
        position: sticky;
        top: 100px;
    }

    .scorecard-scroll {
        overflow-x: auto;
        margin-top: 1rem;
    }

    .scorecard-table {
        width: 100%;
        border-collapse: collapse;
        text-align: center;
        font-size: 0.9rem;
    }

    .scorecard-table th,
    .scorecard-table td {
        padding: 0.75rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .scorecard-table th {
        background: rgba(0, 0, 0, 0.02);
        font-weight: 700;
    }

    .scorecard-table td:first-child {
        background: rgba(0, 0, 0, 0.02);
        font-weight: 700;
    }

    .total-par-badge {
        background: var(--primary);
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-weight: 700;
    }

    .large-stats {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding-bottom: 1rem;
    }

    .stat-item .label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 700;
    }

    .stat-item .value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
    }

    @media (max-width: 900px) {
        .course-main-grid {
            grid-template-columns: 1fr;
        }

        .sticky-sidebar {
            position: static;
        }
    }
</style>

<?php get_footer(); ?>