<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class Cartimize_Checkopt_Manage_Admin_Notice{
	private static $all_notice_settings_cache = array();

	public static function get_default_all_notice_settings(){
		return array(
			'email_optin' => array(
				'is_active' => true,
				'status' => 'initiate',//initiate, show, success, dismissed
				'plugin_type' => 'free',
				'user_type' => 'custom',
				'data' => array(
					'user_id' => null,//only be shown to this user
					'optin_email' => null,
					'optin_time' => null,
					'optin_ip' => null
				)
			)
		);
	}

	public static function get_all_notice_settings(){
		if( empty(self::$all_notice_settings_cache) ){
			$default_all_notice_settings = self::get_default_all_notice_settings();
			$db_all_notice_settings = get_option('cartimize_checkopt_admin_notice_settings', $default_all_notice_settings);
			self::$all_notice_settings_cache =  array_merge( $default_all_notice_settings, (array)$db_all_notice_settings );
		}
		return self::$all_notice_settings_cache;
	}

	public static function load_notices(){
		$all_notice_settings = self::get_all_notice_settings();
		foreach( $all_notice_settings as $notice_slug => $notice_settings ) {
			if( !$notice_settings['is_active'] ){
				continue;
			}
			if( isset($notice_settings['plugin_type']) && in_array( $notice_settings['plugin_type'], array('free', 'pro'), true ) && !cartimize_checkopt_is_plugin_type($notice_settings['plugin_type']) ){
				continue;
			}
			
			$notice_class = self::get_notice_class( $notice_slug );
			if( empty($notice_class) || !class_exists($notice_class) ){
				continue;
			}
			if( $notice_settings['status'] === 'initiate' ){
				$notice_class::initiate();
			}
			elseif( $notice_settings['status'] === 'show' ){
				$notice_class::init_show();
			}
			
		}
	}

	public static function dismiss_notice( $notice_slug ){
		$notice_class = self::get_notice_class( $notice_slug );
		if( empty($notice_class) || !class_exists($notice_class) ){
			return false;
		}
		return $notice_class::dismiss();
	}

	public static function process_notice_form( $notice_slug, $form_data ){
		$notice_class = self::get_notice_class( $notice_slug );
		if( empty($notice_class) || !class_exists($notice_class) ){
			return false;
		}
		return $notice_class::process_notice_form($form_data);
	}

	public static function get_notice_settings( $notice_slug ){
		$all_notice_settings = self::get_all_notice_settings();
		return isset( $all_notice_settings[$notice_slug] ) ? $all_notice_settings[$notice_slug] : false;
	}

	public static function update_notice_settings( $notice_slug, $settings ){
		$all_notice_settings = self::get_all_notice_settings();
		if ( !isset( $all_notice_settings[$notice_slug] ) ){
			return false;
		}
		$all_notice_settings[$notice_slug] = array_merge( $all_notice_settings[$notice_slug], $settings );
		$is_updated = update_option('cartimize_checkopt_admin_notice_settings', $all_notice_settings);
		if( $is_updated ){
			self::$all_notice_settings_cache = $all_notice_settings;//update cache
		}
		return $is_updated;
	}

	public static function get_notice_class( $notice_slug ){
		//don't save this in DB - Risk
		$all_notice_class = array(
			'email_optin' => 'Cartimize_Checkopt_Admin_Notice_Email_Optin'
		);
		return isset( $all_notice_class[$notice_slug] ) ? $all_notice_class[$notice_slug] : false;
	}
}

class Cartimize_Checkopt_Admin_Notice{
	protected static $notice_slug;

	public static function initiate(){
		return null;
	}

	public static function init_show(){
		return null;
	}

	public static function dismiss(){
		$update_settings = array();
		$update_settings['is_active'] = false;
		$update_settings['status'] = 'dismissed';
		$update_settings['dismissed_time'] = time();
		return Cartimize_Checkopt_Manage_Admin_Notice::update_notice_settings( static::$notice_slug, $update_settings );
	}

	public static function process_notice_form($form_data){
		return false;
	}
}

class Cartimize_Checkopt_Admin_Notice_Email_Optin extends Cartimize_Checkopt_Admin_Notice{
	protected static $notice_slug = 'email_optin';

