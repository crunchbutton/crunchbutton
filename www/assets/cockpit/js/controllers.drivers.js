NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/resources', {
			action: 'community-resources',
			controller: 'CommunityResourcesDriverCtrl',
			templateUrl: '/assets/view/drivers-resources.html'

		})
}]);

NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/resources', {
			action: 'community-resources',
			controller: 'CommunityResourcesDriverCtrl',
			templateUrl: '/assets/view/drivers-resources.html'

		})
}]);

NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/drivers/community', {
			action: 'drivers-community',
			controller: 'DriversCommunityCtrl',
			templateUrl: '/assets/view/drivers-community.html'

		})
		.when('/drivers/notification/:id?', {
			action: 'drivers-notification',
			controller: 'DriversNotificationCtrl',
			templateUrl: '/assets/view/drivers-notifications.html'

		})
}]);

NGApp.controller( 'CommunityResourcesDriverCtrl', function ($rootScope, $scope, ResourceService ) {
	ResourceService.driver(function(data) {
		$scope.communities = data;
	});
} );

NGApp.controller( 'DriversCommunityCtrl', function ($scope, $filter, DriverCommunityService ) {

	$scope.loading = true;
	$scope.show_form = false;

	$scope.id_community = null;

	var load = function(){
		$scope.communities = null;
		DriverCommunityService.status( function( json ){
			$scope.communities = json;
			$scope.loading = false;
			if( !$scope.id_community ){
				$scope.id_community = $scope.communities[ 0 ].id_community;
			}
		} );
	}

	var community = function( id_community ){
		for( x in $scope.communities ){
			if( $scope.communities[ x ].id_community == id_community ){
				$scope.community = $scope.communities[ x ];
			}
		}
	}

	$scope.$watch( 'id_community', function( newValue, oldValue, scope ) {
		if( newValue ){
			community( newValue );
		}
	});

	$scope.sendTextMessage = function(){
		if( $scope.formTextDrivers.$invalid ){
			$scope.formTextDriversSubmitted = true;
			return;
		}

		$scope.isSendingTextMessage = true;
		var numbers = new Array;

		if( $scope.community.drivers.length ){
			angular.forEach( $scope.community.drivers, function(staff, key) {
				if(staff.text){
					numbers.push(staff.phone);
				}
			} );
		}

		if (!numbers.length) {
			App.alert( 'Please, select at least one driver!' );
			return;
		}

		DriverCommunityService.textMessage( {id_community: $scope.id_community, numbers: numbers, message: $scope.community.text_message }, function( json ){
			if ( json.error ) {
				App.alert( 'Error sending text message!' );
			} else {
				App.alert( 'Text sent!' );
			}
			load();
			$scope.isSendingTextMessage = false;
		} );
	}

	$scope.updateDriversCount = function(){
		if( $scope.community.drivers.length ){
			var count = 0;
			angular.forEach( $scope.community.drivers, function(staff, key) {
				if(staff.text){
					count++;
				}
			} );
			$scope.driversCount = count;
		}
	}

	$scope.close = function(){
		if( $scope.formClose.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSavingClose = true;
		DriverCommunityService.close( { id_community: $scope.id_community, how_long: $scope.community.how_long, reason: $scope.community.reason }, function( json ){
			if ( json.error ) {
				App.alert( 'Error, the community is not open!' );
			} else {
				App.alert( 'The community is closed!' );
			}
			load();
			$scope.isSavingClose = false;
		} );
	}

	$scope.open = function(){
		if( $scope.formOpen.$invalid ){
			$scope.submittedOpen = true;
			return;
		}

		var hour = $filter('date')( $scope.community.hour, 'H:mm' );

		$scope.isSavingOpen = true;
		DriverCommunityService.open( { id_community: $scope.id_community, hour: hour }, function( json ){
			if ( json.error ) {
				App.alert( 'Error, the community is not closed!' );
			} else {
				App.alert( 'The community is open!' );
			}
			load();
			$scope.isSavingOpen = false;
		} );
	}

	load();

} );



NGApp.controller('DriversDashboardCtrl', function ( $scope, MainNavigationService, DriverOrdersService ) {

	//This links to orders page for pending orders
	$scope.showOrderList = function(){
		MainNavigationService.link('/drivers/order/');
	}

	DriverOrdersService.acceptedOrders();
	DriverOrdersService.pickedupOrders();
	DriverOrdersService.revThisShift();
	DriverOrdersService.revLastShift();
	DriverOrdersService.timeLastShift();
	DriverOrdersService.timeThisShift();
	DriverOrdersService.outstandingOrders();
	//Yell at driver if there is an outstanding undelivered order.

});



NGApp.controller('DriversOrderNavCtrl', function ( $scope, $rootScope, $location, $timeout, DriverOrdersViewService) {
	$scope.oc = DriverOrdersViewService;

	$rootScope.$on('$routeChangeSuccess', function (event, $currentRoute, $previousRoute) {
		if( $location.url() == '/drivers/orders' ){
			$timeout( function(){
				$scope.oc.close_banner();
			}, 3000 );
		}
	});
});

NGApp.controller( 'DriversOrderSignatureCtrl', function ( $scope, $rootScope, $routeParams, DriverOrdersService ) {

	$scope.ready = false;

	if( $rootScope.menuToggled ){
		$rootScope.menuToggle();
	}

	var load = function(){

		$scope.email = { send: false, email: null };

		$scope.show_form = false;
		DriverOrdersService.getReceipt( $routeParams.id, function( data ){
			$scope.order = data;

			if( $scope.order.require_signature && !$scope.order.has_signature ){
				setTimeout( function(){
					var signature = $( '#signature' ).jSignature( { backgroundColor: "rgb(229, 229, 229)" });
				}, 2000 );
			}

			$scope.ready = true;
			DriverOrdersService.hasSignature( $routeParams.id, function( data ){
				if( !data.signature ){
					$scope.show_form = true;
				}
				if( data.email ){
					$scope.email.email = data.email;
				}
			} );
		} );
	}

	$scope.clear = function(){
		$( '#signature' ).jSignature( 'reset' );
	}

	$scope.save = function(){

		var has = $( '#signature' ).jSignature( 'getData', 'native');
		if( has.length == 0 ){
			App.alert( 'Please provide a signature!' );
			return;
		}

		if( $scope.email.send && !$scope.email.email ){
			App.alert( 'Please enter your email!' );
			return;
		}

		if( $scope.email.send && $scope.email.email ){
			if ( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( $scope.email.email ) ){
				App.alert( 'Please enter a valid email!' );
				return;
			}
		}

		var success = function(){

			var data = {};
			var datapair = $( '#signature' ).jSignature( 'getData', 'svgbase64' );
			data.signature = 'data:' + datapair[0] + ',' + datapair[1];
			data.id_order = $routeParams.id;
			data.sent_email = $scope.email.send;
			data.email = $scope.email.email;
			DriverOrdersService.signature( data, function( result ){
				load();
			} );

		}
		App.confirm( 'Save signature?', 'Confirm?', success, function(){ App.dialog.close(); } , 'Ok,Cancel', true);
	}

	load();

});

NGApp.controller('DriversOrderCtrl', function ( $scope, $location, $rootScope, $routeParams, DriverOrdersService, DriverOrdersViewService, AccountService, MainNavigationService) {

	$rootScope.navTitle = '#' + $routeParams.id;
	$scope.ready = false;
	$scope.oc = DriverOrdersViewService;
	DriverOrdersViewService.prep();

	$scope.nextOrder = function() {
		//console.log(arguments);
	};

	$scope.iOS = App.iOS();
var serv = angular.element( 'html' ).injector().get( 'AccountService' );
	var load = function() {
		DriverOrdersViewService.load(function(){
			if(	DriverOrdersViewService.order &&
					DriverOrdersViewService.order.driver &&
					DriverOrdersViewService.order.driver.id_admin &&
					DriverOrdersViewService.order.driver.id_admin != AccountService.user.id_admin){
				setTimeout(function(){
					App.alert('This order has already been accepted by ' + DriverOrdersViewService.order.driver.name, 'Order has already been accepted.', false, function(){
						MainNavigationService.link('/drivers/orders');
					});
				}, 1500);
			}
		});
		watching = null;
	};

	var watching = null;

	if (!AccountService.init) {
		// we got here before the auth service was complete.
		watching = $rootScope.$on('userAuth', load);
	}

	$scope.randomFooter = ( $routeParams.id % 2 );

	load();
	setTimeout(function() {
		DriverOrdersViewService.textLoader = Ladda.create($('#textCustomer5').get(0));
	}, 1000 );

});

NGApp.controller('DriversOrdersCtrl', function ( $scope, $rootScope, DriverOrdersService, MainNavigationService, AccountService, $location ) {

	// #5413
	$scope.showOrders = true;

	var showAll = $.totalStorage('driver-orders-show');

	if (!showAll) {
		showAll = false;
	} else {
		showAll = $.totalStorage('driver-orders-show') == 'all' ? true : false;
	}
	$scope.iOS = App.iOS();
	$scope.show = {
		all: showAll
	};
	$scope.ready = false;

	$scope.$watch('show.all', function() {
		$.totalStorage('driver-orders-show', $scope.show.all ? 'all' : 'mine');
	});

	$scope.filterOrders = function( order ){
		if ($scope.show.all) {
			return true;
		} else {
			if (order.status.status != 'canceled' && ( !order.status.driver || order.status.driver.id_admin == $scope.account.user.id_admin )){
				return true;
			}
		}
		return false;
	}

	$scope.changed = function() {
		$rootScope.$broadcast('updateHeartbeat');
		$scope.update();
	};

	$scope.update = function() {

		DriverOrdersService.list(function(data) {
			$scope.driverorders = data;
			for (var x in $scope.driverorders) {
				if( $scope.driverorders[x].address ){
					$scope.driverorders[x].addressFirstLine = $scope.driverorders[x].address.split(',').shift();
				}
			}
			setTimeout( function(){ $scope.unBusy(); }, 500 );
			$scope.ready = true;
		});
	};

	$scope.signature = function( id_order ) {
		$scope.link( '/drivers/order/signature/' + id_order );
	}

	$scope.accept = function( id_order ) {

		var accept = function( create_shift ){
			$scope.makeBusy();
			params = { id_order: id_order };
			if( create_shift ){
				params.create_shift = true;
			}
			DriverOrdersService.accept( params,
				function( json ){
					if( params.create_shift ){
						$rootScope.$broadcast( 'adminWorking', true );
					}
					if( json.status ) {
						$scope.changed();
					} else {
						$scope.unBusy();
						var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
						App.alert( 'Oops!\n It seems this order was already accepted ' + name + '!'  );
						$scope.changed();
					}
				}
			);
		}

		if( !AccountService.user.working ){
			var acceptAndCreateShift = function(){
				accept( true );
			}
			var acceptAndDontCreateShift = function(){
				accept( false );
			}
			App.confirm( 'You are not currently on shift. Would you like to deliver for the next hour?', 'Confirm?', acceptAndCreateShift, acceptAndDontCreateShift, 'Accept & deliver for next hour,Accept only this order', true );
		} else {
			accept();
		}

	};

	$scope.pickedup = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.pickedup( id_order, $scope.changed);
	};

	$scope.delivered = function( id_order ) {
		$scope.makeBusy();
		DriverOrdersService.delivered( id_order, $scope.changed);
	};

	$scope.showOrder = function( id_order ){
		MainNavigationService.link( '/drivers/order/' + id_order );
	}

	$rootScope.$watch('totalDriverOrders', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count) {
			$scope.update();
		}
	});

	$scope.update();
} );

