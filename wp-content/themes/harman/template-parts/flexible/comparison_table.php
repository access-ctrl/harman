<?php 
$section_title = !empty($section['section_title']) ? wp_kses_post($section['section_title']) : '';
$table_1_title = !empty($section['table_1_title']) ? wp_kses_post($section['table_1_title']) : '';
$table_1_description = !empty($section['table_1_description']) ? wp_kses_post($section['table_1_description']) : '';
$table_1_icon = !empty($section['table_1_icon']) ? wp_kses_post($section['table_1_icon']) : '';
$table_1_video = $section['table_1_animated_video'] ?? '';
$button1 = !empty($section['table_1_button']) ? $section['table_1_button'] : [];
$tbl1lists = !empty($section['table_1_list']) && is_array($section['table_1_list']) ? $section['table_1_list'] : [];

$table_2_title = !empty($section['table_2_title']) ? wp_kses_post($section['table_2_title']) : '';
$table_2_description = !empty($section['table_2_description']) ? wp_kses_post($section['table_2_description']) : '';
$table_2_icon = !empty($section['table_2_icon']) ? wp_kses_post($section['table_2_icon']) : '';
$table_2_video = $section['table_2_animated_video'] ?? '';
$button2 = !empty($section['table_2_button']) ? $section['table_2_button'] : [];
$tbl2lists = !empty($section['table_2_list']) && is_array($section['table_2_list']) ? $section['table_2_list'] : [];
?>

<section class="comparison-table space light-pink">
  <div class="container">
    <?php if ($section_title): ?>
      <div class="row">
        <div class="col-12 section-title">          
            <h2><?php echo $section_title; ?></h2>          
        </div>
      </div>
    <?php endif; ?>
    <div class="row">
      <div class="col-12 col-lg-6">
        <div class="comp-table">
          <div class="comp-table-head">
            <div class="comp-table-left">
              <?php if ($table_1_title): ?><h3><?php echo $table_1_title; ?></h3><?php endif; ?>
              <?php if ($table_1_description): ?><p><?php echo $table_1_description; ?></p><?php endif; ?>
              <?php if (!empty($button1['url'])): ?>
                <a data-animated="fadeInUp" href="<?= esc_url($button1['url']); ?>" target="<?= esc_attr($button1['target'] ?? '_self'); ?>" class="btn btn-primary">
                  <?= esc_html($button1['title'] ?? 'Learn More'); ?>
                </a>
              <?php endif; ?>
            </div>
            
              <div class="comp-table-right">
                <?php if ($table_1_video) { ?>
                  <div class="video-outer">                    
                    <video autoplay loop muted playsinline>
                      <source src="<?= $table_1_video; ?>" type="video/mp4"> 
                    </video>
                  </div>
                <?php } elseif ($table_1_icon) { ?>
                  <img src="<?php echo esc_url($table_1_icon); ?>" alt="">
                <?php } ?>
              </div>
            
          </div>

          <?php if (!empty($tbl1lists)): ?>
            <div class="comp-table-body">
              <ul>
                <?php foreach ($tbl1lists as $tbl1list): ?>
                  <?php if (!empty($tbl1list['text'])): ?>
                    <li><?php echo wp_kses_post($tbl1list['text']); ?></li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="comp-table">
          <div class="comp-table-head">
            <div class="comp-table-left">
              <?php if ($table_2_title): ?><h3><?php echo $table_2_title; ?></h3><?php endif; ?>
              <?php if ($table_2_description): ?><p><?php echo $table_2_description; ?></p><?php endif; ?>
              <?php if (!empty($button2['url'])): ?>
                <a data-animated="fadeInUp" href="<?= esc_url($button2['url']); ?>" target="<?= esc_attr($button2['target'] ?? '_self'); ?>" class="btn btn-primary">
                  <?= esc_html($button2['title'] ?? 'Learn More'); ?>
                </a>
              <?php endif; ?>
            </div>
            
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
            

          </div>

          <?php if (!empty($tbl2lists)): ?>
            <div class="comp-table-body">
              <ul>
                <?php foreach ($tbl2lists as $tbl2list): ?>
                  <?php if (!empty($tbl2list['text'])): ?>
                    <li><?php echo wp_kses_post($tbl2list['text']); ?></li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
