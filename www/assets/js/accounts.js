App.signin.manageLocation = function(){
	// TODO: fix it
	return;
	// If the user signed in and we do not have his location yet, lets use his stored location.
	if( App.loc.address() == '' ){
		if( App.config.user.address ){ // First check if we have the user's address. If we do, lets use it.
			App.loc.geocode( App.config.user.address, function(){ App.page.foodDelivery(true); }, function(){});
		} else if( App.config.user.location_lat && App.config.user.location_lon ){ // Else lets try to find the user's address by his position.
			App.loc.reverseGeocode( 
				App.config.user.location_lat, 
				App.config.user.location_lon, 
				function(){
					if( App.loc.realLoc.addressReverse ){
						var address = App.loc.realLoc.addressReverse;
						App.loc.geocode( address, 
							function(){ 
								App.page.foodDelivery(true); 
							}, 
							function(){ /* error, just ignore it */ });
					}
				}, 
				function(){ /* error, just ignore it */ } 
			);
		}
	}
}

App.signin.facebook = {
	running: false,
	init: function() {}
};


/**
 * signin with facebook and get email
 */
App.signin.facebook.login = function() {
	App.signin.facebook.shouldAuth = true;
	FB.login( App.signin.facebook.processStatus,{
		scope: App.facebookScope
	});
};



/*
App.signin.passwordHelp.change = function(){
	var code = $.trim( $( 'input[name=password-reset-code]' ).val() );
	var password = $.trim( $( 'input[name=password-new]' ).val() );
	if( password == '' ){
		alert( 'Please enter your password.' );
		$( 'input[name=password-new]' ).focus();
		return;
	}
	var url = App.service + 'user/change-password';
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'code' : code, 'password' : password },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'invalid code' ){
					$( '.password-change-error' ).html( 'Sorry, this code is invalid.' );
				}
				if( json.error == 'expired code' ){
					$( '.password-change-error' ).html( 'Sorry, this code is expired.' );
				}
				$( '.password-change-error' ).fadeIn();
			} else {
				if( json.success = 'password changed' ){
					$( '.password-change-message' ).fadeIn();
					$( '.password-change-block' ).find( 'h1' ).html( 'Done!' );
					$( '.password-change-message' ).html( 'Your password has changed!' );
					App.signin.passwordHelp.hasChanged = true;
				}
			}
			$( 'input[name=password-new]' ).hide();
			$( '.password-change-button' ).hide();
		}
	} );
}

App.signin.passwordHelp.reset.close = function(){
	if( App.signin.passwordHelp.hasChanged ){
		location.href = '/';
	}
}

App.signin.passwordHelp.reset.html = function( path ){
	var code = ( path.length > 1 ) ? ( path[ 1 ] ? path[ 1 ] : '' ) : '';
	return App.render('passwordhelp', {
		code: code
	});
}

*/

