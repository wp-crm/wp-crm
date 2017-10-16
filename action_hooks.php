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
$wp_crm_db = get_option('wp_crm_settings');

global $wp_crm;

$wp_crm['version'] = '0.1';

$wp_crm['configuration'] = array(
    'user_level' => 'administrator',
    'default_user_capability' => 'prospect',
    'default_user_capability_permissions_base' => 'subscriber',
    'create_individual_pages_for_crm_capabilities' => 'true'
);

$wp_crm['configuration']['mail'] = array(
    'sender_name' => get_bloginfo(),
    'send_email' => get_bloginfo('admin_email')
);

$wp_crm['configuration']['input_types'] = apply_filters('wp_crm_input_types', array(
    'text' => __('Single Line Text', ud_get_wp_crm()->domain),
    'checkbox' => __("Checkbox", ud_get_wp_crm()->domain),
    'radio' => __("Radio", ud_get_wp_crm()->domain),
    'textarea' => __("Textarea", ud_get_wp_crm()->domain),
    'dropdown' => __("Dropdown", ud_get_wp_crm()->domain),
    'password' => __("Password", ud_get_wp_crm()->domain),
    'date' => __("Date Picker", ud_get_wp_crm()->domain),
    'file_upload' => __("File Upload", ud_get_wp_crm()->domain),
    'recaptcha' => __("Google reCAPTCHA", ud_get_wp_crm()->domain)
));

/**
 * Permissions to be utilized through the plugin.
 * These are automatically added to admin in WPP_C::init();
 */
$wp_crm['capabilities'] = array(
    'Manage Settings' => __('View and edit plugin settings.', ud_get_wp_crm()->domain),
    'View Overview' => __('View individual prospects and the overview page.', ud_get_wp_crm()->domain),
    'View Detailed Logs' => __('View detailed user activity logs.', ud_get_wp_crm()->domain),
    'View Profiles' => __('View a user\'s profile.', ud_get_wp_crm()->domain),
    'Add User Messages' => __('Add to correspondence thread on a user\'s profile.', ud_get_wp_crm()->domain),
    'Send Group Message' => __('Send a group message to users.', ud_get_wp_crm()->domain),
    'Perform Advanced Functions' => __('Perform advanced functions such as merging users.', ud_get_wp_crm()->domain),
    'Change Passwords' => __('Change passwords of other users. This is only checked if the user can edit users in the first place.', ud_get_wp_crm()->domain),
    'Change Role' => __('Change role of other users. This is only checked if the user can edit users in the first place.', ud_get_wp_crm()->domain),
    'Change Color Scheme' => __('Change color scheme. This is only checked if the user can edit users in the first place.', ud_get_wp_crm()->domain)
);

/** 
 * Overwrite $wp_crm with database setting 
 */
if ( !empty( $wp_crm_db ) ) {
  if ( !class_exists( 'CRM_UD_F' ) ) {
    require_once( ud_get_wp_crm()->path( 'lib/class_ud.php', 'dir' ) );
  }
  $wp_crm = CRM_UD_F::array_merge_recursive_distinct($wp_crm, $wp_crm_db);
}

