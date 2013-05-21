// admin_restaurants.js


var DEBUG = {}

$(document).ready(function() {
	PAGE.init();
});


var PAGE = {
	init : function() {
		$('.chosen-select').select2();
		$('#restaurant-id').change(function(event) {
			document.location.href = '/restaurants/' + event.target.value;
		});
		$('.new-restaurant').click(function() {
			document.location.href = '/restaurants/new';
		});
	},
};


