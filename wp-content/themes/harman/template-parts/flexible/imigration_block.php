<?php 
  $section_title = !empty($section['main_title']) ? wp_kses_post($section['main_title']) : '';
  $cta_title = !empty($section['cta_title']) ? wp_kses_post($section['cta_title']) : '';  
  $cta_button = !empty($section['cta_button']) && is_array($section['cta_button']) ? $section['cta_button'] : null;
  $cta_style = !empty($section['cta_style']) ? wp_kses_post($section['cta_style']) : '';
  $section_title = !empty($section['main_title']) ? wp_kses_post($section['main_title']) : '';
  $immigrations = !empty($section['immigration_items']) && is_array($section['immigration_items']) ? $section['immigration_items'] : [];
?>
 
  <div class="immigrations">
    <div class="container">
      <?php if ($section_title): ?>    
        <div class="row">
          <div class="col-12 section-title">
            <h2><?php echo $section_title; ?></h2>
          </div>
        </div>
      <?php endif; ?>

      <div class="row">
        <?php foreach ($immigrations as $immigration): ?>       
          <?php 
            $title = $immigration['title'];
            $image = $immigration['image'];
            $button = $immigration['button'];
          ?>
          <div class="col-12">
            <div class="immgiration-block">
              <div class="immigration-left">
                <?php if ($image): ?>
                  <div class="img-wrapper">
                    <img src="<?php echo $image; ?>" alt="<?php echo esc_attr($image); ?>"> 
                  </div>
                <?php endif; ?>
              </div>
              <div class="immigration-right">
                <?php if ($title): ?>
                  <h3><?php echo $title; ?></h3>
                <?php endif; ?>

                <?php if (!empty($immigration['list_items']) && is_array($immigration['list_items'])): ?>
                  <ul class="list-style1">
                    <?php foreach ($immigration['list_items'] as $list_item): ?>
                      <li><?php echo wp_kses_post($list_item['item']); ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <?php if (!empty($button['url']) && !empty($button['title'])): ?>
                  <a href="<?php echo esc_url($button['url']); ?>" class="btn btn-primary">
                    <?php echo esc_html($button['title']); ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
 

  <?php if($cta_title || $cta_button) :?>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="cta-bar <?php echo $cta_style; ?>">
          <h3><?php echo $cta_title; ?></h3>
          <?php if ($cta_button && !empty($cta_button['title']) && !empty($cta_button['url'])) : ?>
            <a href="<?php echo esc_url($cta_button['url']); ?>" class="btn btn-secondary"><?php echo esc_html($cta_button['title']); ?></a>
          <?php endif; ?> 
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?> 
 

 