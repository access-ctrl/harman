<?php
  // Default values for section attributes

  $left_image = !empty($section['left_image']) ? wp_kses_post($section['left_image']) : '';
  $left_image_mobile = !empty($section['left_image_mobile']) ? wp_kses_post($section['left_image_mobile']) : '';
  $left_image_url = !empty($section['left_image_url']) ? wp_kses_post($section['left_image_url']) : '';
  $banner_image = !empty($section['banner_image']) ? wp_kses_post($section['banner_image']) : '';
  $banner_image_desktop = !empty($section['background_image']) ? wp_kses_post($section['background_image']) : '';
  $button = !empty($section['left_image_link']) ? $section['left_image_link'] : [];
  $video_file =  !empty($section['video_file']) ? wp_kses_post($section['video_file']) : '';
?>

<section class="hero-banner without-text">
<?php if (!empty($video_file)) : ?>
  <video class="banner_image" src="<?php echo $video_file; ?>" autoplay muted loop playsinline></video>
<?php elseif (!empty($banner_image_desktop)) : ?>
  <img class="banner_image" src="<?php echo $banner_image_desktop; ?>" alt="Banner Image">
<?php endif; ?>

<div class="leaves">
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
			</div>
      <div class="leaves leaves2">
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
			</div>
      <div class="leaves leaves3">
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
				<div><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mapple-leaf3.png"></div>
			</div>
  <div class="container">
    <div class="row">
      <div class="col-12 banner-wrapper">
        <div class="hero-banner-left">
          <a href="<?= esc_url($button['url']); ?>">
          <?php if (!empty($left_image)): ?>
            <img src="<?php echo esc_url($left_image); ?>" alt="">
          <?php elseif (!empty($left_image_url)): ?>
            <img class="desktop" src="<?php echo esc_url($left_image_url); ?>" alt="">
            <img class="mobile" src="<?php echo esc_url($left_image_mobile); ?>" alt="">
          <?php endif; ?>

          </a>
        </div>
        <div class="hero-banner-right">
          <img src="<?php echo $banner_image; ?>" alt="">
        </div>
      </div>
    </div>
  </div>
</section>



<section class="fwh-banner">
  <div class="container fwh-banner-overlay">
    <div class="fwh-left">
      <h1><strong>🇫🇷 Learn French Online With an Expert Tutor!</strong></h1>
      <p>Join 2000+ learners already speaking fluent French with Harman. Start your journey today — from beginner to fluent, at your own pace.</p>
      <div class="fwh-cta-buttons">
        <a href="#book" class="btn btn-primary">Book Your Free Trial Class</a>
        <a href="https://wa.me/1234567890" class="btn btn-secondary">WhatsApp Now</a>
      </div>
    </div>
    <div class="fwh-right">
      <img src="/wp-content/uploads/2025/04/Group-1171275535.png" alt="Harman smiling" />
    </div>
  </div>
</section>

<section class="card-content-with-icon space">
  <div class="container">
    <h2 class="section-title"><strong>Why Choose French With Harman?</strong></h2>
    <div class="card-content-with-icon-wrap">
      <div class="card-content-with-icon-box">
        <div class="icon"><img src="/wp-content/themes/harman/assets/images/badge.png" alt="Certified & Experienced"></div>
        <h4><strong>Certified & Experienced</strong></h4>
        <p>10+ years of teaching experience with a strong academic background in French education.</p>
      </div>
      <div class="card-content-with-icon-box">
        <div class="icon"><img src="/wp-content/themes/harman/assets/images/flexible-time.png" alt="flexible time"></div>
        <h4><strong>Flexible Timings</strong></h4>
        <p>Schedule classes around your availability — morning, evening, or weekends.</p>
      </div>
      <div class="card-content-with-icon-box">
        <div class="icon"><img src="/wp-content/themes/harman/assets/images/interactive-learning.png" alt="interactive learning"></div>
        <h4><strong>Interactive Lessons</strong></h4>
        <p>Speak real-life French from day one with dynamic, conversation-based sessions.</p>
      </div>
      <div class="card-content-with-icon-box">
        <div class="icon"><img src="/wp-content/themes/harman/assets/images/infographics.png" alt="infographics"></div>
        <h4><strong>Proven Results</strong></h4>
        <p>Join over 2000 successful students learning to speak French confidently worldwide.</p>
      </div>
    </div>
  </div>
