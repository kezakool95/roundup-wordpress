<?php
/**
 * The main template file
 * 
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 */

get_header(); ?>

<div class="main-content container mt-4">
    <?php if (have_posts()): ?>
    <div class="posts-grid">
        <?php while (have_posts()):
        the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('glass-card mb-2'); ?>>
            <header class="entry-header">
                <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
            </header>

            <div class="entry-content">
                <?php the_excerpt(); ?>
            </div>
        </article>
        <?php
    endwhile; ?>
    </div>

    <div class="pagination">
        <?php the_posts_navigation(); ?>
    </div>

    <?php
else: ?>
    <div class="no-results glass-card text-center p-4">
        <h2>Nothing Found</h2>
        <p>It seems we can't find what you're looking for. Perhaps searching can help.</p>
        <?php get_search_form(); ?>
    </div>
    <?php
endif; ?>
</div>

<?php get_footer(); ?>