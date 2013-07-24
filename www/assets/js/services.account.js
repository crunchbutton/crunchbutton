// AccountService service
NGApp.factory( 'AccountService', function( $http ){
	
	var service = { 
				user : false, 
				email : '', 
				password : '', 
				error : { 
						signin : false, 
						signup : false 
					} 
			};

	service.checkUser = function(){
		if( service.isLogged() ){
			service.user = App.config.user;
		}
	}

	service.isLogged = function(){
		return ( App.config.user.id_user && $.trim( App.config.user.id_user ) != '' );
	}

	service.signin = function(){
		if( !service.isValidEmailPhone() ){
			alert( 'Please enter a valid email or phone.' );
			$( '.signin-email' ).focus();
			return;
		}

		if( !service.isValidPassword() ){
			alert( 'Please enter your password.' );
			$( '.signin-password' ).focus();
			return;
		}
		service.purify();

		service.error.signin = false;

		var url = App.service + 'user/auth';

		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'email' : service.email, 'password' : service.password } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						App.log.account( { 'error' : data.error } , 'sign in error' );
						service.error.signin = true;
					} else {
						// TODO : replace this
						App.config.user = data;
						service.user = data;
						$.magnificPopup.close();
						// If the user is at the restaurant's page - reload it
						if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
							App.page.restaurant( App.restaurant.permalink );
						}
						App.signin.manageLocation();

						if( App.giftcard.callback ){
							App.giftcard.callback();
						}
					}
					
			}	);
	}

	service.signup = function(){
		if( !service.isValidEmailPhone() ){
			alert( 'Please enter a valid email or phone.' );
			$( '.signup-email' ).focus();
			return;
		}

		if( !service.isValidPassword() ){
			alert( 'Please enter a password.' );
			$( '.signup-password' ).focus();
			return;
		}
		service.purify();

		service.error.signin = false;

		var url = App.service + 'user/create/local';

		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'email' : service.email, 'password' : service.password } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						if( data.error == 'user exists' ){
							service.error.signup = true;
						}
						App.log.account( { 'error' : data.error, 'login' : service.email } , 'sign up error' );
					} else {
						// TODO : replace this
						App.processConfig(null, data);
						service.user = data;
						// If the user is at the restaurant's page - reload it
						if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
							App.page.restaurant( App.restaurant.permalink );
						}
						App.signin.manageLocation();

						if( App.giftcard.callback ){
							App.giftcard.callback();
						}
					}
					
			}	);
	}

	service.purify = function(){
		service.email = $.trim( service.email );
		service.password = $.trim( service.password );
	}

	service.isValidEmailPhone = function(){
		// check if it is a phone number
		if( !App.phone.validate( service.email ) ){
			if( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( service.email ) ){
				return false
			}
		}
		return true;
	}

	service.isValidPassword = function(){
		return service.password != '';
	}

	return service;
} );


// AccountHelpService service
NGApp.factory( 'AccountHelpService', function( $http, AccountService, AccountModalService ){ 
	
	// It starts invisible
	var service = { 
			visible : false, 
			error : false,
			success : { 
				visible : false, 
				facebook : { 
					visible : false 
				} 
			}
		};

	var account = AccountService;
	var modal = AccountModalService;

	service.show = function( show ){
		service.visible = show;
		modal.header = !show;
		if( show ){
			service.reset();
		}
	}

	service.reset = function(){
		service.error = false;
		service.success.visible = false;
		service.success.facebook.visible = false;
	}

	service.sendForm = function(){
		if( !account.isValidEmailPhone() ){
			alert( 'Please enter a valid email or phone.' );
			$( '.help-email' ).focus();
			return;
		}

		var url = App.service + 'user/reset';

		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'email' : account.email } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						if( data.error == 'user is not registred' ){
							service.error = true;
							$( 'input[name=password-help-email]' ).focus()
						}
					} else {
						if( data.success = 'success' ){
							service.success.visible = true;
							service.error = false;
							if( data.userHasFacebookAuth ){
								help.success.facebook.visible = true;
							}
							$( '.login-facebook' ).on( 'touchclick', function(){
								App.signin.show();
							} );

						}
					}
					
			}	);
	}
	return service;
} );

// AccountModalService service
NGApp.factory( 'AccountModalService', function( $http, FacebookService ){
	
	var service = {
		header : true,
		signin : true,
		signup : false
	};

	service.facebook = FacebookService;

	service.signinOpen = function(){
		App.dialog.show( '.account-container' );
		service.toggleSignForm( 'signin' );
	}

	service.signupOpen = function(){
		App.dialog.show( '.account-container' );
		service.toggleSignForm( 'signup' );
	}

	service.resetOpen = function(){
		App.dialog.show( '.account-reset-container' );
	}

	service.toggleSignForm = function( form ){
		service.facebook.wait = false;
		if( form == 'signin' ){
			service.signin = true;	
			service.signup = false;	
		} else {
			service.signin = false;	
			service.signup = true;	
		}
	}

	service.headerIsVisible = function(){
		return service.header.visible;
	}

	return service;
} );


// AccountFacebookService service
NGApp.factory( 'AccountFacebookService', function( $http, FacebookService ){
	
	var service = {};

	service.facebook = FacebookService;
	service.account = service.facebook.account;

	service.auth = function(){
		service.facebook.wait = true;
		FB.login( service.facebook.startAuth, { scope: App.facebookScope } );
	}

	service.signout = function( callback ){
		service.facebook.signout( callback );
	}

	return service;

} );

NGApp.factory( 'AccountSignOut', function( $http, AccountFacebookService ){

	var service = {};

	service.facebook = AccountFacebookService;

	service.do = function(){

		if (confirm( 'Confirm sign out?')){
			
			// Force to remove the cookies
			$.each( [ 'token', 'location', 'PHPSESSID' ], function( index, value ){
				$.totalStorage(value, null);
			} );
		
			var signout = function(){
				var url = App.service + 'logout';
				$http( { method: 'GET', url: url } ).success( function( data ) { location.href = '/'; } );
			};
		
			if( service.facebook.facebook.logged || service.facebook.facebook.account.user.facebook ){
				service.facebook.signout( function(){ signout() } );
			} else {
				signout();
			}
		}
	}
	return service;
} );


NGApp.factory( 'AccountResetService', function( $http, $location ){

	var service = {
		step : 1,
		code : '',
		password : '',
		success : false,
		error : false
	};

	service.code = $location.path().replace( '/reset', '' );
	service.code = service.code.replace( '/', '' );

	service.validateCode = function(){
		service.code = $.trim( service.code );
		if( service.code == '' ){
			service.error = 'empty';
			$( '#account-reset-code' ).focus();
			return;	
		}
		var url = App.service + 'user/code-validate';
		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'code' : service.code } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						if( data.error == 'invalid code' ){
							service.error = 'invalid';
						}
						if( data.error == 'expired code' ){
							service.error = 'expired';
						}
						$( '#account-reset-code' ).focus();
						return;
					} else {
						if( data.success = 'valid code' ){
							service.step = 2;
							service.error = false;
						}
					}
					
			}	);
	}

	service.changePassword = function(){

		service.password = $.trim( service.password );
		if( service.password == '' ){
			service.error = 'empty';
			$( '#account-reset-password' ).focus();
			return;	
		}

		var url = App.service + 'user/change-password';
		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'code' : service.code, 'password' : service.password } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						service.error = 'invalid';
						service.step = 1;
						return;
					} else {
						if( data.success = 'password changed' ){
							service.success = true;
						}
					}
					
			}	);
	}

	return service;

} );



