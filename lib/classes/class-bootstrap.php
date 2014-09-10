<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPC {

  if( !class_exists( 'UsabilityDynamics\WPC\Bootstrap' ) ) {

    final class Bootstrap extends \UsabilityDynamics\WP\Bootstrap {
    
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type \UsabilityDynamics\WPC\Bootstrap object
       */
      protected static $instance = null;
    
      /**
       * Core object
       *
       * @private
       * @static
       * @property $settings
       * @type WPI_Core object
       */
      private $core = null;
      
      /**
       * Instantaite class.
       *
       * @todo: get rid of includes, - move to autoload. peshkov@UD
       */
      public function init() {
        
        //** Be sure we do not have errors. Do not initialize plugin if we have them. */
        if( !$this->has_errors() ) {
        
          /** Functions - customized for WP-CRM */
          include_once WP_CRM_Path . '/lib/class_ud.php';

          /** Loads built-in plugin metadata and allows for third-party modification to hook into the filters. Has to be include_onced here to run after template functions.php */
          include_once WP_CRM_Path . '/action_hooks.php';

          /** Defaults filters and hooks */
          include_once WP_CRM_Path . '/lib/class_default_api.php';

          /** Loads notification functions used by WP-crm */
          include_once WP_CRM_Path . '/lib/class_notification.php';

          /** Loads general functions used by WP-crm */
          include_once WP_CRM_Path . '/lib/class_functions.php';

          /** Loads all the metaboxes for the crm page */
          include_once WP_CRM_Path . '/lib/ui/crm_metaboxes.php';

          /** Loads all the metaboxes for the crm page */
          include_once WP_CRM_Path . '/lib/class_core.php';

          /** Ajax Handlers */
          include_once WP_CRM_Path . '/lib/class_ajax.php';
          
          //** Initiate the plugin */
          $this->core = new \WP_CRM_Core();
        
        }
        
      }
      
      /**
       * Define property $schemas here since we can not set correct paths directly in property
       *
       */
      public function define_schemas() {
        $path = WP_CRM_Path . '/static/schemas/';
        $this->schemas = array(
          //** Autoload Classes versions dependencies for Composer Modules */
          'dependencies' => $path . 'schema.dependencies.json',
          //** Plugins Requirements */
          'plugins' => $path . 'schema.plugins.json',
          //** Licenses */
          'licenses' => $path . 'schema.licenses.json',
        );
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {
        WP_CRM_F::maybe_install_tables();
        WP_CRM_F::manual_activation('auto_redirect=false&update_caps=true');
      }
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}

    }

  }

}
