NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider

		.when('/communities', {
			action: 'communities',
			controller: 'CommunitiesCtrl',
			templateUrl: 'assets/view/communities.html',
			reloadOnSearch: false
		})
		.when('/communities/notes', {
			action: 'community',
			controller: 'CommunitiesNotesCtrl',
			templateUrl: 'assets/view/communities-notes.html'
		})
		.when('/community/edit/:id', {
			action: 'community',
			controller: 'CommunityFormCtrl',
			templateUrl: 'assets/view/communities-form.html'
		})
		.when('/community/new', {
			action: 'community',
			controller: 'CommunityFormCtrl',
			templateUrl: 'assets/view/communities-form.html'
		})
		.when('/communities/closed', {
			action: 'community',
			controller: 'CommunitiesClosedCtrl',
			templateUrl: 'assets/view/communities-closed.html'
		})
		.when('/community/:id/:tab?', {
			action: 'community',
			controller: 'CommunityCtrl',
			templateUrl: 'assets/view/communities-community.html'
		});

}]);

NGApp.controller('CommunitiesCtrl', function ($rootScope, $scope, CommunityService, ViewListService) {
	$rootScope.title = 'Communities';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		allowAll: true,
		watch: {
			search: '',
			status: 'active',
			open: 'all',
			fullcount: false
		},
		update: function() {
			update();
		}
	});

	var update = function(){
		CommunityService.list($scope.query, function(d) {
			$scope.communities = d.results;
			$scope.complete(d);
		});
	}

	$scope.openClosingTimeContainer = function( permalink ){
		$rootScope.$broadcast( 'openClosingTimeContainer', { permalink: permalink } );
	}

	$rootScope.$on( 'communityOpenClosedSaved', function(e, data) {
		update();
	});

});

NGApp.controller('CommunitiesNotesCtrl', function ($scope, $rootScope, ViewListService, CommunityService) {

	$rootScope.title = 'Communities Notes';

	$scope.show_more_options = false;

	$scope.openCommunityNoteContainer = function(){
		$rootScope.$broadcast( 'openCommunityNoteContainer', null );
	}

	$rootScope.$on( 'communityNoteSaved', function(e, data) {
		update();
	});

	$scope.moreOptions = function(){

		$scope.show_more_options = !$scope.show_more_options;

		if( $scope.show_more_options ){
			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}

	}

	angular.extend($scope, ViewListService);

	var update = function() {
			CommunityService.notes.list( $scope.query, function(d) {
				$scope.notes = d.results;
				$scope.complete(d);
					if( ( $scope.query.community ) && !$scope.show_more_options ){
						$scope.moreOptions();
					}
			});
		}

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			community: '',
			fullcount: false
		},
		update: update
	});
});

NGApp.controller('CommunitiesClosedCtrl', function ($scope, CommunityService) {

	var communities = [];

	$scope.loading = true;

	CommunityService.closed( function( json ){
 		communities = json;
 		filter();
 		$scope.loading = false;
	} );

	$scope.closed = 'close_3rd_party_delivery_restaurants';

	$scope.$watch( 'closed', function( newValue, oldValue, scope ) {
		filter();
	});

	var filter = function(){
		if( $scope.closed == 'all' ){
			$scope.communities = communities;
		} else {
			$scope.communities = [];
			for( x in communities ){
				if( communities[x][$scope.closed] ){
					$scope.communities.push( communities[x] );
				}
			}
		}
	}

});


