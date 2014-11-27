NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/staff', {
			action: 'staff',
			controller: 'StaffCtrl',
			templateUrl: 'assets/view/staff.html',
			reloadOnSearch: false
		})
		.when('/staff/:id', {
			action: 'staff',
			controller: 'StaffInfoCtrl',
			templateUrl: 'assets/view/staff-staff.html'
		})
		.when('/staff/:id/payinfo', {
			action: 'staff',
			controller: 'StaffPayInfoCtrl',
			templateUrl: 'assets/view/staff-payinfo.html'
		});
}]);

NGApp.controller('StaffInfoCtrl', function ($rootScope, $scope, $routeParams, $location, StaffService, MapService) {
	$scope.staff = null;
	$scope.map = null;
	var marker;

	StaffService.get($routeParams.id, function(staff) {
		$rootScope.title = staff.name + ' | Staff';
		$scope.staff = staff;
		$scope.ready = true;
	});

	StaffService.locations($routeParams.id, function(d) {
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

NGApp.controller('StaffCtrl', function ($scope, StaffService, ViewListService) {
	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
			status: 'active',
			working: 'all'
		},
		update: function() {
			StaffService.list($scope.query, function(d) {
				$scope.staff = d.results;
				$scope.complete(d);
			});
		}
	});
});

NGApp.controller('StaffListCtrl', function( $scope, StaffService ) {

	$scope.showForm = true;

	$scope.search = {};

	$scope.ready = true;
	$scope.search._types = StaffService.typeSearch();
	$scope.search._status = StaffService.statusSearch();
	$scope.search.type = 'all';
	$scope.search.status = 'all';
	$scope.search.name = '';
	$scope.page = 1;

	var list = function(){
		$scope.searched = true;
		$scope.isSearching = true;
		var search = { 'type': $scope.search.type, 'name': $scope.search.name, 'status': $scope.search.status, 'page': $scope.page }
		StaffService.list( search, function( data ){
			$scope.isSearching = false;
			$scope.pages = data.pages;
			$scope.next = data.next;
			$scope.prev = data.prev;
			$scope.staff = data.results;
			$scope.count = data.count;
		} );
	}

	var waiting = false;

	$scope.doSearch = function(){
		$scope.page = 1;
		list();
	}

	$scope.nextPage = function(){
		$scope.page = $scope.next;
		list();
	}

	$scope.prevPage = function(){
		$scope.page = $scope.prev;
		list();
	}

	$scope.payinfo = function( id_admin ){
		$scope.navigation.link( '/staff/payinfo/' + id_admin );
	}

});

NGApp.controller('StaffPayInfoCtrl', function( $scope, StaffPayInfoService, PexCardService ) {

	$scope.status = PexCardService.status;

	$scope.bank = { 'showForm': true };

	var load = function(){
		StaffPayInfoService.load( function( json ){
			if( json.id_admin ){
				$scope.payInfo = json;
				if( json.balanced_bank ){
					$scope.bank.showForm = false;
				}
				$scope.payInfo.using_pex = parseInt( $scope.payInfo.using_pex );
				$scope.ready = true;
				$scope.payment = {};
				$scope.payment._methods = StaffPayInfoService.methodsPayment();
				$scope.payment._using_pex = StaffPayInfoService.typesUsingPex();
				$scope.payment._types = StaffPayInfoService.typesPayment();
			} else {
				App.alert( json.error );
			}
		} )
	}

	$scope.save = function(){
		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}
		$scope.isSaving = true;
		StaffPayInfoService.save( $scope.payInfo, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				load();
				$scope.saved = true;
				setTimeout( function() { $scope.saved = false; }, 1500 );
			}
		} );
	}

	$scope.bankInfoTest = function(){
		StaffPayInfoService.bankInfoTest( function( json ){
			$scope.bank.routing_number = json.routing_number; ;
			$scope.bank.account_number = json.account_number;;
		} )
	}

	$scope.tokenize = function(){
		if( $scope.formBank.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.bankSubmitted = true;
			return;
		}
		$scope.isTokenizing = true;
		var payload = { name: $scope.payInfo.legal_name_payment,
										account_number: $scope.bank.account_number,
										routing_number: $scope.bank.routing_number };
		StaffPayInfoService.bankAccount( payload, function( json ){
			if( json.href ){
				json.legal_name_payment = $scope.payInfo.legal_name_payment;
				StaffPayInfoService.save_bank( json, function( data ){
					if( data.error ){
						App.alert( data.error);
						return;
					} else {
						load();
						$scope.isTokenizing = false;
						$scope.saved = true;
						setTimeout( function() { $scope.saved = false; }, 1500 );
					}
				} );

			} else {
				App.alert( 'Error!' );
				$scope.isTokenizing = false;
			}
		} );
	}

	$scope.list = function(){
		$scope.navigation.link( '/staff/list' );
	}

	$scope.open_card = function( id_card ){
		change_card_status( id_card, PexCardService.status.OPEN );
	}

	$scope.block_card = function( id_card ){
		change_card_status( id_card, PexCardService.status.BLOCKED );
	}

	var change_card_status = function( id_card, status ){
		if( confirm( 'Confirm change card status to ' + status + '?' ) ){
			PexCardService.pex_change_card_status( { id_card: id_card, status: status },
				function( json ){
					if( json.id ){
						for( x in $scope.payInfo.cards ){
							if( $scope.payInfo.cards[ x ].id == json.id ){
								$scope.payInfo.cards[ x ] = json;
							}
						}
						$scope.flash.setMessage( 'Card status changed to ' + status, 'success' );
					} else {
						$scope.flash.setMessage( json.error, 'error' );
					}
				}
			);
		}
	}

	if( $scope.account.isLoggedIn() ){
		load();
	}

});