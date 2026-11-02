<?php
  // Footer information from theme options
  $copyright = get_field('footer_copyright', 'option');
  $f_logo = esc_url(get_field('footer_logo', 'option'));
  $fb = esc_url(get_field('facebook', 'option'));
  $ig = esc_url(get_field('instagram', 'option'));
  $yt = esc_url(get_field('youtube', 'option'));
  $tw = esc_url(get_field('twitter', 'option'));
  $phone = esc_html(get_field('phone_number', 'option')); // Use esc_html for text fields
?>


<footer>
  <div class="container">
    <div class="row">
      <div class="col-12 footer-top">
        <div class="footer-top-left">
          <a href="<?php echo site_url(); ?>"><img src="<?php echo $f_logo; ?>"></a>
        </div>
        <div class="footer-center">
          <div class="contact-info">
              <h4>Get in Touch</h4>
              <ul>
                <!-- <li><a href="https://wa.me/1234567890" target="_blank"><b>WhatsApp:</b> Chat with Harman</a></li> -->
                <li><a href="mailto:hello@frenchwithharman.com"><b>Email:</b> hello@frenchwithharman.com</a></li>
              </ul>
          </div>
        </div>
        <div class="footer-top-right">
           <h4>Follow us On:</h4>
           <div class="social-block">
            <a href="<?php echo $fb; ?>" class="fb" target="_blank"><svg width="11" height="21" viewBox="0 0 11 21" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M7 12.2725H9.5L10.5 8.27246H7V6.27246C7 5.24246 7 4.27246 9 4.27246H10.5V0.912461C10.174 0.869461 8.943 0.772461 7.643 0.772461C4.928 0.772461 3 2.42946 3 5.47246V8.27246H0V12.2725H3V20.7725H7V12.2725Z" fill="white"/>
              </svg>
            </a>
            <a href="<?php echo $tw; ?>" class="tw" target="_blank"><svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M15.6868 0.835938L10.6908 6.54694L6.37082 0.835938H0.111816L7.58882 10.6119L0.502816 18.7109H3.53682L9.00582 12.4609L13.7858 18.7109H19.8878L12.0938 8.40694L18.7188 0.835938H15.6868ZM14.6228 16.8959L3.65382 2.55494H5.45682L16.3028 16.8949L14.6228 16.8959Z" fill="white"/>
              </svg>
            </a>
            <a href="<?php echo $ig; ?>" class="ig" target="_blank">
              <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5.8 0.772461H14.2C17.4 0.772461 20 3.37246 20 6.57246V14.9725C20 16.5107 19.3889 17.986 18.3012 19.0737C17.2135 20.1614 15.7383 20.7725 14.2 20.7725H5.8C2.6 20.7725 0 18.1725 0 14.9725V6.57246C0 5.03421 0.61107 3.55895 1.69878 2.47124C2.78649 1.38353 4.26174 0.772461 5.8 0.772461ZM5.6 2.77246C4.64522 2.77246 3.72955 3.15175 3.05442 3.82688C2.37928 4.50201 2 5.41768 2 6.37246V15.1725C2 17.1625 3.61 18.7725 5.6 18.7725H14.4C15.3548 18.7725 16.2705 18.3932 16.9456 17.718C17.6207 17.0429 18 16.1272 18 15.1725V6.37246C18 4.38246 16.39 2.77246 14.4 2.77246H5.6ZM15.25 4.27246C15.5815 4.27246 15.8995 4.40416 16.1339 4.63858C16.3683 4.873 16.5 5.19094 16.5 5.52246C16.5 5.85398 16.3683 6.17192 16.1339 6.40634C15.8995 6.64076 15.5815 6.77246 15.25 6.77246C14.9185 6.77246 14.6005 6.64076 14.3661 6.40634C14.1317 6.17192 14 5.85398 14 5.52246C14 5.19094 14.1317 4.873 14.3661 4.63858C14.6005 4.40416 14.9185 4.27246 15.25 4.27246ZM10 5.77246C11.3261 5.77246 12.5979 6.29924 13.5355 7.23693C14.4732 8.17461 15 9.44638 15 10.7725C15 12.0985 14.4732 13.3703 13.5355 14.308C12.5979 15.2457 11.3261 15.7725 10 15.7725C8.67392 15.7725 7.40215 15.2457 6.46447 14.308C5.52678 13.3703 5 12.0985 5 10.7725C5 9.44638 5.52678 8.17461 6.46447 7.23693C7.40215 6.29924 8.67392 5.77246 10 5.77246ZM10 7.77246C9.20435 7.77246 8.44129 8.08853 7.87868 8.65114C7.31607 9.21375 7 9.97681 7 10.7725C7 11.5681 7.31607 12.3312 7.87868 12.8938C8.44129 13.4564 9.20435 13.7725 10 13.7725C10.7956 13.7725 11.5587 13.4564 12.1213 12.8938C12.6839 12.3312 13 11.5681 13 10.7725C13 9.97681 12.6839 9.21375 12.1213 8.65114C11.5587 8.08853 10.7956 7.77246 10 7.77246Z" fill="white"/>
              </svg>
            </a>
            <a href="<?php echo $yt; ?>" class="yt" target="_blank"><img src="/wp-content/uploads/2025/05/youtube1-2.png">
          </a>
          </div>          
        </div>
      </div>
    </div>


    <div class="row">
      <div class="col-12 footer-bottom">
        <div class="footer-bottom-left">
          <?php
            wp_nav_menu(array(
                'theme_location' => 'footer_menu',
                'menu_class'     => 'footer-menu',
            ));
          ?>
        </div>
        <div class="footer-bottom-right">
          <p><?= wp_kses_post($copyright); ?></p>
        </div>
      </div>
    </div>
  </div>
