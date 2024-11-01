<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

$_cartimize__plugin_dir = dirname(__FILE__);
if(file_exists($_cartimize__plugin_dir.'/_dev_config.php')){
	@include_once($_cartimize__plugin_dir.'/_dev_config.php');
}

class Cartimize_Checkopt_Constants{

	public static  function init(){
		self::path();
		self::debug();
		self::general();
		self::versions();
	}

	private static function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private static function versions(){
		self::define( 'CARTIMIZE_CHECKOPT_VERSION', '1.0.4' );
	}

	private static function debug(){

	}

	private static function general(){
		$plugin_slug = basename(dirname(__FILE__));
		self::define( 'CARTIMIZE_CHECKOPT_PLUGIN_SLUG', $plugin_slug );
	}

	private static function path(){

		self::define( 'CARTIMIZE_CHECKOPT_PATH', untrailingslashit(WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__))) );
		self::define( 'CARTIMIZE_CHECKOPT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		self::define( 'CARTIMIZE_SITE_URL', 'https://cartimize.com/' );
		self::define( 'CARTIMIZE_SERVICE_URL', CARTIMIZE_SITE_URL.'applogin/' );
		self::define( 'CARTIMIZE_MY_ACCOUNT_URL', CARTIMIZE_SITE_URL.'my-account/' );
		self::define( 'CARTIMIZE_SITE_LOST_PASS_URL', CARTIMIZE_SITE_URL.'my-account/lost-password/' );
		self::define( 'CARTIMIZE_CHECKOPT_GO_PRO_URL', CARTIMIZE_SITE_URL.'woocommerce-checkout-optimization/#buy-section' );
	}
}

Cartimize_Checkopt_Constants::init();