<?php
/**
 * Plugin Name: WP-CRM: Group Messages
 * Plugin URI: https://www.usabilitydynamics.com/product/wp-crm-group-messages/
 * Description: Send group messages to your users from within your WordPress control panel. The WP-CRM attribute filter can be used to create group-specific messages. After a message is sent, a log is made for every recipient, allowing you to keep track of all collaboration.
 * Author: Usability Dynamics, Inc.
 * Version: 1.0.2
 * Requires at least: 4.0
 * Tested up to: 4.9.4
 * Text Domain: wp-crm-group-messages
 * Author URI: http://www.usabilitydynamics.com
 * GitHub Plugin URI: wp-crm/wp-crm-group-messages
 * GitHub Branch: v1.0
 *
 * Copyright 2012 - 2018 Usability Dynamics, Inc.  ( email : info@usabilitydynamics.com )
 *
 */

if( !function_exists( 'ud_get_wp_crm_group_messages' ) ) {

  /**
   * Returns  Instance
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_get_wp_crm_group_messages( $key = false, $default = null ) {
    $instance = \UsabilityDynamics\WPC\WPC_GM_Bootstrap::get_instance();
    return $key ? $instance->get( $key, $default ) : $instance;
  }

}

if( !function_exists( 'ud_check_wp_crm_group_messages' ) ) {
  /**
   * Determines if plugin can be initialized.
   *
   * @author Usability Dynamics, Inc.
   * @since 1.0.0
   */
  function ud_check_wp_crm_group_messages() {
    global $_ud_wp_crm_error;
    try {
      //** Be sure composer.json exists */
      $file = dirname( __FILE__ ) . '/composer.json';
      if( !file_exists( $file ) ) {
        throw new Exception( __( 'Distributive is broken. composer.json is missed. Try to remove and upload plugin again.', 'wp-crm-group-messages' ) );
      }
      $data = json_decode( file_get_contents( $file ), true );
      //** Be sure PHP version is correct. */
      if( !empty( $data[ 'require' ][ 'php' ] ) ) {
        preg_match( '/^([><=]*)([0-9\.]*)$/', $data[ 'require' ][ 'php' ], $matches );
        if( !empty( $matches[1] ) && !empty( $matches[2] ) ) {
          if( !version_compare( PHP_VERSION, $matches[2], $matches[1] ) ) {
            throw new Exception( sprintf( __( 'Plugin requires PHP %s or higher. Your current PHP version is %s', 'wp-crm-group-messages' ), $matches[2], PHP_VERSION ) );
          }
        }
      }
      //** Be sure vendor autoloader exists */
      if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
        require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
      } else {
        throw new Exception( sprintf( __( 'Distributive is broken. %s file is missed. Try to remove and upload plugin again.', 'wp-crm-group-messages' ), dirname( __FILE__ ) . '/vendor/autoload.php' ) );
      }
      //** Be sure our Bootstrap class exists */
      if( !class_exists( '\UsabilityDynamics\WPC\WPC_GM_Bootstrap' ) ) {
        throw new Exception( __( 'Distributive is broken. Plugin loader is not available. Try to remove and upload plugin again.', 'wp-crm-group-messages' ) );
      }
    } catch( Exception $e ) {
      $_ud_wp_crm_error = $e->getMessage();
      return false;
    }
    return true;
  }

}

if( ud_check_wp_crm_group_messages() ) {
  //** Initialize. */
  ud_get_wp_crm_group_messages();
}