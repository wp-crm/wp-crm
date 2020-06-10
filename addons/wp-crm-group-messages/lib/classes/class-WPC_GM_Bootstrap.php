<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPC {

  if( !class_exists( 'UsabilityDynamics\WPC\WPC_GM_Bootstrap' ) ) {

    final class WPC_GM_Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type UsabilityDynamics\WPC\WPC_GM_Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {
        require_once( ud_get_wp_crm_group_messages()->path( 'lib/class-crm-group-messages.php', 'dir' ) );
        
        add_action( 'init', function(){
          \class_crm_group_messages::init();
        }, 0 );
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {}
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}

    }

  }

}
