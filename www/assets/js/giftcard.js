App.giftcard = {
	api : {
		code : 'giftcard/code',
		validate : 'giftcard/validate'
	},
	size : 6,
	callback : false,
	hasStarted : false
}

App.giftcard.show = function( path ){
	if( !App.giftcard.hasStarted ){
		App.giftcard.hasStarted = true;
		$( '.wrapper' ).append( App.render( 'giftcard' ) );	
	}
	$( '.giftcard-container' )
		.dialog( {
			modal: true,
			dialogClass: 'modal-fixed-dialog',
			width: App.modal.contentWidth(),
			close: function( event, ui ) {},
			open: function( event, ui ) { $( 'input[name=giftcard-code]' ).focus(); }
	} );
	setTimeout( function(){
		// Check if the user is logged in
		if( App.config.user.id_user && $.trim( App.config.user.id_user ) != '' ){
			App.giftcard.process( path );
		} else {
			$( '.giftcard-message-title' ).html( 'Welcome' );
			$( '.giftcard-message-text' ).html( 'Please <span class="giftcard-sign-in">sign in</span> to claim your gift card.' );
			$(document).on('touchclick', '.giftcard-sign-in', function() {
				App.giftcard.callback = function(){
					App.giftcard.callback = false;
					$( '.giftcard-message-title' ).html( 'Gift card.' );
					$( '.giftcard-message-text' ).html( 'Just a sec.' );
					App.giftcard.show( path );
				};
				$('.giftcard-container').dialog('close');
				App.signin.show();
			});	
		}
	}, 300 );	
}

App.giftcard.process = function( path ){
	var code = ( path.length > 1 ) ? ( path[ 1 ] ? path[ 1 ] : '' ) : '';
	var url = App.service + App.giftcard.api.code;
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'code' : code },
		dataType: 'json',
		success: function( json ){
			var title = '';
			var text = '';
			if( json.error ){
				title = 'Oops, error!'
				if( json.error == 'gift card already used' ){
					text = 'Sorry, this gift card was already used.';
				} else if( json.error == 'invalid gift card' ){
					text = 'Sorry, this is an invalid gift card.';
				} else {
					text = 'Please try it again.';
				}
			} else {
				if( json.success ){
					title = 'Gift card added!';
					var id_restaurant = json.success['id_restaurant'];
					var restaurant_name = json.success['restaurant'];
					var value = '$' + json.success['value'];
					text = 'You\'ve got a  <span class="giftcard-value">' + value + '</span> gift card to <span id_restaurant="' + id_restaurant + '" class="giftcard-restaurant">' + restaurant_name + '</span>.'

					$(document).on('touchclick', '.giftcard-restaurant', function() {
						var id_restaurant = $( this ).attr( 'id_restaurant' );
						$( '.giftcard-container' ).dialog( 'close' );
						App.loadRestaurant( id_restaurant );
					});
				}
			}
			$( '.giftcard-message-title' ).html( title );
			$( '.giftcard-message-text' ).html( text );
		}
	} );
}

// Methods to redem a gift card at the Notes field
App.giftcard.notesField = { value : 0, isProcessing : false, callback : false, lastValue : 0, backup : { value : 0, backuped : false } };

App.giftcard.notesField.listener = function(){	
	if( App.giftcard.notesField.isProcessing ){
		App.giftcard.notesField.callback = App.giftcard.notesField.listener;
		return false;
	}
	App.giftcard.notesField.isProcessing = true;
	App.giftcard.notesField.value = 0;
	// Clean the message div
	$( '.giftcard-info' ).html( '' );
	// Get the nodes field's value
	var notes = $.trim( $( '[name=notes]' ).val() );
	// Split its words
	var words = notes.split( ' ' );
	var giftcards = {};
	App.giftcard.notesField.giftcards = [];
	for ( var x in words ) {
		var word = $.trim( words[ x ] );
		// To prevent duplicated 
		if( word != '' ){
			// Clean the word - remove special chars
			word = word.replace( /[^a-zA-Z 0-9]+/g, '' );
			// Check if the word has the same size of the gift card (App.giftcard.size)
			if( word.length == App.giftcard.size ){
				// Store this 'giftcards wanna be' in a array
				giftcards[ word ] = word;
			}
		}
	}
	var hasGiftsToValidate = false;
	$.each( giftcards, function( key, value ) {
		hasGiftsToValidate = true;
		App.giftcard.notesField.giftcards.push( value );
		App.giftcard.validate( value, App.giftcard.notesField.message );
	} );
	if( !hasGiftsToValidate ){
		App.giftcard.notesField.compareValues();
	}
}

// Compare values to info the user that he as removed the gift card from the notes field
App.giftcard.notesField.compareValues = function(){
	if( App.giftcard.notesField.lastValue > App.giftcard.notesField.value ){
		$( '.giftcard-info' ).html( '<span class="warning">' + 
																	'You just removed a gift card. If you still want to use that gift card, you must put it back in the notes field.' + 
																'</span>' );
	}
	App.giftcard.notesField.lastValue = App.giftcard.notesField.value;
	App.giftcard.notesField.force( App.giftcard.notesField.value );
	// It is not processing anymore
	App.giftcard.notesField.isProcessing = false;
	// Check if there is a callback and call it.
	if( App.giftcard.notesField.callback ){
		App.giftcard.notesField.callback();
		App.giftcard.notesField.callback = false;
	}
}

// Update the value
App.giftcard.notesField.updateValue = function(){
	App.giftcard.notesField.force( App.giftcard.notesField.value );	
	App.giftcard.notesField.giftcards.pop();
	// Check if it all the gift cards was validated
	if( App.giftcard.notesField.giftcards == 0 ){
		App.giftcard.notesField.compareValues();
	}
}

// Write the message about the gift card
App.giftcard.notesField.message = function( giftinfo ){
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
			if( giftinfo.success.id_restaurant || ( giftinfo.success.id_restaurant && giftinfo.success.id_restaurant == App.restaurant.id_restaurant ) ){
				$( '.giftcard-info' ).html( message + 'Congrats! This gift card (' + giftinfo.success.giftcard + ') gives you $' + giftinfo.success.value + '.' );
				App.giftcard.notesField.value = parseFloat( App.giftcard.notesField.value ) + parseFloat( giftinfo.success.value );
			}
		}
	} else {
		// Error! the gift card was already used
		if( giftinfo.error == 'gift card already used' ){
			$( '.giftcard-info' ).html( message + 'The gift card (' + giftinfo.giftcard + ') you are trying to use was already redeemed.' );
		}
	}
	// Update the value
	App.giftcard.notesField.updateValue();
}

// This is funtion will create a fake and 'cosmetic' credit the real one wil be created when the user post his order
App.giftcard.notesField.force = function( value ){
	// Backup the actual credit value
	if( !App.giftcard.notesField.backup.backuped ){
		App.giftcard.notesField.backup.backuped = true;
		App.giftcard.notesField.backup.value = parseFloat( App.credit.restaurant[ App.restaurant.id ] );
	}
	App.credit.restaurant[ App.restaurant.id ] = parseFloat( App.giftcard.notesField.backup.value ) + parseFloat( value );
	App.credit.show();
	App.cart.updateTotal();
}

// Validates a gift card code
App.giftcard.validate = function( code, callback ){
	console.log('code',code);
	var url = App.service + App.giftcard.api.validate;
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'code' : code },
		dataType: 'json',
		success: function( json ){
			if( callback ){
				callback( json );	
			}
		}
	} );
}


