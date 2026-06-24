<?php
  $title = !empty($section['title']) ? wp_kses_post($section['title']) : '';
  $content = !empty($section['content']) ? wp_kses_post($section['content']) : '';
  $img = !empty($section['banner_image']) ? esc_url($section['banner_image']) : '';
?>

<?php if ($title || $content || $img): ?>
<section class="hero-banner inner-banner dark-theme">   
  <div class="container">
    <div class="row">
      <div class="col-12 banner-wrapper">
        <?php if ($title || $content): ?>
        <div class="hero-banner-left">
          <?php if ($title): ?>
            <h1><?= $title; ?></h1>
          <?php endif; ?>
          <?php if ($content): ?>
            <?= $content; ?>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($img): ?>
        <div class="hero-banner-right">
          <img src="<?= $img; ?>" alt="Banner Image">
        </div>
        <?php endif; ?>
        
      </div>
    </div>
  </div>
</section>
<?php endif; ?>
