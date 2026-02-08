<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 */

get_header(); ?>

<div class="main-content container mt-4">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header mb-4">
                <?php the_title('<h1 class="premium-heading">', '</h1>'); ?>
            </header>

            <div class="entry-content glass-card p-4">
                <?php
                the_content();

                wp_link_pages(array(
                    'before' => '<div class="page-links">' . esc_html__('Pages:', 'teed-up'),
                    'after'  => '</div>',
                ));
                ?>
            </div>
        </article>
    <?php
    endwhile; // End of the loop.
    ?>
</div>

<?php
get_footer();