NGApp.controller( 'DriversSummaryCtrl', function ( $scope, DriverService, $routeParams, StaffService, ViewListService ) {

	angular.extend( $scope, ViewListService );

	$scope.isMobile = App.isMobile();

	if( $scope.account && $scope.account.user && $scope.account.user.id_admin ){
		$scope.id_admin = parseInt( $scope.account.user.id_admin );
	}

	if( $scope.account && $scope.account.isAdmin ){
		if( $routeParams.id ){
			$scope.id_admin = parseInt( $routeParams.id );
		}
	}

	DriverService.payRollInfo( function( json ){
		$scope.payRollInfo = json.payment;
	} );


	$scope.view( {
		scope: $scope,
		watch: { type: 'all' },
		update: function() {
			$scope.query.id_admin = $scope.id_admin;
			DriverService.summary( $scope.query, function( data ){
				$scope.summary = data;
				$scope.complete( data );
			} );
		}
	} );

} );


NGApp.controller( 'DriversPaymentsCtrl', function ( $scope, DriverService, $routeParams, MainNavigationService) {

	MainNavigationService.link('/drivers/summary/' + $routeParams.id);

	/*

	$scope.ready = false;
	$scope.filter = false;

	var drivers = function(){
		DriverService.listSimple( function( data ){
			$scope.drivers = data;
		} );
	}

	$scope.list = function(){
		DriverService.payments( $scope.id_admin, function( json ){
			$scope.result = json;
			$scope.ready = true;
		} );
	}

	$scope.show_payment = function( id_payment ){
		$scope.navigation.link( '/drivers/payment/' + id_payment );
	}

	// Just run if the user is loggedin
	if( $scope.account.isLoggedIn() ){
		$scope.id_admin = parseInt( $scope.account.user.id_admin );
		if( $scope.account.isAdmin ){
			drivers();
			if( $routeParams.id ){
				$scope.id_admin = parseInt( $routeParams.id );
			}
		}
		$scope.list();
	}
	*/
});

