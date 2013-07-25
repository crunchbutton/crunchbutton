// GiftCardModalService service
NGApp.factory( 'GiftCardModalService', function(){
	var service = {};
	service.open = function(){
		App.dialog.show( '.giftcard-container' );
	}
	return service;
} );

// GiftCardService service
NGApp.factory( 'GiftCardService', function( $http, $location, AccountModalService, GiftCardModalService ){

	service = {
		modal : {
			intro : true,
			error : false,
			success : false,
			restaurant : false,
		},

		redeemed : false,
		code : '',
		value : ''
		/*
		api : {
			code : ,
			validate : 'giftcard/validate'
		},
		notesCode : false,
		callback : false,
		hasStarted : false,
		showGiftCardCashMessage: true*/
	}

	service.accountModal = AccountModalService;
	service.giftCardModal = GiftCardModalService;
	service.account = service.accountModal.facebook.account;

	service.openRestaurant = function(){
		$location.path( '/' + App.restaurants.permalink + '/' + service.modal.restaurant.permalink );
		service.modal.close();
	}

	service.modal.signIn = function(){
		service.account.callback = function(){
			service.giftCardModal.open();
			service.processModal();
		}
		service.accountModal.signinOpen();
	}

	service.modal.close = function(){
		$.magnificPopup.close();
	}

	service.parseURLCode = function( ){
		service.code = $location.path().replace( '/giftcard', '' );
		service.code = service.code.replace( '/', '' );
	}

	service.processModal = function(){
		service.modal.reset();
		setTimeout( function(){
			// Check if the user is logged in
			service.redeemed = service.account.isLogged();
			service.validate( function( data ){
				service.modal.intro = false;
				if( data.error ){
					service.modal.error = true;
					switch( data.error ){
						case 'gift card already used':
							service.modal.error = 'used';
							break;
						case 'invalid gift card':
							service.modal.error = 'invalid';
							break;
						default:
							service.modal.error = 'unknow';
							break;
					}
				} else if ( data.success ){
					service.modal.success = true;
					service.value = data.success['value'];
					if( data.success['id_restaurant'] ){
						service.modal.restaurant = { id_restaurant : data.success['id_restaurant'], name : data.success['restaurant'], permalink : data.success['permalink'] };
					} else {
						service.modal.restaurant = false;
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


// Methods to redem a gift card at the Notes field
service.notesField = { value : 0, isProcessing : false, callback : false, lastValue : 0, backup : { value : 0, backuped : false } };

service.notesField.listener = function(){	
	if( service.notesField.isProcessing ){
		service.notesField.callback = service.notesField.listener;
		return false;
	}
	service.notesField.isProcessing = true;
	service.notesField.value = 0;
	// Clean the message div
	$( '.giftcard-info' ).html( '' );
	// Get the nodes field's value
	var notes = $.trim( $( '[name=notes]' ).val() );
	// Split its words
	var words = notes.split( ' ' );
	var giftcards = {};
	service.notesField.giftcards = [];
	for ( var x in words ) {
		var word = $.trim( words[ x ] );
		// To prevent duplicated 
		if( word != '' ){
			// Clean the word - remove special chars
			word = word.replace( /[^a-zA-Z 0-9]+/g, '' );
			// Store this 'giftcards wanna be' in a array
			giftcards[ word ] = word;
		}
	}
	var hasGiftsToValidate = false;
	$.each( giftcards, function( key, value ) {
		hasGiftsToValidate = true;
		service.notesField.giftcards.push( value );
		service.validate( value, service.notesField.message );
	} );
	if( !hasGiftsToValidate ){
		service.notesField.compareValues();
	}
}

// Compare values to info the user that he as removed the gift card from the notes field
service.notesField.compareValues = function(){
	if( service.notesField.lastValue > service.notesField.value ){
		$( '.giftcard-info' ).html( '<span class="warning">' + 
																	'You just removed a gift card. If you still want to use that gift card, you must put it back in the notes field.' + 
																'</span>' );
		service.showGiftCardCashMessage = true;
	}
	service.notesField.lastValue = service.notesField.value;
	service.notesField.force( service.notesField.value );
	// It is not processing anymore
	service.notesField.isProcessing = false;
	// Check if there is a callback and call it.
	if( service.notesField.callback ){
		service.notesField.callback();
		service.notesField.callback = false;
	}
}

// Update the value
service.notesField.updateValue = function(){
	service.notesField.force( service.notesField.value );	
	service.notesField.giftcards.pop();
	// Check if it all the gift cards was validated
	if( service.notesField.giftcards == 0 ){
		service.notesField.compareValues();
	}
}

// Write the message about the gift card
service.notesField.message = function( giftinfo ){

	// Get the message to concatenate
	var message = $( '.giftcard-info' ).html();
	if( $.trim( message ) != '' ){
		message += '<br/>';
	}
	// Success! it is a valid gift card!
	if( giftinfo.success ){
		// Check if gift card was made to the restaurant page
		if( giftinfo.success.id_restaurant && giftinfo.success.id_restaurant != App.restaurant.id_restaurant ){
			$( '.giftcard-info' ).html( message + 'The gift card (' + giftinfo.success.giftcard + ') you are trying to use belongs to another restaurant.' );
		} else {
			// If the restaurant is empty it means it is a 'global' gift card
			if( !giftinfo.success.id_restaurant ){
				if( !App.restaurant.giftcard ){
					$( '.giftcard-info' ).html( message + 'This restaurant does not accept gift card.' );
				} else {
					service.redem( giftinfo, message );
				}
			} else {
				if( giftinfo.success.id_restaurant == giftinfo.success.id_restaurant ){
					service.redem( giftinfo, message );
				}
			}
		}
	} else {
		// Error! the gift card was already used
		if( giftinfo.error == 'gift card already used' ){
			$( '.giftcard-info' ).html( message + 'The gift card (' + giftinfo.giftcard + ') you are trying to use was already redeemed.' );
		} 
	}
	// Update the value
	service.notesField.updateValue();
}

service.redem = function( giftinfo, message ){
	if( App.order.pay_type == 'cash' ){
		service.showGiftCardCashMessage = false;
		$( '.giftcard-info' ).html( message + 'Pay with a card, NOT CASH, to use your  ' +  ( App.config.ab && App.config.ab.dollarSign == 'show' ? '$' : '' ) + App.ceil(  giftinfo.success.value  ).toFixed( 2 ) + ' gift card!</span>' );	
		$( '.cart-giftcard-message' ).html( '' );
	} else {
		service.showGiftCardCashMessage = true;
		$( '.giftcard-info' ).html( message + 'Congrats! This gift card (' + giftinfo.success.giftcard + ') gives you $' + giftinfo.success.value + '.' );
	}
	service.notesField.value = parseFloat( service.notesField.value ) + parseFloat( giftinfo.success.value );	
}

// This is funtion will create a fake and 'cosmetic' credit the real one wil be created when the user post his order
service.notesField.force = function( value ){
	// Backup the actual credit value
	if( !service.notesField.backup.backuped ){
		service.notesField.backup.backuped = true;
		service.notesField.backup.value = parseFloat( App.credit.restaurant[ App.restaurant.id ] );
	}
	App.credit.restaurant[ App.restaurant.id ] = parseFloat( service.notesField.backup.value ) + parseFloat( value );
	App.credit.show();
	App.cart.updateTotal();
}


	return service;

} );