</section>

<section class="space feature-block">
  <img src="https://www.frenchwithharman.com/wp-content/uploads/2025/04/note1.png" class="note1">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center">
        <h2><strong>Courses Offered</strong></h2>        
      </div>
    </div>

    <div class="row g-5">
      <div class="col-12 col-lg-6">
        <div class="feature-card">
          <div class="feature-card-left">
            <h3><strong>A1 –Beginner:</strong></h3>
            <p>Learn basic greetings, grammar, and pronunciation</p>
          </div>
          <div class="comp-table-right">
            <div class="video-outer">
              <video autoplay="" loop="" muted="" playsinline="">
                <source
                  src="https://www.frenchwithharman.com/wp-content/uploads/2025/04/Animation-for-gloal-job-opportunities-1.mp4"
                  type="video/mp4">
              </video>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="feature-card">
          <div class="feature-card-left">
            <h3><strong>A2 –Elementary:</strong></h3>
            <p>Build vocabulary, read/write confidently</p>
          </div>
          <div class="comp-table-right">
            <div class="video-outer">
              <video autoplay="" loop="" muted="" playsinline="">
                <source src="https://www.frenchwithharman.com/wp-content/uploads/2025/04/Higher-Earnings-Logo-1.mp4"
                  type="video/mp4">
              </video>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="feature-card">
          <div class="feature-card-left">
            <h3><strong>B1/B2 –Intermediate/Advanced:</strong></h3>
            <p>Speak fluently, master grammar</p>
          </div>
          <div class="comp-table-right">
            <div class="video-outer">
              <video autoplay="" loop="" muted="" playsinline="">
                <source src="https://www.frenchwithharman.com/wp-content/uploads/2025/04/Higher-Earnings-Logo-1.mp4"
                  type="video/mp4">
              </video>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="feature-card">
          <div class="feature-card-left">
            <h3><strong>Custom Courses:</strong></h3>
            <p>For exams (DELF/DALF/TEF), travel, or work </p>
          </div>
          <div class="comp-table-right">
            <div class="video-outer">
              <video autoplay="" loop="" muted="" playsinline="">
                <source src="https://www.frenchwithharman.com/wp-content/uploads/2025/04/Animation-for-gloal-job-opportunities-1.mp4"
                  type="video/mp4">
              </video>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section testimonial-carousel-section space">
  <div class="container">
    <h2 class="section-title"><strong>What Students Say</strong></h2>

    <div class="owl-carousel testimonial-carousel">
      <!-- Testimonial Item 1 -->
      <div class="testimonial-card">
        <h5 class="testimonial-text"><strong>“Harman made French so easy! I cleared my DELF A2 in 3 months.”</strong></h5>
        <div class="testimonial-meta">
          <img src="/wp-content/uploads/2025/04/Rectangle-17.png" alt="Priya" class="testimonial-photo">
          <div>
            <h5 class="testimonial-name">Priya</h5>
            <span class="testimonial-country">India 🇮🇳</span>
          </div>
        </div>
      </div>

      <!-- Testimonial Item 2 -->
      <div class="testimonial-card">
        <h5 class="testimonial-text"><strong>“Her teaching style is interactive and fun. Highly recommend!”</strong></h5>
        <div class="testimonial-meta">
          <img src="/wp-content/uploads/2025/04/Rectangle-17.png" alt="Alex" class="testimonial-photo">
          <div>
            <h5 class="testimonial-name">Alex</h5>
            <span class="testimonial-country">Canada 🇨🇦</span>
          </div>
        </div>
      </div>

      <!-- Add more testimonials below -->
    </div>
  </div>
</section>


