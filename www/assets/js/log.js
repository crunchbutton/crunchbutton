App.log = {
	api : {
		url : 'log'
	},
	type : {
		order: 'order-js',
		account: 'account-js',
		location: 'location-js'
	}
}

App.log.doLog = function(data, callback) {
	var url = App.logService + App.log.api.url;
	var info = { data : data };
	info.type = ( data.type ) ? data.type : 'log-js';
	console.debug(data.type, info);
	
	$.ajax( {
		type: 'POST',
		url: url,
		dataType: 'json',
		data: info,
		success: function( json ){
			if( callback ){
				callback( json );
			}
		}
	} );
}

App.log.order = function( info, action ){
	App.log.doLog( { 'type' : App.log.type.order, 'info' : info, 'action' :  action } );
}

App.log.account = function( info, action ){
	App.log.doLog( { 'type' : App.log.type.account, 'info' : info, 'action' :  action } );
}

App.log.location = function( info, action ){
	App.log.doLog( { 'type' : App.log.type.location, 'info' : info, 'action' :  action } );
}