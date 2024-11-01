<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

$cartimize_lang = array();

$cartimize_lang['invalid_request'] = __('Invalid request.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['invalid_response'] = __('Invalid response. Please try again.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['invalid_response_empty'] = __('Empty response received. Please try again.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['invalid_response_format'] = __('Invalid response format. Please try again.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['invalid_response_json_failed'] = __('Invalid response json failed. Please try again.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['http_error'] = __('HTTP Error.', 'woo-checkout-optimization-cartimize' );

//service API messages
$cartimize_lang['service__invalid_response'] = __('Invalid response. Please try again.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['service__invalid_request'] = __('Invalid request. Please try again.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['service__login_error'] = __('Email or password is incorrect.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['service__expired'] = __('Your subscribed plan has expired. Please <a href="'.CARTIMIZE_MY_ACCOUNT_URL.'" target="_blank">renew your license</a>.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['service__limit_reached'] = __('You have reached the sites limit for your plan. Please <a href="'.CARTIMIZE_MY_ACCOUNT_URL.'" target="_blank">Upgrade your plan</a>.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['service__invalid_license_info'] = __('Invalid license information. please <a href="mailto:help@cartimize.com?Subject=Invalid%20license%20information" target="_blank">contact support</a>.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['service__server_issue'] = __('Temporary server issue. Please try again later. If issue persists, <a href="mailto:help@cartimize.com?Subject=Invalid%20license%20information" target="_blank">contact support</a>.', 'woo-checkout-optimization-cartimize' );
$cartimize_lang['service__invalid_token'] = __('Invalid token received. Please try again. If issue persists, <a href="mailto:help@cartimize.com?Subject=Invalid%20token%20received" target="_blank">contact support</a>.', 'woo-checkout-optimization-cartimize' );
