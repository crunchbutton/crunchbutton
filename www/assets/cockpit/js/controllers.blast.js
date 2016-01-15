NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/blast', {
			action: 'blasts',
			controller: 'BlastsCtrl',
			templateUrl: 'assets/view/blasts.html'

		}).when('/blast/:id', {
			action: 'blast',
			controller: 'BlastCtrl',
			templateUrl: 'assets/view/blast-blast.html'
		});
}]);

NGApp.controller('BlastsCtrl', function ($rootScope, $scope, BlastService, SocketService, DateTimeService) {

	$scope.loadingBlasts = true;

	$scope.blast = {
		date: DateTimeService.local(new Date).format('YYYY-MM-DD HH:mm:ss Z'),
		content: 'Hello %n!'
	};

	var updateBlasts = function() {
		BlastService.list({}, function(d) {
			$scope.blasts = d;
			$scope.loadingBlasts = false;
		});
	};

	SocketService.listen('blasts', $scope)
		.on('update', function(d) {
			for (var x in $scope.blasts) {
				if ($scope.blasts[x].id_blast == d.id_blast) {
					$scope.blasts[x] = d;
				}
			}

		}).on('create', function(d) {
			updateBlasts();
		});

	updateBlasts();

	$scope.cancel = function(id) {
		BlastService.cancel(id, updateBlasts);
	};

	$scope.save = function() {
		BlastService.post($scope.blast, updateBlasts);
	};

	$scope.sample = function() {
		BlastService.sample({sample: $scope.blast.data, content: $scope.blast.content}, function(samples) {
			$scope.samples = samples;
		});
	};
});

NGApp.controller('BlastCtrl', function ($scope, $routeParams, BlastService, $rootScope, SocketService) {
	$scope.loadingBlast = true;

	var updateBlast = function() {
		BlastService.get($routeParams.id, function(blast) {
			$scope.blast = blast;
			$scope.loadingBlast = false;
		});
	};

	SocketService.listen('blast.' + $routeParams.id, $scope)
		.on('update', function(d) {
			updateBlast();
		});

	updateBlast();

	$scope.cancel = function(id) {
		BlastService.cancel(id, updateBlasts);
	};
});
