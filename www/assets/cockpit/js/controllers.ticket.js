NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tickets', {
			action: 'tickets',
			controller: 'TicketsCtrl',
			templateUrl: 'assets/view/tickets.html',
			reloadOnSearch: false

		}).when('/ticket/:id', {
			action: 'ticket',
			controller: 'TicketCtrl',
			templateUrl: 'assets/view/tickets-ticket.html'
		});
}]);

NGApp.controller('TicketsCtrl', function ($rootScope, $scope, $timeout, TicketService, TicketViewService, ViewListService, StaffService) {

	$rootScope.title = 'Tickets';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
			status: 'all',
			admin: 'all',
			fullcount: false
		},
		update: function() {
			update();
		}
	});

	var update = function(){
		TicketService.list($scope.query, function(d) {
			$scope.lotsoftickets = d.results;
			$scope.complete(d);
		});
	}

	$scope.closeTicket = function( id_support ){
		TicketService.openClose( id_support, function() { update(); } );
	}

	$scope.show_more_options = false;

	$scope.$watch( 'query.admin', function( newValue, oldValue, scope ) {
		if( newValue != '' && newValue != 'all' ){
			$scope.query.search = '';
		}
	});

	$scope.$watch( 'query.search', function( newValue, oldValue, scope ) {
		if( newValue != '' ){
			$scope.query.admin = null;
		}
	});

	$scope.moreOptions = function(){
		$scope.show_more_options = !$scope.show_more_options;
		if( $scope.show_more_options) {
			if( !$scope.communities ){
				StaffService.support_list( function( json ){
					$scope.staff = json;
				} );
			}
		}
	}

});

NGApp.controller('TicketCtrl', function($scope, $rootScope, $interval, $routeParams, OrderService, TicketService, TicketViewService, MapService, SocketService, CommunityService) {

	var id_support = $routeParams.id;

	$scope.refund = function(){
		OrderService.askRefund( $scope.ticket.order.id_order, function(){
			update();
		} );
	}

	$scope.do_not_pay_driver = function(){
		OrderService.do_not_pay_driver( $scope.ticket.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.openCommunityNoteContainer = function(){
		$rootScope.$broadcast( 'openCommunityNoteContainer', $scope.ticket.community.id_community );
	}

	$rootScope.$on( 'communityNoteSaved', function(e, data) {
		if( $scope.ticket.community && $scope.ticket.community.id_community ){
			CommunityService.lastNote( $scope.ticket.community.id_community, function( json ){
				console.log('$scope.ticket.community',$scope.ticket.community);
				console.log('json',json);
				$scope.ticket.community.note = json;
			} );
		}
	});



	$scope.do_not_pay_restaurant = function(){
		OrderService.do_not_pay_restaurant( $scope.ticket.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.do_not_reimburse_driver = function(){
		OrderService.do_not_reimburse_driver( $scope.ticket.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.comment = { isSaving: false, text: null };

	$scope.saveComment = function( close ){
		if( $scope.comment.isSaving ){
			return;
		}
		if( close && $scope.ticket.status != 'open' ){
			return;
		}
		if( $scope.comment.text ){
			$scope.comment.isSaving = true;
			TicketService.message( { 'id_support': id_support, 'body': $scope.comment.text, 'note': true }, function(){
				$scope.comment.isSaving = false;
				if( close ){
					if( $scope.ticket.status == 'open' ){
						$scope.openCloseTicket();
					}
				} else {
					update();
				}
				$scope.comment.isSaving = false;
				$scope.comment.text = '';
			} );
		} else {
			App.alert( 'Please type something!' );
		}
	}

	$scope.comment = function(){
		$scope.saveComment( false );
	}

	$scope.close_and_comment = function(){
		$scope.saveComment( true );
	}

	var cleanup;

	var draw = function() {

		if (!$scope.map || !$scope.ticket) {
			return;
		}

		MapService.trackOrder({
			map: $scope.map,
			order: $scope.ticket.order,
			restaurant: {
				location_lat: $scope.ticket.order._restaurant_lat,
				location_lon: $scope.ticket.order._restaurant_lon
			},
			driver: $scope.ticket.order.driver,
			id: 'ticket-driver-location',
			scope: $scope
		});
	};

	var update = function() {
		$rootScope.title = 'Ticket #' + id_support;
		$scope.loading = true;
		SocketService.listen('ticket.' + id_support, $scope).on('update', function(d) { update(); });
		$scope.comment.isSaving = false;
		TicketService.get( id_support, function(ticket) {
			$scope.ticket = ticket;
			$scope.loading = false;
			if (!cleanup) {
				if( ticket && ticket.order ){
					cleanup = $rootScope.$on('order-route-' + ticket.order.id_order, function(event, args) {
						$scope.$apply(function() {
							$scope.eta = args;
						});
						console.debug('Got route update: ', args);
					});
				}
			}
			$rootScope.$broadcast( 'triggerViewTicket', $scope.ticket );
			draw();
		});
	};

	$scope.openCloseTicket = function(){
		TicketService.openClose( id_support, function() { update(); } );
	}

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	update();

});
