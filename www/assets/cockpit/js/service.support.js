NGApp.factory('TicketViewService', function($rootScope, $resource, $routeParams, NotificationService, AccountService, SocketService) {
	var service = {
		isTyping: false,
		socket: SocketService.socket
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


	service.scroll = function(instant) {
		setTimeout(function() {
			$('.support-chat-contents-scroll').stop(true,false).animate({
				scrollTop: $('.support-chat-contents-scroll')[0].scrollHeight
			}, instant ? 0 : 800);			
		}, 100);
	};

	return service;
});
