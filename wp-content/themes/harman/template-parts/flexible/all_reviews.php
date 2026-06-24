<section class="all-reviews space">
  <div class="container">
    <div class="row">
      <div class="col-12 section-title textleft">
        <h2><strong>Words from</strong> our students</h2>
      </div>
    </div>
    <div class="row mb--20">      
      <?php
      $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

      $args = array(
          'post_type'      => 'student-review',
          'posts_per_page' => 10, // Change as needed
          'paged'          => $paged
      );

      $student_reviews = new WP_Query($args);

      if ($student_reviews->have_posts()) :
        while ($student_reviews->have_posts()) : $student_reviews->the_post();
          // ACF fields
          $video = get_field('video_file');
          $location     = get_field('student_location');
          $course_name  = get_field('cource_name'); // example additional field
          $youtube_video_url = get_field('youtube_video_url');
          $instagram_embed_code = get_field('instagram_video_url');
         
          ?>
          <div class="col-12 col-lg-6">
            <div class="review-item">
            <?php if ($youtube_video_url) :
                preg_match('/(?:youtube\.com.*(?:\?v=|\/embed\/)|youtu\.be\/)([^\&\?\/]+)/', $youtube_video_url, $matches);
                $youtube_id = $matches[1] ?? '';

                if ($youtube_id):
                  $poster_src = "https://img.youtube.com/vi/{$youtube_id}/maxresdefault.jpg";
              ?>
                  <div class="video-wrapper" style="position: relative; width: 100%;">
                    <div class="youtube-poster" style="position: relative; cursor: pointer;" onclick="this.outerHTML = '<iframe width=\'560\' height=\'315\' src=\'https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>?autoplay=1\' frameborder=\'0\' allowfullscreen allow=\'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\'></iframe>';">
                      <img class="thumbnail" src="<?php echo esc_url($poster_src); ?>" alt="YouTube thumbnail" style="width: 100%; display: block;">
                      <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:60px;color:white;text-shadow:0 0 5px #000;"> <img src="<?php echo get_template_directory_uri(); ?>/assets/images/circel-arrow.png"></div>
                    </div>
                  </div>
              <?php endif; ?>
                

              <?php elseif ($instagram_embed_code): ?>                 
                <div class="instagram-embed">
                  <?php echo $instagram_embed_code; ?>
                </div>                  

                <?php elseif ($video): ?>
                <div class="video-wrapper">
                  <video width="320" height="240" >
                    <source src="<?php echo $video; ?>" type="video/mp4">          
                    Your browser does not support the video tag.
                  </video> 
                  <div class="custom-play">
                    <img class="custom-play-btn" src="<?php echo get_template_directory_uri(); ?>/assets/images/circel-arrow.png">
                    <img class="custom-pause-btn" src="<?php echo get_template_directory_uri(); ?>/assets/images/circel-pause.png">             
                  </div> 
                </div> 
              <?php endif; ?>
                <div class="excerpt d-none">
                  <?php the_excerpt(); ?>
                </div>
                <div class="footer d-none">
                  <p><strong><?php the_title(); ?> from  <?php echo esc_html($location); ?></strong> <?php echo esc_html($course_name); ?></p>                                  
                </div>
            </div>
          </div>
        <?php endwhile; ?>
        <div class="pagination">
            <?php
            echo paginate_links(array(
                'total'   => $student_reviews->max_num_pages,
                'current' => $paged
            ));
            ?>
        </div>

        <?php wp_reset_postdata(); ?>

      <?php else : ?>
          <p>No student reviews found.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
