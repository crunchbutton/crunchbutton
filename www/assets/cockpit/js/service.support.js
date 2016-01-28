NGApp.factory('TicketViewService', function($rootScope, $resource, $routeParams, $timeout, NotificationService, AccountService, SocketService, MainNavigationService, TicketService) {
	var service = {
		isTyping: false,
		id_support: false,
		messages: [],
		socket: SocketService.socket
	};

	service.sideInfo = { id_support: null };

	service.sideInfo.setTicket = function( id_support ){
		service.sideInfo.id_support = id_support;
		service.sideInfo.reset();
	}

	service.sideInfo.reset = function(){
		service.sideInfo.data = { id_support: service.sideInfo.id_support, messages: [], page: 0, loaded: 0, total: null, has_more: false };
		service._private = { first_load: true, current_scroll: 0, could_load: true };
		service.sideInfo.update_controller();
		setTimeout( function(){
			service.sideInfo.update_controller();
		} )
	};

	service.sideInfo.force_first_page = function(){
		service.sideInfo.reset();
		service.sideInfo.load();
	}

	service.sideInfo.scroll = function( action ){
		var container = $('.support-chat-contents-scroll');
		switch( action ){
			case 'begin':
				$timeout( function(){
					container.stop( true, false ).animate( { scrollTop: container[0].scrollHeight }, 0 );
					service.sideInfo.release();
				}, 300 );
				break;
			case 'current':
				service._private.current_scroll = container[0].scrollHeight;
				break;
			default:
				var position = action;
				setTimeout(function() {
					var finalScroll = container[0].scrollHeight;
					var goToScroll = finalScroll - service._private.current_scroll;
					container[0].scrollTop = goToScroll;
					service.sideInfo.release();
				}, 100 );
				break;
		}
	}

	service.sideInfo.release = function(){
		setTimeout(function() { service._private.could_load = true }, 500 );
	}

	service.sideInfo.add_message = function( message ){
		service.sideInfo.data.messages.push( message );
		service.sideInfo.data.total++;
		service.sideInfo.data.loaded++;
		service.sideInfo.update_controller();
		service.sideInfo.scroll( 'begin' );
	}

	service.sideInfo.load_ticket_page = function(){
		if( service.sideInfo.id_support ){
			MainNavigationService.link( '/ticket/' + service.sideInfo.id_support );
		}
	}

	service.sideInfo.update_controller = function(){
		$rootScope.$broadcast( 'triggerTicketInfoUpdated', service.sideInfo.data );
	}

	service.sideInfo.load = function(){

		if( service.sideInfo.id_support ){
			if( !service._private.could_load ){
				return false;
			}
			if( service.sideInfo.data.total !== null && service.sideInfo.data.loaded >= service.sideInfo.data.total ){
				return;
			}
			service._private.could_load = false;

			service.sideInfo.data.page++;

			var params = { id_support: service.sideInfo.id_support, page: service.sideInfo.data.page };

			TicketService.side_info( params, function( data ){
				service.sideInfo.scroll( 'current' );
				service.sideInfo.data.pexcard = data.pexcard;
				service.sideInfo.data.restaurant = data.restaurant;
				service.sideInfo.data.order = data.order;
				service.sideInfo.data.total = data.messages.total;
				var messages = [];
				for( x in data.messages.list ){
					if( data.messages.list[ x ].id_support_message ){
						messages.push( data.messages.list[ x ] );
					}
				}
				for( x in service.sideInfo.data.messages ){
					if( service.sideInfo.data.messages[ x ].id_support_message ){
						messages.push( service.sideInfo.data.messages[ x ] );
					}
				}
				service.sideInfo.data.messages = messages;
				service.sideInfo.data.loaded = service.sideInfo.data.messages.length;
				service.sideInfo.data.has_more = ( service.sideInfo.data.loaded >= service.sideInfo.data.total ) ? false : true;
				service.sideInfo.update_controller();
				if( service._private.first_load ){
					service.sideInfo.scroll( 'begin' );
					service._private.first_load = false;
					service.sideInfo.load_ticket_page();
					$rootScope.$broadcast( 'triggerSideViewTicket', {} );
				} else {
					service.sideInfo.scroll();
				}
			} );
			return true;
		}
		return false;
	};

	service.setViewTicket = function(id) {
		service.scope.viewTicket = id;
		service.sideInfo.setTicket( id );
	};

	$rootScope.$on('triggerViewTicket', function(e, ticket) {

		if( service.scope.viewTicket && service.scope.ticket &&
				service.scope.viewTicket.id_support == ticket.id_support &&
				service.scope.ticket.id_support == ticket.id_support ){
			service.sideInfo.force_first_page();
			return;
		}

		NotificationService.check();

		service.scope.viewTicket = ticket;
		service.scope.ticket = ticket;

		if (service.scope.viewTicket) {
			if( typeof service.scope.viewTicket == 'object' ){
				var id_support = service.scope.viewTicket.id_support
			} else {
				var id_support = service.scope.viewTicket;
			}
			service.socket.emit('event.subscribe', 'ticket.' + id_support);
		}
	});

	var notified  = new Array();

	$rootScope.$on('userAuthUpdated', function(e, data) {

		if (AccountService.user && AccountService.user.id_admin) {

			service.socket.emit('event.subscribe', 'user.preference.' + AccountService.user.id_admin );

			service.socket.on('user.preference', function(payload) {
				AccountService.user.prefs[payload.key] = payload.value;
			});

			SocketService.listen( 'user.preference.' + AccountService.user.id_admin, $rootScope).on( 'user.preference', function( payload ){
				AccountService.user.prefs[payload.key] = payload.value;
			} )

			if (AccountService.isSupport) {

				service.socket.emit('event.subscribe', 'ticket.update');

				SocketService.listen('ticket.update', $rootScope).on( 'change_ticket_status', function(){
					$rootScope.$broadcast( 'updateSideTickets' );
					$rootScope.$broadcast( 'updateHeartbeat' );
				} )

				SocketService.listen('tickets', $rootScope)
					.on('message', function(d) {
						console.debug('Recieved chat message: ', d);

						if (notified.indexOf(d.id_support_message) > -1) {
							console.log('already notified ', d.id_support_message);
							return;
						}

						notified.push(d.id_support_message);

						// update the chat room
						console.log('service.sideInfo.id_support',service.sideInfo.id_support);
						console.log('d.id_support',d.id_support);
						if( service.sideInfo.id_support && d.id_support && parseInt( service.sideInfo.id_support ) != parseInt( d.id_support ) ){
							console.log('force_first_page??');
							// service.sideInfo.force_first_page();
						}

						// https://github.com/crunchbutton/crunchbutton/issues/7579#issuecomment-172934677
						if (d.from == 'rep' ) {
							return;
						}

						if( AccountService.user && AccountService.user.prefs && AccountService.user.prefs[ 'notification-desktop-support-all' ] === false ){
							return;
						}

						if (d.id_support == service.scope.viewTicket) {
							//App.playAudio('support-message-recieved');
						} else {
							//App.playAudio('support-message-new');
						}

						NotificationService.notify(d.name, d.body, null, function() {
							try{
								if( document.getElementById('support-chat-box') ){
									document.getElementById('support-chat-box').focus();
								}
							}catch(e){}

						});

					})
					.on( 'sms_status', function( d ){

						console.debug('Recieved sms status update: ', d);

						updateSmsStatus( d );
					} )

			}

		} else {
			//service.socket.close();
			//service.socket = null;
		}
	});

	var updateSmsStatus = function( d ){
		var id_support_message = d.id_support_message;
		var status = d.status;
		var element = $( '#support-message-status-' + id_support_message );
		if( element && element.length ){
			element.attr( 'title', 'Status: ' + status );
		}
	}

	$rootScope.$watch('account.user.prefs["notification-desktop-support-all"]', function(e, value) {
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
		console.log('guid',guid);
		if( !service.sideInfo.id_support ){
			return;
		}
		TicketService.message( {
			id_support: service.sideInfo.id_support,
			body: message,
			guid: guid,
			note: add_as_note
		},
		// callback
		function(d) {
			if(!d.status){
				d.status = 'queued';
			}

			for ( var x in service.sideInfo.data.messages ) {
				if ( service.sideInfo.data.messages[x].guid == guid ) {
					d.guid = guid;
					service.sideInfo.data.messages[x] = d;
					notified.push( d.id_support_message );
					break;
				}
			}
		});
		if( callback ){
			callback()
		} else {
			service.scope.$apply(function() {
				console.log('no callback');
				service.sideInfo.add_message( { body: message, name: AccountService.user.firstName, timestamp: new Date().getTime(), sending: true, guid: guid, status: 'queued' } );
			});
		}
		$rootScope.$broadcast( 'replyFinished' );
	};

	service.scroll = function(instant) {
		setTimeout(function() {
			if( $('.support-chat-contents-scroll').length ){
				$('.support-chat-contents-scroll').stop(true,false).animate({
					scrollTop: $('.support-chat-contents-scroll')[0].scrollHeight
				}, instant ? 0 : 800);
			}
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
