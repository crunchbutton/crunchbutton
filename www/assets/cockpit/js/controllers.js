
NGApp.controller('DefaultCtrl', function ($rootScope, $scope, $http, $location, $routeParams, MainNavigationService, AccountService) {


	var redirect = function() {
		// redirect to login if there is no user
		if (!AccountService.user || !AccountService.user.id_admin) {
			MainNavigationService.link('/login', 'instant');
			return;
		}

		var id_order = $location.path().replace( '/', '' );
		if( !isNaN( parseInt( id_order ) ) ){
			MainNavigationService.link('/drivers/order/' + id_order, 'instant');
		} else {
			if (App.isPhoneGap && !$.totalStorage('isDriverWelcomeSetup')) {
				setTimeout(function(){
					MainNavigationService.link('/drivers/welcome', 'instant');
					$rootScope.$apply();
				},100);
				return;
			}

			if (AccountService.user.permissions.GLOBAL) {
				MainNavigationService.link('/home', 'instant');
			} else if (AccountService.isRestaurant) {
				MainNavigationService.link('/restaurant/order/placement/dashboard', 'instant');
			} else if (AccountService.isDriver) {
				MainNavigationService.link('/drivers/orders', 'instant');
			} else {
				MainNavigationService.link('/login', 'instant');
			}
		}
	};

	// wait for login to complete
	if (!AccountService.init) {
		$scope.$on('userAuth', redirect);
	} else {
		redirect()
	}
});

NGApp.controller('HomeCtrl', function ( $scope) {} );

NGApp.controller('MainHeaderCtrl', function ( $scope) {} );

NGApp.controller('SideMenuCtrl', function ($scope, $rootScope, AccountService) {
	$scope.setupPermissions = function() {}
	$scope.menu = {};

	//$scope.menu.toggle = $.totalStorage('menu.toggle');

	var fixToggle = function() {
		if (!AccountService.user || !AccountService.user.permissions) {
			$scope.menu.toggle = '';
		} else if (AccountService.user.permissions.GLOBAL) {
			$scope.menu.toggle = 'admin';
		} else if (AccountService.isDriver) {
			$scope.menu.toggle = 'driver';
		} else if(AccountService.isMarketingRep) {
			$scope.menu.toggle = 'marketing-rep';
		}
	};

	$rootScope.$on('userAuth', function(e, data) {
		fixToggle();
	});

	//$.totalStorage('menu.toggle', $scope.menu.toggle);

	/*
	$scope.$watch('menu.toggle', function() {
		$.totalStorage('menu.toggle', $scope.menu.toggle);
	});
	*/

	fixToggle();
});

NGApp.controller('InfoCtrl', function ($scope) {

});

NGApp.controller('LegalCtrl', function ($scope) {
	var join = 'moc.nottubhcnurc@nioj'.split('').reverse().join('');
	var goodbye = 'moc.nottubhcnurc@eybdoog'.split('').reverse().join('');
	$scope.join = join;
	$scope.goodbye = goodbye;
});

NGApp.controller('LoginCtrl', function($rootScope, $scope, AccountService, MainNavigationService) {

	$rootScope.hasBack = false;

	$scope.loggingIn = false;

	var l;
	setTimeout( function(){ l = Ladda.create( $( '.button-login .ladda-button' ).get( 0 ) ); }, 700 );

	$scope.newuser = !$.totalStorage('hasLoggedIn');

	$scope.login = function() {
		if( !$scope.username ){
			App.alert('Please enter your username', '', false, function() {
				if (!App.isPhoneGap) {
					$rootScope.focus('[name="username"]');
				}
			});
			return;
		}
		if( !$scope.password ){
			App.alert('Please enter your password', '', false, function() {
				if (!App.isPhoneGap) {
					$rootScope.focus('[name="password"]');
				}
			});
			return;
		}
		$scope.loggingIn = true;
		if( l ){
			l.start();
		}
		AccountService.login( $scope.username, $scope.password, function( status ) {
			if( status ){
				var redirect_to = $.totalStorage( 'redirect_to' );
				if( redirect_to ){
					$.totalStorage( 'redirect_to', null );
					MainNavigationService.link( redirect_to );
				} else {
					MainNavigationService.link( '/' );
				}
			} else {
				$scope.error = true;
				if (!App.isPhoneGap) {
					$rootScope.focus('[name="username"]');
				}
			}
			$scope.loggingIn = false;
			if( l ){
				l.stop();
			}
		} );
	}
	// needs to be updated when the html is
	$scope.welcome = Math.floor( ( Math.random() * 8 ) + 1 );
});

NGApp.controller( 'ProfileCtrl', function ($scope) {});

NGApp.controller( 'ProfilePasswordCtrl', function ($scope, ProfileService) {

	$scope.password = {};

	$scope.isSaving = false;

	$scope.save = function(){
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isSaving = false;
			return;
		}

		ProfileService.change_password( $scope.password, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error , 'error' );
			} else {
				App.alert( 'Your password was changed!' );
				$scope.submitted = false;
				$scope.isSaving = false;
				$scope.password = {};
			}
		} );
	}
});

