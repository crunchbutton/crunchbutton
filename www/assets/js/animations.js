// Animation push
NGApp.animation( '.animation-push', function( $rootScope ) {
	
	var windowWidth = $rootScope.windowWidth;
	
	return {

		enter : function( element, done ) {
			var width = element.css( 'width' );
			element.css( { position: 'absolute', left: ( windowWidth + 30 ), opacity : 1, width : width } );
			jQuery( element ).animate( { left: 0 }, 
					{ duration: 400,
						complete: function(){
												jQuery( element ).css( { position: 'static' } );
												done();
											} 
					}
				);
		},

		leave : function( element, done ) {
			var width = element.css( 'width' );
			element.css( { position: 'absolute', left: 0, width : width } );
			element.css( 'width', width );
			jQuery( element ).animate( { left: ( windowWidth * -1 ), opacity : 0 }, 
					{ duration: 200,
						complete: function(){
												jQuery( element ).css( { position: 'static' } );
												done();
											} 
					}
				);
		}
	};

});

// Animation pop
NGApp.animation( '.animation-pop', function( $rootScope ) {
	
	var windowWidth = $rootScope.windowWidth;
	
	return {

		enter : function( element, done ) {
			var width = element.css( 'width' );
			element.css( { position: 'absolute', left: ( windowWidth * -1 ), opacity : 1, width : width } );
			jQuery( element ).animate( { left: 0 }, 
					{ duration: 400,
						complete: function(){
												jQuery( element ).css( { position: 'static' } );
												done();
											} 
					}
				);
		},

		leave : function( element, done ) {
			var width = element.css( 'width' );
			element.css( { position: 'absolute', left: 0, width : width } );
			jQuery( element ).animate( { left: ( windowWidth + 30 ), opacity : 0 }, 
					{ duration: 200,
						complete: function(){
												jQuery( element ).css( { position: 'static' } );
												done();
											} 
					}
				);
		}
	};
});

// Animation fade
NGApp.animation( '.animation-fade', function() {
	
	return {

		enter : function( element, done ) {
			element.css( { opacity : 0 } );
			jQuery( element ).animate( { opacity: 1 }, 
					{ duration: 300,
						complete: function(){  done(); } 
					}
				);
		},

		leave : function( element, done ) {
			element.css( { opacity : 1 } );
			jQuery( element ).animate( { opacity : 0 }, 
					{ duration: 200,
						complete: function(){ done(); } 
					}
				);
		}
	};
});