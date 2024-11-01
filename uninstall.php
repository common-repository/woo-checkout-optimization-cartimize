<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

/**
 * Uninstall  all plugin settings
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

function cartimize_checkopt_uninstall(){
	global $wpdb;
	

	if ( !is_multisite() ) 
	{
		//delete options
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'cartimize\_setting\_checkopt\_%';" );
		delete_option( 'cartimize_checkopt_first_activation_redirect' );
	} 
	else{

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		$original_blog_id = get_current_blog_id();

		foreach ( $blog_ids as $blog_id ){
			switch_to_blog( $blog_id );
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'cartimize\_setting\_checkopt\_%';" );
			delete_option( 'cartimize_checkopt_first_activation_redirect' );
		}

		switch_to_blog( $original_blog_id );
	}
}

cartimize_checkopt_uninstall();