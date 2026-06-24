<?php 
  $title = !empty($section['title']) ? wp_kses_post($section['title']) : '';  
  $color = !empty($section['background_color']) ? wp_kses_post($section['background_color']) : '';
  $bgimg = !empty($section['background_image']) ? wp_kses_post($section['background_image']) : '';
  $table_1_video = !empty($section['table_1_animated_video']) ? wp_kses_post($section['table_1_animated_video']) : '';
  $bgposition = !empty($section['background_position']) ? wp_kses_post($section['background_position']) : '';
  $content = !empty($section['content']) ? wp_kses_post($section['content']) : '';

  $table_1_title = !empty($section['table_1_title']) ? wp_kses_post($section['table_1_title']) : '';
  $table_1_description = !empty($section['table_1_description']) ? wp_kses_post($section['table_1_description']) : '';
  $table_1_icon = !empty($section['table_1_icon']) ? wp_kses_post($section['table_1_icon']) : '';
  $button1 = !empty($section['table_1_button']) ? $section['table_1_button'] : [];
  $link = !empty($section['link']) ? $section['link'] : []; 

  $table_2_title = !empty($section['table_2_title']) ? wp_kses_post($section['table_2_title']) : '';
  $table_2_description = !empty($section['table_2_description']) ? wp_kses_post($section['table_2_description']) : '';
  $table_2_video = !empty($section['table_2_animated_video']) ? wp_kses_post($section['table_2_animated_video']) : '';
  $table_2_icon = !empty($section['table_2_icon']) ? wp_kses_post($section['table_2_icon']) : '';
  $button2 = !empty($section['table_2_button']) ? $section['table_2_button'] : [];
  $link2 = !empty($section['link2']) ? $section['link2'] : []; 
?>
<section class="cta-block light-scheme space" style="background-color: <?php echo $color; ?>; 
background-image:url(' <?php echo $bgimg; ?>');
background-repeat: no-repeat;
background-position: <?php echo $bgposition; ?>;
">
 
 <div class="container">
  <div class="row">
    <div class="col-12 section-title">
      <?php if (!empty($title)): ?>
        <h2><?php echo wp_kses_post($title); ?></h2>
      <?php endif; ?>

      <?php if (!empty($content)): ?>
        <p><?php echo wp_kses_post($content); ?></p>
      <?php endif; ?>
   
      
    </div>
  </div>
  <div class="row">
      <div class="col-12 col-lg-6">
        <?php if($link['url']) :?>
          <a data-animated="fadeInUp" href="<?= esc_url($link['url']); ?>" target="<?= esc_attr($link['target'] ?? '_self'); ?>">                        
        <?php endif; ?>
          <div class="feature-card">
            <div class="feature-card-left">
              <?php if ($table_1_title): ?><h3><?php echo $table_1_title; ?></h3><?php endif; ?>
              <?php if ($table_1_description): ?><p><?php echo $table_1_description; ?></p><?php endif; ?>
            </div>
            <?php if ($table_1_icon): ?>
                <div class="comp-table-right">
                  <?php if ($table_1_video) { ?>
                    <div class="video-outer">                    
                      <video autoplay loop muted playsinline>
                        <source src="<?php echo esc_url($table_1_video); ?>" type="video/mp4"> 
                      </video>
                    </div>
                  <?php } elseif ($table_1_icon) { ?>
                    <img src="<?php echo esc_url($table_1_icon); ?>" alt="">
                  <?php } ?>
                </div>
              <?php endif; ?>
          </div>
        <?php if($link['url']) :?>
          </a>
        <?php endif; ?>
      </div>
      <div class="col-12 col-lg-6">
        <?php if($link2['url']) :?>
          <a data-animated="fadeInUp" href="<?= esc_url($link2['url']); ?>" target="<?= esc_attr($link2['target'] ?? '_self'); ?>">                        
        <?php endif; ?>
        <div class="feature-card">
          <div class="feature-card-left">
            <?php if ($table_2_title): ?><h3><?php echo $table_2_title; ?></h3><?php endif; ?>
            <?php if ($table_2_description): ?><p><?php echo $table_2_description; ?></p><?php endif; ?>
          </div>
          <?php if ($table_2_icon): ?>
              <div class="comp-table-right">
                <?php if ($table_2_video) { ?>
                  <div class="video-outer">                    
                    <video autoplay loop muted playsinline>
                      <source src="<?php echo esc_url($table_2_video); ?>" type="video/mp4"> 
                    </video>
                  </div>
                <?php } elseif ($table_2_icon) { ?>
                  <img src="<?php echo esc_url($table_2_icon); ?>" alt="">
                <?php } ?>
              </div>
            <?php endif; ?>
        </div>
        <?php if($link2['url']) :?>
          </a>
        <?php endif; ?>
      </div>
    </div>
</div>


</section>