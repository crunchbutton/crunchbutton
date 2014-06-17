NGApp.controller('RestaurantOrderNew', function ($scope, $http) {

	$scope.isSubmitting = false;
	$scope.order = {};

	$scope.submit = function() {
		$scope.isSubmitting = true;
		
		
		
		
		
		$http({
			method: 'POST',
			url: '/api/order',
			data: $scope.order,
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
		}).success(function(data) {
			console.log(data);
			$scope.isSubmitting = false;
		});

	}
	
	$scope.card = {};
	
	// Credit card years
	$scope.card._years = function () {
		var years = [];
		years.push({
			value: '',
			label: 'Year'
		});
		var date = new Date().getFullYear();
		for (var x = date; x <= date + 20; x++) {
			years.push({
				value: x.toString(),
				label: x.toString()
			});
		}
		return years;
	}
	// Credit card months
	$scope.card._months = function () {
		var months = [];
		months.push({
			value: '',
			label: 'Month'
		});
		for (var x = 1; x <= 12; x++) {
			months.push({
				value: x.toString(),
				label: x.toString()
			});
		}
		return months;
	}
});
