<?php 
  $section_title = !empty($section['section_title']) ? wp_kses_post($section['section_title']) : '';
  $content = !empty($section['section_description']) ? wp_kses_post($section['section_description']) : '';

  $table_1_title = !empty($section['table_1_title']) ? wp_kses_post($section['table_1_title']) : '';
  $table_1_description = !empty($section['table_1_description']) ? wp_kses_post($section['table_1_description']) : '';
  $table_1_icon = !empty($section['table_1_icon']) ? wp_kses_post($section['table_1_icon']) : '';
  $table_1_video = !empty($section['table_1_animated_video']) ? wp_kses_post($section['table_1_animated_video']) : '';
  $button1 = !empty($section['table_1_button']) ? $section['table_1_button'] : [];
   

  $table_2_title = !empty($section['table_2_title']) ? wp_kses_post($section['table_2_title']) : '';
  $table_2_description = !empty($section['table_2_description']) ? wp_kses_post($section['table_2_description']) : '';
  $table_2_icon = !empty($section['table_2_icon']) ? wp_kses_post($section['table_2_icon']) : '';
  $table_2_video = !empty($section['table_2_animated_video']) ? wp_kses_post($section['table_2_animated_video']) : '';
  $button2 = !empty($section['table_2_button']) ? $section['table_2_button'] : [];

?>
<section class="space feature-block">
  <img src="https://www.frenchwithharman.com/wp-content/uploads/2025/04/note1.png" class="note1">
  <div class="container">
    <div class="row">
      <div class="col-12 section-title">
        <?php if (!empty($section_title)): ?>      
          <h2><?php echo wp_kses_post($section_title); ?></h2>    
        <?php endif; ?>

        <?php if (!empty($content)): ?>     
          <p><?php echo wp_kses_post($content); ?></p>    
        <?php endif; ?>    
      </div>
    </div>

    <div class="row">
      <div class="col-12 col-lg-6">
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
      </div>
      <div class="col-12 col-lg-6">
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
      </div>
    </div>
  </div>
</section>
