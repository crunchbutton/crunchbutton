App.suggestion.init = function() {

	$(document).on('touchclick', '.suggestion-link', function() {
		App.suggestion.show();
		App.suggestion.itIsSending = false;
	});

	$(document).on('touchclick', '.suggestion-form-button', function() {
		App.suggestion.send();
	});

	$(document).on('submit', '.suggestion-form', function() {
		return false;
	});

	// ToolTip
	$(document).on('touchclick', '.tooltip-help-mobile-touchable', function() {
		if ($('.tooltip-help-content-mobile' ).is(':visible')) {
			return;
		}
		setTimeout(function() {
			$('.tooltip-help-content-mobile').show();
		}, 100);
	});

	$(document).on('touchclick', '.tooltip-help-desktop', function() {
		if ($('.tooltip-help-content-desktop').is(':visible')) {
			return;
		}
		setTimeout(function() {
			$('.tooltip-help-content-desktop').show();
		}, 100);
	});
	
	$(document).on('touchclick', '.tooltip-help-content', function(e) {
		e.stopPropagation();
	});

	$(document).on('touchclick', 'body', function() {
		$('.tooltip-help-content-mobile:visible').hide();
		$('.tooltip-help-content-desktop:visible').hide();
	});
}

App.suggestion.send = function(){

	if ($.trim($('input[name=suggestion-name]').val()) == '' ){
		alert( 'Please enter a suggestion.' );
		$('input[name=suggestion-name]').focus();
		return;
	}

	var data = {
		restaurant: App.restaurant.permalink,
		name: $('input[name=suggestion-name]').val()
	};

	if (!App.suggestion.itIsSending){
		App.suggestion.itIsSending = true;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url:  App.service + 'suggestion/new',
			success: function(content) {
				console.log('saveds', new Date);
				App.suggestion.message(
					'<h1>Awesome, thanks!!</h1>' +
					'<div class="suggestion-thanks-text">If you really really wanna order it RIGHT NOW, call us at ' + App.callPhone( '646-783-1444' ) +  '</div>'
				);
			}
		});
	}
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
	$('.suggestion-container').dialog({
		modal: true,
		dialogClass: 'modal-fixed-dialog',
		width: App.modal.contentWidth(),
		open: function(event, ui) {
			$('.suggestion-name').focus();
		}
	});
}

