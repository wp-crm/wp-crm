<?php
/*
Name: Shortcode Contact Forms
Class: class_contact_messages
Version: 0.2.3
Minimum Core Version: 0.33.0
Description: Create contact forms using shortcodes and keep track of messages in your dashboard.
Feature ID: 12
*/

add_action('wp_crm_init', array('class_contact_messages', 'init'));


/**
 * class_contact_messages Class
 *
 *
 * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
 *
 * @version 1.0
 * @author Andy Potanin <andy.potanin@usabilitydynamics.com>
 * @package WP-CRM
 * @subpackage Contact Forms
 */
class class_contact_messages {

  /**
   * Init level functions for email syncronziation management
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function init() {

    class_contact_messages::add_capabilities();

    add_action("admin_menu", array('class_contact_messages', "admin_menu"), 101);
    add_action('wp_crm_settings_content_contact_messages', array('class_contact_messages', 'settings_page_tab_content'));
    add_action('wp_ajax_process_crm_message', array('class_contact_messages', 'process_crm_message'));
    add_action('wp_ajax_nopriv_process_crm_message', array('class_contact_messages', 'process_crm_message'));
    add_action('admin_enqueue_scripts', array('class_contact_messages', 'admin_enqueue_scripts'));
    add_action('load-crm_page_wp_crm_contact_messages', array('class_contact_messages', 'load_screen'));

    add_action("wp_ajax_wp_crm_messages_table", create_function('',' echo class_contact_messages::ajax_table_rows(); die; '));
    add_action("wp_ajax_wp_crm_visualize_contact_results", create_function('',' class_contact_messages::visualize_contact_results($_REQUEST["filters"]); die();'));
    add_action("wp_ajax_wp_crm_display_shortcode_form", create_function('',' class_contact_messages::display_shortcode_form(array("shortcode" => $_REQUEST["shortcode"], "atts" =>  $_REQUEST["atts"])); die(); '));

    add_filter('wp_crm_settings_nav', array('class_contact_messages', "settings_page_nav"));
    add_filter('widget_text', 'do_shortcode');
    add_filter('wp_crm_notification_actions',array('class_contact_messages', 'default_wp_crm_actions'));
    add_filter('admin_init',array('class_contact_messages', 'admin_init'));
    add_filter('wp_list_table_cell',array('class_contact_messages', 'wp_list_table_cell'));
    add_filter('wp_crm_list_table_object',array('class_contact_messages', 'wp_crm_list_table_object'));
    add_filter('wp_crm_quick_action',array('class_contact_messages', 'wp_crm_quick_action'));

    add_shortcode( 'wp_crm_form',array('class_contact_messages', 'shortcode_wp_crm_form'));

  }

/**
   * Highest level admin functions
   *
   * @since 0.1
   */
   function admin_init() {
     global $wpdb, $wp_crm, $wp_crm_contact_messages_filter;

    //** A work around to load the table columns early enough for ajax functions to use them */
    add_filter("manage_crm_page_wp_crm_contact_messages_columns", array('class_contact_messages', "overview_columns"));

    /** Determine if metabox of sidebar filter should be added to page */
    $wp_crm_contact_messages_filter = false;
    if(count($wp_crm['wp_crm_contact_system_data']) > 1) {
      $wp_crm_contact_messages_filter = true;
    }
    /** Check if we have archived messaged*/
    if($wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->crm_log} WHERE value = 'archived'")) {
      $wp_crm_contact_messages_filter = true;
    }
    $wp_crm_contact_messages_filter = apply_filters('wp_crm_messages_show_filter', $wp_crm_contact_messages_filter);

    if($wp_crm_contact_messages_filter) {
      add_meta_box('wp_crm_messages_filter', __( 'Filter', 'wp_crm' ) , array('class_contact_messages','metabox_filter'), 'crm_page_wp_crm_contact_messages', 'normal', 'default');
    }

   }

/**
   * Highest level admin functions
   *
   * @since 0.1
   */
   function wp_crm_quick_action($action) {
    global $wpdb;

    if($action['action'] == 'trash_message') {

      $success = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->crm_log} WHERE id = {$action['object_id']}"));

      if($success) {
        $return['success'] = 'true';
        $return['message'] = __('Message trashed.', 'wp_crm');
        $return['action'] = 'hide_element';
        return $return;
      }

    }



    return false;
   }


  /**
    * Show user creation UI (mostly for ajax calls)
    *
    * @todo Prone to breaking because of the way values are passed.
    * @since 0.1
    *
    */
    static function display_shortcode_form($args = '') {

      if(!empty($args['shortcode'])) {
        $atts = $args['atts'];

        $atts = stripslashes($atts);

        echo do_shortcode("[{$args['shortcode']} {$atts}]");
      }
    }


/**
   * Sidebar filter for contact messages.
   *
   * @todo finish function
   * @since 0.1
   */
   function metabox_filter($wp_list_table) {
    global $wpdb, $wp_crm;

    $contact_forms = $wp_crm['wp_crm_contact_system_data'];
    $search = $_REQUEST['wp_crm_message_search'];

    if(empty($search)) {
      foreach($contact_forms as $form_slug => $form_data) {
        $search['form_name'][] = $form_slug;
      }
    }

    ?>
    <div class="misc-pub-section">

      <ul class="wp_crm_overview_filters">
      <?php do_action('wp_crm_messages_metabox_filter_before'); ?>

        <li class="wpp_crm_filter_section_title"><?php _e('Status', 'wp_crm'); ?></li>
        <li>
          <input id="wp_crm_attribute_value_new" checked="true" group="wp_crm_message_search_value" type="radio" name="wp_crm_message_search[value]" value="new" />
          <label for="wp_crm_attribute_value_new"><?php _e('New', 'wp_crm'); ?></label>
        </li>


        <li>
          <input id="wp_crm_attribute_value_archived" group="wp_crm_message_search_value" type="radio" name="wp_crm_message_search[value]" value="archived" />
          <label for="wp_crm_attribute_value_archived"><?php _e('Archived', 'wp_crm'); ?></label>
        </li>
        <li>
          <input id="wp_crm_attribute_value_all" group="wp_crm_message_search_value" type="radio" name="wp_crm_message_search[value]" value="all" />
          <label for="wp_crm_attribute_value_all"><?php _e('All', 'wp_crm'); ?></label>
        </li>

      <?php if(is_array($contact_forms)) {  ?>
        <li class="wpp_crm_filter_section_title"><?php _e( 'Originating Form', 'wp_crm' ); ?></li>
      <?php foreach($contact_forms as $form_slug => $form_data) { ?>

        <li>
          <input id="wp_crm_cf_<?php echo $form_slug; ?>" type="checkbox" name="wp_crm_message_search[form_name][]" value="<?php echo $form_slug; ?>" <?php (is_array($search[$form_slug]) && in_array($option_slug, $search[$form_slug]) ? "checked" : ""); ?>/>
          <label for="wp_crm_cf_<?php echo $form_slug; ?>"><?php echo $form_data['title']; ?></label>
        </li>

      <?php } ?>
      <?php }  ?>

      <?php do_action('wp_crm_messages_metabox_filter_after'); ?>
      </ul>



    </div>

    <div class="major-publishing-actions">
      <div class="publishing-action">
        <?php submit_button( __( 'Filter Results', 'wp_crm' ), 'button', false, false, array('id' => 'search-submit') ); ?>
      </div>
      <br class='clear' />
    </div>

    <div class="wp_crm_user_actions">
      <ul class="wp_crm_action_list">
        <li class="button wp_crm_visualize_contact_results"><?php _e('Visualize Contact Data', 'wp_crm'); ?></li>
      <?php do_action('wp_crm_message_actions'); ?>
      </ul>
    </div>

    <script type="text/javascript">
      google.load("visualization", "1", {packages:["annotatedtimeline"]});

      jQuery(".wp_crm_visualize_contact_results").click(function() {

        var filters = jQuery('#wp-crm-filter').serialize()

        jQuery.ajax({
          url: ajaxurl,
          context: document.body,
          data: {
            action : 'wp_crm_visualize_contact_results',
            filters: filters
          },
          success: function(result){
              jQuery('.wp_crm_ajax_result').html(result);
              jQuery('.wp_crm_ajax_result').show("slide", { direction: "down" }, 1000)
          }
        });

      });

  </script>
    <?php

   }


