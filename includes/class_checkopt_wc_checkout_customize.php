<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class Cartimize_Checkopt_WC_Checkout_Customize {

	protected static $is_cart_page_before_coupon_section = false;
	private static $checkout_fields = array();
	//private static $is_checkout_process_name_customize_action_open = false;

	public static function init() {

		add_filter( 'woocommerce_checkout_fields', get_called_class() . '::customize_checkout_fields', 50 );
		add_filter( 'woocommerce_form_field', get_called_class() . '::customize_checkout_fields_html', 50, 4);
		add_filter( 'woocommerce_form_field_args', get_called_class() . '::customize_checkout_field_args', 50, 4);
		add_filter( 'woocommerce_checkout_get_value', get_called_class() . '::customize_checkout_get_value_for_full_name', 10, 2);
		//add_action( 'woocommerce_before_data_object_save', get_called_class() . '::customize_spliting_and_saving_full_name_in_session', 10, 2 );
		//add_action( 'woocommerce_checkout_process', function(){ get_called_class()::$is_checkout_process_name_customize_action_open = true; } );
		//add_action( 'woocommerce_checkout_update_customer', get_called_class() . '::customize_checkout_update_customer_full_name_save_as', 10, 2);
		//add_action( 'woocommerce_checkout_create_order', get_called_class() . '::customize_checkout_before_order_save_full_name_save_as', 10, 2);


		add_filter( 'woocommerce_checkout_posted_data', get_called_class() . '::overide_wc_checkout_posted_data', 10, 2 );
		
		add_filter( 'woocommerce_coupons_enabled', get_called_class() . '::overide_wc_coupons_enabled', 10, 2 );
		add_action('woocommerce_cart_contents', get_called_class() . '::set_cart_page_before_coupon_section');
		add_action('woocommerce_cart_actions', get_called_class() . '::set_cart_page_after_coupon_section');
		add_action('woocommerce_before_checkout_form', get_called_class() . '::move_coupon_form_from_before_to_after_checkout_form', -9999);
		
		add_filter( 'wc_get_template', get_called_class() . '::overide_wc_template', 10, 2 );
		add_filter( 'body_class',  get_called_class() . '::get_classes_for_body', 10 );

		add_action('woocommerce_admin_field_cartimize_row_text', get_called_class() . '::wc_admin_setting_type_cartimize_row_text', 10);
	}

	public static function customize_checkout_fields($fields){
		$fields = get_called_class()::customize_name_fields($fields);
		$fields = get_called_class()::customize_add_desc_for_billing_phone($fields);
		$fields = get_called_class()::bring_shipping_fieldset_before_billing($fields);

		return $fields;
	}

	public static function customize_checkout_fields_html($field, $key, $args, $value){
		$field = get_called_class()::company_name_hide_add_txt_before($field, $key, $args, $value);
		$field = get_called_class()::address_2_hide_add_txt_before($field, $key, $args, $value);
		$field = get_called_class()::order_comments_hide_add_txt_before($field, $key, $args, $value);

		return $field;
	}

	public static function customize_checkout_field_args($args, $key, $value){
		$args = get_called_class()::customize_hide_company_name_behind_link($args, $key, $value);
		$args = get_called_class()::customize_hide_address_2_behind_link($args, $key, $value);
		$args = get_called_class()::customize_hide_order_comments_behind_link($args, $key, $value);

		return $args;
	}

	public static function customize_checkout_get_value_for_full_name($value, $input){
		if( !is_checkout() ){
			return $value;
		}
		if( $input !== 'billing_first_name' && $input !== 'shipping_first_name' ){
			return $value;
		}
		if( get_option('cartimize_setting_checkopt_single_full_name') !== 'yes' ){
			return $value;
		}

		$last_name_input = 'shipping_last_name';

		if( $input === 'billing_first_name' ){
			$last_name_input = 'billing_last_name';
		}

		$full_name = get_called_class()::checkout_get_value($input).' '.get_called_class()::checkout_get_value($last_name_input);
		return trim($full_name);
	}

	private static function checkout_get_value($input){
		//following code taken from class-wc-checkout.php method get_value()

		if ( is_callable( array( WC()->customer, "get_$input" ) ) ) {
			$value = WC()->customer->{"get_$input"}();
		} elseif ( WC()->customer->meta_exists( $input ) ) {
			$value = WC()->customer->get_meta( $input, true );
		}

		$value = $value ? $value : null; // Empty value should return null.
		//ends here

		return $value;
	}

	// public static function customize_spliting_and_saving_full_name_in_session($object, $object_data_store){
	// 	//to split and save first name and last name in session
	// 	if( 
	// 		!get_called_class()::$is_checkout_process_name_customize_action_open ||
	// 		!($object instanceof WC_Customer) ||
	// 		$object_data_store->get_current_class_name() !== 'WC_Customer_Data_Store_Session' || 
	// 		get_option('cartimize_setting_checkopt_single_full_name') !== 'yes'
	// 	){
	// 		return;
	// 	}

	// 	if( 
	// 		is_callable( array( $object, 'get_first_name' ) ) && 
	// 		is_callable( array( $object, 'get_last_name' ) ) && 
	// 		is_callable( array( $object, 'set_first_name' ) ) && 
	// 		is_callable( array( $object, 'set_last_name' ) ) &&
	// 		$object->get_first_name()
	// 	){
	// 		$first_name = $object->get_first_name();
	// 		//$last_name = $object->get_last_name();
	// 		$last_name = '';//overide for bug fix
	// 		get_called_class()::change_first_and_last_name_from_full_name($first_name, $last_name);
	// 		$object->set_first_name($first_name);
	// 		$object->set_last_name($last_name);
	// 	}

	// 	if( 
	// 		is_callable( array( $object, 'get_billing_first_name' ) ) && 
	// 		is_callable( array( $object, 'get_billing_last_name' ) ) && 
	// 		is_callable( array( $object, 'set_billing_first_name' ) ) && 
	// 		is_callable( array( $object, 'set_billing_last_name' ) ) &&
	// 		$object->get_billing_first_name()
	// 	){
	// 		$first_name = $object->get_billing_first_name();
	// 		//$last_name = $object->get_billing_last_name();
	// 		$last_name = '';//overide for bug fix
	// 		get_called_class()::change_first_and_last_name_from_full_name($first_name, $last_name);
	// 		$object->set_billing_first_name($first_name);
	// 		$object->set_billing_last_name($last_name);
	// 	}

	// 	if( 
	// 		is_callable( array( $object, 'get_shipping_first_name' ) ) && 
	// 		is_callable( array( $object, 'get_shipping_last_name' ) ) && 
	// 		is_callable( array( $object, 'set_shipping_first_name' ) ) && 
	// 		is_callable( array( $object, 'set_shipping_last_name' ) ) &&
	// 		$object->get_shipping_first_name()
	// 	){
	// 		$first_name = $object->get_shipping_first_name();
	// 		//$last_name = $object->get_shipping_last_name();
	// 		$last_name = '';//overide for bug fix
	// 		get_called_class()::change_first_and_last_name_from_full_name($first_name, $last_name);
	// 		$object->set_shipping_first_name($first_name);
	// 		$object->set_shipping_last_name($last_name);
	// 	}
	// 	get_called_class()::$is_checkout_process_name_customize_action_open = false;
	// }

	public static function overide_wc_template($located, $template_name){
		
		if( get_option('cartimize_setting_checkopt_enable_shipping_first') !== 'yes' &&
		get_option('cartimize_setting_checkopt_disable_billing_address') !== 'yes' &&
		get_option('cartimize_setting_checkopt_make_coupons_less_prominent') !== 'yes' &&
		get_option('cartimize_setting_checkopt_enable_delayed_account_creation_after_order') !== 'yes'){

			return $located;
		}

		if( 
			WC()->cart && WC()->cart->needs_shipping() && 
			(
				get_option('cartimize_setting_checkopt_disable_billing_address') === 'yes' ||
				(
					function_exists('wc_ship_to_billing_address_only') &&
					wc_ship_to_billing_address_only() &&
					get_option('cartimize_setting_checkopt_enable_shipping_first') === 'yes'
				)
			)
		 ){//if billing addr disabled, then shipping only left

			if( $template_name === 'checkout/form-checkout.php' ){
				return $located;
			}
			if( $template_name === 'checkout/form-billing.php' ){
				$located = CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/form-billing-and-shipping.php';
				return $located;
			}
			if( $template_name === 'checkout/form-shipping.php' ){
				$located = CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/form-shipping.php';
				return $located;
			}
		}

		if( WC()->cart && WC()->cart->needs_shipping() && 
		get_option('cartimize_setting_checkopt_enable_shipping_first') === 'yes' &&
		get_option('cartimize_setting_checkopt_disable_billing_address') !== 'yes' ){

			if( $template_name === 'checkout/form-checkout.php' ){
				$located = CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/ship-first-form-checkout.php';
				return $located;
			}
			if( $template_name === 'checkout/form-billing.php' ){
				$located = CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/ship-first-form-billing.php';
				return $located;
			}
			if( $template_name === 'checkout/form-shipping.php' ){
				$located = CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/ship-first-form-shipping.php';
				return $located;
			}
		}

		if( get_option('cartimize_setting_checkopt_make_coupons_less_prominent') === 'yes' ){

			if( $template_name === 'checkout/form-coupon.php' ){
				$located = CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/form-coupon.php';
				return $located;
			}
			if( $template_name === 'checkout/form-shipping.php' ){
				$located = CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/form-shipping.php';
				return $located;
			}
		}

		if( get_option('cartimize_setting_checkopt_enable_delayed_account_creation_after_order') === 'yes' ){

			if( $template_name === 'checkout/thankyou.php' ){
				$located = CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/thankyou.php';
				return $located;
			}
		}

		return $located;
	}

	public static function overide_wc_checkout_posted_data($data){
		if ( isset($_POST['cartimize_bill_to_different_address']) && $_POST['cartimize_bill_to_different_address'] === '1' ) {
			$wc_checkout  = new WC_Checkout();
			foreach ( $wc_checkout->get_checkout_fields( 'billing' ) as $key => $field ) {
				if( isset( $data[ 'shipping_' . substr( $key, 8 ) ] ) ){
					$data[ $key ] = isset( $data[ 'shipping_' . substr( $key, 8 ) ] ) ? $data[ 'shipping_' . substr( $key, 8 ) ] : '';
				}
			}
		}
		
		if( get_option('cartimize_setting_checkopt_single_full_name') === 'yes' ){
			//splitting first_name and last_name here
			if( isset($data['shipping_first_name']) && !isset($data['shipping_last_name']) ){
				$data['shipping_last_name'] = '';
				get_called_class()::change_first_and_last_name_from_full_name($data['shipping_first_name'], $data['shipping_last_name']);
			}
			if( isset($data['billing_first_name']) && !isset($data['billing_last_name']) ){
				$data['billing_last_name'] = '';
				get_called_class()::change_first_and_last_name_from_full_name($data['billing_first_name'], $data['billing_last_name']);
			}
		}		
		return $data;
	}

	public static function overide_wc_coupons_enabled($is_enabled){
		if(  get_option('cartimize_setting_checkopt_remove_coupon_field_in_cart_page') === 'yes' && get_called_class()::$is_cart_page_before_coupon_section ){
			return false;
		}
		return $is_enabled;
	}

	public static function set_cart_page_before_coupon_section(){
		get_called_class()::$is_cart_page_before_coupon_section = true;
	}

	public static function set_cart_page_after_coupon_section(){
		get_called_class()::$is_cart_page_before_coupon_section = false;
	}

	public static function move_coupon_form_from_before_to_after_checkout_form(){
		if(  get_option('cartimize_setting_checkopt_make_coupons_less_prominent') === 'yes'){
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
			add_action( 'woocommerce_after_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		}
	}

	private static function customize_name_fields($fields){
		if( get_option('cartimize_setting_checkopt_single_full_name') !== 'yes' ){
			return $fields;
		}
		unset($fields['billing']['billing_last_name']);
		unset($fields['shipping']['shipping_last_name']);
		
		if( isset($fields['billing']['billing_first_name']) ){
			//changing the label
			$fields['billing']['billing_first_name']['label'] = __( 'Full name', 'woo-checkout-optimization-cartimize' );
			$fields['billing']['billing_first_name']['autocomplete'] = 'name';

			//adding container class
			get_called_class()::add_remove_items_to_array($fields['billing']['billing_first_name']['class'], array('form-row-wide', 'cartimize_checkopt_first_name_cont'), array('form-row-first'));
		}

		if( isset($fields['shipping']['shipping_first_name']) ){
			//changing the label
			$fields['shipping']['shipping_first_name']['label'] = __( 'Full name', 'woo-checkout-optimization-cartimize' );
			$fields['shipping']['shipping_first_name']['autocomplete'] = 'name';

			//adding container class
			get_called_class()::add_remove_items_to_array($fields['shipping']['shipping_first_name']['class'], array('form-row-wide', 'cartimize_checkopt_first_name_cont'), array('form-row-first'));
		}

		// $css = '#billing_first_name_field, #shipping_first_name_field { width:100%; }';
		// get_called_class()::add_css_txt($css, 'checkout_before_customer_details');
		
		return $fields;
	}

	private static function customize_hide_company_name_behind_link($args, $key, $value){

		if( $key !== 'billing_company' && $key !== 'shipping_company' ){
			return $args;
		}

		if( get_option('cartimize_setting_checkopt_hide_company_name') !== 'yes' ){
			return $args;
		}

		if( $args['required'] ){
			return $args;
		}

		if( $value !== null ){//if value is there do not hide. Here empty value comes as null
			return $args;
		}
		
		if( $key === 'billing_company' || $key === 'shipping_company' ){
			//adding container class
			get_called_class()::add_remove_items_to_array($args['class'], 'cartimize_checkopt_company_name_hide');
		}

		return $args;
	}

	public static function company_name_hide_add_txt_before($field, $key, $args, $value){
		if( $key !== 'billing_company' && $key !== 'shipping_company' ){
			return $field;
		}

		if( get_option('cartimize_setting_checkopt_hide_company_name') !== 'yes' ){
			return $field;
		}

		if( $args['required'] ){
			return $field;
		}

		if( $value !== '' ){//if value is there do not hide. Here empty value comes as ''
			return $field;
		}

		$prefix_cont = '<p class="form-row form-row-wide cartimize_company_name_show_cont" id="cartimize_company_name_show_cont_'.$key.'"><a class="cartimize_expand_link show_company_name">'.__( 'Add a company name', 'woo-checkout-optimization-cartimize' ).'</a></p>';
		$field = $prefix_cont. "\n" .$field;
		return $field;
	}

	private static function customize_hide_address_2_behind_link($args, $key, $value){

		if( $key !== 'billing_address_2' && $key !== 'shipping_address_2' && $key !== 'billing_address_1' && $key !== 'shipping_address_1' ){
			return $args;
		}

		if( get_option('cartimize_setting_checkopt_hide_address2') !== 'yes' ){
			return $args;
		}

		if( $key === 'billing_address_2' || $key === 'shipping_address_2' ){

			if( $args['required'] ){
				return $args;
			}

			if( $value !== null ){//if value is there do not hide. Here empty value comes as null
				return $args;
			}

			//adding container class
			get_called_class()::add_remove_items_to_array($args['class'], 'cartimize_checkopt_address_2_hide');
		}

		if( $key === 'billing_address_1' || $key === 'shipping_address_1' ){

			//adding container class
			get_called_class()::add_remove_items_to_array($args['input_class'], 'cartimize_checkopt_hide_placeholder');//from js remove cartimize_checkopt_hide_placeholder if no hide address_2 behind link
			
		}

		return $args;
	}

	public static function address_2_hide_add_txt_before($field, $key, $args, $value){
		if( $key !== 'billing_address_2' && $key !== 'shipping_address_2' ){
			return $field;
		}

		if( get_option('cartimize_setting_checkopt_hide_address2') !== 'yes' ){
			return $field;
		}

		if( $args['required'] ){
			return $field;
		}

		if( $value !== '' ){//if value is there do not hide. Here empty value comes as ''
			return $field;
		}

		$prefix_cont = '<p class="form-row form-row-wide cartimize_address_2_show_cont" id="cartimize_address_2_show_cont_'.$key.'"><a class="cartimize_expand_link show_address_2">'.__( 'Add another address line', 'woo-checkout-optimization-cartimize' ).'</a></p>';
		$field = $prefix_cont. "\n" .$field;
		return $field;
	}

	private static function customize_hide_order_comments_behind_link($args, $key, $value){

		if( $key !== 'order_comments' ){
			return $args;
		}

		if( get_option('cartimize_setting_checkopt_hide_order_comments') !== 'yes' ){
			return $args;
		}

		if( !apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ){
			return $args;
		}

		if( $value !== null ){//if value is there do not hide. Here empty value comes as null
			return $args;
		}

		//adding container class
		get_called_class()::add_remove_items_to_array($args['class'], 'cartimize_checkopt_order_comments_hide');

		return $args;
	}

	public static function order_comments_hide_add_txt_before($field, $key, $args, $value){
		if( $key !== 'order_comments' ){
			return $field;
		}

		if( get_option('cartimize_setting_checkopt_hide_order_comments') !== 'yes' ){
			return $field;
		}

		if( !apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ){
			return $field;
		}

		if( $value !== '' ){//if value is there do not hide. Here empty value comes as ''
			return $field;
		}

		$prefix_cont = '<p class="form-row form-row-wide cartimize_order_comments_show_cont"><a class="cartimize_expand_link show_order_comments">'.__( 'Add a note to this order', 'woo-checkout-optimization-cartimize' ).'</a></p>';
		$field = $prefix_cont. "\n" .$field;
		return $field;
	}

	private static function customize_add_desc_for_billing_phone($fields){
		if( get_option('cartimize_setting_checkopt_add_desc_for_billing_phone') !== 'yes' || empty( $phone_desc = get_option('cartimize_setting_checkopt_billing_phone_desc') ) ){
			return $fields;
		}
		
		if( isset($fields['billing']['billing_phone']) ){
			$fields['billing']['billing_phone']['description'] = $phone_desc;

			$billing_phone = &$fields['billing']['billing_phone'];
			get_called_class()::add_remove_items_to_array($billing_phone['class'], 'cartimize_checkopt_phone_with_descr');
		}

		return $fields;
	}

	private static function bring_shipping_fieldset_before_billing($fields){

		if( get_option('cartimize_setting_checkopt_enable_shipping_first') !== 'yes' &&
		get_option('cartimize_setting_checkopt_disable_billing_address') !== 'yes' ){
			return $fields;
		}

		$new_fields = array();
		$new_keys_order = $exisiting_keys_order = array_keys($fields);
		$shipping_key = array_search('shipping', $exisiting_keys_order);
		$billing_key = array_search('billing', $exisiting_keys_order);
		if( isset($fields['shipping']) && isset($fields['billing']) && $shipping_key > $billing_key ){
			array_splice($new_keys_order, $shipping_key, 1, array('billing'));
			array_splice($new_keys_order, $billing_key, 1, array('shipping'));

			foreach($new_keys_order as $key ){
				$new_fields[$key] = $fields[$key];
			}
			$fields = $new_fields;
		}

		return $fields;
	}

	public static function get_data_for_frontend_js($data_for_js){
		return $data_for_js;
	}

	// public static function customize_checkout_update_customer_full_name_save_as($customer, $data){
	// 	if( get_option('cartimize_setting_checkopt_single_full_name') !== 'yes' ){
	// 		return;
	// 	}

	// 	if( !class_exists('WC_Customer') || !( $customer instanceof WC_Customer ) ){
	// 		return;
	// 	}

	// 	if( !is_callable( array( $customer, 'get_first_name' ) ) || 
	// 		!is_callable( array( $customer, 'get_last_name' ) ) || 
	// 		!is_callable( array( $customer, 'set_first_name' ) ) || 
	// 		!is_callable( array( $customer, 'set_last_name' ) ) 
	// 	){
	// 		return;
	// 	}

	// 	$first_name = $customer->get_first_name();
	// 	$last_name = $customer->get_last_name();

	// 	//assuming sanitizing taken care


	// 	get_called_class()::change_first_and_last_name_from_full_name($first_name, $last_name);

	// 	$customer->set_first_name($first_name);
	// 	$customer->set_last_name($last_name);

	// 	//after this $customer->save() will be called after this hook
	// }

	
	// public static function customize_checkout_before_order_save_full_name_save_as($order, $data){
	// 	if( get_option('cartimize_setting_checkopt_single_full_name') !== 'yes' ){
	// 		return;
	// 	}

	// 	if( !class_exists('WC_Order') || !( $order instanceof WC_Order ) ){
	// 		return;
	// 	}

	// 	if( !is_callable( array( $order, 'get_billing_first_name' ) ) || 
	// 		!is_callable( array( $order, 'get_billing_last_name' ) ) || 
	// 		!is_callable( array( $order, 'set_billing_first_name' ) ) || 
	// 		!is_callable( array( $order, 'set_billing_last_name' ) ) || 
	// 		!is_callable( array( $order, 'get_shipping_first_name' ) ) || 
	// 		!is_callable( array( $order, 'get_shipping_last_name' ) ) || 
	// 		!is_callable( array( $order, 'set_shipping_first_name' ) ) || 
	// 		!is_callable( array( $order, 'set_shipping_last_name' ) ) 
	// 	){
	// 		return;
	// 	}

	// 	$billing_first_name = $order->get_billing_first_name();
	// 	$billing_last_name = $order->get_billing_last_name();
	// 	$shipping_first_name = $order->get_shipping_first_name();
	// 	$shipping_last_name = $order->get_shipping_last_name();

	// 	//assuming sanitizing taken care


	// 	get_called_class()::change_first_and_last_name_from_full_name($billing_first_name, $billing_last_name);
	// 	get_called_class()::change_first_and_last_name_from_full_name($shipping_first_name, $shipping_last_name);

	// 	$order->set_billing_first_name($billing_first_name);
	// 	$order->set_billing_last_name($billing_last_name);
	// 	$order->set_shipping_first_name($shipping_first_name);
	// 	$order->set_shipping_last_name($shipping_last_name);

	// 	//after this $order->save() will be called after this hook
	// }

	public static function get_classes_for_body($classes){

		return $classes;
	}

	public static function additional_options_check_and_print_heading(){
		$is_print_additional_options = false; //priority 1
		$is_print_additional_information = false; //priority 2
		$options_count = 0;
		if( wc_coupons_enabled() && get_option('cartimize_setting_checkopt_make_coupons_less_prominent') === 'yes' ){
			$is_print_additional_options = true;
			$options_count++;
		}

		if( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ){
			$options_count++;
			if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) {
				$is_print_additional_information = true;
			}
		}


		if( $is_print_additional_options ){
			?><h3><?php echo _n( 'Additional option', 'Additional options', $options_count, 'woo-checkout-optimization-cartimize' ); ?></h3><?php
		}
		elseif( $is_print_additional_information ){
			?><h3><?php _e( 'Additional information', 'woocommerce' );?></h3><?php
		}
	}

	//utility function
	public static function change_first_and_last_name_from_full_name(&$first_name, &$last_name){//full name by default it will be in first name of the input

		if( $last_name || !$first_name ){
			return;
		}

		$name_parts = explode(' ', $first_name, 2);

		$first_name = trim($name_parts[0]);

		if( isset($name_parts[1]) ){
			$last_name =  trim($name_parts[1]);
		}
	}

	//utility function
	public static function add_remove_items_to_array(&$array, $add_items=null, $remove_items=null){
		if( $add_items === null && $remove_items === null ){
			return;
		}
		if( empty($array) ){
			$array = array();
		}
		if( !is_array($array) ){
			$array = (array) $array;
		}
		//add items
		if( $add_items !== null){
			if( !is_array($add_items) ){
				$add_items =  (array)$add_items;
			}
			foreach($add_items as $add_item){
				array_push($array, $add_item);
			}
		}

		//remove items
		if( $remove_items !== null){
			if( !is_array($remove_items) ){
				$remove_items =  (array)$remove_items;
			}
			foreach($remove_items as $remove_item){
				$index = array_search($remove_item, $array, true);
				if( $index === false ){
					continue;
				}
				unset($array[$index]);
			}
		}
		
	}

	public static function wc_admin_setting_type_cartimize_row_text($value){
		if( !class_exists('WC_Admin_Settings') || !is_callable( array('WC_Admin_Settings', 'get_field_description') ) ){
			return;
		}

		$field_description = WC_Admin_Settings::get_field_description( $value );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];

		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
			<td class="forminp">
				<fieldset>
					<?php echo $description; // WPCS: XSS ok. ?>
					<?php echo $tooltip_html; // WPCS: XSS ok. ?>
				</fieldset>
			</td>
		</tr>
		<?php

	}
}

Cartimize_Checkopt_WC_Checkout_Customize::init();