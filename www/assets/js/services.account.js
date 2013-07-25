// AccountService service
NGApp.factory( 'AccountService', function( $http ){
	
	var service = { 
				callback : false, 
				user : false, 
				error : { 
						signin : false, 
						signup : false 
					},
				form : {
					email : '', 
					password : ''	
				}
			};

	service.checkUser = function(){
		if( service.isLogged() ){
			service.user = App.config.user;
		}
	}

	service.isLogged = function(){
		if( App.config.user.id_user ){
			if( App.config.user.id_user != '' ){
				return true;
			}
		}
		return false;
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
			data: $.param( { 'email' : service.form.email, 'password' : service.form.password } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						App.log.account( { 'error' : data.error } , 'sign in error' );
						service.error.signin = true;
					} else {
						// TODO : replace this
						App.config.user = data;
						service.user = data;
						if( service.callback ){
							service.callback();
							service.callback = false;
						} else {
							$.magnificPopup.close();
							if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
								App.page.restaurant( App.restaurant.permalink );
							}
							App.signin.manageLocation();
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
			data: $.param( { 'email' : service.form.email, 'password' : service.form.password } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						if( data.error == 'user exists' ){
							service.error.signup = true;
						}
						App.log.account( { 'error' : data.error, 'login' : service.form.email } , 'sign up error' );
					} else {
						// TODO : replace this
						App.processConfig(null, data);
						service.user = data;
						if( service.callback ){
							service.callback();
							service.callback = false;
						} else {
							$.magnificPopup.close();
							if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
								App.page.restaurant( App.restaurant.permalink );
							}
							App.signin.manageLocation();
						}
					}
					
			}	);
	}

	service.purify = function(){
		service.form.email = $.trim( service.form.email );
		service.form.password = $.trim( service.form.password );
	}

	service.isValidEmailPhone = function(){
		// check if it is a phone number
		if( !App.phone.validate( service.form.email ) ){
			if( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( service.form.email ) ){
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
			data: $.param( { 'email' : account.form.email } ),
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
	service.do = function(){0
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
				console.log('Log out: facebook!');
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
		form : { code : '', password : '' },
		success : false,
		error : false
	};
	service.form.code = $location.path().replace( '/reset', '' );
	service.form.code = service.form.code.replace( '/', '' );
	service.validateCode = function(){
		service.form.code = $.trim( service.form.code );
		if( service.form.code == '' ){
			service.error = 'empty';
			$( '#account-reset-code' ).focus();
			return;	
		}
		var url = App.service + 'user/code-validate';
		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'code' : service.form.code } ),
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
		service.form.password = $.trim( service.form.password );
		if( service.form.password == '' ){
			service.error = 'empty';
			$( '#account-reset-password' ).focus();
			return;	
		}
		var url = App.service + 'user/change-password';
		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'code' : service.form.code, 'password' : service.form.password } ),
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



