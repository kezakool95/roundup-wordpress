<?php get_header(); ?>

<div class="course-archive container">
    <header class="page-header center mt-2">
        <h1>Course Discovery</h1>
        <p class="text-muted">Find your next challenge in Western Australia.</p>
    </header>

    <div class="course-grid">
        <?php if (have_posts()):
    while (have_posts()):
        the_post();
        $location = get_field('location');
        $rating = get_field('rating');
        $par = get_field('par');
        $slope = get_field('slope');
        $booking_url = get_field('booking_url');
?>
        <article class="course-card glass-card h-full flex flex-col">
            <?php 
            $external_image = get_field('external_image_url');
            if (has_post_thumbnail()): ?>
            <div class="course-image">
                <?php the_post_thumbnail('medium_large'); ?>
            </div>
            <?php elseif ($external_image): ?>
            <div class="course-image">
                <img src="<?php echo esc_url($external_image); ?>" alt="<?php the_title(); ?>" />
            </div>
            <?php else: ?>
            <div class="course-image-placeholder">⛳️</div>
            <?php endif; ?>

            <div class="course-content p-2 flex-grow">
                <div class="flex-between mb-1">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                </div>
                
                <div class="course-meta">
                    <?php if ($location): ?>
                        <span class="location">📍 <?php echo esc_html($location); ?></span>
                    <?php endif; ?>
                    
                    <div class="stats-grid-small mt-2">
                        <div class="stat-pill">Par <strong><?php echo esc_html($par ?: '72'); ?></strong></div>
                        <div class="stat-pill">Slope <strong><?php echo esc_html($slope ?: '113'); ?></strong></div>
                    </div>
                </div>
            </div>

            <div class="course-actions p-1 pt-0">
                <?php if ($booking_url): ?>
                    <a href="<?php echo esc_url($booking_url); ?>" target="_blank" class="btn-primary full-width text-center">Book Tee Time ↗</a>
                <?php else: ?>
                    <a href="<?php the_permalink(); ?>" class="btn-secondary full-width text-center">View Details</a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    endwhile;
else: ?>
    <div class="no-results glass-card text-center p-4 full-width">
        <p>No courses found. Try searching or check back later!</p>
    </div>
<?php endif; ?>
    </div>
</div>

<style>
    .course-archive { padding: 2rem 0; }
    .course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem; margin-top: 2rem; }
    .course-card { overflow: hidden; height: 100%; border: 1px solid rgba(255,255,255,0.2); }
    .course-image { height: 200px; overflow: hidden; }
    .course-image img { width: 100%; height: 100%; object-fit: cover; }
    .course-image-placeholder { height: 200px; background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; opacity: 0.1; }
    
    .stats-grid-small { display: flex; gap: 0.75rem; }
    .stat-pill { background: rgba(0,0,0,0.03); padding: 0.4rem 0.75rem; border-radius: 100px; font-size: 0.85rem; }
    .stat-pill strong { color: var(--primary); }
    
    .location { display: block; font-size: 0.9rem; color: var(--text-muted); }
    .flex-between { display: flex; justify-content: space-between; align-items: center; }
</style>

<?php get_footer(); ?>