<section class="section call-to-action space" style="background-image: url(/wp-content/uploads/2025/04/leafs.png);">
  <div class="container">
    <h2 class="cta-heading text-center mb-3"><strong>🚀 Ready to Speak French Confidently?</strong></h2>
    <p class="cta-subtext text-center mb-3">Book your <strong>FREE trial class</strong> today — limited slots available!</p>
    <div class="cta-buttons text-center">
      <a href="#booking" class="btn btn-primary">Book Free Trial</a>
      <a href="https://wa.me/1234567890" class="btn btn-secondary" target="_blank">WhatsApp Harman</a>
    </div>
  </div>
</section>

<section class="section faq-section space">
  <div class="container">
    <h2 class="section-title text-center mb-5 text-white"><strong>Frequently Asked Questions</strong></h2>

    <div class="accordion custom-faq-accordion" id="faqAccordion">
      
      <div class="accordion-item faq-item">
        <h4 class="accordion-header" id="headingOne">
          <button class="accordion-button faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            How are classes conducted?
          </button>
        </h4>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
          <div class="accordion-body faq-answer">
            Classes are conducted live via Zoom or Google Meet with real-time interaction, screen sharing, and exercises.
          </div>
        </div>
      </div>

      <div class="accordion-item faq-item">
        <h4 class="accordion-header" id="headingTwo">
          <button class="accordion-button collapsed faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            Do I need prior knowledge?
          </button>
        </h4>
        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
          <div class="accordion-body faq-answer">
            No prior knowledge is required. We start from the basics in our beginner (A1) courses.
          </div>
        </div>
      </div>

      <div class="accordion-item faq-item">
        <h4 class="accordion-header" id="headingThree">
          <button class="accordion-button collapsed faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            Can I appear for DELF/TEF exams after your classes?
          </button>
        </h4>
        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
          <div class="accordion-body faq-answer">
            Yes, our courses prepare students thoroughly for DELF, DALF, and TEF exams with targeted exam training.
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<section class="section testimonial-two space">
  <div class="container">
    <h2 class="section-title text-center mb-5"><strong>What Students Say About Learning French With Harman</strong></h2>

    <div class="row testimonial-row">
      <!-- Testimonial Card 1 -->
      <div class="col-md-6 col-lg-4">
        <div class="testimonial-card">
          <div class="testimonial-header">
            <img src="/wp-content/uploads/2025/04/Rectangle-17.png" alt="Priya" class="testimonial-photo">
            <div class="testimonial-meta">
              <h5>Priya</h5>
              <span>India 🇮🇳</span>
              <small>A2 Level</small>
            </div>
          </div>
          <div class="testimonial-stars">★★★★★</div>
          <p class="testimonial-quote">“Harman made French so easy! I cleared my DELF A2 in 3 months.”</p>
        </div>
      </div>

      <!-- Testimonial Card 2 -->
      <div class="col-md-6 col-lg-4">
        <div class="testimonial-card">
          <div class="testimonial-header">
            <img src="/wp-content/uploads/2025/04/Rectangle-17.png" alt="Alex" class="testimonial-photo">
            <div class="testimonial-meta">
              <h5>Alex</h5>
              <span>Canada 🇨🇦</span>
              <small>B1 Level</small>
            </div>
          </div>
          <div class="testimonial-stars">★★★★★</div>
          <p class="testimonial-quote">“Her teaching style is so interactive and fun. Highly recommend!”</p>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="testimonial-card">
          <div class="testimonial-header">
            <img src="/wp-content/uploads/2025/04/Rectangle-17.png" alt="Priya" class="testimonial-photo">
            <div class="testimonial-meta">
              <h5>Priya</h5>
              <span>India 🇮🇳</span>
              <small>A2 Level</small>
            </div>
          </div>
          <div class="testimonial-stars">★★★★★</div>
          <p class="testimonial-quote">“Harman made French so easy! I cleared my DELF A2 in 3 months.”</p>
        </div>
      </div>

      <!-- Add more cards as needed -->
    </div>
  </div>
</section>






