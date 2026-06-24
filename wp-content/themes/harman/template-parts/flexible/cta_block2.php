 
<?php 
 
    $title = !empty($section['title']) ? wp_kses_post($section['title']) : '';  
    $color = !empty($section['background_color']) ? esc_attr($section['background_color']) : '';
    $leftimg = $section['left_image'];
    $leftimgurl = $section['left_image_url'];
    $button = !empty($section['button']) ? $section['button'] : [];
    $bgimg = !empty($section['background_image']) ? esc_url($section['background_image']) : '';
    $bgobject = !empty($section['background_object']) ? esc_url($section['background_object']) : '';
    $bgposition = !empty($section['background_position']) ? esc_attr($section['background_position']) : '';
    $content = !empty($section['content']) ? wp_kses_post($section['content']) : '';
?>
<section class="cta-block2 space-top " style="background-color: <?php echo $color; ?>;
background-image: url('<?php echo $bgobject; ?>');
background-repeat: no-repeat;
background-position: <?php echo $bgposition; ?>;">
  <?php if ($bgimg): ?>
    <img src="<?php echo $bgimg; ?>" class="bg-img" alt="Background Image">
  <?php endif; ?>
  <div class="container">
    <div class="row">
      <div class="col-12 cta-block2-wrapper">
        <div class="cta-block2-left">
        <?php if (!empty($left_image)): ?>
          <a data-animated="fadeInUp" href="<?= esc_url($button['url']); ?>" target="<?= esc_attr($button['target'] ?? '_self'); ?>">
            <img src="<?php echo esc_url($left_image); ?>" alt="">
        </a>
          <?php elseif (!empty($leftimgurl)): ?>
            <a data-animated="fadeInUp" href="<?= esc_url($button['url']); ?>" target="<?= esc_attr($button['target'] ?? '_self'); ?>">
            <img src="<?php echo esc_url($leftimgurl); ?>" alt="">
          </a>
          <?php endif; ?>
          
        </div>
        <div class="cta-block2-right">
          <?php if ($title): ?>
            <h3><?php echo $title; ?></h3>
          <?php endif; ?>
          <?php if ($content): ?>
            <p><?php echo $content; ?></p>
          <?php endif; ?>
          <?php if (!empty($button['url'])): ?>
            <a data-animated="fadeInUp" href="<?= esc_url($button['url']); ?>" target="<?= esc_attr($button['target'] ?? '_self'); ?>" class="btn btn-primary">
              <?= esc_html($button['title'] ?? 'Learn More'); ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
