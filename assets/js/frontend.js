/**
 * WooCommerce Checkout Optimization by Cartimize
 * Copyright (c) 2019 Revmakx LLC
 * revmakx.com
 */

jQuery( function( $ ) {

	$('.cartimize_checkopt_company_name_hide #shipping_company, .cartimize_checkopt_company_name_hide #billing_company, .cartimize_checkopt_address_2_hide #shipping_address_2, .cartimize_checkopt_address_2_hide #billing_address_2, .cartimize_get_city_state_from_postcode_hide #shipping_city, .cartimize_get_city_state_from_postcode_hide #shipping_state, .cartimize_get_city_state_from_postcode_hide #billing_city, .cartimize_get_city_state_from_postcode_hide #billing_state').each(function(){
		$(this).attr('tabindex', -1);//to avoid tabbing hidden(not display:none, using absolute) element
	});

	$('.cartimize_company_name_show_cont .show_company_name').on('click', function(){
		//this not used because this class used in 2 places, both should toggle.
		$field_main_cont = $(this).closest('.cartimize_company_name_show_cont').next('.cartimize_checkopt_company_name_hide');

		$field_main_cont.removeClass('cartimize_checkopt_company_name_hide');

		$(this).closest('.cartimize_company_name_show_cont').hide();

		var main_cont_id = $field_main_cont.attr('id');
		var field_id = main_cont_id.replace('_field', '');
		if( field_id === 'shipping_company' || field_id === 'billing_company' ){
			$('#' + field_id).removeAttr('tabindex');
			$('#' + field_id).focus();
		}
	});

	$('.cartimize_address_2_show_cont .show_address_2').on('click', function(){
		$field_main_cont = 
		$(this).closest('.cartimize_address_2_show_cont').next('.cartimize_checkopt_address_2_hide');

		$field_main_cont.removeClass('cartimize_checkopt_address_2_hide');

		$(this).closest('.cartimize_address_2_show_cont').hide();
		$(this).closest('.cartimize_address_2_show_cont').parent().find('.cartimize_checkopt_hide_placeholder').removeClass('cartimize_checkopt_hide_placeholder');

		var main_cont_id = $field_main_cont.attr('id');
		var field_id = main_cont_id.replace('_field', '');
		if( field_id === 'shipping_address_2' || field_id === 'billing_address_2' ){
			$('#' + field_id).removeAttr('tabindex');
			$('#' + field_id).focus();
		}
	});

	//remove cartimize_checkopt_hide_placeholder if no hide address_2 behind link
	if( $('.cartimize_checkopt_hide_placeholder').length ){
		$('.cartimize_checkopt_hide_placeholder').each(function(){
			var $wrapper = $(this).closest('.woocommerce-shipping-fields__field-wrapper, .woocommerce-billing-fields__field-wrapper');
			if( !$wrapper.find('.cartimize_checkopt_address_2_hide').length ){
				$(this).removeClass('cartimize_checkopt_hide_placeholder');
			}
		});
	}

	$('.cartimize_order_comments_show_cont .show_order_comments').on('click', function(){
		$('.cartimize_order_comments_show_cont').next('.cartimize_checkopt_order_comments_hide').removeClass('cartimize_checkopt_order_comments_hide');
		$('.cartimize_order_comments_show_cont').hide();
		$('#order_comments').focus();
	});

	$('.cartimize_checkout_coupon_toggle .show_coupon').on('click', function(){
		$('.checkout_coupon.woocommerce-form-coupon').show();
		$(this).hide();
		$('.checkout_coupon.woocommerce-form-coupon #coupon_code').focus();
	});

	$('.cartimize_show_delayed_account_register_form').on('click', function(){
		$('.cartimize_delayed_account_register_form').toggle();

		//set focus on password field if exists
		if( $('.cartimize_delayed_account_register_form #reg_password').length && $('.cartimize_delayed_account_register_form').is(':visible') ){
			$('.cartimize_delayed_account_register_form #reg_password').focus();
		}
	});

	$('#cartimize_bill_to_different_address_checkbox').on('change', function(){
		if( $('#cartimize_bill_to_different_address_checkbox').is(":checked") ){
			$('.cartimize_billing_address_cont').hide();
		}
		else{
			$('.cartimize_billing_address_cont').show();
		}
		$( document.body ).trigger( 'country_to_state_changed');//to change the select box to select2 style. Only visible select box is changed to select2
		$( document.body ).trigger( 'update_checkout' );
	});

	//relocate error msg container
	function cartimize_checkopt_relocate_error_msg_cont(){
		$( '.cartimize_inline_validation form.checkout .form-row .cartimize_form_field_error' ).each( function(){
			var $input_elements = 
			$(this).closest('.form-row').find('.woocommerce-input-wrapper').find('.input-text, select, input:checkbox').filter(':visible');
			if( !$input_elements.length ){
				return true;//to continue
			}
			var $input_element = $input_elements.first();
			if( $input_element.prop('nodeName') == 'SELECT' && $input_element.hasClass('select2-hidden-accessible') ){
				if( $input_element.next('.select2').length ){
					$input_element = $input_element.next('.select2').first();
				}
				else{//may be not generated
					$input_element.closest('.woocommerce-input-wrapper').append( $(this).detach() );
					return true;//to continue
				}
			}
			//$(this).detach().appendTo($input_element);
			$(this).detach().insertAfter($input_element);
		});
	}
	cartimize_checkopt_relocate_error_msg_cont();

	var country_postcode_tips = {
		'AT': '1010, 9992',
		'BR': '01000-000, 99990-000',
		'CH': '1000, 9658',
		'DE': '01067, 99998',
		'ES': '01001, 52080',
		'FR': '01000, 98799',
		'GB': 'AB10 1AB, WC2N 5HS',
		'IE': 'D02 AF30',
		'JP': '100-0001, 999-8531',
		'PT': '1000-001, 9980-999',
		'US': '00210, 99950-5019',
		'CA': '‎A0A 1C0, K1A 0B1',
		'PL': '00-001, 99-440',
		'CZ': '100 00, 798 62',
		'SK': '010 01, 992 01'
	};

	$(document).bind('cartimize_populate_postcode_tip', function(){
		$postcode_fields = $('#shipping_postcode.cartimize_get_city_state_from_postcode,#billing_postcode.cartimize_get_city_state_from_postcode');

		if( $postcode_fields.length === 0 ){
			return;
		}

		$postcode_fields.each(function(){
			var fieldset;
			if($(this).attr('id') === 'shipping_postcode'){
				fieldset = 'shipping';
			}
			else if($(this).attr('id') === 'billing_postcode'){
				fieldset = 'billing';
			}
			else{
				return true;//to continue
			}

			$field_main_cont = $(this).closest('.form-row', 'form.checkout');
			$field_main_cont.find('label:first').find('.cartimize_show_on_focus').remove();

			//get country abbreviation
			var country_code = $( '#' + fieldset + '_country' ).val();
			if( country_postcode_tips.hasOwnProperty(country_code) ){
				var postcode_tip = 'Ex: ' + country_postcode_tips[country_code];//language needs to fixed
				
				$field_main_cont.find('label:first').append('<span class="cartimize_show_on_focus" style="display:none;">'+postcode_tip+'</span>');
			}
		})
	});

	setTimeout(() => {
		$(document).trigger('cartimize_populate_postcode_tip');
	}, 200);
	

	$( 'form.checkout' ).on('focusin', '#shipping_postcode.cartimize_get_city_state_from_postcode,#billing_postcode.cartimize_get_city_state_from_postcode', function(){
		$field_main_cont = $(this).closest('.form-row', 'form.checkout');
		$field_main_cont.find('.cartimize_show_on_focus').show();
	});
	$( 'form.checkout' ).on('focusout', '#shipping_postcode.cartimize_get_city_state_from_postcode,#billing_postcode.cartimize_get_city_state_from_postcode', function(){
		$field_main_cont = $(this).closest('.form-row', 'form.checkout');
		$field_main_cont.find('.cartimize_show_on_focus').hide();
	});


	//get city and state from the postcode and country through API
	var cache_postcode_details = {};
	var postcode_api_last_call = '';
 
	var timeout_postcode_change, timeout_postcode_country_change;

	$('#shipping_postcode.cartimize_get_city_state_from_postcode,#billing_postcode.cartimize_get_city_state_from_postcode').each(function(){
		if($(this).attr('id') === 'shipping_postcode'){
			fieldset = 'shipping';
		}
		else if($(this).attr('id') === 'billing_postcode'){
			fieldset = 'billing';
		}
		else{
			return;
		}

		var $this = $(this); 

		$('#' + fieldset + '_country').on('change', function(){ 
			clearTimeout(timeout_postcode_country_change);
			timeout_postcode_country_change = setTimeout( function(){ get_city_state_from_postcode($this, true) }, 100); 
		});
	});

	$('#shipping_postcode.cartimize_get_city_state_from_postcode,#billing_postcode.cartimize_get_city_state_from_postcode').on('keyup change', function(e){
		var $this = $(this);
		var timeout = 100;
		if( e.type == 'keyup' ){ timeout = 500; }
		clearTimeout(timeout_postcode_change);
		timeout_postcode_change = setTimeout( function(){ get_city_state_from_postcode($this) }, timeout);
	});
	
	function get_city_state_from_postcode($this, stop_after_country_check=false){
		var fieldset = '';
		if($this.attr('id') === 'shipping_postcode'){
			fieldset = 'shipping';
		}
		else if($this.attr('id') === 'billing_postcode'){
			fieldset = 'billing';
		}
		else{
			return;
		}

		if( $.fn.autocomplete && $('#' + fieldset + '_city').autocomplete( 'instance' ) ) {//$.fn.autocomplete currently in free plugin jqueryui will not be loaded
			$('#' + fieldset + '_city').autocomplete( 'destroy' );
		} 

		var after_get_city_state_api_call = function(){
			//if not value set city or state, set focus to it, if hidden on tab focus will go to someother field.
			var focus_set = false;
			var $focus_element = '';
			var focus_change_required = false;
			if( $('.woocommerce-'+fieldset+'-fields__field-wrapper').find('#'+fieldset+'_city:focus,#'+fieldset+'_state:focus,#'+fieldset+'_postcode:focus').length === 0 ){
				focus_change_required = true;
			}
			
			$('.woocommerce-'+fieldset+'-fields__field-wrapper .cartimize_get_city_state_from_postcode_hide').find('#'+fieldset+'_city,#'+fieldset+'_state').each(function(){

				$(this).removeAttr('tabindex');

				if( focus_change_required && !focus_set && !$this.val()){
					focus_set = true;
					$focus_element =  $this;
				}

			});


			//remove hide class, city and state once showed will be remain visible
			$('.woocommerce-'+fieldset+'-fields__field-wrapper .cartimize_get_city_state_from_postcode_hide').removeClass('cartimize_get_city_state_from_postcode_hide');

			$( '.woocommerce-'+fieldset+'-fields__field-wrapper .cartimize_checkopt_city_state_initial_text' ).remove();

			//to use select2
			$( document.body ).trigger( 'country_to_state_changed');//to change the select box to select2 style. Only visible select box is changed to select2

			if( $focus_element ){
				skip_validation = true;
				$focus_element.trigger('focus');
			}
		}

		try{
			if( 
				$this.filter(':-webkit-autofill').length && 
				( 
					$( '#' + fieldset + '_city').filter(':-webkit-autofill').length || 
					$( '#' + fieldset + '_state').filter('-webkit-autofill').length 
				)
			){
				after_get_city_state_api_call();
				return;
			}
		}
		catch(error){//try...catch fix for autofill selector for non webkit browser
		}

		//get country abbreviation
		var country_code = $( '#' + fieldset + '_country' ).val();

		if( !country_code ){
			return;
		}

		if( typeof cartimize_checkopt_data !== 'undefined' && cartimize_checkopt_data.hasOwnProperty('get_city_state_from_postcode_supported_countries') ){
			var is_country_supported = cartimize_checkopt_data.get_city_state_from_postcode_supported_countries.indexOf(country_code) !== -1;

			if( !is_country_supported ){
				after_get_city_state_api_call();
				return;
			}
		}

		if( stop_after_country_check ){//for on country change support
			return;
		}

		var postcode, postcode_full;
		postcode = $this.val();
		postcode_full = postcode = postcode.trim();

		if(postcode.length < 3){
			return;
		}

		var is_postcode_valid = is_postcode( postcode, country_code );
		if( !is_postcode_valid ){
			cartimize_checkopt_fill_state_city(fieldset, {});
			after_get_city_state_api_call();
			return;
		}

		// following commented to support local data used for GB
		// if( country_code === 'GB' ){
		// 	var postcode_parts = postcode.split(' ');
		// 	postcode = postcode_parts[0];
		// }
		// else 
		if( country_code === 'US' ){
			var postcode_parts = postcode.split('-');
			postcode = postcode_parts[0];
		}
		else if( country_code === 'CA' ){
			//example K1A 0B1, where space inbetween is optionl according to WooCom PHP validation for canada
			if( postcode.indexOf(' ') !== -1 ){//if space is found
				var postcode_parts = postcode.split(' ');
				postcode = postcode_parts[0];
			}
			else{
				postcode = postcode.substring(0, 3);
			}
		}

		//check cache
		if( cache_postcode_details.hasOwnProperty(country_code + '__' + postcode) ){
			places = cache_postcode_details[country_code + '__' + postcode];
			cartimize_checkopt_fill_or_autocomplete_state_city(fieldset, places);
			after_get_city_state_api_call();
			return;
		}

		if( postcode_api_last_call === country_code + '__' + postcode){//to avoid to consective calls trigger on change(focus still in textbox) and on blur kind of (focus goes to next input) even though only change event is binded.
			return;
		}
		postcode_api_last_call = country_code + '__' + postcode;

		var $field_cont = $this.parent();
		$field_cont.append('<div class="cartimize_loading"></div>');
		$( '.woocommerce-'+fieldset+'-fields__field-wrapper .cartimize_checkopt_city_state_initial_text' ).remove();//remove this tip when loading displays

		var api_url = 'https://api.zippopotam.us/' + country_code + '/' + postcode;
		var api_method = 'GET';
		var api_data_type = 'json';
		var api_data = '';

		if( country_code === 'GB' ){
			api_url = cartimize_checkopt_ajax.ajax_url;
			api_method = 'POST';
			api_data_type = 'text';
			api_data = {
				action: 'cartimize_frontend_process_ajax_request',
				cartimize_action: 'get_city_state_from_postcode',
				country_code: country_code,
				postcode: postcode
			};
		}

		$.ajax({
			url: api_url,
			cache: true,
			dataType: api_data_type,
			type: api_method,
			data: api_data,
			success: function(result, success) {
				if( country_code === 'GB' ){
					result = cartimize_clean_and_parse_json_response(result);
					if(result === 'JSON_PARSE_ERROR'){
						cartimize_checkopt_fill_state_city(fieldset, {});
						return false;
					}
				}
				if( !result.hasOwnProperty('places') || typeof result['places'][0] !== 'object' ){
					cache_postcode_details[country_code + '__' + postcode] = {};
					cartimize_checkopt_fill_state_city(fieldset, {});
					return ;
				}

				if( country_code === 'JP' ){
					result = cartimize_fix_jp_state_abbr_zippo(result);
				}
				else if( country_code === 'BR' ){
					result = cartimize_fix_br_state_abbr_zippo(result);
				}

				cartimize_checkopt_fill_or_autocomplete_state_city(fieldset, result['places']);
				cache_postcode_details[country_code + '__' + postcode] = result['places'];
			},
			error: function() {
				cache_postcode_details[country_code + '__' + postcode] = {};
				cartimize_checkopt_fill_state_city(fieldset, {});
			},
			complete: function(){
				after_get_city_state_api_call();
				$field_cont.find('.cartimize_loading').remove();
			}
		});
	};

	function cartimize_checkopt_fill_or_autocomplete_state_city(fieldset, places){
		if( !Array.isArray(places) ){
			return;
		}
		if( places.length > 1 ){
			cartimize_checkopt_autocomplete_state_city(fieldset, places);
		}
		else{
			place_details = places[0];
			cartimize_checkopt_fill_state_city(fieldset, place_details);
		}
	}

	function cartimize_checkopt_fill_state_city(fieldset, place_details){
		$( '#' + fieldset + '_city' ).val(place_details['place name']);

		if( $( '#' + fieldset + '_state' ).is("select") ){
			$( '#' + fieldset + '_state' ).val(place_details['state abbreviation']);
		}
		else{
			$( '#' + fieldset + '_state' ).val(place_details['state']);
		}

		var post_action = 'triggerHandler';//triggerHandler is used to avoid, validation error if empty
		if( $( '#' + fieldset + '_city' ).val() ){
			post_action = 'trigger';
			$( '#' + fieldset + '_city' )[post_action]('change');
		}

		post_action = 'triggerHandler';
		if( $( '#' + fieldset + '_state' ).val() ){
			post_action = 'trigger';
			$( '#' + fieldset + '_state' )[post_action]('change');
		}
	}

	function cartimize_checkopt_autocomplete_state_city(fieldset, places){//when multiple option of city and state for a entered postcode
		var source = [];

		places.forEach(function(place_details, index){
			if( place_details.hasOwnProperty('place name') && place_details.hasOwnProperty('state') ){
				source.push( { label: place_details['place name'] + ':' + place_details['state'], value: place_details } );
			}
		});

		$( '#' + fieldset + '_city' ).closest('.form-row').addClass('cartimize_jqui');

		$( '#' + fieldset + '_city' ).on('focus click', function() {
			if( $('#' + fieldset + '_city').autocomplete( 'instance' ) ){
				$(this).autocomplete( "search", "" );
			}
		});

		$( '#' + fieldset + '_city' ).autocomplete({
			delay: 50,
			minLength: 0,
			appendTo: $( '#' + fieldset + '_city' ).closest('.form-row'),//where to inject autocomplete list html
			source: source,
			select: function( event, ui ) {
				event.preventDefault();
				place_details = ui.item.value;
				cartimize_checkopt_fill_state_city(fieldset, place_details);
			}
		});

		setTimeout( function(){ $( '#' + fieldset + '_city' ).focus(); }, 100);//fix for when city state hidden and as soon as typing postcode and tabbing
	}

	function cartimize_fix_jp_state_abbr_zippo(result){
		//zippopotam.us currently not sending "state abbreviation" for Japan, fixing it
		//call only for japan

		var japan_states = {"Hokkaidou": {"en_state": "Hokkaido","state_ab": "JP01"},"Aomoriken": {"en_state": "Aomori","state_ab": "JP02"},"Iwateken": {"en_state": "Iwate","state_ab": "JP03"},"Miyagiken": {"en_state": "Miyagi","state_ab": "JP04"},"Akitaken": {"en_state": "Akita","state_ab": "JP05"},"Yamagataken": {"en_state": "Yamagata","state_ab": "JP06"},"Fukushimaken": {"en_state": "Fukushima","state_ab": "JP07"},"Ibarakiken": {"en_state": "Ibaraki","state_ab": "JP08"},"Tochigiken": {"en_state": "Tochigi","state_ab": "JP09"},"Gunmaken": {"en_state": "Gunma","state_ab": "JP10"},"Saitamaken": {"en_state": "Saitama","state_ab": "JP11"},"chibaken": {"en_state": "Chiba","state_ab": "JP12"},"Toukyouto": {"en_state": "Tokyo","state_ab": "JP13"},"kanagawaken": {"en_state": "Kanagawa","state_ab": "JP14"},"Niigataken": {"en_state": "Niigata","state_ab": "JP15"},"Toyamaken": {"en_state": "Toyama","state_ab": "JP16"},"Ishikawaken": {"en_state": "Ishikawa","state_ab": "JP17"},"Fukuiken": {"en_state": "Fukui","state_ab": "JP18"},"Yamanashiken": {"en_state": "Yamanashi","state_ab": "JP19"},"Naganoken": {"en_state": "Nagano","state_ab": "JP20"},"Gifuken": {"en_state": "Gifu","state_ab": "JP21"},"Shizuokaken": {"en_state": "Shizuoka","state_ab": "JP22"},"Aichiken": {"en_state": "Aichi","state_ab": "JP23"},"Mieken": {"en_state": "Mie","state_ab": "JP24"},"Shigaken": {"en_state": "Shiga","state_ab": "JP25"},"Kyoutofu": {"en_state": "Kyoto","state_ab": "JP26"},"Oosakafu": {"en_state": "Osaka","state_ab": "JP27"},"Hyougoken": {"en_state": "Hyogo","state_ab": "JP28"},"Naraken": {"en_state": "Nara","state_ab": "JP29"},"Wakayamaken": {"en_state": "Wakayama","state_ab": "JP30"},"Tottoriken": {"en_state": "Tottori","state_ab": "JP31"},"Shimaneken": {"en_state": "Shimane","state_ab": "JP32"},"Okayamaken": {"en_state": "Okayama","state_ab": "JP33"},"Hiroshimaken": {"en_state": "Hiroshima","state_ab": "JP34"},"Yamaguchiken": {"en_state": "Yamaguchi","state_ab": "JP35"},"Tokushimaken": {"en_state": "Tokushima","state_ab": "JP36"},"Kagawaken": {"en_state": "Kagawa","state_ab": "JP37"},"Ehimeken": {"en_state": "Ehime","state_ab": "JP38"},"Kouchiken": {"en_state": "Kochi","state_ab": "JP39"},"Fukuokaken": {"en_state": "Fukuoka","state_ab": "JP40"},"Sagaken": {"en_state": "Saga","state_ab": "JP41"},"Nagasakiken": {"en_state": "Nagasaki","state_ab": "JP42"},"Kumamotoken": {"en_state": "Kumamoto","state_ab": "JP43"},"Ooitaken": {"en_state": "Oita","state_ab": "JP44"},"Miyazakiken": {"en_state": "Miyazaki","state_ab": "JP45"},"Kagoshimaken": {"en_state": "Kagoshima","state_ab": "JP46"},"Okinawaken": {"en_state": "Okinawa","state_ab": "JP47"}};

		if( !result.hasOwnProperty('places') ){
			return result;
		}

		if( !Array.isArray(result.places) ){
			return result;
		}

		result.places.forEach(function(value, index){
			if( value.hasOwnProperty('state') && value.hasOwnProperty('state abbreviation') &&  japan_states.hasOwnProperty(value.state) ){
				result.places[index]['state abbreviation'] =  japan_states[value.state]['state_ab'];
			}
		});
		
		return result;
	}

	function cartimize_fix_br_state_abbr_zippo(result){
		//zippopotam.us currently sending "state abbreviation" in number for Brazil, WooCom used 2 char Alphabet fixing it
		//call only for Brazil

		var brazil_states = {
			'01':'AC',//Acre
			'02':'AL',//Alagoas
			'03':'AP',//Amapá
			'04':'AM',//Amazonas
			'05':'BA',//Bahia
			'06':'CE',//Ceará
			'07':'DF',//Distrito Federal
			'08':'ES',//Espírito Santo
			'29':'GO',//Goiás
			'13':'MA',//Maranhão
			'14':'MT',//Mato Grosso
			'11':'MS',//Mato Grosso do Sul
			'15':'MG',//Minas Gerais
			'16':'PA',//Pará
			'17':'PB',//Paraíba
			'18':'PR',//Paraná
			'30':'PE',//Pernambuco
			'20':'PI',//Piauí
			'21':'RJ',//Rio de Janeiro
			'22':'RN',//Rio Grande do Norte
			'23':'RS',//Rio Grande do Sul
			'24':'RO',//Rondônia
			'25':'RR',//Roraima
			'26':'SC',//Santa Catarina
			'27':'SP',//São Paulo
			'28':'SE',//Sergipe
			'31':'TO'//Tocantins
			};

		if( !result.hasOwnProperty('places') ){
			return result;
		}

		if( !Array.isArray(result.places) ){
			return result;
		}

		result.places.forEach(function(value, index){
			if( value.hasOwnProperty('state abbreviation') && value['state abbreviation'] && brazil_states.hasOwnProperty(value['state abbreviation']) ){
				result.places[index]['state abbreviation'] =  brazil_states[value['state abbreviation']];
			}
		});
		
		return result;
	}

	function get_label_name_by_field_main_cont(field_main_selector){
		var label_name =  $(field_main_selector).find('label:first')
		.clone()    //clone the element
		.children() //select all the children
		.remove()   //remove all the children
		.end()  //again go back to selected element
		.text();

		label_name = label_name.replace('&nbps;', '').trim();
		return label_name;
	}

	//inline validation showing - change field name in error msg, after country change
	$( document.body ).on( 'country_to_state_changed', function(e, country, $wrapper){

		setTimeout(function(){

			$('#shipping_state,#billing_state').each(function(){//bug fixing, wc cache element properties, even after removing tabindex it is coming back, so fix here.
				if( !$(this).closest('.form-row').hasClass('cartimize_get_city_state_from_postcode_hide') ){
					$(this).removeAttr('tabindex');
					if( $(this).is('select') ){
						$(this).next('select2').find('.select2-selection').removeAttr('tabindex');
					}
				}
			})

			if ( typeof $wrapper === 'undefined' ){
				return false;
			}

			var fields = $wrapper.find( '#billing_city_field, #billing_state_field, #billing_postcode_field, #shipping_city_field, #shipping_state_field, #shipping_postcode_field' );

			if( fields.length === 0){
				return;
			}

			fields.each(function(){
				if( $(this).find('.cartimize_form_field_error').length === 1){

					var label_name = get_label_name_by_field_main_cont(this);

					var err_msg_format = $(this).find('.cartimize_form_field_error').data('cartimize-required-error-msg');
					var err_msg = err_msg_format.replace('%s', label_name);
					$(this).find('.cartimize_form_field_error').text(err_msg);

				}
			});

			$(document).trigger('cartimize_populate_postcode_tip');
			show_intial_text_for_city_state_when_hidden('', $wrapper);
		}, 100);
	});

	function show_intial_text_for_city_state_when_hidden(fieldset='', wrapper_selector=''){
		if( fieldset ){
			$wrapper = $('.woocommerce-'+fieldset+'-fields__field-wrapper');
		} else if ( wrapper_selector ){
			$wrapper = $(wrapper_selector);
		}
		$wrapper.find( '.cartimize_checkopt_city_state_initial_text' ).remove();
		
		if( $wrapper.find('.cartimize_get_city_state_from_postcode_hide').length === 0 ){
			return;
		}

		var fields = $wrapper.find( '#billing_city_field, #billing_state_field, #billing_postcode_field, #shipping_city_field, #shipping_state_field, #shipping_postcode_field' );

		if( fields.length === 0){
			return;
		}

		var field_label_name = {
			postcode: '',
			city: '',
			state: ''
		};
		var is_state_visible = true;

		fields.each(function(){
			var label_name = get_label_name_by_field_main_cont(this);

			var field_cont_id = $(this).attr('id');
			var field_for = field_cont_id.replace('billing_', '').replace('shipping_', '').replace('_field', '');

			var allowed_field_for = ['postcode', 'city', 'state'];
			if( allowed_field_for.indexOf(field_for) !== -1 ){
				field_label_name[field_for] = label_name;
			}
			if( field_for === 'state' && $(this).find('#billing_state, #shipping_state').attr('type') == 'hidden' ){					
				is_state_visible = false;
			}
		});

		var initial_text = 'Enter '+ field_label_name['postcode'] + ' for ' + field_label_name['city']; 
		if( field_label_name['state'] && is_state_visible ){
			initial_text += ' & ' + field_label_name['state'];
		}
		
		$wrapper.find( '#billing_postcode_field, #shipping_postcode_field' ).append('<span class="cartimize_checkopt_city_state_initial_text">' + initial_text + '</span>');
	}

	if( $('.cartimize_get_city_state_from_postcode').length > 0 ){
		$('.cartimize_get_city_state_from_postcode').closest('.form-row').css({'position' : 'relative'});
	}
	show_intial_text_for_city_state_when_hidden('billing');
	show_intial_text_for_city_state_when_hidden('shipping');

	function show_hidden_fields_when_auto_fill($this, $field_main_cont){
		var check_fields = [
			{
				'has_class' : 'cartimize_checkopt_address_2_hide',
				//'trigger_elem' : '.cartimize_address_2_show_cont .show_address_2',
				'hide_elem' : '.cartimize_company_name_show_cont'
			},
			{
				'has_class' : 'cartimize_checkopt_company_name_hide',
				//'trigger_elem' : '.cartimize_address_2_show_cont .show_address_2',
				'hide_elem' : '.cartimize_address_2_show_cont'
			}
		];
		for (let check_field of check_fields) {
			if( $field_main_cont.hasClass(check_field.has_class) ){
				if( $($this).val() ){
					$field_main_cont.removeClass(check_field.has_class);
					$field_main_cont.closest('.woocommerce-shipping-fields__field-wrapper, .woocommerce-billing-fields__field-wrapper').find(check_field.hide_elem).hide();
				}
			}
		}
		
	}

	//live & inline validate as soon form field blured
	var skip_validation = false;
	var $form = $( '.cartimize_inline_validation form.checkout' );
	$form.find( '.input-text, select, input:checkbox' ).on('blur', function(){
		//console.log($('input:-internal-autofill-selected').length);
		//console.log($('input:-webkit-autofill').length);

		if( skip_validation ){
			skip_validation = false;
			return;
		}
		if( $(this).closest('.form-row', 'form.checkout').find('.cartimize_form_field_error').length === 1 ){
			$(this).trigger('validate');

			var $field_main_cont = $(this).closest('.form-row', 'form.checkout');
			var $field_error_cont = $field_main_cont.find('.cartimize_form_field_error');
			var js_error_msg = $field_error_cont.data('js-error');
			if(js_error_msg){
				$field_error_cont.html(js_error_msg);
				$field_error_cont.removeClass('php_error').addClass('js_error');
			}

			if( $field_main_cont.hasClass('woocommerce-invalid-required-field') ){
				var label_name = get_label_name_by_field_main_cont($field_main_cont);

				var err_msg_format = $field_main_cont.find('.cartimize_form_field_error').data('cartimize-required-error-msg');
				var err_msg = err_msg_format.replace('%s', label_name);
				$field_main_cont.find('.cartimize_form_field_error').text(err_msg);
			}

			if( $field_main_cont.hasClass('woocommerce-invalid-email') ){
				var invalid_error_msg = $field_error_cont.data('cartimize-invalid-error-msg');

				var email_validation_response = cartimize_validate_email($(this).val());
				if( email_validation_response !== false ){
					invalid_error_msg = invalid_error_msg.replace(/\.$/, ' - ');
					invalid_error_msg += email_validation_response;
				}
				if( invalid_error_msg ){
					$field_error_cont.html(invalid_error_msg);
				}
			}

			if( $field_main_cont.hasClass('validate-postcode') && $(this).val() ){
				var invalid_error_msg = $field_error_cont.data('cartimize-invalid-error-msg');

				var fieldset = '';
				if($(this).attr('id') === 'shipping_postcode'){
					fieldset = 'shipping';
				}
				else if($(this).attr('id') === 'billing_postcode'){
					fieldset = 'billing';
				}

				if(fieldset){
				
					//get country abbreviation
					var country_code = $( '#' + fieldset + '_country' ).val();
					var postcode = $(this).val();
					postcode = postcode.trim();

					var is_postcode_valid = is_postcode( postcode, country_code );

					$field_main_cont.removeClass('woocommerce-invalid-required-field');

					if( is_postcode_valid === false ){

						var label_name = get_label_name_by_field_main_cont($field_main_cont);

						$field_main_cont.removeClass('woocommerce-validated');
						$field_main_cont.addClass('woocommerce-invalid woocommerce-invalid-required-field');
						//invalid_error_msg = invalid_error_msg.replace(/\.$/, ' ');
						//invalid_error_msg += email_validation_response;
						invalid_error_msg = invalid_error_msg.replace('%s', label_name);
						$field_error_cont.html(invalid_error_msg);
					}
				}
			}

			show_hidden_fields_when_auto_fill(this, $field_main_cont);


		}
	});

	/*
	 * @returns true if valid, error_msg if invalid
	 * email is already validated by woocommerce js, here we try to get better help msg
	 */
	function cartimize_validate_email(email){
		var index_of_at = email.indexOf("@");
		if ( index_of_at === -1 ){
			return '@ is missing.';
		}
		else if ( (index_of_at = email.indexOf(".", index_of_at) ) === -1 ){
			return '&lt;dot&gt; after @ is missing.';
		}
		else if ( !email.substr( (index_of_at + 1) ) ){
			return 'com, org etc. is missing.';
		}
		return false;//as already validated woocommerce js
	}

	$( '.cartimize_inline_validation form.checkout.woocommerce-checkout' ).on('click', 'a.cartimize_jump_to_field', function(){
		var field_id = $(this).data('field-id');
		$('#' + field_id).focus();
	});

	//on submit validate if error stop form submission.
	$( '.cartimize_inline_validation form.checkout' ).on('checkout_place_order', function(){
		$(this).find( '.input-text, select, input:checkbox' ).trigger( 'validate' ).blur();
		if( $('.cartimize_form_field_error').filter(':visible').length ){
			checkout_on_error_scroll_to_first_element();
			return false;
		}
		return true;
	});

	//move the php response error message from top box to inline error msg container
	$( document.body ).on( 'checkout_error', function () {
		var $form = $( '.cartimize_inline_validation form.checkout' );
		var $error_msgs = $form.find('.woocommerce-NoticeGroup.woocommerce-NoticeGroup-checkout .woocommerce-error .cartimize_jump_to_field');

		if( !$error_msgs.length ){
			return;
		}

		$error_msgs.each(function(){
			var field_id = $(this).data('field-id');
			var $field_main_cont = $('#' + field_id).closest('.form-row', 'form.checkout');
			var $field_error_cont = $field_main_cont.find('.cartimize_form_field_error');

			if( $field_error_cont.length !== 1 ){
				return true;//to continue
			}
			error_msg = $(this).text();
			var js_error_msg = $field_error_cont.text();
			$field_error_cont.html(error_msg);
			$field_error_cont.data('js-error', js_error_msg);
			$field_error_cont.removeClass('js_error').addClass('php_error');

			if( !$field_main_cont.hasClass('woocommerce-invalid') ){
				$field_main_cont.addClass('woocommerce-invalid');
			}


			$(this).closest('li', '.woocommerce-NoticeGroup.woocommerce-NoticeGroup-checkout').remove();
		});

		var $other_errors_exists = $form.find('.woocommerce-NoticeGroup.woocommerce-NoticeGroup-checkout .woocommerce-error li');

		if( !$other_errors_exists.length ){
			$form.find('.woocommerce-NoticeGroup.woocommerce-NoticeGroup-checkout .woocommerce-error').hide();

			//stop woocommerce scrollTop
			if ( $( 'body' ).hasClass( 'woocommerce-checkout' ) ) {
				$( 'html, body' ).stop();
			}
			checkout_on_error_scroll_to_first_element();
		}

	});

	function checkout_on_error_scroll_to_first_element(){
		scroll_to( $('.cartimize_form_field_error').filter(':visible'), function(){
			//to focus on first error element
			$field_main_cont = $('.cartimize_form_field_error').filter(':visible').closest('.form-row');
			var main_cont_id = $field_main_cont.attr('id');
			if( $field_main_cont.length ){
				var field_id = main_cont_id.replace('_field', '');
				$('#' + field_id).addClass('cartimize_input_text_highlight');
				$('#' + field_id).focus();
				setTimeout(function(){
					$('#' + field_id).removeClass('cartimize_input_text_highlight');
				}, 2100);
			}

		});
	}

	$('.cartimize_inline_validation form.checkout .cartimize_checkopt_validate_phone #billing_phone').on('keypress', function(event){

		var regex = /[\s\#0-9_\-\+\/\(\)\.]/gi;
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
			event.preventDefault();
			return false;
		}
	});

	$('.cartimize_inline_validation form.checkout .cartimize_checkopt_validate_phone #billing_phone').on('keyup blur change click mouseenter mouseleave', function(){
		var str = $(this).val();
		var res = str.replace(/[^\s\#0-9_\-\+\/\(\)\.]/gi, "");
		$(this).val(res);
	});

	$phone_with_descr_cont = $('form.checkout .cartimize_checkopt_phone_with_descr');
	if( $phone_with_descr_cont.find('.description').length === 1 ){
		var phone_descr = $phone_with_descr_cont.find('.description').text();
		$phone_with_descr_cont.find('label[for="billing_phone"]').append('<span class="cartimize_phone_descr_cont">&nbsp;&nbsp;'+phone_descr+'</span>');
		$phone_with_descr_cont.find('.description').remove();
	}

	$validate_phone_cont = $('.cartimize_inline_validation form.checkout .cartimize_checkopt_validate_phone');
	if( $validate_phone_cont.length === 1 ){
		var phone_allowed_char_tip = 'Allowed characters are 0-9 # / . + - _ ( ) (space)';//language needs to fixed
		$validate_phone_cont.find('label[for="billing_phone"]').append('<span class="cartimize_show_on_focus" style="display:none;">'+phone_allowed_char_tip+'</span>');

		$validate_phone_cont.on('focusin', '#billing_phone', function(){
			$validate_phone_cont.find('.cartimize_show_on_focus').show();
		});
		$validate_phone_cont.on('focusout', '#billing_phone', function(){
			$validate_phone_cont.find('.cartimize_show_on_focus').hide();
		});
	}

	var form_coupon_submitted = false;
	$( 'form.checkout_coupon.cartimize_checkout_coupon_form' ).on('submit', function(){
		$(this).prev('.woocommerce-error, .woocommerce-message').remove();
		form_coupon_submitted = true;
	});

	$( document.body ).on( 'update_checkout', function(){
		if( form_coupon_submitted ){
			$form_checkout = $( 'form.checkout_coupon.cartimize_checkout_coupon_form' );
			if( !$form_checkout.length ){
				return;
			}
			if( $form_checkout.prev('.woocommerce-error').length ){
				setTimeout( () => {$form_checkout.show(); }, 401);
			}
			else if( $form_checkout.prev('.woocommerce-message').length ){
				$('.cartimize_checkout_coupon_toggle .show_coupon').show();
			}
			form_coupon_submitted = false;
		}
	});

	//move coupon code form inside checkout form
	$('#cartimize_coupon_code_cont').appendTo('.woocommerce-additional-fields');

	/**
	 * This code is similar to woocommerce/includes/class-wc-validation.php 
	 * WC_Validation::is_postcode();
	 * WooCommerce v3.5.7
	 * /*following header from the file*
	 * 	* General user data validation methods
	 * 	*
	 * 	* @package WooCommerce\Classes
	 * 	* @version  2.4.0
	 * 	***
	 */
	function is_postcode( postcode, country ) {
		if ( postcode.replace(/[\s\-A-Za-z0-9]/gi, '').trim().length > 0 ) {
			return false;
		}

		var valid;
		var regex;

		switch ( country ) {
			case 'AT':
				regex = /^([0-9]{4})$/;
				valid = regex.test(postcode);
				break;
			case 'BR':
				regex = /^([0-9]{5})([-])?([0-9]{3})$/;
				valid = regex.test(postcode);
				break;
			case 'CH':
				regex = /^([0-9]{4})$/i;
				valid = regex.test(postcode);
				break;
			case 'DE':
				regex = /^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$/;
				valid = regex.test(postcode);
				break;
			case 'ES':
			case 'FR':
				regex = /^([0-9]{5})$/i;
				valid = regex.test(postcode);
				break;
			case 'GB':
				valid = is_gb_postcode( postcode );
				break;
			case 'IE':
				regex = /([AC-FHKNPRTV-Y]\d{2}|D6W)[0-9AC-FHKNPRTV-Y]{4}/;
				var tmp_postcode = normalize_postcode(postcode);
				valid = regex.test(tmp_postcode);
				break;
			case 'JP':
				regex = /^([0-9]{3})([-])([0-9]{4})$/;
				valid = regex.test(postcode);
				break;
			case 'PT':
				regex = /^([0-9]{4})([-])([0-9]{3})$/;
				valid = regex.test(postcode);
				break;
			case 'US':
				regex = /^([0-9]{5})(-[0-9]{4})?$/i;
				valid = regex.test(postcode);
				break;
			case 'CA':
				// CA Postal codes cannot contain D,F,I,O,Q,U and cannot start with W or Z. https://en.wikipedia.org/wiki/Postal_codes_in_Canada#Number_of_possible_postal_codes.
				regex = /^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])([\ ])?(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$/i;
				valid = regex.test(postcode);
				break;
			case 'PL':
				regex = /^([0-9]{2})([-])([0-9]{3})$/;
				valid = regex.test(postcode);
				break;
			case 'CZ':
			case 'SK':
				regex = /^([0-9]{3})(\s?)([0-9]{2})$/;
				valid = regex.test(postcode);
				break;

			default:
				valid = true;
				break;
		}

		return valid;
	}

	/**
	 * This code is similar to woocommerce/includes/wc-formatting-functions.php 
	 * wc_normalize_postcode();
	 * WooCommerce v3.5.7
	 */
	function normalize_postcode( postcode ) {
		return postcode.toUpperCase().trim().replace(/[\s\-]/, '');
	}

	/**
	 * This code is similar to woocommerce/includes/class-wc-validation.php 
	 * WC_Validation::is_gb_postcode();
	 * WooCommerce v3.5.7
	 * /*following header from the file*
	 * 	* General user data validation methods
	 * 	*
	 * 	* @package WooCommerce\Classes
	 * 	* @version  2.4.0
	 * 	***
	 */
	function is_gb_postcode( to_check ) {

		// Permitted letters depend upon their position in the postcode.
		// https://en.wikipedia.org/wiki/Postcodes_in_the_United_Kingdom#Validation.
		var alpha1 = '[abcdefghijklmnoprstuwyz]'; // Character 1.
		var alpha2 = '[abcdefghklmnopqrstuvwxy]'; // Character 2.
		var alpha3 = '[abcdefghjkpstuw]';         // Character 3 == ABCDEFGHJKPSTUW.
		var alpha4 = '[abehmnprvwxy]';            // Character 4 == ABEHMNPRVWXY.
		var alpha5 = '[abdefghjlnpqrstuwxyz]';    // Character 5 != CIKMOV.

		var pcexp = [];

		// Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA.
		pcexp[0] = '/^(' + alpha1 + '{1}' + alpha2 + '{0,1}[0-9]{1,2})([0-9]{1}' + alpha5 + '{2})$/';

		// Expression for postcodes: ANA NAA+
		pcexp[1] = '/^(' + alpha1 + '{1}[0-9]{1}' + alpha3 + '{1})([0-9]{1}' + alpha5 + '{2})$/';

		// Expression for postcodes: AANA NAA+
		pcexp[2] = '/^(' + alpha1 + '{1}' + alpha2 + '[0-9]{1}' + alpha4 + ')([0-9]{1}' + alpha5 + '{2})$/';

		// Exception for the special postcode GIR 0AA.
		pcexp[3] = '/^(gir)(0aa)$/';

		// Standard BFPO numbers.
		pcexp[4] = '/^(bfpo)([0-9]{1,4})$/';

		// c/o BFPO numbers.
		pcexp[5] = '/^(bfpo)(c\/o[0-9]{1,3})$/';

		// Load up the string to check, converting into lowercase and removing spaces.
		var postcode = to_check.toLowerCase();
		postcode = postcode.replace(' ', '');

		// Assume we are not going to find a valid postcode.
		var valid = false;

		// Check the string against the six types of postcodes.
		for(var pattern of pcexp){
			pattern = pattern.replace(/^\/|\/$/g, '');
			var regex = new RegExp(pattern);
			if ( regex.test(postcode) ) {
				// Remember that we have found that the code is valid and break from loop.
				valid = true;
				break;
			}
		}

		return valid;
	}

	function scroll_to(scrollElement, callback=''){
		if ( scrollElement.length ) {
			$( 'html, body' ).animate( {
				scrollTop: ( scrollElement.offset().top - 100 )
			}, 1000, 'swing', function(){ 
				if(callback){
					callback();
				}
			} );
		}
	}

	function params_to_object(entries) {
		var result = {}
		for(var entry of entries) { // each 'entry' is a [key, value] tupple
			var [key, value] = entry;
			result[key] = value;
		}
		return result;
	}

	if( $('#cartimize_bill_to_different_address_checkbox').length ){
	// ajaxSetup is global
		$.ajaxSetup({
			beforeSend: function( jqXHR,  settings ) {
				//This function is used to copy shipping values to billing values when shipping first is enabled and when billing address is same as shipping address checkbox is checked
				if( typeof wc_checkout_params === 'undefined' || !wc_checkout_params.hasOwnProperty('wc_ajax_url') || typeof wc_checkout_params.wc_ajax_url === 'undefined' ){
					return;
				}

				var ajax_end_point_url = wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'update_order_review' );//this line taken from /wp-content/plugins/woocommerce/assets/js/frontend/checkout.js method update_checkout_action()
				if( ajax_end_point_url != settings.url ){
					return;
				}

				if( !$('#cartimize_bill_to_different_address_checkbox').length ){
					return;
				}

				if( !$('#cartimize_bill_to_different_address_checkbox').is(":checked") ){
					//not checked - having separate billing address
					return;
				}

				//no separate billing address, copy from shipping address.

				var s_country		 = $( '#shipping_country' ).val();
				var s_state			 = $( '#shipping_state' ).val();
				var s_postcode		 = $( ':input#shipping_postcode' ).val();
				var s_city			 = $( '#shipping_city' ).val();
				var s_address		 = $( ':input#shipping_address_1' ).val();
				var s_address_2		 = $( ':input#shipping_address_2' ).val();

				var append_data = {
					country			: s_country,
					state			: s_state,
					postcode		: s_postcode,
					city			: s_city,
					address			: s_address,
					address_2		: s_address_2
				};

				//https://stackoverflow.com/a/52539264
				if( typeof URLSearchParams === 'function' ){
					var urlParams = new URLSearchParams(settings.data);

					if( typeof Object.fromEntries === 'function' ){
						var data_params = Object.fromEntries(urlParams);
					}
					else{
						var entries = urlParams.entries(); //returns an iterator of decoded [key,value] tuples
						var data_params = params_to_object(entries); 
					}
					
					$.extend( true, data_params, append_data );//1st parama true - recurssive merge
					settings.data = jQuery.param(data_params);
				}
				else{
					//fallback method
					//just append, in the server side mostly it will overide the first variable/param, with 2nd variable/param of same name
					var append_data_str = jQuery.param( append_data );
					settings.data = settings.data + '&' + append_data_str;
				}
			}
		});
	}
});