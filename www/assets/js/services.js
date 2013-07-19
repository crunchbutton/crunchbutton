// Account services

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
NGApp.factory( 'AccountModalService', function( $http ){
	
	var service = {
		header : true,
		signin : true,
		signup : false
	};

	service.toggleSignForm = function( form ){
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
		if( App.config.user.id_user && $.trim( App.config.user.id_user ) != '' ){
			service.user = App.config.user;
		}
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


// AccountFacebookService service
NGApp.factory( 'AccountFacebookService', function( $http, AccountService ){
	
	var service = {
			wait : false,
			running : false,
			logged : false,
			doAuth : true,
			error : { unknown : false, userExists : false }
		};

	service.account = AccountService;

	service.auth = function(){
		FB.login( service.process, { scope: App.facebookScope } );
		service.wait = true;
	}

	service.signout = function( call ){
		FB.logout( call() );
	}

	service.process = function( session ){
		if ( session.status === 'connected' && session.authResponse ) {

			if( session.authResponse.accessToken ){
				App.facebook.registerToken( session.authResponse.accessToken );	
			}

			service.logged = true;
			App.log.account( { 'userID' : session.authResponse.userID} , 'facebook login' );

			if( service.doAuth ){
				FB.api( '/me', { fields: 'name' }, function( response ) {
					if ( response.error ) {
						App.log.account( { 'userID' : session.authResponse.userID, 'error' : response.error } , 'facebook name error' );
						service.error.unknown = true;
						return;
					}

					App.log.account( { 'userID' : session.authResponse.userID, 'response' : response, 'shouldAuth' : service.doAuth, 'running' : service.running } , 'facebook response' );
					if( response.id ){
						service.doAuth = false;

						if( !service.running ){
							service.running = true;
							App.log.account( { 'userID' : session.authResponse.userID, 'running' : service.running } , 'facebook running' );

							// Just call the user api, this will create a facebook user
							var url = App.service + 'user/facebook';

							$http( {
								method: 'GET',
								url: url,
								} ).success( function( data ) {

									App.log.account( { 'userID' : session.authResponse.userID, 'running' : App.signin.facebook.running, 'data' : data } , 'facebook ajax' );
									App.signin.facebook.running = true;
									if( data.error ){
										if( data.error == 'facebook id already in use' ){
											// Log the error
											App.log.account( { 'error' : data.error } , 'facebook error' );
											service.error.unknown = true;
										}
									} else {

										App.processConfig( null, data );
										service.account.user = data;

										if( App.giftcard.callback ){
											App.giftcard.callback();	
										}
										App.signin.manageLocation();
									}
									// Closes the dialog
									$.magnificPopup.close();

									App.log.account( { 'userID' : session.authResponse.userID, 'currentPage' : App.currentPage } , 'facebook currentPage' );

									// If the user is at the restaurant's page - reload it
									if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
										App.page.restaurant( App.restaurant.permalink );
									}
									if( App.currentPage == 'orders' ){
										App.page.orders()								
									}
								}	);
						}
					} else {
						service.error.unknown = true;
					}
				});
			}
		}

	}

	return service;

} );

// TODO: fix ugly thing!
NGApp.factory( 'AccountSignOut', function( $http, AccountFacebookService ){

	var service = {};

	service.facebook = AccountFacebookService;

	service.do = function(){

		if (confirm( 'Confirm sign out?')){
			// Force to remove the cookies
			$.each( [ 'token', 'location', 'PHPSESSID' ], function( index, value ){
				$.cookie( value, null );
			} );
		
			var signout = function(){
				var url = App.service + 'logout';
				$http( { method: 'GET', url: url } ).success( function( data ) { location.href = '/'; } );
			};
		
			if( service.facebook.logged ){
				service.facebook.signout( function(){ signout() } );
			} else {
				signout();
			}
		}
	}

	return service;

} );

// TODO: this service is outdated as its controller
// RecommendRestaurantService service
NGApp.factory( 'RecommendRestaurantService', function( $http ){

	var service = {
		api : {
			add : 'suggestion/restaurant',
			relateuser : 'suggestion/relateuser'
		}
	};

	var formSent = false;
	var recommendations = [];

	service.changeFormStatus = function( status ){
		formSent = status;
	}
	
	service.getFormStatus = function(){
		return formSent;
	}

	service.addRecommendation = function( id ){
		recommendations.push( id );
	}

	service.getRecommendations = function(){
		if( recommendations.length > 0 ){
			return recommendations;
		}
		return false;
	}

	service.relateUser = function(){
		if( service.getRecommendations() ){
			var url = App.service + service.api.relateuser;
			$.each( recommendations, function(index, value) {
				var id_suggestion = value;
				var data = { id_suggestion : id_suggestion, id_user : App.config.user.id_user };
				$http.post( url , data );
			} );
			recommendations = false;
		}
	}

	return service;
} );