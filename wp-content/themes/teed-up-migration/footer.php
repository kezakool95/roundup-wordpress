</main><!-- #primary -->

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <span class="logo-text">Round Up</span>
            <p class="footer-tagline">Perfect your scheduling. Focus on your swing.</p>
        </div>

        <div class="footer-links">
            <h4>Platform</h4>
            <ul>
                <li><a href="<?php echo get_post_type_archive_link('course'); ?>">Courses</a></li>
                <li><a href="<?php echo site_url('/login'); ?>">Log In</a></li>
                <li><a href="<?php echo site_url('/signup'); ?>">Sign Up</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>&copy;
            <?php echo date('Y'); ?> Round Up. All rights reserved.
        </p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>