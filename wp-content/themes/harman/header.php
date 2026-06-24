<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
    rel="stylesheet">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<!-- After Body Start Script -->
<?php
// Output the after body start script
echo get_field('after_body_start_script', 'option');

// Retrieve and sanitize fields
$logo = esc_url(get_field('header_logo', 'option'));
$phone = esc_html(get_field('phone_number', 'option')); // Use esc_html for text fields
 
?>

<header>
  <div class="container">
    <div class="row">
      <div class="col-12 header-wrapper">
        <div class="header-left">
          <a href="<?php echo site_url();?>"><img src="<?php echo $logo; ?>" alt=""></a>
        </div>
        <div class="header-right">
        <nav class="menu-container">
                <?php
                  wp_nav_menu(array(
                      'theme_location' => 'header',
                      'menu_class'     => 'slimmenu main-menu',
                  ));
                ?>
            </nav>
          <?php if($phone) :?>
            <a href="https://wa.me/message/WTP2VFIBX43HM1" target="_blank" class="whatsapp">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_333_1699)">
                <path d="M0.694203 11.8564C0.693644 13.8729 1.22053 15.8418 2.22239 17.5772L0.598389 23.5067L6.66648 21.9156C8.34484 22.8293 10.2253 23.3081 12.1362 23.3082H12.1413C18.4496 23.3082 23.5848 18.1749 23.5875 11.8654C23.5887 8.80801 22.3991 5.93304 20.2378 3.77006C18.0769 1.60727 15.2029 0.415549 12.1408 0.414154C5.83169 0.414154 0.6969 5.54718 0.694296 11.8564" fill="url(#paint0_linear_333_1699)"/>
                <path d="M0.28557 11.8527C0.284919 13.9417 0.830686 15.981 1.86827 17.7786L0.186035 23.9207L6.47171 22.2726C8.20362 23.2168 10.1536 23.7147 12.1378 23.7154H12.1429C18.6776 23.7154 23.9972 18.3975 24 11.8621C24.0011 8.69488 22.7687 5.71656 20.5302 3.47609C18.2914 1.23591 15.3147 0.00130233 12.1429 0C5.60706 0 0.288175 5.31721 0.28557 11.8527ZM4.02892 17.469L3.79422 17.0965C2.80762 15.5277 2.28687 13.7149 2.28762 11.8534C2.28966 6.4213 6.7105 2.00186 12.1466 2.00186C14.7792 2.00298 17.2532 3.02921 19.114 4.89116C20.9748 6.7533 21.9987 9.22865 21.998 11.8614C21.9956 17.2935 17.5747 21.7135 12.1429 21.7135H12.139C10.3703 21.7126 8.63571 21.2376 7.12297 20.34L6.76297 20.1265L3.03292 21.1045L4.02892 17.469Z" fill="url(#paint1_linear_333_1699)"/>
                <path d="M9.17932 6.89749C8.95736 6.40419 8.72378 6.39424 8.51271 6.38559C8.33987 6.37814 8.14229 6.3787 7.9449 6.3787C7.74731 6.3787 7.42629 6.45303 7.15494 6.74931C6.88332 7.04587 6.11792 7.76252 6.11792 9.2201C6.11792 10.6777 7.17959 12.0864 7.32759 12.2843C7.47578 12.4818 9.37718 15.5687 12.3885 16.7562C14.8912 17.7431 15.4005 17.5468 15.9437 17.4973C16.4869 17.448 17.6966 16.7808 17.9434 16.089C18.1904 15.3973 18.1904 14.8044 18.1163 14.6805C18.0423 14.557 17.8447 14.4829 17.5484 14.3348C17.2522 14.1867 15.7955 13.4699 15.524 13.371C15.2523 13.2722 15.0548 13.2229 14.8573 13.5195C14.6597 13.8157 14.0923 14.4829 13.9194 14.6805C13.7467 14.8785 13.5737 14.9032 13.2775 14.755C12.9811 14.6063 12.0269 14.2939 10.8949 13.2847C10.0142 12.4994 9.41959 11.5296 9.24676 11.2329C9.07392 10.9367 9.22825 10.7762 9.3768 10.6286C9.50992 10.4958 9.67318 10.2826 9.82145 10.1097C9.96918 9.93666 10.0185 9.81321 10.1173 9.61563C10.2162 9.41786 10.1667 9.24484 10.0927 9.09666C10.0185 8.94847 9.44276 7.48326 9.17932 6.89749Z" fill="white"/>
                </g>
                <defs>
                <linearGradient id="paint0_linear_333_1699" x1="1150.05" y1="2309.67" x2="1150.05" y2="0.414154" gradientUnits="userSpaceOnUse">
                <stop stop-color="#1FAF38"/>
                <stop offset="1" stop-color="#60D669"/>
                </linearGradient>
                <linearGradient id="paint1_linear_333_1699" x1="1190.88" y1="2392.07" x2="1190.88" y2="0" gradientUnits="userSpaceOnUse">
                <stop stop-color="#F9F9F9"/>
                <stop offset="1" stop-color="white"/>
                </linearGradient>
                <clipPath id="clip0_333_1699">
                <rect width="23.814" height="24" fill="white" transform="translate(0.186035)"/>
                </clipPath>
                </defs>
              </svg>  <span>+91 <?php echo $phone; ?></span>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</header>