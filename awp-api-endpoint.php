<?php
/**
 * Plugin Name: AskWatsonPain API Custom Endpoints
 * Plugin URI: https://github.com/nlk-plugins/awp-api-endpoint
 * Description: Add custom endpoints for AWP App
 * Version: 1.0.0
 * Author: Tim Spinks
 * Author URI: https://github.com/nlk-plugins/awp-api-endpoint
 * License: GPL2
 * Resources:
 *  https://developer.wordpress.org/rest-api/
 *  https://prospress.github.io/subscriptions-rest-api-docs/
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Get subscriptions by user email
 *
 * @param array $data Options for the function.
 * @return obj|null Subscriptions object
 */
function awp_user_subscriptions( $data ) {
	$user_id = get_user_by( 'email', $data['email'] );
 
	if ( empty( $user_id ) ) {
		return null;
	}

	$sub_array = wcs_get_users_subscriptions( $user_id );

	if ( ! is_array( $sub_array ) ) {
		return null;
	}

	$sub_obj = array_values( $sub_array )[0]; // here we are getting the *first* subscription. Instead we should probably look for any Active subscriptions...
 
	return $woocommerce->get( 'subscriptions/' . $sub_obj->id );
}


add_action( 'rest_api_init', function () {
	register_rest_route( 'awp-api/v1', '/user/(?P<email>\d+)', array(
		'methods' => 'GET',
		'callback' => 'awp_user_subscriptions',
	) );
} );


/**
 * Test connection to API without any authentication headers.
 */
add_shortcode( 'test_api_connection', 'my_test_api_connection' );
function my_test_api_connection() {

	$url = get_site_url() . '/wp-json';

	$response = wp_remote_get( $url );

	$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

	return '<pre>' . print_r( $api_response, true ) . '</pre>';
}

