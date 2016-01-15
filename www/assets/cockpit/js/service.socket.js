
NGApp.factory('eventSocket', function (socketFactory, $rootScope, $q) {

	var myIoSocket = io.connect('https://event.cockpit.la');

	mySocket = socketFactory({
		ioSocket: myIoSocket
	});

	return mySocket;
});

NGApp.factory('SocketService', function(eventSocket, AccountService, $rootScope, $q) {
	if (!eventSocket) {
		console.error('No event socket');
		return null;
	}
	var service = {};
	var listening = {};
	service.socket = eventSocket;
	service.connected = false;
	$rootScope.socketConnected = false;

	service.createQ = function() {
		if (service.defferedConnection) {
			service.defferedConnection.reject();
		}

		service.defferedConnection = $q.defer();

		service.q = function(fn) {
			service.defferedConnection.promise.then(fn);
		};
	};

	service.createQ();

	// when the socket is fully authenticated
	service.q(function() {
		service.connected = true;
		$rootScope.socketConnected = true;

		if( AccountService && AccountService.user && AccountService.user.id_admin ){

			service.socket.emit( 'event.subscribe', 'user.preference.' + AccountService.user.id_admin);
			if (AccountService.user && AccountService.user.prefs && AccountService.user.prefs['notification-desktop-support-all'] == '1') {
				console.debug('Subscribing to all tickets');
				service.socket.emit('event.subscribe', 'ticket.all');
			} else {
				console.debug('Unsubscribing to all tickets');
				service.socket.emit('event.unsubscribe', 'ticket.all');
			}
		}
	});


	service.listen = function(group, scope) {

		if (!listening[group]) {
			service.q(function() {
				service.socket.emit('event.subscribe', group, listening[group]);
			});

			listening[group] = [scope.$id];
			console.debug('Creating listener to ' + group, listening[group]);
		} else {
			if (listening[group].indexOf(scope.$id) >= 0) {
				console.debug('Duplicate listener for ' + group, listening[group]);
			} else {
				listening[group].push(scope.$id);
				console.debug('Adding listener to ' + group, listening[group]);
			}
		}


		// @todo: test better
		scope.$on('$destroy', function() {
			if (listening[group] && listening[group].indexOf(scope.$id) >= 0) {
				listening[group].splice(listening[group].indexOf(scope.$id),1);

				service.q(function() {
					service.socket.emit('event.unsubscribe', group);
				});

				console.debug('Removing listener for ' + group, listening[group]);
			}
		});

		var Listener = function(group, scope) {
			this.group = group;
			this.scope = scope;

			var self = this;

			this.on = function(evt, fn) {
				var event = group + '.' + evt;
				console.debug('Listening to ' + this.group + ' for ' + event, listening[group]);

				service.socket.forward(event, self.scope);
				scope.$on('socket:' + event, function (ev, data) {

					console.debug('Recieved event', ev, data, self.group, listening[group]);

					scope.$apply(function() {
						fn(data, evt);
					});
				});

				return this;
			}
		};

		return new Listener(group, scope);
	};

	// response after sending auth credentials
	service.socket.on('auth', function (data) {
		console.debug('socket auth response:', data);

		if (data.status) {
			service.defferedConnection.resolve();
		}
	});

	service.socket.on('disconnect', function (data) {
		console.debug('Disconnected from event server.');
		$rootScope.socketConnected = true;
	});

	service.socket.on('connect', function (data) {
		console.debug('Connected to socket.io');

		var load = function(e, data) {
			console.debug('Socket authenticating...');

			if (AccountService.user && AccountService.user.id_admin) {

				console.debug('Have a user, authenticating with socket server');

				service.socket.emit('auth', {
					token: $.cookie('token'),
					phpsessid: $.cookie('PHPSESSID'),
					host: location.host
				});
			}
			watching = null;
		};

		var watching = null;

		if (!AccountService.init) {
			// we got here before the auth service was complete.
			watching = $rootScope.$on('userAuth', load);
		} else {
			load();
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

