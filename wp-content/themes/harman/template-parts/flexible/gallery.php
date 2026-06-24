<?php 
  $title = !empty($section['section_title']) ? wp_kses_post($section['section_title']) : '';
  $gallery_images = !empty($section['gallery_Images']) ? $section['gallery_Images'] : [];
?>
<section class="space gallery">
  <div class="container">
    <div class="row">
      <div class="col-12 section-title">
        <?php if ($title): ?><h2><?php echo $title; ?></h2><?php endif; ?>
      </div>
    </div>

    <?php if (!empty($gallery_images)) : ?>
      <div class="row">
        <div class="col-12">
          <div class="owl-carousel gallery-slider">
            <?php foreach ($gallery_images as $image_item) :
              $image = !empty($image_item['image']) ? $image_item['image'] : '';
              if ($image):
                // If image returns an ID, use wp_get_attachment_image_url
                $image_url = is_array($image) && isset($image['url']) ? $image['url'] : (is_numeric($image) ? wp_get_attachment_image_url($image, 'full') : $image);
            ?>
              <div class="item">
                <img src="<?php echo esc_url($image_url); ?>" alt="">
              </div>
            <?php endif; endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