</footer>

<div id="webinar-popup" class="webinar-popup">
  <div class="webinar-popup-content">
    <span class="close-popup">&times;</span>
    <h2>Attend Our Free Webinar</h2>
    <p>Click below to join our webinar on WhatsApp</p>
    <a href="https://api.whatsapp.com/message/WTP2VFIBX43HM1?autoload=1&app_absent=0" target="_blank" class="whatsapp-button">Join on WhatsApp</a>
  </div>
</div>

<img class="footer-note" src="/wp-content/uploads/2025/04/footernote.avif">
 <?php if($phone) :?>
  <div class="fixed-social-icon">
    <a href="https://wa.me/message/WTP2VFIBX43HM1" target="_blank" class="btn-whatsapp-pulse btn-whatsapp-pulse-border">
      <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <g clip-path="url(#clip0_333_1699)">
                  <path d="M0.694203 11.8564C0.693644 13.8729 1.22053 15.8418 2.22239 17.5772L0.598389 23.5067L6.66648 21.9156C8.34484 22.8293 10.2253 23.3081 12.1362 23.3082H12.1413C18.4496 23.3082 23.5848 18.1749 23.5875 11.8654C23.5887 8.80801 22.3991 5.93304 20.2378 3.77006C18.0769 1.60727 15.2029 0.415549 12.1408 0.414154C5.83169 0.414154 0.6969 5.54718 0.694296 11.8564" fill="url(#paint0_linear_333_1699)"></path>
                  <path d="M0.28557 11.8527C0.284919 13.9417 0.830686 15.981 1.86827 17.7786L0.186035 23.9207L6.47171 22.2726C8.20362 23.2168 10.1536 23.7147 12.1378 23.7154H12.1429C18.6776 23.7154 23.9972 18.3975 24 11.8621C24.0011 8.69488 22.7687 5.71656 20.5302 3.47609C18.2914 1.23591 15.3147 0.00130233 12.1429 0C5.60706 0 0.288175 5.31721 0.28557 11.8527ZM4.02892 17.469L3.79422 17.0965C2.80762 15.5277 2.28687 13.7149 2.28762 11.8534C2.28966 6.4213 6.7105 2.00186 12.1466 2.00186C14.7792 2.00298 17.2532 3.02921 19.114 4.89116C20.9748 6.7533 21.9987 9.22865 21.998 11.8614C21.9956 17.2935 17.5747 21.7135 12.1429 21.7135H12.139C10.3703 21.7126 8.63571 21.2376 7.12297 20.34L6.76297 20.1265L3.03292 21.1045L4.02892 17.469Z" fill="url(#paint1_linear_333_1699)"></path>
                  <path d="M9.17932 6.89749C8.95736 6.40419 8.72378 6.39424 8.51271 6.38559C8.33987 6.37814 8.14229 6.3787 7.9449 6.3787C7.74731 6.3787 7.42629 6.45303 7.15494 6.74931C6.88332 7.04587 6.11792 7.76252 6.11792 9.2201C6.11792 10.6777 7.17959 12.0864 7.32759 12.2843C7.47578 12.4818 9.37718 15.5687 12.3885 16.7562C14.8912 17.7431 15.4005 17.5468 15.9437 17.4973C16.4869 17.448 17.6966 16.7808 17.9434 16.089C18.1904 15.3973 18.1904 14.8044 18.1163 14.6805C18.0423 14.557 17.8447 14.4829 17.5484 14.3348C17.2522 14.1867 15.7955 13.4699 15.524 13.371C15.2523 13.2722 15.0548 13.2229 14.8573 13.5195C14.6597 13.8157 14.0923 14.4829 13.9194 14.6805C13.7467 14.8785 13.5737 14.9032 13.2775 14.755C12.9811 14.6063 12.0269 14.2939 10.8949 13.2847C10.0142 12.4994 9.41959 11.5296 9.24676 11.2329C9.07392 10.9367 9.22825 10.7762 9.3768 10.6286C9.50992 10.4958 9.67318 10.2826 9.82145 10.1097C9.96918 9.93666 10.0185 9.81321 10.1173 9.61563C10.2162 9.41786 10.1667 9.24484 10.0927 9.09666C10.0185 8.94847 9.44276 7.48326 9.17932 6.89749Z" fill="white"></path>
                  </g>
                  <defs>
                  <linearGradient id="paint0_linear_333_1699" x1="1150.05" y1="2309.67" x2="1150.05" y2="0.414154" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#1FAF38"></stop>
                  <stop offset="1" stop-color="#60D669"></stop>
                  </linearGradient>
                  <linearGradient id="paint1_linear_333_1699" x1="1190.88" y1="2392.07" x2="1190.88" y2="0" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#F9F9F9"></stop>
                  <stop offset="1" stop-color="white"></stop>
                  </linearGradient>
                  <clipPath id="clip0_333_1699">
                  <rect width="23.814" height="24" fill="white" transform="translate(0.186035)"></rect>
                  </clipPath>
            </defs>
      </svg>
    </a>
    </div>
 <?php endif; ?>


<?php wp_footer(); ?>

<?= get_field('before_body_end_script', 'option') ?>
</body>
</html>
