


NGApp.factory('PushService', function($http) {

	var service = {
		id: null,
		badges: 0
	};

	if (!App.isPhoneGap) {
		return service;
	}

	document.addEventListener('pushnotification', function(e) {
		service.receive(e.msg);
	}, false);

	parent.plugins.pushNotification.register(
		function(id) {
			service.id = id;
			console.debug('Push id: ' + id);

			$http({
				method: 'POST',
				url: App.service + 'config',
				data: {key: 'push-ios', value: service.id},
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			});
		},
		function() {
			console.error('Failed registering push notifications', arguments);
		},
		{
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
		}
	);

	
	service.receive = function(msg) {
		console.debug('Notification: ', msg);

		var complete = function() {

		};

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