NGApp.controller( 'DriversPexCardCtrl', function ( $scope, PexCardService ) {

	$scope.submitted = false;
	$scope.isSearching = false;
	$scope.isActivating = false;
	$scope.activateOption = true;

	$scope.status = PexCardService.status;

	$scope.active = function(){
		if( $scope.card.id ){
			$scope.isActivating = true;
			PexCardService.driver_active( $scope.card.id, function( json ){
				if( json.success ){
					$scope.activateOption = false;
					$scope.crunchbutton_card_id = null;
					$scope.last_four_digits = null;
					$scope.card = null;
					App.alert( 'Your PEX Card is Active!', 'success' );
				} else {
					App.alert( 'Error activating card!', 'error' );
					$scope.isActivating = false;
				}

			} );
		}
	}

	$scope.search = function() {

		$scope.card = null;
		$scope.activateOption = true;

		if( $scope.isSearching ){
			return;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isSearching = false;
			return;
		}

		$scope.isSearching = true;
		$scope.isActivating = false;

		var data = { 'crunchbutton_card_id': $scope.crunchbutton_card_id, 'last_four_digits' : $scope.last_four_digits };

		PexCardService.driver_search( data,
			function( json ){
				$scope.isSearching = false;
				$scope.submitted = false;
				if( json.id ){
					$scope.card = json;
				} else {
					App.alert( json.error, 'error' );
				}
			}
		);
	};

} );

NGApp.controller( 'DriversPaymentCtrl', function ( $scope, DriverService, $routeParams ) {

	$scope.ready = false;
	$scope.schedule = true;

	load = function(){
		DriverService.payment( function( json ){
			$scope.result = json;
			if( json.pay_type == DriverService.PAY_TYPE_REIMBURSEMENT ){
				$scope.pay_type_reimbursement = true;
			} else {
				$scope.pay_type_payment = true;
			}
			$scope.ready = true;
			$scope.unBusy();
		} );
	}

	load();
});



NGApp.controller( 'DriversShiftsCtrl', function ( $scope, DriverShiftsService ) {

	$scope.show = { all : true };
	$scope.ready = false;

	$scope.filterShifts = function( shift ){
		if( $scope.show.all ){
			return true;
		} else {
			if( shift.mine ){
				return true;
			}
		}
		return false;
	}

	$scope.list = function(){
		DriverShiftsService.list( function( data ){
			DriverShiftsService.groupByDay( data, function( data ){
				$scope.drivershifts = data;
				$scope.ready = true;
			} );
		} );
	}

	$scope.schedules = function(){
		$scope.navigation.link( '/drivers/shifts/schedule' );
	}


	$scope.list();

} );

