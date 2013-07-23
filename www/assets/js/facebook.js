App.facebook = {
	api : {
		status : {
			order : 'facebook/status/order/'
		},
		publish : {
			order : 'facebook/publish/order/',
			permission : 'facebook/publish/permission'
		},
		token : 'facebook/token',
		login : 'facebook/url',
	},
	maxtries : 2,
	tries : 0,
	token : false,
	orderStatus : false
}

// This method pre load the order info that could be posted
App.facebook.preLoadOrderStatus = function( uuid ){
	var url = App.service + App.facebook.api.status.order + App._order_uuid;
	$.getJSON( url, function( json ) {
		if( json.success ){
			App.facebook.orderStatus = json.success;
		} else {
			App.facebook.orderStatus = false;
		}
	} );
}

// This method let the user type his message before post
App.facebook.postOrder = function(){
	if( !App.facebook.orderStatus ){
		App.facebook.preLoadOrderStatus();
		alert( 'Oops, please try again!' );
		return;
	}
	var status = App.facebook.orderStatus;
	FB.ui({
		method: 'stream.publish',
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
		}
	} );
}

App.facebook.postOrderByAPI = function( uuid ){
	App.facebook.postOrderResetTries();
	App.facebook.postOrderRun( uuid );
}

App.facebook.postOrderResetTries = function(){
	App.facebook.tries = 0;
}

App.facebook.checkPublishPermission = function( success, error ){
	var url = App.service + App.facebook.api.publish.permission;
	$.getJSON( url, function( json ) {
		if( json.success ){
			success();
		} else if( json.error ){
			error();
		}
	} );
}

App.facebook.postOrderRun = function( uuid ){
	if( !App.facebook.token ) {
		App.facebook.requestLogin( function(){ App.facebook.postOrderRun( uuid ); } )
	} else {
		App.facebook.checkPublishPermission( 
			// This function will be called if the user has allowed the publish_stream
			function(){
				App.facebook.postOrderAuto( uuid );	
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
	if( App.facebook.token ){
		var url = App.service + App.facebook.api.publish.order;
		$.ajax( {
			type: 'POST',
			url: url,
			data: { 'uuid': uuid },
			dataType: 'json',
			success: function( json ){
				if( json.success ){
					if( json.success == 'status posted' ){
						App.facebook.postOrderResetTries();
						alert( 'Thank you for sharing!' );
					}
				} else if( json.error ){
					alert( 'Oops, error. Please try it again.' );
				}
			}
		} );
	}
}

App.facebook.requestLogin = function( callback ){
	FB.login( function( response ){ App.facebook.processStatus( response, callback ); }, { scope : App.facebookScope } );
}

App.facebook.registerToken = function( token ){
	if( !App.facebook.token ){
		App.facebook.token = token;
		var url = App.service + App.facebook.api.token;
		$.cookie( 'fbtoken', token, { expires: new Date(3000,01,01), path: '/'});
	}
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
		if( response.authResponse.accessToken ){
			App.facebook.registerToken( response.authResponse.accessToken );	
		}
		callback();
	}
}

$(function() {
	$(document).on( 'touchclick', '.share-order-facebook-button', function() {
		App.facebook.postOrder();
	});
});
