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
			$scope.ticket = ticket;
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


NGApp.factory('TicketViewService', function($rootScope, $resource, $routeParams) {
	var service = {
		isTyping: false
	};
	service.setViewTicket = function(id) {
		service.scope.viewTicket = id;
	};
	
	$rootScope.$on('triggerViewTicket', function(e, ticket) {
		service.scope.viewTicket = ticket;
		$rootScope.supportToggled = true;
		
		if (service.websocket && service.websocket.readyState == WebSocket.OPEN) {
			service.websocket.send(JSON.stringify({
				cmd: 'ticket.subscribe',
				ticket: service.scope.viewTicket
			}));
		}
	});

	service.websocket = new WebSocket('ws://localhost:9000/test?token=' + $.cookie('token'));
	
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
			cmd: 'ticket.message',
			ticket: service.scope.viewTicket,
			message: message
		};
		service.websocket.send(JSON.stringify(msg));
		service.isTyping = false;
	};
	
	var typingTimer;
	
	service.typing = function(val) {
		if (!service.isTyping) {
			service.isTyping = true;
			service.websocket.send({
				cmd: 'ticket.typing.start'
			});

		} else {
			if (!val) {
				service.isTyping = false;
				service.websocket.send({
					cmd: 'ticket.typing.stop'
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
					cmd: 'ticket.typing.stop'
				});
			}
		}, 5000);
	};

	service.websocket.onmessage = function(ev) {
		var msg = JSON.parse(ev.data); //PHP sends Json data
		var type = msg.type; //message type
		var umsg = msg.message; //message text
		var uname = msg.name; //user name
		var ucolor = msg.color; //color
		
		console.debug('Recieved chat message: ', umsg);

		if(type == 'usermsg') 
		{
			$('#message_box').append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
		}
		if(type == 'system')
		{
			$('#message_box').append("<div class=\"system_msg\">"+umsg+"</div>");
		}
		
		$('#message').val(''); //reset text
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