/**
   * Visualize contact data.
   *
   * @todo Add grouping by week - potanin@UD
   * @todo Add selection of messages from new users, who have never submitted a message before - potanin@UD
   *
   * @since 0.20
   *
   */
    function visualize_contact_results($data) {

      global $wpdb;

      parse_str($_REQUEST['filters'], $wp_crm_filter_vars);
      $wp_crm_message_search = $wp_crm_filter_vars['wp_crm_message_search'];

      //** Get users from filter query */
      $wp_crm_message_search['value'] = 'all';
      $wp_crm_message_search['group_by'] = "date_format(time, '%m-%d-%Y') ";

      $wp_crm_message_search['select_fields'] = array(
        'count(id) as daily_messages',
        'date_format(time, "%Y-%m-%d") as date'
      );

      $data = class_contact_messages::get_messages($wp_crm_message_search);

      /*
      $wp_crm_message_search['select_fields'] = array(
        'DISTINCT(object_id)',
        'count(id) as daily_messages',
        'date_format(time, "%Y-%m-%d") as date'
      );

      $distinct_data = class_contact_messages::get_messages($wp_crm_message_search);
      */


      if(empty($data)) {
        die('<div class="wp_crm_visualize_results no_data">' . __('There is not enough quantifiable data to generate any graphs.', 'wp_crm') . '</div>');
      }


       $zoomStartTime = date('Y-m-d', strtotime('-1 month'));

      ?>
      <div class="wp_crm_visualize_results">
      <script type="text/javascript">

        jQuery(document).ready(function() {
           wp_crm_messages_chart();
        });

        function wp_crm_messages_chart() {

          var data = new google.visualization.DataTable({});
          data.addColumn('date', '<?php _e('Date', 'wp_crm'); ?>');
          data.addColumn('number', '<?php _e('All Daily Messages', 'wp_crm'); ?>');
          data.addRows(<?php echo count($data); ?>);

          <?php
          //** Add All Messages */
          foreach($data as $row => $row_data) { ?>
          data.setValue(<?php echo $row; ?>, 0, new Date(<?php echo implode(',', split('-', $row_data['date'])); ?>));
          data.setValue(<?php echo $row; ?>, 1, <?php echo $row_data['daily_messages'];; ?>);
          <?php } ?>

          var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('wp_crm_messages_chart'));
          chart.draw(data, {
            colors: ['red','blue'],
            zoomStartTime:  new Date(<?php echo implode(',', split('-', $zoomStartTime)); ?>)
          });

        }

       </script>

        <div class="wp_crm_chart_wrapper">

          <div id="wp_crm_messages_chart" class="wp_crm_messages_visualization_graph" style='width: 99%; height: 240px;'></div>
        </div>


      </div>

      <?php

    }


/**
   * Hooks into list table cell.
   *
   * Executed on all, so need to only apply to messages.
   * Converts passed object for this table_scope into standard array usable by single_cell
   *
   * @since 0.1
   */
    function wp_crm_list_table_object($data) {


      if($data['table_scope'] != 'wp_crm_contact_messages') {
        return $data;
      }

      $object = (array) $data['object'];


      $return_data = $object;

      //** Rename some keys for convinience */
      $return_data['ID'] = $object['message_id'];
      $return_data['status'] = $object['value'];

      return $return_data;

    }

