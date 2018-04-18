<?php

/**
 * Class redeclaration fix
 */
if ( !class_exists( 'WP_CRM_AJAX' ) ) {
  
  /**
   * 
   */
  class WP_CRM_AJAX {
    
    /**
     * CSV Export
     */
    static function csv_export() {
      WP_CRM_F::csv_export( $_REQUEST["wp_crm_search"] );
      die();
    }
    
    /**
     * Visualize users
     */
    static function visualize_results() {
      WP_CRM_F::visualize_results( $_REQUEST["filters"] );
      die();
    }
    
    /**
     * Check plugins updates
     */
    static function check_plugin_updates() {
      die( WP_CRM_F::check_plugin_updates() );
    }
    
    /**
     * Debug user by ID
     */
    static function user_object() {
      echo "CRM Object Report: \n" . print_r( wp_crm_get_user( $_REQUEST["user_id"] ), true ) . "\nRaw Meta Report: \n" .  print_r( WP_CRM_F::show_user_meta_report( $_REQUEST["user_id"] ), true );
      die();
    }
    
    /**
     * Debug user by ID
     */
    static function user_search_network() {
      $search = $_REQUEST['term'];
      $users = WP_CRM_F::user_search(
                                array(
                                  'primary_blog' => '',
                                  'search_string' => $search,
                                ),
                                array(
                                  'select_what' => 'ID, display_name, user_email',
                                  'meta_field_search' => '',
                                )
                              );
      wp_send_json($users);
    }
    
    /**
     * Debug meta data
     */
    static function show_meta_report() {
      die( print_r( WP_CRM_F::show_user_meta_report(), true ) );
    }
    
    /**
     * User activity stream
     */
    static function get_user_activity_stream() {
      die( WP_CRM_F::get_user_activity_stream(
        array(
          "user_id" => !empty( $_REQUEST["user_id"] ) ? $_REQUEST["user_id"] : '',
          "per_page" => !empty( $_REQUEST["per_page"] ) ? $_REQUEST["per_page"] : 10,
          "more_per_page" => !empty( $_REQUEST["more_per_page"] ) ? $_REQUEST["more_per_page"] : 10,
          "filter_types" => !empty( $_REQUEST["filter_types"] ) ? $_REQUEST["filter_types"] : ''
        )
      ));
    }
    
    /**
     * Insert activity message
     */
    static function insert_activity_message() {
      $message = $_REQUEST["content"];
      $message = wp_kses($message, array(
        'a' => array(
          'href' => array(),
          'title' => array()
        ),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
      ));
      die( WP_CRM_F::insert_event(
        array(
          'time' => $_REQUEST["time"],
          'attribute' => !empty( $_REQUEST["message_type"] ) ? $_REQUEST["message_type"] : "note",
          'object_id' => $_REQUEST["user_id"],
          'text' => $message,
          'ajax' => 'true'
        )
      ));
    }
    
    /**
     * Get notification template
     */
    static function get_notification_template() {
      die( WP_CRM_F::get_notification_template( $_REQUEST["template_slug"] ) );
    }
    
    /**
     * Create fake users
     */
    static function do_fake_users() {
      $_REQUEST['number'] = isset($_REQUEST['number'])?$_REQUEST['number']:""; 
      die(WP_CRM_F::do_fake_users("number={$_REQUEST['number']}&do_what={$_REQUEST['do_what']}"));
    }
    
    /**
     * List Table
     */
    static function list_table() {
      die( WP_CRM_F::ajax_table_rows() );
    }
    
    /**
     * Quick action
     */
    static function quick_action() {
      die( WP_CRM_F::quick_action() );
    }
    
    /**
     * Check email for duplication
     */
    static function check_email_for_duplicates() {
      die( WP_CRM_F::check_email_for_duplicates( $_REQUEST['email'], $_REQUEST['user_id'] ) );
    }
  }
  
}