App.giftcard = {
	api : {
		code : 'giftcard/code'
	},
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
			$(document).on('click', '.giftcard-sign-in', function() {
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
					var restaurant = '<a href="/food-delivery/' + json.success['permalink'] + '">' + json.success['restaurant'] + '</a>';
					var value = '$' + json.success['value'];
					text = 'You\'ve got a  <span class="giftcard-value">' + value + '</span> gift card to <span class="giftcard-restaurant">' + restaurant + '</span>.'
				}
			}
			$( '.giftcard-message-title' ).html( title );
			$( '.giftcard-message-text' ).html( text );
		}
	} );
}