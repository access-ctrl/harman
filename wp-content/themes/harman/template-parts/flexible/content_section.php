<?php 
  $title = !empty($section['title']) ? wp_kses_post($section['title']) : ''; 
  $content = !empty($section['content']) ? wp_kses_post($section['content']) : ''; 
  ?>
<section class="space content-section">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h2><?php echo $title; ?></h2>
      </div>
      <div class="col-12">
        <h1><?php echo $content; ?></h1>
      </div>
    </div>
  </div>
</section>