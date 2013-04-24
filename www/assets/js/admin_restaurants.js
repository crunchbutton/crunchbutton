// admin_restaurants.js


var DEBUG = {}

$(document).ready(function() {
	PAGE.init();
});


var PAGE = {
	init : function() {
		$('.chosen-select').chosen();
		$('.chosen-select').focus();
	},
};


/*
    $(document).on('keyup', '.[name="order-search"]', function(e) {
			      if (e.which == 13) {
							        App.orders.load();
											      } 
						    }); 
		*/
		    

