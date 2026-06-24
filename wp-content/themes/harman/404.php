<?php get_header(); ?>

<section class="space light-bg">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="no-post-found text-center">
          <h2><?php esc_html_e('404 - Page Not Found', 'harman-wp'); ?></h2>
          <p><?php esc_html_e('Sorry, the page you are looking for does not exist.', 'harman-wp'); ?></p>
          <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
            <?php esc_html_e('Return to Home', 'harman-wp'); ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
get_footer();
?>
``