NGApp.controller( 'DriversShiftsScheduleRatingCtrl', function ( $scope, $rootScope, DriverShiftScheduleRatingService ) {

	var isSaving = false;

	$scope.ready = false;

	var list = function(){
		DriverShiftScheduleRatingService.list( function( data ){
			process( data );
			$scope.ready = true;
		} );
	}

	$scope.shiftsToWorkFrom = [];
	for( var i = 0; i <= 10; i++ ){
		$scope.shiftsToWorkFrom.push( { value: i, label: i } );
	}

	$scope.shiftsToWorkTo = [];
	for( var i = 0; i <= 10; i++ ){
		$scope.shiftsToWorkTo.push( { value: i, label: i } );
	}
	$scope.shiftsToWorkTo.push( { value: 100, label: 'As many as possible!' } );

	$scope.options = { shifts_from: 1, shifts_to: 1 };

	var process = function( data ){
		$scope.available = 0;
		$scope.period = data.info.period;
		$scope.shifts = data.results;
		setTimeout( function(){
			$rootScope.$apply(function() {
				$scope.options = data.options;
			});
		}, 200 );

	}

	$scope.fixOptionTo = function(){
		$scope.shiftsToWorkTo = [];
		for( var i = $scope.options.shifts_from; i <= 10; i++ ){
			$scope.shiftsToWorkTo.push( { value: i, label: i } );
		}
		$scope.shiftsToWorkTo.push( { value: 100, label: 'As many as possible!' } );
		if( $scope.options && $scope.options.shifts_from > $scope.options.shifts_to ){
			$scope.options.shifts_to = $scope.options.shifts_from;
		}
	}

	$scope.save = function(){

		if( !$scope.options.shifts_from ){
			App.alert( 'Please select how many shifts you want to work.<br>' );
			return;
		}
		if( !$scope.options.shifts_to ){
			App.alert( 'Please select how many shifts you want to work.<br>' );
			return;
		}

		$scope.makeBusy();
		$scope.isSaving = true;
		var shifts = {};
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				var shift = $scope.shifts[ i ];
				shifts[ shift.id_community_shift ] = shift.ranking;
			}
		}
		var data = { options: $scope.options, shifts: shifts };
		DriverShiftScheduleRatingService.save( data, function( data ){
			$scope.isSaving = false;
			process( data );
			$scope.unBusy();
		} );
	}

	if( !$.totalStorage( 'hide_shift_preferences_help' ) ){
		setTimeout( function(){
			App.dialog.show('.driver-shift-preferences-help');
		}, 2000 );
	}

	list();

} );

NGApp.controller( 'DriversShiftsScheduleRatingHelpCtrl', function ( $scope, $rootScope ) {

	$scope.hide_shift_preferences_help = ( $.totalStorage( 'hide_shift_preferences_help' ) ? true : false );
console.log('$scope.hide_shift_preferences_help',$scope.hide_shift_preferences_help);
	$scope.$watch( 'hide_shift_preferences_help', function( newValue, oldValue, scope ) {

		if( newValue ){
			$.totalStorage( 'hide_shift_preferences_help', 1 );
		} else {
			$.totalStorage( 'hide_shift_preferences_help', 0 );
		}


	});
});

NGApp.controller( 'DriversShiftsScheduleCtrl', function ( $scope, DriverShiftScheduleService ) {

	var isSaving = false;

	$scope.ready = false;

	var list = function(){
		DriverShiftScheduleService.list( function( data ){
			process( data );
			$scope.ready = true;
		} );
	}

	$scope.availableToWork = [];
	for (var i = 12; i >= 1; i--) {
		$scope.availableToWork.push( { 'option': i } );
	}

	var process = function( data ){
		$scope.available = 0;
		$scope.yes = 0;
		$scope.not = 0;
		$scope.period = data.info.period;
		if( data.shifts ){
			$scope.shiftsAvailableToWork = { option: parseInt( data.shifts ) };
		}

		$scope.shifts = data.results;
		count();
	}

	var count = function(){
		$scope.available = 0;
		$scope.yes = 0;
		$scope.not = 0;
		var list = [];
		var ranking = 1;
		var selecteds = [];
		if( $scope.shifts && $scope.shifts.length ){
			$scope.shifts.ranking_next = null;
			$scope.shifts.ranking_prev = null;
			for( var i = 0; i < $scope.shifts.length; i++ ){
				var shift = $scope.shifts[ i ];
				if( shift.ranking > 0 ){
					ranking++;
					selecteds.push( i );
				}
				if( !shift.ranking && shift.ranking != 0 ){
					$scope.available++;
				}
				if( shift.ranking > 0 ){
					$scope.yes++;
				}
				if( shift.ranking == 0 ){
					$scope.not++;
				}
			}
		}

		$scope.nextRanking = ranking;
		if( selecteds && selecteds.length ){

			for( var i = 0; i < selecteds.length; i++ ){
				var shift_index = selecteds[ i ];
				var next_index = selecteds[ i + 1 ];
				var prev_index = selecteds[ i - 1 ];
				$scope.shifts[ shift_index ].ranking_next = null;
				$scope.shifts[ shift_index ].ranking_prev = null;

				if( $scope.shifts[ prev_index ] ){
					$scope.shifts[ shift_index ].ranking_prev = $scope.shifts[ prev_index ].id_community_shift;
				} else {
					$scope.shifts[ shift_index ].ranking_prev = 0;
				}

				if( $scope.shifts[ next_index ] ){
					$scope.shifts[ shift_index ].ranking_next = $scope.shifts[ next_index ].id_community_shift;
				} else {
					$scope.shifts[ shift_index ].ranking_next = 0;
				}
			}
		}
	}

	$scope.save = function(){
		$scope.makeBusy();
		$scope.isSaving = true;
		var shifts = {};
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				var shift = $scope.shifts[ i ];
				shifts[ shift.id_community_shift ] = shift.ranking;
			}
		}
		DriverShiftScheduleService.save( shifts, function( data ){
			$scope.isSaving = false;
			process( data );
			$scope.unBusy();
		} );
	}

	$scope.updateShiftsAvailable = function( option ){
		$scope.shiftsAvailableToWork = option.option;
		DriverShiftScheduleService.shiftsAvailableToWork( $scope.shiftsAvailableToWork, function( data ){
			process( data );
		} );
	}

	$scope.rankingChange = function( id_community_shift, id_community_shift_change ){
		var shift = null;
		var change = null;
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				if ( $scope.shifts[ i ].id_community_shift == id_community_shift ) {
					shift = i;
				}
				if ( $scope.shifts[ i ].id_community_shift == id_community_shift_change ) {
					change = i;
				}
			}
			if( $scope.shifts[ shift ] && $scope.shifts[ change ] ){
				var actual = $scope.shifts[ shift ].ranking;
				$scope.shifts[ shift ].ranking = $scope.shifts[ change ].ranking;
				$scope.shifts[ change ].ranking = actual;
				actual = $scope.shifts[ shift ];
				$scope.shifts[ shift ] = $scope.shifts[ change ];
				$scope.shifts[ change ] = actual;
			}
		}
		count();
		return;
		$scope.makeBusy();
		DriverShiftScheduleService.rankingChange( id_community_shift, id_community_shift_change, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	$scope.dontWantToWork = function( id_community_shift ){
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				if ( $scope.shifts[ i ].id_community_shift == id_community_shift ) {
					$scope.shifts[ i ].ranking = 0;
				}
			}
		}
		count();
		return;
		$scope.makeBusy();
		DriverShiftScheduleService.dontWantToWork( id_community_shift, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	$scope.wantToWork = function( id_community_shift ){
		if( $scope.shifts && $scope.shifts.length ){
			for( var i = 0; i < $scope.shifts.length; i++ ){
				if ( $scope.shifts[ i ].id_community_shift == id_community_shift ) {
					$scope.shifts[ i ].ranking = $scope.nextRanking;
				}
			}
		}
		count();
		return;
		$scope.makeBusy();
		DriverShiftScheduleService.wantToWork( id_community_shift, $scope.nextRanking, function( data ){
			if( !data.error ){
				process( data );
			}
		} );
	}

	list();

} );

