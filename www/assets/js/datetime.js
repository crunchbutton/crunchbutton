var dateTime = {
	timer : 0,
	now : false,
	gears : null
};

dateTime.update = function(){
	var time = _gmtServer.split( '/' );
	dateTime.now = new Date( Number(time[0]), Number(time[1]-1), Number(time[2]), Number(time[3]), Number(time[4]), ( Number(time[5]) + dateTime.timer ) ) ;
	dateTime.timer++;
	dateTime.gears = setTimeout( function(){ dateTime.update(); }, 1000 );
}

// This method will be called by phonegap at the 'resume' event
dateTime.restart = function(){
	dateTime.timer = 0;
	dateTime.now = false;
	var now = new Date();
	_gmtServer = now.getUTCFullYear() + '/' + (now.getUTCMonth()+1) + '/' + now.getUTCDate() + '/' + now.getUTCHours() + '/' + now.getUTCMinutes() + '/' + now.getUTCSeconds();
}

// Update the gmt time based on server
dateTime.reload = function(){
	var url = App.service + 'gmt';
	App.http.get( url, {
		cache: false
	} ).success( function ( json ) {
		_gmtServer = json.gmt;
		dateTime.timer = 0;
		if( dateTime.gears ){
			clearTimeout( dateTime.gears );
		}
		dateTime.update();
		App.rootScope.$broadcast( 'appResume', false );
	} ).error( function(){ window.location.reload(); } );
}

dateTime.toString = function(){
	return dateTime.now.toString( 'dd MMMM yyyy HH:mm:ss' );
}

dateTime.getNow = function(){
	return Date.parse( dateTime.toString() );
}

dateTime.update();