$(function() {
	$('.meal-item').mousedown(function() {
		$(this).addClass('meal-item-down');
	});
	$('.meal-item').mouseup(function() {
		$(this).removeClass('meal-item-down');
	});
	if (document.documentElement.clientWidth > 900) {	
		$('.meal-items').masonry({
			itemSelector: '.meal-item',
			gutterWidth: 18, 
			isFitWidth: true
		});
	}
	
	$('.meal-item').live('click',function() {

	});

	var App = {
	
	};
});