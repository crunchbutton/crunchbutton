App.log = {
	api : {
		url : 'log'
	}
}

App.log.doLog = function( data, callback ){
	var url = App.service + App.log.api.url;
	var info = { data : data };
	info.type = ( data.type ) ? data.type : 'log-js';
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