App.recommend = {
	api : {
		sms : 'support/sms',
	},
	itIsSending : false
}

App.recommend.init = function(){
	$( document ).on( 'click', '.home-recommend-button', function() {
		App.recommend.send();
	} );	
	$( document ).on( 'click', '.home-recommend-message-create-account', function(){
		App.signup.show( false );
	} );
}

App.recommend.send = function(){
	if ( $.trim( $( '.home-recommend-text' ).val() ) == '' ){
		alert( "Please enter the restaurant\'s name." );
		$( '.home-recommend-text' ).focus();
		return;
	}
	App.recommend.showThankYou();
}

App.recommend.showThankYou = function(){
	$( '.home-recommend-form' ).animate( { 'opacity' : 0 }, function(){
		$( '.home-recommend-form' ).hide();
		$( '.home-recommend-thank-you' ).css( 'opacity', 0 );
		$( '.home-recommend-thank-you' ).show();
		$( '.home-recommend-thank-you' ).animate( { 'opacity' : 1 } );	
	} );
	
}
