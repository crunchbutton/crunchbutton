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

dateTime.toString = function(){
	return dateTime.now.toString( 'dd MMMM yyyy HH:mm:ss' );
}

dateTime.update();