<?php
/**
 * WP-CRM Upgrade Handler
 *
 * @since 1.1.0
 * @author alim
 */
namespace UsabilityDynamics\WPC {

  if( !class_exists( 'UsabilityDynamics\WPC\Upgrade' ) ) {

    class Upgrade {

      /**
       * Run Upgrade Process
       *
       * @param $old_version
       * @param $new_version
       */
      static public function run( $old_version, $new_version ) {
        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        self::do_backup( $old_version, $new_version );
        update_option( "wp_crm_version", $new_version );

        switch( true ) {
          case ( version_compare( $old_version, '1.1.0', '<' ) ):
            if( is_multisite()) {
              $sites = array();
              $blogusers = array();
              $current_blog_id = get_current_blog_id();

              // The base crm log tables to work with.
              $table_crm_log = $wpdb->base_prefix . 'crm_log';
              $table_crm_log_meta = $table_crm_log . '_meta';

              // Getting sites.
              if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {
                $sites = get_sites();
              }
              else if ( function_exists( 'wp_get_sites' ) ) {
                $sites = wp_get_sites();
              }

              // Get user ids from sites.
              foreach ( $sites as $site ) {
                $site = (object) $site;
                $users = get_users(array('blog_id' => $site->blog_id));
                foreach ( $users as $user ) {
                  $blogusers[$site->blog_id][] = $user->ID;
                }
              }

              foreach ($blogusers as $blog_id => $_users) {
                // Switching blog
                switch_to_blog( $blog_id );
                // Filtering users
                $users = array_unique($_users);
                
                // If this is the main site or the $users array is empty then continue.
                if($table_crm_log == $wpdb->crm_log || empty($users)) continue;

                $users = implode(', ', $users);
                // Getting log ids
                $log_ids = $wpdb->get_col("SELECT id from $table_crm_log WHERE user_id IN ( $users )");
                $log_ids = implode(', ', $log_ids);

                // Copying logs to new table
                $sql = "INSERT INTO {$wpdb->crm_log} WHERE id IN ( $log_ids );\n";
                $wpdb->get_results($sql);

                // Copying logs meta to new table
                $sql = "INSERT INTO {$wpdb->table_crm_log_meta} WHERE message_id IN ( $log_ids );\n";
                $wpdb->get_results($sql);

                if(!count(array_intersect($_users, $blogusers[1]))){
                  // Deleting logs from old table
                  $sql = "DELETE FROM $table_crm_log WHERE user_id IN ( $log_ids );\n";
                  $wpdb->get_results($sql);
                  
                  // Deleting logs meta from old table
                  $sql = "DELETE FROM $table_crm_log_meta WHERE message_id IN ( $log_ids );\n";
                  $wpdb->get_results($sql);
                }
                restore_current_blog();
                
              }
            }
            // End case
            
        }

      }

      /**
       * Saves backup of WPP settings to uploads and to DB.
       *
       * @param $old_version
       * @param $new_version
       */
      static public function do_backup( $old_version, $new_version ) {
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
            $upload[ 'version' ] = $old_version;
            $upload[ 'time' ] = time();
            update_option( 'wp_crm_settings_backup', $upload );
          }

        }
      }

    }

  }

}
