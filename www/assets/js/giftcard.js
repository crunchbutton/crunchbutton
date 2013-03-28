App.giftcard = {
	api : {
		code : 'giftcard/code'
	}
}

App.giftcard.show = function( path ){
	$( '.wrapper' ).append( App.render( 'giftcard' ) );
	$( '.giftcard-container' )
		.dialog( {
			modal: true,
			dialogClass: 'modal-fixed-dialog',
			width: App.modal.contentWidth(),
			close: function( event, ui ) { App.signin.passwordHelp.reset.close(); },
			open: function( event, ui ) { $( 'input[name=giftcard-code]' ).focus(); }
	} );

	setTimeout( function(){
		// Check if the user is logged in
		if( App.config.user.id_user && $.trim( App.config.user.id_user ) != '' ){
			App.giftcard.process( path );
		} else {
			$( '.giftcard-message-title' ).html( 'Oops, you must sign in.' );
			$( '.giftcard-message-text' ).html( 'Oops, you must sign in.' );
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
					text = 'Sorry, you typed an invalid gift card.';
				} else {
					text = 'Please try it again.';
				}
				
			} else {
				if( json.success ){
					title = 'Gift card addedd!';
					var restaurant = '<a href="/food-delivery/' + json.success['permalink'] + '">' + json.success['restaurant'] + '</a>';
					var value = '$' + json.success['value'];
					text = 'It was added <span class="giftcard-value">' + value + '</span> for you spend at <span class="giftcard-restaurant">' + restaurant + '</span>.'
				}
			}
			$( '.giftcard-message-title' ).html( title );
			$( '.giftcard-message-text' ).html( text );
		}
	} );
}

App.giftcard.sendForm = function(){
	var code = $.trim( $( 'input[name=giftcard-code]' ).val() );
	if( code == '' ){
		alert( 'Please enter the gift card code!' );
		$( 'input[name=giftcard-code]' ).focus();
		return;
	}
	var url = App.service + App.giftcard.api.code;
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'code' : code },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'gift card already used' ){
					$( '.giftcard-code-error' ).html( 'Sorry, this gift card was already used.' );
				} else if( json.error == 'invalid gift card' ){
					$( '.giftcard-code-error' ).html( 'Sorry, you typed an invalid gift card.' );
				} else {
					$( '.giftcard-code-error' ).html( 'Oops, error! Please try it again.' );
				}
				$( '.giftcard-code-error' ).fadeIn();
				$( 'input[name=giftcard-code]' ).focus()
			} else {
				if( json.success ){
					$( '.giftcard-block' ).hide();
					$( '.giftcard-message' ).show();
					$( '.giftcard-restaurant' ).html( '<a href="/food-delivery/' + json.success['permalink'] + '">' + json.success['restaurant'] + '</a>' );
					$( '.giftcard-value' ).html( '$' + json.success['value'] );
				}
			}
		}
	} );
}