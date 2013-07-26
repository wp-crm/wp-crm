<?php
/**
 * WP-CRM General Functions
 *
 * Contains all the general functions used by the plugin.
 *
 * @version 0.01
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package WP-CRM
 * @subpackage Functions
 */

class WP_CRM_F {

  /**
   * Detailed Activity Log
   *
   * @todo Add geolocation service.
   * @todo Add caching for host resolution.
   */
  static function get_detailed_activity_log( $args = '' ) {
    global $wpdb, $wp_crm;

    $args = wp_parse_args( $args, array(
      'object_type' => 'user',
      'hide_empty' => false,
      'order_by' => 'time',
      'start' => '0',
      'import_count' => 500,
      'get_count' => 'false',
      'filter_types' => array(
        array(
          'attribute' => 'detailed_log',
          'other' => 2,
          'hidden' => 'false'
        )
      )
    ));

    $activity_log = WP_CRM_F::get_events( $args );

    $_resolved = array();
    $_locations = get_transient( '_wpc_geolocation' );

    if( !$_locations ) {
      $_update_cache = true;
    }

    foreach( (array) $activity_log as $count => $entry ) {

      $activity_log[ $count ]->display_name = get_userdata( $entry->object_id )->display_name;
      $activity_log[ $count ]->edit_url = admin_url( 'admin.php?page=wp_crm_add_new&user_id=' . $entry->object_id );
      $activity_log[ $count ]->time_stamp = strtotime( $entry->time );
      $activity_log[ $count ]->date = date( get_option( 'date_format', strtotime( $entry->time ) ) );
      $activity_log[ $count ]->time_ago = human_time_diff( strtotime( $entry->time ) ) . __( ' ago.', 'wpp' );

      switch( true ) {

        case $entry->attribute == 'detailed_log' && $entry->action == 'login':
          $activity_log[ $count ]->text = sprintf( __( 'Logged in from %1s.', 'wpp' ), $entry->value );

          if( function_exists( 'gethostbyaddr' )) {
            $activity_log[ $count ]->host_name = $_resolved[ $entry->value ] ? $_resolved[ $entry->value ] : $_resolved[ $entry->value ] = @gethostbyaddr( $entry->value );
          }

          if( $entry->value ) {
            $activity_log[ $count ]->location = $_locations[ $entry->value ] ? $_locations[ $entry->value ] : $_locations[ $entry->value ] = WP_CRM_F::get_service( 'geolocation', '', $entry->value, array( 'json' ) );
          }

        break;

        case $entry->attribute == 'detailed_log' && $entry->action == 'logout':
          $activity_log[ $count ]->text = sprintf( __( 'Logged out from %1s.', 'wpp' ), $entry->value );

          if( function_exists( 'gethostbyaddr' )) {
            $activity_log[ $count ]->host_name = $_resolved[ $entry->value ] ? $_resolved[ $entry->value ] : $_resolved[ $entry->value ] = @gethostbyaddr( $entry->value );
          }

          if( $entry->value ) {
            $activity_log[ $count ]->location = $_locations[ $entry->value ] ? $_locations[ $entry->value ] : $_locations[ $entry->value ] = WP_CRM_F::get_service( 'geolocation', '', $entry->value, array( 'json' ) );
          }

        break;

      }

    }

    if( $_update_cache && $_locations ) {
      set_transient( '_wpc_geolocation', $_locations, 3600 );
    }

    return $activity_log;

  }

  /**
   * Handler for general API calls to UD
   *
   * On Errors, the data response includes request URL, request body, and response headers / body.
   *
   * @updated 1.0.3
   * @since 1.0.0
   * @author potanin@UD
   */
  static function get_service( $service = false, $resource = '', $args = array(), $settings = array() ) {

    if( !$service ) {
      return new WP_Error( 'error', sprintf( __( 'API service not specified.' , UD_API_Transdomain ) ) );
    }

    $request = array_filter( wp_parse_args( $settings, array(
      'headers' => array(
        'Authorization' => 'Basic ' . base64_encode( 'api_key:' . get_option( '_ud::customer_key' ) ),
        'Accept' => 'application/json'
      ),
      'timeout' => 120,
      'stream' => false,
      'sslverify' => false
    )));

    foreach( (array) $settings as $set ) {

      switch( $set ) {

        case 'json':
          $request[ 'headers' ][ 'Accept' ] = 'application/json';
        break;

        case 'encrypted':
          $request[ 'headers' ][ 'Encryption' ] = 'Enabled';
        break;

        case 'xml':
          $request[ 'headers' ][ 'Accept' ] = 'application/xml';
        break;

      }

    }

    if( !empty( $request[ 'filename' ] ) && file_exists( $request[ 'filename' ] ) ) {
      $request[ 'stream' ] = true;
    }

    $response = wp_remote_get( $request_url = 'http://api.usabilitydynamics.com/' . $service . '/' . $resource . ( is_array( $args ) ? '?' . build_query( $args ) : $args ), $request );

    if( !is_wp_error( $response ) ) {

      /** If content is streamed, must rely on message codes */
      if( $request[ 'stream' ] ) {

        switch( $response[ 'response' ][ 'code' ] ) {

          case 200:
            return true;
          break;

          default:
            unlink( $request[ 'filename' ] );
            return false;
          break;
        }

      }

      switch( true ) {

        case ( intval( $response[ 'headers' ][ 'content-length' ] ) === 0 ):
          return new WP_Error( 'UD_API::ger_service' , __( 'API did not send back a valid response.' ), array(
            'request_url' => $request_url,
            'request_body' => $request,
            'headers' => $response[ 'headers' ],
            'body' => $response[ 'body' ]
          ));
        break;

        case ( $response[ 'response' ][ 'code' ] == 404 ):
          return new WP_Error( 'ud_api', __( 'API Not Responding. Please contact support.' ), array(
            'request_url' => $request_url,
            'request_body' => $request,
            'headers' => $response[ 'headers' ]
          ));
        break;

        case ( strpos( $response[ 'headers' ][ 'content-type' ], 'text/html' ) !== false ):
          return new WP_Error( 'UD_API::ger_service',  __( 'Unformatted API Response: ' ) . $response[ 'body' ], array(
            'request_url' => $request_url,
            'request_body' => $request,
            'headers' => $response[ 'headers' ]
          ));
        break;

        case ( strpos( $response[ 'headers' ][ 'content-type' ], 'application/json' ) !== false ):
          $json = json_decode( $response[ 'body' ] );
          return $json->success === false ? new WP_Error( 'UD_API::ger_service', $json->message, $json->data ) : $json;
        break;

        case ( strpos( $response[ 'headers' ][ 'content-type' ], 'application/xml' ) !== false ):
          return $response[ 'body' ];
        break;

        default:
          return new WP_Error( 'ud_api', __( 'An unknown error occurred while trying to make an API request to Usability Dynamics. Please contact support.' ) );
        break;

      }

    }

    if( is_file( $request[ 'filename' ] ) ) {
      unlink( $request[ 'filename' ] );
    }

    return is_wp_error( $response) ? $response : new WP_Error( 'error', sprintf( __( 'API Failure: %1s.' , UD_API_Transdomain ), $response[ 'response' ][ 'message' ] ));

  }


  /**
   * Get details about an attribute.
   *
   * @version 1.30.2
   */
  function get_attribute($attribute = false) {
    global $wp_crm;

    if(!$attribute) {
      return;
    }

    $user_table_keys = array(
      'ID',
      'user_login',
      'user_pass',
      'user_nicename',
      'user_email',
      'user_url',
      'user_registered',
      'user_activation_key',
      'user_status',
      'display_name'
    );

    //** Try to get data from settings */
    $return = (array) $wp_crm['data_structure']['attributes'][$attribute];

    $return['key'] = $attribute;

    if(in_array($attribute, $user_table_keys)) {
      $return['storage_type'] = 'user_table';
    } else {
      $return['storage_type'] = 'meta_table';
    }

    return apply_filters('wp_crm_attribute_data', $return);

  }



  /**
   * Track detailed activity such as logins and password resets.
   *
   * @version 1.17.3
   */
  function track_detailed_user_activity() {

    add_action('password_reset', create_function('$user', '  WP_CRM_F::insert_event(array("object_id"=> $user->ID, "attribute" => "detailed_log", "other" => 5, "action" => "password_reset")); '));
    add_action('wp_login', create_function('$user_login', ' $user = get_userdatabylogin($user_login);  WP_CRM_F::insert_event(array("object_id"=> $user->ID, "attribute" => "detailed_log", "other" => 2, "action" => "login")); '));

  }



  /**
   * Makes sure the script is loaded, otherwise loads it
   *
   * @version 1.17.3
   */
  function force_script_inclusion($handle = false) {
    global $wp_scripts;

    //** WP 3.3+ allows inline wp_enqueue_script(). Yay. */
    wp_enqueue_script($handle);

    if(!$handle) {
      return;
    }

    //** Check if already included */
    if(wp_script_is($handle, 'done')) {
      return true;
    }

    //** Check if script has dependancies that have not been loaded */
    if(is_array($wp_scripts->registered[$handle]->deps)) {
      foreach($wp_scripts->registered[$handle]->deps as $dep_handle) {
        if(!wp_script_is($dep_handle, 'done')) {
          $wp_scripts->in_footer[] = $dep_handle;
        }
      }
    }
    //** Force script into footer */
    $wp_scripts->in_footer[] = $handle;
  }



  /**
   * Makes sure the style is loaded, otherwise loads it
   *
   * @param string $handle registered style's name
   * @author Maxim Peshkov
   */
  function force_style_inclusion($handle = false) {
    global $wp_styles;
    static $printed_styles = array();

    if(!$handle) {
      return;
    }
    //** Check if already included */
    if(wp_style_is($handle, 'done') || isset($printed_styles[$handle])) {
      return true;
    } else {
      $printed_styles[$handle] = true;
      wp_print_styles($handle);
    }
  }


  /**
   * Scans through filters to see if anything is hooked into the regular profile page.
   *
   * personal_options traditionally appears towards the top of the profile before visual editor selector
   * show_user_profile and edit_user_profile traditionally appear below all the user meta fields
   * profile_personal_options appears before the user meta editing table
   *
   * @since 0.22
   *
   */
  function crm_profile_page_metaboxes() {
    global $wp_filter, $wp_crm_user;

    $user_id = $wp_crm_user['ID']['default'][0];
    $requested_user = $_GET['user_id'];

    if($user_id == $requested_user) {
      $own_profile = true;
    }

        /* All profiles */
    if(count($wp_filter['personal_options'])) {
      add_meta_box( 'wp_crm_personal_options', __('Personal Options', 'wp_crm') , array('WP_CRM_F', 'personal_options') , 'crm_page_wp_crm_add_new', 'normal', 'default');
    }

    /* Non-self profile */
    if(!$own_profile && count($wp_filter['edit_user_profile'])) {
      add_meta_box( 'wp_crm_edit_self_profile',  __('Additional Settings', 'wp_crm'), array('WP_CRM_F', 'edit_user_profile') , 'crm_page_wp_crm_add_new', 'normal', 'default');
    }

    /* Self Profile  - Included into Special Actions box */
    if($own_profile && (count($wp_filter['show_user_profile']) || count($wp_filter['profile_personal_options']))) {
      //add_meta_box( 'wp_crm_edit_user_profile',  __('Additional Settings', 'wp_crm') , function($object) {  do_action('edit_user_profile'); do_action('profile_personal_options'); }, 'crm_page_wp_crm_add_new', 'normal', 'default');
    }

  }


  /**
   * Load third-party plugin compatibility
   *
   * Cycle through /connections/ folder and load any files for installed plugins.
   *
   * @since 0.21
   *
   */
  function load_plugin_compatibility() {

    $asset_directories = array(WP_CRM_Connections);

    //** Load any existing assets for active plugins */
    foreach ( wp_get_active_and_valid_plugins() as $plugin_path ) {

      $plugin_slug = basename(plugin_basename( trim( dirname($plugin_path) ) ));

      //** Get plugin name from directory name, or file name (if plugin has no directory and is in root) */
      if($plugin_slug == 'plugins' || empty($plugin_slug)) {
        $plugin_slug = basename(plugin_basename( trim( $plugin_path ) ));
      }

      //** Look for plugin-specific styles and load them */
      foreach($asset_directories as $directory) {

        $file_path = trailingslashit($directory) . $plugin_slug . '.php';

        if(file_exists($file_path)) {

          if(WP_DEBUG == true) {
            include_once($file_path);
          } else {
            @include_once($file_path);
          }

        }

      }

    }

  }


  /**
   * Return an array of primary attributes.
   *
   * @since 0.21
   *
   */
    function get_primary_attributes() {
      global $wp_crm;

      if(!is_array($wp_crm['data_structure']['attributes'])) {
        return false;
      }

      $primary = array();

      foreach($wp_crm['data_structure']['attributes'] as $slug => $data) {

        if($data['primary'] == 'true') {
          $primary[$slug] = $data;
        }

      }

      if(is_array($primary)) {
        return $primary;
      }

      return false;

    }


    /**
     * Handles Password Reset notification if the default WP reset email is disabled.
     *
     * @since 0.30.3
     * @author potanin@UD
     */
    function retrieve_password( $user_login ) {
      global $wp_crm, $wpdb;

      if($wp_crm['configuration']['disable_wp_password_reset_email'] != 'true') {
        return;
      }

      $user_data = get_user_by('login', $user_login );

      if( !$user_data ) {
        return false;
      }

      $user_id = $user_data->ID;
      $user_login = $user_data->data->user_login;
      $user_email = $user_data->data->user_email;

      $allow = apply_filters('allow_password_reset', true, $user_data->ID);

      if ( $allow ) {

        $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM {$wpdb->users} WHERE user_login = %s", $user_login));

        if ( empty($key) ) {
          $key = wp_generate_password(20, false);
          $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
        }

        //** Build default notification arguments */
        foreach($wp_crm['data_structure']['attributes'] as $attribute => $attribute_data) {
          $notification_info[$attribute]  = wp_crm_get_value($attribute, $user_id);
        }

        $notification_info['reset_url'] = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

        if( !wp_crm_send_notification( 'password_reset' , $notification_info ) ) {
          wp_crm_add_to_user_log( $user_id, __('User attempted to reset password, but reset email could not be sent.', 'wp_crm') );

        } else {
          wp_crm_add_to_user_log( $user_id, __('Password reset initiated by user, email sent with a password reset link.', 'wp_crm') ) ;

        }

      }

    }

