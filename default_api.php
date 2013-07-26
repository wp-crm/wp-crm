<?php
/**
 * WP-CRM Default API
 *
 * @version 0.1
 * @author Andy Potanin <andy.potanin@twincitiestech.com>
 * @package WP-CRM
 */

add_filter('wp_crm_contact_form_data_validation', array('wp_crm_default_api', 'email_validation'), 0,2);
add_filter('wp_crm_user_card_keys', array('wp_crm_default_api', 'wpp_crm_card_keys_default'));
add_filter('wp_crm_primary_user_attribute_keys', array('wp_crm_default_api', 'wpp_crm_card_keys_default'));
add_filter('wp_crm_notification_actions', array('wp_crm_default_api', 'default_wp_crm_actions'));
add_filter('wp_crm_display_phone_number', array('wp_crm_default_api', 'wpp_crm_format_phone_number'));
add_filter('wp_crm_display_company', array('wp_crm_default_api', 'wp_crm_display_company'), 0, 4);
add_filter('wp_crm_display_user_email', array('wp_crm_default_api', 'wp_crm_display_user_email'), 0, 4);
add_filter('wp_crm_settings_lower', array('wp_crm_default_api', 'wp_crm_add_overview_user_actions'), 0, 10);

/**
 * Default WP-CRM API
 *
 * Use this class for examples on how to use the WP-CRM API
 *
  */
class wp_crm_default_api {

  /**
   * Add default user actions.
   *
   * Can be disabled / enabled by Settings page.
   *
   * @since 0.1
   */
  function wp_crm_add_overview_user_actions($wp_crm) {

    //** Add Quick Actions */
    if(current_user_can('edit_users')) {
      $wp_crm['overview_user_actions']['reset_password']['label'] = __('Quick Password Reset', 'wp_crm');
    }

    //** Add trigger */
    add_filter('wp_crm_notification_actions', create_function('$current', ' $current["password_reset"] = __("Password Reset", "wp_crm"); return $current;  '));

    return $wp_crm;

  }


  /**
   * Add attribute to overview "User Card" selection for settings page.
   *
   * @since 0.1
   */
  function wpp_crm_card_keys_default($current) {
    global $wp_crm;

    $attribute_keys = array_keys($wp_crm['data_structure']['attributes']);

    $to_add['display_name'] = array(
      'title' => __('Display Name', 'wp_crm'),
      'quick_description' => __('Generated automatically by WordPress.', 'wp_crm')
    );

    $to_add['user_login'] = array(
      'title' => __('User Login', 'wp_crm'),
      'quick_description' => __('Generated automatically by WordPress.', 'wp_crm')
    );

    foreach($to_add as $attrib_key => $attrib_data) {

      //** Do not add attributes if they already exist in General Settings */
      if(in_array($attrib_key, $attribute_keys)) {
        continue;
      }

      $new[$attrib_key] = $attrib_data;

    }

    if(is_array($new)) {
      return $new + $current;
    }

    return $current;

  }


/**
   * Fires off when a user retrieves their WordPres password.
   *
   * Adds note to user stream.
   *
   */
  function wp_crm_retrieve_password($user_login) {
    global $wpdb;

    $user_id = username_exists($user_login);

    if(!$user_id) {
      return;
    }

    $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM {$wpdb->users} WHERE user_login = %s", $user_login));
    if ( empty($key) ) {
      $key = wp_generate_password(20, false);
      do_action('retrieve_password_key', $user_login, $key);
      $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
    }

    $reset_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
    $message .= sprintf(__('Password reset initiated by user. Reset URL: %1s.', 'wp_crm'), '<a href="' . $reset_url . '">' . $reset_url . '</a>');

    wp_crm_add_to_user_log($user_id, $message);

  }


