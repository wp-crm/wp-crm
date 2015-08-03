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

      /**
       * Run Install Process.
       *
       * @author peshkov@UD
       */
      public function run_install_process() {
        /* Compatibility with WP-CRM 0.36.5 and less versions */
        $old_version = get_option( 'wp_crm_version' );
        if( $old_version ) {
          $this->run_upgrade_process();
        }
      }

      /**
       * Run Upgrade Process:
       * - do WP-Invoice settings backup.
       *
       * @author peshkov@UD
       */
      public function run_upgrade_process() {
        /* Do automatic Settings backup! */
        $settings = get_option( 'wp_crm_settings' );

        if( !empty( $settings ) ) {

          /**
           * Fixes allowed mime types for adding download files on Edit Product page.
           *
           * @see https://wordpress.org/support/topic/2310-download-file_type-missing-in-variations-filters-exe?replies=5
           * @author peshkov@UD
           */
          add_filter( 'upload_mimes', function( $t ){
            if( !isset( $t['json'] ) ) {
              $t['json'] = 'application/json';
            }
            return $t;
          }, 99 );

          $filename = md5( 'wp_crm_settings_backup' ) . '.json';
          $upload = @wp_upload_bits( $filename, null, json_encode( $settings ) );

          if( !empty( $upload ) && empty( $upload[ 'error' ] ) ) {
            if( isset( $upload[ 'error' ] ) ) unset( $upload[ 'error' ] );
            $upload[ 'version' ] = $this->old_version;
            $upload[ 'time' ] = time();
            update_option( 'wp_crm_settings_backup', $upload );
          }

        }

        do_action( $this->slug . '::upgrade', $this->old_version, $this->args[ 'version' ], $this );
      }

    }

  }

}
