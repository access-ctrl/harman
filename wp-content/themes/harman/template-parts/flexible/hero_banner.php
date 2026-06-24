<?php
  // Default values for section attributes
  $tagline = !empty($section['tag_line']) ? esc_html($section['tag_line']) : '';
  $title = wp_kses_post($section['title']);
  $content = !empty($section['sub_title']) ? wp_kses_post($section['sub_title']) : '';
  $banner_image = !empty($section['banner_image']) ? wp_kses_post($section['banner_image']) : '';
  $banner_image_desktop = !empty($section['banner_image_mobile']) ? wp_kses_post($section['banner_image_mobile']) : '';
  $button = !empty($section['button']) ? $section['button'] : [];
?>

<section class="hero-banner">
  <img class="banner_image" src="<?php echo $banner_image_desktop; ?>">
  <div class="container">
    <div class="row">
      <div class="col-12 banner-wrapper">
        <div class="hero-banner-left">
          <?php if ($tagline): ?>
            <h5><?= esc_html($tagline); ?></h5>
          <?php endif; ?>
          <?php if ($title): ?>
            <h1><?= $title; ?></h1>
          <?php endif; ?>
          <?php if ($content): ?>
            <p><?= $content; ?></p>
          <?php endif; ?>
          <?php if ($button): ?>
            <a data-animated="fadeInUp" href="<?= $button['url']; ?>" target="<?= $button['target']; ?>" class="btn btn-primary"><?= $button['title']; ?></a>
          <?php endif; ?>
        </div>
        <div class="hero-banner-right">
          <img src="<?php echo $banner_image; ?>" alt="">
        </div>
      </div>

      
    </div>
  </div>
</section>

