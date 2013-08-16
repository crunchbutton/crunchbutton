// CommunityAlias service
NGApp.factory( 'CommunityAliasService', function( PositionsService ){

	var service = {};

	service.position = PositionsService;

	service.route = function( id, success, error ){
		
		var parts = id.split( '/' );
		var alias = false;
		var restaurant = false;

		for( x in parts ){
			if( parts[ x ] != '' ){
				if( App.aliases[parts[x]] ){
					alias = App.aliases[parts[x]];
				} else {
					restaurant = parts[x];
				}
			}
		}

		success = success || function(){};
		error = error || function(){};

		if (alias) {
			// Get the location of the alias
			var loc = App.locations[ alias.id_community ];
			if ( loc.loc_lat && loc.loc_lon ) {
				var res = new Location({
					lat: loc.loc_lat,
					lon: loc.loc_lon,
					type: 'alias',
					verified: true,
					prep: alias.prep,
					city: alias.name_alt,
					address: alias.name_alt
				});
				success( { alias: res }, restaurant );
				return;
			}
		}
		error();
	};

	return service;
} );