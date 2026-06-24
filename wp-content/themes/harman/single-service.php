<?php
  get_header();

  // Loop through flexible page sections
  $page_sections = get_field('page_section');
  if ($page_sections) {
    foreach ($page_sections as $section) {
      $layout = $section['acf_fc_layout'];
      include get_template_directory() . '/template-parts/flexible/' . $layout . '.php';
      unset($section);
    }
  }

  get_footer();
?>
