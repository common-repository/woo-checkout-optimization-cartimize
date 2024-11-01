<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

function cartimize_checkopt_is_free_plugin(){
	return cartimize_checkopt_is_plugin_type('free');
}

function cartimize_checkopt_is_pro_plugin(){
	return cartimize_checkopt_is_plugin_type('pro');
}

function cartimize_checkopt_is_plugin_type($type){
	static $deducted_type = null;

	if( !$deducted_type ){
		if( $type !== 'free' && $type !== 'pro' ){
			return null;
		}
		$plugin_slug = basename(dirname(dirname(__FILE__)));
		if( $plugin_slug === 'woo-checkout-optimization-cartimize' ){
			$deducted_type = 'free';
		}
		elseif( $plugin_slug === 'woo-checkout-optimization-cartimize-pro' ){
			$deducted_type = 'pro';
		}
	}
	if( is_null($deducted_type) ){
		return null;
	}
	return $deducted_type === $type ? true : false;
}

function cartimize_get_response_from_json($response){
	cartimize_check_response_error($response);
	cartimize_check_http_error($response);

	$response_str = wp_remote_retrieve_body($response);
	$clean_response_str = cartimize_remove_response_junk($response_str);
	$response_data = json_decode($clean_response_str, true);

	if($response_data === null){
		//if required use json_last_error()
		throw new cartimize_exception('invalid_response_json_failed');
	}
	
	return $response_data;
}

function cartimize_check_response_error($response){
	if ( is_wp_error( $response ) ) {
		throw new cartimize_exception($response->get_error_code(), $response->get_error_message());
	}
}

function cartimize_check_http_error($response){
	$http_code = wp_remote_retrieve_response_code( $response );
	if($http_code !== 200){
		$response_msg = wp_remote_retrieve_response_message( $response );
		throw new cartimize_exception('http_error', 'HTTP status code: ('.$http_code.') '.$response_msg);
	}
}

function cartimize_get_response_body($response){
	$response_str = wp_remote_retrieve_body($response);
	if( empty( trim($response_str) )){
		throw new cartimize_exception('invalid_response_empty');
	}
	return $response_str;
}

function cartimize_remove_response_junk($response){
	$start_tag_len = strlen('<cartimize_response>');
	$start_pos = stripos($response, '<cartimize_response');
	$end_pos = stripos($response, '</cartimize_response');
	if($start_pos === false || $end_pos === false){
		throw new cartimize_exception('invalid_response_format');
	}

	$response = substr($response, $start_pos);//clearing anything before start tag
	$end_pos = stripos($response, '</cartimize_response');//new end_pos
	$response = substr($response, $start_tag_len, $end_pos-$start_tag_len);

	return $response;
}

function cartimize_prepare_response($response){//to send response in form json with a wrapper
	$json = json_encode($response);
	return '<cartimize_response>'.$json.'</cartimize_response>';
}

function cartimize_show_http_response_in_error_msg($http_response, $additional_msg=''){

	$http_response_str = var_export($http_response, true);
	$http_response_str = htmlentities($http_response_str);
	if(!empty($additional_msg)){
		$http_response_str = $additional_msg."\n".$http_response_str;
	}
	$return = '<br><a onClick="jQuery(this).next(\'textarea.cartimize_http_response_display\').toggle();" style="color: unset; cursor: pointer; text-decoration: underline;">Click here to see HTTP response</a>.<textarea class="cartimize_http_response_display" style="display:none;width:400px;height:100px;">'.$http_response_str.'</textarea>';
	return $return;
}


class cartimize_exception extends Exception {
	//$error is as error code like slug
	protected $error;
	public function __construct($error = '', $message = '', $code = 0, $previous_throwable = NULL){
		$this->error = $error;
		parent::__construct($message, $code, $previous_throwable);
	}
	public function getError(){
		return $this->error;
	}
	public function getFormatedError(){
		return cartimize_get_error_msg($this->error);
	}
	public function getErrorMsg(){
		$msg = $this->getMessage();
		return empty($msg) ?  $this->getFormatedError() : $msg;
	}
}

function cartimize_get_error_msg($error_slug){
	return cartimize_get_lang($error_slug);
}

function cartimize_get_lang($lang_slug){
	static $lang;
	if(!isset($lang)){
		include_once(CARTIMIZE_CHECKOPT_PATH . '/lang.php');
		$lang = $cartimize_lang;
	}
	return isset($lang[$lang_slug]) ? $lang[$lang_slug] : $lang_slug;
}

/*
* cartimize_http_build_query_for_curl() supports build query with file upload
* where as http_build_query() supports only multi dimension array not with file upload
*/
function cartimize_http_build_query_for_curl( $arrays, &$new = array(), $prefix = null ) {

	if ( $arrays  instanceof CURLFile ) {
		$new[$prefix] = $arrays;
		return $new;
	}
	
	if ( is_object( $arrays ) ) {
		$arrays = get_object_vars( $arrays );
	}

	foreach ( $arrays as $key => $value ) {
		$k = isset( $prefix ) ? $prefix . '[' . $key . ']' : $key;
		if ( is_array( $value ) || is_object( $value )  ) {
			$new = cartimize_http_build_query_for_curl( $value, $new, $k );
		} else {
			$new[$k] = $value;
		}
	}
	return $new;
}
