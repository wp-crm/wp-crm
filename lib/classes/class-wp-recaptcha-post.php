<?php
namespace ReCaptcha\RequestMethod;

use ReCaptcha\RequestMethod;
use ReCaptcha\RequestParameters;

/**
 * Sends POST requests to the reCAPTCHA service.
 */
class WpRecaptchaPost implements RequestMethod
{
    /**
     * URL to which requests are POSTed.
     * @const string
     */
    const SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Submit the POST request with the specified parameters.
     *
     * @param RequestParameters $params Request parameters
     * @return string Body of the reCAPTCHA response
     */
    public function submit(RequestParameters $params)
    {
        $response = wp_remote_post( self::SITE_VERIFY_URL, array(
            'body' => $params->toArray(),
            )
        );

        if ( is_wp_error( $response ) ) {
           return false;
        } else {
           return $response['body'];
        }
    }
}