NGApp.controller( 'DriversOnboardingDocsCtrl', function ( $scope, $timeout, DriverOnboardingService ) {

	$scope.ready = false;
	var waiting = false;
	$scope.page = 1;

	var list = function(){
		DriverOnboardingService.docs.listDocs( $scope.page, function( data ){
			$scope.pages = data.pages;
			$scope.next = data.next;
			$scope.prev = data.prev;
			$scope.documents = data.results;
			$scope.count = data.count;
			$scope.ready = true;
		} );
	}

	$scope.approve = function( doc ){
		var approve = ( doc.approved ) ? false : true;
		DriverOnboardingService.docs.approve( doc.id_driver_document_status, approve, function( data ){
			list();
		} );
	}

	$scope.nextPage = function(){
		$scope.page = $scope.next;
		list();
	}

	$scope.prevPage = function(){
		$scope.page = $scope.prev;
		list();
	}

	$scope.edit = function( id_admin ){
		$scope.navigation.link( '/drivers/onboarding/' + id_admin );
	}

	$scope.download = function( id_driver_document_status ){
		DriverOnboardingService.docs.download( id_driver_document_status );
	}

	list();

} );

NGApp.controller('DriversOnboardingCtrl', function ($scope, $timeout, $location, $rootScope, StaffService, ViewListService, CommunityService) {

	angular.extend( $scope, ViewListService );

	var load = function(){
		StaffService.list($scope.query, function(d) {
			$scope.drivers = d.results;
			$scope.complete(d);
		});
	}

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'driver',
			status: 'active',
			working: 'all',
			pexcard: 'all',
			community: '',
			send_text: 'all',
			fullcount: true
		},
		update: function() {
			load();
		}
	});

	$scope.moreOptions = function(){
		$scope.show_more_options = !$scope.show_more_options;
		$rootScope.$broadcast('search-toggle');
		if( $scope.show_more_options) {

			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}

		$rootScope.$broadcast('search-toggle');
	}

	var limits = [];
	limits.push( { value: '20', label: '20' } );
	limits.push( { value: '50', label: '50' } );
	limits.push( { value: '100', label: '100' } );
	limits.push( { value: '200', label: '200' } );
	$scope.limits = limits;

	var statuses = [];
	statuses.push( { value: 'all', label: 'All' } );
	statuses.push( { value: 'active', label: 'Active' } );
	statuses.push( { value: 'inactive', label: 'Inactive' } );
	$scope.statuses = statuses;

	var send_text_options = [];
	send_text_options.push( { value: 'all', label: 'All' } );
	send_text_options.push( { value: true, label: 'Yes' } );
	send_text_options.push( { value: false, label: 'No' } );
	$scope.send_text_options = send_text_options;

	$scope.send_text_about_schedule = function( id_admin, value ){
		params = { 'id_admin': id_admin, 'value': value };
		StaffService.send_text_about_schedule( params, function( json ){
			if( json.error ){
				App.alert( 'Error saving: ' + json.error , 'error' );
			}
		} );
	}

	$scope.last_note = function( id_admin ){
		$rootScope.$broadcast( 'openStaffNoteContainer', id_admin );
	}

	$rootScope.$on( 'staffNoteSaved', function(e, data) {
		for( x in $scope.drivers ){
			if( $scope.drivers[x].id_admin == data.id_admin ){
				$scope.drivers[x].note = data;
			}
		}
	});

});

