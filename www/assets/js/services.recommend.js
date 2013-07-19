// RecommendRestaurantService service
NGApp.factory( 'RecommendRestaurantService', function( $http, PositionsService ){

	var service = {
		restaurant : '',
		greetings : false
	};

	service.position = PositionsService;

	var recommendations = [];

	service.addRecommendation = function( id ){
		recommendations.push( id );
	}

	service.send = function(){

		if ( service.restaurant == '' ){
			alert( "Please enter the restaurant\'s name." );
			$( '.recommend-restaurant' ).focus();
			return;
		}

		var pos = service.position.pos();

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

		var url = App.service + 'suggestion/restaurant';

		$http.post( url , data )
			.success( function( data ) {
					service.greetings = true;
					service.addRecommendation( data.id_suggestion );
					service.restaurant = '';
			}	);
	}

	service.getRecommendations = function(){
		if( recommendations.length > 0 ){
			return recommendations;
		}
		return false;
	}

	service.relateUser = function(){
		if( service.getRecommendations() ){
			var url = App.service + 'suggestion/relateuser';
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