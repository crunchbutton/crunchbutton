NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/config', {
			action: 'config',
			controller: 'ConfigCtrl',
			templateUrl: 'assets/view/config.html'
		})
		.when('/config/rewards', {
			action: 'config-rewards',
			controller: 'ConfigRewardsCtrl',
			templateUrl: 'assets/view/config-rewards.html'
		})
		.when('/config/auto-reply', {
			action: 'config-rewards',
			controller: 'ConfigAutoReplyCtrl',
			templateUrl: 'assets/view/config-auto-reply.html'
		});

}]);


NGApp.controller('ConfigRewardsCtrl', function( $scope, CustomerRewardService ) {

	var load = function(){
		CustomerRewardService.reward.config.load( function( json ){
			if( !json.error ){
				$scope.config = json;
				$scope.ready = true;
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
		CustomerRewardService.reward.config.save( $scope.config, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				$scope.basicInfo = data;
				$scope.saved = true;
				$scope.flash.setMessage( 'Information saved!' );
				setTimeout( function() { $scope.saved = false; }, 1500 );
			}
		} );
	}

	if( $scope.account.isLoggedIn() ){
		load();
	}

});


NGApp.controller('ConfigAutoReplyCtrl', function( $scope, ConfigAutoReplyService ) {

	var load = function(){
		ConfigAutoReplyService.load( function( json ){
			if( !json.error ){
				$scope.messages = json;
				$scope.ready = true;
			}
		} )
	}

	$scope.message = { text : '' };

	$scope.remove = function( id_config ){
		ConfigAutoReplyService.remove( id_config, function( data ){
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				load();
				App.alert( 'Message removed!' );
			}
		} );
	}

	$scope.save = function(){
		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}
		$scope.isSaving = true;
		ConfigAutoReplyService.save( $scope.message, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				load();
				App.alert( 'Message saved!' );
				$scope.message.text = '';
			}
		} );
	}

	if( $scope.account.isLoggedIn() ){
		load();
	}

});

NGApp.controller( 'ConfigCtrl', function ( $scope ) {
});