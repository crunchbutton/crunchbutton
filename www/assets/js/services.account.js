// AccountService service
NGApp.factory( 'AccountService', function( $http, $rootScope, PositionsService ){

	var service = {
		processing : false,
		callback : false,
		user : false,
		form : {
			email : '',
			password : ''
		}
	};

	// array with the errors
	service.errors = [];

	// reset the erros
	service.errorReset = function(){
		service.errors = [];
	}

	service.errorsList = {
			'enter-email-phone': 'Please enter a valid email or phone.',
			'enter-password': 'Please enter your password.',
			'login-incorrect': 'Your log in information was incorrect.',
			'already-registered': 'It seems that the email is already registered!',
			'not-registered': 'Sorry, that email/phone is not registered with us.',
			'enter-code':'Please enter the reset code.',
			'code-invalid':'Sorry, this code is invalid.',
			'code-expired':'Sorry, this code is expired.'
		};

	service.reset = function(){
		service.processing = false;
		service.form.email = '';
		service.form.password = '';
	}

	service.checkUser = function(){
		if( service.isLogged() ){
			service.user = App.config.user;
			$rootScope.$broadcast( 'haveUser', service.user );
			$rootScope.$safeApply();
		} else {
			service.updateInfo();
		}
	}

	service.updatePoints = function( callback ){
		var url = App.service + 'user/points';
		$http( {
			method: 'GET',
			url: url
			} ).success( function( data ) {
				if( data.id_user != '' ){
					$rootScope.$safeApply( function(){
						service.user.points = data;
						App.config.user.points = data;
					} );
				}
				if( callback ){
					callback( data );
				}
			}	);
	}

	service.isLogged = function(){
		if( App.config.user && App.config.user.id_user != '' ){
			service.user = App.config.user;
		}
		if( service.user ){
			if( service.user.id_user != '' ){
				return true;
			}
		}
		return false;
	}

	// This method will sign in or sign up an user
	service.enter = function(){
		service.processing = true;
		service.errorReset();
		if( !service.isValidEmailPhone() ){
			service.errors.push( service.errorsList[ 'enter-email-phone' ] );
			$rootScope.focus( '.signin-email' );
			service.processing = false;
			return;
		}

		if( !service.isValidPassword() ){
			service.errors.push( service.errorsList[ 'enter-password' ] );
			$rootScope.focus( '.signin-password' );
			service.processing = false;
			return;
		}

		service.purify();

		var url = App.service + 'user/enter';

		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'email' : service.form.email, 'password' : service.form.password } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					service.processing = false;
					if( data.error ){
						service.errors.push( service.errorsList[ 'login-incorrect' ] );
						App.rootScope.$safeApply();
					} else {
						service.user = data;
						service.updateInfo();
						if( service.callback ){
							service.callback();
							service.callback = false;
						} else {
							$.magnificPopup.close();
							$rootScope.$broadcast( 'userAuth', service.user );
						}
					}
			}	);
	}


	service.removePaymentMethod = function( callback ){
		var url = App.service + 'user/remove-payment-method';
		$http( {
			method: 'POST',
			url: url,
			data: {},
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
				if( !data.error ){
					service.user = data;
					service.updateInfo();
				}
				callback( data );
			}	);
	}

	service.update = function( account, callback ){

		var url = App.service + 'user/update';

		$http( {
			method: 'POST',
			url: url,
			data: $.param( account ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
				if( !data.error ){
					service.user = data;
					service.updateInfo();
				}
				callback( data );
			}	);
	}

	service.signin = function(){
		service.errorReset();
		if( !service.isValidEmailPhone() ){
			service.errors.push( service.errorsList[ 'enter-email-phone' ] );
			$rootScope.focus( '.signin-email' );
			return;
		}

		if( !service.isValidPassword() ){
			service.errors.push( service.errorsList[ 'enter-password' ] );
			$rootScope.focus( '.signin-password' );
			return;
		}

		service.purify();

		var url = App.service + 'user/auth';

		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'email' : service.form.email, 'password' : service.form.password } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						service.errors.push( service.errorsList[ 'login-incorrect' ] );
						App.rootScope.$safeApply();
						App.log.account( { 'error' : data.error } , 'sign in error' );
					} else {
						service.user = data;
						service.updateInfo();
						if( service.callback ){
							service.callback();
							service.callback = false;
						} else {
							$.magnificPopup.close();
							$rootScope.$broadcast( 'userAuth', service.user );
						}
					}
			}	);
	}

	service.signup = function(){
		service.errorReset();
		if( !service.isValidEmailPhone() ){
			service.errors.push( service.errorsList[ 'enter-email-phone' ] );
			$rootScope.focus( '.signup-email' );
			return;
		}

		if( !service.isValidPassword() ){
			service.errors.push( service.errorsList[ 'enter-password' ] );
			$rootScope.focus( '.signup-password' );
			return;
		}

		service.purify();

		var url = App.service + 'user/create/local';

		$http({
			method: 'POST',
			url: url,
			data: $.param( { 'email' : service.form.email, 'password' : service.form.password } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data){

			if (data.error) {
				if( data.error == 'user exists' ){
					service.errors.push( service.errorsList[ 'already-registered' ] );
				}
				App.log.account( { 'error' : data.error, 'login' : service.form.email } , 'sign up error' );
			} else {
				service.updateInfo();
				service.user = data;
				if( service.callback ){
					service.callback();
					service.callback = false;
				} else {
					$.magnificPopup.close();
					$rootScope.$broadcast('userCreated', service.user);
					$rootScope.$broadcast('userAuth', service.user);
				}
			}
		});
	}

	service.updateInfo = function( data, callback ){
		var url = App.service + 'user';
		$http( {
			method: 'GET',
			url: url
			} ).success( function( data ) {
				if( data.id_user != '' ){
					service.user = data;
					App.config.user = data;
					$rootScope.$broadcast('userUpdated', service.user);
					$rootScope.$safeApply();
				}
				if( callback ){
					callback();
				}
			}	);
	}

	service.checkPresetUpdate = function( id_preset, id_restaurant, callbackReload ){
		var reload = false;
		if( id_preset && id_preset > 0 ){
			if( service.user.presets && service.user.presets[ id_restaurant ] ){
				if( service.user.presets[ id_restaurant ].id_preset != id_preset ){
					reload = true;
				}
			} else {
				reload = true;
			}
		}
		if( reload ){
			service.updateInfo( null, callbackReload );
		}
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
		if( service.form.password ){
			return service.form.password != '';
		}
		return false;
	}

	return service;
} );