	public static function initiate(){
		add_action('woocommerce_update_options_cartimize_checkopt_settings', 'Cartimize_Checkopt_Admin_Notice_Email_Optin::on_cartimize_settings_update_note_user');
	}

	public static function init_show(){
		add_action('admin_notices', 'Cartimize_Checkopt_Admin_Notice_Email_Optin::show');
	}

	public static function process_notice_form($form_data){
		if( !isset($form_data['email']) || !is_email($form_data['email']) ){
			throw new cartimize_exception('invalid_email', 'Invalid email.');
		}

		$url = CARTIMIZE_SERVICE_URL;

		$post_data = array();
		$post_data['action'] = 'free_user_email_optin';
		$post_data['user_email'] = $form_data['email'];
		$post_data['user_ip'] = $_SERVER['REMOTE_ADDR'];

		$http_args = array(
			'method' => "POST",
			'timeout' => 30,
			'body' => $post_data
		);

		try{
			$response = wp_remote_request( $url, $http_args );

			//cartimize_debug::log($response,'-----------$service_response----------------');
			$response_data = cartimize_get_response_from_json($response);
		}
		catch(cartimize_exception $e){
			throw $e;
		}

		if(empty($response_data) || !is_array($response_data) || !isset($response_data['status']) ){
			throw new cartimize_exception('invalid_response'); 
		}

		if( $response_data['status'] === 'success' ){
			$update_settings = array();
			$update_settings['is_active'] = false;
			$update_settings['status'] = 'success';
			Cartimize_Checkopt_Manage_Admin_Notice::update_notice_settings( static::$notice_slug, $update_settings );
			return true;
		}
		elseif( $response_data['status'] === 'error' ){
			if( !empty($response_data['message']) ){
				throw new cartimize_exception('response', $response_data['message']);
			}
		}
		throw new cartimize_exception('response', 'Unknown error.');
	}

	public static function on_cartimize_settings_update_note_user(){
		$current_user = wp_get_current_user();
		if( !$current_user->exists() ){
			return false;
		}
		$update_settings = array();
		$update_settings['status'] = 'show';
		$update_settings['data' ]['user_id'] = $current_user->ID;
		$result = Cartimize_Checkopt_Manage_Admin_Notice::update_notice_settings( self::$notice_slug, $update_settings );
		if($result){
			self::init_show();//to show it immediatly, currently it comes in next page load. Post page not redirecting to get page.
		}
	}

	public static function show(){
		
		$current_user = wp_get_current_user();
		if( !$current_user->exists() ){
			return false;
		}

		$notice_settings = Cartimize_Checkopt_Manage_Admin_Notice::get_notice_settings( self::$notice_slug );
		if( empty($notice_settings['data']['user_id']) || $notice_settings['data']['user_id'] != $current_user->ID ){
			return false;
		}

		$current_user_email = $current_user->user_email;
		?>
		<div class="notice notice-info wp-clearfix cartimize_admin_notice is-dismissible" data-notice-slug="<?php echo self::$notice_slug; ?>" style="display:block !important;"><p style="line-height: 1.3em;float: left;width: 60%;padding: 5px 0px;"><img style="float:left;margin-right: 10px;" src="<?php echo CARTIMIZE_CHECKOPT_PLUGIN_URL; ?>assets/images/email-collect-admin-notice-icon.gif"><strong style="margin: 2px 0 4px;display: inline-block;">WooCommerce Checkout Optimization plugin by Cartimize</strong><br>Be the first to know about Exclusive offers on Pro, New Features and<br>research-backed insights to improve your store, straight from industry experts.</p><div style="float: right;width: 38%;text-align: right;margin: 20px 0;margin-right: 7px;"><input type="email" id="cartimize_email_optin" value="<?php echo $current_user_email; ?>" style="padding: 9px; border-radius: 3px 0 0 3px;"><input type="button" value="Become an Insider" style="background-color: #003e99; color: #fff; border: 0; border-radius: 0 3px 3px 0; padding: 10px 20px;" class="process_notice_form"><div class="result_cont"></div></div>
		</div>
		<?php
	}
}

Cartimize_Checkopt_Manage_Admin_Notice::load_notices();
