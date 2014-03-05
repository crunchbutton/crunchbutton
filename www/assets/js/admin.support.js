var SupportChatInterface = { 
	thread : null,
	id_support : null,
	status : 'maximized',
	last_id_support_message : 0,
	id_support_message : 0,
	isFirstLoad : true,
	setLastIdSupportMessage: function( id ){
		var self = this;
		self.last_id_support_message = parseInt( id );
	},
	history : function(){
		var self = this;
		if( self.thread ){
			clearTimeout( self.thread );
		}		
		if( !self.id_support ){
			return;
		}
		var url = '/support/plus/chat/' + self.id_support + '/history';
		$.ajax( {
			url: url,
			complete: function( content ) {
				$( '#chat-history-' + self.id_support ).html( content.responseText );
				if( self.hasNewMessage() && !self.isFirstLoad ){
					if( self.status == 'minimized' ){
						self.warnNewMessageOn();
					} else {
						self.warnNewMessageOn();
						self.scroll();
						setTimeout( function(){
							self.warnNewMessageOff();
						}, 5000 );
					}
				}
				if( self.isFirstLoad ){
					self.scroll();
				}
				self.isFirstLoad = false;
			}
		} );
		if( self.live() ){
			self.thread = setTimeout( function(){
				self.history();
			}, 3000 );	
		}
	},
	live : function(){
		var self = this;
		return ( $( '#chat-live-' + self.id_support ).length > 0 );

	},
	container : function(){
		var self = this;
		return $( '#chat-container-' + self.id_support );
	},
	close : function(){
		var self = this;
		SupportChats.close( self.id_support );
		self = null;
	},
	toggle : function(){
		var self = this;
		if( self.status == 'minimized' ){
			self.maximize();		
		} else {
			self.minimize();		
		}
	},
	minimize : function(){
		var self = this;
		self.status = 'minimized';
		$( '#chat-box-content-' + self.id_support ).hide();
		$( '#chat-menu-' + self.id_support ).hide();
		$( '#chat-minimize-' + self.id_support ).hide();
		$( '#chat-maximize-' + self.id_support ).show();
		self.setPosition();
	},
	maximize : function(){
		var self = this;
		self.status = 'maximized';
		$( '#chat-box-content-' + self.id_support ).show();
		$( '#chat-menu-' + self.id_support ).show();
		$( '#chat-minimize-' + self.id_support ).show();
		$( '#chat-maximize-' + self.id_support ).hide();
		self.setPosition();
		self.warnNewMessageOff();
	},
	setPosition : function(){
		var self = this;
		var container = self.container();
		if( self.status == 'minimized' ){
			container.css( 'height', 41 );
		} else {
			container.css( 'height', 344 );
		}
	},
	hasNewMessage : function(){
		var self = this;
		var newMessage = false;
		if( self.last_id_support_message > self.id_support_message ){
			newMessage = true;
		}
		self.id_support_message = self.last_id_support_message;
		return newMessage;
	},
	warnNewMessageOn : function(){
		var self = this;
		$( '#chat-warning-' + self.id_support ).show();
	},
	warnNewMessageOff : function(){
		var self = this;
		$( '#chat-warning-' + self.id_support ).hide();
	},
	scroll: function(){
		var self = this;
		$( '#chat-history-' + self.id_support ).scrollTop( $( '#chat-history-' + self.id_support ).prop( 'scrollHeight' ) );
	},
	closeTicket : function(){
		var self = this;
		if( !confirm( 'Confirm close the ticket #' + self.id_support + '?') ){
			return;
		}
		var url = '/support/plus/' + self.id_support + '/close-ticket';
		$.ajax( {
			type : 'POST',
			url: url,
			complete: function( content ) {
				$( '#close-ticket-container-' + self.id_support ).hide();
				self.history();
			}
		} );
	},
	reply : function( text ){
		var self = this;
		$( '#message-box-' + self.id_support ).hide();
		$( '#sending-' + self.id_support ).show();
		var url = '/support/plus/' + self.id_support + '/conversation';
		$.ajax( {
			type : 'POST',
			data : { 'text' : text },
			url: url,
			complete: function( content ) {
				self.history();
				$( '#message-box-' + self.id_support ).show();
				$( '#sending-' + self.id_support ).hide();
			}
		} );
	}
}

// container-chats
var SupportChat = function( id_support ) {
	var self = this;
	$.extend( self, SupportChatInterface );
	self.id_support = id_support;
}

var SupportChats = {
	chats : {},
	count : function(){
		var chats = 0;
		for( x in SupportChats.chats ){
			if( SupportChats.chats[ x ] && SupportChats.chats[ x ].id_support ){
				chats++;	
			}
		}
		return chats;
	},
	closeTicket : function( id_support ){
		var self = this;
		if( !confirm( 'Confirm close the ticket #' + id_support + '?') ){
			return;
		}
		var url = '/support/plus/' + id_support + '/close-ticket';
		$.ajax( {
			type : 'POST',
			url: url,
			complete: function( content ) {
				$( '#close-ticket-button-' + id_support ).hide();
				$( '#status-support-' + id_support ).html( 'closed' );
			}
		} );
	},
	close : function( id_support ){
		var container = SupportChats.chats[ id_support ].container();
		container.html( '' );
		container.remove();
		delete SupportChats.chats[ id_support ];
		SupportChats.reorderWindows();
	},
	nextPosition : function(){
		var totalChats = SupportChats.count();
		var positionRight = 10 + parseInt( ( totalChats ) * 260 );
		return positionRight;
	},
	container : function( id_support, content ){
		return '<div class="chat-container" style="right:' + SupportChats.nextPosition() + 'px" id="chat-container-' + id_support + '">' + content + '</div>';
	},
	reorderWindows : function(){
		var chats = 0;
		for( x in SupportChats.chats ){
			if( SupportChats.chats[ x ] ){
				var container = SupportChats.chats[ x ].container();
				console.log('container',container);
				var positionRight = 10 + parseInt( ( chats ) * 260 );
				container.css( 'right', positionRight );
				chats++;	
			}
		}
	},
	createChat : function( id_support ){
		if( ( SupportChats.nextPosition() + 260 ) > $( window ).width() ){
			alert( 'It seems you have too many chat windows opened, please close one before open another.' );
			return;
		}
		if( !SupportChats.chats[ id_support ] ){
			var url = '/support/plus/chat/' + id_support;
			$.ajax( {
				url: url,
				complete: function( content ) {
					$( '#container-chats' ).append( SupportChats.container( id_support, content.responseText ) );
				}
			} );
		} else {
			if( SupportChats.chats[ id_support ].status == 'minimized' ){
				SupportChats.chats[ id_support ].maximize();
			} else {
				SupportChats.chats[ id_support ].minimize();	
			}
		}
	}
};