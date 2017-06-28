<?php
/**
 * API
 *
 */
namespace UsabilityDynamics\WPC {

  use WP_REST_Request;

  if( !class_exists( 'UsabilityDynamics\WPC\API' ) ) {

    /**
     *
     */
    class API {

      function __construct() {

        add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );

      }

      /**
       *
       *
       *
       *
       *
       */
      public function rest_api_init() {

        // https://usabilitydynamics-www-reddoorcompany-com-production-hotfix.c.rabbit.ci/wp-json/form/v1/submit/
        register_rest_route( 'wp-crm/v1/form', '/submit/', array(
          'methods' => array( 'POST', 'GET' ),
          'callback' => array( $this, 'submit_form' ),
        ) );

      }

      /**
       * Submit CRM Form.
       *
       *
       * https://usabilitydynamics-www-reddoorcompany-com-production-hotfix.c.rabbit.ci/wp-json/form/v1/submit/
       *
       *
       * @keys https://www.google.com/recaptcha/admin#site/336785383
       * @docs https://developers.google.com/recaptcha/docs/verify
       *
       * @param WP_REST_Request $request
       * @return array
       */
      public function submit_form( WP_REST_Request $request ) {

        nocache_headers();

        $_verification = json_decode( wp_remote_retrieve_body( wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
          'body' => array(
            'secret' => defined( 'RDC_RECAPTCHA_SECRET' ) ? RDC_RECAPTCHA_SECRET : '6Lfn7xIUAAAAAHtv6LS1QgU01y68PjellGSb4cKD',
            'remoteip' => isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : null,
            'response' => isset( $_POST[ 'g-recaptcha-response' ] ) ? $_POST[ 'g-recaptcha-response' ] : null,
          )
        ) ) ) );

        return array(
          'ok' => true,
          'message' => 'form submitted',
          'verification' => $_verification,
          'body' => $_POST
        );

      }

    }

  }

}