App.signin.manageLocation = function(){
	// TODO: fix it
	return;
	// If the user signed in and we do not have his location yet, lets use his stored location.
	if( App.loc.address() == '' ){
		if( App.config.user.address ){ // First check if we have the user's address. If we do, lets use it.
			App.loc.geocode( App.config.user.address, function(){ App.page.foodDelivery(true); }, function(){});
		} else if( App.config.user.location_lat && App.config.user.location_lon ){ // Else lets try to find the user's address by his position.
			App.loc.reverseGeocode( 
				App.config.user.location_lat, 
				App.config.user.location_lon, 
				function(){
					if( App.loc.realLoc.addressReverse ){
						var address = App.loc.realLoc.addressReverse;
						App.loc.geocode( address, 
							function(){ 
								App.page.foodDelivery(true); 
							}, 
							function(){ /* error, just ignore it */ });
					}
				}, 
				function(){ /* error, just ignore it */ } 
			);
		}
	}
}

