App.credit = {
	api : {
		byRestaurant : 'user/credit/'
	},
	restaurant : {}
}

App.credit.getCredit = function( complete ){
	if( App.restaurant.id ){
		var url = App.service + App.credit.api.byRestaurant + App.restaurant.id;
		$.getJSON( url, function( json ) {
			if( json.credit ){
				App.credit.restaurant[App.restaurant.id] = json.credit;
			} else {
				App.credit.restaurant[App.restaurant.id] = 0;
			}
			if( complete ){
				complete();
			}
		} );	
	}
}

App.credit.show = function(){
	var gift = App.credit.restaurant[App.restaurant.id];
	if( parseFloat( gift ) ){
		$( '.restaurant-gift-value' ).html( gift );
		$( '.restaurant-gift' ).show();
	} else {
		$( '.restaurant-gift' ).hide();
	}
	
}