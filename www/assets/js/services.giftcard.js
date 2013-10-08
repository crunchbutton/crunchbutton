// GiftCardService service
NGApp.factory( 'GiftCardService', function( $http, $location, $rootScope, AccountModalService, CreditService, RestaurantsService ){

	var service = {
		redeemed : false,
		code : false,
		value : '',
		notes_field : { lastValidation: '', content : '', giftcards : { success : [], error : [] }, value : '0.00', removed : false, id_restaurant : null, hasGiftCards : false, restaurant_accepts : false }, /* Notes field */
		modal : {
			intro : true,
			error : false,
			success : false,
			restaurant : false,
		}
	};

	credit = CreditService;

	service.accountModal = AccountModalService;
	service.account = service.accountModal.facebook.account;

	service.open = function(){
		App.dialog.show( '.giftcard-container' );
		service.parseURLCode();
		if( service.modal.intro ){
			setTimeout( function(){
				service.processModal();
				$location.path( '/location' );
			}, 100 );
		}
	}

	service.openRestaurant = function(){
		$location.path( '/' + RestaurantsService.permalink + '/' + service.modal.restaurant.permalink );
		service.modal.close();
	}

	service.parseURLCode = function(){
		service.code = $location.path().replace( '/giftcard', '' );
		service.code = service.code.replace( '/', '' );
	}

	/* Register the view */
	service.viewed = function(){
		var url = App.service + 'giftcard/viewed';
		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'code' : service.code } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} );
	}

	service.processModal = function(){
		if( !service.code || service.code == '' ){ 
			return; 
		}
		
		if( !(/^\w+$/.test( $.trim( service.code ) )) ){
			service.code = '';
			return;
		}
		service.modal.reset();
		setTimeout( function(){

			// Do not auto redeem gift cards #1662
			service.redeemed = false;
			
			service.validate( function( data ){

				service.modal.intro = false;
				if( data.error ){
					service.modal.error = true;
					switch( data.error ){
						case 'gift card already used':
							service.viewed();
							service.modal.error = 'used';
							break;
						case 'invalid gift card':
							service.modal.error = 'invalid';
							break;
						default:
							service.modal.error = 'unknow';
							break;
					}
					service.code = false;
				} else if ( data.success ){
					service.viewed();
					service.modal.success = true;
					service.value = data.success['value'];
					if( data.success['id_restaurant'] ){
						service.modal.restaurant = { id_restaurant : data.success['id_restaurant'], name : data.success['restaurant'], permalink : data.success['permalink'] };
					} else {
						service.modal.restaurant = false;
					}
					$rootScope.$broadcast( 'GiftCardProcessed', true );
					if( service.redeemed ){
						service.code = '';
					}
				}
			} );

		}, 500 );
	}

	service.modal.reset = function(){
		service.modal.intro = true;
		service.modal.error = false;
		service.modal.success = false;
		service.modal.restaurant = false;
	}

	service.modal.signIn = function(){
		service.account.callback = function(){
			service.open();
			service.processModal();
		}
		service.modal.close();
		setTimeout( function(){
			service.accountModal.signinOpen();	
		}, 500 );
	}

	service.modal.close = function(){
		$.magnificPopup.close();
	}

	// Validates a gift card code
	service.validate = function( callback ){
		service.code = $.trim( service.code );
		if( service.code == '' ){
			callback( { error : true } );
			return;
		}
		if( service.redeemed ){
			var url = App.service + 'giftcard/code';
		} else {
			var url = App.service + 'giftcard/validate';
		}
		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'code' : service.code } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
				callback( data );
			}	).error(function( data, status ) { 
				callback( { error : true } ); 
			} );
	}

	service.notes_field.reset = function(){
		service.notes_field.content = ''; 
		service.notes_field.giftcards = { success : [], error : [] };
		service.notes_field.value = '0.00';
		service.notes_field.removed = false;
		service.notes_field.id_restaurant = null;
		service.notes_field.hasGiftCards = false;
		service.notes_field.restaurant_accepts = false;
		service.code = false;
	}

	service.notes_field.start = function(){

		service.notes_field.hasGiftCards = false;
		service.notes_field.giftcards.success = [];
		
		if( service.notes_field.content == '' ){
			service.notes_field.lastValidation = '';
			service.notes_field.checkAllValidated();
		}

		if( service.notes_field.content && service.notes_field.content != '' ){

		var words = service.notes_field.content;

		if( words == service.notes_field.lastValidation ){
			return;
		}

		service.notes_field.lastValidation = words;
		if( !service.notes_field.running ){
			service.notes_field.running = true;
			var url = App.service + 'giftcard/validate-words';
			$http( {
				method: 'POST',
				url: url,
				data: $.param( { 'words' : words } ),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				} ).success( function( data ) {
					if( data.success ){
						service.notes_field.hasGiftCards = true;
						var giftcard = data.success;
						if( giftcard.id_restaurant &&  giftcard.id_restaurant != service.notes_field.id_restaurant ){
							giftcard.error = 'other restaurant';
						}
						service.notes_field.giftcards.success.push( data.success );	
					} else if( data.error ){
						service.notes_field.giftcards.success = [];
					}
					service.notes_field.total--;
					service.notes_field.checkAllValidated();
					service.notes_field.running = false;
				}	).error(function( data, status ) { service.notes_field.total--; service.notes_field.checkAllValidated();  service.notes_field.running = false; } );
			}
		}
	}

	service.notes_field.checkAllValidated = function(){
		service.notes_field.compareValues();
		$rootScope.$broadcast( 'giftCardUpdate' );
	}

	service.notes_field.compareValues = function(){
		service.notes_field.removed = false;
		var values = 0;
console.log('values',values);
		if( service.notes_field.giftcards.success.length > 0 ){
			$.each( service.notes_field.giftcards.success, function( key, giftcard ) {
				if( giftcard && !giftcard.error ){
					values += parseFloat( giftcard.value ); 
				}
			} );
		}
console.log(' service.notes_field.value', service.notes_field.value);
		if( service.notes_field.value > values ){
			service.notes_field.removed = true;
		}

		if( service.notes_field.restaurant_accepts ){
console.log('credit.redeemed',credit.redeemed);
			service.notes_field.value = App.ceil( values ).toFixed( 2 );
			credit.setValue( App.ceil( credit.redeemed + values ).toFixed( 2 ) );
		}
console.log(' { value : service.value }', { value : service.value });
		$rootScope.$broadcast( 'creditChanged',  { value : service.value } );
	}

	return service;

} );

NGApp.factory( 'CreditService', function( $http, $rootScope ){

	var service = { value : '0.00', tooltip : false, redeemed : 0 };
	
	service.setValue = function( value ){
		service.value = value;
		$rootScope.$broadcast( 'creditChanged',  { value : service.value } );
	}

	service.getCredit = function( restaurant_id ){
		var url = App.service + 'user/credit/' + restaurant_id;
		$http( { url: url } ).success( function( data ) { 
			if( data.credit ){
				service.setValue( App.ceil( data.credit ).toFixed( 2 ) );
				service.redeemed = data.credit;
			} else {
				service.setValue( App.ceil( 0 ).toFixed( 2 ) );
				service.redeemed = 0;
			}
		}	).error(function() {
				service.setValue( App.ceil( 0 ).toFixed( 2 ) );
				service.redeemed = 0;
		} );
	}
	return service;
} );



