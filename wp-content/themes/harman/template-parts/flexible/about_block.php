<?php 
  $title = !empty($section['section_title']) ? wp_kses_post($section['section_title']) : '';
  $content = !empty($section['content']) ? wp_kses_post($section['content']) : '';
  $about_img = !empty($section['about_image']) ? wp_kses_post($section['about_image']) : '';
  $bg_img = !empty($section['section_background']) ? wp_kses_post($section['section_background']) : '';
  $button = !empty($section['button']) ? $section['button'] : [];
  $stats = !empty($section['stats']) && is_array($section['stats']) ? $section['stats'] : [];
?>

<?php if ($title || $content || !empty($button['url']) || $about_img || !empty($stats)): ?>
<section class="space about-block bg-img">
  <img class="desktop-only" src="<?php echo $bg_img; ?>" alt="">
  <div class="container">
    <div class="row">
      <?php if ($title || $content || !empty($button['url'])): ?>
      <div class="col-12 col-lg-6">
        <div class="section-title">
          <?php if ($title): ?>
            <h2><?= $title; ?></h2>
          <?php endif; ?>

          <?php if ($content): ?>
            <p><?= $content; ?></p>
          <?php endif; ?>

          <?php if (!empty($button['url'])): ?>
            <a data-animated="fadeInUp" 
               href="<?= esc_url($button['url']); ?>" 
               target="<?= esc_attr($button['target'] ?? '_self'); ?>" 
               class="btn btn-primary">
              <?= esc_html($button['title'] ?? 'Learn More'); ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($about_img || !empty($stats)): ?>
      <div class="col-12 col-lg-6">
        <?php if ($about_img): ?>
          <img src="<?= esc_url($about_img); ?>" alt="">
        <?php endif; ?>

        <?php if (!empty($stats)): ?>
        <div class="all-stats">
          <?php foreach ($stats as $stat): ?>
            <?php if (!empty($stat['numbers'])): ?>
              <div class="stats-item">
                <strong><?= wp_kses_post($stat['numbers']); ?></strong>
                <p><?= !empty($stat['title']) ? wp_kses_post($stat['title']) : ''; ?></p>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>
