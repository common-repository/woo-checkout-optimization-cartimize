<?php
/**
 * Plugin Name: WooCommerce Checkout Optimization by Cartimize
 * Plugin URI: https://cartimize.com/woocommerce-checkout-optimization/
 * Description: Cut down the WooCommerce checkout flow length by 50% and implement <strong><em>research-backed</em></strong> user experience improvements to the WooCommerce checkout flow that are proven to improve conversion.
 * Version: 1.0.4
 * Author: Cartimize
 * Author URI: https://cartimize.com/
 * Developer: Cartimize
 * Developer URI: https://cartimize.com/
 * Text Domain: woo-checkout-optimization-cartimize
 * Domain Path: /languages
 * WC requires at least: 3.5
 * WC tested up to: 3.8.1
 */

/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

include WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/includes/common_misc.php';

if( cartimize_checkopt_is_woocommerce_active() ){
	if( !cartimize_checkopt_is_cartimize_checkopt_pro_active() ){	
		add_action( 'plugins_loaded', 'cartimize_checkopt_include_and_int' );
	}
}
else{
	add_action( 'plugins_loaded', 'cartimize_checkopt_woocom_not_active_deactivate_self' );
}

function cartimize_checkopt_include_and_int(){
	if( ($GLOBALS['cartimize_checkopt_requirements'] = cartimize_checkopt_check_min_version_requirements(false) ) !== true ){
		cartimize_checkopt_requirements_not_met_deactivate_self();
		return;
	}

	include WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/includes/class_checkopt_init.php';
	Cartimize_Checkopt_Init::init();
}

class Cartimize_Checkopt_Install{

	public static function init(){
		$plugin_slug = basename(dirname(__FILE__));
		register_activation_hook( $plugin_slug .'/'. $plugin_slug.'.php',  __CLASS__ . '::on_plugin_activate');

		//register_deactivation_hook( $plugin_slug .'/'. $plugin_slug.'.php',  __CLASS__ . '::on_plugin_deactivate');
	}

	public static function on_plugin_activate(){
		//going for wordpress option because autoload will be optimized and it will not overide if already exists
		add_option('cartimize_checkopt_first_activation_redirect', true);
	}

	// public static function on_plugin_deactivate(){

	// }
}

Cartimize_Checkopt_Install::init();