/**
   * Hooks into list table cell.
   *
   * Executed on all, so need to only apply to messages.
   *
   * @since 0.1
   */
    function wp_list_table_cell($cell_data) {
      global $wp_crm, $wpdb;

      if($cell_data['table_scope'] != 'wp_crm_contact_messages') {
        return $cell_data;
      }

      $object = $cell_data['object'];
      $user_id = $object['user_id'];

      if($associated_object = $object['associated_object']) {
        $associated_object = get_post($associated_object);

        //** Only allow specific post types to be "associated "*/
        if(apply_filters('wp_crm_associated_post_types', false, $associated_object->post_type)) {
          $post_type = get_post_type_object($associated_object->post_type);
        } else {
          unset($associated_object);
        }
      }


      switch($cell_data['column_name']) {

        case 'user_card':

        $r .= WP_CRM_F::render_user_card(array('user_id' => $user_id));

        break;

        case 'messages':

          $total_messages = $object['total_messages'];
          $additional_messages = ($total_messages - 1);
          ob_start();

          ?>

            <ul>
              <li><?php echo  CRM_UD_F::parse_urls(nl2br($object['text']), 100,'_blank'); ?></li>

              <?php if($associated_object) { ?>
              <li><?php echo sprintf(__('Related %s:','wp_crm'), $post_type->labels->singular_name); ?> <a href="<?php echo admin_url("post.php?post={$associated_object->post_ID}&action=edit"); ?>" target="_blank"><?php echo $associated_object->post_title; ?></a></li>
              <?php } ?>

              <li><?php echo human_time_diff(strtotime($object['time'])); ?> <?php _e( 'ago', 'wp_crm' ); ?>.
                <?php if($additional_messages) { echo '<a href="' . admin_url("admin.php?page=wp_crm_add_new&user_id=$user_id") . '">' . $additional_messages . ' ' . __( 'other messages.', 'wp_crm' ) . '</a>'; }  ?>
              </li>
            </ul>

            <?php

            $row_actions = array(
              'trash_message'=>__( 'Trash', 'wp_crm' )
            );

            if($object['status'] != 'archived') {
              $row_actions['archive_message'] = __('Archive', 'wp_crm');
            }

            //** Only allow Trashing of recently registered users */
            $week_ago = date('Y-m-d', strtotime('-3 days'));
            if($wpdb->get_var("SELECT ID FROM {$wpdb->users} WHERE ID = {$user_id} AND user_registered  > '{$week_ago}'") && get_user_meta($user_id, 'wpc_cm_generated_account')) {
              $row_actions['trash_message_and_user'] = __('Trash Message and User', 'wp_crm');
              $verify_actions['trash_message_and_user'] = true;
            }

            $row_actions = apply_filters('wp_crm_message_quick_actions', $row_actions);
            $verify_actions = apply_filters('wp_crm_message_quick_actions_verification', $verify_actions);

              ?>
            <?php if($row_actions) { ?>
            <div class="row-actions">
               <?php foreach($row_actions as $action => $title) { ?>
                  <span  wp_crm_action="<?php echo $action; ?>" <?php echo ($verify_actions[$action] ? 'verify_action="true"' : '');?> object_id="<?php echo $object['ID']; ?>" class="<?php echo $action; ?> wp_crm_message_quick_action"><?php echo $title; ?></span>
               <?php } ?>
              </div>
              <?php } ?>


           <?php
          $content = ob_get_contents();
          ob_end_clean();
          $r .= $content;

        break;

        case 'other_messages':

        break;

      }

      return $r;

    }


    /**
     * Add notification actions for contact message.
     *
     * @since 0.1
     */
    function default_wp_crm_actions($current) {
        global $wp_crm;

        if(is_array($wp_crm['wp_crm_contact_system_data'])) {
            foreach($wp_crm['wp_crm_contact_system_data'] as $contact_form_slug => $form_data) {
                $current[$contact_form_slug] = $form_data['title'];
            }
        }

        return $current;
    }


  function admin_enqueue_scripts() {
      global $current_screen, $wp_properties, $wp_crm;

      // Load scripts on specific pages
      switch($current_screen->id)  {

        case 'crm_page_wp_crm_contact_messages':
          wp_enqueue_script('post');
          wp_enqueue_script('postbox');
          wp_enqueue_script('google-jsapi');
          wp_enqueue_style('wp_crm_global');
          wp_enqueue_script('wp-crm-data-tables');
          wp_enqueue_style('wp-crm-data-tables');
        break;

       }

  }


  function settings_page_nav($current) {
    $current['contact_messages']['slug'] = 'contact_messages';
    $current['contact_messages']['title'] = __('Shortcode Forms', 'wp_crm');

    return $current;

  }


  /**
   * Shortcode for displaying contact forms.
   *
   * @shortcode_atts display_notes true|false If a note exists for an attribute, it will display notes below the input field if its a textbox, or above it if its another type of element.
   *
   * @todo add provision to not display fields that no longer exist
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function shortcode_wp_crm_form($atts, $content = null, $code = '') {
    global $wp_crm;

    $a = shortcode_atts( array(
      'js_callback_function' => false,
      'js_validation_function' => false,
      'form' => false,
      'display_notes' => 'false',
      'require_login_for_existing_users' => 'true',
      'use_current_user' => 'true',
      'success_message' => __( 'Your message has been sent. Thank you.', 'wp_crm' ),
      'submit_text' => __( 'Submit', 'wp_crm' )
    ), $atts );


    if(!$a['form'] || !is_array($wp_crm['wp_crm_contact_system_data'])) {
      return;
    }

    //** Find form based on name */
    foreach($wp_crm['wp_crm_contact_system_data'] as $this_slug => $form_data) {

      //** Check to see if passed form tag matches either the name of the current slug */
      if($form_data['title'] == $a['form'] || $a['form'] == $form_data['current_form_slug']) {
        $form_slug = $this_slug;
        break;
      }
    }

    $form_vars = array(
      'form_slug' => $form_slug,
      'success_message' => $a['success_message'],
      'submit_text' => $a['submit_text']
    );

    if(isset($a['use_current_user'])) {
      $form_vars['use_current_user'] = $a['use_current_user'];
    }

    if($a['js_callback_function']) {
      $form_vars['js_callback_function']  = $a['js_callback_function'];
    }

    if($a['js_validation_function']) {
      $form_vars['js_validation_function']  = $a['js_validation_function'];
    }

    if($a['require_login_for_existing_users'] == 'true') {
      $form_vars['require_login_for_existing_users']  = true;
    }

    if($a['display_notes'] == 'true') {
      $form_vars['display_notes'] = true;
    }

    if($form_slug) {

      ob_start();
      class_contact_messages::draw_form($form_vars);

      $form = ob_get_contents();
      ob_end_clean();
      return $form;
      return preg_replace('(\r|\n|\t)', '', $form);

    } else {
      return;
    }

  }


  /**
   * Echos out contact form
   *
   *
   * @todo add provision to not display fields that no longer exist
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function draw_form($form_settings) {
    global $wp_crm, $post;

    extract($form_settings);

    $form = $wp_crm['wp_crm_contact_system_data'][$form_slug];

    if(empty($form['fields'])) {
      return false;
    }

    WP_CRM_F::force_script_inclusion('jquery-ui-datepicker');
    WP_CRM_F::force_script_inclusion('wp_crm_profile_editor');

    $wp_crm_nonce = md5(NONCE_KEY . $form_slug);

    $wpc_form_id = 'wpc_' . $wp_crm_nonce . '_form';

    //** Load user object if passed */
    if($use_current_user == 'true') {
      $current_user = wp_get_current_user();

      if ( 0 == $current_user->ID ) {
        $user_data = false;
      } else {
        $user_data = wp_crm_get_user($current_user->ID);
      }
    }

    if($require_login_for_existing_users) {
      //** Get array of fields that must be checked to verify if user already exists */
      $check_fields = apply_filters('wp_crm_distinct_user_fields', array('user_email'));
    }

  ?>
  <form id="<?php echo $wpc_form_id; ?>" class="form-horizontal wp_crm_contact_form wp_crm_contact_form_<?php echo $form_slug; ?>">
  <ul class="wp_crm_contact_form">
    <li class="wp_crm_<?php echo $wp_crm_nonce; ?>_first">
      <?php /* Span Prevention */ ?>
        <input type="hidden" name="action" value="process_crm_message" />
        <input type="text" name="wp_crm_nonce" value="<?php echo $wp_crm_nonce; ?>" />
        <input type="text" name="email" />
        <input type="text" name="name" />
        <input type="text" name="url" />
        <input type="text" name="comment" />
        <input type="hidden" name="wp_crm[success_message]" value="<?php echo esc_attr($success_message); ?>" />
        <?php if($user_data) { ?>
        <input type="hidden" name="wp_crm[user_id]" value="<?php echo $current_user->ID; ?>" />
        <?php } ?>
      <?php /* Span Prevention */ ?>
    </li>
  <?php
    $tabindex = 1;

    foreach($form['fields'] as $field) {

      $this_attribute = $wp_crm['data_structure']['attributes'][$field];
      $this_attribute['autocomplete'] = 'false';

      if($user_data && $user_data[$field]) {
        $values = $user_data[$field];
      } else {
        $values = false;
      }
      $continue = apply_filters("wp_crm_before_{$field}_frontend", array('continue'=>false, 'values' => $values, 'attribute' => $this_attribute, 'user_object' => $user_data, 'args' => $args));
      if($continue['continue']){ continue; };

    ?>
    <li class="wp_crm_form_element <?php echo ($this_attribute['required'] == 'true' ? 'wp_crm_required_field' : ''); ?> wp_crm_<?php echo $field; ?>_container">
      <div class="control-group wp_crm_<?php echo $field; ?>_div">
        <label class="control-label wp_crm_input_label"><?php echo $this_attribute['title']; ?></label>
        <div class="controls wp_crm_input_wrapper">
          <?php if($display_notes && $this_attribute['input_type'] != 'text') { ?><span class="wp_crm_attribute_note"><?php echo nl2br($this_attribute['description']); ?></span><?php } ?>
          <?php echo WP_CRM_F::user_input_field($field, $values, $this_attribute, $user_data, "tabindex=$tabindex"); ?>
          <?php if($display_notes && $this_attribute['input_type'] == 'text') { ?><span class="wp_crm_attribute_note"><?php echo nl2br($this_attribute['description']); ?></span><?php } ?>
          <span class="help-inline wp_crm_error_messages"></span>
        </div>
      </div>
    </li>
  <?php
    do_action("wp_crm_after_{$slug}_frontend", array('values' => $values, 'attribute' => $attribute, 'user_object' => $user_object, 'args' => $args));
  $tabindex++; } ?>

  <?php  if($form['message_field'] == 'on') { ?>
  <li class="wp_crm_form_element wp_crm_message_field ">
    <div class="control-group">
      <label class="control-label wp_crm_input_label">&nbsp;</label>
      <div class="controls wp_crm_input_wrapper">
        <?php echo WP_CRM_F::user_input_field('message_field', false,  array('input_type' => 'textarea'), $user_data, "tabindex=$tabindex"); ?>
      </div>
    </div>
  </li>
  <?php } ?>
    <li class="wp_crm_form_response"><div class="wp_crm_response_text" style="display:none;"></li>
    <li class="wp_crm_submit_row">
      <div class="control-group">
        <div class="controls wp_crm_input_wrapper">
          <input class="btn-primary <?php echo md5($wp_crm_nonce . '_submit'); ?>" type="submit" value="<?php echo $submit_text; ?>" />
        </div>
      </div>
      <input type="hidden" name="form_slug" value="<?php echo md5($form_slug); ?>" />
      <input type="hidden" name="associated_object" value="<?php echo $post->ID; ?>" />
    </li>
  </ul>
  </form>

  <style type="text/css">.wp_crm_<?php echo $wp_crm_nonce; ?>_first {display:none;}</style>
  <script type="text/javascript">
    jQuery(document).ready(function() {

      if(typeof wp_crm_developer_log != 'function') {
        function wp_crm_developer_log() {}
      }

      if(typeof _gaq != 'object') {
        var _gaq = false;
      }

      if(_gaq) {
        _gaq.push(['_trackEvent', "Contact Form", "Viewed", "<?php echo esc_attr($form['title']); ?>"]);
      }

      var this_form = jQuery("#<?php echo $wpc_form_id; ?>");
      var submit_button = jQuery("input[type=submit]", this_form);
      var form_response_field = jQuery(".wp_crm_form_response div", this_form);

      var this_form_data = {};
      var validation_error = false;

      jQuery(this_form).change(function(event) {

        if(this_form_data.start_form == undefined) {
          this_form_data.start_form = event.timeStamp;
        }

        if(_gaq && this_form_data.interaction_logged !== undefined) {
          _gaq.push(['_trackEvent', "Contact Form", "Interacted With", "<?php echo esc_attr($form['title']); ?>"]);
          this_form_data.interaction_logged = true;
        }

      });


      jQuery(this_form).submit(function(event) {
        event.preventDefault();
        submit_this_form();
      });

      jQuery(submit_button).click(function(event) {
        event.preventDefault();
        submit_this_form();
      });


    <?php if($require_login_for_existing_users) { foreach($check_fields as $attribute_slug) { ?>
      jQuery(".wp_crm_<?php echo $attribute_slug; ?>_field", this_form).change(function() {
        validation_error = true;
        submit_this_form('system_validate', this);
      });
      <?php } ?>
      <?php } ?>

      function submit_this_form(crm_action, trigger_object) {
        var validation_error = false;
        var form = this_form;

        wp_crm_developer_log('submit_this_form() initiated.');

        if(typeof wp_crm_save_user_form == 'function') {
          /* passed form object into wp_crm_save_user_form() is not usable */
          if(!wp_crm_save_user_form(jQuery(form))) {
            return false;
          }
        } else {
          wp_crm_developer_log('wp_crm_save_user_form() function does not exist.');
        }

        jQuery("*", form).removeClass(form).removeClass("wp_crm_input_error");
        jQuery(".control-group", form).removeClass(form).removeClass("error");

        jQuery("span.wp_crm_error_messages", form).removeClass(form).text("");

        <?php if(isset($form_settings['js_validation_function'])) { ?>
          /** Custom validation */
          if(!validation_error){
            t = <?php echo $form_settings['js_validation_function']; ?>(form);
            if(!t) {
              validation_error = true;
            }
          }
        <?php } ?>

        if(validation_error) {
          jQuery(submit_button).removeAttr("disabled");
          return false;
        }

        params = jQuery(this_form).serialize();

        if(crm_action != 'system_validate') {
          jQuery(submit_button).attr("disabled", "disabled");

          jQuery(form_response_field).show();
          jQuery(form_response_field).removeClass('success');
          jQuery(form_response_field).removeClass('failure');
          jQuery(form_response_field).text("<?php _e('Processing...', 'wp_crm'); ?>");
        }

        if(crm_action) {
          params = params + "&crm_action=" + crm_action;
        }

        jQuery(submit_button).attr("disabled", "disabled");

        jQuery.ajax({
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          dataType: "json",
          data: params,
          cache: false,
          success: function(result) {

            /* Enable submit button in case it was disabled during validation */
            jQuery(submit_button).removeAttr("disabled");

            /* Get conflicting fields */
            if(result.bad_fields !== undefined) {

              jQuery.each(result.bad_fields, function(field) {

                /* If check started by a specific object, we only update it */
                if(jQuery(trigger_object).hasClass("regular-text") && jQuery(trigger_object).attr("wp_crm_slug") != field) {
                  return;
                }

                jQuery("div.wp_crm_"+field+"_div input.regular-text:first, div.wp_crm_"+field+"_div select", form).addClass("wp_crm_input_error");
                jQuery("div.wp_crm_"+field+"_div.control-group", form).addClass("error");
                jQuery("div.wp_crm_"+field+"_div span.wp_crm_error_messages", form).text(result.bad_fields[field]);
              });
            }

            /* If doing only a validation, stop here */
            if(crm_action == 'system_validate') {
              if(result.validation_passed == true) {
                validation_error = true;
              } else {
                validation_error = false;
              }
              return;
            }

            if(result.success == "true") {

              if(_gaq) {
                _gaq.push(['_trackEvent', "Contact Form: <?php echo esc_attr($form['title']); ?>", "Submitted", "Total Time", (+new Date) - this_form_data.start_time]);
              }

              jQuery(form_response_field).addClass("success");
              jQuery(submit_button).removeAttr("disabled");

            } else {

              if(_gaq) {
                _gaq.push(['_trackEvent', "Contact Form: <?php echo esc_attr($form['title']); ?>", "Submission Failure", result.message]);
                this_form_data.interaction_logged = true;
              }


              jQuery(form_response_field).addClass("failure");
              jQuery(submit_button).removeAttr("disabled");
            }

            <?php if($js_callback_function) { ?>
            if(typeof <?php echo $js_callback_function; ?> == 'function') {
              callback_data = {};
              callback_data.form =  jQuery("#<?php echo $wpc_form_id; ?>");
              callback_data.result =  result;
              <?php echo $js_callback_function; ?>(callback_data);
            }
            <?php } ?>

            jQuery(form_response_field).text(result.message);

        },
        error: function(result) {

          jQuery(form_response_field).show();
          jQuery(form_response_field).addClass("failure");
          jQuery(form_response_field).text("<?php _e('A server error occurred while trying to process the form.', 'wp_crm'); ?>");

          jQuery(form_response_field).addClass("failure");
          jQuery(submit_button).removeAttr("disabled");

          if(_gaq) {
            _gaq.push(['_trackEvent', "Contact Form: <?php echo esc_attr($form['title']); ?>", "Submission Failure", "Server error."]);
            this_form_data.interaction_logged = true;
          }

        }
      });

     }

    });
  </script>
  <?php


  }


  /**
   * Insert message into log
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function insert_message($user_id, $message, $form_slug) {
      $insert_id = WP_CRM_F::insert_event("object_id={$user_id}&user_id={$user_id}&attribute=contact_form_message&text={$message}&value=new&other={$form_slug}");

      if($insert_id) {
        return $insert_id;
      }

      return false;
  }

  /**
   * Insert message meta into log meta
   *
   * @version 0.20
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function insert_message_meta($message_id, $meta_key, $meta_value, $args = false) {
    global $wpdb;

    $defaults = array(
      'meta_group' => ''
     );

    $args = wp_parse_args( $args, $defaults );


    $insert['message_id'] = $message_id;
    $insert['meta_key'] = $meta_key;
    $insert['meta_value'] = $meta_value;

    if(!empty($meta_group)) {
      $insert['meta_group'] = $args['meta_group'];
    }

    $wpdb->insert($wpdb->crm_log_meta, $insert);

    return $wpdb->insert_id;

  }


  /**
   * Processes contact form via ajax request.
   *
   * @todo add security precautions to filter out potential SQL injections or bad data (such as account escalation)
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function process_crm_message() {
    global $wp_crm;

    //** Server seems to return nothing somethines, adding space in beginning seems to solve */
    //** This needs to be removed - it causes a warning when the header items are set later in the code, when then causes the form NOT to work echo ' '; */

    //** watch for spam */
    if(!empty($_REQUEST['comment']) ||
          !empty($_REQUEST['email']) ||
            !empty($_REQUEST['name']) ||
              !empty($_REQUEST['url'])) {
      die(json_encode(array('success' => 'false', 'message' => __('If you see this message, WP-CRM thought you were a robot.  Please contact admin if you do not think are you one.','wp_crm'))));
    }

    $data = $_REQUEST['wp_crm'];
    $crm_action = $_REQUEST['crm_action'];

    if(empty($data)) {
      die();
    }

    //** Some other security */
    if(isset($data['user_data']['user_id'])) {
      //** Fail - user_id will never be passed in this manner unless somebody is screwing around */
      die(json_encode(array('success' => 'false', 'message' => __('Form could not be submitted.','wp_crm'))));
    }

    $md5_form_slug = $_REQUEST['form_slug'];
    $associated_object = $_REQUEST['associated_object'];

    foreach($wp_crm['wp_crm_contact_system_data'] as $form_slug => $form_data) {
      if($md5_form_slug == md5($form_slug)) {
        $confirmed_form_slug = $form_slug;
        $confirmed_form_data = $form_data;
        continue;
      }
    }

    if(!$confirmed_form_slug) {
      die();
    }

    if(isset($data['user_id'])) {

      //** User ID was passsed. Verify that current user is logged in */
      $current_user = wp_get_current_user();

      if ( 0 == $current_user->ID || $data['user_id'] != $current_user->ID) {
        //** User ID not found, or passed doesn't match. Either way, fail with ambigous messages.
        die(json_encode(array('success' => 'false', 'message' => __('Form could not be submitted.','wp_crm'))));
      } else {
        //** We have User ID, we are updating an existing profile */
        $data['user_data']['user_id']['default'][] = $current_user->ID;
      }
    }

    //** Get required fields */
    foreach($wp_crm['data_structure']['attributes'] as $field_slug => $field_data) {
      if($field_data['required']) {
        $required_fields[] = $field_slug;
      }
    }

    $check_fields = array();
    if($confirmed_form_data['do_not_check_user_email'] != 'on'){
      $check_fields[] = 'user_email';
    }

    $check_fields = apply_filters('wp_crm_distinct_user_fields',$check_fields);

    //** Do not check any fields if nothing to check */
    foreach($data['user_data'] as $field_slug => $field_data) {

      foreach($field_data as $key => $value) {

        /**
         * If current field is textarea and it has predefined values as CSV then it displays the dropdown
         * under the textarea on front-end. So it is expected that we can use one of them.
         * For instance - type text into textarea or select it in dropdown or even both.
         * This fix is for the case when select option in dropdown.
         *
         * @author korotkov@UD
         */
        if ( $wp_crm['data_structure']['attributes'][$field_slug]['input_type'] == 'textarea' ) {
          if ( !empty( $value['option'] ) && empty( $value['value'] ) ) {
            $data['user_data'][$field_slug][$key]['value'] = $wp_crm['data_structure']['attributes'][$field_slug]['option_labels'][$value['option']];
          }
        }

        $value = WP_CRM_F::get_first_value($value);

        //** Check for completion */
        if($wp_crm['data_structure']['attributes'][$field_slug]['required']) {

          $error = apply_filters('wp_crm_contact_form_data_validation', false, array('field' => $field_slug, 'value' => $value));

          if($error) {
            $bad_fields[$field_slug] = $error;
            continue;
          }

          if(empty($value)) {
            $bad_fields[$field_slug] = sprintf(__('%1s cannot be empty.', 'wp_crm'), $wp_crm['data_structure']['attributes'][$field_slug]['title']);
          }

        }

        //** Check for data conlicts */
        if(is_array($check_fields) && in_array($field_slug, $check_fields)) {

          //** Current field needs to be checked to avoid conflict */
          if($conflict_user_id = WP_CRM_F::check_data_field($field_slug, $value)) {
            if($data['user_data']['user_id']['default'][0] != $conflict_user_id) {
              $bad_fields[$field_slug] = sprintf(__('This %1s belongs to a registered user, please login.', 'wp_crm'), $wp_crm['data_structure']['attributes'][$field_slug]['title']);
            }
          }

        }

      }

    }

    //** If this is a validation request, we check to make sure everything is good */
    if($crm_action == 'system_validate') {

      if($bad_fields) {
        die(json_encode(array('success' => true, 'validation_passed' => false, 'bad_fields' => $bad_fields)));
      } else {
        die(json_encode(array('success' => true, 'validation_passed' => true)));
      }
    }

    if($bad_fields) {
      die(json_encode(array('success' => 'false',  'bad_fields' => $bad_fields, 'message' => __('Form could not be submitted. Please make sure you have entered your information properly.','wp_crm'))));
    }

    $user_data = @wp_crm_save_user_data($data['user_data'], 'default_role='.$wp_crm['configuration']['new_contact_role'].'&use_global_messages=false&match_login=true&no_redirect=true&return_detail=true');

    if(!$user_data) {
      if($confirmed_form_data['message_field'] == 'on') {
        //** If contact form includes a message, notify that message could not be sent */
        die(json_encode(array('success' => 'false', 'message' => __('Message could not be sent. Please make sure you have entered your information properly.','wp_crm'))));
      } else {
        //** If contact form DOES NOT include a message, notify that it could not be submitted */
        die(json_encode(array('success' => 'false', 'message' => __('Form could not be submitted. Please make sure you have entered your information properly.','wp_crm'))));
      }
    } else {
      $user_id = $user_data['user_id'];

      if($user_data['new_user']) {
        //** Log in DB that this account was created automatically via contact form */
        update_user_meta($user_id,'wpc_cm_generated_account', true);
      }

    }

    $message = WP_CRM_F::get_first_value($_REQUEST['wp_crm']['user_data']['message_field']);

    if($confirmed_form_data['notify_with_blank_message'] != 'on' && empty($message)) {
      //** No message submitted */
    } else {

      if(empty($message)) {
        $message = __(' -- No message. -- ', 'wp_crm');
      }

      //** Message is submitted. Do stuff. */
      $message_id = class_contact_messages::insert_message($user_id, $message, $confirmed_form_slug);

      $associated_object = !empty($associated_object) ? $associated_object : false;
      if($associated_object) {
        class_contact_messages::insert_message_meta($message_id, 'associated_object', $associated_object);
      }

      //** Build default notification arguments */
      foreach($wp_crm['data_structure']['attributes'] as $attribute => $attribute_data) {
        $notification_info[$attribute]  = wp_crm_get_value($attribute, $user_id);
      }

      $notification_info['message_content'] = stripslashes($message);
      $notification_info['trigger_action'] = $confirmed_form_data['title'];
      $notification_info['profile_link'] = admin_url("admin.php?page=wp_crm_add_new&user_id={$user_id}");

      /** Add extra filters */
      $maybe_notification_info = apply_filters('wp_crm_notification_info', $notification_info, $associated_object);

      //** Make sure our array wasn't overwritten by a poorly written hooked in function, it shuold never be blank */
      if(!empty($maybe_notification_info) || !is_array($maybe_notification_info)) {
        $notification_info = $maybe_notification_info;
      }

      //** Pass the trigger and array of notification arguments to sender function */
      wp_crm_send_notification($confirmed_form_slug, $notification_info);

    }

    $result = array(
      'success' => 'true',
      'message' => $data['success_message']
    );

    if(current_user_can('manage_options')) {
      $result['user_id'] = $user_id;
    }

    echo json_encode($result);
    die();
  }


  /**
   * Adds content to the Messages tab on the settings page
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function settings_page_tab_content($wp_crm) {

    if(empty($wp_crm['wp_crm_contact_system_data'])) {
      $wp_crm['wp_crm_contact_system_data']['example_form']['title'] = 'Example Contact Form';
      $wp_crm['wp_crm_contact_system_data']['example_form']['full_shortcode'] = '[wp_crm_form form=example_contact_form]';
      $wp_crm['wp_crm_contact_system_data']['example_form']['current_form_slug'] = 'example_contact_form';
    }
  ?>
  <script type="text/javascript">
    jQuery(document).ready(function() {

    jQuery("#wp_crm_wp_crm_contact_system_data .slug_setter").live('change', function() {
      var parent = jQuery(this).parents('.wp_crm_notification_main_configuration');
      jQuery(".wp_crm_contact_form_shortcode", parent).val("[wp_crm_form form=" + wp_crm_create_slug(jQuery(this).val()) + "]");
      jQuery(".wp_crm_contact_form_current_form_slug", parent).val(wp_crm_create_slug(jQuery(this).val()));
    });


    });
  </script>
  <div class="wp_crm_inner_tab">

      <p>
        <?php _e('Use this section to add and configure new shortcode forms.', 'wp_crm'); ?>
      </p>
     <table id="wp_crm_wp_crm_contact_system_data" class="form-table wp_crm_form_table ud_ui_dynamic_table widefat">
      <thead>
        <tr>
           <th class="wp_crm_contact_form_header_col"><?php _e( 'Form Settings', 'wp_crm' ) ?></th>
           <th class="wp_crm_contact_form_attributes_col"><?php _e( 'Fields', 'wp_crm' ) ?></th>
          <?php /* <th class="wp_crm_settings_col"><?php _e( 'Trigger Actions','wp_crm' ) ?></th> */ ?>
          <th class="wp_crm_delete_col">&nbsp;</th>
          </tr>
      </thead>
      <tbody>
      <?php  foreach($wp_crm['wp_crm_contact_system_data'] as $contact_form_slug => $data):  $row_hash = rand(100,999); ?>
        <tr class="wp_crm_dynamic_table_row" slug="<?php echo $contact_form_slug; ?>"  new_row='false'>
          <td class='wp_crm_contact_form_header_col'>
            <ul class="wp_crm_notification_main_configuration">
              <li>
                <label for=""><?php _e('Title:', 'wp_crm'); ?></label>
                <input type="text" id="title_<?php echo $row_hash; ?>" class="slug_setter regular-text" name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][title]" value="<?php echo $data['title']; ?>" />
              </li>

              <li>
                <label><?php _e('Shortcode:', 'wp_crm'); ?></label>
                <input type="text" READONLY class='regular-text wp_crm_contact_form_shortcode'   name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][full_shortcode]"   value="<?php echo $data['full_shortcode']; ?>" />
                <input type="hidden" class='regular-text wp_crm_contact_form_current_form_slug'   name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][current_form_slug]"   value="<?php echo $data['current_form_slug']; ?>" />
              </li>


              <li>
                <label for=""><?php _e( 'Role:', 'wp_crm' ); ?></label>
                <select id="" name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][new_user_role]">
                  <option value=""> - </option>
                  <?php wp_dropdown_roles($data['new_user_role']); ?>
                </select>
                <span class="description"><?php _e( 'If new user created, assign this role.', 'wp_crm' ); ?></span>
             </li>


              <li class="wp_crm_checkbox_on_left">
                <input <?php checked($data['message_field'], 'on'); ?> id="message_<?php echo $row_hash; ?>" type="checkbox"  name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][message_field]"  value="on"  value="<?php echo $data['message_field']; ?>" />
                <label for="message_<?php echo $row_hash; ?>"><?php _e('Display textarea for custom message.', 'wp_crm'); ?></label>
              </li>

              <li class="wp_crm_checkbox_on_left">
                <input <?php checked($data['notify_with_blank_message'], 'on'); ?> id="blank_message<?php echo $row_hash; ?>" type="checkbox"  name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][notify_with_blank_message]"  value="on"  value="<?php echo $data['notify_with_blank_message']; ?>" />
                <label for="blank_message<?php echo $row_hash; ?>"><?php _e('Send message notification even if no message is submitted.', 'wp_crm'); ?></label>
              </li>

              <li class="wp_crm_checkbox_on_left">
                <input <?php checked($data['do_not_check_user_email'], 'on'); ?> id="do_not_check_user_email<?php echo $row_hash; ?>" type="checkbox"  name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][do_not_check_user_email]"  value="on"  value="<?php echo $data['do_not_check_user_email']; ?>" />
                <label for="do_not_check_user_email<?php echo $row_hash; ?>"><?php _e('Do not require users with existing accounts to sign in first.', 'wp_crm'); ?></label>
              </li>

            </ul>
          </td>
          <td>
           <?php if(is_array($wp_crm['data_structure']['attributes'])): ?>
            <ul class="wp-tab-panel">
              <?php foreach($wp_crm['data_structure']['attributes'] as $attribute_slug => $attribute_data):

              if(empty( $attribute_data['title'])) {
                continue;
              }

              ?>
                <li>
                  <input id="field_<?php echo $attribute_slug; ?>_<?php echo $row_hash; ?>" type="checkbox" <?php CRM_UD_UI::checked_in_array($attribute_slug, $data['fields']); ?> name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][fields][]"  value="<?php echo $attribute_slug; ?>" />
                  <label for="field_<?php echo $attribute_slug; ?>_<?php echo $row_hash; ?>"><?php echo $attribute_data['title']; ?></label>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>

          </td>

          <?php /*
          <td class="wp_crm_settings_col">

            <?php if(is_array($wp_crm['notification_actions'])): ?>
            <ul class="wp-tab-panel">
              <?php foreach($wp_crm['notification_actions'] as $action_slug => $action_title):

              if(empty($action_title)) {
                continue;
              }
              ?>
                <li>
                  <input <?php if( $action_slug == $contact_form_slug) echo ' DISABLED checked=true ' ; ?> id="field_<?php echo $action_slug; ?>_<?php echo $row_hash; ?>"  type="checkbox" <?php CRM_UD_UI::checked_in_array($action_slug, $data['fire_on_action']); ?> name="wp_crm[wp_crm_contact_system_data][<?php echo $contact_form_slug; ?>][fire_on_action][]"  value="<?php echo $action_slug; ?>" />
                  <label for="field_<?php echo $action_slug; ?>_<?php echo $row_hash; ?>"><?php echo $action_title; ?></label>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php else: ?>
              <p><?php _e('You do not have any notification actions yet. ', 'wp_crm'); ?></p>
            <?php endif; ?>
          </td>
          */ ?>

          <td valign="middle"><span class="wp_crm_delete_row  button"><?php _e( 'Delete', 'wp_crm' ) ?></span></td>
        </tr>

      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan='4'>
          <input type="button" class="wp_crm_add_row button-secondary" value="<?php _e( 'Add Row', 'wp_crm' ) ?>" />
          </td>
        </tr>
      </tfoot>

      </table>
      <p><?php _e('To see list of variables you can use in wp_crm_contact_system_data open up the "Help" tab and view the user data structure.  Any variable you see in there can be used in the subject field, to field, BCC field, and the message body. Example: [user_email] would include the recipient\'s e-mail.', 'wp_crm'); ?></p>
      <p><?php _e('To add notification actions use the <b>wp_crm_notification_actions</b> filter, then call the action within <b>wp_crm_send_notification()</b> function, and the messages association with the given action will be fired off.', 'wp_crm'); ?></p>


      <table class='form-table'>
        <tr>
          <th><?php _e( 'Options', 'wp_crm' ); ?></th>
          <td>
            <ul>

              <li>
                <label for="wp_crm_new_contact_role"><?php _e( 'Default role to use for new contacts:', 'wp_crm' ); ?> </label>
                 <select id="wp_crm_new_contact_role" name="wp_crm[configuration][new_contact_role]"><option value=""> - </option><?php wp_dropdown_roles($wp_crm['configuration']['new_contact_role']); ?></select>
                 <div class="description"><?php _e('WP-CRM creates user profiles, if only temporary, to store inquiries and messages from shortcode forms.', 'wp_crm'); ?>  </div>
              </li>
            </ul>
          </td>
      </table>

      <?php do_action('wp_crm_settings_notification_tab'); ?>


    </div>

