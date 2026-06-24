<?php
add_action('rest_api_init', function () {

  //Get Meta Key
  function get_meta_key()
  {
    $userID = get_current_user_id();
    if (is_admin_user()) {
      return 'lottie_config_admin';
    } else {
      return 'lottie_config_-' . $userID;
    }
  }

  //Check user is Admin
  function is_admin_user()
  {
    return current_user_can('manage_options');
  }

  function lottiefiles_getConfig($request)
  {
    $tokenGlobal = get_option('lottie_config_admin');

    if ($tokenGlobal) {
      $responseData = json_decode($tokenGlobal);
      $responseData->is_block_logged_in = true;
      if (is_admin_user()) {

        return $responseData;
      } else {
        return lottiefiles_userData(json_decode($tokenGlobal));
      }
    } else {
      return lottiefiles_userData(json_decode($tokenGlobal));
    }
  }

  function lottiefiles_userData($tokenGlobal)
  {
    $metaKey = get_meta_key();
    $userID = get_current_user_id();
    $userMeta = get_user_meta($userID, $metaKey);
    if ($userMeta) {
      $responseData = json_decode(array_values($userMeta)[0]);
      $responseData->is_block_logged_in = true;
      $responseData->switchAccount = $tokenGlobal->shareWithOthers ? true : false;
      return $responseData;
    } else {
      if ($tokenGlobal->shareWithOthers) {
        $tokenGlobal->is_block_logged_in = true;
        return $tokenGlobal;
      } else {
        $data = ['is_block_logged_in' => false];
        return $data;
      }
    }
  }

  function lottiefiles_addOption($request)
  {
    $metaKey = get_meta_key();
    if (is_admin_user()) {
      $tokenGlobal = get_option($metaKey);
      if ($tokenGlobal) {
        $params = $request->get_params();
        $jsonData = wp_json_encode($params);
        update_option($metaKey, $jsonData);
        return rest_ensure_response($params, 200);
      } else {
        $params = $request->get_params();
        $params['isAdmin'] = is_admin_user();
        $jsonData = wp_json_encode($params);
        add_option($metaKey, $jsonData);
        return rest_ensure_response($params, 200);
      }
    } else {

      $tokenGlobal = get_option('lottie_config_admin');
      $params = $request->get_params();

      if ($tokenGlobal) {
        if ($tokenGlobal->shareWithOthers) {
          $params['switchAccount'] = true;
        }
      } else {
        $params['switchAccount'] = false;
      }

      $params['isAdmin'] = is_admin_user();
      $userID = get_current_user_id();
      $jsonData = wp_json_encode($params);
      add_user_meta($userID, $metaKey, $jsonData);
      return rest_ensure_response($params, 200);
    }
  }

  function lottiefiles_deleteConfig($request)
  {
    $metaKey = get_meta_key();
    if (is_admin_user()) {
      delete_option($metaKey);
      return json_decode(get_option($metaKey));
    } else {
      $userID = get_current_user_id();

      return delete_user_meta($userID, $metaKey);
    }
  }

  function lottiefiles_isBlockLoggedIn($request)
  {
    return json_decode(get_option('lottie_config_admin'));
  }

  //Register route
  register_rest_route('lottiefiles/v1', '/settings/', [
    //Endpoint to get settings from
    [
      'methods' => ['GET'],
      'callback' => 'lottiefiles_getConfig',
      'permission_callback' => '__return_true',

    ],
    //Endpoint to update settings at
    [
      'methods' => ['POST'],
      'callback' => 'lottiefiles_addOption',
      'permission_callback' => '__return_true',
    ],
    //Endpoint to delete settings at
    [
      'methods' => ['DELETE'],
      'callback' => 'lottiefiles_deleteConfig',
      'permission_callback' => '__return_true',
    ]
  ]);
});