NGApp.controller('CommunityFormCtrl', function ($scope, $routeParams, $rootScope, $filter, CommunityService, MapService ) {

	$scope.ready = false;
	$scope.isSaving = false;

	$scope.save = function(){

		if( !$scope.community.dont_warn_till_enabled ){
			$scope.community.dont_warn_till = null;
		}

		if( $scope.form.$invalid ){
			$scope.submitted = true;
			return;
		}

		$scope.isSaving = true;

		if( $scope.community.dont_warn_till_enabled && $scope.community.dont_warn_till ){
			$scope.community.dont_warn_till_fmt = $filter( 'date' )( $scope.community.dont_warn_till, 'yyyy-MM-dd HH:mm:ss' )
		}

		CommunityService.save( $scope.community, function( json ){
			$scope.isSaving = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				community();
				$scope.navigation.link( '/community/edit/' + json.permalink );
			}
		} );
	}

	$scope.$watch( 'community.address', function( newValue, oldValue, scope ) {
		var address = newValue;
		if( address ){
			g = new google.maps.Geocoder();
			g.geocode( {address:address},function(data,s) {
				if(s === 'ZERO_RESULTS') { return; }
				if(s !== 'OK') { return; }

				if( !data || !data.length ) { return; }
				$scope.community.loc_lat = data[0].geometry.location.lat();
				$scope.community.loc_lon = data[0].geometry.location.lng();
		});
		}
	});

	$scope.cancel = function(){
		$rootScope.navigation.back();
	}

	var load = function(){
		$scope.timezones = CommunityService.timezones();
		$scope.yesNo = CommunityService.yesNo();
		$scope.ready = true;
	}

	$scope.restaurants = new Array();

	var campus_cash = null;

	$scope.$watch( 'community.campus_cash', function( newValue, oldValue, scope ) {
		if( !campus_cash && newValue ){
			$scope.community.campus_cash_default_payment = true;
		}
	});


	var community = function(){
		if( $routeParams.id ){
			CommunityService.get( $routeParams.id, function( d ) {

				campus_cash = d.campus_cash;

				$rootScope.title = d.name + ' | Community';
				$scope.community = d;
				if( $scope.community.dont_warn_till ){
					var dont_warn_till = new Date( 	$scope.community.dont_warn_till.y,
																					( $scope.community.dont_warn_till.m -1 ),
																					$scope.community.dont_warn_till.d,
																					$scope.community.dont_warn_till.h,
																					$scope.community.dont_warn_till.i );
					$scope.community.dont_warn_till = dont_warn_till;
					$scope.community.dont_warn_till_enabled = 1;
				} else {
					$scope.community.dont_warn_till = null;
					$scope.community.dont_warn_till_enabled = 0;
				}
				angular.forEach( d._restaurants, function( restaurant, id_restaurant ) {
					$scope.restaurants.push( { 'id_restaurant' : restaurant.id_restaurant, 'name' : restaurant.name } );
				} );
				load();
			});
		} else {
			$scope.community = { 'active': 1, 'private': 0, 'image': 0, 'close_all_restaurants': 0, 'close_3rd_party_delivery_restaurants': 0, 'driver_checkin': 1 };
			load();
		}
	}

	community();
});

NGApp.controller('CommunityOpenCloseCtrl', function ($scope, $routeParams, $rootScope, $filter, CommunityService, DriverService ) {

	$rootScope.$on( 'openClosingTimeContainer', function(e, data) {

		$scope.loading = true;
		$scope.community = null;
		App.dialog.show('.open-close-community-dialog-container');

		var permalink = data.permalink ? data.permalink : $routeParams.id;

		CommunityService.openCloseStatus( permalink, function( d ) {
			$scope.loading = false;
			$scope.community = d;
			if( $scope.community.dont_warn_till ){
				var dont_warn_till = new Date( 	$scope.community.dont_warn_till.y,
																( $scope.community.dont_warn_till.m -1 ),
																$scope.community.dont_warn_till.d,
																$scope.community.dont_warn_till.h,
																$scope.community.dont_warn_till.i );
				$scope.community.dont_warn_till = dont_warn_till;
			}

			$scope.close_3rd_party_delivery_restaurants_original = d.close_3rd_party_delivery_restaurants;
			$scope.close_all_restaurants_original = d.close_all_restaurants;

			if( !$scope.community.dont_warn_till ){
				$scope.community.dont_warn_till = ( new Date() );
			}

			$scope.status_changed = false;

			DriverService.byCommunity( $scope.community.id_community, function( data ){
				$scope.drivers = data;
				$scope.updateDriversCount();
			} );

			});

			$scope.isSaving = false;
	});

	$scope.updateDriversCount = function(){
		if( $scope.drivers.length ){
			var count = 0;
			angular.forEach( $scope.drivers, function(staff, key) {
				if( staff.down_to_help_out ){
					count++;
				}
			} );
			$scope.driversCount = count;
		}
	}

	$scope.sendTextMessage = function(){
		var phones = [];
		angular.forEach($scope.drivers, function(staff, key) {
			if( staff.down_to_help_out ){
				phones.push( staff.phone );
			}
		} );
		if( phones.length ){
			$rootScope.closePopup();
			setTimeout( function(){
				var message = 'We could use extra drivers right now, since orders are so busy. If you can help out with some orders now, or soon, just shoot us a text back :) Thanks!';
				$rootScope.$broadcast( 'textNumber', phones );
				$rootScope.$broadcast( 'textInfo', { message:message, permalink:$routeParams.id, type:'down_to_help_out' } );
			}, 500 );
		} else {
			App.alert( 'Select at least one driver.' );
		}
	}

	$scope.$watch( 'community.close_3rd_party_delivery_restaurants', function( newValue, oldValue, scope ) {
		verify_status();
	});

	$scope.$watch( 'community.close_all_restaurants', function( newValue, oldValue, scope ) {
		verify_status();
	});

	var verify_status = function(){
		if( !$scope.community ){
			return;
		}
		if( $scope.close_3rd_party_delivery_restaurants_original != $scope.community.close_3rd_party_delivery_restaurants ||
			  $scope.close_all_restaurants_original != $scope.community.close_all_restaurants ){
			$scope.status_changed = true;
		} else {
			$scope.status_changed = false;
		}
	}

	$scope.formOpenCloseSave = function(){

		if( !$scope.community.dont_warn_till_enabled ){
			$scope.community.dont_warn_till = null;
		}

		if( $scope.formOpenClose.$invalid ){
			$scope.formOpenCloseSubmitted = true;
			return;
		}

		if( $scope.community.dont_warn_till_enabled && $scope.community.dont_warn_till ){
			$scope.community.dont_warn_till_fmt = $filter( 'date' )( $scope.community.dont_warn_till, 'yyyy-MM-dd HH:mm:ss' )
		} else {
			$scope.community.dont_warn_till_fmt = null;
		}

		$scope.isSavingOpenClose = true;
		CommunityService.saveOpenClose( $scope.community, function( json ){
			$scope.isSavingOpenClose = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$rootScope.closePopup();
				$rootScope.$broadcast( 'communityOpenClosedSaved', json );
				window.open( 'https://crunchbutton.com/' + $scope.community.permalink );
			}
		} );
	}

} );


