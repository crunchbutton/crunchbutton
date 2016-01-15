NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/dispute/evidence/:id', {
			action: 'dispute',
			controller: 'DisputeEvidenceCtrl',
			templateUrl: 'assets/view/disputes-dispute-evidence.html'
		})
		.when('/dispute/:id', {
			action: 'dispute',
			controller: 'DisputeCtrl',
			templateUrl: 'assets/view/disputes-dispute.html'
		})
		.when('/disputes/', {
			action: 'dispute',
			controller: 'DisputesCtrl',
			templateUrl: 'assets/view/disputes.html',
			reloadOnSearch: false
		})
}]);

NGApp.controller('DisputesCtrl', function ($rootScope, $scope, $timeout, DisputeService, ViewListService) {

	$rootScope.title = 'Disputes';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			fullcount: false
		},
		update: function() {
			update();
		}
	});

	var update = function(){
		DisputeService.list($scope.query, function(d) {
			$scope.disputes = d.results;
			$scope.complete(d);
		});
	}

});

NGApp.controller('DisputeEvidenceCtrl', function( $scope, $routeParams, DisputeService ) {
	$scope.loading = true;
	DisputeService.evidence( $routeParams.id, function( json ){
		$scope.evidence = json;
		$scope.loading = false;
	} );

});

NGApp.controller('DisputeCtrl', function( $scope, $routeParams, $rootScope, DisputeService ) {

	$scope.loading = true;

	var load = function(){

		$scope.isSaving = false;
		$scope.isUploading = false;

		DisputeService.get( $routeParams.id, function( json ){

			$scope.dispute = json;
			$scope.loading = false;

			if( $scope.dispute.can_send_more ){

				DisputeService.last_evidence( $routeParams.id, function( json ){
					$scope.evidence = json;
					if( $scope.evidence.receipt ){
						$scope.evidence.temp_name = $scope.evidence.receipt;
					}
				} );

			}

		} );
	}

	$rootScope.$on( 'triggerUploadFileAdded', function(e, file_name) {
		$scope.evidence.temp_name = file_name;
	});

	// this is a listener to upload error
	$scope.$on( 'resourceUploadError', function(e, data) {
		App.alert( 'Upload error, please try again or send us a message.' );
	} );

	$scope.$on( 'triggerUploadProgress', function(e, progress) {
		$scope.isUploading = true;
		console.log('progress',progress);
	} );

	// this is a listener to upload success
	$scope.$on( 'resourceUpload', function(e, data) {
		var response = data.response;
		if( response.success ){
			$scope.evidence.receipt = response.success.id;
			$scope.evidence.receipt_url = response.success.url;
			save_evidence( $scope.evidence_send );
		} else {
			App.alert( 'File not saved! Please reload this page.');
		}
	});

	$scope.evidence_send_limit = DisputeService.evidence_send_limit;

	$scope.evidence_send = false;


	var save_evidence = function( send ){

		$scope.isSaving = true;

		if( $scope.evidence_send ){
			$scope.evidence.send = true;
		}

		DisputeService.evidence_save( $scope.evidence, function( json ){
			$scope.isSaving = false;
			if( json.success ){
				if( json.success == 'sent' ){
					App.alert( 'Evidence sent!' );
				} else {
					App.alert( 'Evidence draft saved!' );
				}
				load();
			} else {
				App.alert( 'Error saving: ' + json.error );

			}
		} );
	}

	$scope.evidence_save = function( send ){

		$scope.evidence_send = send;

		if( $scope.isUploading || $scope.isSaving ){
			return;
		}
		if( $scope.form.$invalid ){
			$scope.submitted = true;
			App.alert( 'Please fill all the required fields!' );
			return;
		}
		if( $scope.evidence.temp_name == $scope.evidence.receipt ){
			save_evidence( send );
		} else {
			$rootScope.$broadcast( 'triggerStartUpload' );
			$scope.isUploading = true;
			return;
		}
	}

	load();
} );


NGApp.controller('DisputeEvidenceFormCtrl', function( $scope, $routeParams, DisputeService ) {

	$scope.loading = true;

	var load = function(){
		if( $routeParams.id_stripe_dispute_evidence ){
			DisputeService.evidence( $routeParams.id, function( json ){
				$scope.evidence = json;
				$scope.loading = false;
			} );
		}

	}
	load();
} );
