<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

if(isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])){
	$redirect_to = $_GET['redirect_to'];
}
else{
	$redirect_to = 'wc-settings&tab=cartimize_checkopt_settings&show=welcome_msg';
}
$redirect_url = esc_url_raw( admin_url( 'admin.php?page='.$redirect_to ) );


?>
<div class="cartimize_admin">
	<div class="acc-login-form">
		<h2>
		 <?php echo __( 'Login to your Cartimize account', 'cartimize-woo-checkout-optimizer' ); ?>
		</h2>
		<p><?php echo __( 'You would have recieved an account creation email with your password when you purchased the plugin.', 'cartimize-woo-checkout-optimizer' ); ?></p>
		<div class="pad">
			<div id="cartimize_service_login_btn_result"></div>

				<fieldset>
					<label for="cartimize_service_email"><?php echo __( 'Email', 'cartimize-woo-checkout-optimizer' ); ?></label> 
					<input type="text" id="cartimize_service_email">
				</fieldset>

			<fieldset>
					<label for="cartimize_service_password" style="
	"><?php echo __( 'Password', 'cartimize-woo-checkout-optimizer' ); ?></label> 
					<input type="password" id="cartimize_service_password">
				<a href="<?php echo CARTIMIZE_SITE_LOST_PASS_URL; ?>" target="_blank" style="
		">Forgot password?</a></fieldset>
		</div>
		
			<input type="button" value="Login to my account" name="service_login" class="button-primary" id="cartimize_service_login_btn">
	</div>
</div>

<script type="text/javascript">
var cartimize_redirect_after_login = '<?php echo $redirect_url; ?>';
</script>