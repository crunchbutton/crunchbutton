NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/support', {
			redirectTo: '/tickets'
		})
		.when('/tickets', {
			action: 'tickets',
			controller: 'TicketsViewCtrl',
			templateUrl: 'assets/view/tickets-view.html'
		})
		.when('/ticket/:id', {
			action: 'ticket',
			controller: 'TicketsTicketCtrl',
			templateUrl: 'assets/view/tickets-ticket.html'
		});
}]);


NGApp.controller('SideTicketsCtrl', function($scope, $rootScope, TicketService, TicketViewService) {
	$scope.params = {
		status: 'open'
	};

	$rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count) {
			console.debug('Updating support tickets...');
			TicketService.list($scope.params, function(tickets) {
				TicketViewService.scope.tickets = tickets;
			});
		}
	});
});

NGApp.controller('SideTicketCtrl', function($scope, $rootScope, TicketService, TicketViewService) {

	var loadTicket = function(id) {
		TicketService.get(id, function(ticket) {
			TicketViewService.scope.ticket = ticket;
			TicketViewService.scroll();
		});
	};

	$rootScope.$on('triggerViewTicket', function(e, ticket) {
		loadTicket(ticket == 'refresh' ? TicketViewService.scope.ticket : ticket);
	});
	
	$scope.send = TicketViewService.send;
	
	loadTicket(TicketViewService.scope.viewTicket);

});

NGApp.controller('SideSupportCtrl', function($scope, $rootScope, TicketViewService) {
	TicketViewService.scope = $scope;
	$scope.setViewTicket = TicketViewService.setViewTicket;
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

//	service.websocket = new WebSocket('wss://' + location.host + ':9000/test?_token=' + $.cookie('token'));
	
	service.socket = io('https://chat.cockpit.la');
	
	service.socket.on('connect', function (data) {
		console.debug('Connected to socket.io');
		service.socket.emit('auth', {
			token: $.cookie('token'),
			phpsessid: $.cookie('PHPSESSID'),
			host: location.host
		});

		if (AccountService.user.id_admin == 1) {
			service.socket.emit('event.subscribe', 'ticket.all');
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
	
	service.socket.on('event.response', function(payload) {
		console.debug('event response: ',payload);
	});

	var notified  = new Array();
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

	service.scroll = function() {
		$('.support-chat-contents-scroll').stop(true,false).animate({
			scrollTop: $('.support-chat-contents-scroll')[0].scrollHeight
		}, 1800);
	};

	return service;
});

NGApp.controller('TicketsViewCtrl', function($scope, $rootScope, TicketService, TicketViewService) {
	$scope.params = {
		status: 'open'
	};

	$rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count) {
			console.debug('Updating support tickets...');
			TicketService.list($scope.params, function(tickets) {
				$scope.tickets = tickets;
			});
		}
	});
	
	TicketService.list($scope.params, function(tickets) {
		$scope.tickets = tickets;
	});
});

NGApp.controller('TicketsTicketCtrl', function($scope, $rootScope, $routeParams, TicketService) {
	$rootScope.$broadcast('triggerViewTicket', $routeParams.id);

	TicketService.get($routeParams.id, function(ticket) {
		$scope.ticket = ticket;
		$scope.ready = true;
	});
});