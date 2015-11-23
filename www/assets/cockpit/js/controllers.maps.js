/**
 * maps, and map queries should be directly linkable when the query params change. just like google maps.
 * this way we can link to the from other pages
 */



NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/maps', {
			action: 'maps',
			controller: 'MapsCtrl',
			templateUrl: 'assets/view/maps.html'

		}).when('/maps/drivers', {
			action: 'maps-drivers',
			controller: 'MapsDriversCtrl',
			templateUrl: 'assets/view/maps-drivers.html'

		}).when('/maps/driver/:id', {
			action: 'maps-driver',
			controller: 'MapsDriverCtrl',
			templateUrl: 'assets/view/maps-driver.html'

		}).when('/maps/orders', {
			action: 'maps-orders',
			controller: 'MapsOrdersCtrl',
			templateUrl: 'assets/view/maps-orders.html'

		}).when('/maps/order/:id', {
			action: 'maps-order',
			controller: 'MapsOrderCtrl',
			templateUrl: 'assets/view/maps-order.html'
		});

}]);

NGApp.controller('MapsCtrl', function ($scope, $routeParams) {

});

NGApp.controller('MapsDriversCtrl', function ($scope, $routeParams) {

});

NGApp.controller('MapsDriverCtrl', function ($scope, $routeParams, StaffService, MapService) {
	var current = null;

	$scope.$on('mapInitialized', function(event, map) {
		StaffService.locations($routeParams.id, function(d) {
			for (var x in d) {
				var lat = parseFloat(d[x].lat);
				var lon = parseFloat(d[x].lon);

				if (current && lat == current.lat && lon == current.lon) {
					continue;
				} else if (current) {

				}

				var myLatlng = new google.maps.LatLng(lat, lon);
				var params = {
					map: map,
					position: myLatlng,
				};

				if (x == 0) {
					params.icon = MapService.icon.car;
					params.zIndex = 100;
					map.setCenter(myLatlng);
					current = {
						lat: lat,
						lon: lon
					};

				} else {
					params.zIndex = 99;
					params.icon = 'small_red';
				}
				new google.maps.Marker(params);
			}
			$scope.locations = d;
		});

		$scope.ready = true;
	});

	StaffService.get($routeParams.id, function(d) {
		$scope.driver = d;
	});


	var update = function() {
		StaffService.locations($routeParams.id, function(d) {
			$scope.locations = d;
		});
		// add marker
	};


});

NGApp.controller('MapsOrdersCtrl', function ($scope, $routeParams) {

});

NGApp.controller('MapsOrderCtrl', function ($scope, $routeParams) {

});


NGApp.controller('MapsDialogCtrl', function ( $scope, $rootScope, AppAvailabilityService ) {

	$rootScope.$on( 'openMapsDialog', function( e, data ) {

		var type = data.type;
		var address1 = data.address1;
		var address2 = data.address2;

		switch( type ){
			case 'route':
				$scope.link = '?daddr=' + address1 + '&saddr=' + address2;
			break;
			case 'query':
				$scope.link = '?q=' + address1 ;
			break;
		}

		$scope.link = $scope.link.replace(/#/g, 'apt');

		$scope.maps = [];

		for ( map in AppAvailabilityService.maps ) {
  		$scope.maps.push( AppAvailabilityService.maps[ map ] );
		}

		if( $scope.maps.length ){
			App.dialog.show( '.maps-dialog-container' );
		} else {
			setTimeout( function(){
				$scope.link = 'http://maps.apple.com/' + $scope.link;
				parent.window.open( $scope.link, '_system', 'location=yes' );
			} );
		}

	});

	$scope.openMap = function( url ){
		parent.window.open( url, '_system', 'location=yes' );
	}

});