<?php
/*
Plugin Name: WP-CRM - Customer Relationship Management
Plugin URI: http://usabilitydynamics.com/products/wp-crm/
Description: Integrated Customer Relationship Management for WordPress.
Author: Usability Dynamics, Inc.
Version: 0.34.2
Author URI: http://usabilitydynamics.com

Copyright 2011  Usability Dynamics, Inc.    (email : andy.potanin@twincitiestech.com)

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

/** Plugin Version */
define('WP_CRM_Version', '0.34.2');

/** Path for Includes */
define('WP_CRM_Path', untrailingslashit(plugin_dir_path( __FILE__ )));

/** Path for front-end links */
define('WP_CRM_URL', untrailingslashit(plugin_dir_url( __FILE__ )));

/** Path for Includes */
define('WP_CRM_Cache', WP_CONTENT_DIR . '/cache');

/** Path for Includes */
define('WP_CRM_Templates', WP_CRM_Path . '/templates');

/** Path for Includes */
define('WP_CRM_Connections', WP_CRM_Path . '/core/connections');

/** Path for Includes */
define('WP_CRM_Third_Party', WP_CRM_Path . '/third-party');

/** Directory path for include_onces of template files  */
define('WP_CRM_Premium', WP_CRM_Path . '/core/premium');

// Global Usability Dynamics / TwinCitiesTech.com, Inc. Functions - customized for WP-CRM
include_once WP_CRM_Path . '/core/class_ud.php';

/** Loads built-in plugin metadata and allows for third-party modification to hook into the filters. Has to be include_onced here to run after template functions.php */
include_once WP_CRM_Path . '/action_hooks.php';

/** Defaults filters and hooks */
include_once WP_CRM_Path . '/default_api.php';

/** Loads general functions used by WP-crm */
include_once WP_CRM_Path . '/core/class_functions.php';

 /** Loads all the metaboxes for the crm page */
include_once WP_CRM_Path . '/core/ui/crm_metaboxes.php';

/** Loads all the metaboxes for the crm page */
include_once WP_CRM_Path . '/core/class_core.php';

//* Register activation hook -> has to be in the main plugin file */
register_activation_hook(__FILE__,array('WP_CRM_F', 'activation'));

//* Register activation hook -> has to be in the main plugin file */
register_deactivation_hook(__FILE__,array('WP_CRM_F', 'deactivation'));

//* Initiate the plugin */
add_action( 'plugins_loaded', create_function('', 'new WP_CRM_Core;'));

