NGApp.factory('PushService', function($http, $location, $timeout, MainNavigationService, DriverOrdersService, $rootScope) {

	var service = {
		id: null,
		badges: 0,
		registered: false,
		plugin: null
	};

	if (!App.isPhoneGap || !window.parent.PushNotification) {
		return service;
	}

	var saveToken = function(id, complete) {
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

		service.plugin.on('registration', function(data) {
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

		var newOrder = function( id_order ){
			var open = function(){
				if( $location.path() !== '/drivers/order/' + id_order ){
					$rootScope.makeBusy();
					$timeout( function(){
						MainNavigationService.link( '/drivers/order/' + id_order );
					}, 400 );
					return;
				}
			}
			var cancel = function(){};
			var message = 'Open the new order #' + id_order +' ?';
			var title = 'New Order';
			App.confirm( message, title, open, cancel, 'Open,Cancel')
		}

		// Android
		if( window.parent && window.parent.device && window.parent.device.platform && ( window.parent.device.platform == 'android' || window.parent.device.platform == 'Android' ) ){
			switch ( msg.event ) {
				case 'message':
					if( msg.payload && msg.payload.message && msg.payload.title == 'Cockpit New Order' ){
						var id_order = msg.payload.id.replace( /^\D+/g, '');
						if( id_order ){
							newOrder( id_order );
							return;
						}
					}
					break;
			}
		}

		// iOS
		if ( window.parent && window.parent.device.platform && window.parent.device.model && window.parent.device.platform == 'iOS') {
			if( msg.alert ){
				switch ( true ) {
					case ( msg.alert.search( 'New order' ) >= 0 ):
						var id_order = msg.alert.replace( /^\D+/g, '');
						if( id_order ){
							newOrder( id_order );
						}
						break;
				}
			}
			if( parseInt( msg.foreground ) == 1 && msg.badge ){
				service.badge++;
				service.plugin.pushNotification.setApplicationIconBadgeNumber(complete, complete, service.badge);
			}
		}
	}

	return service;
});
