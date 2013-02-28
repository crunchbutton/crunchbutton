
App.page.home = function() {

	$( '.nav-back' ).removeClass( 'nav-back-show' );
	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );

	$('.content').addClass('short-meal-list');
	
	App.showPage({
		page: 'home',
		title: 'Crunchbutton',
		data: {
			topCommunities: App.topCommunities,
			yourArea: App.loc.reverseGeocodeCity || 'your area',
			autofocus: $(window).width() >= 768 ? ' autofocus="autofocus"' : ''
		}
	});

	// @hacks
	if (navigator.userAgent.toLowerCase().indexOf('safari') > -1 && navigator.userAgent.toLowerCase().indexOf('mobile') == -1 && navigator.userAgent.toLowerCase().indexOf('chrome') == -1) {
		// safari desktop
		$('.location-detect').css({
			'margin-top': '2px',
			'height': '50px'
		});
	} else if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
		// firefox desktop
		$('.location-detect').css({
			'margin-top': '0px',
			'height': '52px'
		});
	}

	if (App.showReset){
		App.signin.passwordHelp.reset.init();
	}

	if (App.showErrorLocation) {
		setTimeout(function() {
			App.showErrorLocation = false;
		}, 100);
		$('.enter-location, .button-letseat-form').hide();
		$('.error-location').show();
		App.track('Location Error', {
			lat: App.loc.lat,
			lon: App.loc.lon,
			address: $('.location-address').val()
		});
	} else {
		if ($('.enter-location').length) {
			$('.location-address').val('');
			$('.error-location').fadeOut(100, function() {
				$('.enter-location, .button-letseat-form').fadeIn();
			});
		}
	}
};

App.page.foodDelivery = function() {
	if (!App.restaurants.list){
		App.foodDelivery.forceProcess = true;
		App.foodDelivery.preProcess();
		return;
	}
	App.page.foodDelivery.load();
};

App.page.restaurant = function(id) {

	$('.config-icon').addClass('config-icon-mobile-hide');
	$('.nav-back').addClass('nav-back-show');

	App.cartHighlightEnabled = false;

	$('.content').removeClass('smaller-width');
	$('.content').removeClass('short-meal-list');
	
	if ( !App.loc.lat ) {
		App.loc.lat = ( $.cookie('location_lat') ) ? parseFloat( $.cookie('location_lat') ) : App.config.user.location_lat;
	}
	if ( !App.loc.lon ) {
		App.loc.lon = ( $.cookie('location_lon') ) ? parseFloat( $.cookie('location_lon') ) : App.config.user.location_lon;
	}

	App.cache('Restaurant', id, function() {
		if (App.restaurant && App.restaurant.permalink != id) {
			App.cart.resetOrder();
		}

		App.restaurant = this;		
		var community = App.getCommunityById(App.restaurant.id_community);

		App.showPage({
			tracking: {
				title: 'Restaurant page loaded',
				data: {
					restaurant: App.restaurant.name
				}
			},
			page: 'restaurant',
			title: App.restaurant.name + ' | Food Delivery | Order from ' + ( community.name  ? community.name  : 'Local') + ' Restaurants | Crunchbutton',
			data: {
				restaurant: App.restaurant,
				presets: App.config.user.presets,
				user: App.config.user,
				community: community,
				form: {
					tip: App.order.tip,
					name: App.config.user.name,
					phone: App.phone.format(App.config.user.phone),
					address: App.config.user.address || App.loc.enteredLoc,
					notes: (App.config.user && App.config.user.presets && App.config.user.presets[App.restaurant.id_restaurant]) ? App.config.user.presets[App.restaurant.id_restaurant].notes : '',
					card: {
						number: App.config.user.card,
						month: App.config.user.card_exp_month,
						year: App.config.user.card_exp_year
					}
				}
			}
		});
		
		if (App.config.user.presets) {
			$('.payment-form').hide();
		}

		if (App.cart.hasItems()) {
			App.cart.reloadOrder();
		} else if (App.config.user && App.config.user.presets && App.config.user.presets[App.restaurant.id_restaurant]) {
			try {
				App.cart.loadOrder(App.config.user.presets[App.restaurant.id_restaurant]);
			} catch (e) {
				App.cart.loadOrder(App.restaurant.preset());
			}
		} else {
			App.cart.loadOrder(App.restaurant.preset());
		}

		// As the div restaurant-items has position:absolute this line will make sure the footer will not go up.
		$('.body').css({
			'min-height': $('.restaurant-items').height()
		});

		setTimeout(function() {
			var total = App.cart.updateTotal();
		},200);

		App.cartHighlightEnabled = false;
	
		if (App.order['pay_type'] == 'cash') {
			App.trigger.cash();
		} else {
			App.trigger.credit();
		}
	
		if (App.order['delivery_type'] == 'takeout' || App.restaurant.delivery != '1') {
			App.trigger.takeout();
		} else {
			App.trigger.delivery();
		}
	
		if (!App.config.user.id_user) {
			App.config.user.address = App.loc.enteredLoc;
			App.loc.enteredLoc = '';
		}
		
	});

};


