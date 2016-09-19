NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/config', {
			action: 'tools',
			controller: 'ConfigCtrl',
			templateUrl: '/assets/view/config.html'
		})
		.when('/config/rewards', {
			action: 'tools',
			controller: 'ConfigRewardsCtrl',
			templateUrl: '/assets/view/config-rewards.html'
		})
		.when('/config/geo-orders', {
			action: 'tools',
			controller: 'ConfigGeoOrdersCtrl',
			templateUrl: '/assets/view/config-geo-orders.html'
		})
		.when('/config/live', {
			action: 'tools',
			controller: 'ConfigLiveCtrl',
			templateUrl: '/assets/view/config-live.html'
		})
		.when('/config/rules', {
			action: 'tools',
			controller: 'ConfigRulesCtrl',
			templateUrl: '/assets/view/config-rules.html'
		})
		.when('/config/blocked', {
			action: 'tools',
			controller: 'ConfigBlockedsCtrl',
			templateUrl: '/assets/view/config-blockeds.html'
		})
		.when('/config/auto-reply', {
			action: 'tools',
			controller: 'ConfigAutoReplyCtrl',
			templateUrl: '/assets/view/config-auto-reply.html'
		})
		.when('/config/community-open', {
			action: 'tools',
			controller: 'ConfigNotificationCommunityOpenCtrl',
			templateUrl: '/assets/view/config-community-open.html'
		})
		.when('/config/cs-calls', {
			action: 'tools',
			controller: 'ConfigCSCallsCtrl',
			templateUrl: '/assets/view/config-cs-calls.html'
		});

}]);


NGApp.controller('ConfigBlockedsCtrl', function( $scope, BlockedService ) {

	$scope.loading = true;

	var load = function(){
		BlockedService.config.load( function( json ){
			$scope.loading = false;
			if( !json.error ){
				$scope.message = json.message;
				$scope.ready = true;
			} else {
				App.alert( json.error);
			}
		} )
	}

	$scope.save = function(){
		if( $scope.isSaving ){
			return;
		}
		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}
		$scope.isSaving = true;
		BlockedService.config.save( { 'message': $scope.message }, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				App.alert( 'Information saved!' );
			}
		} );
	}

	load();

});

NGApp.controller('ConfigCSCallsCtrl', function( $scope, CustomerServiceConfig) {

	$scope.yesNo = CustomerServiceConfig.yesNo();

	var load = function(){
		CustomerServiceConfig.load( function( json ){
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
		CustomerServiceConfig.save( $scope.config, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				load();
				App.alert( 'Settings saved!' );
			}
		} );
	}

	load();

});

NGApp.controller('ConfigNotificationCommunityOpenCtrl', function( $scope, NotificationCommunityOpenService, CommunityService) {

	$scope.yesNo = CommunityService.yesNo();

	var load = function(){
		NotificationCommunityOpenService.load( function( json ){
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
		NotificationCommunityOpenService.save( $scope.config, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				load();
				App.alert( 'Settings saved!' );
			}
		} );
	}

	load();

});

NGApp.controller('ConfigRulesCtrl', function( $scope, RulesService ) {

	var load = function(){
		RulesService.rules.config.load( function( json ){
			if( !json.error ){
				$scope.config = json;
				$scope.ready = true;
			}
		} )
	}

	$scope.yesNo = RulesService.yesNo();

	$scope.save = function(){
		$scope.isSaving = true;
		RulesService.rules.config.save( $scope.config, function( data ){
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

	load();

});

NGApp.controller('ConfigLiveCtrl', function( $scope, ConfigLiveMenuService ) {

	var load = function(){
		ConfigLiveMenuService.load( function( json ){
			if( !json.error ){
				$scope.config = json;
				$scope.ready = true;
			}
		} )
	}

	$scope.save = function(){
		$scope.isSaving = true;
		ConfigLiveMenuService.save( $scope.config, function( data ){
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

	load();

} );


NGApp.controller('ConfigGeoOrdersCtrl', function( $scope, GeoOrdersService ) {

	var load = function(){
		GeoOrdersService.load( function( json ){
			if( !json.error ){
				$scope.config = json;
				$scope.ready = true;
			}
		} )
	}

	$scope.save = function(){
		$scope.isSaving = true;
		GeoOrdersService.save( $scope.config, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				$scope.basicInfo = data;
				$scope.saved = true;
				App.alert( 'Information saved!' );
				setTimeout( function() { $scope.saved = false; }, 1500 );
			}
		} );
	}

	load();

});

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

	load();

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

	load();

});

NGApp.controller( 'ConfigCtrl', function ( $scope ) {});
