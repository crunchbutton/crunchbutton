/**
 * event binding
 */
App.signin.init = function() {

	$('.wrapper').append(App.render('signin'));

	$(document).on('touchclick', '.signin-facebook-button', function() {
		App.signin.facebook.login();
	});

	$(document).on('touchclick', '.signin-form-button', function() {
		App.signin.sendForm();
	});

	$(document).on('touchclick', '.signin-password-help', function() {
		App.signin.passwordHelp.show();
	});

	$(document).on('touchclick', '.signin-password-help-back', function() {
		App.signin.passwordHelp.hide();
	});

	$(document).on('touchclick', '.signin-password-help-button', function() {
		App.signin.passwordHelp.sendForm();
	});

	$(document).on('submit', '.signin-help-form', function() {
		App.signin.passwordHelp.sendForm();
		return false;
	});
	
	$(document).on('submit', '.signin-form', function(e) {
		App.signin.sendForm();
		e.stopPropagation();
		return false;
	});

	$(document).on('touchclick', '.signin-icon', function() {
		App.signin.show();
	});

	$(document).on('touchclick', '.signup-link', function() {
		App.signup.show( false );
		$('.signin-container').dialog('close');
	});

	$(document).on('touchclick', '.sign-in-icon', function() {
		if (App.config.user.id_user) {
			var pacmanSide = ( App.currentPage == 'restaurants' ) ? 'left' : 'right';
			App.controlMobileIcons.showPacman( pacmanSide, function(){ $( '.sign-in-icon' ).addClass( 'config-icon-mobile-hide' ); } );
			History.pushState({}, 'Crunchbutton - Orders', '/orders');
		} else {
			App.signin.show();
		}
	});

	$(document).on('touchclick', '.signout-icon', function() {
		App.signin.signOut();
	});

	$(document).on('touchclick', '.signin-user', function() {
		History.pushState({}, 'Your Account', '/orders');;
	});

	History.Adapter.bind(window,'statechange',function() {
		App.signin.checkUser();
	});

	App.signin.facebook.init();
}

App.signin.sendForm = function(){
	// Checks it fhe login is a phone
	var login = $( 'input[name=signin-email]' ).val();
	login = login.replace(/[^\d]*/gi,'')
	if( !App.phone.validate( login ) ){
		// It seems not to be a phone number, lets check if it is a email
		login = $.trim( $( 'input[name=signin-email]' ).val() );
		if( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( login ) ){
			login = false;
		}
	}
	if( !login ){
		alert( 'Please enter a valid email or phone.' );
		$( 'input[name=signin-email]' ).focus();
		return;
	}

	if( $.trim( $( 'input[name=signin-password]' ).val() ) == '' ){
		alert( 'Please enter your password.' );
		$( 'input[name=signin-password]' ).focus();
		return;
	}
	var email = login,
			password = $.trim( $( 'input[name=signin-password]' ).val() ),
			url = App.service + 'user/auth';
	$('.signin-error').hide();
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'email' : email, 'password' : password },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				
				// Log the error
				App.log.account( { 'error' : json.error } , 'sign in error' );

				$('.signin-error').fadeIn();
			} else{
				App.config.user = json;
				App.signin.checkUser();
				$( '.signin-container' ).dialog( 'close' );
				// If the user is at the restaurant's page - reload it
				if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
					App.page.restaurant( App.restaurant.permalink );
				}
				App.signin.manageLocation();

				if( App.giftcard.callback ){
					App.giftcard.callback();
				}
			}
		}
	} );
}

