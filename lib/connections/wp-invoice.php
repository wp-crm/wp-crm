<?php

/**
 * Name: WP-Invoice connector
 * Description: Adds Extra functionality to WP-CRM when the WP-Invoice plugin is active.
 * Author: Usability Dynamics, Inc.
 * Version: 1.0
 *
 */
//** Load user invoices into global user */
add_action('wp_crm_user_loaded', array('WPI_WPC', 'wp_crm_user_loaded'));
add_action('init', array('WPI_WPC', 'init'));

class WPI_WPC {

  /**
   * Init
   */
  static function init() {
    add_filter('wp_crm_overview_columns', array('WPI_WPC', 'wp_crm_overview_columns'));
    add_filter('wp_crm_overview_cell', array('WPI_WPC', 'wp_crm_overview_cell'), 10, 2);
  }

  /**
   * 
   * @global type $wp_crm_user
   * @param type $wp_crm_user
   */
  static function wp_crm_user_loaded($wp_crm_user) {
    global $wp_crm_user;

    $user_email = $wp_crm_user['user_email']['default'][0];
    $user_id = $wp_crm_user['ID']['default'][0];

    $wpi_query['recipient'] = $user_id;

    if ($user_invoices = WPI_Functions::query($wpi_query)) {
      $wp_crm_user['has_invoices'] = true;
      $wp_crm_user['user_invoices'] = $user_invoices;
    }

    add_action('wp_crm_metaboxes', array('WPI_WPC', 'wp_crm_metaboxes'));
  }

  /**
   * 
   * @global type $wp_crm_user
   */
  static function wp_crm_metaboxes() {
    global $wp_crm_user;

    if ( !class_exists( 'WPI_UI' ) ) return;

    global $wpi_settings;

    if ( !empty( $wp_crm_user['has_invoices'] ) && current_user_can(WPI_UI::get_capability_by_level($wpi_settings['user_level'])) ) {
      add_meta_box("Invoices", "Invoices", array('WPI_WPC', 'metabox'), 'crm_page_wp_crm_add_new', 'normal', 'default');
    }
  }

  /**
   * 
   * @global type $wpi_settings
   * @param type $user_object
   */
  static function metabox($user_object) {
    global $wpi_settings;

    foreach ($user_object['user_invoices'] as $single_invoice) {
      $single_invoice = get_invoice($single_invoice->ID);
      $print[$single_invoice['post_status']][] = '<li><a href="' . admin_url("admin.php?page=wpi_page_manage_invoice&wpi[existing_invoice][invoice_id]={$single_invoice['ID']}") . '">' . $single_invoice['post_title'] . '</a></li>';
    }

    $status_names = apply_filters('wpi_invoice_statuses', $wpi_settings['invoice_statuses']);

    foreach ($print as $invoice_status => $invoice_list) {
      $status_label = ( $status_names[$invoice_status] ? $status_names[$invoice_status] : $invoice_status);

      if (is_array($invoice_list)) {
        echo '<b>' . $status_label . '</b><ul>' . implode('', $invoice_list) . '</ul>';
      }
    }
  }

  /**
   * 
   * @global type $wpdb
   * @param type $current
   * @param type $data
   * @return type
   */
  static function wp_crm_overview_cell($current, $data) {

    if ($data['column_name'] != 'wpi_sales') {
      return $current;
    }

    $user_worth = WPI_WPC::get_user_worth($data['user_id']);

    if (!empty($user_worth)) {
      return $user_worth;
    }
  }

  /**
   * 
   * @param type $current
   * @return string
   */
  static function wp_crm_overview_columns($current) {
    $current['wpi_sales']['title'] = __('Sales', ud_get_wp_crm()->domain);
    $current['wpi_sales']['overview_column'] = 'true';
    return $current;
  }

  /**
   * 
   * @global type $wpdb
   * @param type $user_id
   * @param type $args
   * @return boolean
   */
  static function get_user_worth($user_id, $args = "") {
    global $wpdb;

    $defaults = array(
        'format_number' => 'true'
    );

    $args = wp_parse_args($args, $defaults);
    extract($args, EXTR_SKIP);

    $user_email = $wpdb->get_var("SELECT user_email FROM {$wpdb->users} WHERE ID = {$user_id}");

    if ($have_sales = $wpdb->get_var("
      SELECT SUM(value)
      FROM {$wpdb->prefix}wpi_object_log as log
      LEFT JOIN {$wpdb->postmeta} as invoice_meta
      ON log.object_ID = invoice_meta.post_id
      WHERE action = 'add_payment'
      AND meta_value = '{$user_email}'
      AND meta_key = 'user_email'
      "
            )) {

      if ($have_refunds = $wpdb->get_var("
      SELECT SUM(value)
      FROM {$wpdb->prefix}wpi_object_log as log
      LEFT JOIN {$wpdb->postmeta} as invoice_meta
      ON log.object_ID = invoice_meta.post_id
      WHERE action = 'refund'
      AND meta_value = '{$user_email}'
      AND meta_key = 'user_email'
      "
              )) {
        $have_sales = $have_sales - $have_refunds;
      }

      if (class_exists('WPI_Functions')) {
        if ($args['format_number'] == 'true') {
          return WPI_Functions::currency_format($have_sales);
        } else {
          return $have_sales;
        }
      } else {
        return $have_sales;
      }
    }

    return false;
  }

}