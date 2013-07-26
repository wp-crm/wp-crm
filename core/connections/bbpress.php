<?php

/**
  * Name: BB Press Connector
  * Description: Adds Extra functionality to WP-CRM when the BB Press plugin is active.
  * Author: Usability Dynamics, Inc.
  * Version: 1.0
  *
  */


   //** Detect if BBPress is active and load functionality */
   add_action('init', array('WPC_BB_Press', 'init'));

   class WPC_BB_Press {

  /**
   * Main function, determines if BB Press exists
   *
   * @since 0.18
   */
    function init() {
      global $wpdb;

      if(!$wpdb->get_var("show tables like 'bb_posts'")) {
        return;
      }

      add_action('wp_crm_user_loaded', array('WPC_BB_Press', 'wp_crm_user_loaded'));

      add_filter('wp_crm_overview_columns', array('WPC_BB_Press', 'wp_crm_overview_columns'));
      add_filter('wp_crm_overview_cell', array('WPC_BB_Press', 'wp_crm_overview_cell'), 10, 2);
    }


  /**
   * Loads extra data for the user
   *
   * @todo Update so the table prefix is not hardcoded
   *
   */
    function wp_crm_user_loaded($wp_crm_user) {
      global $wp_crm_user, $bb_table_prefix, $wpdb;

      //** Check if BB Press exists */
      $user_id = $wp_crm_user['ID']['default'][0];

      if($bb_data = WPC_BB_Press::get_user_counts($user_id)) {

        $wp_crm_user = $wp_crm_user + $bb_data;

        add_action('wp_crm_metaboxes', array('WPC_BB_Press', 'wp_crm_metaboxes'));
      }
    }


    function get_user_counts($user_id) {
      global $wpdb;

      $week_ago = $mysqldate = date("Y-m-d", strtotime('-1 week'));
      $two_weeks_ago = $mysqldate = date("Y-m-d", strtotime('-2 week'));

      $wp_crm_user['total_posts'] = $wpdb->get_results("SELECT post_id, forum_id, topic_id, post_text FROM bb_posts WHERE poster_id = {$user_id}");
      $wp_crm_user['last_week'] = $wpdb->get_results("SELECT post_id, forum_id, topic_id, post_text FROM bb_posts WHERE poster_id = $user_id AND post_time > '{$week_ago}'");
      $wp_crm_user['last_two_weeks'] = $wpdb->get_results("SELECT post_id, forum_id, topic_id, post_text FROM bb_posts WHERE poster_id = $user_id AND post_time > '{$two_weeks_ago}'");

      foreach($wp_crm_user as $key => $value){
        if(empty($value)) {
          unset($wp_crm_user[$key]);
        }
      }

      if(empty($wp_crm_user)) {
        return false;
      }

      return $wp_crm_user;
    }


    function wp_crm_metaboxes() {
      global $wp_crm_user;
      add_meta_box("Forum", "Forum" , array('WPC_BB_Press', 'metabox'), 'crm_page_wp_crm_add_new', 'normal', 'default');
    }

    function metabox($user_object) {
      global $wpi_settings, $wp_crm_user;
      ?>
      <ul>
        <li><?php _e('Posts', 'wp_crm'); ?>: <?php echo count($wp_crm_user['total_posts']); ?></li>
        <li><?php _e('Last Week', 'wp_crm'); ?>: <?php echo count($wp_crm_user['last_week']); ?></li>
        <li><?php _e('Last Two Weeks', 'wp_crm'); ?>: <?php echo count($wp_crm_user['last_two_weeks']); ?></li>
      </ul>
      <?php

    }


  function wp_crm_overview_cell($current, $data) {


    if($data['column_name'] != 'forum_participation') {
      return $current;
    }

    $bb_data = WPC_BB_Press::get_user_counts($data['user_id']);

    if(!empty($bb_data)) {
      ob_start(); ?>
      <ul>
        <li><?php _e('Posts', 'wp_crm'); ?>: <?php echo count($bb_data['total_posts']); ?></li>
        <li><?php _e('Last Week', 'wp_crm'); ?>: <?php echo count($bb_data['last_week']); ?></li>
        <li><?php _e('Last Two Weeks', 'wp_crm'); ?>: <?php echo count($bb_data['last_two_weeks']); ?></li>
      </ul>
      <?php $echo = ob_get_contents();
      ob_end_clean();

    }

    return $echo;

  }


  function wp_crm_overview_columns($current) {
    $current['forum_participation']['title'] = __( 'Forum Participation', 'wp_crm' );
    $current['forum_participation']['overview_column'] = 'true';
    return $current;

  }


}