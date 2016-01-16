NGApp.factory('PushService', function($http, $location, $timeout, MainNavigationService, DriverOrdersService, $rootScope) {

	var service = {
		id: null,
		badges: 0,
		registered: false,
		plugin: null,
		register: function(callback) {
			if (callback) {
				callback();
			}
		}
	};

	if (!App.isCordova || !window.parent.PushNotification) {
		return service;
	}

	var saveToken = function(data, complete) {
		service.id = data.registrationId;
		console.debug('Push id: ' + data.registrationId);

		if ( window.parent && window.parent.device && window.parent.device.platform && ( window.parent.device.platform == 'android' || window.parent.device.platform == 'Android' || window.parent.device.platform == 'amazon-fireos')) {
			var key = 'push-android';
		} else {
			var key = 'push-ios';
		}

		$http({
			method: 'POST',
			url: App.service + 'config',
			data: {key: key, value: service.id}
		});

		complete();
	};

	service.register = function(complete) {

		service.plugin = window.parent.PushNotification.init({'android': {'senderID': '1029345412368'}, 'ios': {}, 'windows': {} } );
		console.debug('register push');

		service.plugin.on('registration', function(data) {
			console.debug('resgistration callback');
			saveToken(data, complete);
		});

		service.plugin.on('error', function(data) {
			console.error('Failed registering push notifications', data);
			App.alert('Failed to enable Push notifications. Please go to your push notification settings on your device and enable them for Cockpit.');
			complete();
		});

		service.plugin.on('notification', function(data) {
			service.receive(data);
		});

			/*
				'categories': [
					{
						'identifier': 'order-new-test',
						'actions': [
							{
								'title': 'Accept',
								'identifier': 'i11',
								'authentication': 'false',
								'mode': 'background'
							},
							{
								'title': 'View',
								'identifier': 'i22',
								'authentication': 'false',
								'mode': 'foreground'
							}
						]
					},
					{
						'identifier': 'support-message-test',
						'actions': [
							{
								'title': 'Close',
								'identifier': 'i44',
								'authentication': 'false',
								'destructive': 'true',
								'mode': 'background'
							},
							{
								'title': 'View',
								'identifier': 'i33',
								'authentication': 'false',
								'mode': 'foreground'
							}

						]
					}
				]
			};
		}
		*/

	};


	service.receive = function(msg) {

		console.debug(msg);

		var gotoLink = function() {
			$rootScope.$safeApply(function() {
				MainNavigationService.link(msg.additionalData.link);
			});
		};

		// if the user clicked on the notification
		if (msg.additionalData && msg.additionalData.link && !msg.additionalData.foreground) {
			gotoLink();
		}
		// if we are in the foreground and we are forcing showing in foreground
		if (msg.additionalData && msg.additionalData.foreground && msg.additionalData.showInForeground) {
			var fn = function(){ };
			if (msg.additionalData.link) {
				App.rootScope.$broadcast('notificationNewOrder', msg.message, msg.additionalData.link );
			} else {
				App.alert(msg.message, ' ', false, fn);
			}
		}

		// iOS
		if ( window.parent && window.parent.device.platform && window.parent.device.model && window.parent.device.platform == 'iOS') {
			if( parseInt( msg.additionalData.foreground ) == 1 && msg.badge ){
				service.badge++;
				service.plugin.pushNotification.setApplicationIconBadgeNumber(complete, complete, service.badge);
			}
		}

		/*
		switch (msg.identifier) {
			case 'i11': // accept an order
				var order = msg.alert.replace(/^#([0-9]+).*$/,'$1');
				DriverOrdersService.accept(order, function(json) {
					console.debug('ACCEPT RESPONSE', json);
					if (json.status) {

					} else {
						var name = json[ 'delivery-status' ].accepted.name ? ' by ' + json[ 'delivery-status' ].accepted.name : '';
						App.alert( 'Oops!\n It seems this order was already accepted ' + name + '!'  );
					}
				});
			case 'i22': // view an order
				var order = msg.alert.replace(/^#([0-9]+).*$/,'$1');
				MainNavigationService.link('/drivers/order/' + order);
				return;
				break;
		}
		*/



	}

	return service;
});