<?php
  }


  /**
   * Ad contact message specific capabilities
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function add_capabilities() {
    global $wp_crm;

    $wp_crm['capabilities']['View Messages'] = __('View messages from shortcode forms.', 'wp_crm');

  }


  /**
   * Modify admin navigational menu to include link(s) for contact message viewing.
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function admin_menu() {
    global $wp_crm;

    //** Check how many message exist */

    $new_message_count = count(class_contact_messages::get_messages());

    //** Only show message section if messages exist */
    /*
    if($message_count) {
    }
    */

    $wp_crm['pages']['contact_messages']['overview'] = add_submenu_page('wp_crm','Messages', 'Messages' . ($new_message_count ? ' (' . $new_message_count . ')' : ''), 'WP-CRM: View Messages', 'wp_crm_contact_messages', array('class_contact_messages', 'page_loader'), '', 30);

    // Add columns to overview page
    add_filter("manage_{$wp_crm['pages']['contact_messages']['overview']}_columns", array('class_contact_messages', "overview_columns"));

  }


 /**
  * Returns columns for specific person type based on $_GET[page] variable
  *
  * @since 0.1
  */
  function overview_columns($columns) {
    global $wp_crm;

    $columns['cb'] = '<input type="checkbox" />';
    $columns['messages'] = __('Message', 'wp_crm');
    $columns['user_card'] = __('Sender', 'wp_crm');


    return $columns;
  }

  /**
   * Adds screen options and contextual help.
   *
   * Called after admin_init and current_screen initialization
   * See: wp-admin/includes/admin.php
   *
   * @action load-crm_page_wp_crm_contact_messages
   * @author peshkov@UD
   */
  function load_screen() {
    global $wp_crm_contact_messages_filter;

    /** Screen Options */
    if(function_exists('add_screen_option')) {
      add_screen_option('layout_columns', array('max' => 2, 'default' => ( $wp_crm_contact_messages_filter ? 2 : 1 )) );
    }
  }

    /**
    * Used for loading back-end UI
    *
    * All back-end pages call this function, which then determines that UI to load below the headers.
    *
    * @since 0.1
   */
  function page_loader() {
    global $wp_crm, $screen_layout_columns, $current_screen, $wpdb, $crm_messages, $user_ID;

    // Figure out what object we are working with
    $object_slug = $current_screen->base;

    if(method_exists('class_contact_messages',$object_slug)) {
      call_user_func(array('class_contact_messages', $object_slug));
    } else {
      echo "<div class='wrap'><h2>Template Error</h2><p>Template via method <b>class_contact_messages::{$object_slug}()</b> not found.</p><div>";
    }

  }

  /**
   * Contact for the contact message overview page
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function crm_page_wp_crm_contact_messages() {
    global $current_screen, $wp_crm, $wpdb, $screen_layout_columns;

    $wp_list_table = new WP_CMR_List_Table("table_scope=wp_crm_contact_messages&per_page=25&ajax_action=wp_crm_messages_table");
    //** Load items into table class */
    $wp_list_table->all_items = class_contact_messages::get_messages();
    //** Items are only loaded, prepare_items() only paginates them */
    $wp_list_table->prepare_items();
    $wp_list_table->data_tables_script();

    ?>
    <div class="wp_crm_overview_wrapper wrap">
      <div class="wp_crm_ajax_result"></div>
      <h2><?php _e('Contact Messages', 'wp_crm'); ?></h2>
      <form id="wp-crm-filter" action="#" method="POST">
        <?php if(!CRM_UD_F::is_older_wp_version('3.4')) : ?>
        <div id="poststuff">
          <div id="post-body" class="metabox-holder <?php echo 2 == $screen_layout_columns ? 'columns-2' : 'columns-1'; ?>">
            <div id="post-body-content">
              <?php $wp_list_table->display(); ?>
            </div>
            <div id="postbox-container-1" class="postbox-container">
              <div id="side-sortables" class="meta-box-sortables ui-sortable">
                <?php do_meta_boxes($current_screen->id, 'normal', $wp_list_table); ?>
              </div>
            </div>
          </div>
        </div><!-- /poststuff -->
        <?php else : ?>
        <div id="poststuff" class="<?php echo $current_screen->id; ?>_table metabox-holder <?php echo 2 == $screen_layout_columns ? 'has-right-sidebar' : ''; ?>">
          <div class="wp_crm_sidebar inner-sidebar">
            <div class="meta-box-sortables ui-sortable">
              <?php do_meta_boxes($current_screen->id, 'normal', $wp_list_table); ?>
            </div>
          </div>
          <div id="post-body">
            <div id="post-body-content">
              <?php $wp_list_table->display(); ?>
            </div><!-- /.post-body-content -->
          </div><!-- /.post-body -->
          <br class="clear" />
        </div><!-- /#poststuff -->
        <?php endif; ?>
      </form>
    </div><!-- /.wp_crm_overview_wrapper -->
    <?php
  }

  /**
   * Main function to query messages from log
   *
   * @version 1.0
   * Copyright 2011 Andy Potanin, Usability Dynamics, Inc.  <andy.potanin@usabilitydynamics.com>
   */
  function get_messages($args = false) {
    global $wpdb;

    $defaults = array(
      'value' => 'new',
      'attribute' => 'contact_form_message',
      'group_by' => 'object_id',
      'form_name' => false,
      'select_fields' => array('id as message_id', 'value', 'object_id as user_id', 'count(id) as total_messages', 'text', 'time'),
      'return_fields' => false
    );

    $args = wp_parse_args( $args, $defaults );

    //** Make sure fields is always either an array, or false
    if(!empty($args['return_fields'])) {

      if(!is_array($args['return_fields'])) {
        $args['return_fields'] = array($args['return_fields']);
      }

    } else {
      $args['return_fields'] = false;
    }


    //** Handle group message queries */
    if($args['attribute'] == 'group_message') {
      unset($args['value']);
    }


    if($args['group_by'] && !empty($args['group_by'])) {
      $group_query = " GROUP BY  {$args['group_by']} ";
    }

    if(!empty($args['attribute'])) {
      $where_query[] = " attribute = '{$args['attribute']}' ";
    }

    if(!empty($args['form_name'])) {

      $where_query[] = ' (other="' . implode(' OR other ="', $args['form_name']) . '")';
    }

    //** Filter by type, unless 'all' is specified */
    if(!empty($args['value']) && $args['value'] != 'all') {
      $where_query[] = " value = '{$args['value']}' ";
    }

    if(!empty($args['select_fields'])) {
      $select_query = implode(', ', $args['select_fields']);
    }

    $where_query  = 'WHERE (' . implode(" AND ", $where_query) . ") ";

    $messages = $wpdb->get_results("SELECT {$select_query} \nFROM {$wpdb->crm_log} \n{$where_query}\n{$group_query}\nORDER BY time DESC", ARRAY_A);

    //** Get messages meta */
    foreach($messages as $key => $message_data) {
      $meta_data = $wpdb->get_results("SELECT * FROM {$wpdb->crm_log_meta} WHERE message_id = {$message_data[message_id]}", ARRAY_A);

      if(!empty($meta_data)) {
        foreach($meta_data as $meta_data) {
          $messages[$key][$meta_data['meta_key']] = $meta_data['meta_value'];
        }
      }

      //** If custom fields are requested, we get them now*/
      if($args['return_fields']) {
        foreach($messages[$key] as $meta_key  => $meta_value) {

          if(!in_array($meta_key, $args['return_fields'])) {
            unset($messages[$key][$meta_key]);
          }

        }
      }

    }




    $messages = stripslashes_deep($messages);

    return $messages;

  }


/**
  * Draws table rows for ajax call
  *
  *
  * @since 0.1
  *
  */
  function ajax_table_rows() {

    include WP_CRM_Path . '/core/class_user_list_table.php';

    //** Get the paramters we care about */
    $sEcho = $_REQUEST['sEcho'];
    $per_page = $_REQUEST['iDisplayLength'];
    $iDisplayStart = $_REQUEST['iDisplayStart'];
    $iColumns = $_REQUEST['iColumns'];

    parse_str($_REQUEST['wp_crm_filter_vars'], $wp_crm_filter_vars);
    $wp_crm_message_search = $wp_crm_filter_vars['wp_crm_message_search'];

    //* Init table object */
    $wp_list_table = new WP_CMR_List_Table("current_screen=crm_page_wp_crm_contact_messages&table_scope=wp_crm_contact_messages&ajax=true&per_page={$per_page}&iDisplayStart={$iDisplayStart}&iColumns={$iColumns}");

    //** Load items into table class */
    $wp_list_table->all_items = class_contact_messages::get_messages($wp_crm_message_search);

    $wp_list_table->prepare_items();

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
      'aaData' => $data
    ));

  }

}

