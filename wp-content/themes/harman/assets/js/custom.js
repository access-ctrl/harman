jQuery(document).ready(function () {
  jQuery('.slimmenu').slimmenu(
    {
      resizeWidth: '767',
      collapserTitle: '',
      animSpeed: 'medium',
      indentChildren: true,
      childrenIndenter: '&raquo;'
    });

  jQuery('.videos').owlCarousel({
    loop: false,
    margin: 25,
    responsiveClass: true,
    nav: true,
    responsive: {
      0: { items: 1 },
      600: { items: 2 },
      1000: { items: 3 }
    }
  });


  jQuery('header .collapse-button').on('click', function () {
    jQuery(this).toggleClass('active');
  });

  jQuery('.custom-play').on('click', function () {
    var $button = jQuery(this);
    var $wrapper = $button.closest('.video-wrapper');
    var $video = $wrapper.find('video').get(0);

    if ($video.paused) {
      $video.play();
      $wrapper.addClass('playing');
    } else {
      $video.pause();
      $wrapper.removeClass('playing');
    }
  });


  // Delegated hover for videos inside the carousel
  jQuery(document).on('mouseenter', '.videos video', function () {
    this.play();
  });

  jQuery(document).on('mouseleave', '.videos video', function () {
    this.pause();
    this.currentTime = 0; // reset to beginning
  });

  setTimeout(function () {
    jQuery('.videos .owl-next').trigger('click');
  }, 2000); // 2000 milliseconds = 2 seconds

  jQuery('.gallery-slider').owlCarousel({
    loop: true,
    margin: 18,
    responsiveClass: true,
    nav: true,
    autoplay: true,
    slideTransition: 'linear',
    autoplayTimeout: 3000,
    autoplaySpeed: 3000,
    autoplayHoverPause: false,
    onInitialized: runVideoHover,
    onTranslated: runVideoHover,
    responsive: {
      0: {
        items: 1,
      },
      600: {
        items: 3,
      },
      1000: {
        items: 4,
      }
    }
  })

  function runVideoHover(event) {
    jQuery('.owl-item').off('mouseenter mouseleave', 'video');
    jQuery('.owl-item').on('mouseenter', 'video', function () {
      this.play();
    }).on('mouseleave', 'video', function () {
      this.pause();
      this.currentTime = 0;
    });
  }


});



document.addEventListener('DOMContentLoaded', function () {
  // Check if we're on the homepage
  if (window.location.pathname === '/' && !localStorage.getItem('webinar_popup_shown')) {
    const popup = document.getElementById('webinar-popup');
    const closeBtn = document.querySelector('.close-popup');

    // Show the popup after 5 seconds
    setTimeout(() => {
      popup.style.display = 'block';
      localStorage.setItem('webinar_popup_shown', 'true'); // mark as shown
    }, 5000);

    // Close popup on clicking 'X'
    closeBtn.addEventListener('click', function () {
      popup.style.display = 'none';
    });

    // Close on background click
    window.addEventListener('click', function (e) {
      if (e.target === popup) {
        popup.style.display = 'none';
      }
    });
  }
});


jQuery(document).ready(function(){
  jQuery(".testimonial-carousel").owlCarousel({
    loop: true,
    margin: 30,
    nav: false,
    dots: true,
    autoplay: true,
    autoplayTimeout: 6000,
    responsive: {
      0: { items: 1 },
      768: { items: 2 },
      1024: { items: 3 }
    }
  });
});
