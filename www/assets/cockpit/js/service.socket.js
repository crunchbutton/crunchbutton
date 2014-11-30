
NGApp.factory('eventSocket', function (socketFactory, $rootScope) {
//	$rootScope.$on('userAuth', function(e, data) {
	var myIoSocket = io.connect('https://chat.cockpit.la');

	mySocket = socketFactory({
		ioSocket: myIoSocket
	});

	return mySocket;
});

NGApp.factory('SocketService', function(eventSocket, AccountService, $rootScope) {
	var service = {};
	service.socket = eventSocket;
	
	service.listen = function(group, scope) {
		
		service.socket.emit('event.subscribe', group);

		// @todo: test better
		scope.$on('$destroy', function() {
			//service.socket.emit('event.unsubscribe', group);
		});
		

		
		var listener = {
			group: group,
			scope: scope,
			on: function(evt, fn) {
				var event = group + '.' + evt;
				console.log(event);
				service.socket.forward(event, scope);	
				scope.$on('socket:' + event, function (ev, data) {
					console.debug(ev, data);
					scope.$apply(function() {
						fn(data, event);
					});
				});
				return listener;
			}
		};

		return listener;
	};
	
	service.socket.on('connect', function (data) {
		console.debug('Connected to socket.io');

		service.socket.emit('auth', {
			token: $.cookie('token'),
			phpsessid: $.cookie('PHPSESSID'),
			host: location.host
		});
		
		service.socket.emit('event.subscribe', 'user.preference.' + AccountService.user.id_admin);
		
		if (AccountService.user && AccountService.user.prefs && AccountService.user.prefs['notification-desktop-support-all'] == '1') {
			console.debug('Subscribing to all tickets');
			service.socket.emit('event.subscribe', 'ticket.all');
		} else {
			console.debug('Unsubscribing to all tickets');
			service.socket.emit('event.unsubscribe', 'ticket.all');
		}
	});
	
	
	$rootScope.$on('user-preference', function(e, data) {
		if (!service.socket) {
			return;
		}
		console.log('Broadcasting preference change', data);

		service.socket.emit('event.broadcast', {
			to: {room: 'user.preference.' + AccountService.user.id_admin},
			event: 'user.preference',
			payload: {
				key: data.key,
				value: data.value
			}
		});
	});
	
	
	return service;
});