    /**
     * A callback function for action 'phpmailer_init'
     * @param type $phpmailer
     * @author odokienko@UD
     */
    function shortcode_form_send_notification($phpmailer){
      //var_dump($phpmailer);
    }

    /**
     * Disable the standard WordPress password reset e-mail by blanking out the message.
     *
     * @since 0.21
     *
     */
    function retrieve_password_message($message) {
      global $wp_crm, $wpdb;

      if($wp_crm['configuration']['disable_wp_password_reset_email'] == 'true') {

        //** Returning false disabled the built-in WP message sending notification */
        return false;

      }

      return $message;

    }

    /**
     * Draw a dropdown of available user roles.
     *
     * @since 0.21
     *
     */
    function wp_dropdown_roles($args = false) {
      $p = '';
      $r = '';

      $args = wp_parse_args( $args, array(''));

      $editable_roles = get_editable_roles();

      foreach ( $editable_roles as $role => $details ) {
        $name = translate_user_role($details['name'] );
        if ( $selected == $role ) // preselect specified role
          $p = "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
        else
          $r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
      }

      return '<select class="'.$args['class'].'"><option></option>' . $p . $r . '</select>';

    }


/**
   * Gets user e-mail.
   *
   * This function is here because multiple fields allow e-mails, and eventually we'll need to be able to select the correct one.
   *
   * @since 0.21
   *
   */
    function get_user_email($user_id = false, $args = false) {
      global $wpdb;

      $args = wp_parse_args( $args, array(''));

      $user_email = $wpdb->get_var("SELECT user_email FROM {$wpdb->users} WHERE ID = {$user_id}");

      $user_email = apply_filters('wp_crm_get_user_email', $user_email,  array('user_id' => $user_id));

      if($user_email) {
        return $user_email;
      }

      return false;

   }

   function check_email_for_duplicates($email,$user_id){
     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       return __( "Invalid email", 'wp_crm' );
     }

     $id = email_exists($email);

     if ($id===false || $id == $user_id) {
       return "Ok";
     }

     if (
        $id && (                              /* Duplicate have found */
          !is_numeric($user_id) ||            /* Either we have new user */
          ($id != $user_id)                   /* or we've found duplicate not for current user_id */
        )
     ){
       return __( "Email already exists", 'wp_crm' );
     }

     return false;
   }


/**
   * Checks a field for conflicts
   *
   * @since 0.21
   *
   */
   function check_data_field($key = false, $value = false) {
    global $wpdb;

    if(!$key || !$value) {
      return false;
    }

    //** Check primary table */
    if($user_id = $wpdb->get_var("SELECT ID FROM {$wpdb->users} WHERE {$key} = '{$value}'")) {
      return $user_id;
    }

    //** Check meta fields */
    if($user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$key}' AND meta_value = '{$value}'")) {
      return $user_id;
    }


    return false;

   }


/**
   * Returns user's information as set in Overview Page User Card
   *
   * @since 0.21
   *
   */
   function render_user_card($args = false) {
    global $wp_crm,$current_user;

    if(!$args) {
      return;
    }

    $defaults = array(
      'avatar_width' => 50,
      'do_not_display_user_avatars' => ($wp_crm['configuration']['do_not_display_user_avatars'] == 'true' ? true : false),
      'show_user_actions' => false
    );

    $args = wp_parse_args( $args, $defaults);

    extract($args);

    if(!isset($user_object)) {
      $user_object = wp_crm_get_user($user_id);
    }

    if($show_user_actions && is_array($wp_crm['overview_user_actions'])) {
      foreach($wp_crm['overview_user_actions'] as $action => $data) {
        if($data['enable'] == 'true') {

          $html = $data['label'];

          if ($action=='reset_password' && $current_user->ID==(int)$user_id){
            continue;
          }

          //** Apply filters to action */
          $action = apply_filters('wp_crm_user_action', array(
            'html' => $html,
            'action' => $action,
            'data' => $data,
            'user_object' => $user_object,
            'user_id' => $user_id)
          );

          $user_actions[] = '<li class="wp_crm_user_action" user_id="' . $action['user_id']  . '" action="' . $action['action'] . '">' . $action['html'] . '</li>';
        }
      }
    }

     //** Get selected attributes from Settings page */
    $user_card_attributes = $wp_crm['configuration']['overview_table_options']['main_view'];

    //** Load Default user card values to avoid having blank user cards */
    if(!is_array($user_card_attributes)) {
      $user_card_attributes[] = 'display_name';
      $user_card_attributes[] = 'user_email';
    }

    ob_start();
    ?>

    <?php if(!$do_not_display_user_avatars) { ?>
    <div class='user_avatar'>
      <?php if(current_user_can('WP-CRM: View Profiles')) { ?>
        <a href='<?php echo admin_url("admin.php?page=wp_crm_add_new&user_id={$user_id}"); ?>'><?php echo get_avatar( $user_id, $avatar_width ); ?></a>
      <?php } else { ?>
        <?php echo  get_avatar( $user_id, $avatar_width ); ?>
      <?php } ?>
    </div>
    <?php } ?>

    <div class="user_card_inner_wrapper">
      <ul class="user_card_data">
        <li class='primary'>
          <?php if(current_user_can('WP-CRM: View Profiles')) { ?>
          <a href='<?php echo admin_url("admin.php?page=wp_crm_add_new&user_id={$user_id}"); ?>'><?php echo WP_CRM_F::get_primary_display_value($user_object); ?></a>
          <?php } else { ?>
          <?php echo WP_CRM_F::get_primary_display_value($user_object); ?>
          <?php } ?>
        </li>
        <?php foreach($user_card_attributes as $key) { ?>
          <li class="<?php echo $key; ?>">
            <?php

              unset($visible_options);

              if($wp_crm['data_structure']['attributes'][$key]['has_options']) {
                $visible_options = WP_CRM_F::list_options($user_object, $key);
              } else {
                $visible_options[] = apply_filters('wp_crm_display_' . $key, WP_CRM_F::get_first_value($user_object[$key]),$user_id, $user_object,  'user_card');
              }

              if(is_array($visible_options)) {
                foreach($visible_options as $this_key => $option) {
                  if(CRM_UD_F::is_url($option)) {
                    $visible_options[$this_key] = "<a href='$option'>$option</a>";
                  }
                }
              }

               if(is_array($visible_options)) {
                echo '<ul><li>' . implode('</li><li>', $visible_options) . '</li></ul>';
              }

            ?></li>
        <?php } ?>
      </ul>

   <?php  if(is_array($user_actions)) { echo '<ul class="wp_crm_user_row_actions">' . implode('<li class="wp_crm_divider"> | </li>', $user_actions) . '</ul>'; }  ?>
   </div>
    <?php

    $content = ob_get_contents();
    ob_end_clean();

    return $content;


   }




/**
   * Visualize quantifiable data
   *
   * @todo There may be an issue with overlapping attributes for certain users, as unaccounted_for sometimes results in a negative number
   * @since 0.19
   *
   */
  function visualize_results($filters) {
    global $wpdb;

    parse_str($filters, $filters);
    $wp_crm_search = $filters['wp_crm_search'];

    //** Get users from filter query */
    $user_ids = WP_CRM_F::user_search($wp_crm_search, array('ids_only' => 'true'));

    $quantifiable_attributes = WP_CRM_F::get_quantifiable_attributes();

    if(!$quantifiable_attributes || !$user_ids) {
    return;
    }

    $user_id_query = ' user_id = ' . implode(' OR user_id = ', $user_ids);

    foreach($quantifiable_attributes as $attribute_slug => $attribute) {

      if(!empty($attribute['option_keys'])) {
        foreach($attribute['option_keys'] as $short_slug => $full_meta_key) {
          $this_count = $wpdb->get_var("SELECT count(DISTINCT(user_id)) FROM {$wpdb->usermeta} WHERE meta_key = '{$full_meta_key}' AND ({$user_id_query})");
          if(empty($this_count)) {
            continue;
          }
          $data[$attribute_slug]['counts'][$short_slug] = $this_count;
          $data[$attribute_slug]['labels'][$short_slug] = $attribute['option_labels'][$short_slug];
        }
      } else {
        $this_count = $wpdb->get_var("SELECT count(DISTINCT(user_id)) FROM {$wpdb->usermeta} WHERE meta_key = '{$attribute_slug}' AND ({$user_id_query})");
        if(empty($this_count)) {
          continue;
        }
        $data[$attribute_slug]['counts'][$short_slug] = $this_count;
        $data[$attribute_slug]['labels'][$short_slug] = $attribute['title'];
      }

      $data[$attribute_slug]['title'] = $attribute['title'];

      if(empty($data[$attribute_slug]['counts'])) {
        unset($data[$attribute_slug]);
      } else {
        //** Calculate "other" */
        $unaccounted_for = count($user_ids) - array_sum($data[$attribute_slug]['counts']);
        if($unaccounted_for > 0) {
          $data[$attribute_slug]['counts']['unaccounted_for'] = $unaccounted_for;
          $data[$attribute_slug]['labels']['unaccounted_for'] = __('Unaccounted', 'wp_crm');
        }
      }

    }


    if(empty($data)) {
      die('<div class="wp_crm_visualize_results no_data">' . __('There is not enough quantifiable data to generate any graphs.', 'wp_crm') . '</div>');
    }

    ?>
    <div class="wp_crm_visualize_results">
    <script type="text/javascript">

      jQuery(document).ready(function() {
        <?php foreach($data as $attribute_slug => $attribute_data){
          echo "wp_crm_attribute_{$attribute_slug}_chart();\r\n";
        }?>
      });

    <?php foreach($data as $attribute_slug => $attribute_data){ ?>
        function wp_crm_attribute_<?php echo $attribute_slug; ?>_chart() {

        var data = new google.visualization.DataTable({});
        data.addColumn('string', 'Attribute');
        data.addColumn('number', 'Count');
        data.addRows(<?php echo count($attribute_data['counts']); ?>);

        <?php $row = 0; foreach($attribute_data['counts'] as $short_slug => $count) { ?>

        data.setValue(<?php echo $row; ?>, 0, '<?php echo $attribute_data['labels'][$short_slug]; ?>');
        data.setValue(<?php echo $row; ?>, 1, <?php echo $count; ?>);
        <?php $row++; } ?>

        var chart = new google.visualization.PieChart(document.getElementById('wp_crm_attribute_<?php echo $attribute_slug; ?>_chart'));
        chart.draw(data, {
          backgroundColor: '#F7F7F7',
          is3D: true,
          chartArea: {width:"60%",height:"90%"},
          width: 380,
          height:310,
          legend: 'bottom'
        });


        }
    <?php } ?>
     </script>

     <?php foreach($data as $attribute_slug => $attribute_data){ ?>
        <div class="wp_crm_chart_wrapper">
        <span class="wp_crm_chart_title"><?php echo $attribute_data['title']; ?></span>
        <div id="wp_crm_attribute_<?php echo $attribute_slug; ?>_chart" class="wp_crm_visualization_graph"></div>
        </div>
      <?php } ?>


    </div>

    <?php
   }



/**
   * Return information about quantifiable attributes
   *
   *
   * @since 0.19
   *
   */
   function get_quantifiable_attributes() {
    global $wp_crm;

    if($cache = wp_cache_get('wp_crm_quantifiable_attributes')) {
      return $cache;
    }

    $quantifiable_fields = array('checkbox', 'dropdown');

    foreach($wp_crm['data_structure']['attributes'] as $attribute_slug => $attribute_data) {

        if(in_array($attribute_data['input_type'], $quantifiable_fields)) {

          $return[$attribute_slug] = $attribute_data;
        }

    }

    if(!is_array($return)) {
      $return = array();
    }

    wp_cache_add('wp_crm_quantifiable_attributes', $return);

    return $return;


   }


/**
   * Handle version-specific updates
   *
   * Ran if version in DB is older than version of THIS code right before the DB version is updated.
   * Reference readme.txt to see details of updates.
   *
   * @since 0.1
   *
   */
   function handle_update($old_version) {
    global $wp_roles;

    if( !$wp_roles ) {
      return;
    }

    $roles = $wp_roles->get_names();

    switch (true) {

      case $old_version <  0.17:

        foreach($roles as $role => $role_label) {
          $wp_roles->remove_cap( $role, 'wp_crm_manage_settings' );
          $wp_roles->remove_cap( $role, 'wp_crm_add_prospects' );
          $wp_roles->remove_cap( $role, 'wp_crm_view_main_overview' );
          $wp_roles->remove_cap( $role, 'wp_crm_manage_settings' );
          $wp_roles->remove_cap( $role, 'wp_crm_view_messages' );
          $wp_roles->remove_cap( $role, 'wp_crm_add_users' );
          $wp_roles->remove_cap( $role, 'wp_crm_Manage Settings' );
        }

      break;

      case $old_version < 0.31:

        if(is_object($wp_roles)) {
          foreach($wp_roles->roles as $role => $role_data) {
            if(is_array($role_data['capabilities']) && array_key_exists('edit_users', $role_data['capabilities'])) {
              $wp_roles->add_cap($role, 'WP-CRM: Change Passwords', true);
              $wp_roles->add_cap($role, 'WP-CRM: Change Color Scheme', true);
            }
          }
        }

      break;

    }



  }


/**
   * Loads currently requested user into global variable
   *
   * Ran on admin_init. Currently only applicable to the user profile page in order to load metaboxes early based on available user data.
   *
   * @since 0.1
   *
   */
    static function get_notification_template($slug = '') {
      global $wp_crm;

      if(!empty($wp_crm['notifications'][$slug])) {
        return json_encode($wp_crm['notifications'][$slug]);;
      } else {
        return json_encode(array('error' => __('Notification template not found.', 'wp_crm')));
      }
    }