NGApp.controller( 'DriversOnboardingFormCtrl', function ( $scope, $routeParams, $filter, FileUploader, DriverOnboardingService, CommunityService, StaffPayInfoService ) {

	$scope.ready = false;
	$scope.submitted = false;
	$scope.isSaving = false;

	$scope.payment_types = StaffPayInfoService.typesPayment();

	var vehicle_default = null;

	$scope._yesNo = DriverOnboardingService.yesNo();
	$scope.timezones = CommunityService.timezones();
	DriverOnboardingService.vehicles( function( json ){
		if( !$scope.vehicles ){
			$scope.vehicles = json.options;
			vehicle_default = json.default;
		}
	} );

	$scope.$watch( 'driver.phone', function( newValue, oldValue, scope ) {
		referral();
	} );


	$scope.$watch( 'driver.name', function( newValue, oldValue, scope ) {
		referral();
	} );

	var referral = function(){
		if( $scope.driver && !$scope.driver.id_admin ){
			var name = $scope.driver.name;
			var phone = $scope.driver.phone;
			if( name && phone ){
				DriverOnboardingService.referral( phone, name, function( data ){
					if( data.code ){
						$scope.driver.invite_code = data.code;
					};
				} );
			}
		}
	}

	CommunityService.listSimple( function( data ){
		if( !$scope.communities ){
			$scope.communities = data;
		}
		$scope.ready = true;
	} );

	var docs = function(){
		// Load the docs
		var id_admin = $scope.driver.id_admin;
		DriverOnboardingService.docs.list( id_admin, function( data ){
			$scope.documents = data;
		} );
		docsPendency();
	}

	$scope.approve = function( doc ){
		var approve = ( doc.approved ) ? false : true;
		DriverOnboardingService.docs.approve( doc.id_driver_document_status, approve, function( data ){
			docs();
		} );
	}

	$scope.remove = function( id_driver_document_status ){
		if( confirm( 'Confirm remove document?' ) ){
			DriverOnboardingService.docs.remove( id_driver_document_status, function( data ){
				docs();
			} );
		}
	}

	var docsPendency = function(){
		DriverOnboardingService.docs.pendency( $routeParams.id, function( data ){  } );
	}

	var logs = function(){
		DriverOnboardingService.logs( $routeParams.id, function( data ){
			$scope.logs = data;
		} );
	}

	var start = function(){

		DriverOnboardingService.get( $routeParams.id, function( driver ){

			$scope.driver = driver;

			if( driver.pexcard_date ){
				$scope.driver.pexcard_date = new Date( driver.pexcard_date );
			} else {
				$scope.driver.pexcard_date = new Date();
			}

			if( !$scope.driver.id_admin ){
				$scope.driver.notify = true;
			}

			if( !$scope.driver.vehicle && vehicle_default ){
				$scope.driver.vehicle = vehicle_default
			}

			docs( driver.id_admin );
		} );

		DriverOnboardingService.phone_types( function( json ){
			$scope.phone_types = json.options;
			$scope.phones_default = json.default;
			$scope.iphone_options = json.iphone_options;
			$scope.android_options = json.android_options;
			$scope.android_versions = json.android_versions;

			$scope.iphone_type = json.default;
			$scope.android_type = json.default;
			//$scope.android_type_other = json.other;//michal
			$scope.android_version = json.default;
		} );

		DriverOnboardingService.carrier_types( function( json ){
			$scope.carrier_types = json.options;
			$scope.carrier_type_other = json.other;
		} );

    DriverOnboardingService.tshirt_sizes( function( json ){
			$scope.tshirt_sizes = json.tshirt_options;
		} );

	}

	$scope.notify = function(){
		DriverOnboardingService.notifySetup( $scope.driver.id_admin, function( json ){
			if( json.success ){
				App.alert( 'Notification sent!' );
				// logs();
			} else {
				App.alert( 'Notification not sent: ' + json.error , 'error' );
			}
		} );
	}

	$scope.setDocument = function( id_driver_document ){
		$scope.doc_uploaded = id_driver_document
	}

	// method save that saves the driver
	$scope.save = function(){

		if( $scope.isSaving ){
			return;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			$scope.isSaving = false;
			return;
		}

		if( $scope.driver.pexcard_date ){
			$scope.driver.pexcard_date = $filter( 'date' )( $scope.driver.pexcard_date, 'yyyy-MM-dd' );
		}

		$scope.isSaving = true;
		DriverOnboardingService.save( $scope.driver, function( json ){
			if( json.success ){
				var url = '/drivers/onboarding/' + json.success.id_admin;
				if( $scope.driver.id_admin ){
					$scope.reload();
				} else {
					$scope.navigation.link( url );
				}
				setTimeout( function(){
					App.alert( 'Driver saved!' );
				}, 50 );
				$scope.isSaving = false;
			} else {
				App.alert( 'Driver not saved: ' + json.error , 'error' );
				$scope.isSaving = false;
			}
		} );
	}

	$scope.cancel = function(){
		$scope.navigation.link( '/drivers/onboarding/' );
	}

	$scope.setDocument = function( id_driver_document ){
		$scope.doc_uploaded = id_driver_document
	}

	// this is a listener to upload error
	$scope.$on( 'driverDocsUploadedError', function(e, data) {
		App.alert( 'Upload error, please try again or send us a message.' );
	} );
	// this is a listener to upload success
	$scope.$on( 'driverDocsUploaded', function(e, data) {
		if( data.success ){
			App.alert( 'File saved!' );
			docs();
		} else {
			App.alert( 'Upload error, please try again or send us a message.' );
		}
	});

	// Upload control stuff
	$scope.doc_uploaded = 0;
	var uploader = $scope.uploader = new FileUploader({
		url: '/api/driver/documents/upload/'
	});

	$scope.download = function( id_driver_document_status ){
		DriverOnboardingService.docs.download( id_driver_document_status );
	}

	$scope.$watch( 'ready', function( newValue, oldValue, scope ) {
		start();
	});

} );

