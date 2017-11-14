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

function awp_users() {
	return get_users();
}

function has_active_subscription( $user_id ) {
	if ( empty( $user_id ) ) {
		return false;
	}
	if ( WC_Subscriptions_Manager::user_has_subscription( $user_id, '', 'active') ) {
		return true;
	}
	return false;
}

/**
 * Get subscriptions by user email
 *
 * @param array $data Options for the function.
 * @return obj|null Subscriptions object
 */
function awp_user_subscriptions( WP_REST_Request $data ) {
	$woocommerce = wc();

	$user = get_user_by( 'email', $data['email'] );

	if ( empty( $user ) ) {
		return null;
	}

	$user_id = (int) $user->data->ID;

	return has_active_subscription( $user_id );
}


add_action( 'rest_api_init', function () {
	register_rest_route( 'awp/v1', '/user/', array(
		'methods' => 'GET',
		'callback' => 'awp_users',
	) );
	register_rest_route( 'awp/v1', '/user/(?P<email>.+)', array(
		'methods' => 'GET',
		'callback' => 'awp_user_subscriptions',
		// 'args' => array(
		// 	'email' => array(
		// 		// 'required' => true,
		// 		'sanitize_callback' => function( $param, $request, $key ) {
		// 			return filter_var($email, FILTER_SANITIZE_EMAIL);
		// 		},
		// 		'validate_callback' => function( $param, $request, $key ) {
		// 			return filter_var($email, FILTER_VALIDATE_EMAIL);
		// 		}
		// 	)
		// )
	) );
} );


/**
 * Test connection to API without any authentication headers.
 */
add_shortcode( 'test_api_connection', 'my_test_api_connection' );
function my_test_api_connection() {

	$url = get_site_url() . '/wp-json/awp/v1';

	$response = wp_remote_get( $url );

	$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

	return '<pre>' . print_r( $api_response, true ) . '</pre>';
}

