<?php 
  $section_title = !empty($section['section_title']) ? wp_kses_post($section['section_title']) : '';   
  $selected_posts = $section['students'] ?? [];
  $button1 = !empty($section['table_1_button']) ? $section['table_1_button'] : [];
?>

<section class="video-slider space">
  <div class="container"> 
    <?php if ($section_title): ?>
      <div class="row">
        <div class="col-12 section-title">          
          <h2><?php echo $section_title; ?></h2>          
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($selected_posts)) : ?>
      <div class="row">
        <div class="col-12">
          <div class="owl-carousel videos">
            <?php foreach ($selected_posts as $index => $post) :
              setup_postdata($post);
              $post_id = $post->ID;
              $video = get_field('video_file', $post_id);
              $youtube_video_url = get_field('youtube_video_url', $post_id);              
              $instagram_embed_code = get_field('instagram_video_url', $post_id); // ACF textarea or WYSIWYG
            ?>            
              <div class="item">
                <?php
                if ($youtube_video_url):
                  preg_match('/(?:youtube\.com.*(?:\?v=|\/embed\/)|youtu\.be\/)([^\&\?\/]+)/', $youtube_video_url, $matches);
                  $youtube_id = $matches[1] ?? '';

                  if ($youtube_id):
                    $poster_src = "https://img.youtube.com/vi/{$youtube_id}/maxresdefault.jpg";
                ?>
                    <div class="youtube-poster" style="position: relative; cursor: pointer;" onclick="this.outerHTML = '<iframe width=\'560\' height=\'315\' src=\'https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>?autoplay=1\' frameborder=\'0\' allowfullscreen allow=\'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\'></iframe>';">
                      <img class="thumbnail" src="<?php echo esc_url($poster_src); ?>" alt="YouTube thumbnail" style="width: 100%; display: block;">
                      <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:60px;color:white;text-shadow:0 0 5px #000;">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/circel-arrow.png">
                      </div>
                    </div>
                <?php endif; ?>

                <?php elseif ($video): ?>
                  <video width="320" height="240" controls>
                    <source src="<?php echo esc_url($video); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                  </video> 

                <?php elseif ($instagram_embed_code): ?>                 
                  <div class="instagram-embed">
                    <?php echo $instagram_embed_code; ?>
                  </div>                  
                <?php endif; ?>
                  
                <div class="video-bottom"><h5 class="d-none"><?php the_title(); ?></h5></div>
              </div>
            <?php endforeach; wp_reset_postdata(); ?>           
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php
// Load Instagram JS once (in footer is best practice)
add_action('wp_footer', function () {
  ?>
  <script async src="//www.instagram.com/embed.js"></script>
  <?php
});
?>