  /**
   * Makes sure the submitted e-mail is good.
   *
   * @uses sanitize_email() for validation
   *
   */
  function email_validation($current, $data) {

    $field = $data['field'];
    $value = $data['value'];

    if($field == 'user_email') {

      $sanitize_email = sanitize_email($value);

      if(empty($sanitize_email)) {
        return __('Please enter a valid e-mail address.', 'wp_crm');
      }

    }

    return false;

  }


  /**
   * Add default notification actions.
   *
   * @since 0.1
   */
  function default_wp_crm_actions($current) {
    $current['new_user_registration'] = __("User Registration", 'wp_crm');
    $current['support_request'] = __("Support Request", 'wp_crm');
    return $current;
  }



 /**
   * Format company on overview page in the main_view cell
   *
   * @todo add link to filter down by company
   * @since 0.1
   */
 function wp_crm_display_company($current, $user_id, $user_object, $scope) {

  if($scope == 'main_view') {
    return (WP_CRM_F::get_first_value($user_object['title']) ? WP_CRM_F::get_first_value($user_object['title']) . ' at ' : '') . '<a href="">' . WP_CRM_F::get_first_value($user_object['company']) . '</a>';
  }

  return $current;

 }


 /**
   * Format user_email on overview page in the main_view cell and add a new filter to potentially allow e-mails to be sent via CRM
   *
   * @since 0.1
   */
 function wp_crm_display_user_email($current, $user_id, $user_object, $scope) {

  if($scope == 'main_view') {
    return apply_filters('wp_crm_contact_link', " <a href='mailto:{$current}'>{$current}</a>", $user_object);
  }

  return $current;

 }



  /**
   * Converts a string into a readable phone number
   *
   * @since 0.1
   */
  function wpp_crm_format_phone_number($phone) {

    $phone = preg_replace("/[^0-9]/", "", $phone);

    if(strlen($phone) == 7)
    return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
    elseif(strlen($phone) == 10)
    return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
    else
    return $phone;

  }


}



if(!function_exists('wp_crm_get_value')) {
  /**
   * Get a value of a user.
   *
   * Ideally an attribute key should be passed, in which case the first value will be returned by default.
   * If a full meta key is passed, the value will be returned.
   * If a value of an option is passed, a boolean will be returned depending on if the option is enabled for the user.
   *
   * @since 0.1
   *
   */
  function wp_crm_get_value($meta_key, $user_id = false, $args = '') {
    global $current_user, $wp_crm;

    $args = wp_parse_args( $args, array(
      'return' => 'value',
      'concat_char' => ', ',
      'meta_key' => $meta_key
    ));

    if(!$user_id) {
      $user_id = $current_user->ID;
    }

    $quantifiable_attributes = array_keys(WP_CRM_F::get_quantifiable_attributes());

    //** Check if meta key exists as key and as label */
    $attributes = $wp_crm['data_structure']['attributes'];
    $full_meta_keys = $wp_crm['data_structure']['full_meta_keys'];
    $meta_keys = $wp_crm['data_structure']['meta_keys'];

    //* If passed key is an attribute key (as intended) */
    if(is_array($attributes) && in_array($meta_key, array_keys($attributes))) {
      $args['attribute_key'] = $meta_key;
    }

    //* If passed meta_key is actually a label name */
    if(is_array($meta_keys) && in_array($meta_key, $meta_keys)) {
      $meta_value_flip = array_flip($meta_keys);
      $args['attribute_key'] = $meta_value_flip[$meta_key];
      $meta_key = $args['attribute_key'];
    }

    //* If a full meta key is passed (for pre-defined values)  */
    if(is_array($full_meta_keys) && in_array($meta_key, array_keys($full_meta_keys))) {
      $args['full_meta_key'] = $meta_key;
      $args['attribute_key'] = $full_meta_keys[$meta_key];
    }

    //** If all else fails, use the passed $meta_key (it may be a main user table key) */
    if(empty($args['attribute_key'])) {
      $args['attribute_key'] = $meta_key;
    }

    //** Get the full attribute data once we have the attribute_key */
    $attribute = WP_CRM_F::get_attribute($args['attribute_key']);

    //* Make sure we have a user object */
    if(!empty($user_id) && is_numeric($user_id)) {
      $user_object = (array) wp_crm_get_user($user_id);
    }

    //** If we have the full meta key then we can get value easily from get_user_meta() */
    if(!empty($args['full_meta_key']) && $args['full_meta_key'] != $args['attribute_key']) {
      $args['value'] = get_user_meta($user_id, $args['full_meta_key'], true);

    } else {

      //** Attribute has options, we return the label of the option */
      if($attribute['has_options']) {

        $args['option_key']= $args['option_key'][0];
        $args['label'] = $meta_keys[$args['attribute_key'] . '_option_' . $args['option_key']];
        $args['return_option_label'] = true;

        if($attribute['input_type'] == 'text' || $attribute['input_type'] == 'textarea' || $attribute['input_type'] == 'date') {

          if($args['option_key'] == 'default') {

            $args['value'] = get_user_meta($user_id, $args['attribute_key'], true);
          } else {
            $args['value'] = get_user_meta($user_id, $args['attribute_key'] . '_option_' . $args['option_key'], true) . ', ' . $args['label'];
          }

        } else {

          $options = WP_CRM_F::list_options($user_object, $args['attribute_key']);

          if(is_array($options)) {
            $args['value'] = implode($args['concat_char'], $options);
          }

        }

      } else {

        $args['value'] = WP_CRM_F::get_first_value($user_object[$args['attribute_key']]);

      }

    }

    //** Check if this should be a boolean response */
    if(!$args['return_option_label'] && in_array($args['attribute_key'], $quantifiable_attributes)) {
      if($args['value'] == 'on') {
        $args['value'] = true;
      } else {
        $args['value'] = false;
      }
    }

    switch($args['return']) {

      case 'value':
        $result = $args['value'];
      break;

      case 'detail':
        $result = $args;
      break;

      default:
        $result = $args[$args['return']];
      break;

    }

    return $result;


  }

}


