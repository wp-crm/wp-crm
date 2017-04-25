<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPC {

  use Exception;
  use WP_CRM_Core;

  if( !class_exists( 'UsabilityDynamics\WPC\WPC_Bootstrap' ) ) {
    /**
     * @property WP_CRM_Core core
     * @property API api
     */
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

        // Handle forced pre-release update checks.
        if ( is_admin() && isset( $_GET[ 'force-check' ] ) && $_GET[ 'force-check' ] === '1' ) {
          add_filter( 'site_transient_update_plugins', array( $this, 'update_check_handler' ), 50, 2 );
        }

        // Handle regular pre-release checks.
        add_filter( 'pre_update_site_option__site_transient_update_plugins', array( $this, 'update_check_handler' ), 50, 2 );

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
        $this->core = new WP_CRM_Core();

        if( class_exists( 'UsabilityDynamics\WPC\API' ) ) {
          $this->api = new API();
        }

        
        add_action( 'wpmu_new_blog', array($this, 'maybe_install_tables'), 10, 6 );

      }

      /**
       * Pre release updates handler
       * @param $response
       * @param null $old_value
       * @return mixed
       */
      public function update_check_handler( $response, $old_value = null ) {
        global $wp_crm;

        if ( ! $response || !isset( $response->response ) || ! is_array( $response->response ) || ! isset( $wp_crm ) || ! isset( $wp_crm[ 'configuration' ][ 'pre_release_updates' ] ) ) {
          return $response;
        }

        // If pre-release update checks are disabled, do nothing.
        if ( $wp_crm[ 'configuration' ][ 'pre_release_updates' ] !== 'true' ) {
          return $response;
        }

        // Last check was very recent. (This doesn't seem to be right place for this). That being said, if it's being forced, we ignore last time we tried.
        if ( current_filter() === 'site_transient_update_plugins' && !( isset( $_GET[ 'force-check' ] ) && $_GET[ 'force-check' ] === '1' ) && $response->last_checked && ( time() - $response->last_checked ) < 360 ) {
          return $response;
        }

        // e.g. "wp-crm", the clean directory name that we are runnig from.
        $_plugin_name = plugin_basename( dirname( dirname( __DIR__ ) ) );

        // e.g. "wp-invoice/wp-invoice.php". Directory name may vary but the main plugin file should not.
        $_plugin_local_id = $_plugin_name . '/wp-crm.php';

        // Bail, no composer.json file, something broken badly.
        if ( ! file_exists( WP_PLUGIN_DIR . '/' . $_plugin_name . '/composer.json' ) ) {
          return $response;
        }

        try {

          // Must be able to parse composer.json from plugin file, hopefully to detect the "_build.sha" field.
          $_composer = json_decode( file_get_contents( WP_PLUGIN_DIR . '/' . $_plugin_name . '/composer.json' ) );

          if ( is_object( $_composer ) && isset( $_composer->extra ) && isset( $_composer->extra->_build ) && isset( $_composer->extra->_build->sha ) ) {
            $_version = $_composer->extra->_build->sha;
          }

          // @todo Allow for latest branch to be swapped out for another track.
          $_response = wp_remote_get( 'https://api.usabilitydynamics.com/v1/product/updates/' . $_plugin_name . '/latest/' . ( isset( $_version ) && $_version ? '?version=' . $_version : '' ) );

          if ( wp_remote_retrieve_response_code( $_response ) === 200 ) {
            $_body = wp_remote_retrieve_body( $_response );
            $_body = json_decode( $_body );

            // If there is no "data" field then we have nothing to update.
            if ( isset( $_body->data ) ) {

              if( !isset( $response->response ) ) {
                $response->response = array();
              }

              if( !isset( $response->no_update ) ) {
                $response->no_update = array();
              }

              $response->response[ $_plugin_local_id ] = $_body->data;

              if ( isset( $response->no_update[ $_plugin_local_id ] ) ) {
                unset( $response->no_update[ $_plugin_local_id ] );
              }

            }

          }

        } catch( \Exception $e ) {}

        return $response;
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
       * Example of wpmu_new_blog usage
       * 
       * @param int    $blog_id Blog ID.
       * @param int    $user_id User ID.
       * @param string $domain  Site domain.
       * @param string $path    Site path.
       * @param int    $site_id Site ID. Only relevant on multi-network installs.
       * @param array  $meta    Meta data. Used to set initial site options.
       */
      function maybe_install_tables( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        if ( !class_exists('\WP_CRM_F') ) {
          include_once ud_get_wp_crm()->path( "lib/class_functions.php", 'dir' );
        }
        $blog_details = get_blog_details($blog_id);
        \WP_CRM_F::maybe_install_tables(array($blog_details));
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
        Upgrade::run($this->old_version, $this->args['version']);
        do_action( $this->slug . '::upgrade', $this->old_version, $this->args[ 'version' ], $this );
      }

      /**
       * Set Feature Flag constants by parsing composer.json
       *
       * @author potanin@UD
       *
       * @param array|bool $_parsed
       * @param array $options - Override of which feature flags should be enabled.
       * @return array|mixed|null|object
       * @internal param array $data - Composer.json parsed.
       */
      static public function parse_feature_flags( $_parsed = false, $options = array() )  {

        try {

          if( !isset( $_parsed ) || !is_object( $_parsed ) ) {

            if( !file_exists( path_join( dirname(dirname( __DIR__)), 'composer.json' ) ) ) {
              throw new Exception( "Missing composer.json file." );
            }

            $_raw = file_get_contents( path_join( dirname(dirname( __DIR__)), 'composer.json' ) );

            $_parsed = json_decode( $_raw );

            // @todo Catch poorly formatted JSON.
            if( !is_object( $_parsed ) ) {
              throw new Exception( "Unable to parse composer.json file." );
            }

          }

          // Missing feature flags.
          if( !isset( $_parsed ) || !isset( $_parsed->extra ) || !isset( $_parsed->extra->featureFlags ) ) {
            return null;
          }

          foreach( (array) $_parsed->extra->featureFlags as $_feature ) {

            if( !defined( $_feature->constant ) ) {

              if( is_array( $options ) && isset( $options[ $_feature->option ] ) ) {
                define( $_feature->constant, (bool) $options[ $_feature->constant ] );
              }  else {
                define( $_feature->constant, $_feature->enabled );
              }

            }

          }

        } catch (Exception $e) {
          echo error_log( 'Caught [wp-crm] exception: [' . $e->getMessage() . ']' );
        }

        return isset($_parsed) ? $_parsed : null;

      }


    }

  }

}
