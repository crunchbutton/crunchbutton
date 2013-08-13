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
	service.preLoadOrderStatus = function(uuid){
		$http.get(App.service + 'facebook/status/order/' + service._order_uuid).success( function( data ) {
			if (data.success) {
				service.orderStatus = data.success;
			} else {
				service.orderStatus = false;
			}
		});
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

	// request permission to post on a users timeline
	service.requestPermission = function(callback) {
		callback = typeof callback === 'function' ? callback : function(){};
		FB.ui({
			method: 'permissions.request',
			perms: serviceScope,
			display: 'iframe'
		}, callback);
	}

	// process status is called any time a status change event is triggered with facebook
	service.processStatus = function(status) {
		console.debug('Facebook process status >>',status);

		if (status.status === 'connected' && status.authResponse) {

			service.logged = true;
			service.error.unknown = false;
			service.error.userExists = false;
			service.error.login = false;

			service.token = status.authResponse.accessToken;
			$.totalStorage('fbtoken', service.token);
			
			// if the app already has a user, we dont give a crap about facebook
			if (App.config.user.id_user) {
				return;
			}

			if (status.authResponse.userID) {
				App.log.account({'userID': status.authResponse.userID}, 'facebook login');
				
				// make sure we dont double call the authentication and user creation service
				if (!service.running) {

					service.running = true;
					App.log.account({'userID': status.authResponse.userID, 'running': service.running}, 'facebook running');

					// if it is phonegap call a special facebook connection
					var data = {};
					if (App.isPhoneGap) {
						data.fbtoken = service.token;
					}

					// Just call the user api, this will create a facebook user
					$http.get(App.service + 'user/facebook', data).success(function(data) {
	
						App.log.account({'userID': status.authResponse.userID, 'running': service.running, 'data': data }, 'facebook ajax');
	
						if (data.error) {
							if (data.error == 'facebook id already in use') {
								App.log.account({'error': data.error}, 'facebook error');
								service.error.unknown = true;
							}

						} else {
							service.account.updateInfo();
							service.account.user = data;

							if (service.account.callback) {
								service.account.callback();
								service.account.callback = false;
							} else {
								App.signin.manageLocation();
								try {
									$.magnificPopup.close();
								} catch (e) {}
							}
							
						}

						App.log.account({'userID': status.authResponse.userID} , 'facebook currentPage');
						App.rootScope.$broadcast('userAuth', service.user);
					});
				}
			}

		} else {
			// the service is notConnected. reset everything
			service.logged = false;
			service.error.unknown = false;
			service.error.userExists = false;
			service.error.login = false;
			service.token = null;
			$.totalStorage('fbtoken', null);
		}

	}

	// sign out of facebook
	service.signout = function(callback) {
		FB.logout(function() {
			service.logged = false;
			if (typeof callback === 'function') {
				callback();
			}
		});
	}

	return service;
});