/**
   * Loads currently requested user into global variable
   *
   * Ran on admin_init. Currently only applicable to the user profile page in order to load metaboxes early based on available user data.
   *
   * @since 0.1
   *
   */
    static function csv_export($wp_crm_search = '') {
      global $wpdb, $wp_crm;

      $file_name = "wp-crm-export-".date("Y-m-d").".csv";

      $meta_keys = $wp_crm['data_structure']['meta_keys'];

      $primary_columns = $wpdb->get_col("SHOW COLUMNS FROM {$wpdb->users}");

      $results = WP_CRM_F::user_search($wp_crm_search);

      foreach($results as $result) {

        $primary = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = {$result->ID}", ARRAY_A);

        foreach($meta_keys as $meta_key => $meta_label) {

          $meta_key_labels[] = $meta_label;

          if(in_array($meta_key, $primary_columns)) {
            $value = $primary[$meta_key];
          } else {
            $value = get_user_meta($result->ID, $meta_key, true);
          }

          if(!empty($value)) {
            $display_columns[$meta_key] = $meta_label;
          }

          $user[trim($meta_key)] = trim($value);

        }

        $users[] = $user;

      }

      header("Content-type: application/csv");
      header("Content-Disposition: attachment; filename=$file_name");
      header("Pragma: no-cache");
      header("Expires: 0");

      echo implode(',', $display_columns) . "\n";

      foreach($users as $user) {
        unset($this_row);
        foreach($display_columns as $meta_key => $meta_label) {
          $this_row[] = '"' . $user[$meta_key] . '"';
        }
        echo implode(",", $this_row) . "\n";
      }



    }




  /**
   * Loads currently requested user into global variable
   *
   * Ran on admin_init. Currently only applicable to the user profile page in order to load metaboxes early based on available user data.
   *
   * @param $user_id [optional] Default is got from $_REQUEST['user_id']
   * @param $manually [optional] Default is false.
   * @since 0.1
   *
   */
  static function maybe_load_profile($user_id = false, $manually = false) {
    global $wp_crm_user;

    $user_id = $user_id ? $user_id : (isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : false);

    if( $manually || ($_GET['page'] == 'wp_crm_add_new' && !empty($user_id)) )  {
      $maybe_user = wp_crm_get_user($user_id);

      if($maybe_user) {
        $wp_crm_user = $maybe_user;
        do_action('wp_crm_user_loaded', $wp_crm_user);
      } else {
        $wp_crm_user = false;
      }
    }
  }



    /**
     * Outputs user options as list.
     *
     * @since 0.1
     *
    */
    static function list_options($user_object, $column_name, $args = '') {
      global $wp_crm;

      if(!is_array($user_object[$column_name])) {
        return;
      }
      foreach($user_object[$column_name] as $option_type_slug => $option_type_values) {

        foreach($option_type_values as $single_option_value) {
          if($single_option_value == 'on') {
            $return[] = $wp_crm['data_structure']['attributes'][$column_name]['option_labels'][$option_type_slug];
          }
        }

      }

      return $return;
    }


    /**
     * Generate fake users
     *
     * This function is mostly for development.
     *
     * @todo Add function to remove dummy users.
     * @since 0.1
     *
    */
    static function do_fake_users($args = '') {
      global $wp_crm, $wpdb;

      $defaults = array(
        'number' => 5,
        'do_what' => 'generate'
      );

      $full_meta_keys = $wp_crm['data_structure']['full_meta_keys'];

      $args = wp_parse_args( $args, $defaults );
      $count = 0;

      if($args['do_what'] == 'generate') {

        $names = array('Gilbert', 'James', 'Anthony', 'Mark', 'Kimberly',  'John', 'Bill', 'Randy', 'Mary', 'Jenna', 'Beth', 'Allyson', 'Samantha', 'Davis', 'Roberts', 'Campbell', 'Edwards', 'Martinez');
        $emails = array('gmail.com', 'yahoo.com', 'msn.com', 'acme.com', 'xyz.com', 'mac.com', 'microsoft.com', 'google.com');
        $words = explode(' ', 'Nunc vel augue diam Duis nec magna justo; eget mollis Cras nibh lectus mattis malesuada mattis metus Quisque scelerisque neque auctor vehicula justo odio dapibus semquis eleifend eros lectus eget odio! Donec sollicitudin; orciet cursus malesuada ligula nunc iaculis ligula eu suscipit diam purus inligula Class aptent taciti sociosqu ad litora torquent per conubia nostra per inceptos himenaeos In hac habitasse platea dictumst Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Cras sed dolorrhoncus malesuada Mauris lacus nibh fringilla iddictum venenatis anisl Morbi euismod turpis vitae tellus sagittis tristique erat iaculis Nam vel massa arcu Sed vehicula porttitor imperdiet');

        shuffle($words);

        while ($count <= ($args['number'] - 1)) {
          $count++;

          //** Change up order for each fake user */
          CRM_UD_F::shuffle_assoc($full_meta_keys);

          $meta_data = array();
          $done_attributes = array();

          $user_data['first_name'] = $names[array_rand($names, 1)];
          $user_data['last_name'] = $names[array_rand($names, 1)];
          $user_data['display_name'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
          $user_data['user_email'] = $user_data['first_name'] . '.' . $user_data['last_name'] . '@' . $emails[array_rand($emails, 1)];
          $user_data['user_login'] = $user_data['user_email'];

          $main_keys = array_keys($user_data);

          foreach($full_meta_keys as $full_key => $short_key) {

            //** reset counts */
            $i = 0;
            $fake_messages = 0;

            $this_attribute = $wp_crm['data_structure']['attributes'][$short_key];

            if($this_attribute['has_options'] && $full_key == $short_key) {
              continue;
            }

            //** 50/50 we put something into here, unless required or stored in main user table */
            if(in_array($short_key, $main_keys) || (mt_rand(0,1) == 1 && $this_attribute['required'] != 'true')) {
              continue;
            }

            switch ($this_attribute['input_type']) {

              case 'textarea':

                while ($i <= mt_rand(15,100)) {
                  $meta_data[$full_key] .= $words[array_rand($words, 1)] . ' ';
                  $i++;
                }

                $done_attributes[] = $short_key;

              break;


              case 'text':

                while ($i <= mt_rand(3,7)) {
                  $meta_data[$full_key] .= $words[array_rand($words, 1)] . ' ';
                  $i++;
                }

                $done_attributes[] = $short_key;

              break;

              case 'dropdown':

                if(!in_array($short_key, $done_attributes)) {
                  $meta_data[$full_key] = 'on';
                  $done_attributes[] = $short_key;
                }

              break;

              case 'checkbox':
                $meta_data[$full_key] = 'on';
                $done_attributes[] = $short_key;
              break;

            }

          }

          $user_id = wp_insert_user($user_data);

          $meta_data['wp_crm_fake_user'] = true;

          if($user_id && !is_wp_error($user_id)) {

            foreach($meta_data as $meta_key => $meta_value) {
              update_user_meta($user_id,  $meta_key, trim($meta_value));
            }

            $generated_users[] = $user_id;

            while ($fake_messages <= mt_rand(2,10)) {

              $fake_message = '';
              $fake_words = 1;

              while ($fake_words <= mt_rand(50,300)) {
                $fake_message .= $words[array_rand($words, 1)] . ' ';
                $fake_words++;
              }

              trim($fake_message);

              if(!empty($fake_message)) {
                wp_crm_add_to_user_log($user_id, $fake_message);
              }

              echo "Adding message to $user_id : " . strlen($fake_message) . " \n";

              $fake_messages++;

            }


            //** Delete e-mails from some users */
            if($wp_crm['configuration']['allow_account_creation_with_no_email'] == 'true' && mt_rand(0,10) > 7) {
              $wpdb->update($wpdb->users, array('user_email' => ''), array('ID' => $user_id));
            }

          } else {
            /* echo $user_id->get_error_message(); */
          }

        }

        echo 'Generated ' . count($generated_users) . ' fake users. User IDs: ' . print_r($generated_users, true);

      }

      if($args['do_what'] == 'remove') {

        //** Get all fake users */
        $fake_users = $wpdb->get_col("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'wp_crm_fake_user' AND meta_value =1");

        if($fake_users) {
          foreach($fake_users as $user_id) {
            if(wp_delete_user($user_id)) {
              $deleted_user[] = true;
            }
          }
          $deleted_user = count($deleted_user);
          echo "Fake users found. Deleted {$deleted_user} fake user(s)";

        } else {
          echo __('No fake users found.', 'wp_crm');
        }


      }

    }

    function edit_profile_url($url, $user, $scheme){
      global $wp_crm;

      if($wp_crm['configuration']['replace_default_user_page'] == 'true' && current_user_can('edit_users') ) {
        $url = admin_url("admin.php?page=wp_crm_my_profile");
      }
      return $url;
    }


    /**
     * Performs a user search
     *
     * @since 0.1
     */
    static function user_search($search_vars = false, $args = array()) {
      global $wp_crm, $wpdb, $blog_id;

      $args = wp_parse_args( $args, array(
        'select_what' => '*',
        'ids_only' => 'false'
      ));

      if($args['ids_only'] == 'true') {
        $args['select_what'] = 'ID';
      }

      $sort_by = ' ORDER BY user_registered DESC ';
      /** Start our SQL, we include the 'WHERE 1' to avoid complex statements later */

      $sql = "SELECT {$args[select_what]} FROM {$wpdb->users} AS u WHERE 1";

      if(!empty($search_vars) && is_array($search_vars)) {
        foreach($search_vars as $primary_key => $key_terms) {

          //** Handle search_string differently, it applies to all meta values */
          if($primary_key == 'search_string') {
            /* First, go through the users table */
            $tofind = trim(strtolower($key_terms));
            if ($tofind){
              $sql .= " AND (";
              $sql .= " u.ID IN (SELECT ID FROM {$wpdb->users} WHERE LOWER(display_name) LIKE '%$tofind%' OR LOWER(user_email) LIKE '%$tofind%')";
              /* Now go through the users meta table */
              $sql .= " OR u.ID IN (SELECT user_id FROM {$wpdb->usermeta} WHERE LOWER(meta_value) LIKE '%$tofind%')";
              $sql .= ")";
            }
            continue;
          }

          //** Handle role filtering differently too*/
          if($primary_key == 'wp_role') {
            $sql .= " AND (";
            unset($or);
            foreach($key_terms as $single_term) {
              $or = (isset($or) ? " OR " : "");
              $sql .= "{$or}u.ID IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value LIKE '%{$single_term}%')";
            }
            $sql .= ")";
            continue;
          }

          //** Build array of actual meta keys and values ot look for */
          if(is_array($key_terms)) {
            /** Anything in here is required for the user (it's an OR), so we enclose our statement with () */
            $sql_option_array = array();
            foreach($key_terms as $single_term) {
              $meta_key = $wp_crm['data_structure']['attributes'][$primary_key]['option_keys'][$single_term];
              $sql_option_array[] = "u.ID IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$meta_key}' AND (meta_value = 'on' OR meta_value = 'true'))";

            }
            if ($sql_option_array){
              $sql .= " AND (".  implode(' or ', $sql_option_array).")";
            }
          }else{

            if ($wp_crm['data_structure']['attributes'][$primary_key]['input_type']=='checkbox'){
              $sql .= " AND u.ID IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$primary_key}' AND (meta_value = 'on' OR meta_value = 'true'))";
            }
          }

        }
      }

      //** Multi site fix */
      $id = get_current_blog_id();
      $blog_prefix = $wpdb->get_blog_prefix($id);
      $sql .= " AND u.ID IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$blog_prefix}capabilities' )";

      $sql = $sql . $sort_by;

      if($args['ids_only'] == 'true') {
        $results = $wpdb->get_col($sql);
      } else {
        $results = $wpdb->get_results($sql);
      }

      return $results;
    }


 /**
    * Draws table rows for ajax call
    *
    *
    * @since 0.1
    *
    */
    static function ajax_table_rows($wp_settings = false) {

      include WP_CRM_Path . '/core/class_user_list_table.php';

      //** Get the paramters we care about */
      $sEcho = $_REQUEST['sEcho'];
      $per_page = $_REQUEST['iDisplayLength'];
      $iDisplayStart = $_REQUEST['iDisplayStart'];
      $iColumns = $_REQUEST['iColumns'];

      //** Parse the serialized filters array */
      parse_str($_REQUEST['wp_crm_filter_vars'], $wp_crm_filter_vars);
      $wp_crm_search = $wp_crm_filter_vars['wp_crm_search'];

      //* Init table object */
      $wp_list_table = new CRM_User_List_Table("ajax=true&per_page={$per_page}&iDisplayStart={$iDisplayStart}&iColumns={$iColumns}");

      $wp_list_table->prepare_items($wp_crm_search);

      if ( $wp_list_table->has_items() ) {

        foreach ( $wp_list_table->items as $count => $item ) {
          $data[] = $wp_list_table->single_row( $item );
        }

      } else {
        $data[] = $wp_list_table->no_items();
      }

      return json_encode(array(
        'sEcho' => $sEcho,
        'iTotalRecords' => count($wp_list_table->all_items),
        'iTotalDisplayRecords' =>count($wp_list_table->all_items),
        'user_ids' => $wp_list_table->user_ids,
        'page_user_ids' => $wp_list_table->page_user_ids,
        'aaData' => $data
        ));

    }


    /**
     * Generates all possible meta keys given the data structure
     *
     * Generates:
     * - attributes: option keys and option labels
     * - meta_keys: full meta keys to their labels
     * - full_meta_keys: relationship of full meta keys to attribute keys
     *
     * @since 0.1
     *
     */
    static function build_meta_keys($wp_settings = false) {
      global $wpdb;

      if(!$wp_settings) {
        global $wp_crm;
      } else {
        $wp_crm = $wp_settings;
      }

       foreach($wp_crm['data_structure']['attributes'] as $main_key => $attribute_data) {

         $meta_keys[$main_key] = $attribute_data['title'];
        $full_meta_keys[$main_key] = $main_key;

        if(!empty($attribute_data['options'])) {

          //** Watch for taxonomy: slug */
          if(strpos($attribute_data['options'], 'taxonomy:') !== false) {
            $source_taxonomy = trim(str_replace('taxonomy:', '', $attribute_data['options']));

            //** Load all taxonomy terms.  Cannot use get_terms() because this function is ran before most others run register_taxonomy() */
            $taxonomy_terms = $wpdb->get_results("SELECT tt.term_id, name, slug, description FROM {$wpdb->prefix}term_taxonomy tt LEFT JOIN {$wpdb->prefix}terms t ON tt.term_id = t.term_id WHERE taxonomy = '{$source_taxonomy}' AND name != ''");

            if($taxonomy_terms) {
              foreach($taxonomy_terms as $term_data) {
                 $exploded_array[] = $term_data->name;
              }
            }

          } else {

            if(strpos($attribute_data['options'], ',')) {
              $exploded_array = explode(',', $attribute_data['options']);
            } else {
              $exploded_array = array($attribute_data['options']);
            }

          }

          //** Go through every option and identify what meta_key it will use */
          foreach($exploded_array as $option_title) {
            $option_key = $main_key . '_option_' . sanitize_title_with_dashes($option_title);
            $meta_keys[$option_key] = $option_title;
            $full_meta_keys[$option_key] = $main_key;

            $wp_crm['data_structure']['attributes'][$main_key]['option_keys'][sanitize_title_with_dashes($option_title)] = $option_key;
            $wp_crm['data_structure']['attributes'][$main_key]['option_labels'][sanitize_title_with_dashes($option_title)] = trim($option_title);
          }

          if(!empty($wp_crm['data_structure']['attributes'][$main_key]['option_keys'])) {
            $wp_crm['data_structure']['attributes'][$main_key]['has_options'] = true;
          }


        } else {
          unset($wp_crm['data_structure']['attributes'][$main_key]['options']);
        }

       }

       $wp_crm['data_structure']['meta_keys'] = $meta_keys;
       $wp_crm['data_structure']['full_meta_keys'] = $full_meta_keys;


       return $wp_crm['data_structure'];


    }


  /**
   * Handle "quick actions" via ajax
   *
   * Return json instructions on next action
   *
   * @since 0.1
   *
   */
  function show_user_meta_report($user_id = false) {
    global $wpdb;

    if($user_id = intval($user_id)) {
      $user_specific_query = " AND user_id = '$user_id' ";
    }

    $exclude_prefices = array('screen_layout_', 'meta-box-order_', 'metaboxhidden', 'closedpostboxes_', 'managetoplevel_', 'manageedi');
    $excluded_keys = implode("%' AND meta_key NOT LIKE '" ,  $exclude_prefices);

    //* get all user meta keys */
    $meta_keys = $wpdb->get_col("SELECT DISTINCT(meta_key) FROM {$wpdb->usermeta} WHERE (meta_key NOT LIKE '{$excluded_keys}%') GROUP BY meta_key");

    foreach($meta_keys as $key) {


      if(!$typical_options = $wpdb->get_col("SELECT DISTINCT(meta_value) FROM {$wpdb->usermeta} WHERE meta_key = '$key'  AND meta_value != '' $user_specific_query LIMIT 0, 3 ")) {
        continue;
      }

      $return[$key] = implode(',', $typical_options);



    }

    return $return;


  }



  /**
   * Handle "quick actions" via ajax
   *
   * Return json instructions on next action.  User by several JS functions.
   *
   * @since 0.1
   *
   */
  function quick_action($array = false) {
    global $wpdb;

    $action = (!empty($_REQUEST['wp_crm_quick_action']) ? $_REQUEST['wp_crm_quick_action'] : false);
    $object_id = (!empty($_REQUEST['object_id']) ? $_REQUEST['object_id'] : false);

    switch ($action) {

      case 'reset_user_password':

        $user_password = wp_generate_password( 12, false);

        if($object_id && $wpdb->update($wpdb->users, array('user_pass' => wp_hash_password($user_password)), array('ID' => $object_id))) {

          $user_data = get_userdata($object_id);
          $user_login = $user_data->user_login;
          $user_email = $user_data->user_email;
          $reset_key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM {$wpdb->users} WHERE user_login = %s", $user_login));

          if ( empty($reset_key) ) {
            $key = wp_generate_password(20, false);
            $wpdb->update($wpdb->users, array('user_activation_key' => $reset_key), array('user_login' => $user_login));
          }

          wp_crm_add_to_user_log($object_id, __('Password reset. A random password has been generated for user by system.', 'wp_crm'));

          $args['user_login'] = $user_login;
          $args['user_email'] = $user_email;
          $args['user_password'] = $user_password;
          $args['reset_key'] = $reset_key;
          $args['reset_url'] = network_site_url("wp-login.php?action=rp&key={$reset_key}&login=" . rawurlencode($user_login), 'login');

          wp_crm_send_notification('password_reset', $args);

          $return['success'] = 'true';

        } else {
          $return['success'] = 'false';
          $return['sql'] = $wpdb->last_query;
        }


      break;

      case 'archive_message':

        $wpdb->update($wpdb->crm_log, array('value' => 'archived'), array('id' =>$object_id));
        $return['success'] = 'true';
        $return['message'] = __('Message archived.', 'wp_crm');
        $return['action'] = 'hide_element';

      break;


      case 'delete_log_entry':

        do_action('wp_crm_delete_log_entry', $object_id);

        if($wpdb->query("DELETE FROM {$wpdb->crm_log} WHERE id = {$object_id}")) {
          $return['success'] = 'true';
          $return['message'] = __('Message deleted.', 'wp_crm');
          $return['action'] = 'hide_element';
        }

      break;

      case 'trash_message_and_user':

        if( current_user_can( 'delete_users' ) ) {
         $user_id = $wpdb->get_var("SELECT object_id FROM {$wpdb->crm_log} WHERE id = {$object_id} AND object_type = 'user' ");

         if($user_id) {
          wp_delete_user($user_id);
        }

        $return['success'] = 'true';
        $return['message'] = __('Sender trashed.', 'wp_crm');
        $return['action'] = 'hide_element';
        }

      break;

      default:
        $return = apply_filters('wp_crm_quick_action', array(
          'action' => $action,
          'object_id' => $object_id
        ));

      break;


    }



    if(is_array($return)) {
      return json_encode($return);
    } else {
      return false;
    }

  }


  /**
   * Delete WP-CRM related user things
   *
   *
   * @since 0.1
   *
   */
   function deleted_user($object_id) {
    global $wpdb;

    $wpdb->query("DELETE FROM {$wpdb->crm_log} WHERE object_type = 'user' AND object_id = {$object_id}");

   }


  /**
   * Returns first value from an array
   *
   *
   * @since 0.1
   *
   */
   function get_first_value($array = false) {

    if(!$array) {
      return false;
    }

    if(!is_array($array)) {
      return $array;
    }

    if(isset($array['value']) && !is_array($array['value'])) {
      return $array['value'];
    }

    if(isset($array['default']['value']) && !is_array($array['default']['value'])) {
      return $array['default']['value'];
    }

    foreach($array as $key => $data) {

      if(isset($data['value'])) {
        return $data['value'];
      }

      if(isset($data[0])) {
        return $data[0];
      }

    }


   }

   /**
   * Returns notifications for a given trigger action
   *
   *
   * @since 0.1
   *
   */
   function get_trigger_action_notification($action = false, $force = false) {
    global $wp_crm;

    if(!$action) {
      return;
    }

    foreach($wp_crm['notifications'] as $slug => $notification_data){
      if(is_array($notification_data['fire_on_action']) && in_array($action, $notification_data['fire_on_action']) || $force) {
        $notifications[$slug] = $notification_data;
      }

    }

   return $notifications;

   }


   /**
   * Returns user values in an array by keys set in the WP_CRM meta keys (data tab)
   *
   * @since 0.16
   *
   */
   function get_user_replacable_values($user_id = false) {
    global $wp_crm, $wpdb;

    $meta_keys = $wp_crm['data_structure']['meta_keys'];

    $primary_columns = $wpdb->get_col("SHOW COLUMNS FROM {$wpdb->users}");

    $primary = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = {$user_id}", ARRAY_A);

    foreach($meta_keys as $meta_key => $meta_label) {

      if(in_array($meta_key, $primary_columns)) {
        $value = $primary[$meta_key];
      } else {
        $value = get_user_meta($user_id, $meta_key, true);
      }

      if(!empty($value)) {
        $display_columns[$meta_key] = $meta_label;
      }

      $user[trim($meta_key)] = trim($value);

    }

    return $user;


   }

   /**
   * Replaced notification variables with actual values
   *
   *
   * @since 0.21
   *
   */
   function replace_notification_values($notification_data = false, $replace_with = false) {
    global $wp_crm;

    if(!is_array($replace_with)) {
      return;
    }

    $notification_keys = array_keys($notification_data);

    foreach($replace_with as $key => $value) {

      if(is_array($value)) {
        $value = WP_CRM_F::get_first_value($value);
      }

      foreach($notification_data as $n_key => $n_value) {
        $notification_data[$n_key] = str_replace('[' . $key . ']', $value, $n_value);
      }

    }

    return $notification_data;

   }



    /**
     * Tries to determine what the main display value of the user should be
     * Cycles through in attribute order to find first with value
     *
     * @since 0.1
     *
    */
    static function get_primary_display_value($user_object) {
        global $wp_crm;

        if(!empty($user_object) && is_numeric($user_object)) {
          $user_object = wp_crm_get_user($user_object);
        }

        if($primary_user_attribute = $wp_crm['configuration']['primary_user_attribute']) {
          $primary_user_attribute = WP_CRM_F::get_first_value($user_object[$primary_user_attribute]);

          if(!empty($primary_user_attribute)) {
            $return = $primary_user_attribute;
          }
        }

        //** If unable to get value from primary user attribute, grab the first from attribute list */

        if(!$return && !empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {

          $attribute_keys = array_keys($wp_crm['data_structure']['attributes']);

          foreach($attribute_keys as $key) {

            if($return = WP_CRM_F::get_first_value($user_object[$key])) {
              break;
            }

          }
        }

        //** Default to user_login */
        if(!$return || is_array($return)) {
          $return = WP_CRM_F::get_first_value($user_object['user_login']);
        }

        //** Return values */
        if($return) {
          return $return;
        }

        return false;
    }


    /**
     * Get first value -> used to guess "default" user value
     *
     * @since 0.1
     *
    */
    static function get_first_user_data_key()  {
      global $wp_crm;

      foreach($wp_crm['data_structure']['attributes'] as $key => $data) {
        return $key;
      }

    }

    /**
     * Get user data structure.  May be depreciated.
     *
     * @since 0.1
     *
    */
    static function user_object_structure($args = '') {

        global $wp_crm, $wpdb;

        $defaults = array(
            'table_cols' => 'false',
            'root_only' => 'false'
        );

        $args = wp_parse_args( $args, $defaults );

        foreach($wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users}") as $column) {
            $a[$column->Field] = CRM_UD_F::de_slug($column->Field);
            $table_cols[] = $column->Field;
        }

        if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
            foreach($wp_crm['data_structure']['attributes'] as $attribute => $attribute_data){

                $a[$attribute] = $attribute_data['title'];

                if($args['root_only'] == 'true') {
                    continue;
                }

                if(!empty($attribute_data['options'])) {
                    foreach(explode(',', $attribute_data['options']) as $this_option) {
                        $a[$attribute .'_' . CRM_UD_F::create_slug($this_option)] = $this_option;
                    }
                }
            }
        }

        if($args['table_cols'] == 'true') {
            return $table_cols;
        }

        return $a;
    }

  /**
   * Fixes admin titles.
   *
   *
   * @since 0.1
   *
   */
  static function admin_title($current_title) {
    global $current_screen, $wpdb;
    switch($current_screen->id) {

      case 'crm_page_wp_crm_add_new':

        if(isset($_REQUEST['user_id']) && $user_object = get_userdata($_REQUEST['user_id'])) {
          return str_replace("New Person", "Editing {$user_object->display_name}", $current_title);
        }

      break;
    }

    return $current_title;

  }



  /**
   * Run manually when a version mismatch is detected.
   *
   * Called in admin_init and on activation hook.
   *
   * @todo Need a better way of handling adding capabilities since this function is not activated when premium features are added.
   * @since 0.1
   *
    */
  static function manual_activation( $args = '' ) {
    global $wp_crm, $wp_roles;

    $defaults = array(
      'auto_redirect' => 'false',
      'update_caps' => 'true'
    );

    $args = wp_parse_args( $args, $defaults );

    $installed_ver = get_option( "wp_crm_version" );

    if(@version_compare($installed_ver, WP_CRM_Version) == '-1') {

      if(!empty($installed_ver)) {
        //** Handle any updates related to version changes */
        WP_CRM_F::handle_update($installed_ver);
      }

      // Unschedule event
      $timestamp = wp_next_scheduled( 'wp_crm_premium_feature_check' );
      wp_unschedule_event($timestamp, 'wp_crm_premium_feature_check' );
      wp_clear_scheduled_hook('wp_crm_premium_feature_check');

      // Schedule event
      wp_schedule_event(time(), 'daily', 'wp_crm_premium_feature_check');

      // Update option to latest version so this isn't run on next admin page load
      update_option( "wp_crm_version", WP_CRM_Version );

      //** Get premium features on activation */
      @WP_CRM_F::feature_check();

      $args['update_caps'] = 'true';
      //$args['auto_redirect'] = 'true';

    }

    //** load this here to get the capabilities */
    include_once WP_CRM_Path . '/action_hooks.php';

    //** Add capabilities */
    if(($args['update_caps'] == 'true') && (is_array($wp_crm['capabilities']) && $wp_roles)) {
      if(is_array($wp_crm['capabilities'])) {
        foreach($wp_crm['capabilities'] as $capability => $description) {
          $wp_roles->add_cap('administrator','WP-CRM: ' . $capability,true);
        }
        update_option('wp_crm_caps_set', true);
      }
    }


    if($args['auto_redirect'] == 'true') {
      //** Redirect to overview page so all updates take affect on page reload. Not done on activation() */
      die( wp_redirect(admin_url('admin.php?page=wp_crm&message=plugin_updated')) );

    }


    return;



  }


  /**
   * Draws default user input field
   *
   * Values are always in always in array format.  A string may be passed, but it will be converted into an array and placed into the 'default' holder.
   * Just because mutliple "types" of values are passed does not mean they will be rendered, WP-CRM data settings are checked first to see which attributes have predefined values
   *
   *  Array
   *   (
   *      [default] => Array
   *           (
   *               [0] => 555-default-number
   *           )
   *
   *       [home] => Array
   *           (
   *               [0] => 444-home number
   *               [1] => 445- secondary home number
   *           )
   *
   *       [cell] => Array
   *           (
   *               [0] => 651-my only cell
   *           )
   *
   *   )
   *
   *
   * @since 0.01
   *
   */
  function user_input_field($slug, $values = false, $attribute = false, $user_object = false, $args = '') {
    global $wp_crm, $wpdb;

    //* Only supported in WP 3.3+ and is here to ensure the scripts are loaded */
    wp_enqueue_script('wp_crm_profile_editor');

    $defaults = array(
      'default_input_type' => 'text'
    );

    $args = wp_parse_args( $args, $defaults );

    if(isset($args['tabindex'])) {
      $tabindex = " TABINDEX={$args['tabindex']} ";
    }

    //** Load attribute data if it isn't passed */
    if(empty($attribute)) {
      $attribute = $wp_crm['data_structure']['attributes'][$slug];
    }

    //** If value array is not passed, we create an array */
    if(!is_array($values)) {
      $values = array('default' => array($values));
    }

    //** Calculate total values passed and convert to loop-ready format */
    if($attribute['input_type'] != 'checkbox' && $attribute['input_type'] != 'dropdown') {

      foreach($values as $type_slug => $type_values) {

      //** Check if this type exists in data structure if this is a non-default type slug */
      if($type_slug != 'default' && $attribute['option_keys'] && !in_array("{$slug}_option_{$type_slug}", $attribute['option_keys'])) {
        //* If this type does not exist in option_keys, discard data */
        continue;
      }

      //** Cycle through individual values for this type */
      foreach($type_values as $single_value) {

        //** Set random ID now as meta key for later use for DOM association */
        $rand_id =  rand(10000,99999);
        $loop_ready_values[$rand_id]['value'] = $single_value;
        $loop_ready_values[$rand_id]['option'] = $type_slug;
        $loop_ready_values[$rand_id]['label'] = $attribute['option_labels'][$type_slug];

      }

      }
    }

    //** Checkbox options are handled differently because they all need to be displayed, and we don't cycle through the values but through the available options */
    if($attribute['input_type'] == 'checkbox') {

      if($attribute['has_options']) {
        foreach($attribute['option_labels'] as $option_key => $option_label) {

          $rand_id =  rand(10000,99999);
          $loop_ready_values[$rand_id]['option'] = $option_key;
          $loop_ready_values[$rand_id]['label'] = $option_label;

          if($values[$option_key] && (in_array('on', $values[$option_key]) || in_array('true', $values[$option_key]))) {
            $loop_ready_values[$rand_id]['enabled'] = true;
          }

        }
      } else {
          //** In case checkbox doesn't have options  we don't cycle through them but only check the primary key */

          $rand_id =  rand(10000,99999);
          $loop_ready_values[$rand_id]['option'] = $slug;
          $loop_ready_values[$rand_id]['label'] = $attribute['title'];

          if(in_array('on', $values['default']) || in_array('true', $values['default'])) {
            $loop_ready_values[$rand_id]['enabled'] = true;
          }

      }

    }

    if($attribute['input_type'] == 'dropdown') {

        foreach($values as $type_slug => $type_values) {
          $rand_id =  rand(10000,99999);
          $loop_ready_values[$rand_id]['option'] = $type_slug;
          //** only the first value for an option will be selected. we assume that there will not be situations of same dropdown having same value twice */
          $loop_ready_values[$rand_id]['label'] = $type_values[0];

      }

    }

    $values = $loop_ready_values;
    $total_values = count($values);

    if($total_values > 1) {
      $multiple_values = true;
    }

    $class = array();

    if(empty($attribute['input_type'])) {
      $attribute['input_type'] = $default_input_type;
    }

    $class[] = 'wp_crm_'.$slug.'_field';

    if($attribute['input_type'] == 'text') {
      $class[] = 'regular-text';
    }

    if($attribute['input_type'] == 'date') {
      $class[] = 'regular-text';
      $class[] = 'wpc_date_picker';
    }

    if($attribute['input_type'] == 'dropdown') {
      $class[] = 'wp_crm_dropdown';
    }

    if($attribute['input_type'] == 'password') {
      $class[] = 'wp_crm_password';
    }

    if($attribute['allow_multiple'] == 'true') {
      $class[] = 'allow_multiple';
    }

    if($attribute['required'] == 'true') {
      $class[] = 'wp_crm_required_field';
    }

    if ($slug=='user_email'){
      $class[] = 'email_validated';
    }

    if($attribute['has_options']) {
      $class[] = 'has_options';
    }

    if($attribute['uneditable'] == 'true') {
      $class[] = 'wp_crm_attribute_uneditable';
    }

    if($wp_crm['configuration']['standardize_display_name'] == 'true' && $slug == 'display_name') {
      $class[] = 'wp_crm_attribute_uneditable';
      $class[] = 'wp_crm_standardized_name';
    }

    $class = implode(' ' , apply_filters('wp_crm_'.$attribute['input_type'].'_class',$class));

    ob_start();

    //** Draw inputs on back-end */
    if ( is_admin() ) :

      do_action("wp_crm_before_{$slug}_input", array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
    ?>

    <div class="input_div <?php echo $class; ?> wp_crm_<?php echo $slug; ?>_div">

    <?php

    switch ($attribute['input_type']) {

      case 'date':
      case 'password':
      case 'text':
        $input_type = in_array( $attribute['input_type'], array( 'date' ) ) ? 'text' : $attribute['input_type'] ;
        foreach($values as $rand => $value_data) {
          ?>
          <div class="wp_crm_input_wrap"  random_hash="<?php echo $rand; ?>" >
          <input <?php echo $tabindex; ?> wp_crm_slug="<?php echo esc_attr($slug); ?>" random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  <?php echo ($class) ? 'class="'.$class.'"' : '' ; ?> type="<?php echo $input_type; ?>" value="<?php echo ($slug!='user_pass') ? esc_attr($value_data['value']) : ''; ?>" />
          <?php if($attribute['has_options']) { ?>
            <select wp_crm_option_for="<?php echo esc_attr($slug); ?>"  <?php echo $tabindex; ?> class="wp_crm_input_options" random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
            <option></option>
            <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
              <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
            <?php endforeach; ?>
            </select>
          <?php } //* end: has_options */?>
          </div>
          <?php
        }
      break;

     case 'textarea':
       foreach($values as $rand => $value_data) { ?>
      <div class="wp_crm_input_wrap" random_hash="<?php echo $rand; ?>" >

       <textarea  wp_crm_slug="<?php echo esc_attr($slug); ?>"  <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]" class="wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>"><?php echo $value_data['value']; ?></textarea>

        <?php if($attribute['has_options']) { ?>
          <select wp_crm_option_for="<?php echo esc_attr($slug); ?>" <?php echo $tabindex; ?>  random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
          <option></option>
          <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
            <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
          <?php endforeach; ?>
          </select>
        <?php } //* end: has_options */?>

      </div>
    <?php }
      break;

    case 'checkbox':
      if(!empty($attribute['has_options'])) {
        ?>
        <div class="wp_crm_input_wrap wp_checkbox_input wp-tab-panel"  >
          <ul class="wp_crm_checkbox_list"  wp_crm_slug="<?php echo esc_attr($slug); ?>">
          <?php foreach($values as $rand => $value_data) { ?>
            <li option_meta_value="<?php echo esc_attr($value_data['option']); ?>" >
              <input random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  type='hidden' value="" />
              <input random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]"  type='hidden' value="<?php echo esc_attr($value_data['option']); ?>" />
              <input id="wpi_checkbox_<?php echo $rand; ?>" <?php checked($value_data['enabled'], true); ?> <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  class="wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>" type='<?php echo $attribute['input_type']; ?>' value="on" />
              <label for="wpi_checkbox_<?php echo $rand; ?>"><?php echo $value_data['label']; ?></label>
            </li>
          <?php } ?>
          </ul>
        </div>
        <?php
      } else {
        foreach($values as $rand => $value_data) {
          ?>
          <input random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  type='hidden' value="" />
          <input random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]"  type='hidden' value="<?php echo esc_attr($value_data['option']); ?>" />
          <input id="wpi_checkbox_<?php echo $rand; ?>" <?php checked($value_data['enabled'], true); ?> <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  class="wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>" type='<?php echo $attribute['input_type']; ?>' value="on" />
          <?php
        }
      }
      break;

    case 'dropdown':
      foreach($values as $rand => $value_data) { ?>
      <div class="wp_crm_input_wrap wp_dropdown_input"  random_hash="<?php echo $rand; ?>" >

      <select class="wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>" wp_crm_slug="<?php echo esc_attr($slug); ?>"  <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
        <option value=""></option>
        <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
          <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
        <?php endforeach; ?>
      </select>

      </div>
    <?php }
      break;

    default:
      do_action('wp_crm_render_input',  array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
      break;
    }

    //** API Access for data after the field *'
    do_action("wp_crm_after_{$slug}_input", array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
    ?>

    <?php /* if($attribute['autocomplete'] == 'true'): ?>
    <script type="text/javascript">
       <?php
        // get data
        $values = $wpdb->get_col("SELECT DISTINCT(meta_value) FROM {$wpdb->usermeta} WHERE meta_key = '{$slug}'");
        if(!empty($values)) {
       ?>
       var <?php echo $slug; ?>_autocomplete_vars = ["<?php echo implode('","', $values); ?>"];
       jQuery(document).ready(function() {
        jQuery("input.wp_crm_<?php echo $slug; ?>_field").click(function() {
          var input = jQuery(this);
          input.autocomplete({source: <?php echo $slug; ?>_autocomplete_vars, appendTo: jQuery(input).parents('.input_div')});
        });
      });
    </script>
    <?php } endif; /* autocomplete */ ?>

    </div>
    <?php

    //** Draw input for front-end */
    else:
      do_action("wp_crm_before_{$slug}_input_frontend", array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
      switch ($attribute['input_type']) {

        case 'date':
        case 'password':
        case 'text':
          $input_type = in_array( $attribute['input_type'], array( 'date' ) ) ? 'text' : $attribute['input_type'] ;
          foreach($values as $rand => $value_data) {  ?>
              <input <?php echo $tabindex; ?> wp_crm_slug="<?php echo esc_attr($slug); ?>" random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  class="input-large wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>" type="<?php echo $input_type; ?>" value="<?php echo ($slug!='user_pass') ? esc_attr($value_data['value']) : ''; ?>" />
              <?php if($attribute['has_options']) { ?>
                <select wp_crm_option_for="<?php echo esc_attr($slug); ?>" <?php echo $tabindex; ?> class="input-small wp_crm_input_options" random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
                  <option></option>
                  <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
                    <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
                  <?php endforeach; ?>
                </select>
              <?php } //* end: has_options */?>
          <?php }
        break;

      case 'textarea': foreach($values as $rand => $value_data) { ?>
         <textarea wp_crm_slug="<?php echo esc_attr($slug); ?>"  <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]" class="input-large wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>"><?php echo $value_data['value']; ?></textarea>
          <?php if($attribute['has_options']) { ?>
            <select class="input-small" wp_crm_option_for="<?php echo esc_attr($slug); ?>" <?php echo $tabindex; ?>  random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
              <option></option>
              <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
                <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
              <?php endforeach; ?>
            </select>
          <?php } //* end: has_options */?>
      <?php }
        break;

      case 'checkbox':
        foreach($values as $rand => $value_data) { ?>
          <input random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  type='hidden' value="" />
          <input random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]"  type='hidden' value="<?php echo esc_attr($value_data['option']); ?>" />
          <label class="checkbox" for="wpi_checkbox_<?php echo $rand; ?>">
            <input id="wpi_checkbox_<?php echo $rand; ?>" <?php checked($value_data['enabled'], true); ?> <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][value]"  class="wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>" type='<?php echo $attribute['input_type']; ?>' value="on" />
            <?php echo $value_data['label']; ?>
          </label>
        <?php }
        break;

      case 'dropdown':
        foreach($values as $rand => $value_data) { ?>
        <select class="input-large wp_crm_<?php echo $slug; ?>_field <?php echo $class; ?>" wp_crm_slug="<?php echo esc_attr($slug); ?>"  <?php echo $tabindex; ?> random_hash="<?php echo $rand; ?>" name="wp_crm[user_data][<?php echo $slug; ?>][<?php echo $rand; ?>][option]">
          <option value=""></option>
          <?php foreach($attribute['option_labels'] as $type_slug => $type_label): ?>
            <option  <?php selected($type_slug, $value_data['option']); ?> value="<?php echo $type_slug; ?>"><?php echo $type_label; ?></option>
          <?php endforeach; ?>
        </select>
      <?php }
        break;

      default:
          do_action('wp_crm_render_input_frontend',  array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
        break;
      }

      //** API Access for data after the field *'
      do_action("wp_crm_after_{$slug}_input_frontend", array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));

    endif;

    $content = ob_get_contents();
    ob_end_clean();

    $content = apply_filters('wp_crm_user_input_field', $content, $slug, $value);

    return $content;

   }

  /**
   * Return user object
   *
    * @since 0.01
   *
    */
  function get_user($user_id) {
    return get_userdata($user_id);
  }



  /**
   * Saves settings, applies filters, and loads settings into global variable
   *
   * Run from WP_CRM_C::WP_CRM_C()
   *
   * @since 0.01
   *
   */
  function settings_action($force_db = false, $args = false) {
    global $wp_crm;

    // Process saving settings
    if(isset($_REQUEST['wp_crm']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'wp_crm_setting_save') ) {

      // Handle backup
      if($backup_file = $_FILES['wp_crm']['tmp_name']['settings_from_backup']) {
        $backup_contents = file_get_contents($backup_file);

        if(!empty($backup_contents))
          $decoded_settings = json_decode($backup_contents, true);

        if(!empty($decoded_settings))
          $_REQUEST['wp_crm'] = $decoded_settings;
      }

      $wp_crm_settings = apply_filters('wp_crm_settings_save', $_REQUEST['wp_crm'], $wp_crm);

      // Prevent removal of featured settings configurations if they are not present
      if($wp_crm['configuration']['feature_settings'])
      foreach($wp_crm['configuration']['feature_settings'] as $feature_type => $preserved_settings) {

        if(empty($_REQUEST['wp_crm']['configuration']['feature_settings'][$feature_type])) {
          $wp_crm_settings['configuration']['feature_settings'][$feature_type] = $preserved_settings;
         }
      }

      //* Regenerate possible meta keys */
      $wp_crm_settings['data_structure'] = WP_CRM_F::build_meta_keys( $wp_crm_settings);

      update_option('wp_crm_settings', $wp_crm_settings);

      // Load settings out of database to overwrite defaults from action_hooks.
      $wp_crm_db = get_option('wp_crm_settings');

      // Overwrite $wp_crm with database setting
      $wp_crm = array_merge($wp_crm, $wp_crm_db);

      // Reload page to make sure higher-end functions take affect of new settings
      // The filters below will be ran on reload, but the saving functions won't
      if($_REQUEST['page'] == 'wp_crm_settings'); {
        unset($_REQUEST);
        wp_redirect(admin_url("admin.php?page=wp_crm_settings&message=updated"));
        exit;
      }

    } else {

      //** Check if this is a new install */
      $check_crm_settings = get_option('wp_crm_settings');

      if(empty($check_crm_settings['configuration']) || $args['force_defaults'] == true) {

        $assumed_email = 'crm@' . $_SERVER['HTTP_HOST'];

        //* Load some basic data structure (need better place to put this) */
        $wp_crm['data_structure']['attributes']['display_name']['title'] = 'Display Name';
        $wp_crm['data_structure']['attributes']['display_name']['primary'] = 'true';
        $wp_crm['data_structure']['attributes']['display_name']['display'] = 'true';
        $wp_crm['data_structure']['attributes']['display_name']['input_type'] = 'text';
        $wp_crm['data_structure']['attributes']['display_name']['required'] = 'true';

        $wp_crm['data_structure']['attributes']['user_email']['title'] = 'User Email';
        $wp_crm['data_structure']['attributes']['user_email']['primary'] = 'true';
        $wp_crm['data_structure']['attributes']['user_email']['display'] = 'false';
        $wp_crm['data_structure']['attributes']['user_email']['input_type'] = 'text';
        $wp_crm['data_structure']['attributes']['user_email']['required'] = 'true';
        $wp_crm['data_structure']['attributes']['user_email']['overview_column'] = 'true';

        $wp_crm['data_structure']['attributes']['company']['title'] = 'Company';
        $wp_crm['data_structure']['attributes']['company']['input_type'] = 'text';
        $wp_crm['data_structure']['attributes']['company']['primary'] = 'true';
        $wp_crm['data_structure']['attributes']['company']['display'] = 'true';

        $wp_crm['data_structure']['attributes']['phone_number']['title'] = 'Phone Number';
        $wp_crm['data_structure']['attributes']['phone_number']['input_type'] = 'text';
        $wp_crm['data_structure']['attributes']['phone_number']['display'] = 'true';

        $wp_crm['data_structure']['attributes']['user_type']['title'] = 'Important Date';
        $wp_crm['data_structure']['attributes']['user_type']['options'] = 'Birthday, Anniversary';
        $wp_crm['data_structure']['attributes']['user_type']['input_type'] = 'date';

        $wp_crm['data_structure']['attributes']['user_type']['title'] = 'User Type';
        $wp_crm['data_structure']['attributes']['user_type']['options'] = 'Customer,Vendor,Employee';
        $wp_crm['data_structure']['attributes']['user_type']['input_type'] = 'checkbox';

        $wp_crm['data_structure']['attributes']['instant_messenger']['title'] = 'IM';
        $wp_crm['data_structure']['attributes']['instant_messenger']['options'] = 'Skype,Google Talk,AIM';
        $wp_crm['data_structure']['attributes']['instant_messenger']['input_type'] = 'text';
        $wp_crm['data_structure']['attributes']['instant_messenger']['allow_multiple'] = 'true';

        $wp_crm['data_structure']['attributes']['description']['title'] = 'Description';
        $wp_crm['data_structure']['attributes']['description']['input_type'] = 'textarea';

        $wp_crm['configuration']['overview_table_options']['main_view'] = array('display_name', 'user_email');
        $wp_crm['configuration']['default_sender_email'] = "CRM <$assumed_email>";
        $wp_crm['configuration']['primary_user_attribute'] = 'display_name';

        $wp_crm['wp_crm_contact_system_data']['example_form']['title'] = __('Example Shortcode Form', 'wp_crm');
        $wp_crm['wp_crm_contact_system_data']['example_form']['full_shortcode'] = '[wp_crm_form form=example_contact_form]';
        $wp_crm['wp_crm_contact_system_data']['example_form']['current_form_slug'] = 'example_contact_form';
        $wp_crm['wp_crm_contact_system_data']['example_form']['message_field'] = 'on';
        $wp_crm['wp_crm_contact_system_data']['example_form']['fields'] = array('display_name','user_email', 'company', 'phone_number');

        $wp_crm['notifications']['example']['subject'] = __('Thank your for your message!', 'wp_crm');
        $wp_crm['notifications']['example']['to'] = '[user_email]';
        $wp_crm['notifications']['example']['send_from'] = $assumed_email;
        $wp_crm['notifications']['example']['message'] = __("Hello [display_name],\nThank you, your message has been received.", 'wp_crm');
        $wp_crm['notifications']['example']['fire_on_action'] = array('example_form');

        $wp_crm['notifications']['message_notification']['subject'] = __('Message from Website', 'wp_crm');
        $wp_crm['notifications']['message_notification']['to'] = get_bloginfo('admin_email');
        $wp_crm['notifications']['message_notification']['send_from'] = $assumed_email;
        $wp_crm['notifications']['message_notification']['message'] = __("Shortcode Form: [trigger_action]\nSender Name: [display_name]\nSender Email: [user_email]\nMessage: [message_content]", 'wp_crm');
        $wp_crm['notifications']['message_notification']['fire_on_action'] = array('example_form');

        $wp_crm['data_structure'] = WP_CRM_F::build_meta_keys( $wp_crm);

        //** Commit defaults to DB */
        update_option('wp_crm_settings', $wp_crm);

       }

    }

    if($force_db) {

      //* Load settings out of database to overwrite defaults from action_hooks. */
      $wp_crm_db = get_option('wp_crm_settings');

      //* Overwrite $wp_crm with database setting */
      $wp_crm = array_merge($wp_crm, $wp_crm_db);

    }

    $wp_crm = stripslashes_deep($wp_crm);

    $wp_crm = apply_filters('wp_crm_settings', $wp_crm);

    return $wp_crm;

  }

  /**
   * Make attribute of field user_email/required depends on
   * option Allow account creation with no email
   *
   * @param array $new_settings
   * @param array $old_settings
   * @return array
   * @author odokienko@UD
   */
  function wp_crm_settings_save_email_required ($new_settings,$old_settings){

    if (!empty($new_settings['data_structure']['attributes']['user_email'])){

      $new_settings['data_structure']['attributes']['user_email']['required'] = ($new_settings['configuration']['allow_account_creation_with_no_email']=='true') ? 'false' : 'true';

    }

    return $new_settings;

  }


  /**
   * PHP function to echoing a message to JS console
   *
   * @since 1.32.0
   */
  function console_log($text = false) {
    global $wp_crm;

    if($wp_crm['configuration']['developer_mode'] != 'true') {
      return;
    }

    if(empty($text)) {
      return;
    }

    if(is_array($text) || is_object($text)) {
      $text = str_replace("\n", '', print_r($text, true));
    }

    //** Cannot use quotes */
    $text = str_replace('"', '-', $text);

    add_filter('wp_footer', create_function('$nothing,$echo_text = "'. $text .'"', 'echo \'<script type="text/javascript">console.log("\' . $echo_text . \'")</script>\'; '));
    add_filter('admin_footer', create_function('$nothing,$echo_text = "'. $text .'"', 'echo \'<script type="text/javascript">console.log("\' . $echo_text . \'")</script>\'; '));

  }
  /**
   * Check plugin updates - typically for AJAX use
   *
    * @since 0.01
   *
    */
  function check_plugin_updates() {
    global $wp_crm;

    echo WP_CRM_F::feature_check(true);

  }

  /**
   * Minify JavaScript
   *
    * Uses third-party JSMin if class isn't declared.
    *
    * @since 0.01
   *
    */
  function minify_js($data) {

    if(!class_exists('W3_Plugin'))
      include_once wp_crm_Path. '/third-party/jsmin.php';
    elseif(file_exists(WP_PLUGIN_DIR . '/w3-total-cache/lib/Minify/JSMin.php'))
      include_once WP_PLUGIN_DIR . '/w3-total-cache/lib/Minify/JSMin.php';
    else
      include_once wp_crm_Path. '/third-party/jsmin.php';

    if(class_exists('JSMin'))
      $data = JSMin::minify($data);

    return $data;
  }

  /**
   * Checks for updates against TwinCitiesTech.com Server
   *
    *
    * @since 0.01
   *
    */
  static function feature_check($return = false) {
    global $wp_crm;

    $blogname = get_bloginfo('url');
    $blogname = urlencode(str_replace(array('http://', 'https://'), '', $blogname));
    $system = 'wp_crm';
    $wp_crm_version = get_option( "wp_crm_version" );

    $check_url = "http://updates.usabilitydynamics.com/?system=$system&site=$blogname&system_version=$wp_crm_version";
    $response = @wp_remote_get($check_url);

     if(!$response) {
      return;
    }

    // Check for errors
    if(is_object($response) && !empty($response->errors)) {

      foreach($response->errors as $update_errrors) {
        $error_string .= implode(",", $update_errrors);
        CRM_UD_F::log("Feature Update Error: " . $error_string);
      }

      if($return) {
        return sprintf(__('An error occurred during premium feature check: <b> %s </b>.','wp_crm'), $error_string);
      }

      return;
    }

    // Quit if failture
    if($response['response']['code'] != '200') {
      return;
    }

   $response = @json_decode($response['body']);

    if(is_object($response->available_features)) {

      $response->available_features = CRM_UD_F::objectToArray($response->available_features);

      // Updata database
      $wp_crm_settings = get_option('wp_crm_settings');
      $wp_crm_settings['available_features'] =  CRM_UD_F::objectToArray($response->available_features);
       update_option('wp_crm_settings', $wp_crm_settings);


    } // available_features


    if($response->features == 'eligible' && $wp_crm['configuration']['disable_automatic_feature_update'] != 'true') {

      // Try to create directory if it doesn't exist
      if(!is_dir(WP_CRM_Premium)) {
        @mkdir(WP_CRM_Premium, 0755);
      }

      // Save code
      if(is_dir(WP_CRM_Premium) && is_object($response->code)) {
        foreach($response->code as $code) {

          $filename = $code->filename;
          $php_code = $code->code;
          $version = $code->version;

          $default_headers = array(
          'Name' => __('Feature Name','wp_crm'),
          'Version' => __('Version','wp_crm'),
          'Description' => __('Description','wp_crm')
          );

          $current_file = @get_file_data( WP_CRM_Premium . "/" . $filename, $default_headers, 'plugin' );
          //echo "$filename - new version: $version , old version:$current_file[Version] |  " .  @version_compare($current_file[Version], $version) . "<br />";

          if(@version_compare($current_file['Version'], $version) == '-1') {
            $this_file = WP_CRM_Premium . "/" . $filename;
            $fh = @fopen($this_file, 'w');
            if($fh) {
              fwrite($fh, $php_code);
              fclose($fh);

              if($current_file[Version])
                CRM_UD_F::log(sprintf(__('WP-CRM Premium Feature: %s updated to version %s from %s.','wp_crm'), $code->name, $version, $current_file['Version']));
              else
                CRM_UD_F::log(sprintf(__('WP-CRM Premium Feature: %s updated to version %s.','wp_crm'), $code->name, $version));

              $updated_features[] = $code->name;
            }
          } else {

          }


        }
      }
    }

    // Update settings
    WP_CRM_F::settings_action(true);

    //** May not be a good place to run this because features are not active yet and any feature-added capabilities are now known of yet */
    //WP_CRM_F::manual_activation('auto_redirect=false&update_caps=true');

    if($return && $wp_crm['configuration']['disable_automatic_feature_update'] == 'true') {
      return __('Update ran successfully but no features were downloaded because the setting is disabled.','wp_crm');

    } elseif($return) {
      return __('Update ran successfully.','wp_crm');
    }
  }


  /**
   * Check for premium features and load them
   *
   * @since 0.01
   *
    */
  function load_premium() {
    global $wp_crm;

    $default_headers = array(
      'Name' => __('Name','wp_crm'),
      'Version' => __('Version','wp_crm'),
      'Description' => __('Description','wp_crm')
    );

    if(!is_dir(WP_CRM_Premium)) {
      return;
    }

    if ($premium_dir = opendir(WP_CRM_Premium)) {

      if(file_exists(WP_CRM_Premium . "/index.php")) {

        if(WP_DEBUG) {
          include_once(WP_CRM_Premium . "/index.php");
        } else {
          @include_once(WP_CRM_Premium . "/index.php");
        }
      }

      while (false !== ($file = readdir($premium_dir))) {

        if($file == 'index.php')
          continue;

        if(end(explode(".", $file)) == 'php') {

          $plugin_slug = str_replace(array('.php'), '', $file);

          if(WP_DEBUG) {
            $plugin_data = get_file_data( WP_CRM_Premium . "/" . $file, $default_headers, 'plugin' );
          } else {
            $plugin_data = @get_file_data( WP_CRM_Premium . "/" . $file, $default_headers, 'plugin' );
          }
          $wp_crm['installed_features'][$plugin_slug]['name'] = $plugin_data['Name'];
          $wp_crm['installed_features'][$plugin_slug]['version'] = $plugin_data['Version'];
          $wp_crm['installed_features'][$plugin_slug]['description'] = $plugin_data['Description'];

          // Check if the plugin is disabled
          if($wp_crm['installed_features'][$plugin_slug]['disabled'] != 'true') {

            if(WP_DEBUG) {
              include_once(WP_CRM_Premium . "/" . $file);
            } else {
              @include_once(WP_CRM_Premium . "/" . $file);
            }

             // Disable plugin if class does not exists - file is empty
            if(!class_exists($plugin_slug)) {
              unset($wp_crm['installed_features'][$plugin_slug]);
            }

            $wp_crm['installed_features'][$plugin_slug]['disabled'] = 'false';

          }

        }
      }
    }

  }


  /**
   * Installs tables and runs WP_CRM_F::manual_activation() which actually handles the upgrades
   *
   * @since 0.01
   *
    */
  function activation() {
    global $current_user, $wp_crm, $wp_roles;

    WP_CRM_F::maybe_install_tables();

    WP_CRM_F::manual_activation('auto_redirect=false&update_caps=true');

  }


  /**
   * Install DB tables.
   *
   * @since 0.01
   * @uses $wpdb
   *
   */
  function maybe_install_tables() {
    global $wpdb;

    // Array to store SQL queries
    $sql = array();

    if(!$wpdb->crm_log) {
      $wpdb->crm_log = $wpdb->base_prefix . 'crm_log';
    }

    if(!$wpdb->crm_log_meta) {
      $wpdb->crm_log_meta = $wpdb->crm_log . '_meta';
    }

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql = "CREATE TABLE {$wpdb->crm_log} (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      object_id mediumint(9) NOT NULL,
      object_type VARCHAR(11),
      user_id mediumint(9) NOT NULL,
      action VARCHAR(255),
      attribute VARCHAR(255),
      value VARCHAR(255),
      msgno VARCHAR(255),
      email_to VARCHAR(255),
      email_from VARCHAR(255),
      subject VARCHAR(255),
      text TEXT,
      email_references VARCHAR(255),
      time DATETIME,
      other VARCHAR(255),
      UNIQUE KEY id (id)
    );";
    dbDelta( $sql );


    $sql = "CREATE TABLE {$wpdb->crm_log_meta} (
      meta_id mediumint(9) NOT NULL AUTO_INCREMENT,
      message_id mediumint(9) NOT NULL,
      meta_key VARCHAR(255),
      meta_group VARCHAR(255),
      meta_value TEXT,
      UNIQUE KEY id (meta_id)
    );";


    dbDelta( $sql );




  }

  /**
   * A callback function for add_filter 'set-screen-option'.
   * @See WP_CRM_CORE::init
   * @return $value|false
   * @author odokienko@UD
   */
  function crm_set_option($status=false, $option=false, $value=false) {

    if ($status || !$option ) {
      return false;
    }elseif('crm_page_wp_crm_add_new_per_page' == $option ) {
      return $value;
    }

  }

  /*
   * Set Custom Screen Options
   * @author odokienko@UD
   */

  function crm_screen_options() {
    global $current_screen, $wpdb, $current_user;

    $user_filter = (is_numeric($_REQUEST['user_id']))? " object_id={$_REQUEST['user_id']} " : '1';

    $output = '';

    switch ($current_screen->id) {
      case 'crm_page_wp_crm_add_new':
        $results = $wpdb->get_results("SELECT DISTINCT attribute,other FROM `{$wpdb->crm_log}` WHERE {$user_filter} ");

        foreach ((array) $results as $row){
          $name = $row->attribute . (($row->other)?'['.$row->other.']':'');
          $output[] = '<label for="wp_crm_ui_crm_user_activity_' . $name . '">';
          $output[] = '<input type="checkbox" ' . checked( get_user_option('wp_crm_ui_crm_user_activity_'.$name),'false', false ) . ' value="' . $name . '" id="wp_crm_ui_crm_user_activity_' . $name . '" name="wp_crm_ui_crm_user_activity_' . $name . '"  class="non-metabox-option" attribute="' . $row->attribute . '" other="' . $row->other . '" />';
          $output[] = apply_filters( 'wp_crm_entry_type_label' , $row->attribute, $row );
          $output[] = '</label>';
        }

        if( !empty( $output ) ) {
          $output = '<div id="crm_user_activity_filter"><h5>' . __('Show in User Activity History', 'wp_crm').'</h5>' .  implode( '', (array) $output ) .  '</div>';
        }

        break;
    }

    return $output;
  }

   /**
   * Create label for user activity stream attribute
   *
   * @version 1.0
   * @author odokienko@UD
   */
  function wp_crm_entry_type_label($attr,$entity) {
    global $wp_crm;

    switch ($attr){
      case "note":
        $attr = __("Note", 'wp_crm');
        break;
      case "general_message":
        $attr = __("General Message" , 'wp_crm');
        break;
      case "phone_call":
        $attr = __("Phone Call", 'wp_crm');
        break;
      case "meeting":
        $attr = __("Meeting", 'wp_crm');
        break;
      case "file":
        $attr = __("File", 'wp_crm');
        break;
      case "detailed_log":
        $attr = __("Detailed Log", 'wp_crm');
        break;
      case "contact_form_message":
        $attr = sprintf( __( 'Shortcode Form Message %1s.' , 'wp_crm' ), $entity->other );
        break;

    }

    return $attr;

  }


  /**
   * Displays user activity stream for display.
   *
   * @since 0.1
   */
  function get_user_activity_stream($args = '', $passed_result = false) {
    global $wpdb, $wp_crm, $current_user;

    $args = wp_parse_args( $args, array(
      'per_page'=>((get_user_option( 'crm_page_wp_crm_add_new_per_page' )) ? (int)get_user_option( 'crm_page_wp_crm_add_new_per_page' ) : 10),
      'more_per_page' => false,
      'filter_types'=>false
    ));

    if (empty($args['per_page'])){
      $args['per_page'] = ((get_user_option( 'crm_page_wp_crm_add_new_per_page' )) ? (int)get_user_option( 'crm_page_wp_crm_add_new_per_page' ) : 10);
      $args['more_per_page'] = $args['per_page'];
    }

    if(empty($args['user_id'])) {
      return;
    }

    foreach ((array)$args['filter_types'] as $row){
      update_user_option($current_user->ID, "crm_ui_crm_user_activity_{$row['attribute']}".(($row['other'])?'['.$row['other'].']':''), $row['hidden']);
    }

    $params = array(
      'object_id' => $args['user_id'],
      'filter_types' => $args['filter_types'],
      'import_count' => ''
    );

    $all_messages = WP_CRM_F::get_events($params);

    /** @todo $messages is not initialized */
    if( !empty( $passed_result ) ) {
      $result = $passed_result;
    } else {
      $params['import_count'] = $args['per_page'];
      $result = WP_CRM_F::get_events($params);
    }

    $result = stripslashes_deep($result);

    ob_start();

    foreach($result as $entry) {

      $entry_classes[] = $entry->attribute;
      $entry_classes[] = $entry->object_type;
      $entry_classes[] = $entry->action;

      $entry_classes = apply_filters('wp_crm_entry_classes', $entry_classes, $entry);
      $entry_type = apply_filters('wp_crm_entry_type_label', $entry->attribute, $entry);
      $left_by = $wpdb->get_var("SELECT display_name FROM {$wpdb->users} WHERE ID = '{$entry->user_id}'");
      $entry_text = apply_filters('wp_crm_activity_single_content', nl2br($entry->text), array('entry' => $entry, 'args' => $args));

      //** If detailed activity is tracked, certain fields are machine-populated. */
      if( $wp_crm['configuration']['track_detailed_user_activity'] == 'true' && empty( $entry_text ) ) {

        switch( true ) {

          case $entry->attribute == 'detailed_log' && $entry->action == 'login':
            $entry_text = sprintf( __( 'Logged in from IP %1s, %2s ago.', 'wpp' ), $entry->value, /* gethostbyaddr( $entry->value ), */ human_time_diff( strtotime( $entry->time ) ) );
          break;

        }

      }

      if( empty( $entry_text ) ) {
        continue;
      }

      ?><tr class="wp_crm_activity_single <?php echo @implode(' ', array_unique($entry_classes)); ?>">

        <td class="left">
          <ul class='message_meta'>
            <li class='timestamp'>
              <span class='time'><?php echo date(get_option('time_format'), strtotime($entry->time)); ?></span>
              <span class='date'><?php echo date(get_option('date_format'), strtotime($entry->time)); ?></span>
            </li>
            <?php if($entry_type): ?><li class="entry_type"><?php echo $entry_type; ?> </li><?php endif; ?>
            <?php if($left_by): ?><li class="by_user">by <?php echo $left_by; ?> </li><?php endif; ?>
            <li class="wp_crm_log_actions">
              <span verify_action="true" instant_hide="true" class="wp_crm_message_quick_action wp_crm_subtle_link" object_id="<?php echo $entry->id; ?>" wp_crm_action="delete_log_entry"><?php _e('Delete', 'wp_crm'); ?></span>
            </li>
          </ul>
        </td>

        <td class="right">
          <p class="wp_crm_entry_content"><?php echo $entry_text;  ?></p>
        </td>

      </tr><?php

    }

    $new_per_page =  (($args['per_page'])?$args['per_page']:0)+((get_user_option( 'crm_page_wp_crm_add_new_per_page')) ? get_user_option( 'crm_page_wp_crm_add_new_per_page') : 10);

    $rest = count($all_messages)-count($result);

    $output  = array(
      'tbody' => ob_get_clean(),
      'per_page'=> $new_per_page,
      'current_count' => count($result),
      'total_count'  => count($all_messages),
      'more_per_page' => (($rest>=$args['more_per_page'])?$args['more_per_page']:$rest)
    );


    return json_encode($output);

  }



  /**
   * Logs an action
   *
   * @since 0.1
   */
  function insert_event($args = '') {
    global $wpdb, $current_user;

    $defaults = array(
      'object_type' => 'user',
      'user_id' => $current_user->data->ID,
      'attribute' => 'general_message',
      'action' => 'insert',
      'ajax' => 'false',
      'time' => date('Y-m-d H:i:s')
     );

    if(is_array($args)) {
      $args = array_merge($defaults, $args);
    } else {
      $args = wp_parse_args( $args, $defaults );
    }

    //** Convert time - just in case */
    if(empty($args['time'])) {
      $time_stamp = time();
    } else {
      $time_stamp = strtotime($args['time']);
    }

    if($args['attribute'] == 'detailed_log' && empty($args['value'])) {
      $args['value'] = $_SERVER['REMOTE_ADDR'];
    }

    $args['time'] = date('Y-m-d H:i:s', $time_stamp);

    //** Insert event. We double-check $wpdb->crm_log exists in case this function is called very early */
    $wpdb->insert($wpdb->crm_log ? $wpdb->crm_log : $wpdb->base_prefix . 'crm_log', array(
      'object_id' => $args['object_id'],
      'object_type' => $args['object_type'],
      'user_id' => $args['user_id'],
      'attribute' => $args['attribute'],
      'action' => $args['action'],
      'value' => $args['value'],
      'email_from' => $args['email_from'],
      'email_to' => $args['email_to'],
      'text' => $args['text'],
      'other' => $args['other'],
      'time' => $args['time']
    ));

    if($args['ajax'] == 'true')  {
      if($wpdb->insert_id) {
        return json_encode(array('success' => 'true', 'insert_id' => $wpdb->insert_id));
      } else {
        return json_encode(array('success' => 'false'));
      }
    }

    return $wpdb->insert_id;

  }


  /**
   * Get events from log.
   *
   * <code>
   * WP_CRM_F::get_events( array( 'filter_types' => array( array( 'attribute' => 'detailed_log', 'other' => 2, 'hidden' => 'false' )  ) ) )
   * </code>
   *
   * @since 0.1
   */
  function get_events($args = '') {
    global $wpdb, $wp_crm, $current_user;

    $args = wp_parse_args( $args, array(
      'object_type' => 'user',
      'hide_empty' => false,
      'order_by' => 'time',
      'start' => '0',
      'import_count' => ((get_user_option('crm_page_wp_crm_add_new_per_page'))? get_user_option('crm_page_wp_crm_add_new_per_page') : 10),
      'get_count' => 'false',
      'filter_types'=> array(),
      'user_user_data' => false
     ));

    /** if enmpty input 'filter_types' then get filters from get_user_option */
    if (empty($args['filter_types'])){
      $results = $wpdb->get_results("SELECT DISTINCT attribute, other FROM {$wpdb->crm_log}" . (($args['object_id'])?" WHERE object_id=".(int)$args['object_id']:''));
      foreach ($results as $row) {
        if('true'==get_user_option("crm_ui_crm_user_activity_{$row->attribute}".(($row->other)?'['.$row->other.']':''))){
          $args['filter_types'][] = array("attribute"=>$row->attribute,'other'=>$row->other,'hidden'=>'true');
        }else{
          $args['filter_types'][] = array("attribute"=>$row->attribute,'other'=>$row->other,'hidden'=>'false');
        }
      }
    }

    foreach( (array) $args['filter_types'] as $type ) {
      $_attributes[ $type[ 'attribute' ] ] = $type[ 'hidden' ] == 'false' ? true : false;
    }

    if($args['import_count']) {
      $limit = " LIMIT {$args[start]}, {$args[import_count]} ";
    }

    if($args['object_id']) {
      $query[] = " (object_id = '{$args['object_id']}') ";
    }

    if($args['hide_empty']) {
      $query[] = " (object_id = '{$args['object_id']}') ";
    }

    //** If Detailed Activity is tracked */
    if( $wp_crm['configuration']['track_detailed_user_activity'] == 'true' && $_attributes[ 'detailed_log' ]  ) {
      $query[] = " (attribute = 'detailed_log') ";
    } else if( $args['object_type'] ) {
      $query[] = " (text != '') ";
    }

    if(is_array($args['filter_types'])) {
      $temp_type = array();
      $check_all_fields_are_filtered = true;
      foreach ($args['filter_types'] as $filter_type){
        if ($filter_type['hidden']=='true'){
          $temp_type[] =
            'not ('.(($filter_type['attribute'])?"attribute='{$filter_type['attribute']}'":'').
            (($filter_type['attribute']&&$filter_type['other'])?' and ':'').
            (($filter_type['other'])?"other='{$filter_type['other']}'":'').')';
        }else{
          $check_all_fields_are_filtered = false;
        }
      }

      if ($temp_type && !$check_all_fields_are_filtered){
        $query[] = " ( " . implode(" AND ", $temp_type ) . ") ";
      }

    }

    if($query) {
      $query = " WHERE " . implode(' AND ', $query);
    }

    if($args['order_by']) {
      $order_by = " ORDER BY {$args[order_by]} DESC ";
    }

    $sql = "SELECT * FROM {$wpdb->crm_log} {$query} {$order_by} {$limit}";

    $results = $wpdb->get_results($sql);

    if($args['get_count'] == 'true') {
      return count($results);
    }

    return $results;

  }


  /**
   * Dectivate plugin, stop crons.
   *
   * @since 0.01
   *
    */
  function deactivation() {
    global $wp_crm, $wp_roles;

    $timestamp = wp_next_scheduled( 'wp_crm_premium_feature_check' );
    wp_unschedule_event($timestamp, 'wp_crm_premium_feature_check' );
    wp_clear_scheduled_hook('wp_crm_premium_feature_check');

  }


  /**
   * Adds message to global message holder variable.
   *
   * @since 0.01
   *
    */
    function add_message($message, $type = 'good') {
        global $wp_crm_messages;
        if (!is_array($wp_crm_messages))
            $wp_crm_messages = array();

        array_push($wp_crm_messages, array('message' => $message, 'type' => $type));
    }


  /**
   * Prints out global messages and styles them accordingly.
   *
   * @since 0.01
   *
   */
    function print_messages() {
        global $wp_crm_messages;

        echo '<div class="wp_crm_ajax_update_message"></div>';

        if (count($wp_crm_messages) < 1) {
            return;
        }

        $update_messages = array();
        $warning_messages = array();

        echo "<div id='wp_crm_message_stack'>";

        foreach ($wp_crm_messages as $message) {

            if ($message['type'] == 'good')
                array_push($update_messages, $message['message']);

            if ($message['type'] == 'bad')
                array_push($warning_messages, $message['message']);
        }

        if (count($update_messages) > 0) {
            echo "<div class='wp_crm_message wp_crm_yellow_notification updated fade'><p>";
            foreach ($update_messages as $u_message)
                echo $u_message . "<br />";
            echo "</p></div>";
        }

        if (count($warning_messages) > 0) {
            echo "<div class='wp_crm_message wp_crm_red_notification error'><p>";
            foreach ($warning_messages as $w_message)
                echo $w_message . "<br />";
            echo "</p></div>";
        }

        echo "</div>";
    }

    /**
     * Filter user information
     *
     * @param array $current
     * @return array
     */
    function wpi_user_information( $current ) {

      $current = array_reverse($current);
      if ( empty( $current['last_name'] ) ) {
        $current['last_name'] = __('Last Name', 'wp_crm');
      }
      if ( empty( $current['first_name'] ) ) {
        $current['first_name'] = __('First Name', 'wp_crm');
      }

      return array_reverse($current);
    }

    function wp_crm_admin_notice(){
        global $current_screen,$wp_crm;
        if ( $current_screen->id != 'crm_page_wp_crm_settings' || !is_array($wp_crm['data_structure']['attributes'])) return false;
        $required_fields = array('user_email');
        $required_fields = apply_filters('wp_crm_requires_fields',$required_fields);
        foreach ($required_fields as $field){
          if (!array_key_exists($field,$wp_crm['data_structure']['attributes']) ){
            WP_CRM_F::add_message( sprintf( __( 'Warning: there is no field with slug \'%s\' in list of user attributes on Data tab!', 'wp_crm' ), $field ), 'bad' );
          }
        }
    }
    /**
     * Render group select
     * @global array $wp_crm
     * @param array $args
     * @author korotkov@ud
     */
    function attribute_grouping_options( $args ) {
      global $wp_crm;

      $defaults = array();
      extract( wp_parse_args($args, $defaults) );

      if ( !empty( $wp_crm['data_structure']['attribute_groups'] ) ):
        ob_start();
        ?>
        <li>
          <label><?php _e('Group:', 'wp_crm'); ?></label>
          <select class="wp_crm_group" name="wp_crm[data_structure][attributes][<?php echo $slug; ?>][group]">
            <option value="0"><?php _e('Primary Information', 'wp_crm'); ?></option>
            <?php foreach ($wp_crm['data_structure']['attribute_groups'] as $group_key => $group_object): ?>
              <option value="<?php echo $group_key; ?>"<?php echo $wp_crm['data_structure']['attributes'][$slug]['group'] == $group_key ? ' selected="selected"' : ''; ?>><?php echo $group_object['title']; ?></option>
            <?php endforeach; ?>
          </select>
        </li>
      <?php
        $html = apply_filters('wp_crm_attribute_grouping_options', ob_get_contents());
        ob_clean();
        echo $html;
      endif;
    }

    /**
     * Render groups list table
     * @global array $wp_crm
     * @author korotkov@ud
     */
    function add_grouping_settings() {
      global $wp_crm;

      ob_start();
      ?>
      <tr>
        <th><?php _e('Attributes Groups','wp_crm'); ?></th>
        <td>
          <table id="wp_crm_attribute_groups" class="ud_ui_dynamic_table widefat">
            <thead>
              <tr>
                <th class="wp_crm_name_col"><?php _e('Group Name','wp_crm'); ?></th>
                <th class="wp_crm_metabox_col"><?php _e('Metabox Title','wp_crm'); ?></th>
                <th class="wp_crm_delete_col"><?php _e('Actions','wp_crm'); ?></th>
              </tr>
            </thead>
            <tbody>
                <tr>
                  <td>
                    <input readonly="readonly" type="text" value="<?php _e('Primary Information', 'wp_crm'); ?>" />
                  </td>
                  <td>
                    <input readonly="readonly" type="text" value="<?php _e('Primary Information', 'wp_crm'); ?>" />
                  </td>
                  <td>
                  </td>
                </tr>
            <?php
              if ( !empty( $wp_crm['data_structure']['attribute_groups'] ) && is_array($wp_crm['data_structure']['attribute_groups']) ):
                foreach ( $wp_crm['data_structure']['attribute_groups'] as $slug => $value):
            ?>
                  <tr class="wp_crm_dynamic_table_row" slug="<?php echo $slug; ?>"  new_row='false'>
                    <td>
                      <input class="slug_setter" type="text" name="wp_crm[data_structure][attribute_groups][<?php echo $slug; ?>][title]" value="<?php echo $value['title']; ?>" />
                    </td>
                    <td>
                      <input type="text" name="wp_crm[data_structure][attribute_groups][<?php echo $slug; ?>][metabox]" value="<?php echo $value['metabox']; ?>" />
                      <input style="display:none;" type="text" class="slug" readonly="readonly" value="<?php echo $slug; ?>" />
                    </td>
                    <td>
                      <span class="wp_crm_delete_row  button"><?php _e('Delete','wp_crm') ?></span>
                    </td>
                  </tr>
            <?php
                endforeach;
              else :
            ?>
                <tr class="wp_crm_dynamic_table_row" slug="sample"  new_row='true'>
                  <td>
                    <input class="slug_setter" type="text" name="wp_crm[data_structure][attribute_groups][sample][title]" value="Sample" />
                  </td>
                  <td>
                    <input type="text" name="wp_crm[data_structure][attribute_groups][sample][metabox]" value="Sample Attributes" />
                    <input style="display:none;" type="text" class="slug" readonly="readonly" value="sample" />
                  </td>
                  <td>
                    <span class="wp_crm_delete_row  button"><?php _e('Delete','wp_crm') ?></span>
                  </td>
                </tr>
            <?php
              endif;
            ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3">
                  <input type="button" class="wp_crm_add_row button-secondary" value="<?php _e('Add Row','wp_crm') ?>" />
                </td>
              </tr>
            </tfoot>
          </table>
        </td>
      </tr>
      <?php
      $html = apply_filters('wp_crm_add_grouping_settings', ob_get_contents());
      ob_clean();
      echo $html;
    }

    /**
     * Register required metaboxes in order to attribute groups
     * @global object $current_screen
     * @global array $wp_crm
     * @return null ?
     */
    function grouped_metaboxes() {
      global $current_screen, $wp_crm;

      //** If no groups yet */
      if ( empty( $wp_crm['data_structure']['attribute_groups'] ) || !is_array($wp_crm['data_structure']['attribute_groups']) ) return;

      $available_groups = array();
      $primary_group    = array();

      if ( !empty( $wp_crm['data_structure']['attributes'] ) && is_array( $wp_crm['data_structure']['attributes'] ) ) {
        foreach( $wp_crm['data_structure']['attributes'] as $slug => $attribute ) {
          if ( !empty( $attribute['group'] ) && $attribute['group'] != '0' ) {
            if (array_key_exists($attribute['group'], $wp_crm['data_structure']['attribute_groups']) ) {
              $available_groups[$attribute['group']][$slug] = $attribute;
            } else {
              $primary_group[] = $attribute;
            }
          } else {
            $primary_group[] = $attribute;
          }
        }
      }

      if ( empty( $available_groups ) ) return;

      foreach ($available_groups as $group_key => $group_value) {
        add_filter("postbox_classes_{$current_screen->id}_{$group_key}", array(__CLASS__, 'custom_metabox_class'));

        //** Determine metabox title */
        $title = !empty($wp_crm['data_structure']['attribute_groups'][$group_key]['metabox'])
                    ?$wp_crm['data_structure']['attribute_groups'][$group_key]['metabox']
                    :(!empty($wp_crm['data_structure']['attribute_groups'][$group_key]['title'])
                        ?$wp_crm['data_structure']['attribute_groups'][$group_key]['title']
                        :$group_key);

        add_meta_box($group_key, $title, array(__CLASS__, 'custom_group_metabox'), $current_screen->id, 'advanced', 'high', array('fields'=>$group_value));
      }

      //** Remove primary metabox if it has no attrs */
      if ( empty($primary_group) ) {
        remove_meta_box('primary_information', 'crm_page_wp_crm_add_new', 'normal');
      }

    }

    /**
     * Add custom class to metabox
     * @param array $current
     * @return string
     * @author korotkov@ud
     */
    function custom_metabox_class( $current ) {
      $current[] = 'custom_group';
      return $current;
    }

    /**
     * Render custom attributes metabox
     * @global array $wp_crm
     * @param array $post
     * @param array $metabox
     * @return null
     * @author korotkov@ud
     * @todo Maybe we can use crm_page_wp_crm_add_new::primary_information function for this because they are similar
     */
    function custom_group_metabox($post, $metabox) {
      if ( empty( $metabox['args']['fields'] ) && !is_array( $metabox['args']['fields'] ) ) return;
      global $wp_crm;
      $user_role = WP_CRM_F::get_first_value($post['role']);

      ?>
      <table class="form-table">
      <?php if (!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) : ?>
        <?php foreach ($metabox['args']['fields'] as $slug => $attribute):
                $row_classes = array();
                $row_classes[] = (@$attribute['has_options'] ? 'wp_crm_has_options' : 'wp_crm_no_options');
                $row_classes[] = (@$attribute['required'] == 'true' ? 'wp_crm_required_field' : '');
                $row_classes[] = (@$attribute['primary'] == 'true' ? 'primary' : 'not_primary');
                $row_classes[] = ((is_array($wp_crm['hidden_attributes'][$user_role]) && in_array($slug, $wp_crm['hidden_attributes'][$user_role])) ? 'hidden' : '');
                $row_classes[] = 'wp_crm_user_entry_row';
                $row_classes[] = "wp_crm_{$slug}_row";

                $continue = apply_filters("wp_crm_before_{$field}_frontend", array('continue'=>false, 'values' => $post[$slug], 'attribute' => $attribute, 'args' => $metabox['args']));
                if($continue['continue']){ continue; };

        ?>
                <tr meta_key="<?php echo esc_attr($slug); ?>" wp_crm_input_type="<?php echo esc_attr($attribute['input_type']); ?>" class="<?php echo implode(' ', $row_classes); ?>">
                  <th>
                    <?php if (@$attribute['input_type'] != 'checkbox' || isset($attribute['options'])): ?>
                      <?php ob_start(); ?>
                      <label for="wp_crm_<?php echo $slug; ?>_field">
                        <?php echo $attribute['title']; ?>
                      </label>
                      <div class="wp_crm_description"><?php echo $attribute['description']; ?></div>
                      <?php $label = ob_get_contents();
                      ob_end_clean(); ?>
                      <?php echo apply_filters('wp_crm_user_input_label', $label, $slug, $attribute, $post); ?>
                    <?php endif; ?>
                  </th>
                  <td class="wp_crm_user_data_row"  wp_crm_attribute="<?php echo $slug; ?>">
                    <div class="blank_slate hidden" show_attribute="<?php echo $slug; ?>"><?php echo (!empty($attribute['blank_message']) ? $attribute['blank_message'] : "Add {$attribute['title']}"); ?></div>
                    <?php echo WP_CRM_F::user_input_field($slug, $post[$slug], $attribute, $post); ?>
                    <?php if (isset($attribute['allow_multiple']) && $attribute['allow_multiple'] == 'true'): ?>
                      <div class="add_another"><?php _('Add Another'); ?></div>
                    <?php endif; ?>
                  </td>
                </tr>
            <?php
            do_action("wp_crm_after_{$slug}", array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
            endforeach; ?>
          <?php endif; ?>
        </table>
      <?php
    }

    /**
     * Filters attributes to remove unwanted
     * @global array $wp_crm
     * @param array $attributes
     * @return array
     * @author korotkov@ud
     */
    function filter_primary_metabox( $attributes ) {
      global $wp_crm;

      if ( !empty( $attributes ) && is_array( $attributes ) ) {
        foreach ( $attributes as $slug => $attribute ) {
          if ( !empty( $attribute['group'] ) && !empty( $wp_crm['data_structure']['attribute_groups'] ) )
            if ( array_key_exists($attribute['group'], $wp_crm['data_structure']['attribute_groups']) ) {
              unset( $attributes[$slug] );
            }
        }
      }

      return $attributes;
    }
}