/**
 * Order page. displayed after order, or at order history
 */
App.page.order = function(id) {

	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
	$( '.nav-back' ).addClass( 'nav-back-show' );

	if (App.justCompleted) {
		App.justCompleted = false;
	}

	$('.content').addClass('smaller-width');
	$('.main-content').css('width','auto');
	
	// Just to make sure the user button will be shown
	App.signin.checkUser();

	App.cache('Order', id, function() {
		var order = this;
		
		if (!order.uuid) {
			History.replaceState({},'Crunchbutton','/orders');
			return;
		}
		
		App.cache('Restaurant',order.id_restaurant, function() {
			var restaurant = this;

			App.showPage({
				title: 'Crunchbutton - Your Order',
				page: 'order',
				data: {
					order: order,
					restaurant: restaurant,
					user: App.user
				}
			});
			
		});
	});
};


/**
 * Legal page. loaded from xhr.
 */
App.page.legal = function() {
	App.currentPage = 'legal';
	$.getJSON('/api/legal',function(json) {
		$('.main-content').html(json.data);
		App.refreshLayout();
	});
};


/**
 * Help page. loaded from xhr.
 */
App.page.help = function() {
	App.currentPage = 'help';
	$.getJSON('/api/help',function(json) {
		$('.main-content').html(json.data);
		App.refreshLayout();
	});
};


/**
 * Order page. only avaiable after a user has placed an order or signed up.
 * @todo: change to account page
 */
App.page.orders = function() {
	if (!App.config.user.id_user) {
		History.pushState({}, 'Crunchbutton', '/');
		return;
	}

	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
	$( '.nav-back' ).addClass( 'nav-back-show' );

	$.getJSON('/api/user/orders',function(json) {
		App.showPage({
			title: 'Your Account',
			page: 'orders',
			data: {
				orders: json,
				user: App.user
			}
		});
	});

	App.refreshLayout();
};




/**
 * FoodDelivery's methods
 *
 * @author		pererinha
 */
App.foodDelivery = {};

// Before we change the url we need to make sure that there are restaurants at the typed place.
App.foodDelivery.preProcess = function() { 
	if( !App.foodDelivery.positions() ){
		return;
	}

	var url = App.service + 'restaurants?lat=' + App.loc.lat + '&lon=' + App.loc.lon + '&range=' + ( App.loc.range || App.defaultRange );
	App.restaurants.list = false;

	$.getJSON( url ,function(json) {	

		// Flag to make sure that this function will not be run twice.
		App.foodDelivery.IsLoading = true;
		// Reset the flag to make sure that this function will not be run twice.
		setTimeout( function(){
			App.foodDelivery.IsLoading = false;	
		}, 200 );

		// There is no restaurant near to the user. Go home and show the error.
		if( typeof json['restaurants'] == 'undefined' || json['restaurants'].length == 0 ){
			App.forceHome = true;
			App.showErrorLocation = true;
			App.loadHome();
			$('input').blur();
			return;
		} else {
			App.restaurants.list = [];
			for (var x in json.restaurants) {
				var res = new Restaurant(json.restaurants[x]);
				res.open();
				App.restaurants.list[App.restaurants.list.length] = res;
			};
			
			App.restaurants.list.sort(sort_by({
			    name: '_open',
			    reverse: true
			}, {
			    name: '_weight',
			    primer: parseInt,
			    reverse: true
			}));

			if( App.foodDelivery.forceProcess ){
				App.foodDelivery.forceProcess = false;
				App.page.foodDelivery.load();
			}
			var loc = '/' + App.restaurants.permalink;
			History.pushState({}, 'Crunchbutton', loc);		
		}
	}	);
}

