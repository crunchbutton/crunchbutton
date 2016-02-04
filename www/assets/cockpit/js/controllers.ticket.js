NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tickets', {
			action: 'tickets',
			controller: 'TicketsCtrl',
			templateUrl: '/assets/view/tickets.html',
			reloadOnSearch: false

		})
		.when('/tickets/beta', {
			action: 'tickets-beta',
			controller: 'TicketsCtrl',
			templateUrl: '/assets/view/tickets.html',
			reloadOnSearch: false

		})
		.when('/ticket/:id', {
			action: 'ticket',
			controller: 'TicketCtrl',
			templateUrl: '/assets/view/tickets-ticket.html'
		});
}]);

NGApp.controller('TicketsCtrl', function ($rootScope, $scope, $timeout, $location, TicketService, TicketViewService, ViewListService, StaffService) {

	$rootScope.title = 'Tickets';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
			status: 'all',
			admin: null,
			fullcount: false
		},
		update: function() {
			update();
		}
	});
	$scope.beta = false;

	if( $location.path() == '/tickets/beta' ){
		$scope.beta = true;
	}

	var update = function(){
		if( $scope.beta ){
			TicketService.list_beta($scope.query, function(d) {
				$scope.lotsoftickets = d.results;
				$scope.complete(d);
			});
		} else {
			TicketService.list($scope.query, function(d) {
				$scope.lotsoftickets = d.results;
				$scope.complete(d);
			});
		}
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
		$rootScope.$broadcast('search-toggle');
	}

});

NGApp.controller('TicketCtrl', function($scope, $rootScope, $interval, $routeParams, OrderService, TicketService, TicketViewService, MapService, SocketService, CommunityService) {

	var id_support = $routeParams.id;

	$scope.refund = function(){
		OrderService.askRefund( $scope.ticket.order.id_order, $scope.ticket.order.delivery_service, $scope.ticket.restaurant.formal_relationship, function(){
			$rootScope.closePopup();
			setTimeout( function(){ App.alert( 'This order will be refunded soon!' ); }, 300 );
			update();
		} );
	}

	$scope.resend_notification_drivers = function(){
		$scope.isDriverNotifying = true;
		OrderService.resend_notification_drivers( $scope.ticket.order, function(result) {
			$scope.isDriverNotifying = false;
		});
	}

	$scope.resend_notification = function(){
		$scope.isRestaurantNotifying = true;
		OrderService.resend_notification( $scope.ticket.order, function(result) {
			$scope.isRestaurantNotifying = false;
		});
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

	$scope.do_not_pay_driver = function(){
		OrderService.do_not_pay_driver( $scope.ticket.order.id_order, function( result ){
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
			$scope.isCommenting = false;
			$scope.isClosing = false;
			return;
		}
		if( close && $scope.ticket.status != 'open' ){
			$scope.isClosing = false;
			return;
		}
		if( $scope.comment.text ){
			$scope.comment.isSaving = true;
			TicketService.message( { 'id_support': id_support, 'body': $scope.comment.text, 'note': true }, function( json ){
				$scope.comment.isSaving = false;
				$scope.isCommenting = false;
				$scope.isClosing = false;
				if( close ){
					if( $scope.ticket.status == 'open' ){
						$scope.openCloseTicket();
					}
				} else {
					update( true );
					TicketViewService.sideInfo.add_message( json );
				}
				$scope.comment.isSaving = false;
				$scope.comment.text = '';
			} );
		} else {
			App.alert( 'Please type something!' );
		}
	}

	$scope.comment = function(){
		$scope.isCommenting = true;
		$scope.saveComment( false );
	}

	$scope.close_and_comment = function(){
		$scope.isClosing = true;
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

	var update = function( ignoreBroadcast ) {
		$rootScope.title = 'Ticket #' + id_support;
		$scope.loading = true;
		SocketService.listen('ticket.' + id_support, $scope).on('update', function(d) { update(); });
		$scope.comment.isSaving = false;
		TicketService.get( id_support, function(ticket) {
			$scope.ticket = ticket;
			if( ticket.order ){
				$scope.order = ticket.order;
			}
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
			if( !ignoreBroadcast ){
				$rootScope.$broadcast( 'triggerViewTicket', $scope.ticket );
			}
			draw();
		});
	};

	$scope.openCloseTicket = function(){
		TicketService.openClose( id_support, function() { update( true ); } );
	}

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	$scope.openStaffNoteContainer = function( id_admin ){
		$rootScope.$broadcast( 'openStaffNoteContainer', id_admin );
	}

	$rootScope.$on( 'staffNoteSaved', function(e, data) {
		// $scope.ticket.driver.note = data;
	});


	$rootScope.$on( 'ticketStatusUpdated', function(e, data) {
		if( data.ignoreBroadcast ){
			update( true );
		} else {
			update();
		}
	});

	$scope.campus_cash_retrieving = false;

	$scope.campus_cash = function (){
		if( $scope.campus_cash_retrieving ){
			return;
		}

		$scope.campus_cash_retrieving = true;
		var params = { id_order: $scope.ticket.order.id_order, sha1: $scope.ticket.order.campus_cash_sha1 }

		OrderService.campus_cash( params, function( result ){
			if( result.success ){
				App.alert( result.success, null, null, function(){}, true );
			} else {
				App.alert( 'Error!' );
			}
			$scope.campus_cash_retrieving = false;
		} );
	}

	$scope.mark_cash_card_charged = function(){
		var success = function(){
			OrderService.mark_cash_card_charged( $scope.ticket.order.id_order, function( result ){
				if( result.success ){
					$scope.ticket.order.campus_cash_charged = true;
				} else {
					App.alert( 'Error marking order as paid!' );
				}
			} );
		}
		$scope.ticket.order.campus_cash_charged = false;
		var fail = function(){};
		App.confirm('After you mark this order as charged you will not be able to see the Student ID Number anymore.', 'Confirm?', success, fail, 'Confirm,Cancel', true);
	}

	$scope.approve_address = function(){
		OrderService.approve_address( $scope.ticket.order.id_order, function(){
			App.alert( 'Address approved!' )
			$scope.ticket.order._address.status = 'approved';
		} );

	}

	update();

});