App.signin.manageLocation = function(){
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

/**
 * sign out and go to the home page
 */
App.signin.signOut = function(){
	if (confirm( 'Confirm sign out?')){
		// Force to remove the cookies
		$.each( [ 'token', 'location', 'PHPSESSID' ], function( index, value ){
			$.cookie( value, null );
		} );
		if( App.signin.facebook.isLogged ){
			FB.logout( function(){
				$.getJSON('/api/logout',function(){
					$( '.signout-icon' ).hide();
					location.href = '/';
				} );
			} );
		} else {
			$.getJSON('/api/logout',function(){
				$( '.signout-icon' ).hide();
				location.href = '/';
			} );
		}
	}
}

App.signin.facebook = {
	running: false,
	init: function() {}
};

App.signin.facebook.processStatus = function( session ){
	if ( session.status === 'connected' && session.authResponse ) {
		if( session.authResponse.accessToken ){
			App.facebook.registerToken( session.authResponse.accessToken );	
		}
		App.signin.facebook.isLogged = true;
		
		App.log.account( { 'userID' : session.authResponse.userID} , 'facebook login' );
		if( App.signin.facebook.shouldAuth ){
			FB.api( '/me', { fields: 'name' }, function( response ) {
				if ( response.error ) {
					App.log.account( { 'userID' : session.authResponse.userID, 'error' : response.error } , 'facebook name error' );
					return;
				}
				App.log.account( { 'userID' : session.authResponse.userID, 'response' : response, 'shouldAuth' : App.signin.facebook.shouldAuth, 'running' : App.signin.facebook.running } , 'facebook response' );
				if( response.id ){
					App.signin.facebook.shouldAuth = false;
					$( '.signin-facebook-message' ).show();
					$( '.signup-facebook-message' ).show();
					$( '.signin-facebook' ).hide();
					$( '.signup-facebook' ).hide();
					// Just call the user api, this will create a facebook user
					var url = App.service + 'user/facebook';
					if( !App.signin.facebook.running ){
						App.signin.facebook.running = true;
						App.log.account( { 'userID' : session.authResponse.userID, 'running' : App.signin.facebook.running } , 'facebook running' );
						$.ajax( {
							type: 'GET',
							url: url,
							dataType: 'json',
							success: function( json ){
								App.log.account( { 'userID' : session.authResponse.userID, 'running' : App.signin.facebook.running, 'json' : json } , 'facebook ajax' );
								App.signin.facebook.running = true;
								if( json.error ){
									if( json.error == 'facebook id already in use' ){
										// Log the error
										App.log.account( { 'error' : json.error } , 'facebook error' );
										alert( 'Sorry, It seems the facebook user is already related with other user.' );
									}
								} else {
									App.processConfig(null, json);
									App.signin.checkUser();
									if( App.giftcard.callback ){
										App.giftcard.callback();	
									}
									App.signin.manageLocation();
								}
								// Closes the dialog
								try{ $( '.ui-dialog-content' ).dialog( 'close' );} catch(e){};
								
								App.log.account( { 'userID' : session.authResponse.userID, 'currentPage' : App.currentPage } , 'facebook currentPage' );

								// If the user is at the restaurant's page - reload it
								if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
									App.page.restaurant( App.restaurant.permalink );
								}
								if( App.currentPage == 'orders' ){
									App.page.orders()								
								}
								App.recommend.relateUser();
							}
						} );
					}
				}
			});
		}
	}
}


/**
 * signin with facebook and get email
 */
App.signin.facebook.login = function() {
	App.signin.facebook.shouldAuth = true;
	FB.login( App.signin.facebook.processStatus,{ scope: App.facebookScope } );
};

/**
 * show the signin modal
 */
App.signin.show = function(){
	App.signin.passwordHelp.hide();
	$('.signin-facebook-message').hide();
	$('.signin-facebook').show();

	$( 'input[name=signin-email]' ).val( '' );
	$( 'input[name=signin-password]' ).val( '' );
	$('.signin-error').hide();
	$( '.signin-container' )
		.dialog( {
			dialogClass: 'modal-fixed-dialog',
			modal: true,
			width: App.modal.contentWidth(),
			open: function( event, ui ) { $( '.signin-email' ).focus(); }
		} );

}

App.signin.checkUser = function(){
	// If the user is logged
	if( App.config.user.id_user && $.trim( App.config.user.id_user ) != '' ){
		$( '.signin-user' ).show();
		$( '.signin-icon' ).hide();
		$( '.signout-icon' ).hide();
		$( '.signin-box-header' ).addClass( 'signin-box-header-min' );
	} else {
		$( '.signin-user' ).hide();
		$( '.signin-icon' ).show();
		$( '.signup-icon' ).show();
		$( '.signout-icon' ).hide();
		$( '.signin-box-header' ).removeClass( 'signin-box-header-min' );
	}
	if( App.currentPage == 'home' ){
		$( '.config-icon' ).addClass( 'config-icon-desktop-hide' );
	} else {
		$( '.config-icon' ).removeClass( 'config-icon-desktop-hide' );
	}
}

