<?php 
  $title = !empty($section['section_title']) ? wp_kses_post($section['section_title']) : '';  
  $color = !empty($section['background_color']) ? wp_kses_post($section['background_color']) : '';
  $bgimg = !empty($section['background_image']) ? wp_kses_post($section['background_image']) : '';   
  $bgposition = !empty($section['background_position']) ? wp_kses_post($section['background_position']) : '';
  $listing_blocks = !empty($section['listing_block']) && is_array($section['listing_block']) ? $section['listing_block'] : [];
  
?>

<section class="space content-block" style="background-color: <?php echo $color; ?>; 
background-image:url(' <?php echo $bgimg; ?>');
background-repeat: no-repeat;
background-position: <?php echo $bgposition; ?>;
">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h2><?php echo $title; ?></h2>
      </div>
      <div class="col-12">
        <?php foreach ($listing_blocks as $listing_block): 
          //print_r($listing_block);
          ?>       
          <?php 
            $title = $listing_block['title'];
            $content = $listing_block['description'];   
            $button = $listing_block['button'];         
          ?>
          <?php if($title) :?>
            <h3><?php echo $title; ?></h3>
          <?php endif; ?>

          <?php if($content) :?>
            <p><?php echo $content; ?></p>
          <?php endif; ?>

          <?php if (!empty($listing_block['list_items']) && is_array($listing_block['list_items'])): ?>
              <ul class="list-style1">
                <?php foreach ($listing_block['list_items'] as $list_item): ?>
                  <li><?php echo wp_kses_post($list_item['item']); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if (!empty($button['url']) && !empty($button['title'])): ?>
                  <a href="<?php echo esc_url($button['url']); ?>" class="btn btn-primary">
                    <?php echo esc_html($button['title']); ?>
                  </a>
                <?php endif; ?>

        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>