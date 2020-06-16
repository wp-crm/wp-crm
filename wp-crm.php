<?php
/**
 * Plugin Name: WP-CRM
 * Plugin URI: https://www.usabilitydynamics.com/product/wp-crm/
 * Description: This plugin is intended to significantly improve user management, easily create contact forms, and keep track of incoming shortcode form messages.
 * Author: Usability Dynamics, Inc.
 * Version: 1.2.0
 * Requires at least: 4.0
 * Tested up to: 5.4
 * Text Domain: wp-crm
 * Author URI: https://www.usabilitydynamics.com
 * GitHub Plugin URI: wp-crm/wp-crm
 * GitHub Branch: v1.0
 * Support: https://wordpress.org/support/plugin/wp-crm
 * UserVoice: http://feedback.usabilitydynamics.com/forums/95257-wp-crm
 *
 * Copyright 2012 - 2020 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 */

/** Plugin Version */
if ( !defined( 'WP_CRM_Version' ) ) {
  define('WP_CRM_Version', '1.2.0');
}

/** Path for Includes */
if ( !defined( 'WP_CRM_Cache' ) ) {
  define( 'WP_CRM_Cache', WP_CONTENT_DIR . '/cache' );
}
/** Path for Includes */
if ( !defined( 'wp_crm_Path' ) ) {
  define( 'wp_crm_Path', plugin_dir_path( __FILE__ ) );
}

/*
 * Setting crm_log and crm_log_meta table name with prefix.
 * This will avoid redundency leter.
 *
*/
add_action( 'switch_blog', 'wp_crm_wpdb_table_name' );
wp_crm_wpdb_table_name();
function wp_crm_wpdb_table_name($blog = null, $prev_blog_id = null) {
  global $wpdb;
  $wpdb->crm_log = $wpdb->prefix . 'crm_log';
  $wpdb->crm_log_meta = $wpdb->crm_log . '_meta';
  $wpdb->tables[] = $wpdb->crm_log;
  $wpdb->tables[] = $wpdb->crm_log_meta;
}

if( !function_exists( 'ud_get_wp_crm' ) ) {

  /**
   * Returns  Instance
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   * @param bool $key
   * @param null $default
   * @return
   */
  function ud_get_wp_crm( $key = false, $default = null ) {

    // Create wp-crm instance.
    $instance = \UsabilityDynamics\WPC\WPC_Bootstrap::get_instance();

    return $key ? $instance->get( $key, $default ) : $instance;
  }

}

if( !function_exists( 'ud_check_wp_crm' ) ) {

  /**
   * Determines if plugin can be initialized.
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_check_wp_crm() {
    global $_ud_wp_crm_error;

    try {
      //** Be sure composer.json exists */
      $file = dirname( __FILE__ ) . '/composer.json';
      if( !file_exists( $file ) ) {
        throw new Exception( __( 'Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wp-crm' ) );
      }
      $data = json_decode( file_get_contents( $file ), true );
      //** Be sure PHP version is correct. */
      if( !empty( $data[ 'require' ][ 'php' ] ) ) {
        preg_match( '/^([><=]*)([0-9\.]*)$/', $data[ 'require' ][ 'php' ], $matches );
        if( !empty( $matches[1] ) && !empty( $matches[2] ) ) {
          if( !version_compare( PHP_VERSION, $matches[2], $matches[1] ) ) {
            throw new Exception( sprintf( __( 'Plugin requires PHP %s or higher. Your current PHP version is %s', 'wp-crm' ), $matches[2], PHP_VERSION ) );
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
      } else {
        throw new Exception( sprintf( __( 'Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wp-crm' ), dirname( __FILE__ ) . '/vendor/autoload.php' ) );
      }
      //** Be sure our Bootstrap class exists */
      if( !class_exists( '\UsabilityDynamics\WPC\WPC_Bootstrap' ) ) {
        throw new Exception( __( 'Distributive is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wp-crm' ) );
      }

      // Invoke feature flags.
      UsabilityDynamics\WPC\WPC_Bootstrap::parse_feature_flags( $data, get_option( 'wp_crm_flags', array() ) );

    } catch( Exception $e ) {
      $_ud_wp_crm_error = $e->getMessage();
      return false;
    }

    return true;
  }

}

if( !function_exists( 'ud_wp_crm_message' ) ) {
  /**
   * Renders admin notes in case there are errors on plugin init
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_wp_crm_message() {
    global $_ud_wp_crm_error;
    if( !empty( $_ud_wp_crm_error ) ) {
      $message = sprintf( __( '<p><b>%s</b> can not be initialized. %s</p>', 'wp-crm' ), 'WP-CRM', $_ud_wp_crm_error );
      echo '<div class="error fade" style="padding:11px;">' . $message . '</div>';
    }
  }
  add_action( 'admin_notices', 'ud_wp_crm_message' );
}

if( ud_check_wp_crm() ) {


  // @todo Move into a more approprivate place.
  if( defined( 'WP_CRM_DISABLE_DASHBOARD_SPLASH' ) && WP_CRM_DISABLE_DASHBOARD_SPLASH ) {
    add_filter( 'transient_ud_splash_dashboard', '__return_false' );
    add_filter( 'transient_ud_need_splash', '__return_false' );
    add_filter( 'pre_option_dismiss_wp-crm_1_1_0_notice', '__return_true' );
  }

  //** Initialize. */
  ud_get_wp_crm();
  if( class_exists( '\UsabilityDynamics\WPA\Addons' ) ) {
    new \UsabilityDynamics\WPA\Addons( ud_get_wp_crm()->get_instance() );
  }
}
