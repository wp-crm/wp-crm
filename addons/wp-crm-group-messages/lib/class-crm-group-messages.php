<?php
/*
  Name: CRM Group Messages
  Class: class_crm_group_messages
  Version: 1.0.0
  Minimum Core Version: 1.0.0
  Description: Group messaging system for WP-CRM
  Feature ID: 11
 */

/**
 * class_crm_group_messages Class
 *
 *
 * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
 *
 * @version 1.0
 * @author Andy Potanin <andy.potanin@twincitiestech.com>
 * @package WP-Property
 * @subpackage Admin Functions
 */
class class_crm_group_messages {

  /**
   * Init level functions for newsletter management
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function init() {

    if (current_user_can('WP-CRM: Send Group Message')) {
      add_action("wp_crm_user_actions", array('class_crm_group_messages', "wp_crm_user_actions"));

      add_action('wp_ajax_wp_crm_display_newsletter_ui', create_function("", '  echo class_crm_group_messages::display_newsletter_ui($_REQUEST["filters"]); die();'));

      add_action('wp_ajax_wp_crm_n_send_message', create_function("", '  echo class_crm_group_messages::send_message($_REQUEST); die();'));

      add_action('wp_ajax_newsletter_get_log', create_function("", ' class_crm_group_messages::send_log($_REQUEST["newsletter_id"]); die();'));

      add_filter('toplevel_page_wp_crm_help', array('class_crm_group_messages', 'wp_crm_contextual_help'));

      add_filter('wp_crm_messages_show_filter', array('class_crm_group_messages', 'messages_show_filter'));

      add_filter('wp_crm_messages_metabox_filter_before', array('class_crm_group_messages', 'messages_metabox_filter_before'));

      //** Settings Page */
      //add_action('wp_crm_settings_content_group_messages', array('class_crm_group_messages', 'wp_crm_settings_content_group_messages'));
      //add_filter('wp_crm_settings_nav', array('class_crm_group_messages', 'settings_nav'));
    }

    add_filter('wp_crm_entry_type_label', array('class_crm_group_messages', 'wp_crm_entry_type_label'));


    add_filter('wp_crm_settings_lower', array('class_crm_group_messages', 'wp_crm_settings_lower'));
  }

  /**
   * Adds admin tools manu to settings page navigation
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function settings_nav($tabs) {

    $tabs['admin_tools'] = array(
        'slug' => 'group_messages',
        'title' => __('Group Messages', ud_get_wp_crm_group_messages()->domain)
    );

    return $tabs;
  }

  /**
   * Load information into settings towards the bottom on init
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function wp_crm_settings_lower($wp_crm) {

    //** Check Display Name by default. */
    if (empty($wp_crm['group_messages']['pdf_list_attributes'])) {
      $wp_crm['group_messages']['pdf_list_attributes'] = array('display_name' => 'display_value');
    }

    return $wp_crm;
  }

  /**
   * Content for "Group Messages" Settings Page tab.
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function wp_crm_settings_content_group_messages($wp_crm) {

    $pdf_list_attributes = $wp_crm['data_structure']['attributes'];
    ?>


        <table class="form-table">
          <tr>
            <th><?php _e('PDF Attachment', ud_get_wp_crm_group_messages()->domain); ?>
            </th>
            <td>
    <?php _e('User attributes to include in PDF Attachments for each recipient without an e-mail:', ud_get_wp_crm_group_messages()->domain); ?>
              <div class="wp-tab-panel">
                <ul>
    <?php foreach ($pdf_list_attributes as $slug => $data) { ?>
                      <li>
                        <input id="wp_crm_group_message_pdf_list_attribute_<?php echo $slug; ?>" value="display_value" type="checkbox" <?php checked( !empty($wp_crm['group_messages']['pdf_list_attributes'][$slug])?$wp_crm['group_messages']['pdf_list_attributes'][$slug]:false, 'display_value' ); ?> name="wp_crm[group_messages][pdf_list_attributes][<?php echo $slug; ?>]" />
                        <label for="wp_crm_group_message_pdf_list_attribute_<?php echo $slug; ?>" ><?php echo $data['title']; ?> <span class="description"><?php echo $data['description']; ?></span></label>
                      </li>
          <?php } ?>
                </ul>
              </div>
              <div class="description"><?php _e('When message recipients include users without e-mails, a PDF list of such recipients can be distributed amongst recipients with e-mails.', ud_get_wp_crm_group_messages()->domain); ?></div>
            </td>
          </tr>
        </table>

              <?php
  }

  /**
   * Display checkboxes for sent out group messages
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function messages_metabox_filter_before($current) {
    global $wpdb;

    if (!$wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->crm_log} WHERE attribute = 'group_message'")) {
      return;
    }
    ?>
    <li>
    <li class="wpp_crm_filter_section_title"><?php _e('Type', ud_get_wp_crm_group_messages()->domain); ?></li>

    <li>
      <input id="wp_crm_contact_form_message" type="radio" checked="true" name="wp_crm_message_search[attribute]" value="contact_form_message" />
      <label for="wp_crm_contact_form_message"><?php _e('Shortcode Form Messages', ud_get_wp_crm_group_messages()->domain); ?></label>
    </li>

    <li>
      <input id="wp_crm_attribute_group_message" type="radio" name="wp_crm_message_search[attribute]" value="group_message" />
      <label for="wp_crm_attribute_group_message"><?php _e('Group Messags', ud_get_wp_crm_group_messages()->domain); ?></label>
    </li>

    </li>
    <?php
  }

  /**
   * Hook into decision if sidebar filter should be displayed on the messages page.
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function messages_show_filter($current) {
    global $wpdb;

    //* Show filter if there have been any outgoing group messages */
    if ($wpdb->get_var("SELECT count(object_type) FROM {$wpdb->crm_log} WHERE object_type = 'group' AND attribute ='group_message'")) {
      return true;
    }

    return $current;
  }

  /**
   * Changes label type in user activity stream
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function wp_crm_entry_type_label($entry) {

    if ($entry == 'group_message') {
      return __('Group Message', ud_get_wp_crm_group_messages()->domain);
    }

    return $entry;
  }

  /**
   * Display newsletter specific contextual help
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function wp_crm_contextual_help($data = '') {

    $data['Group Messages'][] = '<h3>' . __('Group Messages (Newsletters)', ud_get_wp_crm_group_messages()->domain) . '</h3>';
    $data['Group Messages'][] = '<p>' . __('To send a group message, first use the filters to narrow down to the users you want to contact.', ud_get_wp_crm_group_messages()->domain) . '</p>';
    $data['Group Messages'][] = '<p>' . __('Once the users are filtered, click "Show Actions", and "Send Group Message" to begin creating a message.', ud_get_wp_crm_group_messages()->domain) . '</p>';

    return $data;
  }

  /**
   * Displays UI for editing a newsletter
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function display_newsletter_ui($filters = '') {
    global $wpdb, $wp_crm;

    parse_str($filters, $filters);
    $wp_crm_search = $filters['wp_crm_search'];

    $rand = rand(100, 999);

    $users = WP_CRM_F::user_search($wp_crm_search);

    $current_user = wp_get_current_user();

    if (is_array($users)) {
      foreach ($users as $this_email_count => $user) {

        $user_email = WP_CRM_F::get_user_email($user->ID);

        if ($user_email) {
          $user_emails[] = $user_email;
        } else {
          $no_emails[] = $user->ID;
        }
      }

      if (count($user_emails) > 5) {
        $more_emails = (count($user_emails) - $this_email_count);
        $more_emails = count($user_emails);

        $display_user_emails = $user_emails;
        array_splice($display_user_emails, 5);

        $user_emails = $display_user_emails;

        $user_emails[] = sprintf(_n("and %d other email", "and  %d other emails", $more_emails), $more_emails);
      }
    }

    if (is_array($wp_crm['notifications'])) {
      $has_templates = true;
    }

    $total_users = count($users);

    //** Check if Configure SMTP Plugin Exists */
    $c2c_configure_smtp = get_option('c2c_configure_smtp');

    if (is_array($c2c_configure_smtp)) {
      $send_from_emails[$c2c_configure_smtp['from_email']] = $c2c_configure_smtp['from_name'];
    }
    if (!empty($wp_crm['configuration']['default_sender_email'])) {
      /* if default_sender_email is clear email */
      if (filter_var($wp_crm['configuration']['default_sender_email'], FILTER_VALIDATE_EMAIL)) {
        $send_from_emails[$wp_crm['configuration']['default_sender_email']] = $wp_crm['configuration']['email']['sender_name'];
        /* if email looks like: User Name <user@email> */
      } elseif (preg_match("~([^<]+)<([^>]+)>~", $wp_crm['configuration']['default_sender_email'], $match_email) && filter_var($match_email[2], FILTER_VALIDATE_EMAIL)) {
        $send_from_emails[$match_email[2]] = $match_email[1];
      }
    }

    $send_from_emails[get_option('admin_email')] = get_option('admin_email');
    $send_from_emails[$current_user->user_email] = $current_user->data->display_name;

    $send_from_emails = array_unique(apply_filters('wp_crm_send_from_emails', $send_from_emails));

    //** Remove any blank values from array */
    $send_from_emails = array_filter($send_from_emails);
    ?>

    <div class="wp_crm_newsletter_ui">

      <script type="text/javascript">
        jQuery(document).ready(function () {

          var wpc_n_form = jQuery("#wp_crm_newsletter_edit_form_<?php echo $rand; ?>");

          jQuery( document ).on("change", ".wpc_no_email_roles", function () {

            var this_value = jQuery(this).val();

            if (this_value == "") {
              jQuery(".wpc_option_break_up_report_amongst_group", wpc_n_form).prop("checked", false);
            } else {
              jQuery(".wpc_option_break_up_report_amongst_group", wpc_n_form).prop("checked", true);
            }
            jQuery(".wpc_option_break_up_report_amongst_group", wpc_n_form).attr("wp_crm_action_detail", this_value);
          });


          jQuery(".newsletter_subject", wpc_n_form).focus();

          jQuery( wpc_n_form ).on("change", ".message_template", function () {

            var template_slug = jQuery("option:selected", this).val();
            var notification_type = jQuery("option:selected", this).attr('notification_type');

            if (template_slug == "") {
              return;
            }

            if (notification_type == "crm_notification") {

              jQuery.post(ajaxurl, {
                action: "wp_crm_get_notification_template",
                template_slug: template_slug
              }, function (result) {

                jQuery('.newsletter_subject', wpc_n_form).val(result.subject);
                jQuery('.newsletter_content', wpc_n_form).val(result.message);


              }, "json");

            } else if (notification_type == "sent_newsletter") {

            }

          });


          jQuery( wpc_n_form ).on("click", ".send_message", function () {

            jQuery('.wp_crm_group_message_response', wpc_n_form).hide();

    <?php if (count($users) > 20) { ?>;
              if (!confirm("<?php printf(__("You are about to send this message to %d users, are you sure?.", ud_get_wp_crm_group_messages()->domain), count($users)); ?>")) {
                return;
              }
    <?php } ?>


            jQuery(this).val("<?php _e('Sending...', ud_get_wp_crm_group_messages()->domain); ?>");

            /* jQuery(this).attr("disabled", true); */

            var params = {}

            params = {
              action: 'wp_crm_n_send_message',
              users: <?php echo json_encode($wp_crm_search); ?>,
              subject: jQuery('.newsletter_subject', wpc_n_form).val(),
              message: jQuery('.newsletter_content', wpc_n_form).val(),
              from_email: jQuery('.send_from option:selected', wpc_n_form).attr('from_email'),
              from_name: jQuery('.send_from option:selected', wpc_n_form).attr('from_name')
            }

            if (jQuery(".wp_crm_no_user_action:checked").length) {

              params.no_user_acount = new Array();

              jQuery(".wp_crm_no_user_action:checked").each(function () {

                var this_action = {}

                this_action.type = jQuery(this).attr("wp_crm_action");
                this_action.detail = jQuery(this).attr("wp_crm_action_detail");

                params.no_user_acount.push(this_action);

              });
            }


            jQuery.ajax({
              method: "post",
              url: ajaxurl,
              data: params,
              dataType: "json",
              success: function (result) {

                if (result.success == 'true') {

                  jQuery(wpc_n_form).remove();
                  jQuery('.wp_crm_ajax_update_message').show();
                  jQuery('.wp_crm_ajax_update_message').addClass('updated fade');
                  jQuery('.wp_crm_ajax_update_message').html('<p>' + result.message + '</p>');
                } else {
                  jQuery('.send_message', wpc_n_form).val("<?php _e('Send', ud_get_wp_crm_group_messages()->domain); ?>");
                  jQuery('.send_message', wpc_n_form).removeAttr("disabled");
                  jQuery('.wp_crm_group_message_response', wpc_n_form).show();
                  jQuery('.wp_crm_group_message_response', wpc_n_form).html(result.message);
                }

              }
            });

          });


          jQuery( wpc_n_form ).on("click", ".discard_message", function () {
            jQuery(".wp_crm_newsletter_ui").remove();
          });
        });
      </script>



      <form action="" id="wp_crm_newsletter_edit_form_<?php echo $rand; ?>">
        <ul class="wp_crm_newsletter_ui_fields">
          <li class="wpc_section_wrapper">
            <label><?php _e('From:', ud_get_wp_crm_group_messages()->domain); ?> </label>
            <select class="send_from">
    <?php foreach ($send_from_emails as $from_email => $from_name) { ?>
                <option from_email="<?php echo esc_attr($from_email); ?>" from_name="<?php echo esc_attr($from_name); ?>"><?php echo esc_attr($from_name); ?> <?php echo esc_attr('<' . $from_email . '>'); ?> </option>
    <?php } ?>
            </select>
          </li>

          <li class="wpc_section_wrapper">
            <label><?php _e('To:', ud_get_wp_crm_group_messages()->domain); ?> </label>
            <div class="send_to"><?php echo @implode(", ", $user_emails); ?></div>
          </li>

    <?php if ($has_templates) { ?>
            <li class="wpc_section_wrapper">
              <label><?php _e('Template:', ud_get_wp_crm_group_messages()->domain); ?> </label>
              <select class="message_template">
                <option></option>
      <?php foreach ($wp_crm['notifications'] as $slug => $notification_data) { ?>
                  <option notification_type="crm_notification" value="<?php echo esc_attr($slug); ?>"><?php echo esc_attr($notification_data['subject']); ?> </option>
      <?php } ?>
              </select>
            </li>
              <?php } ?>

          <li class="wpc_section_wrapper">
            <label><?php _e('Subject:', ud_get_wp_crm_group_messages()->domain); ?> </label>
            <input type="text" class="regular-text newsletter_subject" value="" />
          </li>



          <li class="wpc_section_wrapper">
            <textarea class="newsletter_content"></textarea>
          </li>

    <?php if ( isset($no_emails) ) { ?>
            <li class="wpc_section_wrapper wp_crm_no_email_handling">

                <?php echo sprintf(__('Be advised, %1d of the %2d intended recipients do not have an e-mail addresses.  <span class="wp_crm_secondary_action wp_crm_toggle hidden" toggle="wp_crm_no_email_actions">Take action.</span>', ud_get_wp_crm_group_messages()->domain), count($no_emails), count($users)); ?>

                <?php
                $no_email_options = apply_filters('wp_crm_no_email_options', array(
                    'break_up_report_amongst_group' => sprintf(__('Evenly break-up the users amongst recipients in the %1s group, and send them the user report.', ud_get_wp_crm_group_messages()->domain), WP_CRM_F::wp_dropdown_roles(array('class' => 'wpc_no_email_roles'))),
                    'send_me_report' => __('E-mail me full report of all users without e-mail addresses.', ud_get_wp_crm_group_messages()->domain)
                ));
                ?>

      <?php if (is_array($no_email_options)) { ?>
                <ul class="wp_crm_no_email_actions">
        <?php foreach ($no_email_options as $option_slug => $option_label) { ?>
                    <li>
                      <input type="checkbox" class="wpc_option_<?php echo esc_attr($option_slug); ?> wp_crm_no_user_action" wp_crm_action="<?php echo esc_attr($option_slug); ?>" />
                      <label class=""><?php echo $option_label; ?></label>
                    </li>
        <?php } ?>
                </ul>
            <?php } ?>


            </li>

            <?php } ?>
        </ul>

        <div class="wp_crm_newsletter_actions">
          <input type="button" class="send_message" value="<?php _e("Send Message", ud_get_wp_crm_group_messages()->domain); ?>" />
          <input type="button" class="discard_message" value="<?php _e("Discard", ud_get_wp_crm_group_messages()->domain); ?>" />
          <span class="wp_crm_group_message_response"></span>
        </div>
      </form>

    </div>

    <?php
  }

  /**
   * Set sender email filter
   *
   * @param string $current
   * @return string
   * @author korotkov@ud
   */
  static function wp_mail_from($current) {

    if (!empty($_REQUEST["from_email"]) && filter_var($_REQUEST["from_email"], FILTER_VALIDATE_EMAIL)) {
      return $_REQUEST["from_email"];
    }
    return $current;
  }

  /**
   * Set sender name filter
   *
   * @param string $current
   * @return string
   * @author korotkov@ud
   */
  static function wp_mail_from_name($current) {
    if (!empty($_REQUEST["from_name"])) {
      return $_REQUEST["from_name"];
    }
    return $current;
  }

  /**
   * Send out message(s)
   *
   * @version 1.0
   * Copyright 2011 Usability Dynamics, Inc.  <andy.potanin@twincitiestech.com>
   */
  static function send_message($data) {
    global $wp_crm, $wpdb;

    $m['subject'] = $data['subject'];
    $m['message'] = stripslashes($data['message']);
    $m['headers'][] = "Content-Type: text/html";
    $m['headers'] = implode($m['headers']);

    $users = WP_CRM_F::user_search($data['users']);
    //print_r($users);

    if ($data["from_email"]) {
      $m['from_email'] = $data["from_email"];
      add_filter('wp_mail_from', array(__CLASS__, 'wp_mail_from'));
    }

    if ($data["from_name"]) {
      $m['from_name'] = $data["from_name"];
      add_filter('wp_mail_from_name', array(__CLASS__, 'wp_mail_from_name'));
    }

    if ( isset($data['single_recipient']) ) {
      $m['recipients'][0]['user_email'] = $data['single_recipient'];
      $m['recipients'][0]['user_id'] = email_exists($data['single_recipient']);
    } else {

      foreach ($users as $user_key => $single_user) {

        $this_user_email = WP_CRM_F::get_user_email($single_user->ID);

        if ($this_user_email) {
          $m['recipients'][$user_key]['user_email'] = $this_user_email;
          $m['recipients'][$user_key]['user_id'] = $single_user->ID;
        } else {
          $no_email_users[] = wp_crm_get_user($single_user->ID);
        }
      }
    }


//    if ( isset($data['no_user_acount']) && is_array( $data['no_user_acount'] ) ) {
//
//      //** Prepare for no-email actions */
//
//      foreach ($data['no_user_acount'] as $action_detail) {
//
//        if ($action_detail['type'] == 'break_up_report_amongst_group') {
//
//          $branch_role = $action_detail['detail'];
//
//
//          $break_out_bad_emails = true;
//
//          $bad_users = count($no_email_users);
//
//          //** Get recipients that are going to be handling bad users */
//          foreach ($m['recipients'] as $recipient_data) {
//
//            if (user_can($recipient_data['user_id'], $branch_role)) {
//              $m['branch_users'][] = $recipient_data['user_id'];
//            }
//          }
//
//          if (count($m['branch_users'])) {
//            $good_users = count($m['branch_users']);
//
//            $bad_per_good = ceil($bad_users / $good_users);
//
//            $bad_user_chunks = array_chunk($no_email_users, $bad_per_good);
//          } else {
//            //** No branches found from list */
//            $response['success'] = 'false';
//            $response['message'] = __('There are no users in this list that have e-mails and the necessary role to disseminate the information.', ud_get_wp_crm_group_messages()->domain);
//            die(json_encode($response));
//          }
//        }
//
//        if ($action_detail['type'] == 'send_me_report') {
//          $send_me_bad_email_report = true;
//        }
//      }
//    }

    //** Save Template */
    $group_message_id = WP_CRM_F::insert_event(
                    array(
                        'object_type' => 'group',
                        'attribute' => 'group_message',
                        'subject' => $m['subject'],
                        'text' => $m['message']
                    )
    );

    $this_count = 0;
    $this_branch_count = 0;
    
    $table_columns = $wpdb->get_col("SHOW COLUMNS FROM {$wpdb->users}");

    foreach ($m['recipients'] as $user_key => $single_recipient) {

      $these_bad_recipients = array();
      $this_bad_user_csv_string = array();

      /**
       * Specify message and subject based on $m data for current recipient
       * @author korotkov@ud
       */
      $message = $m['message'];
      $subject = $m['subject'];

      $this_recipient = wp_crm_get_user($single_recipient['user_id']);
      
      $primary = $wpdb->get_row("SELECT * FROM {$wpdb->users} WHERE ID = {$single_recipient['user_id']}", ARRAY_A);
      
      $user = array();

      //** Loop data attributes */
      foreach( $wp_crm['data_structure']['attributes'] as $_attr_key => $attr_data ) {
        
        //** If key is from table cols - just use it's value */
        if ( in_array( $_attr_key, $table_columns ) ) {
          $value = $primary[$_attr_key];
          
        } else {
          
          //** Otherwice - if attribute has options then process it is an array of values */
          if ( !empty( $attr_data['has_options'] ) ) {
            
            //** Flush */
            $values = array();
            
            //** Loop options key */
            foreach( $attr_data['option_keys'] as $option_key => $meta_key ) {
              
              //** If something found as checked/selected */
              if ( $v = get_user_meta( $single_recipient['user_id'], $meta_key, true ) ) {
                
                //** If type of attribute is text but it has options  */
                if ( $attr_data['input_type'] === 'text' ) {
                  
                  //** Use both values to not confuse */
                  $values[] = $attr_data['option_labels'][$option_key].' ('.$v.')';
                } else {
                  
                  //** Otherwice - just use label of option since we need only it */
                  $values[] = $attr_data['option_labels'][$option_key];
                }
              }
            }
            
            //** Implode with comma all stuff we found chacked or selected */
            $value = implode( ',', $values );
          } else {
          
            //** If doen't have options then just use meta value */
            $value = get_user_meta( $single_recipient['user_id'], $_attr_key, true );
          }
        }
        
        $user[$_attr_key] = $value;
        
      }

      foreach ($user as $slug => $value) {
        /**
         * Replace tags in specified message and subject
         * @author korotkov@ud
         */
        $message = str_replace("[{$slug}]", $value, $message);
        $subject = str_replace("[{$slug}]", $value, $subject);
      }

      //** Replace any blank [whatever] */
      $message = preg_replace('/\[(.+?)\]/si', '', $message);
      $subject = preg_replace('/\[(.+?)\]/si', '', $subject);

//      $attachments = array();
//
//      //** If we are disseminating information, and the current user is part of the branch list */
//      if (isset($break_out_bad_emails) && $break_out_bad_emails && in_array($single_recipient['user_id'], $m['branch_users'])) {
//
//        $pdf_file = class_crm_group_messages::generate_pdf_email_report(array(
//                    'data' => $data,
//                    /**
//                     * 'message' should content current user's subject so we can't use $m here.
//                     * @author korotkov@ud
//                     */
//                    'message' => array('subject' => $subject),
//                    'no_email_users' => $bad_user_chunks[$this_branch_count]
//        ));
//
//        if ($pdf_file['pdf_file_path']) {
//          $attachments[] = $pdf_file['pdf_file_path'];
//
//          foreach ($bad_user_chunks[$this_branch_count] as $bad_user) {
//            $this_bad_user_csv_string[] = WP_CRM_F::get_primary_display_value($bad_user);
//            $these_bad_recipients[] = $bad_user;
//          }
//        }
//
//        $this_branch_count++;
//      }

      $wp_mail_response = wp_mail($single_recipient['user_email'], $subject, nl2br($message), $m['headers']); //, $attachments);

      $wp_mail_response = true;

      if ($wp_mail_response) {

        //* Reference sent email to recipient */
        WP_CRM_F::insert_event(
                array(
                    'object_id' => $single_recipient['user_id'],
                    'value' => $group_message_id,
                    'email_from' => $m['from_email'],
                    'email_to' => $single_recipient['user_email'],
                    'attribute' => 'group_message',
                    'subject' => $subject,
                    'text' => $message, // . sprintf(__('<p class="wp_crm_system_message">User received PDF Attachment and asked //to contact the following recipients: %1s </p>', ud_get_wp_crm_group_messages()->domain), implode(', ', (//array) $this_bad_user_csv_string))
                )
        );

        $sent_count[] = true;

        //** If this message included an attachment of bad recipients */
//        if (!empty($these_bad_recipients)) {
//          foreach ($these_bad_recipients as $bad_user) {
//
//            //* Reference sent email to recipient */
//            WP_CRM_F::insert_event(
//                    array(
//                        'object_id' => $bad_user['ID']['default'][0],
//                        'value' => $group_message_id,
//                        'email_from' => $m['from_email'],
//                        'email_to' => $single_recipient['user_email'],
//                        'attribute' => 'group_message',
//                        'subject' => $subject,
//                        'text' => '<p class="wp_crm_system_message">' . WP_CRM_F::get_primary_display_value($this_recipient) . ' //entrusted with manually delivering the following message:</p>' . $message . '</p>'
//                    )
//            );
//          }
//        }
      } else {
        
      }

      $this_count++;
    }

    if (count($sent_count) > 0) {
      $response['success'] = 'true';
      $response['message'] = sprintf(_n("Successfully sent %d message.", "Successfully sent %d messages.", count($sent_count)), count($sent_count));
    } else {
      $response['success'] = 'false';
      $response['message'] = __('No messages were sent. Check your SMTP settings.', ud_get_wp_crm_group_messages()->domain);
    }

    die(json_encode($response));
  }

  /**
   * Return an array of display attributes.
   *
   * @since 0.4
   *
   */
  static function get_display_attributes() {
    global $wp_crm;

    $display = array();

    if (empty($wp_crm['group_messages']['pdf_list_attributes'])) {
      $display = array('display_name' => 'display_value');
    }

    foreach ($wp_crm['group_messages']['pdf_list_attributes'] as $slug => $display_type) {
      $display[$slug] = $wp_crm['data_structure']['attributes'][$slug];
      $display[$slug]['display_type'] = $display_type;
    }

    if (is_array($display)) {
      return $display;
    }

    return false;
  }

  /**
   * Return a template of an e-mail report
   *
   * @todo Add function to load custom template if exists
   * @since 0.3
   */
  static function pdf_report_template($args = false) {

    if (!$args) {
      return false;
    }

    //** Get attributes to display in bad user table */
    $display_attributes = class_crm_group_messages::get_display_attributes();

    $message = $args['message'];

    ob_start();
    ?>
    <style type="text/css">
      * {
        margin:0;
        padding:0;
      }

      ul, li {
        margin:0;
        padding:0;
        list-style:none;
      }

    </style>
    <table border="0" width="100%">
      <tr><td colspan="2"><h1><?php echo $message['subject']; ?></h1></td></tr>
      <tr><td colspan="2"><hr /></td></tr>
      <tr><td width="100">From:</td><td><?php echo ($message['from_name'] ? $message['from_name'] . ', ' : ''); ?><?php echo $message['from_email']; ?></td></tr>
      <tr><td>Sent:</td><td><?php echo date('l, F jS, Y'); ?></td></tr>
      <tr><td colspan="2"><br /></td></tr>
      <tr><td colspan="2"><?php echo nl2br($message['message']); ?></td></tr>
    </table>

    <?php if (!empty($display_attributes) && $args['no_email_users']) { ?>

      <h4><?php printf(__('Please notify the following (%1s) people of this message:', ud_get_wp_crm_group_messages()->domain), count($args['no_email_users'])); ?></h4>

      <table border="0" width="100%">

      <?php
      $display_attributes_array = $display_attributes;

      foreach ($args['no_email_users'] as $display_attributes) {

        foreach ($display_attributes as $key => $value) {

          if (!$display_attributes_array[$key]) {
            continue;
          }

          $options_array = array();

          foreach ($value as $k => $val) {
            if ($wp_crm_db['data_structure']['attributes'][$key]['option_labels'][$k]) {
              $options_array[] = $wp_crm_db['data_structure']['attributes'][$key]['option_labels'][$k];
            }
          }

          $get_content = array_pop($value);

          if ($display_attributes_array[$key]['title']) {
            $output = array();

            if ($get_content[0]) {
              $output[] = $display_attributes_array[$key]['title'] . ': ';
            }

            if (is_array($get_content) && isset($get_content[0]) && $get_content[0] !== 'on' && $get_content[0]) {
              $output[] = $get_content[0] . ' ';
            }

            if (count($options_array)) {
              $output[] = implode(', ', $options_array);
            }

            if (count($output)) {
              echo '<tr><td>' . implode('', $output) . '</td></tr>';
            }
          }
        }

        echo '<tr><td> <hr /> </td></tr>';
      }
    }
    ?>

    </table>
      <?php
      $content = ob_get_contents();
      ob_end_clean();

      return $content;
    }

    /**
     * Generate e-mail reports
     *
     * @since 0.3
     */
    static function generate_pdf_email_report($args) {
      global $l;

      $data = $args['data'];
      $message = $args['message'];
      $no_email_users = $args['no_email_users'];

      if (!is_dir(WP_CRM_Cache) && !mkdir(WP_CRM_Cache)) {
        return false;
      }

      $l = Array();
      $l['a_meta_charset'] = 'UTF-8';
      $l['a_meta_dir'] = 'ltr';
      $l['a_meta_language'] = 'en';
      $l['w_page'] = 'page';

      $pdf_content = class_crm_group_messages::pdf_report_template($args);

      if (!class_exists('TCPDF')) {
        require_once WP_CRM_Path . '/third-party/tcpdf/tcpdf.php';
      }

      $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

      $pdf->setPrintHeader(false);
      $pdf->setPrintFooter(false);
      $pdf->SetCreator("WP-CRM");
      $pdf->SetAuthor("WP-CRM");
      $pdf->SetTitle($message['subject']);
      $pdf->SetSubject($message['subject']);
      $pdf->AddPage('P', LETTER);

      $pdf->writeHTML($pdf_content, true, false, true, false, '');
      $pdf_file_path = WP_CRM_Cache . '/' . rand(100, 999) . '.pdf';

      $pdf->Output($pdf_file_path, 'F');

      if (file_exists($pdf_file_path)) {
        $return['pdf_file_path'] = $pdf_file_path;
        return $return;
      }

      return false;
    }

    /**
     * Function that injects itself into the filter on the overiew page.
     *
     * @todo Passing user_ids may work better if not in serialized array to avoid server URL length limitations when sending messages to large groups
     *
     * @since 0.1
     */
    static function wp_crm_user_actions() {
      ?>
    <script type="text/javascript">
      var wp_list_count;

      jQuery(document).ready(function () {

        jQuery(".wp_crm_send_newsletter").click(function () {
          wp_crm_newsletter_show_ui();
        });

        function wp_crm_newsletter_show_ui() {

          //** Clear our ajax messages */
          jQuery(".wp_crm_ajax_update_message").hide();

          var filters = jQuery('#wp-crm-filter').serialize();

          jQuery.ajax({
            url: ajaxurl,
            context: document.body,
            data: {
              action: 'wp_crm_display_newsletter_ui',
              filters: filters
            },
            success: function (result) {
              jQuery('.wp_crm_ajax_result').html(result);
              jQuery('.wp_crm_ajax_result').show("slide", {direction: "down"}, 1000)
            }
          });
        }
      });
    </script>
    <li class="button wp_crm_send_newsletter"><?php _e("Send Group Message", ud_get_wp_crm_group_messages()->domain); ?></li>
    <?php
  }

}
