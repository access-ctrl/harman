<?php 
  $title = !empty($section['title']) ? wp_kses_post($section['title']) : '';   
  $stats = !empty($section['all_stats']) ? $section['all_stats'] : [];
?>

<?php if ($title || !empty($stats)): ?>
<section class="space">
  <div class="container">
    <div class="row">
      <?php if ($title): ?>
        <div class="col-12 section-title">
          <h2><?= $title; ?></h2>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($stats)): ?>
      <div class="row">
        <?php foreach ($stats as $stat): 
          $icon = !empty($stat['icon']) ? esc_url($stat['icon']) : '';
          $number = !empty($stat['number']) ? wp_kses_post($stat['number']) : '';
          $label = !empty($stat['label']) ? wp_kses_post($stat['label']) : '';

          // Only show the stat block if any of the fields are not empty
          if ($icon || $number || $label): 
        ?>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="stats-item">
              <?php if ($icon): ?>
                <div class="icon-wrapper">
                  <img src="<?= $icon; ?>" alt="Stat Icon">
                </div>
              <?php endif; ?>
              <?php if ($number): ?>
                <strong><?= $number; ?></strong>
              <?php endif; ?>
              <?php if ($label): ?>
                <p><?= $label; ?></p>
              <?php endif; ?>
            </div>
          </div>            
        <?php 
          endif; 
        endforeach; ?>               
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
