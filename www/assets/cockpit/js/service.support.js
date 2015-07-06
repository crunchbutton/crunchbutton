NGApp.factory('TicketViewService', function($rootScope, $resource, $routeParams, NotificationService, AccountService, SocketService, TicketService) {

	var service = {
		isTyping: false,
		id_support: false,
		messages: [],
		socket: SocketService.socket
	};

	service.setViewTicket = function(id) {
		service.scope.viewTicket = id;
		service.messages = [];
	};

	$rootScope.$on('triggerViewTicket', function(e, ticket) {

		NotificationService.check();

		if( !$rootScope.supportToggled ){

			service.scope.viewTicket = ticket;

			$rootScope.supportToggled = true;

			if (service.scope.viewTicket) {
				service.socket.emit('event.subscribe', 'ticket.' + service.scope.viewTicket);
			}
		}
	});

	var notified  = new Array();

	$rootScope.$on('userAuth', function(e, data) {

		if (AccountService.user && AccountService.user.id_admin) {

			service.socket.on('user.preference', function(payload) {
				console.debug('Recieved preference update', payload);
				AccountService.user.prefs[payload.key] = payload.value;
				$rootScope.$apply();
			});

			if (AccountService.isSupport) {


				SocketService.listen('tickets', $rootScope)
					.on('message', function(d) {
						console.debug('Recieved chat message: ', d);

						if (notified.indexOf(d.id_support_message) > -1) {
							return;
						}

						notified.push(d.id_support_message);

						if (d.id_admin == AccountService.user.id_admin) {
							return;
						}

						if (d.id_support == service.scope.viewTicket) {
							//App.playAudio('support-message-recieved');
						} else {
							//App.playAudio('support-message-new');
						}

						NotificationService.notify(d.name, d.body, null, function() {
							document.getElementById('support-chat-box').focus();
						});

					});
			}

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
			service.socket.emit('event.subscribe', 'tickets');
		} else {
			console.debug('Unsubscribing to all tickets');
			service.socket.emit('event.unsubscribe', 'tickets');
		}
	});

	service.send = function(message, add_as_note, callback) {
		var add_as_note = ( add_as_note ? true : false );
		var guid = App.guid();
		if( !service.id_support ){
			return;
		}
		TicketService.message({
			id_support: service.id_support,
			body: message,
			guid: guid,
			note: add_as_note
		}, function(d) {
			console.log('d',d);
			for (var x in service.messages) {
				console.log('service.messages[x].guid',service.messages[x].guid);
				if (service.messages[x].guid == guid) {
					d.guid = guid;
					service.messages[x] = d;
					notified.push(d.id_support_message);
					break;
				}
			}
		});

		if( callback ){
			callback()
		} else {
			service.scope.$apply(function() {
				$rootScope.$broadcast( 'newSupportMessage', { body: message, name: AccountService.user.firstName, timestamp: new Date().getTime(), sending: true, guid: guid } );
			});
		}
	};

	service.scroll = function(instant) {
		setTimeout(function() {
			$('.support-chat-contents-scroll').stop(true,false).animate({
				scrollTop: $('.support-chat-contents-scroll')[0].scrollHeight
			}, instant ? 0 : 800);
		}, 100);
	};

	/*
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
	*/

	return service;
});
