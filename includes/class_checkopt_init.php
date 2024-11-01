<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class Cartimize_Checkopt_Init{

	public static function init(){//dedicated for this class
		
		include WP_PLUGIN_DIR . '/' . basename(dirname(dirname(__FILE__))) . '/constants.php';
		
		get_called_class()::include_files();
		Cartimize_Checkopt_WC_Settings_Tab::init();
		
		get_called_class()::maybe_admin_init();

		get_called_class()::enqueue_styles_and_scripts();
		
		if (get_option('cartimize_checkopt_first_activation_redirect')) {
			add_action('admin_init', __CLASS__ . '::on_activate_redirect');
		}

	}

	protected static function enqueue_styles_and_scripts(){
		add_action('wp_enqueue_scripts', get_called_class() . '::frontend_enqueue_styles', 50);
		add_action('wp_enqueue_scripts', get_called_class() . '::frontend_enqueue_scripts', 10);

		add_action('admin_enqueue_scripts', get_called_class() . '::admin_enqueue_styles', 10);
		add_action('admin_enqueue_scripts', get_called_class() . '::admin_enqueue_scripts', 10);
	}

	protected static function include_files(){
		include CARTIMIZE_CHECKOPT_PATH . '/includes/common_func.php';
		include CARTIMIZE_CHECKOPT_PATH . '/includes/class_checkopt_manage_admin_notice.php';
		include CARTIMIZE_CHECKOPT_PATH . '/includes/class_checkopt_customize_wc_admin_settings.php';
		include CARTIMIZE_CHECKOPT_PATH . '/includes/class_checkopt_wc_settings_tab.php';
		include CARTIMIZE_CHECKOPT_PATH . '/includes/class_checkopt_wc_checkout_customize.php';
	}

	public static function admin_enqueue_styles() {
		wp_register_style('cartimize_checkopt_admin_style', CARTIMIZE_CHECKOPT_PLUGIN_URL . 'assets/css/admin.css', array(), CARTIMIZE_CHECKOPT_VERSION);
		wp_enqueue_style('cartimize_checkopt_admin_style');
	}

	public static function admin_enqueue_scripts() {
		wp_register_script('cartimize_checkopt_admin_common', CARTIMIZE_CHECKOPT_PLUGIN_URL . 'assets/js/common.js', array('jquery'), CARTIMIZE_CHECKOPT_VERSION, true);
		wp_enqueue_script('cartimize_checkopt_admin_common');

		wp_register_script('cartimize_checkopt_admin_script', CARTIMIZE_CHECKOPT_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CARTIMIZE_CHECKOPT_VERSION, true);
		wp_enqueue_script('cartimize_checkopt_admin_script');
		wp_localize_script('cartimize_checkopt_admin_script', 'cartimize_checkopt_ajax', array( 'ajax_url' => admin_url('admin-ajax.php'), 'admin_url' => admin_url()));
	}
	
	public static function frontend_enqueue_styles() {
		wp_register_style('cartimize_checkopt_common_style', CARTIMIZE_CHECKOPT_PLUGIN_URL . 'assets/css/frontend.css', array(), CARTIMIZE_CHECKOPT_VERSION);
		wp_enqueue_style('cartimize_checkopt_common_style');
	}

	public static function frontend_enqueue_scripts() {
		wp_register_script('cartimize_checkopt_frontend_common', CARTIMIZE_CHECKOPT_PLUGIN_URL . 'assets/js/common.js', array('jquery'), CARTIMIZE_CHECKOPT_VERSION, true);
		wp_enqueue_script('cartimize_checkopt_frontend_common');

		wp_register_script('cartimize_checkopt_frontend_script', CARTIMIZE_CHECKOPT_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), CARTIMIZE_CHECKOPT_VERSION, true);
		wp_enqueue_script('cartimize_checkopt_frontend_script');

		if( cartimize_checkopt_is_pro_plugin() ){
			$data_for_js = Cartimize_Checkopt_WC_Checkout_Customize_Pro::get_data_for_frontend_js(array());
		}
		else{
			$data_for_js = Cartimize_Checkopt_WC_Checkout_Customize::get_data_for_frontend_js(array());
		}
		wp_localize_script('cartimize_checkopt_frontend_script', 'cartimize_checkopt_data', $data_for_js);
		wp_localize_script('cartimize_checkopt_frontend_script', 'cartimize_checkopt_ajax', array( 'ajax_url' => admin_url('admin-ajax.php') ));
	}

	public static function on_activate_redirect() {

		if(get_option('cartimize_checkopt_first_activation_redirect')) {
			update_option('cartimize_checkopt_first_activation_redirect', false);//don't change to delete_option, as we are using add_option it will add only if slug not exisits that maintain 1 time use
	
			//in rare case lets redirect to respective dev and prod page
			if(!isset($_GET['activate-multi'])){
				wp_redirect(admin_url( 'admin.php?page=wc-settings&tab=cartimize_checkopt_settings&show=welcome_msg' ));
				exit();
			}
		}
	}

	protected static function maybe_admin_init(){
		if( !defined( 'WP_ADMIN' ) || WP_ADMIN !== true ){
			return;
		}
		add_action('wp_ajax_cartimize_process_ajax_request', get_called_class() . '::admin_process_ajax_request');
	}

	public static function admin_process_ajax_request(){
		
		if(isset($_POST['cartimize_action']) && $_POST['cartimize_action'] === 'dismiss_notice'){
			$response = array();

			$notice_slug = sanitize_text_field(trim($_POST['notice_slug']));
			$result = Cartimize_Checkopt_Manage_Admin_Notice::dismiss_notice($notice_slug);

			$response['status'] = $result ? 'success' : 'error';

		}
		elseif(isset($_POST['cartimize_action']) && $_POST['cartimize_action'] === 'process_notice_form'){
			$response = array();
			$form_data = array();
			$notice_slug = sanitize_text_field(trim($_POST['notice_slug']));
			$form_data['email'] = sanitize_email(trim($_POST['email']));
			try{
				$result = Cartimize_Checkopt_Manage_Admin_Notice::process_notice_form( $notice_slug, $form_data );
				$response['status'] = $result ? 'success' : 'error';
				if( $result ){
					$response['message'] = 'Success! Welcome to the club! :)';
				}
			}
			catch(cartimize_exception $e){
				$error = $e->getError();
				$error_msg = $e->getErrorMsg();
		
				$response = array();
				$response['status'] = 'error';
				$response['error_msg'] = $error_msg;
			}
		}

		if(!empty($response)){
			echo cartimize_prepare_response($response);
			exit();
		}
	}
}