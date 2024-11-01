<?php
/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>

<h3><?php esc_html_e( 'Billing details', 'woocommerce' ); ?></h3>

<div id="cartimize_bill_to_different_address_cont">
	<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
		<input id="cartimize_bill_to_different_address_checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( 'yes' === get_option( 'cartimize_setting_checkopt_set_shipping_address_as_billing_address_default' ) ? 1 : 0  ); ?> type="checkbox" name="cartimize_bill_to_different_address" value="1" /> <span><?php esc_html_e( 'Use my shipping details', 'woocommerce' ); ?></span>
	</label>
	<?php //<input type="hidden" name="ship_to_different_address" value="1"> ?>
	<h3 id="ship-to-different-address" style="display:none !important;"><?php //this block code require for WooCom to process as expected ?>
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
			<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" checked="checked" type="checkbox" name="ship_to_different_address" value="1" />
			<span><?php esc_html_e( 'Ship to a different address?', 'woocommerce' ); ?></span>
		</label>
	</h3>
</div>


		
<div class="woocommerce-billing-fields">

	<div class="cartimize_billing_address_cont" style="<?php echo ( 'yes' === get_option( 'cartimize_setting_checkopt_set_shipping_address_as_billing_address_default' ) ) ? 'display:none;' : ''; ?>">
		<?php /*<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

			<h3><?php esc_html_e( 'Billing &amp; Shipping', 'woocommerce' ); ?></h3>

		<?php else : ?>

			<h3><?php esc_html_e( 'Billing details', 'woocommerce' ); ?></h3>

		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>*/ ?>	

		<div class="woocommerce-billing-fields__field-wrapper">
			<?php
				$fields = $checkout->get_checkout_fields( 'billing' );

				foreach ( $fields as $key => $field ) {
					if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) && !isset( $field['country'] ) ) {//for v3.5.0 compactibility
						$field['country'] = $checkout->get_value( $field['country_field'] );
					}
					if( $key !== 'billing_phone' && $key !== 'billing_email' ){
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
				}
			?>
		</div>

		<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
	</div>
</div>

<?php include(CARTIMIZE_CHECKOPT_PATH.'/templates/woocommerce/checkout/form-additional-fields.php'); ?>