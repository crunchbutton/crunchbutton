// FacebookService service
NGApp.factory( 'FacebookService', function( $http, $location, $rootScope, AccountService, ReferralService) {

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

	service.referral = ReferralService;

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
		if( service.orderStatus ){
			var status = service.orderStatus;
			status.link = service.referral.cleaned_url();
			App.share({
				url: 'http://' + status.link,
				name: status.name,
				caption: status.caption,
				description: status.description,
				picture: status.picture,
				success : function( success ){
					$http( {
						method: 'POST',
						url: App.service + 'facebook/reward/',
						data: $.param( { 'post_id': success.post_id, 'uuid': service._order_uuid } ),
						headers: {'Content-Type': 'application/x-www-form-urlencoded'}
					} ).success( function(){
						$rootScope.$broadcast( 'orderShared' );
					} );
				}
			});
		} else {
			service.preLoadOrderStatus();
			App.alert( 'Oops, please try again!' );
			return;
		}
	}

	service.postInvite = function(url) {
		App.share({
			url: url,
			name: 'Noms',
			caption: ' ',
			description: url
		});
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
		console.log(status);

		if (status.status === 'connected' && status.authResponse) {

			service.logged = true;
			service.error.unknown = false;
			service.error.userExists = false;
			service.error.login = false;

			service.token = status.authResponse.accessToken;
			$.totalStorage('fbtoken', service.token);

			// if the app already has a user, we dont give a crap about facebook
			if ( App.config.user.id_user && App.config.user.facebook ) {
				return;
			}

			// android's returns this variable as userId
			if( status.authResponse.userId ){
				status.authResponse.userID = status.authResponse.userId;
			}

			if (status.authResponse.userID) {

				App.log.account({'userID': status.authResponse.userID}, 'facebook login');

				// make sure we dont double call the authentication and user creation service
				if (!service.running) {
					service.running = true;
					App.log.account({'userID': status.authResponse.userID, 'running': service.running}, 'facebook running');

					// if it is phonegap call a special facebook connection
					var data = {};
					url = App.service + 'user/facebook?fbrtoken=' + service.token;

					// Just call the user api, this will create a facebook user
					$http( { method: 'GET', url: url, cache: false } ).success(function( data ) {

						App.log.account({'userID': status.authResponse.userID, 'running': service.running, 'data': data }, 'facebook ajax');

						if (data.error) {
							if (data.error == 'facebook id already in use') {
								$rootScope.$broadcast( 'facebookIdAlreadyUsed', true );
								App.log.account({'error': data.error}, 'facebook error');
								service.error.userExists = true;
							}
							service.running = false;
							return;
						} else {
							service.account.updateInfo();
							service.account.user = data;

							if (service.account.callback) {
								service.account.callback();
								service.account.callback = false;
							} else {
								try {
									$.magnificPopup.close();
								} catch (e) {}
							}

						}

						App.log.account({'userID': status.authResponse.userID} , 'facebook currentPage');
						$rootScope.$broadcast('userAuth', service.account.user);
						service.running = false;
						service.wait = false;
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