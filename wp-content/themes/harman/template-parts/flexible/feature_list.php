<?php 
  $section_title = !empty($section['section_title']) ? wp_kses_post($section['section_title']) : '';
  $content = !empty($section['section_content']) ? wp_kses_post($section['section_content']) : '';
  $tbl1lists = !empty($section['list']) && is_array($section['list']) ? $section['list'] : [];
  $color = !empty($section['background_color']) ? wp_kses_post($section['background_color']) : '';
  $bgimg = !empty($section['background_image']) ? wp_kses_post($section['background_image']) : '';
  $bgposition = !empty($section['background_position']) ? wp_kses_post($section['background_position']) : '';
  
?>
<section class="feature-list space" style="background-color: <?php echo $color; ?>; 
background-image:url('<?php echo $bgimg; ?>');
background-repeat: no-repeat;
background-position: <?php echo $bgposition; ?>;
">
  <div class="container"> 
    <?php if ($section_title): ?>
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
    <?php endif; ?>
    <div class="row">
      <div class="col-12">
        <ul>
          <?php $i = 1;?>
          <?php foreach ($tbl1lists as $tbl1list): 
             ?>
            <?php if (!empty($tbl1list['text'])): ?>
              <li><span><?php echo sprintf('%02d', $i); ?></span><?php echo wp_kses_post($tbl1list['text']); ?></li>
            <?php endif; ?>
            <?php $i++; ?>
          <?php endforeach; ?>
             
        </ul>
      </div>
    </div>
  </div>
</section>