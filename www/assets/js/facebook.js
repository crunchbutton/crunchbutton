App.facebook = {
	api : {
		status : {
			order : '/facebook/status/order/'
		},
		publish : {
			order : '/facebook/publish/order/',
			permission : '/facebook/publish/permission'
		},
		url_auth : '/facebook/url',
	},
	maxtries : 2,
	tries : 0,
	postType : 'auto'
}

App.facebook.postOrderResetTries = function(){
	App.facebook.tries = 0;
}

App.facebook.postOrder = function( uuid ){
	App.facebook.postOrderResetTries();
	App.facebook.postOrderRun( uuid );
}

App.facebook.checkPublishPermission = function( success, error ){
	FB.api( '/me/permissions', function ( response ) {
		if( response.data[0].publish_stream ){
			success();
		} else {
			error();
		}
	} );
}

App.facebook.postOrderRun = function( uuid ){
	if( !App.signin.facebook.isLogged ) {
		App.facebook.requestLogin( function(){ App.facebook.postOrderRun( uuid ); } )
	} else {
		App.facebook.checkPublishPermission( 
			// This function will be called if the user has allowed the publish_stream
			function(){
				if( App.facebook.postType == 'user' ){
					App.facebook.postOrderUser( uuid );	
				} else {
					App.facebook.postOrderAuto( uuid );	
				}
				
			}, 
			// This function will be called if the user has NOT allowed the publish_stream or he is not logged in
			function(){ 
				if( App.facebook.tries < App.facebook.maxtries ){
					App.facebook.tries++;
					App.facebook.requestPermission( function(){ App.facebook.postOrderRun( uuid ); } );
				} else {
					alert( 'Oops, error. Please try it again.' );
					window.location.reload();
				}
			} 
		);
	}
}

App.facebook.postOrderAuto = function( uuid ){
	var url = App.service + App.facebook.api.publish.order + uuid;
	$.getJSON( url, function( json ) {
		if( json.success ){
			if( json.success == 'status posted' ){
				App.facebook.postOrderResetTries();
				alert( 'Thank you for sharing!' );
			}
		} else if( json.error ){
			alert( 'Oops, error. Please try it again.' );
		}
	} );
}

App.facebook.postOrderUser = function( uuid ){
	console.log('App.facebook.postOrderUser');
	var url = App.service + App.facebook.api.status.order + uuid;
	console.log('url', url);
	$.getJSON( url, function( json ) {
		console.log('json', json);
		if( json.success ){
			var status = json.success;
			App.facebook.postOrderResetTries();
			FB.ui({
				method: 'stream.publish',
				// display: 'touch|frame|popup',
				user_message_prompt: 'CrunchButton: Publish This!',
				message: status.message,
				attachment: {
				name: status.name,
				caption: status.caption,
				description: status.description,
				href: status.link,
				media:[{'type':'image','src':status.picture,'href':status.link}],
				},
				action_links: [{ text: status.site_name, href: status.site_url }]
			},
			function(response) {
				if (response && response.post_id) {
					alert( 'Thank you for sharing!' );
				} else {
					alert( 'Oops, error. Please try it again.' );
				}
			} );
		} else if( json.error ){
			alert( 'Oops, error. Please try it again.' );
		}
	} );
}

App.facebook.requestLogin = function( callback ){
	FB.login( function( response ){ App.facebook.processStatus( response, callback ); }, { scope : App.facebookScope } );
}

App.facebook.requestPermission = function( callback ){
	FB.ui({
		 method: 'permissions.request',
		 'perms': App.facebookScope,
		 'display': 'iframe'
		},
		function( response ) {
			callback();
		}
	);
}

App.facebook.processStatus = function( response, callback ){
	if ( response.status === 'connected' && response.authResponse ) {
		callback();
	}
}

$(function() {
	$(document).on( 'click', '.post-order-facebook-auto', function() {
		var uuid = $( this ).attr( 'uuid' );
		App.facebook.postType = 'auto';
		App.facebook.postOrder( uuid );
	});
	$(document).on( 'click', '.post-order-facebook-user', function() {
		var uuid = $( this ).attr( 'uuid' );
		App.facebook.postType = 'user';
		App.facebook.postOrder( uuid );
	});
});
