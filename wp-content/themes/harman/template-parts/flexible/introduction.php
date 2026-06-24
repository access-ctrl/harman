<?php 
  $title = !empty($section['title']) ? wp_kses_post($section['title']) : ''; 
  $color = !empty($section['background_color']) ? wp_kses_post($section['background_color']) : '';
  $bgimg = !empty($section['background_image']) ? wp_kses_post($section['background_image']) : '';
  $bgposition = !empty($section['background_position']) ? wp_kses_post($section['background_position']) : '';
  $lists = !empty($section['headings_with_info']) ? $section['headings_with_info'] : [];
?>
<section class="space intro" style="background-color: <?php echo $color; ?>; 
background-image:url(' <?php echo $bgimg; ?>');
background-repeat: no-repeat;
background-position: <?php echo $bgposition; ?>;
">
 
  <div class="container">
    <div class="row">
      <div class="col-12 intro-wrapper">
        <div class="intro-wrapper-left">
          <?php if (!empty($title)): ?>
            <h2><?php echo wp_kses_post($title); ?></h2>
          <?php endif; ?>
        </div>
        <div class="intro-wrapper-right">
          <ul>
          <?php foreach ($lists as $list): ?>                                  
            <li>
              <h4><?= wp_kses_post($list['heading']); ?></h4>
              <p><?= wp_kses_post($list['content']); ?></p>                
            </li>              
          <?php endforeach; ?>          
          </ul>
        </div>

      </div>
    </div>
  </div>
</section>