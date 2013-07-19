function RecommendCtrl( $scope, $http, RecommendRestaurantService ) {

	$scope.service = RecommendRestaurantService;

	$scope.formSent = $scope.service.getFormStatus();

	// Watch the variable status change
	$scope.$watch( 'service.getFormStatus()', function( newValue, oldValue, scope ) {
		$scope.formSent = newValue;
	});

	$scope.send = function(){

		if ( $.trim( $( '.home-recommend-text' ).val() ) == '' ){
			alert( "Please enter the restaurant\'s name." );
			$( '.home-recommend-text' ).focus();
			return;
		}

		var pos = App.loc.pos();

		var content = 'Address entered: ' + pos.addressEntered + '\n' + 
									'Address reverse: ' + pos.addressReverse + '\n' + 
									'City: ' + pos.city + '\n' + 
									'Region: ' + pos.region + '\n' + 
									'Lat: ' + pos.lat + '\n' + 
									'Lon: ' + pos.lon;
		var data = {
			name: $( '.home-recommend-text' ).val(),
			content : content
		};

		var url = App.service + $scope.service.api.add;

		$http.post( url , data )
			.success( function( data ) {
					RecommendRestaurantService.changeFormStatus( true );
					$scope.service.addRecommendation( data.id_suggestion );
					$( '.home-recommend-text' ).val( '' );
			}	);
	}
}

// RecommendRestaurantService service
NGApp.factory( 'RecommendRestaurantService', function( $http ){

	var service = {
		api : {
			add : 'suggestion/restaurant',
			relateuser : 'suggestion/relateuser'
		}
	};

	var formSent = false;
	var recommendations = [];

	service.changeFormStatus = function( status ){
		formSent = status;
	}
	
	service.getFormStatus = function(){
		return formSent;
	}

	service.addRecommendation = function( id ){
		recommendations.push( id );
	}

	service.getRecommendations = function(){
		if( recommendations.length > 0 ){
			return recommendations;
		}
		return false;
	}

	service.relateUser = function(){
		if( service.getRecommendations() ){
			var url = App.service + service.api.relateuser;
			$.each( recommendations, function(index, value) {
				var id_suggestion = value;
				var data = { id_suggestion : id_suggestion, id_user : App.config.user.id_user };
				$http.post( url , data );
			} );
			recommendations = false;
		}
	}

	return service;
} );