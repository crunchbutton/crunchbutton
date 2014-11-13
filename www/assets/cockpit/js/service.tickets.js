
NGApp.factory('TicketService', function($rootScope, $resource, $routeParams) {

	var service = {};

	var tickets = $resource( App.service + 'tickets/:id_support', { id_support: '@id_support'}, {
		'load' : { 'method': 'GET', params : {} },
		'get' : { 'method': 'GET', params : {} }
	});
	
	service.list = function(params, callback) {
		tickets.query(params, function(data){
			callback(data);
		});
	}

	service.get = function(id_support, callback) {
		tickets.load({id_support: id_support}, function(data) {
			callback(data);
		});
	}
	
	$rootScope.$on('tickets', function(e, data) {
		$rootScope.supportMessages = {
			count: data,
			time: new Date
		};
	});

	return service;

});


NGApp.factory('TicketViewService', function($rootScope, $resource, $routeParams, NotificationService, AccountService) {
	var service = {
		isTyping: false
	};
	service.setViewTicket = function(id) {
		service.scope.viewTicket = id;
	};
	
	$rootScope.$on('triggerViewTicket', function(e, ticket) {
		NotificationService.check();

		service.scope.viewTicket = ticket;
		$rootScope.supportToggled = true;
		
		if (service.scope.viewTicket) {
			service.socket.emit('event.subscribe', 'ticket.' + service.scope.viewTicket);
		}
	});

	$rootScope.$on('userAuth', function(e, data) {

		if (AccountService.user && AccountService.user.id_admin) {

			service.socket = io('https://chat.cockpit.la');
			
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
			
			
			service.socket.on('event.response', function(payload) {
				console.debug('event response: ',payload);
			});
		
			var notified  = new Array();
			service.socket.on('user.preference', function(payload) {
				console.debug('Recieved preference update', payload);
				AccountService.user.prefs[payload.key] = payload.value;
				$rootScope.$apply();
			});

			service.socket.on('ticket.message', function(payload) {
		
				console.debug('Recieved chat message: ', payload);
				
				if (notified.indexOf(payload.id_support_message) > -1) {
					return;
				}
		
				notified.push(payload.id_support_message);
				
				if (payload.id_support == service.scope.viewTicket) {
					service.scope.ticket.messages.push(payload);
					service.scope.$apply();
					service.scroll();
				}
				
				if (payload.id_admin == AccountService.user.id_admin) {
					return;
				}
				
				if (payload.id_support == service.scope.viewTicket) {
					App.playAudio('support-message-recieved');
				} else {
					App.playAudio('support-message-new');
				}
		
				NotificationService.notify(payload.name, payload.body, null, function() {
					document.getElementById('support-chat-box').focus();
				});
		
			});

		} else {
			//service.socket.close();
			//service.socket = null;
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
	
	$rootScope.$watch('account.user.prefs["notification-desktop-support-all"]', function(e, value) {
		if (!service.socket) {
			return;
		}
		if (value == '1') {
			console.debug('Subscribing to all tickets');
			service.socket.emit('event.subscribe', 'ticket.all');
		} else {
			console.debug('Unsubscribing to all tickets');
			service.socket.emit('event.unsubscribe', 'ticket.all');
		}
	});
	
	service.send = function(message) {
		var msg = {
			url: '/api/tickets/' + service.scope.viewTicket + '/message',
			data: {
				body: message
			}
		};
/*
		service.scope.ticket.messages.push({
			body: message,
			name: AccountService.user.firstName,
			timestamp: new Date().getTime()
		});
		service.scope.$apply();
		service.scroll();
*/
		
		service.socket.emit('event.message', msg);
		service.isTyping = false;
	};
	
	var typingTimer;
	
	service.typing = function(val) {
		return;
		if (!service.isTyping) {
			service.isTyping = true;
			service.websocket.send({
				type: 'ticket.typing.start'
			});

		} else {
			if (!val) {
				service.isTyping = false;
				service.websocket.send({
					type: 'ticket.typing.stop'
				});
			}
		}
		
		if (typingTimer) {
			clearTimeout(typingTimer);
		}
		typingTimer = setTimeout(function() {
			if (service.isTyping) {
				service.isTyping = false;
				service.websocket.send({
					type: 'ticket.typing.stop'
				});
			}
		}, 5000);
	};


	service.scroll = function() {
		$('.support-chat-contents-scroll').stop(true,false).animate({
			scrollTop: $('.support-chat-contents-scroll')[0].scrollHeight
		}, 1800);
	};

	return service;
});
