<?php

/*
  Plugin Name: tiger.do Integration Helper
  Plugin URI: https://www.tiger.do/
  Description: Integrate tiger.do on your site
  Author: Tiger Digitech Pvt. Ltd.
  Version: 1.0.7
  Author URI: https://www.tiger.do/
 */
// If this file is called directly, then abort execution.
require_once plugin_dir_path( __FILE__ ) . "tiger-integration-helper.php";
if ( is_admin() )
	$my_settings_page = new tiger_integrationSettingsClass( __FILE__ );

$tiger_integration_options = get_option( 'tiger_integration_options' );
if ( $tiger_integration_options )
	$tiger_integration_options = get_option( 'tiger_integration_options' );

function tiger_integration_footer() {
	global $post, $tiger_integration_options;
	if ( tiger_integration_pages( 'tiger_integration_type' ) ) {
		if ( isset( $tiger_integration_options['tiger_integration_script'] ) ) {
			if ( isset( $tiger_integration_options['priority'] ) ) {
				echo preg_replace( '/async(=[\'"]+[a-z]*[\'"]+)?/i', '', html_entity_decode( $tiger_integration_options['tiger_integration_script'], ENT_QUOTES ) );
			} else
				echo html_entity_decode( $tiger_integration_options['tiger_integration_script'], ENT_QUOTES );
		}
	}

}

if ( isset( $tiger_integration_options['priority'] ) )
	add_action( 'wp_head', 'tiger_integration_footer' );
else
	add_action( 'wp_footer', 'tiger_integration_footer' );

function tiger_integration_pages( $param ) {
	global $tiger_integration_options;
	if ( isset( $tiger_integration_options[$param] ) ) {
		return in_array( get_post_type(), $tiger_integration_options[$param] );
	}
	return true;

}