App.signin.passwordHelp = {};

App.signin.passwordHelp.show = function(){
	if( $.trim( $( 'input[name=signin-email]' ).val() ) != '' ){
		$( 'input[name=password-help-email]' ).val( $.trim( $( 'input[name=signin-email]' ).val() ) );
	}
	$( '.signin-password-help-button' ).show();
	$( '.signin-password-help-back' ).show();
	$( '.signin-help-container' ).show();
	$( '.signin-form-options' ).hide();
	$( '.signin-password-help-message' ).hide();
	$( '.signin-password-help-message' ).html( '' );
	$( 'input[name=password-help-email]' ).focus();
}

App.signin.passwordHelp.hide = function(){
	$( '.signin-help-container' ).hide();
	$( '.signin-form-options' ).show();
}

App.signin.passwordHelp.sendForm = function(){
	// Checks it fhe login is a phone
	var login = $( 'input[name=password-help-email]' ).val();
	login = login.replace(/[^\d]*/gi,'')
	if( !App.phone.validate( login ) ){
		// It seems not to be a phone number, lets check if it is a email
		login = $.trim( $( 'input[name=password-help-email]' ).val() );
		if( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( login ) ){
			login = false;
		}
	}
	if( !login ){
		alert( 'Please enter a valid email or phone.' );
		$( 'input[name=password-help-email]' ).focus();
		return;
	}
	$( '.password-help-error' ).html( '' );
	$( '.password-help-error' ).hide();
	var url = App.service + 'user/reset';
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'email' : login },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'user is not registred' ){
					$( '.password-help-error' ).html( 'Sorry, that email/phone is not registered with us.' );
					$( '.password-help-error' ).fadeIn();
					$( 'input[name=password-help-email]' ).focus()
				}
				// Log the error
				App.log.account( { 'error' : json.error } , 'password help error' );
			} else {
				if( json.success = 'success' ){
					$( '.signin-password-help-message' ).show();
					$( '.signin-password-help-button' ).hide();
					$( '.signin-password-help-back' ).hide();
					
					var message = 'You will receive a code to reset your password! It will expire in 24 hours.';
					
					if( json.userHasFacebookAuth ){
						message += '<br/>';
						message += '<br/>';
						message += 'You can also try logging in with <span class="login-facebook">Facebook</span>.';
					}
					
					$( '.signin-password-help-message' ).html( message );

					$( '.login-facebook' ).on( 'touchclick', function(){
						App.signin.show();
					} );

				}
			}
		}
	} );
}

App.signin.passwordHelp.reset = {};

App.signin.passwordHelp.reset.init = function(){
	$( '.password-reset-container' )
		.dialog( {
			modal: true,
			dialogClass: 'modal-fixed-dialog',
			width: App.modal.contentWidth(),
			close: function( event, ui ) { App.signin.passwordHelp.reset.close(); },
			open: function( event, ui ) { $( 'input[name=password-reset-code]' ).focus(); }
		} );
	$( '.password-reset-code-button' ).on( 'touchclick', function(){
		App.signin.passwordHelp.reset.sendForm();
	} );
	$( '.password-change-button' ).on( 'touchclick', function(){
		App.signin.passwordHelp.reset.change();
	} );
	$( '.password-reset-form' ).submit(function() {
		return false;
	} );
	$( '.password-change-form' ).submit(function() {
		return false;
	} );
}

App.signin.passwordHelp.reset.sendForm = function(){
	$( '.password-reset-code-error' ).html( '' );
	$( '.password-reset-code-error' ).hide();
	var code = $.trim( $( 'input[name=password-reset-code]' ).val() );
	if( code == '' ){
		alert( 'Please enter the reset code.' );
		$( 'input[name=password-reset-code]' ).focus();
		return;
	}
	var url = App.service + 'user/code-validate';
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'code' : code },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'invalid code' ){
					$( '.password-reset-code-error' ).html( 'Sorry, this code is invalid.' );
				}
				if( json.error == 'expired code' ){
					$( '.password-reset-code-error' ).html( 'Sorry, this code is expired.' );
				}
				$( '.password-reset-code-error' ).fadeIn();
				$( 'input[name=password-reset-code]' ).focus()
			} else {
				if( json.success = 'valid code' ){
					$( '.password-reset-block' ).hide();
					$( '.password-change-block' ).show();
					$( 'input[name=password-new]' ).focus();
				}
			}
		}
	} );
}