// AccountHelpService service
NGApp.factory( 'AccountHelpService', function( $http, $rootScope, AccountService, AccountModalService ){

	// It starts invisible
	var service = {
			visible : false,
			success : {
				visible : false,
				facebook : {
					visible : false
				}
			}
		};

	// array with the errors
	service.errors = [];

	// reset the erros
	service.errorReset = function(){
		service.errors = [];
	}

	var account = AccountService;
	var modal = AccountModalService;

	service.show = function( show ){
		service.success.visible = false;
		account.errorReset();
		service.errorReset();
		service.visible = show;
		modal.header = !show;
		if( show ){
			service.reset();
			$rootScope.focus( '.help-email' );
		} else {
			$rootScope.focus( '.signin-email' );
		}
	}

	service.reset = function(){
		service.errorReset();
		service.success.visible = false;
		service.success.facebook.visible = false;
		service.sendingHelpForm = false;
	}

	service.sendForm = function(){
		service.errorReset();
		if( !account.isValidEmailPhone() ){
			service.errors.push( AccountService.errorsList[ 'enter-email-phone' ] );
			$rootScope.focus( '.help-email' );
			return;
		}

		var url = App.service + 'user/reset';

		service.sendingHelpForm = true;

		$http( {
			method: 'POST',
			url: url,
			data: $.param( { 'email' : account.form.email } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					if( data.error ){
						if( data.error == 'user is not registered' ){
							service.errors.push( AccountService.errorsList[ 'not-registered' ] );
							$rootScope.focus( '.help-email' );
							service.sendingHelpForm = false;
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
NGApp.factory( 'AccountModalService', function( $http, $rootScope, FacebookService ){

	var service = {
		header : true,
		signin : true,
		signup : false,
		facebookLogin : false
	};

	service.facebook = FacebookService;

	service.signinOpen = function(){
		service.header = true;
		App.dialog.show( '.account-container' );
		service.toggleSignForm( 'signin' );
	}

	service.signupOpen = function(){
		service.header = true;
		App.dialog.show( '.account-container' );
		service.toggleSignForm( 'signup' );
	}

	service.facebookOpen = function(){
		service.header = false;
		App.dialog.show( '.account-container' );
		service.toggleSignForm( 'facebook' );
	}

	service.resetOpen = function(){
		App.dialog.show( '.account-reset-container' );
	}

	service.toggleSignForm = function( form ){
		service.facebook.account.errorReset();
		service.facebook.wait = false;
		service.signin = ( form == 'signin' );
		service.signup = ( form == 'signup' );
		service.facebookLogin = ( form == 'facebook' );
		if( service.signin ){
			if( !App.iOS() ){
				$rootScope.focus( '.signin-email' );
			}
		}
		if( service.signup ){
			if( !App.iOS() ){
				$rootScope.focus( '.signup-email' );
			}
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

	service.auth = function() {
		service.facebook.wait = true;

		if (window.facebookConnectPlugin) {
			window.facebookConnectPlugin.login(App.facebookScope.split(','), service.facebook.processStatus, service.facebook.processStatus);
		} else if (window.FB) {
			window.FB.login(service.facebook.processStatus, { scope: App.facebookScope });
		}
	}

	service.signout = function( callback ){
		service.facebook.signout( callback );
	}

	return service;

} );

NGApp.factory( 'AccountSignOut', function( $http, $rootScope, $location, AccountFacebookService, AccountService, MainNavigationService ){

	var service = {};

	service.facebook = AccountFacebookService;

	// When the user confirms the signout
	service.signoutConfirmed = function( button ){
		// cordova send the button user pressed 1 == OK
		if( button != 1 ){
			return;
		}
		// Force to remove the cookies
		$.each(['token', 'location', 'locsv3', 'PHPSESSID', 'fbtoken', 'userEntered'], function(index, value) {
			$.totalStorage(value, null);
			$.cookie(value, null);
		});
		var signout = function() {
			// log the session out on the server
			$http.get(App.service + 'logout').success(function(data) {
				$http.get(App.service + 'config').success(function(data) {
					$rootScope.$broadcast('userAuth', data.user);
					App.processConfig(data);
				});
				// Redirect the user to location page
				if (App.isCordova) {
					MainNavigationService.link( '/splash' );
				} else {
					MainNavigationService.link( '/location' );
				}
			}).error(function() {
				console.debug('couldnt log out',arguments)
			});
			AccountService.reset();
		};

		if ( service.facebook.facebook.logged || service.facebook.facebook.account.user.facebook ) {
			try {
				service.facebook.signout();
			} catch(e) {
				console.log('e',e);
			}
			signout();
		} else {
			signout();
		}
	}

	// perform a logout
	service.signout = function() {
		if ( App.confirm('Are you sure you want to log out?', 'Crunchbutton', service.signoutConfirmed ) ) {
			service.signoutConfirmed( 1 );
		}
	}
	return service;
} );


NGApp.factory( 'AccountResetService', function( $http, $location, AccountService ){

	var service = {
		step : 1,
		form : { code : '', password : '' },
		success : false,
		error : false
	};

	// array with the errors
	service.errors = [];

	// reset the erros
	service.errorReset = function(){
		service.errors = [];
	}

	service.form.code = $location.path().replace( '/reset', '' );
	service.form.code = service.form.code.replace( '/', '' );
	service.validateCode = function(){
		service.errorReset();
		service.form.code = $.trim( service.form.code );
		if( service.form.code == '' ){
			service.errors.push( AccountService.errorsList[ 'enter-code' ] );
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
							service.errors.push( AccountService.errorsList[ 'code-invalid' ] );
						}
						if( data.error == 'expired code' ){
							service.errors.push( AccountService.errorsList[ 'code-expired' ] );
						}
						$( '#account-reset-code' ).focus();
						return;
					} else {
						if( data.success = 'valid code' ){
							service.step = 2;
						}
					}
			}	);
	}

	service.changePassword = function(){
		service.errorReset();
		service.form.password = $.trim( service.form.password );
		if( service.form.password == '' ){
			service.errors.push( AccountService.errorsList[ 'enter-password' ] );
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
						service.errors.push( AccountService.errorsList[ 'code-invalid' ] );
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
