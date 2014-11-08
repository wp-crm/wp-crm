<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPC {

  if( !class_exists( 'UsabilityDynamics\WPC\WPC_Bootstrap' ) ) {

    final class WPC_Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPC\WPC_Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {
        /** Functions - customized for WP-CRM */
        include_once ud_get_wp_crm()->path( "lib/class_ud.php", 'dir' );

        /** Loads built-in plugin metadata and allows for third-party modification to hook into the filters. Has to be include_onced here to run after template functions.php */
        include_once ud_get_wp_crm()->path( "action_hooks.php", 'dir' );

        /** Defaults filters and hooks */
        include_once ud_get_wp_crm()->path( "lib/class_default_api.php", 'dir' );

        /** Loads notification functions used by WP-crm */
        include_once ud_get_wp_crm()->path( "lib/class_notification.php", 'dir' );

        /** Loads general functions used by WP-crm */
        include_once ud_get_wp_crm()->path( "lib/class_functions.php", 'dir' );

        /** Loads all the metaboxes for the crm page */
        include_once ud_get_wp_crm()->path( "lib/ui/crm_metaboxes.php", 'dir' );

        /** Loads all the metaboxes for the crm page */
        include_once ud_get_wp_crm()->path( "lib/class_core.php", 'dir' );

        /** Ajax Handlers */
        include_once ud_get_wp_crm()->path( "lib/class_ajax.php", 'dir' );

        /** Contact messages */
        include_once ud_get_wp_crm()->path( "lib/class_contact_messages.php", 'dir' );

        //** Initiate the plugin */
        $this->core = new \WP_CRM_Core();
      }
      
      /**
       * Return localization's list.
       *
       * @author peshkov@UD
       * @return array
       */
      public function get_localization() {
        return apply_filters( 'wpc::get_localization', array(
          'licenses_menu_title' => __( 'Add-ons', $this->domain ),
          'licenses_page_title' => __( 'WP-CRM Add-ons Manager', $this->domain ),
        ) );
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {
        if ( !class_exists('\WP_CRM_F') ) {
          include_once ud_get_wp_crm()->path( "lib/class_functions.php", 'dir' );
        }
        \WP_CRM_F::maybe_install_tables();
        \WP_CRM_F::manual_activation('auto_redirect=false&update_caps=true');
      }
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}

    }

  }

}
