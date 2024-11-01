<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class Cartimize_Checkopt_WC_Settings_Tab {

	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', get_called_class() . '::add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_cartimize_checkopt_settings', get_called_class() . '::settings_tab' );
		add_action( 'woocommerce_update_options_cartimize_checkopt_settings', get_called_class() . '::update_settings' );
	}
	
	public static function add_settings_tab( $settings_tabs ) {
		$settings_tabs['cartimize_checkopt_settings'] = __( 'Cartimize', 'woo-checkout-optimization-cartimize' );
		return $settings_tabs;
	}

	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @uses woocommerce_update_options()
	 * @uses get_called_class()::get_settings()
	 */
	public static function update_settings() {
		woocommerce_update_options( get_called_class()::get_settings() );
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses get_called_class()::get_settings()
	 */
	public static function settings_tab() {
		echo '<div class="cartimize_checkopt_settings_page">';
		$requirement_met = cartimize_checkopt_check_min_version_requirements();
		if( $requirement_met !== true ){
			$GLOBALS['hide_save_button'] = true;
			$requirements = $requirement_met;
			cartimize_checkopt_show_requirements($requirements, true);
		}
		else{

			get_called_class()::check_and_show_first_activate_setting_page_msg();
			?>
			<div style="position: absolute; right: 20px;">
			<?php
			echo '<span>'.__('Need help?', 'woo-checkout-optimization-cartimize' ).' <a target="_blank" href="mailto:help@cartimize.com?subject=WooCommerce Checkout Optimization Plugin - ">help@cartimize.com</a></span>';
			get_called_class()::show_pro_preview_toggle();
			?></div>
			<?php
			Cartimize_Checkopt_Customize_WC_Admin_Settings::output_fields( get_called_class()::get_settings(true) );

		}
		echo '</div>';
	}

	private static function show_pro_preview_toggle(){
		?>
		<div class="buy_pro_right_side_cont">
			<div style="font-weight: bold; text-align: center; padding-bottom: 10px;">Upgrade to Pro</div>
			<label>
			<input type="checkbox" id="cartimize_toggle_pro_preview_settings"> Show Pro features
			</label>
			<br>
			<a class="go_pro_btn_link" href="<?php echo CARTIMIZE_CHECKOPT_GO_PRO_URL; ?>" target="_blank">Go Pro</a>
		</div>
		<?php
	}

	private static function check_and_show_first_activate_setting_page_msg(){
		if( isset($_GET['show']) && $_GET['show'] === 'welcome_msg' ){
			?>
			<div style="background-color: #ffffe5; padding: 10px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,0.1);">
				<?php echo __('Welcome to Cartimize\'s WooCommerce Checkout Optimization plugin. Let\'s now implement some <strong><em>research-backed</em></strong> user experience improvements to the checkout flow that are proven to improve conversion. <br>Read more on <a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin" target="_blank">The pitfalls of the WooCommerce checkout and how to fix them</a> article.', 'woo-checkout-optimization-cartimize' ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @return array Array of settings for @see woocommerce_admin_fields() function.
	 */
	public static function get_settings($get_pro_preview=false) {
		$settings = array(

			//Simplify checkout forms starts here
			1000 => array(
				'name'     => __( 'Simplifying checkout forms', 'woo-checkout-optimization-cartimize' ),
				'type'     => 'title',
				'desc'     => __( 'To achieve a perceived simplicity of the checkout flow, we can minimize the number of form fields and selections, displayed by default.', 'woo-checkout-optimization-cartimize' ),
				'id'       => 'cartimize_checkopt_simplify_forms_section'
			),
			1010 => array(
				'title' => __( 'Name fields', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Use a single Full name field', 'woo-checkout-optimization-cartimize' ),
				'desc_tip' => __( 'Users generally think of their name as a single entity and acknowledging this tendency by implementing a single "Full Name" field performs well.<br>The first word will be saved as the first name and the rest as last name.', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_single_full_name',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#use-full-name" target="_blank">Why?</a>',
				'checkboxgroup'   => 'start',
			),
			1020 => array(
				'title' => __( '', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Hide \'Company Name\' field, if optional, behind a link', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_hide_company_name',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#hide-company-name" target="_blank">Why?</a>',
				'checkboxgroup'   => 'end',
			),
			1030 => array(
				'title' => __( 'Addres Fields', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Hide second address field, if optional, behind a link', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_hide_address2',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#hide-second-address" target="_blank">Why?</a>',
				'checkboxgroup'   => 'start',
			),
			1050 => array(
				'title' => __( 'Phone number', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Include an explanation why the phone number is needed', 'woo-checkout-optimization-cartimize' ),
				'desc_tip' => __( 'Users continue to be concerned about the security and confidentiality of their personal information on the web. When it’s truly necessary to require users to supply the information, it should clearly be explained why the data is required.', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_add_desc_for_billing_phone',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#explain-phone-number" target="_blank">Why?</a>',
				'checkboxgroup'   => 'start',
			),
			1051 => array(
				'title' => __( '', 'woo-checkout-optimization-cartimize' ),
				'type' => 'text',
				'desc' => __( '<br>Eg. Only for shipping-related queries', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_billing_phone_desc',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#explain-phone-number" target="_blank">Why?</a>',
				'checkboxgroup'   => 'end',
			),
			1060 => array(
				'title' => __( 'Order Notes', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Hide \'Order notes\' field behind a link', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_hide_order_comments',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#hide-order-notes" target="_blank">Why?</a>',
			),
			1990 => array(
				 'type' => 'sectionend',
				 'id' => 'cartimize_checkopt_simplify_forms_section'
			),
			//Simplify checkout forms ends here

			//Shipping and billing address starts here
			3000 => array(
				'name'     => __( 'Shipping and Billing address', 'woo-checkout-optimization-cartimize' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'cartimize_checkopt_ship_bill_addr_section'
			),
			3010 => array(
				'title' => __( 'Shipping Address', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Let users enter their Shipping Address first instead of Billing Address', 'woo-checkout-optimization-cartimize' ),
				'desc_tip' => __( 'Users have no issues understanding what a shipping address is, as opposed to a billing address. Asking users to type their shipping address instead of a billing address performs better.' ),
				'id'   => 'cartimize_setting_checkopt_enable_shipping_first',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#use-shipping-as-billing" target="_blank">Why?</a>',
				'checkboxgroup'   => 'start',
			),
			3011 => array(
				'title' => __( '', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Set Shipping Address as Billing Address by default', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_set_shipping_address_as_billing_address_default',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#billing-is-shipping" target="_blank">Why?</a>',
				'checkboxgroup'   => 'end',
			),
			3990 => array(
				'type' => 'sectionend',
				'id' => 'cartimize_checkopt_ship_bill_addr_section'
			),
			//Shipping and billing address ends here

			//Coupon section prominence starts here
			5000 => array(
				'name'     => __( 'Coupon Section Prominence', 'woo-checkout-optimization-cartimize' ),
				'type'     => 'title',
				'desc'     => __( 'Showing the coupon code section prominently directly in the checkout flow is one of the best ways to make sure all users without a coupon notice that they could be getting a better deal on their purchase. To reduce the amount of needless attention drawn, you can collapse it behind a link.', 'woo-checkout-optimization-cartimize' ),
				'id'       => 'cartimize_checkopt_less_prominent_coupon_section'
			),
			5010 => array(
				'title' => __( 'Enable?', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Yes, make coupon section much less prominent', 'woo-checkout-optimization-cartimize' ),
				'desc_tip' => __( 'If enabled, the coupon section will be moved below the address form and hidden behind a link.', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_make_coupons_less_prominent',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#coupon-less-prominent" target="_blank">Why?</a>',
				'checkboxgroup'   => 'start',
			),
			5020 => array(
				'title' => __( '', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Remove coupon field from Cart page', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_setting_checkopt_remove_coupon_field_in_cart_page',
				'checkboxgroup'   => 'end',
			),
			5990 => array(
				'type' => 'sectionend',
				'id' => 'cartimize_checkopt_less_prominent_coupon_section'
			),
			//Coupon section prominence ends here
		);

		if($get_pro_preview){
			$pro_preview_settings = get_called_class()::get_pro_preview_settings();
			$settings = $settings + $pro_preview_settings;
			ksort($settings);
		}
		return $settings;
	}

	private static function get_pro_preview_settings(){
		$pro_preview_settings = array(

			//Simplify checkout forms starts here
			1040 => array(
				'title' => __( '', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Auto-detect City and State from ZIP code', 'woo-checkout-optimization-cartimize' ),
				'desc_tip' => __( 'Once user enters the ZIP code, City and State will be auto-detected.<br><span style="font-size: 11px;">See <a href="http://zippopotam.us/#where" target="_blank">supported countries</a>.</span>', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_checkopt_pro_preview_get_city_state',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#auto-detect-after-zip" target="_blank">Why?</a>',
				'checkboxgroup'   => 'end',
			),
			//Simplify checkout forms ends here

			//Inline Validation starts here
			2000 => array(
				'name'     => __( 'Live inline validation', 'woo-checkout-optimization-cartimize' ),
				'type'     => 'title',
				'desc'     => __( 'The validity of the user’s inputs are checked live as the user progresses through the form, as opposed to checking the inputs in a lump sum when the user submits the form.', 'woo-checkout-optimization-cartimize' ),
				'id'       => 'cartimize_checkopt_live_inline_validation_section',
			),
			2010 => array(
				'title' => __( 'Enable?', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Yes, enable live inline validation', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_checkopt_pro_settings_preview_enable_inline_validation',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#inline-validation" target="_blank">Why?</a>',
			),
			2990 => array(
				'type' => 'sectionend',
				'id' => 'cartimize_checkopt_live_inline_validation_section'
			),
			//Inline Validation ends here

			//Shipping and billing address starts here
			3020 => array(
				'title' => __( 'Billing Address', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Disable billing address', 'woo-checkout-optimization-cartimize' ),
				'desc_tip' => __( 'If enabled, billing address fields will be removed from the checkout.', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_checkopt_pro_settings_preview_disable_billing_addr',
			),
			//Shipping and billing address ends here

			//Delayed account creation starts here
			4000 => array(
				'name'     => __( 'Delayed Account Creation', 'woo-checkout-optimization-cartimize' ),
				'type'     => 'title',
				'desc'     => __( 'Prompting users to create an account during the checkout flow is a potential obstacle on their road to complete their purchase. With Delayed Account Creation, users won’t have to evaluate or make up their mind about whether or not they should create an account – they can move directly to finalizing their purchase.', 'woo-checkout-optimization-cartimize' ),
				'id'       => 'cartimize_checkopt_delayed_account_creation_section'
			),
			4010 => array(
				'title' => __( 'Enable?', 'woo-checkout-optimization-cartimize' ),
				'type' => 'checkbox',
				'desc' => __( 'Yes, enable delayed account creation', 'woo-checkout-optimization-cartimize' ),
				'desc_tip' => __( 'If enabled, account creation options will be removed from the checkout flow and users can create an account after completing the purchase.<br>This will override the <code>Allow customers to create an account during checkout</code> setting under WooCommerce → Settings → Accounts &amp; Privacy → Account creation.', 'woo-checkout-optimization-cartimize' ),
				'id'   => 'cartimize_checkopt_pro_settings_preview_enable_delayed_account_after_order',
				'short_tip' => '<a href="https://cartimize.com/teardowns/the-pitfalls-of-woocommerce-checkout/?utm_source=checkopt_plugin#delayed-account-creation" target="_blank">Why?</a>',
			),
			4990 => array(
				'type' => 'sectionend',
				'id' => 'cartimize_checkopt_delayed_account_creation_section'
			),
			//Delayed account creation ends here

		);

		foreach($pro_preview_settings as $key => $value){
			$pro_preview_settings[$key]['is_pro_preview'] = true;
			if( in_array($value['type'], array('text', 'password', 'textarea', 'radio', 'checkbox'), true) ){
				$pro_preview_settings[$key]['custom_attributes'] = array('disabled' => 'disabled');
			}
		}
		return $pro_preview_settings;
	}
}
