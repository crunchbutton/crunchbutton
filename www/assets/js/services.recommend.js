// RecommendRestaurantService service
NGApp.factory( 'RecommendRestaurantService', function( $http, PositionsService, AccountService ){

	var service = {
		form : { restaurant : '' },
		greetings : false
	};

	service.position = PositionsService;
	service.account = AccountService;

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
					data: $.param( { id_suggestion : id_suggestion, id_user : service.account.user.id_user } ),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					} );
			} );
			recommendations = false;
		}
	}
	return service;
} );

//RecommendFoodService Service
NGApp.factory( 'RecommendFoodService', function( $http ){

	var service = { 
			thanks : false,
			isSending : false,
			modal : {},
			form : { name : '' }
		};
	
	service.reset = function(){
		service.thanks = false;
		service.isSending = false;
		service.form.name = '';
	}

	service.modal.open = function(){
		service.reset();
		App.dialog.show( '.suggest-food-container' );
	};

	service.send = function(){
		service.form.name = $.trim( service.form.name );
		if ( service.form.name == '' ){
			alert( 'Please enter a suggestion.' );
			$('#suggestion-name').focus();
			return;
		}

		service.isSending = true;
		var url = App.service + 'suggestion/new';

		$http( {
			method: 'POST',
			url: url,
			data: $.param( { restaurant: App.restaurant.permalink, name: service.form.name } ),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			} ).success( function( data ) { service.thanks = true; service.isSending = false; }	);
	}

	return service;
} );