NGApp.controller( 'DriversNotificationCtrl', function ( $scope, $routeParams, DriverService ) {

	var id_admin = $routeParams.id;

	$scope.loading = true;

	var reset = function(){
		$scope.notification = { 'value': null, 'type': null };
	}

	var types = [];
	types.push( { value: 'email', label: 'Email' } );
	types.push( { value: 'phone', label: 'Phone' } );
	types.push( { value: 'sms-dumb', label: 'Dumb SMS' } );
	types.push( { value: 'sms', label: 'SMS' } );

	$scope.types = types;

	$scope.save = function(){

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;

		$scope.notification.id_admin = $scope.id_admin;

		DriverService.notifications.save( $scope.notification, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				load();
				reset();
			}
		} );
	}


	var load = function(){
		DriverService.notifications.list( id_admin, function( json ){
			$scope.driver = json.driver;
			$scope.id_admin = json.id_admin;
			$scope.add_notification = json.add_notification;
			$scope.notifications = json.notifications;
			$scope.loading = false;
		} );
	}

	$scope.change_status = function( notification ){
		DriverService.notifications.change_status( notification.id_admin_notification, function( json ){
			if( json.error ){
				App.alert( json.error );
			}
			load();
		} );
	}

	reset();
	load();

} );

NGApp.controller( 'DriversOnboardingSetupCtrl', function( $scope, DriverOnboardingService ) {

	$scope.ready = false;
	$scope.finished = false;
	$scope.sending = false;

	$scope.driver = { password: '', email : '', confirm: '' };

	$scope.access = function(){
		$scope.navigation.link( '/login' );
	}

	$scope.check_password = false;

	$scope.send = function(){
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}
		$scope.sending = true;
		DriverOnboardingService.setupSave( $scope.driver, function( json ){
			if( json.success ){
				$scope.driver	= json.success;
				$scope.sending = false;
				$scope.finished = true;
			} else {
				$scope.sending = false;
				$scope.error = json.error;
			}
		} );
	}

	DriverOnboardingService.setupValidate( function( json ){
		if( json.success ){
			$scope.driver.id_admin = json.success.id_admin;
			$scope.driver.hasEmail = json.success.hasEmail;
			$scope.ready = true;
		} else {
			$scope.error = json.error;
		}
	} );

} );

NGApp.controller( 'DriversDocsFormCtrl', function( $scope, $rootScope, DriverOnboardingService, StaffService) {

	$scope.ready = false;
	$scope.status = {};
	$scope.pexcard = false;

	var docs = function(){

		// Load the docs
		DriverOnboardingService.docs.list( $scope.account.user.id_admin, function( data ){

			$scope.documents = data;
			$scope.ready = true;

			$scope.status.docs = true;
			$scope.status.identification = true;

			angular.forEach($scope.documents, function(doc, x) {
				if ($scope.documents[x].url && (!$scope.documents[x].status || $scope.documents[x].status.file.substring(9, 14) == 'blank')) {
					$scope.status.docs = false;
				} else if (!$scope.documents[x].url && !$scope.documents[x].status) {
					$scope.status.identification = false;
				}
			});
		} );
	}

	$scope.isMobile = App.isMobile();

	$scope.setDocument = function( id_driver_document ){
		$scope.doc_uploaded = id_driver_document
	}

	// this is a listener to upload error
	$scope.$on( 'driverDocsUploadedError', function(e, data) {
		App.alert( 'Upload error, please try again or send us a message.' );
	} );

	// this is a listener to upload success
	$scope.$on( 'driverDocsUploaded', function(e, data) {
		if( data.success ){
			App.alert( 'File saved!' );
			docs();
		} else {
			App.alert( 'Upload error, please try again or send us a message.' );
		}
	});

	$scope.download = function( id_driver_document_status ){
		DriverOnboardingService.docs.download( id_driver_document_status );
	}

	var loadInfo = function(){
		if( $scope.account && $scope.account.user && $scope.account.user.id_admin ){
			StaffService.status( $scope.account.user.id_admin, function(data) {
				if (data.payment == true) {
					$scope.status.paymentinfo = true;
				}
			});
			StaffService.has_pexcard( $scope.account.user.id_admin, function( json ) {
				if( json.success ){
					$scope.pexcard = json.success;
				}
			});
			docs();
		} else {
			setTimeout( function(){ loadInfo(); }, 500 );
		}
	}

	loadInfo();

} );

NGApp.controller( 'PreOnboardingCtrl', function( $scope, PreOnboardingService, CommunityService, DriverOnboardingService ) {

	$scope.ready = false;
	$scope.submitted = false;
	$scope.driver = {};

	CommunityService.listSimple( function( data ){
		$scope.communities = data;
		$scope.ready = true;
	} );

	DriverOnboardingService.vehicles( function( json ){
		$scope.vehicles = json.options;
		$scope.driver.vehicle = json.default;
	} );

	$scope.sending = false;

	$scope.save = function(){
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}
		$scope.sending = true;
		PreOnboardingService.save( $scope.driver, function( json ){
			if( json.success ){
				$scope.login = json.success.login;
				$scope.finished = true;
			} else {
				$scope.sending = false;
				$scope.error = json.error;
			}
		} );
	}
} );

