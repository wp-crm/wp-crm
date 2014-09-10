<?php
/*
Plugin Name: WP-CRM
Plugin URI: http://usabilitydynamics.com/products/wp-crm/
Description: Integrated Customer Relationship Management for WordPress.
Author: UsabilityDynamics, Inc.
Version: 1.0.0
Author URI: http://usabilitydynamics.com

Copyright 2011 - 2014  Usability Dynamics, Inc.    (email : andy.potanin@twincitiestech.com)

Created by Usability Dynamics, Inc (website: twincitiestech.com       email : support@twincitiestech.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 3 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if( !function_exists( 'ud_get_wp_crm' ) ) {

  if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once ( __DIR__ . '/vendor/autoload.php' );
  }

  /** Plugin Version */
  if ( !defined( 'WP_CRM_Version' ) ) {
    define('WP_CRM_Version', '1.0.0');
  }

  /** Path for Includes */
  if ( !defined( 'WP_CRM_Path' ) ) {
    define( 'WP_CRM_Path', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
  }

  /** Path for front-end links */
  if ( !defined( 'WP_CRM_URL' ) ) {
    define( 'WP_CRM_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
  }

  /** Path for Includes */
  if ( !defined( 'WP_CRM_Cache' ) ) {
    define( 'WP_CRM_Cache', WP_CONTENT_DIR . '/cache' );
  }

  /** Path for Includes */
  if ( !defined( 'WP_CRM_Templates' ) ) {
    define( 'WP_CRM_Templates', WP_CRM_Path . '/static/templates' );
  }

  /** Path for Includes */
  if ( !defined( 'WP_CRM_Connections' ) ) {
    define( 'WP_CRM_Connections', WP_CRM_Path . '/lib/connections' );
  }

  /** Path for Includes */
  if ( !defined( 'WP_CRM_Third_Party' ) ) {
    define( 'WP_CRM_Third_Party', WP_CRM_Path . '/third-party' );
  }
  
  /**
   * Returns WP_Invoice object
   *
   * @author korotkov@UD
   * @since 1.0.0
   */
  function ud_get_wp_crm( $key = false, $default = null ) {
    if( class_exists( '\UsabilityDynamics\WPC\Bootstrap' ) ) {
      $instance = \UsabilityDynamics\WPC\Bootstrap::get_instance();
      return $key ? $instance->get( $key, $default ) : $instance;
    }
    return false;
  }

}

//** Initialize. */
ud_get_wp_crm();

