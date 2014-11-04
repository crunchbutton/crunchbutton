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
		loadTicket(ticket);
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
		
		if (service.websocket && service.websocket.readyState == WebSocket.OPEN) {
			service.websocket.send(JSON.stringify({
				type: 'ticket.subscribe',
				ticket: service.scope.viewTicket
			}));
		}
	});

	service.websocket = new WebSocket('wss://' + location.host + ':9000/test?_token=' + $.cookie('token'));
	
	service.websocket.onopen = function(ev) {
		console.debug('Connected to chat server.');
	}
	service.websocket.onerror = function(ev) {
		console.error('Chat server error: ', ev.data);
	}; 
	service.websocket.onclose = function(ev) {
		console.debug('Chat server connection closed.');
	};
	
	service.send = function(message) {
		var msg = {
			type: 'ticket.message',
			ticket: service.scope.viewTicket,
			body: message,
			_token: $.cookie('token')
		};

		service.scope.ticket.messages.push({
			body: message,
			name: AccountService.user.firstName,
			timestamp: new Date().getTime()
		});
		service.scope.$apply();
		service.scroll();

		service.websocket.send(JSON.stringify(msg));
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

	service.websocket.onmessage = function(ev) {
		var payload = JSON.parse(ev.data);
		console.debug('Recieved chat message: ', payload);
		
		if (payload.type != 'ticket.message') {
			return;
		};
		
		App.playAudio('support-message-recieved');

		NotificationService.notify(payload.name, payload.body, null, function() {
			document.getElementById('support-chat-box').focus();
		});
		
		service.scope.ticket.messages.push(payload);
		service.scope.$apply();
		service.scroll();
	};
	
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