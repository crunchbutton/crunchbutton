App.suggestion.init = function(){

	$(document).on('click', '.suggestion-link', function() {
		App.suggestion.show();
		App.suggestion.itIsSending = false;
	});

	$(document).on('click', '.suggestion-form-button', function() {
		App.suggestion.send();
	});

	$(document).on('submit', '.suggestion-form', function() {
		return false;
	});

	// ToolTip
	$(document).on('click', '.tooltip-help-mobile-touchable', function() {
		if ($('.tooltip-help-content-mobile' ).is(':visible')) {
			return;
		}
		setTimeout(function() {
			$('.tooltip-help-content-mobile').show();
		}, 100);
	});

	$(document).on('click', '.tooltip-help-desktop', function() {
		if ($('.tooltip-help-content-desktop').is(':visible')) {
			return;
		}
		setTimeout(function() {
			$('.tooltip-help-content-desktop').show();
		}, 100);
	});
	
	$(document).on('click', '.tooltip-help-content', function(e) {
		e.stopPropagation();
	});

	$(document).on('click', 'body', function() {
		$('.tooltip-help-content-mobile:visible').hide();
		$('.tooltip-help-content-desktop:visible').hide();
	});
}

App.suggestion.html = function(){
	return '' +
	'<div class="suggestion-container">' +
		'<div class="suggestion-form-container">' +
			'<form class="suggestion-form">' +
				'<h1>What do you suggest?</h1>' +
				'<input type="text" maxlength="250" name="suggestion-name" tabindex="10" />' +
				'<div class="divider"></div>' +
				'<a href="javascript:;" class="suggestion-form-button">Suggest</a>' +
				'<div class="divider"></div>' +
			'</form>' +
			'<div class="suggestion-message">' +
			'</div>' +
		'</div>' +
		'<div class="suggestion-form-tip">' +
			'Crunchbutton "curates" menus. <br/>' +
			'We\'ve curated just the top food here. <br/>' +
			'You can suggest food, and, if it\'s really good, you\'ll see it on the menu soon.' +
		'</div>' +
	'</div>';
}

App.suggestion.send = function(){

	if( $.trim( $( 'input[name=suggestion-name]' ).val() ) == '' ){
		alert( 'Please enter the food\'s name.' );
		$( 'input[name=suggestion-name]' ).focus();
		return;
	}

	var suggestionURL = App.service + 'suggestion/new';

	var data = {};
	data[ 'type' ] = 'dish';
	data[ 'status' ] = 'new';
	data[ 'id_user' ] = ( App.config.user.id_user ) ? App.config.user.id_user : 'null';
	data[ 'id_restaurant' ] = App.restaurant.id;
	data[ 'id_community' ] = App.restaurant.id_community;
	data[ 'name' ] = $( 'input[name=suggestion-name]' ).val();

	if( !App.suggestion.itIsSending ){
		App.suggestion.itIsSending = true;
		$.ajax({
			type: "POST",
			dataType: 'json',
			data: data,
			url: suggestionURL,
			success: function(content) {
				App.suggestion.message( '<h1>Awesome, thanks!!</h1>' +
																'<div class="suggestion-thanks-text">If you really really wanna make order it RIGHT NOW, call us at ' + App.callPhone( '800-242-1444' ) +  '</div>' );
			}
		});
	}
}

App.suggestion.link = function(){
	return '<div class="suggestion-link-container">' +
						'<div class="suggestion-link-title">Really want something else?</div>' +
						'<a href="javascript:;" class="suggestion-link">Suggest other food</a>' +
					'</div>';
}

App.suggestion.message = function( msg ){
	/* Hides the form and shows the message box */
	$( '.suggestion-form' ).hide();
	$( '.suggestion-form-tip' ).hide();
	$( '.suggestion-message' ).show();
	$( '.suggestion-message' ).html( msg );
}

App.suggestion.show = function(){
	/* Resets the default values */
	$( 'input[name=suggestion-name]' ).val( '' );
	/* Shows the form and hides the message box  */
	$( '.suggestion-form' ).show();
	$( '.suggestion-form-tip' ).show();
	$( '.suggestion-message' ).hide();
	/* Shows the modal */
	setTimeout( function(){
			/* Shows the shield */
			App.modal.shield.show();
			$( '.suggestion-container' )
				.dialog( {
					dialogClass: 'modal-fixed-dialog',
					width: App.modal.contentWidth(),
					close: function( event, ui ) { App.modal.shield.close(); },
					open: function( event, ui ) { $( '.suggestion-name' ).focus(); }
				} );
		}, 100 );
}

App.suggestion.tooltipContainer = function( device ){
	var help = 'Crunchbutton "curates" menus. We\'ve curated just the top food here. ' +
											'If you really want something else, suggest it below.'

	return '<span class="tooltip-help-' + device + '-container">' + 
											( device == 'mobile' ? '<span class="tooltip-help-mobile-touchable">' : '' ) + 
												'<span class="tooltip-help tooltip-help-' + device + '"><span>?</span>' + 
											( device == 'mobile' ? '</span>' : '' ) + '</span>' +
											'<div class="tooltip-help-content tooltip-help-content-' + device + '">' +
												help +
											'</div></span>';
}
