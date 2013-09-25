var dateTime = {
	timer : 0,
	now : false
};

dateTime.update = function(){
	var time = _gmtServer.split( '/' );
	dateTime.now = new Date( Number(time[0]), Number(time[1]-1), Number(time[2]), Number(time[3]), Number(time[4]), ( Number(time[5]) + dateTime.timer ) ) ;
	dateTime.timer++; 
	setTimeout( function(){ dateTime.update(); } ,1000);
}

// This method will be called by phonegap at the 'resume' event
dateTime.restart = function(){
	dateTime.timer = 0;
	dateTime.now = false;
	var now = new Date();
	_gmtServer = now.getUTCFullYear() + '/' + (now.getUTCMonth()+1) + '/' + now.getUTCDate() + '/' + now.getUTCHours() + '/' + now.getUTCMinutes() + '/' + now.getUTCSeconds();
}

dateTime.toString = function(){
	return dateTime.now.toString( 'dd MMMM yyyy HH:mm:ss' );
}

dateTime.update();