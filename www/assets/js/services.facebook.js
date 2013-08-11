// FacebookService service
NGApp.factory( 'FacebookService', function( $http, $location, AccountService ){

	var service = {
		token : false,
		_order_uuid : false,
		orderStatus : false,
		logged : false,
		doAuth : true,
		wait : false,
		running : false,
		error : { unknown : false, userExists : false, login : false }
	}

	service.account = AccountService;

	// This method pre load the order info that could be posted
	service.preLoadOrderStatus = function( uuid ){
		var url = App.service + 'facebook/status/order/' + service._order_uuid;
		$http( {
			method: 'GET',
			url: url,
			} ).success( function( data ) {
				if( data.success ){
					service.orderStatus = data.success;
				} else {
					service.orderStatus = false;
				}
			}	);
	}

	// This method let the user type his message before post
	service.postOrder = function(){
		if( !service.orderStatus ){
			service.preLoadOrderStatus();
			alert( 'Oops, please try again!' );
			return;
		}
		var status = service.orderStatus;
		FB.ui({
			method: 'stream.publish',
			user_message_prompt: 'CrunchButton: Publish This!',
			message: status.message,
			attachment: {
			name: status.name,
			caption: status.caption,
			description: status.description,
			href: status.link,
			media:[{'type':'image','src':status.picture,'href':status.link}],
			},
			action_links: [{ text: status.site_name, href: status.site_url }]
		},
		function(response) {
			if (response && response.post_id) {
				alert( 'Thank you for sharing!' );
			}
		} );
	}

	service.requestLogin = function( callback ){
		FB.login( function( response ){ service.processStatus( response, callback ); }, { scope : serviceScope } );
	}

	service.registerToken = function( token ){
		if( !service.token ){
			service.token = token;
			$.totalStorage( 'fbtoken', token);
		}
	}

	service.requestPermission = function( callback ){
		FB.ui({
			 method: 'permissions.request',
			 'perms': serviceScope,
			 'display': 'iframe'
			},
			function( response ) {
				callback();
			}
		);
	}

	service.auth = function( session ){

		service.logged = true;

		App.log.account( { 'userID' : session.authResponse.userID} , 'facebook login' );

		if( service.doAuth ){

			FB.api( '/me', { fields: 'name' }, function( response ) {

				if ( response.error ) {
					App.log.account( { 'userID' : session.authResponse.userID, 'error' : response.error } , 'facebook name error' );
					service.error.unknown = true;
					return;
				}

				App.log.account( { 'userID' : session.authResponse.userID, 'response' : response, 'shouldAuth' : service.doAuth, 'running' : service.running } , 'facebook response' );
				if( response.id ){

					service.doAuth = false;

					if( !service.running ){
						service.running = true;
						App.log.account( { 'userID' : session.authResponse.userID, 'running' : service.running } , 'facebook running' );

						// Just call the user api, this will create a facebook user
						var url = App.service + 'user/facebook';

						$http( {
							method: 'GET',
							url: url,
							} ).success( function( data ) {

								App.log.account( { 'userID' : session.authResponse.userID, 'running' : service.running, 'data' : data } , 'facebook ajax' );

								if( data.error ){
									if( data.error == 'facebook id already in use' ){
										// Log the error
										App.log.account( { 'error' : data.error } , 'facebook error' );
										service.error.unknown = true;
									}
								} else {

									service.account.updateInfo();
									service.account.user = data;
									if( service.account.callback ){
										service.account.callback();
										service.account.callback = false;
									} else {
										App.signin.manageLocation();
										$.magnificPopup.close();
									}
									
								}
								App.log.account( { 'userID' : session.authResponse.userID } , 'facebook currentPage' );
								$rootScope.$broadcast( 'userAuth', service.user );
							}	);
					}
				} else {
					service.error.unknown = true;
				}
			});
		}
	}

	service.signout = function( callback ){
		FB.logout( callback() );
	}

	service.startAuth = function( response ){
		service.processStatus( response, service.auth );
	}

	service.processStatus = function( response, callback ){
		if ( response.status === 'connected' && response.authResponse ) {
			if( response.authResponse.accessToken ){
				service.logged = true;
				service.registerToken( response.authResponse.accessToken );	
			}
			if( callback ){
				callback( response );
			}
		}
	}

	return service;

} );