App.foodDelivery.positions = function(){
	// Make sure that the positons were setted up.
	App.loc.lat = ( App.loc.lat && App.loc.lat != 0 ) ? App.loc.lat : parseFloat( $.cookie( 'location_lat' ) );
	App.loc.lon = ( App.loc.lon && App.loc.lon != 0 ) ? App.loc.lon : parseFloat( $.cookie( 'location_lon' ) );
	App.loc.range  = ( App.loc.range && App.loc.range > 0 ) ? App.loc.range : ( parseFloat( $.cookie( 'location_range' ) ) || App.defaultRange );

	App.loc.prep = ( App.loc.prep && App.loc.prep != '' ) ? App.loc.prep : $.cookie( 'location_prep' );
	App.loc.name_alt = ( App.loc.name_alt && App.loc.name_alt != '' ) ? App.loc.name_alt : $.cookie( 'location_name_alt' );

	// If we don't have the community try to load the place name.
	if( !App.loc.prep || !App.loc.name_alt ) {
		App.foodDelivery.loadPlaceName();
	}

	// Go home you don't have lat neither lon
	if( !App.loc.lat || !App.loc.lon) {
		App.forceHome = true;
		App.showErrorLocation = true;
		App.loadHome();
		$('input').blur();
		return false;
	} 
	return true;
}

App.foodDelivery.loadPlaceName = function() {
	if (google.maps.Geocoder){
		App.loc.reverseGeocode(function() {
			App.foodDelivery.tagLine();
			App.foodDelivery.title();
		});
	} else {
		setTimeout( function(){
			App.foodDelivery.loadPlaceName();
		}, 100 );
	}
}

App.foodDelivery.tagLine = function(){
	try {
		var slogan = App.slogan;
		var sloganReplace = ( App.loc.prep || ( App.loc.city_name ? 'at' : '' ) ) + ' ' + ( App.loc.name_alt || App.loc.city_name || '' ) ;
		sloganReplace = $.trim(sloganReplace);
		var tagline = App.tagline.tagline.replace('%s', sloganReplace);
		slogan = slogan.replace('%s', sloganReplace);
	} catch (e) {
		console.log(App.slogan, App.tagline);
		var slogan = '';
		var tagline = ''; 
	}
	return {
		slogan: slogan,
		tagline: tagline
	};
}

App.foodDelivery.title = function(){
	document.title =  ( App.loc.name_alt || App.loc.city_name || '' ) + ' Food Delivery | Order Food from ' + ( App.loc.name_alt || App.loc.city_name || 'Local') + ' Restaurants | Crunchbutton';
}

App.page.foodDelivery.load = function(){

	$( '.config-icon' ).removeClass( 'config-icon-mobile-hide' );
	$( '.nav-back' ).removeClass( 'nav-back-show' );

	if (App.restaurants.list.length == 4) {
		$('.content').addClass('short-meal-list');
	} else {
		$('.content').removeClass('short-meal-list');
	}
	$('.content').removeClass('smaller-width');

	App.currentPage = 'food-delivery';
	
	App.foodDelivery.title();
	var titles = App.foodDelivery.tagLine();
	
	App.hasLocation = true;
	
	App.showPage({
		page: 'restaurants',
		data: {
			slogan: titles.slogan,
			tagline: titles.tagline,
			restaurants: App.restaurants.list
		}
	});
}