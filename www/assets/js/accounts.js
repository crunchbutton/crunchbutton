/* CONTROLLERS */
function AccountModalHeaderCtrl( $scope, $http, AccountModalService ) {
	$scope.modal = AccountModalService;
}

function AccountSignInCtrl( $scope, $http, AccountModalService, AccountService, AccountHelpService ) {

	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	$scope.help = AccountHelpService;

}

function AccountSignUpCtrl( $scope, $http, AccountModalService, AccountService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;

	// Watch the variable user
	$scope.$watch( 'account.user', function( newValue, oldValue, scope ) {
		$scope.account.user = newValue;
		if( newValue ){
			$scope.modal.header = false;
		}
	});
}

/* SERVICES */

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

// Facebook button compoment
NGApp.directive( 'facebookSigninButton', function ( AccountFacebookService ) {
	return {
		restrict: 'A',
		templateUrl: 'view/account.facebook.html',
		scope: {
			title: '@'
		},
		controller: function ( $scope ) {
			$scope.facebook = AccountFacebookService;
			console.log('$scope.facebook',$scope.facebook);
		}
	};;
});

/*
// Validate login
NGApp.directive( 'validateLogin', function () {
		return {
			restrict: 'A',
			require: 'ngModel',
				link: function(scope, elm, attrs, ctrl){
					console.log('ctrl',ctrl);
					ctrl.$parsers.unshift( function( value ){
						valid = true;
						if( value == '' ){
							valid = false;
						} else {
							valid = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( value );
						}

						// var valid = App.phone.validate( value );
						// if( !valid ){
							// Valid email
							
						// }
						ctrl.$setValidity( 'validateLogin', valid );
						return valid ? undefined : value;
					} );
				}
		};
} );


// Validate login
NGApp.directive( 'validateEmpty', function () {
		return {
			restrict: 'A',
			require: 'ngModel',
				link: function(scope, elm, attrs, ctrl){
					ctrl.$parsers.unshift( function( value ){
						valid = ( value != '' );
						ctrl.$setValidity( 'validateEmpty', valid );
						return valid ? undefined : value;
					} );
				}
		};
} );
*/
/**
 * event binding
 */



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

