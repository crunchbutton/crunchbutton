// admin_restaurants.js


var DEBUG = {}

$(document).ready(function() {
	PAGE.init();
});


var PAGE = {
	init : function() {
		$('.chosen-select').chosen();
		$('#restaurant-id').change(function(event) {
			document.location.href = '/admin/restaurants/' + event.target.value;
		});
		$('.new-restaurant').click(function() {
			document.href.location = '/admin/restaurants/new';
		});
	},
};