App.signin.passwordHelp.reset.change = function(){
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



App.signup = {};


/**
 * event binding
 */
App.signup.init = function() {

	$('.wrapper').append(App.render('signup'));

	$(document).on('touchclick','.signup-add-password-button', function() {
		App.signup.show(false);
	});

	$(document).on('touchclick','.signup-icon', function() {
		App.signup.show( false );
	});

	$(document).on('touchclick','.signup-form-button', function() {
		App.signup.sendForm();
	});

	$(document).on('touchclick','.signup-facebook-button', function() {
		App.signin.facebook.login();
	});

	$(document).on('touchclick','.signin-link', function() {
		App.signin.show();
		$('.signup-container').dialog('close');
	});

	$(document).on('submit','.signup-form', function() {
		App.signup.sendForm();
		return false;
	});
}


/**
 * show the signup modal
 */
App.signup.show = function( justFacebook ){
	$( '.signup-facebook' ).show();
	$( '.signup-facebook-message' ).hide();
	if( App.config.user.facebook ){
		$( '.signup-facebook-container' ).hide();
	} else {
		$( '.signup-facebook-container' ).show();
	}


	$( 'input[name=signup-password]' ).val( '' );
	$( '.signup-form-options' ).show();
	$( '.signup-success-container' ).hide();
	if( justFacebook ){
		$( '.signup-form' ).hide();
	} else {
		$( '.signup-form' ).show();
	}
	$( '.signin-error' ).hide();
	$( '.signup-container' )
		.dialog( {
			modal: true,
			dialogClass: 'modal-fixed-dialog',
			width: App.modal.contentWidth(),
			open: function( event, ui ) { $( '.signup-phone' ).focus(); }
		} );

}

App.signup.checkLogin = function(){
	var login = $( 'input[name=pay-phone]' ).val().replace(/[^\d]*/gi,'');
	if( App.phone.validate( login ) ){
		var url = App.service + 'user/verify/' + login	
		$.getJSON( url, function( json ) {
			if( json.error ){
				if( json.error == 'user exists' ){
					$( 'input[name=pay-password]' ).val( '' );
					$( '.password-field' ).hide();
				}
			} else {
				$( '.password-field' ).fadeIn();
				$( 'input[name=pay-password]' ).val( '' );
				$( 'input[name=pay-password]' ).focus();
			}
		} );
	} else {
		$( 'input[name=pay-password]' ).val( '' );
		$( '.password-field' ).hide();
	}
}

App.signup.sendForm = function(){
	login = $.trim( $( 'input[name=signup-email]' ).val() );
	if( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( login ) ){
		login = false;
	}
	if( !login ){
		alert( 'Please enter a valid email email address.' );
		$( 'input[name=signup-email]' ).focus();
		return;
	}

	if( $.trim( $( 'input[name=signup-password]' ).val() ) == '' ){
		alert( 'Please enter your password.' );
		$( 'input[name=signup-password]' ).focus();
		return;
	}
	var password = $.trim( $( 'input[name=signup-password]' ).val() ),
			url = App.service + 'user/create/local';
	$( '.signup-error' ).hide();
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'email' : login, 'password' : password },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'user exists' ){
					$('.signup-error').html( 'It seems that the email is already registered!' );
				}
				// Log the error
				App.log.account( { 'error' : json.error, 'login' : login } , 'sign up error' );
				$('.signup-error').fadeIn();
			} else{
				App.processConfig(null, json);
				$( '.success-phone' ).html( login );
				$( '.signup-call-to-action' ).hide();
				$( '.signup-form-options' ).hide();
				$( '.signup-success-container' ).show();
				App.signin.checkUser();
				// If the user is at the restaurant's page - reload it
				if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
					App.page.restaurant( App.restaurant.permalink );
				}
				if( App.giftcard.callback ){
					$( '.signup-container' ).dialog( 'close' );
					App.giftcard.callback();
				}
				App.recommend.relateUser();
			}
		}
	} );
}