NGApp.controller('CommunityAddNoteCtrl', function ($scope, $routeParams, $rootScope, $filter, CommunityService, DriverService ) {

	$rootScope.$on( 'openCommunityNoteContainer', function(e, data) {
		$scope.note = {};
		$scope.note.community = data;
		App.dialog.show('.community-add-note-dialog-container');
		$scope.isSavingNote = false;
		$scope.formAddNoteSubmitted = false;

		if( !$scope.note.community ){
			if( !$scope.communities ){
				CommunityService.listSimple( function( json ){
					$scope.communities = json;
				} );
			}
		}
	});

	$scope.formAddNoteSave = function(){

		if( $scope.formAddNote.$invalid ){
			$scope.formAddNoteSubmitted = true;
			return;
		}

		if( !$scope.note.community ){
			App.alert( 'Please select a community!' );
			return;
		}

		CommunityService.addNote( $scope.note, function( json ){
			$scope.isSavingNote = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				$rootScope.closePopup();
				$rootScope.$broadcast( 'communityNoteSaved', json );
			}
			$scope.formAddNoteSubmitted = false;
		} );
	}

} );

NGApp.controller('CommunityCtrl', function ($scope, $routeParams, $rootScope, MapService, CommunityService, RestaurantService, OrderService, StaffService, DriverService) {

	$scope.loading = true;
	$scope.isSaving = false;
	$scope.isSavingAlias = false;

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		update();
	});

	// method to load orders - called at ui-tab directive
	$scope.loadOrders = function(){
		$scope.loadingOrders = true;
		OrderService.list( { community: $scope.community.id_community, limit: 5}, function(d) {
			$scope.orders = d.results;
			$scope.loadingOrders = false;
		} );
	}

	$scope.current_page = 'community';

	// method to load restaurants - called at ui-tab directive
	$scope.loadRestaurants = function(){
		$scope.loadingRestaurants = true;
		RestaurantService.list( { community: $scope.community.id_community, limit: 50 }, function(d) {
			$scope.restaurants = d.results;
			$scope.loadingRestaurants = false;
		});
	}

	$rootScope.$on( 'restaurantForceCloseSaved', function(e, data) {
		$scope.loadRestaurants();
	});

	// method to load drivers - called at ui-tab directive
	$scope.loadDrivers = function(){
		$scope.loadingStaff = true;
		StaffService.list( { community: $scope.community.id_community, limit: 50, type: 'driver', 'working': 'force','send_text':'all'}, function(data) {
			$scope.staff = data.results;
			$scope.loadingStaff = false;
		});
	}

	// method to load aliases - called at ui-tab directive
	$scope.loadAliases = function(){
		$scope.loadingAliases = true;
		CommunityService.alias.list( $routeParams.id, function( json ){
			$scope.aliases = json;
			$scope.loadingAliases = false;
		} );
	}

	// method to load logs - called at ui-tab directive
	$scope.loadLogs = function(){
		$scope.loadingLogs = true;
		CommunityService.closelog.list( $routeParams.id, function( json ){
			$scope.closelogs = json;
			$scope.loadingLogs = false;
		} );
	}

	$scope.loadNotes = function(){
		$scope.loadingNotes = true;
		CommunityService.notes.list( { community: $scope.community.id_community }, function( json ){
			$scope.notes = json.results;
			$scope.loadingNotes = false;
		} );
	}

	$scope.notes_to_driver_edit = function( id_restaurant ){
		$rootScope.$broadcast( 'openEditNotesToDriver', {id_restaurant:id_restaurant, callback: function(){
			$rootScope.closePopup();
			$scope.loadRestaurants();
		} } );
	}

	$scope.loadDriversSMS = function(){
		$scope.loadDrivers = true;
		DriverService.byCommunity( $scope.community.id_community, function( data ){
			$scope.drivers = data;
			$scope.loadDrivers = false;
		} );
	}

	$scope.sendSms = {
		unSelectAll: function(){
			angular.forEach($scope.drivers, function(staff, key) {
				staff.send = false;
			});
		},
		selectAll: function(){
			$scope.sendSms.unSelectAll();
			angular.forEach($scope.drivers, function(staff, key) {
				if( staff.phone ){
					staff.send = true;
				}
			});
		},
		selectAllDownToHelpOut: function(){
			angular.forEach($scope.drivers, function(staff, key) {
				if( staff.phone && staff.down_to_help_out ){
					staff.send = true;
				}
				var phones = [];
				angular.forEach($scope.drivers, function(staff, key) {
					if( staff.send ){
						phones.push( staff.phone );
					}
				} );
				$rootScope.closePopup();
				if( phones.length ){
					setTimeout( function(){
						var message = 'We could use extra drivers right now, since orders are so busy. If you can help out with some orders now, or soon, just shoot us a text back :) Thanks!';
						$rootScope.$broadcast( 'textNumber', phones );
						$rootScope.$broadcast( 'textInfo', { message:message, permalink: $routeParams.id, type:'down_to_help_out' } );
					}, 1 );
				} else {
					App.alert( 'Select at least one driver.' );
				}

			});
		},
		selectAllWorking: function(){
			$scope.sendSms.unSelectAll();
			angular.forEach($scope.drivers, function(staff, key) {
				if( staff.phone && staff.active && staff.working ){
					staff.send = true;
				}
			});
		},
		send: function(){
			var phones = [];
			angular.forEach($scope.drivers, function(staff, key) {
				if( staff.send ){
					phones.push( staff.phone );
				}
			} );
			if( phones.length ){
				$rootScope.$broadcast( 'textNumber', phones );
			} else {
				App.alert( 'Select at least one driver.' );
			}
		}
	}

	$scope.openClosingTimeContainer = function(){
		$rootScope.$broadcast( 'openClosingTimeContainer' );
	}

	$scope.restaurantForceCloseContainer = function( permalink ){
		$rootScope.$broadcast( 'restaurantForceCloseContainer', { permalink: permalink } );
	}

	$scope.openCommunityNoteContainer = function(){
		$rootScope.$broadcast( 'openCommunityNoteContainer', $routeParams.id );
	}

	$scope.aliasDialogContainer = function(){
		$scope.alias = { id_community: $scope.community.id_community, permalink: $scope.community.permalink, sort: $scope.community.next_sort };
		App.dialog.show('.alias-dialog-container');
	};

	$scope.remove_alias = function( id_community_alias ){
		if( confirm( 'Confirm remove the alias?' ) ){
			CommunityService.alias.remove( { 'id_community_alias' : id_community_alias, permalink: $scope.community.permalink }, function( data ){
				if( data.error ){
					App.alert( data.error);
					return;
				} else {
					community();
					$scope.flash.setMessage( 'Alias removed!' );
				}
			} );
		}
	}

	$scope.aliasAdd = function(){
		if( $scope.formAlias.$invalid ){
			$scope.formAliasSubmitted = true;
			return;
		}

		$scope.isSavingAlias = true;

		CommunityService.alias.add( $scope.alias, function( json ){
			$scope.isSavingAlias = false;
			if( json.error ){
				App.alert( 'Error saving: ' + json.error );
			} else {
				update(true);
				App.dialog.close();
				console.log('y u no work');
			}
			console.log('y u no work 2');

			$scope.formAliasSubmitted = false;
		} );
	}

	var update = function(force) {
		if ((!$scope.map || !$scope.community) &&  !force) {
			return;
		}
		MapService.trackCommunity({
			map: $scope.map,
			community: $scope.community,
			scope: $scope,
			id: 'community-location'
		});
	};


	var load = function(){
		CommunityService.basic($routeParams.id, function(d) {
			$rootScope.title = d.name + ' | Community';
			$scope.community = d;
			$scope.loading = false;
			update();
		});
	}

	load();

	$rootScope.$on( 'communityNoteSaved', function(e, data) {
		$scope.loadNotes();
	});

	$rootScope.$on( 'communityOpenClosedSaved', function(e, data) {
		load();
		$scope.loadLogs();
		$scope.community = data;
	});

});
