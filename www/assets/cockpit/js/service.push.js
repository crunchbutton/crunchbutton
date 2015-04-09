


NGApp.factory('PushService', function($http, MainNavigationService, DriverOrdersService) {

	var service = {
		id: null,
		badges: 0
	};

	if (!App.isPhoneGap) {
		return service;
	}

	document.addEventListener('pushnotification', function(e) {
		console.log('Push recieve event', e);
		service.receive(e.msg);
	}, false);

	window.parent.pushnotification = function() {
		console.log('Push recieve function', e);
		service.receive(e.msg);
	};

	var saveToken = function(id, complete) {
		service.id = id;

		if (window.parent.device.platform == 'android' || window.parent.device.platform == 'Android' || window.parent.device.platform == 'amazon-fireos') {
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
		if (window.parent.device.platform == 'android' || window.parent.device.platform == 'Android' || window.parent.device.platform == 'amazon-fireos') {
			var params = {
				'senderID': '1029345412368',
				'ecb': 'pushnotification'
			};
		} else {
			var params = {
				'badge': 'true',
				'sound': 'true',
				'alert': 'true',
				'ecb': 'pushnotification',
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

		parent.plugins.pushNotification.register(
			function(id) {
				service.id = id;
				console.debug('Push id: ' + id);

				if (id == 'OK') {
					complete();
					return;
				}

				saveToken(id, complete);
			},
			function() {
				console.error('Failed registering push notifications', arguments);
				App.alert('Failed to enable Push notifications. Please go to your push notification settings on your device and enable them for Cockpit.');
				complete();
			},params
		);
	};


	service.receive = function(msg) {
		console.debug('Notification: ', msg);

		var complete = function() {

		};

		if (msg.event == 'registered' && msg.regid) {
			saveToken(msg.regid, complete);
		}

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

		// iOS
		if (msg.alert) {
			App.alert(msg.alert);
		}

		if (msg.badge) {
			service.badge++;
			parent.plugins.pushNotification.setApplicationIconBadgeNumber(complete, complete, service.badge);
		}

		if (msg.sound) {
			var snd = new parent.Media(msg.sound.replace('www/',''));
			snd.play();
		}
	}

	return service;
});
