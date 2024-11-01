<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

if(!function_exists('cartimize_checkopt_is_plugin_active')){
	function cartimize_checkopt_is_plugin_active($plugin_main_file){
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ;
		if(is_multisite()){
			$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
		}
		return in_array($plugin_main_file, $active_plugins) || array_key_exists($plugin_main_file, $active_plugins);
	}
}

if(!function_exists('cartimize_checkopt_is_woocommerce_active')){
	function cartimize_checkopt_is_woocommerce_active(){
		if( class_exists( 'WooCommerce' ) ){
			return true;
		}
		return cartimize_checkopt_is_plugin_active('woocommerce/woocommerce.php');
	}
}

if(!function_exists('cartimize_checkopt_is_cartimize_checkopt_active')){
	function cartimize_checkopt_is_cartimize_checkopt_active(){
		return cartimize_checkopt_is_plugin_active('woo-checkout-optimization-cartimize/woo-checkout-optimization-cartimize.php');
	}
}

if(!function_exists('cartimize_checkopt_is_cartimize_checkopt_pro_active')){
	function cartimize_checkopt_is_cartimize_checkopt_pro_active(){
		if(class_exists('Cartimize_Checkopt_Pro_Init')){
			return true;
		}
		
		return cartimize_checkopt_is_plugin_active('woo-checkout-optimization-cartimize-pro/woo-checkout-optimization-cartimize-pro.php');
	}
}

if(!function_exists('cartimize_checkopt_deactivate_self')){
	function cartimize_checkopt_deactivate_self(){
		$plugin_slug = basename(dirname(dirname(__FILE__)));
		deactivate_plugins( $plugin_slug .'/'. $plugin_slug.'.php' );

		if( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}

if(!function_exists('cartimize_checkopt_if_woocom_not_active_deactivate_self_notice')){
	function cartimize_checkopt_if_woocom_not_active_deactivate_self_notice() {
		echo '<div class="notice notice-warning"><p>The \'WooCommerce Checkout Optimization by Cartimize\' plugin has been deactivated. It requires WooCommerce to be active.</p></div>';
	}
}

if(!function_exists('cartimize_checkopt_if_requirments_not_met_deactivate_self_notice')){
	function cartimize_checkopt_if_requirments_not_met_deactivate_self_notice() {
		echo '<div class="notice notice-warning"><p>WooCommerce Checkout Optimization by Cartimize - Minimum requirements not met.'; 
		if( is_array($GLOBALS['cartimize_checkopt_requirements']) ){
			cartimize_checkopt_show_requirements($GLOBALS['cartimize_checkopt_requirements'], true, false);
		}
		echo '</p></div>';
	}
}

if(!function_exists('cartimize_checkopt_woocom_not_active_deactivate_self')){
	function cartimize_checkopt_woocom_not_active_deactivate_self(){
		add_action( 'admin_init', 'cartimize_checkopt_deactivate_self' );
		add_action( 'admin_notices', 'cartimize_checkopt_if_woocom_not_active_deactivate_self_notice' );
	}
}

if(!function_exists('cartimize_checkopt_requirements_not_met_deactivate_self')){
	function cartimize_checkopt_requirements_not_met_deactivate_self(){
		add_action( 'admin_init', 'cartimize_checkopt_deactivate_self' );
		add_action( 'admin_notices', 'cartimize_checkopt_if_requirments_not_met_deactivate_self_notice' );
	}
}


if(!function_exists('cartimize_checkopt_check_min_version_requirements')){
	function cartimize_checkopt_check_min_version_requirements($full=true){

		global $woocommerce;

		$required = array();
		$required['php']['version'] = '7.0';
		$required['php']['name'] = 'PHP';
		$required['mysql']['version'] = '5.5';
		$required['mysql']['name'] = 'MySQl';
		$required['wp']['version'] = '4.7';
		$required['wp']['name'] = 'WP';
		$required['wc']['version'] = '3.5';
		$required['wc']['name'] = 'WooCommerce';


		if($full){
			$mysql_full_version = $GLOBALS['wpdb']->get_var("SELECT VERSION()");
			$mysql_tmp = explode('-', $mysql_full_version);
			$mysql_version = array_shift($mysql_tmp);
		}

		$php_version = PHP_VERSION;
		$php_tmp = explode('-', $php_version);
		$php_version = array_shift($php_tmp);

		$wc_version = $woocommerce->version;//make sure WooCommerce is active before using this.

		$installed = array();
		$installed['php']['version'] = $php_version;
		if($full){
			$installed['mysql']['version'] = $mysql_version;
		}
		$installed['wp']['version'] = get_bloginfo( 'version' );
		$installed['wc']['version'] = $wc_version;

		$is_all_ok = true;
		if (version_compare($installed['php']['version'], $required['php']['version'], '<')) {
			//not ok
			$is_all_ok = false;
			$installed['php']['is_met'] = false;
		}
		else{
			$installed['php']['is_met'] = true;
		}
		if ($full){
			if( version_compare($installed['mysql']['version'], $required['mysql']['version'], '<')) {
				//not ok
				$is_all_ok = false;
				$installed['mysql']['is_met'] = false;
			}
			else{
				$installed['mysql']['is_met'] = true;
			}
		}

		if (version_compare($installed['wp']['version'], $required['wp']['version'], '<')) {
			//not ok
			$is_all_ok = false;
			$installed['wp']['is_met'] = false;
		}
		else{
			$installed['wp']['is_met'] = true;
		}

		if (version_compare($installed['wc']['version'], $required['wc']['version'], '<')) {
			//not ok
			$is_all_ok = false;
			$installed['wc']['is_met'] = false;
		}
		else{
			
			$installed['wc']['is_met'] = true;
		}

		if($is_all_ok){
			return true;
		}
		if(!$full){
			unset($required['mysql'], $installed['mysql']);
		}
		return array('required' => $required, 'installed' => $installed);
	}
}

if(!function_exists('cartimize_checkopt_show_requirements')){
	function cartimize_checkopt_show_requirements($rr, $show_only_not_met=false, $show_heading=true){
		$show_all = !$show_only_not_met;
	?>
	<table width="300" border="0" cellspacing="5"><tbody>
	<?php if( $show_heading ){ ?>
		<tr><td class="" colspan="3"><span class="error-box">Minimum requirements not met!</span><br><br></td></tr>		
	<?php
	}
	?>
	<tr><td></td><td align="left"><strong>Required</strong></td><td align="left"><strong>Current</strong></td></tr>
	<?php
	foreach($rr['required'] as $item => $value){
		if( $show_all || ($show_only_not_met && !$rr['installed'][$item]['is_met']) ){ ?>
			<tr><td><?php echo $rr['required'][$item]['name'] ?></td><td><?php echo $rr['required'][$item]['version'] ?></td> <td><?php echo $rr['installed'][$item]['version'] ?></td></tr>
			<?php 
			}
	}
	?>
	</tbody></table>
	<?php
	}
}