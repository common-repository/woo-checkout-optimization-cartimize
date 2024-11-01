/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

jQuery( function( $ ) {

	$('#cartimize_setting_checkopt_add_desc_for_billing_phone').on('change', function(e, is_initial){
		var disable = true;
		if( $(this).is(":checked") ){
			disable = false;
		}
		$('#cartimize_setting_checkopt_billing_phone_desc').attr('disabled', disable);
		if( !is_initial ){
			$('#cartimize_setting_checkopt_billing_phone_desc').focus();//focus() will not work when disabled.
		}
		$('#cartimize_setting_checkopt_billing_phone_desc').next('.description').toggleClass('disabled_text', disable);
	});

	$('#cartimize_setting_checkopt_enable_shipping_first').on('change', function(e, is_initial){
		var disable = true;
		var checked = $('#cartimize_setting_checkopt_set_shipping_address_as_billing_address_default').is(":checked");
		if( $(this).is(":checked") ){
			disable = false;
			if( !is_initial ){
				checked = true;
			}
		}
		$('#cartimize_setting_checkopt_set_shipping_address_as_billing_address_default').attr('disabled', disable).attr('checked', checked);
		$('#cartimize_setting_checkopt_set_shipping_address_as_billing_address_default').parent('label').toggleClass('disabled_text', disable);
	});

	$('#cartimize_setting_checkopt_single_full_name').trigger('change');
	$('#cartimize_setting_checkopt_add_desc_for_billing_phone').trigger('change', [true]);
	$('#cartimize_setting_checkopt_enable_shipping_first').trigger('change', [true]);



	//service auth - related code -- starts here

	$('#cartimize_service_email,#cartimize_service_password').keypress(function (e) {
		if (e.which == 13) {
			jQuery( "#cartimize_service_login_btn" ).click();
			return false;
		}
	});

	$('#cartimize_service_login_btn').on('click', function(){
		cartimize_service_login();
	});
	async function cartimize_service_login(){
		var this_element = '#cartimize_service_login_btn';
		var result_element = '#cartimize_service_login_btn_result';
	
		var email = jQuery('#cartimize_service_email').val();
		var password = jQuery('#cartimize_service_password').val();
		//connect_str = connect_str.trim();
	
		// if(typeof connect_str === 'undefined' || connect_str == ''){
		// 	cartimize_show_result(result_element, 'error', 'Invalid input.');
		// 	return false;
		// }
	
		var request = {};
		var response = {};
		request.url = cartimize_checkopt_ajax.ajax_url;
		request.method = 'POST';
		request.data = {
			action: 'cartimize_process_ajax_request',
			cartimize_action: 'service_login',
			email: email,
			password: password
		};

		jQuery(result_element).html('');

		jQuery(this_element).addClass('loading');
		jQuery(this_element).data('default_value', jQuery(this_element).val() );
		jQuery(this_element).val('Logging you in...');
		await cartimize_do_http_call(request, response);
		jQuery(this_element).val( jQuery(this_element).data('default_value') );
		jQuery(this_element).removeClass('loading');
	
		if(response.http_is_success){
			response_data = cartimize_clean_and_parse_json_response(response.http_data);
			if(response_data === 'JSON_PARSE_ERROR'){
				cartimize_show_result(result_element, 'error', 'Invalid response received.');
				return false;
			}
			if(response_data.hasOwnProperty('status')){
				if(response_data.status === 'success'){
	
					// var result_html = '<div class="success-box">Sucess! Redirecting...</div>';
					// jQuery(result_element).html(result_html);
	
					//redirect to main page
					if(typeof cartimize_redirect_after_login != 'undefined'){
						jQuery(this_element).val('Success! Redirecting...');
						setTimeout(function() { location.assign(cartimize_redirect_after_login); }, 10);
					}
					return true;
				}
				if(response_data.status === 'error'){
					cartimize_show_result(result_element, 'error', response_data.error_msg);
					//alert('Error:' + response_data.error_msg);
					return true;
				}
			}
		}
		else{
			cartimize_show_result(result_element, 'error', 'HTTP call failed.');
			//alert('HTTP call failed.');
		}
	}
	////service auth - related code -- end here

	$('#cartimize_toggle_pro_preview_settings').click(function(){
		if( $(this).is(":checked") ){
			$('.cartimize_checkopt_settings_page .cartimize_pro_preview_toggle').show();
		}
		else{
			$('.cartimize_checkopt_settings_page .cartimize_pro_preview_toggle').hide();
		}
	});

	var $sticky_block = $('.cartimize_checkopt_settings_page .buy_pro_right_side_cont');
	if( $sticky_block.length ){
	
		var sticky_block_top = $sticky_block.offset().top - parseFloat($sticky_block.css('marginTop').replace(/auto/, 0)) - 30;

		$(window).scroll(function (event) {
			// what the y position of the scroll is
			var y = $(this).scrollTop();

			// whether that's below the form
			if (y >= sticky_block_top) {
				// if so, ad the fixed class
				$sticky_block.addClass('fixed');
			} else {
				// otherwise remove it
				$sticky_block.removeClass('fixed');
			}
		});
	}

	//notice related starts here
	$('.cartimize_admin_notice.is-dismissible').on('click', '.notice-dismiss', async function(){//to save notice as dismissed
		var $notice_parent = $(this).closest('.cartimize_admin_notice.is-dismissible');
		var notice_slug = $notice_parent.data('notice-slug');
		if( !notice_slug ){
			return;
		}

		var request = {};
		var response = {};
		request.url = cartimize_checkopt_ajax.ajax_url;
		request.method = 'POST';
		request.data = {
			action: 'cartimize_process_ajax_request',
			cartimize_action: 'dismiss_notice',
			notice_slug: notice_slug
		};

		await cartimize_do_http_call(request, response);

	});

	$('.cartimize_admin_notice.is-dismissible').on('focus', '#cartimize_email_optin', async function(){
		$(this).select();
	});

	$('.cartimize_admin_notice.is-dismissible').on('click', '.process_notice_form', async function(){
		var $notice_parent = $(this).closest('.cartimize_admin_notice.is-dismissible');
		var notice_slug = $notice_parent.data('notice-slug');
		var email = $notice_parent.find('#cartimize_email_optin').val();
		if( !email ){
			return;
		}

		result_element = $notice_parent.find('.result_cont');

		var request = {};
		var response = {};
		request.url = cartimize_checkopt_ajax.ajax_url;
		request.method = 'POST';
		request.data = {
			action: 'cartimize_process_ajax_request',
			cartimize_action: 'process_notice_form',
			notice_slug: notice_slug,
			email: email
		};

		$(this).val('Adding you...');
		$(this).prop('disabled', true);
		await cartimize_do_http_call(request, response);
		$(this).val('Become an Insider');
		$(this).prop('disabled', false);

		if(response.http_is_success){
			response_data = cartimize_clean_and_parse_json_response(response.http_data);
			if(response_data === 'JSON_PARSE_ERROR'){
				cartimize_show_result(result_element, 'error', 'Invalid response received.');
				return false;
			}
			if(response_data.hasOwnProperty('status')){
				if(response_data.status === 'success'){	
					cartimize_show_result(result_element, 'success', response_data.message);
					$(this).hide();
					$notice_parent.find('#cartimize_email_optin').hide();
					setTimeout(function(){ $notice_parent.fadeOut(); }, 3000 );
					return true;
				}
				if(response_data.status === 'error'){
					cartimize_show_result(result_element, 'error', response_data.error_msg);
					return true;
				}
			}
		}
		else{
			cartimize_show_result(result_element, 'error', 'HTTP call failed.');
		}

	});
	//notice related ends here
});