if(!function_exists('wp_crm_get_user')) {
  /**
   * Get user object based on the CRM data hierarchy
   *
   * @hooked_into WP_CRM_Core::admin_head();
   * @since 0.1
   */
  function wp_crm_get_user($user_id, $args = '') {
    global $wp_crm, $wpdb, $current_user;

    $args = wp_parse_args( $args, array(
      'return_type' => 'object'
    ));

    //** Check if user exists */
    if(!$user_table = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = $user_id", ARRAY_A)) {
      return false;
    }

    //** Get all values from user table */
    foreach($user_table as $key => $value) {
      if($value) {
        $user_data[$key]['default'][0] = $value;
      }
    }

    //** Get data from meta table */
    if($wp_crm['data_structure']['attributes']) {
      foreach($wp_crm['data_structure']['attributes'] as $key => $data) {

        //* Get default value */
        $default_values = get_user_meta($user_id, $key);

        //** Add default value to array, taking into account that it may already have been populated from user table */
        if($default_values) {
          foreach($default_values as $default_value) {
          $user_data[$key]['default'][]  = $default_value;
          }
        }

        if($data['has_options']) {
          //** If key has options, we check all meta keys for values */
          foreach($data['option_keys'] as $option_key) {

            if($option_values = get_user_meta($user_id, $option_key)) {

              foreach($option_values as $option_count => $option_value) {
                $option_annex = str_replace($key . '_option_', '', $option_key);
                $user_data[$key][$option_annex][$option_count] = $option_value;
              }

            }
          }
        }
      }
    }

    //** Handle roles and capabilities */
    $capabilities = unserialize($wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = $user_id AND meta_key = '{$wpdb->prefix}capabilities'"));
    if (!empty ($capabilities)) {
      foreach($capabilities as $cap_slug => $cap_active) {
        if($cap_active) {
          $user_data['role']['default'][0] = $cap_slug;
        }
      }
    }


    //* Fix up certain attributes */
    //$user_data['display_name'][0]['value'] = $user_data['first_name'][0]['value'] . ' ' . $user_data['last_name'][0]['value'];

    if($return_type == 'object') {
      $user_data = (object)$user_data;
    }

    if($return_type == 'array') {
      $user_data = (array)$user_data;
    }

    return $user_data;
  }
}

if(!function_exists('wp_crm_send_notification')) {

  /**
   * Send an e-mail or a text message to a recipient .
   *
   * Returns true if at least one of notifications was sent out.
   *
   * @since 0.1
   */
  function wp_crm_send_notification($action = false, $args = false) {
    global $wp_crm, $wpdb;

    if(!$action) {
      return false;
    }

    $defaults = array(
      'force' => false
    );

    if(!is_array($args)) {
      $args = wp_parse_args( $args, $defaults );
    }

    if(empty($args)) {
      return false;
    }

    $notifications = WP_CRM_F::get_trigger_action_notification($action, $args['force']);

    if(!$notifications) {
      return false;
    }
    $result = false;
    //** Act upon every notification one at a time */
    foreach($notifications as $notification) {

      $message = WP_CRM_F::replace_notification_values($notification, $args);

      if(!$message) {
        continue;
      }

      $headers['From']    = "From: ".$message['send_from'];
      $headers['Bcc']     = "Bcc: ".$message['bcc'];

      add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));

      if($wp_crm['configuration']['do_not_use_nl2br_in_messages'] == 'true') {
        $message['message'] = $message['message'];
      } else {
        $message['message'] = nl2br($message['message']);
      }

      if (wp_mail($message['to'], $message['subject'], $message['message'], $headers, ($args['attachments'] ? $args['attachments'] : false))){
        $result = true;
      }

    }
    return $result;
  }
} /* wp_crm_send_notification */

