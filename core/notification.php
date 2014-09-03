<?php
/**
 * WP-CRM Notification Functions
 *
 * Contains all the notification functions.
 *
 * @version 0.1
 * @author peshkov@UD
 * @package WP-CRM
 * @subpackage Functions
 */

class WP_CRM_N {


  /**
   * Replaced notification variables with actual values
   *
   * @param array $notification_data
   * @param array $replace_with
   * @since 0.21
   *
   */
  function replace_notification_values( $notification_data = false, $replace_with = false ) {
    global $wp_crm;

    if(!is_array($replace_with)) {
      return;
    }

    $notification_keys = array_keys($notification_data);

    foreach($replace_with as $key => $value) {
      if(is_array($value)) {
        $value = WP_CRM_F::get_first_value($value);
      }
      foreach($notification_data as $n_key => $n_value) {
        $notification_data[$n_key] = str_replace('[' . $key . ']', $value, $n_value);
      }
    }
    return $notification_data;
  }


  /**
   * Returns notifications for a given trigger action
   *
   *
   * @since 0.1
   *
   */
  static function get_trigger_action_notification($action = false, $force = false) {
    global $wp_crm;
    
    $notifications = array();

    if( !$action ) {
      return false;
    }
    
    foreach($wp_crm['notifications'] as $slug => $notification_data){
      if(is_array($notification_data['fire_on_action']) && in_array($action, $notification_data['fire_on_action']) || $force) {
        $notifications[$slug] = $notification_data;
      }
    }
    
    return $notifications;
  }


  /**
   * Hook for action 'phpmailer_init'
   * See: wp_mail() function.
   *
   * @param object $phpmailer Class PHPMailer.
   * @author peshkov@UD
   * @version 1.0
   */
  function phpmailer_init( $phpmailer ) {
    global $_crm_notification;

    $_crm_notification = wp_parse_args( $_crm_notification, array(
      'reply_to_mail' => '',
      'reply_to_name' => '',
      'from' => '',
      'from_name' => '',
      'bcc' => '',
    ) );

    //** Do nothing if $_crm_notification variable is not set  */
    if( empty( $_crm_notification ) || !is_array( $_crm_notification ) ) {
      return null;
    }

    //** Add Reply-To */
    if( !empty( $_crm_notification[ 'reply_to_mail' ] ) ) {
      $phpmailer->ClearReplyTos();
      $phpmailer->AddReplyTo( $_crm_notification[ 'reply_to_mail' ], $_crm_notification[ 'reply_to_name' ] );
    }

    //** Add From */
    if( !empty( $_crm_notification[ 'from' ] ) ) {
      //** In some cases ( legacy functionality ) 'from' string can be like: 'John <john@mail.com>'. To prevent the errors we parse and fix it. peshkov@UD */
      preg_match( '/<(.*)?>/', $_crm_notification[ 'from' ], $matches );
      if( !empty( $matches ) ) {
        $_crm_notification[ 'from_name' ] = !empty( $_crm_notification[ 'from_name' ] ) ? $_crm_notification[ 'from_name' ] : trim( str_replace( $matches[ 0 ], '', $_crm_notification[ 'from' ] ) );
        $_crm_notification[ 'from' ] = $matches[ 1 ];
      }
      $phpmailer->SetFrom( $_crm_notification[ 'from' ], $_crm_notification[ 'from_name' ], 1 );
    }

    //** Add BCCs */
    if( !empty( $_crm_notification[ 'bcc' ] ) ) {
      $phpmailer->ClearBCCs();
      $bcc = explode( ',', (string)$_crm_notification[ 'bcc' ] );
      foreach( $bcc as $_bcc ) {
        $_bcc = trim( $_bcc );
        if( !empty( $_bcc ) ) {
          $phpmailer->AddBCC( $_bcc );
        }
      }
    }

  }

}
