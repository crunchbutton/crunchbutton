// RecommendRestaurantService service
NGApp.factory( 'RecommendRestaurantService', function( $http, PositionsService ){

	var service = {
		form : { restaurant : '' },
		greetings : false
	};

	service.position = PositionsService;

	var recommendations = [];

	service.addRecommendation = function( id ){
		recommendations.push( id );
	}

	service.send = function(){
		if ( service.form.restaurant == '' ){
			alert( "Please enter the restaurant\'s name." );
			$( '.recommend-restaurant' ).focus();
			return;
		}

		var pos = service.position.pos();

		var content = 'Address entered: ' + pos.entered() + '\n' + 
									'Address reverse: ' + pos.address() + '\n' + 
									'City: ' + pos.city() + '\n' + 
									'Region: ' + pos.region() + '\n' + 
									'Lat: ' + pos.lat() + '\n' + 
									'Lon: ' + pos.lon();

		var url = App.service + 'suggestion/restaurant';
		$http( {
			method: 'POST',
			url: url,
			data: $.param( { name: service.form.restaurant, content : content } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) {
					service.greetings = true;
					service.addRecommendation( data.id_suggestion );
					service.form.restaurant = '';
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
				$http( {
					method: 'POST',
					url: url,
					data: $.param( { id_suggestion : id_suggestion, id_user : App.config.user.id_user } ),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					} );
			} );
			recommendations = false;
		}
	}

	return service;
} );