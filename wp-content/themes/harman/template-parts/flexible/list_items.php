<?php 
$section_title = !empty($section['section_title']) ? wp_kses_post($section['section_title']) : ''; 
$listitems = !empty($section['item']) && is_array($section['item']) ? $section['item'] : [];
?>

<section class="space">
  <div class="container">
    <div class="row">
      <div class="col-12 section-title">
        <h2><?= $section_title ; ?></h2>
      </div>
      <div class="col-12">
       <?php if (!empty($listitems)): ?>
        <div class="listitems">
          <ul>
            <?php foreach ($listitems as $listitem): ?>
              <?php if (!empty($listitem['item'])): ?>
                <li><?php echo wp_kses_post($listitem['item']); ?></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      </div>
    </div>
  </div>
</section>