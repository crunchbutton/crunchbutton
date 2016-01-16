NGApp.factory('PushService', function($http, $location, $timeout, MainNavigationService, $rootScope) {

	var service = {
		id: null,
		badges: 0,
		registered: false,
		plugin: null
	};

	if (!App.isCordova || !window.parent.PushNotification) {
		return service;
	}

	var saveToken = function(data, complete) {
		service.id = data.registrationId;
		console.debug('Push id: ' + data.registrationId);

		if ( window.parent && window.parent.device && window.parent.device.platform && ( window.parent.device.platform == 'android' || window.parent.device.platform == 'Android' || window.parent.device.platform == 'amazon-fireos')) {
			var key = 'user-push-android';
		} else {
			var key = 'user-push-ios';
		}

		$http({
			method: 'POST',
			url: App.service + 'config',
			data: {key: key, value: service.id}
		});

		complete();
	};

	service.register = function(complete) {

		if (!complete) {
			complete = function(){};
		}

		service.plugin = window.parent.PushNotification.init({'android': {'senderID': '1029345412368'}, 'ios': {}, 'windows': {} } );
		console.debug('register push');

		service.plugin.on('registration', function(data) {
			saveToken(data, complete);
		});

		service.plugin.on('error', function(data) {
			console.error('Failed registering push notifications', data);
			complete();
		});

		service.plugin.on('notification', function(data) {
			service.receive(data);
		});
	};


	service.receive = function(msg) {

		console.debug(msg);

		var gotoLink = function() {
			$rootScope.$safeApply(function() {
				switch (msg.additionalData.linkTarget) {
					case 'blank':
						window.open(msg.additionalData.link, '_blank');
						break;
					case 'system':
						window.open(msg.additionalData.link, '_system');
						break;
					default:
						MainNavigationService.link(msg.additionalData.link);
						break;
				}
			});
		};

		// if the user clicked on the notification
		if (msg.additionalData && msg.additionalData.link && !msg.additionalData.foreground) {
			gotoLink();
		}

		// if we are in the foreground and we are forcing showing in foreground
		if (msg.additionalData && msg.additionalData.foreground && msg.additionalData.showInForeground) {
			var fn = null;
			if (msg.additionalData.link) {
				fn = gotoLink;
			}
			App.remoteNotification(msg.message, '', fn);
		}
	}

	return service;
});
