<?php
/**
 * WP-CRM Actions and Hooks File
 *
 * Do not modify arrays found in these files, use the filters to modify them in your functions.php file
 * Sets up default settings and loads a few actions.
 *
 * Copyright 2011 Usability Dynamics, Inc. <info@usabilitydynamics.com>
 *
 * @link http://twincitiestech.com/plugins/
 * @version 0.1
 * @author Usability Dynamics, Inc. <info@usabilitydynamics.com>
 * @package WP-CRM
*/


  // Load settings out of database to overwrite defaults from action_hooks.
  $wp_crm_db = get_option('wp_crm_settings');

  /*
    Default configuration
  */
  $wp_crm['version'] = '0.1';

  $wp_crm['configuration'] = array(
    'default_user_capability' => 'prospect',
    'default_user_capability_permissions_base' => 'subscriber',
    'create_individual_pages_for_crm_capabilities' => 'true'
    );

  $wp_crm['configuration']['mail'] = array(
    'sender_name' => get_bloginfo(),
    'send_email' => get_bloginfo('admin_email')
  );
   $wp_crm['configuration']['input_types'] = array(
    'text' => __('Single Line Text', 'wp_crm'),
    'checkbox' => __("Checkbox", 'wp_crm'),
    'textarea' => __("Textarea", 'wp_crm'),
    'dropdown' => __("Dropdown", 'wp_crm'),
    'password' => __("Password", 'wp_crm'),
    'date' => __("Date Picker", 'wp_crm')
  );
 
  /*
    Permissions to be utilized through the plugin. 
    These are automatically added to admin in WPP_C::init();
  */
  $wp_crm['capabilities'] = array(
    'Manage Settings' => __('View and edit plugin settings.', 'wp_crm'),
    'View Overview' => __('View individual prospects and the overview page.', 'wp_crm'),
    'View Detailed Logs' => __('View detailed user activity logs.', 'wp_crm'),
    'View Profiles' => __('View a user\'s profile.', 'wp_crm'),
    'Add User Messages' => __('Add to correspondence thread on a user\'s profile.', 'wp_crm'),
    'Send Group Message' => __('Send a group message to users.', 'wp_crm'),
    'Perform Advanced Functions' => __('Perform advanced functions such as merging users.', 'wp_crm'),
    'Change Passwords' => __('Change passwords of other users. This is only checked if the user can edit users in the first place.', 'wp_crm'),
    'Change Color Scheme' => __('Change color scheme. This is only checked if the user can edit users in the first place.', 'wp_crm')
  );


  //** Overwrite $wp_crm with database setting */
  if(!empty($wp_crm_db)) {
    $wp_crm = CRM_UD_F::array_merge_recursive_distinct($wp_crm, $wp_crm_db);
  }

