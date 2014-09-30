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

NGApp.controller('MapsDriverCtrl', function ($scope, $routeParams) {

});

NGApp.controller('MapsOrdersCtrl', function ($scope, $routeParams) {
	setTimeout(function() {
		var mapOptions = {
			center: {
				lat: -34.397, lng: 150.644},
				zoom: 8
			};
		var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	}, 100);
});

NGApp.controller('MapsOrderCtrl', function ($scope, $routeParams) {

});