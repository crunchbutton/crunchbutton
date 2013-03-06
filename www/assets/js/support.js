App.support = {
	api : {
		sms : 'support/sms',
	},
	itIsSending : false
}

App.support.init = function(){
	$( 'body' ).append( App.render( 'support' ) );

	$(document).on('click', '.support-minimized', function() {
		App.support.maxi();
	});

	$(document).on('click', '.support-maximized-header', function() {
		App.support.mini();
	});

	$(document).on('click', '.support-maximized-button', function() {
		App.support.sendSMS();
	});	
}

App.support.maxi = function(){
	$( '.support-minimized' ).hide();
	$( '.support-maximized' ).show();

	$('input[name=support-name]').val( App.config.user.name || '' );
	$('input[name=support-phone]').val( App.config.user.phone || '' );
	$('textarea[name=support-message]').val( '' )
}

App.support.mini = function(){
	$( '.support-minimized' ).show();
	$( '.support-maximized' ).hide();
}

App.support.sendSMS = function(){
	if ($.trim($('input[name=support-name]').val()) == '' ){
		alert( 'Please enter your name.' );
		$('input[name=support-name]').focus();
		return;
	}

	if ($.trim($('input[name=support-phone]').val()) == '' ){
		alert( 'Please enter your phone.' );
		$('input[name=support-phone]').focus();
		return;
	}

	if (!App.phone.validate($.trim($('input[name=support-phone]').val()))) {
		alert( 'Please enter a valid phone.' );
		$('input[name=support-phone]').focus();
		return;
	}

	if ($.trim($('textarea[name=support-message]').val()) == '' ){
		alert( 'Please enter the message.' );
		$('input[name=support-message]').focus();
		return;
	}

	var data = {
		name: $('input[name=support-name]').val(),
		phone: $('input[name=support-phone]').val(),
		message: $('textarea[name=support-message]').val()
	};

	if (!App.support.itIsSending){
		App.support.itIsSending = true;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url:  App.service + App.support.api.sms,
			success: function( json ) {
				if( json.success ){
					alert( 'Thank you!' );
					App.support.mini();
				}
				App.support.itIsSending = false;
			}
		});
	}
}