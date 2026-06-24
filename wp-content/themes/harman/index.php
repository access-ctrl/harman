<?php
  get_header();
?>

<section class="hero-banner inner-banner">

<section class="space light-bg">
  <div class="container">
    <?php if (have_posts()) : ?>
      <div class="row">
        <div class="col-12 feature-blocks large-imgs grid-2">
          <?php
          while (have_posts()) : the_post(); ?>
            <div id="post-<?php the_ID(); ?>" <?php post_class('feature-block'); ?>>
              <div class="main-img">
                <a href="<?php the_permalink(); ?>">
                  <?php if (has_post_thumbnail()) : ?>
                    <img src="<?= get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>" alt="<?= esc_attr(get_the_title()); ?>">
                  <?php else : ?>
                    <img src="<?= esc_url(get_template_directory_uri() . '/assets/images/noimage.jpg'); ?>" alt="<?= esc_attr(get_the_title()); ?>">
                  <?php endif; ?>
                </a>
              </div>
              <h3><a href="<?php the_permalink(); ?>"><?= get_the_title(); ?></a></h3>
              <p><?= get_the_excerpt(); ?></p>
            </div>
          <?php endwhile; ?>
        </div>
      </div>

      <!-- Pagination -->
      <div class="row">
        <div class="col-12 pagination">
          <?=
            paginate_links([
              'prev_text' => __('&laquo; Previous', 'harman-wp'),
              'next_text' => __('Next &raquo;', 'harman-wp'),
            ]);
          ?>
        </div>
      </div>

    <?php else : ?>
      <div class="no-post-found">
        <p><?php esc_html_e('Sorry, no posts found.', 'harman-wp'); ?></p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php
get_footer();
?>
