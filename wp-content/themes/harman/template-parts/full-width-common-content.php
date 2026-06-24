<section class="hero-banner inner-banner">
  <?php if (has_post_thumbnail()) { ?>
    <img src="<?= get_the_post_thumbnail_url(); ?>" alt="" class="banner-main-img desktop-img">
  <?php } ?>
  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-12">
        <h1><?= get_the_title(); ?></h1>
      </div>
    </div>
  </div>
</section>

<section class="space light-bg">
  <div class="container">
    <div class="row">
      <div class="col-12 col-xl-9">
        <?= get_the_content(); ?>
      </div>
    </div>
  </div>
</section>