NGApp.controller( 'NotificationAlertCtrl', function ($scope, $rootScope ) {
	$rootScope.$on('notificationAlert', function(e, title, message, fn, unselectable) {

		if (!App.isPhoneGap) {
			$(':focus').blur();
		}

		var complete = function() {
			$rootScope.closePopup();
			if (typeof fn === 'function') {
				fn();
			}
		};

		$scope.unselectable = unselectable;

		if ($scope.$$phase) {
			$scope.alertTitle = title;
			$scope.message = message;
			$scope.complete = complete;
			App.dialog.show('.notification-alert-container');

		} else {
			$rootScope.$apply(function(scope) {
				scope.alertTitle = title;
				scope.message = message;
				$scope.complete = complete;
				App.dialog.show('.notification-alert-container');
			});
		}
	});
});

NGApp.controller( 'AgreementBoxCtrl', function ($scope, $rootScope ) {
	$rootScope.$on('agreementBox', function(e, title, message, success, fail ) {

		$scope.agree = false;

		$(':focus').blur();

		var complete = function() {
			$rootScope.closePopup();
			if (typeof success === 'function') {
				success();
			}
		};

		var cancel = function() {
			$rootScope.closePopup();
			if (typeof fail === 'function') {
				fail();
			}
		};

		if ($scope.$$phase) {
			$scope.confirmationTitle = title;
			$scope.message = message;
			$scope.complete = complete;
			$scope.cancel = cancel;
			App.dialog.show('.agreement-container');

		} else {
			$rootScope.$apply(function(scope) {
				scope.confirmationTitle = title;
				scope.message = message;
				scope.complete = complete;
				scope.cancel = cancel;
				App.dialog.show('.agreement-container');
			});
		}
	});
});

NGApp.controller( 'NotificationConfirmCtrl', function ($scope, $rootScope ) {
	$rootScope.$on('notificationConfirm', function(e, title, message, success, fail, buttons) {

		$(':focus').blur();

		var complete = function() {
			$rootScope.closePopup();
			if (typeof success === 'function') {
				success();
			}
		};

		var cancel = function() {
			$rootScope.closePopup();
			if (typeof fail === 'function') {
				fail();
			}
		};

		if ($scope.$$phase) {
			$scope.alertTitle = title;
			$scope.message = message;
			$scope.complete = complete;
			$scope.cancel = cancel;
			$scope.buttonOk = 'Ok';
			$scope.buttonCancel = 'Cancel';
			if( buttons ){
				buttons = buttons.split(',');
				if( buttons.length == 2 ){
					$scope.buttonOk = buttons[0];
					$scope.buttonCancel = buttons[1];
				}
			}
			App.dialog.show('.notification-confirm-container');

		} else {
			$rootScope.$apply(function(scope) {
				scope.alertTitle = title;
				scope.message = message;
				scope.complete = complete;
				scope.cancel = cancel;
				scope.buttonOk = 'Ok';
				scope.buttonCancel = 'Cancel';
				if( buttons ){
					buttons = buttons.split(',');
					if( buttons.length == 2 ){
						scope.buttonOk = buttons[0];
						scope.buttonCancel = buttons[1];
					}
				}
				App.dialog.show('.notification-confirm-container');
			});
		}
	});
});

NGApp.controller( 'NotificationNewOrderCtrl', function ($scope, $rootScope, $route, MainNavigationService, DriverOrdersService ) {

	$rootScope.$on('notificationNewOrder', function(e, message, link) {

		$(':focus').blur();

		$scope.accept = function(){
			var id_order = link.replace( '/drivers/order/', '' );
			DriverOrdersService.accept( id_order,
				function( json ){
					if( json.status ) {
						$rootScope.$broadcast('updateHeartbeat')
						$route.reload();
					} else {
						$scope.unBusy();
						var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
						App.alert( 'Oops!\n It seems this order was already accepted ' + name + '!'  );
						$rootScope.$broadcast('updateHeartbeat')
					}
					$rootScope.closePopup();
				}
			);
		};

		$scope.view = function(){
			MainNavigationService.link(link);
			$rootScope.closePopup();
		};

		$scope.close = function(){
			$rootScope.closePopup();
		};

		if ($scope.$$phase) {
			$scope.message = message;
			App.dialog.show('.notification-new-order-container');
		} else {
			$rootScope.$apply(function(scope) {
				scope.message = message;
				App.dialog.show('.notification-new-order-container');
			});
		}
	});
});

NGApp.controller( 'CallText', function ($scope, $rootScope) {

	$rootScope.$on('callText', function(e, num) {
		openModal( num );
	});

	$rootScope.$on('textNumber', function(e, num) {
		openModal( num );
		$scope.hideSMSBox = false;
		$scope.complete = function( json ){
			if( json.success ){
				setTimeout(function() { App.alert( 'Text messages sent!' ); }, 10 );
			} else {
				if( json.error ){
					setTimeout(function() { App.alert( json.error ); }, 10 );
				}
			}
		};
	});

	var openModal = function( num ){

		$(':focus').blur();

		if( angular.isArray( num ) ){
			$scope.staffList = true;
		} else {
			$scope.staffList = false;
		}

		$scope.number = num;
		$scope.complete = $rootScope.closePopup;
		App.dialog.show('.notification-call-text-container');

		$scope.hideCallBox = true;
		$scope.hideSMSBox = true;
	}

	// variables to controll the template '/assets/view/support-phone.html'
	// when it is called by a modal
	$scope.isModal = true;
	$scope.hideCallBox = true;
	$scope.hideSMSBox = true;

});

NGApp.filter('capitalize', function() {
	return function(input, scope) {
		if (input == null) {
			return null;
		}
		if (input!=null) {
			input = input.toLowerCase();
		}
		return input.substring(0,1).toUpperCase()+input.substring(1);
	}
});
