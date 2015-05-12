
NGApp.factory('eventSocket', function (socketFactory, $rootScope) {

//	$rootScope.$on('userAuth', function(e, data) {
	var myIoSocket = io.connect('https://event.cockpit.la');

	mySocket = socketFactory({
		ioSocket: myIoSocket
	});

	return mySocket;
});

NGApp.factory('SocketService', function(eventSocket, AccountService, $rootScope) {
	var service = {};
	var listening = {};
	service.socket = eventSocket;

	service.listen = function(group, scope) {

		if (!listening[group]) {
			service.socket.emit('event.subscribe', group, listening[group]);
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
				service.socket.emit('event.unsubscribe', group);

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


	service.socket.on('connect', function (data) {

		console.debug('Connected to socket.io');

		service.socket.emit('auth', {
			token: $.cookie('token'),
			phpsessid: $.cookie('PHPSESSID'),
			host: location.host
		});

		var subscribe = function(){

			if( AccountService && AccountService.user && AccountService.user.id_admin ){

				service.socket.emit( 'event.subscribe', 'user.preference.' + AccountService.user.id_admin);
				if (AccountService.user && AccountService.user.prefs && AccountService.user.prefs['notification-desktop-support-all'] == '1') {
					console.debug('Subscribing to all tickets');
					service.socket.emit('event.subscribe', 'ticket.all');
				} else {
					console.debug('Unsubscribing to all tickets');
					service.socket.emit('event.unsubscribe', 'ticket.all');
				}

			} else {
				setTimeout( function(){ subscribe() }, 100 );
			}
		}

		subscribe();

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

