var SupportChatInterface = { 
	thread : null,
	id_support : null,
	status : 'maximized',
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
				self.scroll();
			}
		} );
		self.thread = setTimeout( function(){
			self.history();
		}, 3000 );
	},
	minimize : function(){
		var self = this;
		self.status = 'minimized';
		$( '#chat-box-content-' + self.id_support ).hide();
		$( '#chat-minimize-' + self.id_support ).hide();
		$( '#chat-maximize-' + self.id_support ).show();
	},
	maximize : function(){
		var self = this;
		self.status = 'maximized';
		$( '#chat-box-content-' + self.id_support ).show();
		$( '#chat-minimize-' + self.id_support ).show();
		$( '#chat-maximize-' + self.id_support ).hide();
	},
	scroll: function(){
		var self = this;
		if( $( '#autoscroll_' + self.id_support ).is( ':checked' ) ){
			$( '#chat-history-' + self.id_support ).scrollTop( $( '#chat-history-' + self.id_support ).prop( 'scrollHeight' ) );
		}
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


var SupportChat = function( id ) {
	var self = this;
	$.extend( self, SupportChatInterface );
	self.id_support = id;
}
	