NGApp.controller('DriversPaymentFormCtrl', function( $scope, StaffPayInfoService, ConfigService ) {

	$scope.bank = { 'showForm': true };
	$scope.basicInfo = {};
	$scope.basicInfoOk = false;
	var load = function(){
		StaffPayInfoService.loadById( $scope.account.user.id_admin, function( json ){
			if( json.id_admin ){
				$scope.basicInfo = json;
				if(json.stripe_id && json.stripe_account_id ){
					$scope.bank.showForm = false;
				}
				if( json.legal_name_payment && json.social_security_number && json.address && json.dob ){
					$scope.basicInfoOk = true;
				}
				$scope.ready = true;
				$scope.payment = {};
			} else {
				App.alert( json.error );
			}
		} )
	}

	$scope.save_basic_info = function(){
		if( $scope.formBasic.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.basicSubmitted = true;
			return;
		}
		$scope.isSaving = true;
		StaffPayInfoService.save( $scope.basicInfo, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				$scope.basicInfo = data;
				$scope.saved = true;
				$scope.basicInfoOk = true;
				App.alert( 'Information saved!' );
				setTimeout( function() { $scope.saved = false; }, 1500 );
			}
		} );
	}

	$scope.bankInfoTest = function(){
		StaffPayInfoService.bankInfoTest( function( json ){
			$scope.bank.routing_number = json.routing_number;
			$scope.bank.account_number = json.account_number;
		} );
	}

	$scope.createBankAccount = function(){

		if( !$scope.basicInfo.id_admin_payment_type ){
			App.alert( 'You must save the "Basic Information" form before save the Bank Account Information.' );
			return;
		}

		if( $scope.formBank.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.bankSubmitted = true;
			return;
		}

		$scope.isTokenizing = true;

		stripe();
	}

	var stripe = function(){
		Stripe.bankAccount.createToken( {
			country: 'US',
			currency: 'USD',
			routing_number: $scope.bank.routing_number,
			account_number: $scope.bank.account_number
		}, function( header, response ){

			if( response.id ){
				var params = {
					'token': response.id,
					'id_admin': $scope.account.user.id_admin
				};
				StaffPayInfoService.save_stripe_bank( params, function( d ){
					$scope.isTokenizing = false;
					if( d.id_admin ){
						bank_info_saved();
					} else {
						var error = d.error ? d.error : '';
						App.alert( 'Error: ' + error );
					}
				} );
			} else {
				$scope.isTokenizing = false;
				App.alert( 'Error creating a Stripe token' );
			}
		} );
	}

	var bank_info_saved = function(){
		document.activeElement.blur();
		load();
		$scope.isTokenizing = false;
		$scope.saved = true;
		$scope.bank.account_number = '';
		$scope.bank.routing_number = '';
		$scope.bank.showForm = false;
		App.alert( 'Bank information saved!' );
		setTimeout( function() { $scope.saved = false; }, 1500 );
	}

	$scope.list = function(){
		$scope.navigation.link( '/staff/list' );
	}


	// just to cache the config process stuff
	ConfigService.getProcessor( function( json ){
		$scope.processor = json.processor.type;
		$scope.isStripe = true;
		load();
	} );

});

NGApp.controller('DriversHelpCtrl', function( $scope, AccountService ) {
	$scope.account = AccountService;
});

NGApp.controller('InviteCtrl', function() {});
NGApp.controller('DriversFeedbackCtrl', function($scope, FeedbackService) {
	$scope.feedback = {};
	$scope.errors = {};
    $scope.post = function(){

    	$scope.errors = {};
    	if (!$scope.feedback.message) {
    		$scope.errors.message = true;
    	}
    	if (jQuery.isEmptyObject($scope.errors)) {
    		console.log($scope.feedback);
    			FeedbackService.post($scope.feedback, function(data){
					$scope.finished = true;
        		//console.log(data);
    			})
    	}
    }
});
NGApp.controller('DriversHelpCreditCardCtrl', function() {});
NGApp.controller('DriversLocationsCtrl', function($rootScope, $scope, $routeParams, $location, StaffService, MapService) {


		$scope.staff = null;
		$scope.map = null;
		$scope.loading = true;
		var marker;

		StaffService.get($rootScope.account.user.id_admin, function(staff) {
			$rootScope.title = staff.name + ' | Staff';
			$scope.staff = staff;
			$scope.loading = false;
		});

		StaffService.locations($rootScope.account.user.id_admin, function(d) {
			$scope.locations = d;
			update();
		});

		$scope.$watch('staff', function() {
			console.log('staff');
			update();
		});

		$scope.$watch('map', function() {
			console.log('map');
			//update();
		});

		var update = function() {
			if (!$scope.map || !$scope.staff || !$scope.locations) {
				return;
			}

			MapService.trackStaff({
				map: $scope.map,
				staff: $scope.staff,
				locations: $scope.locations,
				scope: $scope,
				id: 'staff-locations'
			});

		};

		$scope.$on('mapInitialized', function(event, map) {
			$scope.map = map;
			MapService.style(map);
			//update();
		});
});


NGApp.controller('DriversWelcomeHomeCtrl', function() {

});
NGApp.controller('DriversWelcomeInfoCtrl', function() {});

NGApp.controller('DriversWelcomeLocationCtrl', function($location, LocationService, $scope, $rootScope) {
	var complete = function() {
		$location.path('/drivers/welcome/push');
		//history.pushState({}, 'next', '/drivers/welcome/push');
		$rootScope.$safeApply();
	};
	var l;
	$scope.locateit = function() {
		if (App.isCordova) {
			LocationService.register(complete);
		} else {
			complete();
		}
		l.start();
	};
	setTimeout(function(){
		l = Ladda.create($('.welcome-button .ladda-button').get(0));
	},700);
});

NGApp.controller('DriversWelcomePushCtrl', function($rootScope, $location, PushService, $scope) {
	var complete = function() {
		$location.path('/drivers/welcome/wahoo');
		//history.pushState({}, 'next', '/drivers/welcome/wahoo');
		$rootScope.$safeApply();
	};
	$scope.pushit = function() {
		if (App.isCordova && PushService.register) {
			PushService.register(complete);
		} else {
			complete();
		}
	};;
});
NGApp.controller('DriversWelcomeWahooCtrl', function() {
	$.totalStorage('isDriverWelcomeSetup', '1');
});