if(!function_exists('wp_crm_save_user_data')) {
  /**
   * Saves user data
   *
   * @hooked_into WP_CRM_Core::admin_head();
   * @since 0.1
   */
  function wp_crm_save_user_data($user_data, $args = '') {
    global $wpdb, $wp_crm;

    $insert_data = array();
    $insert_custom_data = array();

    $args = wp_parse_args( $args, array(
      'use_global_messages' => 'true',
      'match_login' => 'false',
      'no_errors' => 'false',
      'return_detail' => 'false',
      'default_role' => get_option('default_role'),
      'no_redirect' => 'false'
    ));

    $wp_insert_user_vars = array(
      'user_pass',
      'user_email',
      'user_login',
      'user_url',
      'role',
      'user_nicename',
      'display_name',
      'user_registered',
      'first_name',
      'last_name',
      'nickname'
    );

    //** Get custom meta attributes */
    $wp_user_meta_data = array();

    if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
      foreach($wp_crm['data_structure']['attributes'] as $slug => $value) {
        if(!in_array($slug, $wp_insert_user_vars)) {
          $wp_user_meta_data[] = $slug;
        }
      }
    }

    //** Add custom keys that are not necessarily created in WP-CRM data but must be saved if passed */
    $wp_user_meta_data[] = 'show_admin_bar_front';
    $wp_user_meta_data[] = 'admin_color';

    $temp_data['user_id'] = WP_CRM_F::get_first_value($user_data['user_id']);

    // Prepare Data
    foreach($user_data as $meta_key => $values) {

      //** Fix up values if they are not passed in the crazy CRM format */
      if(!empty($values) && !is_array($values)) {

        //** Check if Attribute TITLE was passed intead of the slug */
        foreach($wp_crm['data_structure']['attributes'] as $attribute_slug => $attribute_data) {
          if($attribute_data['title'] == $meta_key) {

            //** Actual slug / meta_key found, we overwrite the passed one */
            $meta_key = $attribute_slug;
            break;
          }
        }

        //** Check if this is an option key, and value needs to be convered to 'on' */
        if($wp_crm['data_structure']['attributes'][$meta_key]['has_options']) {
          if(in_array($values, $wp_crm['data_structure']['attributes'][$meta_key]['option_labels'] )) {

            //** Get option key from passed option title */
            $option_key = array_search($values, $wp_crm['data_structure']['attributes'][$meta_key]['option_labels']);
            //** Restet $values, and update with checkbox friendly data entry */
            $values = array(
              rand(10000, 99999) => array (
                  'value' => 'on',
                  'option' => $option_key
              )
            );

          }
        } else {
          //** Handle Regular values */
            $values = array(
              rand(10000, 99999) => array (
                  'value' => $values
              )
            );

        }

      }

      //** Make sure values are always in array format */
      $values = (array) $values;

      foreach( $values as $temp_key => $data) {

        //** If this attribute is in the main user table, we store it here */
        if(in_array($meta_key, $wp_insert_user_vars)) {

          //** Do not overwrite $insert_data if its already set */
          if(!isset($insert_data[$meta_key])) {
            $insert_data[$meta_key] = $data['value'];
            continue;
          }

          //** Store data in meta table as well, as long as it's not already stored in main table */
          if($insert_data[$meta_key] != $data['value']) {
            //** Store any extra keys in values in regular data */
            $insert_custom_data[$meta_key][] = $data['value'];
          }

        }

        //** If the attribute is a meta key created  by WP-CRM, we store it here */
        if (in_array($meta_key, $wp_user_meta_data)) {

          switch ($wp_crm['data_structure']['attributes'][$meta_key]['input_type']) {

            case 'checkbox':
              if(!empty($data['option']) && $data['value'] == 'on') {

                //** get full meta key of option */
                $full_meta_key = $wp_crm['data_structure']['attributes'][$meta_key]['option_keys'][$data['option']];

                if(empty($full_meta_key)) {
                  $full_meta_key= $meta_key;
                }

                $insert_custom_data[$full_meta_key][] = 'on';

              }
            break;

            case 'dropdown':

              //** get full meta key of option */
              $full_meta_key = $wp_crm['data_structure']['attributes'][$meta_key]['option_keys'][$data['option']];

              if(empty($full_meta_key)) {
                $full_meta_key= $meta_key;
              }

              if(!empty($data['option'])) {
                $insert_custom_data[$full_meta_key][] = 'on';
              }

            break;

            default:

              //* Do not save empty values until this is being done on the profile editing page */
              if ( isset($data['value']) ) {
                if (!$args['admin_save_action'] && empty($data['value'])) {
                  continue;
                }
              }

              if($wp_crm['data_structure']['attributes'][$meta_key]['has_options']) {
                $full_meta_key = $wp_crm['data_structure']['attributes'][$meta_key]['option_keys'][$data['option']];

                if(empty($full_meta_key)) {
                  $full_meta_key= $meta_key;
                }

                $insert_custom_data[$full_meta_key][] = $data['value'];
              } else {
                $insert_custom_data[$meta_key][] = $data['value'];
              }

              break;
          }

        }

      }
    }

    //* Determine user_id */
    if(empty($temp_data['user_id'])) {
      if ($args['match_login'] == 'true' && (isset($user_data['user_login']) || isset($user_data['user_email']))) {

        $temp_data['user_login'] = WP_CRM_F::get_first_value($user_data['user_login']);
        $temp_data['user_email'] = WP_CRM_F::get_first_value($user_data['user_email']);

        //* Try to get ID based on login and email */
        if($temp_data['user_email']) {
          $insert_data['ID'] = username_exists($temp_data['user_email']);
        }
        //* Validate e-mail */
        if(empty($insert_data['ID'])) {
          $insert_data['ID'] = email_exists($temp_data['user_email']);
        }

      }
    } else {
      //** User ID was passed */
      $insert_data['ID'] = $temp_data['user_id'];
    }

    if(empty($insert_data['ID'])) {
      $new_user = true;
    }

    //** Set user_login from user_email or a guessed value if this is a new usr and user_login is not passed */
    if($new_user && empty($insert_data['user_login'])) {
      //** Try getting it from e-mail address */
      if(!empty($insert_data['user_email'])) {
        $insert_data['user_login'] = $insert_data['user_email'];
      } else {

        //** Try to guess user_login from first passed user value */
         if($user_login = WP_CRM_F::get_primary_display_value($user_data)) {
          $user_login = sanitize_user($user_login, true);
          $user_login = apply_filters('pre_user_login', $user_login);
          $insert_data['user_login'] = $user_login;
        }
      }
    }

    //** If password is passed, we hash it */
    if(empty($insert_data['user_pass'])) {
      //** Unset password to prevent it being cleared out */
      unset($insert_data['user_pass']);
    } else {
      //** We don't need to do it because wp_insert/update_user does it too! @author korotkov@ud */
      /*if($new_user) {
        $insert_data['user_pass'] = wp_hash_password($insert_data['user_pass']);
      }*/
    }

    //** Set default role if no role set and this isn't a new user */
    if(empty($insert_data['role']) && !isset($insert_data['ID'])) {
      $insert_data['role'] = $args['default_role'];
    }

    //** @author korotkov@ud */
    if( empty($insert_data['user_email']) ) {
      if( $wp_crm['configuration']['allow_account_creation_with_no_email'] == 'true' ) {
        $fake_user_email = rand(10000,99999) . '@' .  rand(10000,99999) . '.com';
        $insert_data['user_email'] = $fake_user_email;
      } else {
        WP_CRM_F::add_message( __('Error saving user: Email address cannot be empty', 'wp_crm'), 'bad');
        return false;
      }
    } else {
      if ( !filter_var($insert_data['user_email'], FILTER_VALIDATE_EMAIL) ) {
        WP_CRM_F::add_message( __('Error saving user: Email address is invalid', 'wp_crm'), 'bad');
        return false;
      }
    }

    //** Determine if data has user_nicename we should sanitize it. peshkov@UD */
    if(isset($insert_data['user_nicename'])) {
      $user_nicename = sanitize_title($insert_data['user_nicename']);
      if(empty($user_nicename)) unset($insert_data['user_nicename']);
      else $insert_data['user_nicename'] = $user_nicename;
    }

    //** Always update display name if its blank */
    if(empty($insert_data['display_name']) && isset($insert_data['user_email'])) {
      $insert_data['display_name'] = $insert_data['user_email'];
    }

    if($new_user) {
      $user_id = wp_insert_user($insert_data);

      //** If multisite - assign user to blog */
      if ( is_multisite() ) {
        global $blog_id;
        add_user_to_blog( $blog_id, $user_id, $insert_data['role'] );
      }
    } else {
      $user_id = wp_update_user($insert_data);
    }

    if(is_numeric($user_id)) {

      if(isset($fake_user_email)) {
        $wpdb->update($wpdb->users, array('user_email' => ''), array('ID' => $user_id));
      }

      //** Remove all old meta values if field is set (to avoid deleting unpasssed values) */
      foreach($wp_user_meta_data as $meta_key) {

        if(isset($insert_custom_data[$meta_key])) {
          delete_user_meta($user_id, $meta_key);
        }

        //** Delete old option meta keys for this meta_key  */
        if($wp_crm['data_structure']['attributes'][$meta_key]['has_options']) {
          //** Delete "holder" meta key (this may not be necessary */
          delete_user_meta($user_id, $meta_key);
          foreach($wp_crm['data_structure']['attributes'][$meta_key]['option_keys'] as $old_meta_key) {
            //** Delete individual long (optional) meta keys */
            delete_user_meta($user_id, $old_meta_key);
          }
        }
        /**
         * If attribute is just CHECKBOX (input type) and it doesn't store any predefined values
         * we have to remove it from usermeta on using admin user edit page, because
         * when attribute is not checked it's not set here (in funstion's params), so we MUSTN'T store t anymore.
         * peshkov@UD
         */
        elseif (isset($args['admin_save_action']) && $wp_crm['data_structure']['attributes'][$meta_key]['input_type'] == 'checkbox') {
          delete_user_meta($user_id, $meta_key);
        }

      }

      //** Add meta values */
      if(is_array($insert_custom_data) && !empty($insert_custom_data)) {
        foreach((array)$insert_custom_data as $meta_key => $meta_value) {
          foreach($meta_value as $single_value)  {
           add_user_meta($user_id, $meta_key, $single_value);
          }
        }
      }

      $display_name = WP_CRM_F::get_primary_display_value($user_id);

      if($display_name) {
        $wpdb->update($wpdb->users, array('display_name' => $display_name), array('ID' => $user_id));
      }

      if($new_user) {
        if($args['use_global_messages'] == 'true') {
          WP_CRM_F::add_message(__('New user added.', 'wp_crm'));
        }
      } else {
        if($args['use_global_messages'] == 'true') {
          WP_CRM_F::add_message(__('User updated.', 'wp_crm'));
        }
      }

      do_action('wp_crm_save_user', array(
        'user_id' =>$user_id,
        'insert_data' =>$insert_data,
        'insert_custom_data' =>$insert_custom_data,
        'args' => $args)
      );

      // Don't redirect if data was passed
      if($args['no_redirect'] != 'true') {
        if(!empty($_REQUEST['redirect_to'])) $url = urldecode($_REQUEST['redirect_to']) . "&message=updated";
        else $url = admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id&message=" . ($new_user ? 'created' : 'updated'));
        wp_redirect($url);
      }

    } else {
      if($args['use_global_messages'] == 'true') {
        switch($user_id->get_error_code()) {
          case 'existing_user_email':
            $existing_id = email_exists($insert_data['user_email']);
            WP_CRM_F::add_message(sprintf(__('Error saving user: %s', 'wp_crm'), $user_id->get_error_message() . ' <a href="' . admin_url("admin.php?page=wp_crm_add_new&user_id={$existing_id}"). '">'. __('Go to user profile','wp_crm') . '</a>'), 'bad');
          break;

          default:
            WP_CRM_F::add_message(sprintf(__('Error saving user: %s', 'wp_crm'), $user_id->get_error_message()), 'bad');
          break;
        }
      }
    }

    if($args['no_errors'] && is_wp_error($user_id)) {
      return false;
    }

    if($args['return_detail'] == 'true') {
      $return['user_id'] = $user_id;

      if($new_user) {
        $return['new_user'] = true;
      }

      return $return;
    }

    return $user_id;
  }

}  /* wp_crm_save_user_data */


if(!function_exists('wp_crm_add_to_user_log')) {

  /**
   * Updated user activity stream.
   *
   * @todo $args argument should be modifiable to pass extra data - such as importance of message. - potanin@UD
   * @hooked_into WP_CRM_Core::admin_head();
   * @since 0.1
   */
  function wp_crm_add_to_user_log($user_id, $message, $time = false, $args = false) {
    $insert_data = wp_parse_args( $args, array(
      'object_id' => $user_id,
      'attribute' => 'note',
      'text' => $message
    ));

    if($time) {
      $insert_data['time'] = $time;
    }

    if(WP_CRM_F::insert_event($insert_data)) {
      return true;
    }

    return false;
  }

}


//** Other Hooks with Core and With Plugins We Like */
add_action('wp_crm_associated_post_types', 'wpp_crm_associated_post_types', 0, 2);

function wpp_crm_associated_post_types($current, $post_type) {

  if($post_type == 'property') {
    return true;
  }

  return false;

}


