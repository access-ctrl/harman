<?php
  get_header();

  // Retrieve flexible content sections
  $page_sections = get_field('page_section');
  if ($page_sections) {
    foreach ($page_sections as $section) {
      $layout = $section['acf_fc_layout'];
      include get_template_directory() . '/template-parts/flexible/' . $layout . '.php';
    }
  } else {
    include get_template_directory() . '/template-parts/full-width-common-content.php';
  }

  get_footer();
?>
