<?php



//Register assets for Settings
add_action('init', function () {
  $handle = 'lottiefiles-admin-settings';
  if (file_exists(dirname(__FILE__, 4) . "/build/$handle-page.asset.php")) {
    $assets = include dirname(__FILE__, 4) . "/build/$handle-page.asset.php";
    $dependencies = $assets['dependencies'];

    wp_register_script(
      $handle,
      plugins_url("/build/$handle-page.js", dirname(__FILE__, 3)),
      $dependencies,
      $assets['version']
    );
    if (strstr($_SERVER['REQUEST_URI'], 'lottiefiles-admin-settings')) {
      wp_enqueue_style(
        $handle,
        plugins_url("/build/$handle-page-style.css", dirname(__FILE__, 3)),
        array('wp-components'),
        $assets['version']
      );
    }
  }
});

//Enqueue assets for Settings on admin page only
add_action('admin_enqueue_scripts', function ($hook) {
  if ('toplevel_page_lottiefiles-admin-settings' != $hook) {
    return;
  }
  wp_enqueue_script('lottiefiles-admin-settings');
});

// //Register Settings menu page
add_action('admin_menu', function () {
  add_menu_page(
    'LottieFiles',
    'LottieFiles',
    'manage_options',
    'lottiefiles-admin-settings',
    function () {
      //React root
      echo '<div id="lottiefiles-admin-settings"></div>';
    },
    'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgiIGhlaWdodD0iMTkiIHZpZXdCb3g9IjAgMCAxOCAxOSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xLjM0OTA0IDBDMC45OTEyNDkgMCAwLjY0ODExNyAwLjE0MjEzIDAuMzk1MTIzIDAuMzk1MTIzQzAuMTQyMTMgMC42NDgxMTcgMCAwLjk5MTI0OSAwIDEuMzQ5MDRMMCAxNi45MDM2QzAgMTcuMjYxNCAwLjE0MjEzIDE3LjYwNDUgMC4zOTUxMjMgMTcuODU3NUMwLjY0ODExNyAxOC4xMTA1IDAuOTkxMjQ5IDE4LjI1MjYgMS4zNDkwNCAxOC4yNTI2SDE2LjY1MUMxNy4wMDg4IDE4LjI1MjYgMTcuMzUxOSAxOC4xMTA1IDE3LjYwNDkgMTcuODU3NUMxNy44NTc5IDE3LjYwNDUgMTggMTcuMjYxNCAxOCAxNi45MDM2VjEuMzQ5MDRDMTggMC45OTEyNDkgMTcuODU3OSAwLjY0ODExNyAxNy42MDQ5IDAuMzk1MTIzQzE3LjM1MTkgMC4xNDIxMyAxNy4wMDg4IDAgMTYuNjUxIDBMMS4zNDkwNCAwWk0xNC40Nzc3IDQuNjMxNjFDMTQuNTUwNyA0LjEzMDQ5IDE0LjIwOTcgMy42NjUwNSAxMy43MTU2IDMuNTkwNzlDMTEuODUwOSAzLjMxMTE5IDEwLjA4NDIgNC45OTEyNyA4LjE5OTU4IDguNjMwMThDNi43MTk4NyAxMS40ODc1IDUuMzg5MDkgMTIuOTIzNyA0LjQ4MjY4IDEyLjg1NTdDMy45ODQ4OCAxMi44MTgzIDMuNTUxMzggMTMuMTk3NSAzLjUxNDQ2IDEzLjcwMTlDMy40Nzc5NiAxNC4yMDY4IDMuODUxNzIgMTQuNjQ2NSA0LjM0OTUyIDE0LjY4MzhDNi4xODQzMiAxNC44MjExIDcuOTI3NDUgMTMuMDk4MyA5LjgwMDQyIDkuNDgyMjRDMTEuMjY2OSA2LjY1MDE4IDEyLjU3NjkgNS4yNzI5NCAxMy40NTE0IDUuNDA0NDRDMTMuOTQ1IDUuNDc3ODYgMTQuNDA0NiA1LjEzMjMxIDE0LjQ3NzcgNC42MzExOVY0LjYzMTYxWiIgZmlsbD0iYmxhY2siLz4KPC9zdmc+Cg=='

  );
});
