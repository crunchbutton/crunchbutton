NGApp.factory('PushService', function($http, $location, $timeout, MainNavigationService, $rootScope) {

	var service = {
		id: null,
		badges: 0,
		registered: false,
		plugin: null
	};

	if (!App.isPhoneGap || !window.parent.PushNotification) {
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
			console.debug('resgistration callback', data);
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
				fn = gotoLink;
			}
			App.alert(msg.message, 'remote-notification', false, fn);
		}
	}

	return service;
});
