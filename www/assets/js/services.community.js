// CommunityAlias service
NGApp.factory( 'CommunityAliasService', function( PositionsService ){

	var service = {};

	service.position = PositionsService;

	service.removeCommunityStyle = function(){
		if( service.style ){
			angular.element( 'html' ).removeClass( service.style );
		}
	}

	service.communityStyle = function( permalink ){
		service.removeCommunityStyle();
		service.style = 'community-' + permalink;
		angular.element( 'html' ).addClass( service.style );
	}

	service.route = function( id, success, error ){

		var parts = id.toLowerCase().split( '/' );
		var alias = false;
		var restaurant = false;
		if( App && App.aliases ){
			for( x in parts ){

						if( parts[ x ] != '' ){
							if( App.aliases[parts[x]] ){
								alias = App.aliases[parts[x]];
							} else {
								restaurant = parts[x];
							}
						}
					}
		}


		success = success || function(){};
		error = error || function(){};

		if (alias) {
			// Get the location of the alias
			var loc = App.locations[ alias.id_community ];
			if ( loc && loc.loc_lat && loc.loc_lon ) {
				var res = new Location({
					lat: loc.loc_lat,
					lon: loc.loc_lon,
					type: 'alias',
					verified: true,
					prep: alias.prep,
					city: alias.name_alt,
					address: alias.name_alt,
					permalink: alias.permalink,
					image: alias.image,
				});
				service.communityStyle( alias.permalink );
				success( { alias: res }, restaurant );
				return;
			}
		}
		service.removeCommunityStyle();
		error();
	};

	return service;
} );