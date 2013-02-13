/**
 *
 * Crunchbutton
 *
 * @author: 	Devin Smith (http://devin.la)
 * @date: 		2012-06-20
 *
 */


var App = {
	cartHighlightEnabled: false,
	currentPage: null,
	slogans: ['Push a button. Get Food.'],
	tagline: 'Order the top food %s. For free. <br /> \
		After you order, everything is saved for future 1 click ordering. <br /> \
		<strong>Choose a restaurant:</strong> \
		',
	service: '/api/',
	cached: {},
	community: null,
	page: {},
	config: null,
	forceHome: false,
	order: {
		cardChanged: false,
		pay_type: 'card',
		delivery_type: 'delivery',
		tip: '15'
	},
	signin : {},
	suggestion : {},
	restaurants: {
		permalink : 'food-delivery'
	},
	modal : {
		shield : { 'isVisible' : false }
	},
	_init: false,
	_pageInit: false,
	_identified: false
};

App.loadRestaurant = function(id) {

	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );

	App.cache('Restaurant', id,function() {

		if (!this.open()) {
			var hours = '';

			for (var x in this._hours) {
				hours += x + ': ';
				for (var xx in this._hours[x]) {
					hours += this._hours[x][xx][0] + ' - ' + this._hours[x][xx][1] + (xx == 0 ? ', ' : '');
				}
				hours += "\n";
			}
			alert("This restaurant is currently closed. It will be open during the following hours:\n\n" + hours);
			App.busy.unBusy();
		} else {
			if (this.redirect) {
				location.href = this.redirect;
				return;
			}
			var loc = '/' + App.restaurants.permalink + '/' + this.permalink;
			History.pushState({}, 'Crunchbutton - ' + this.name, loc);
		}
	});
};

App.loadCommunity = function(id) {

	return App.routeCommunity( id );
	/* deprecated */
	if (App.loadedPage == id) {
		App.community = App.cached['Community'][id];
		App.loadedPage = null;
		/* return; */
	}

	App.cache('Community',id, function() {
		App.community = this;

		if (!App.community.id_community) {
			App.cache('Community','yale', function() {
				App.community = this;
				App.loadPage();
			});
			$('.main-content').show();
			$('.main-content').html('just a sec...');

			return;
		} else {
			App.loadPage();
		}
	});
};

App.routeCommunity = function(id) {
	if (App.loadedPage == id) {
		App.community = App.cached['Community'][id];
		App.loadedPage = null;
	}
	App.cache('Community',id, function() {
		App.community = this;
		var community = this;
		if( community.loc_lat && App.community.loc_lon ){
			App.loc.lat = community.loc_lat;
			App.loc.lon = community.loc_lon;
			$.cookie('location_lat', App.loc.lat, { expires: new Date(3000,01,01), path: '/'});
			$.cookie('location_lon', App.loc.lon, { expires: new Date(3000,01,01), path: '/'});	
			var loc = '/' + App.restaurants.permalink;
			History.pushState({}, 'Crunchbutton', loc);		
			return;
		}
	});
};

App.loadHome = function() {
	App.currentPage = 'home';
	History.pushState({}, 'Crunchbutton', '/');
	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );	
	if( App.showErrorLocation ){
		App.showErrorLocation = false;
		$('.enter-location, .button-letseat-form').fadeOut(100, function() {
			$('.error-location').fadeIn();
		});
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

App.page.resetPassword = function( path ){
	if( !App.signin.passwordHelp.reset.hasStarted ){
		App.signin.passwordHelp.reset.hasStarted = true;
		$( '.wrapper' ).append( App.signin.passwordHelp.reset.html( path ) );
		App.showReset = true;
		App.page.home();
	}
}

App.page.home = function() {
	document.title = 'Crunchbutton';

	$('.nav-back').removeClass('nav-back-show');

	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );

	$('.content').addClass('short-meal-list');

	var top = '';
	for (var x in App.topCommunities) {
		top += '<div class="meal-item" data-permalink-community="' + App.topCommunities[x].id_community + '">' +
			'<div class="meal-item-spacer"></div>' +
			'<div class="meal-item-content">' +
				'<div class="meal-pic" style="background: url(' + App.topCommunities[x].image + ');"></div>' +
					'<h2 class="meal-restaurant">' + App.topCommunities[x].name + '</h2>' +
					'<h3 class="meal-food">Top Restaurant: ' + App.topCommunities[x].restaurant + '</h3>' +
				'</div>' +
			'</div>';
	}

	$('.main-content').html('<div class="main-content-readable">' +
		'<div class="home-welcome home-welcome-click">' +
			'<h1>Order the best food with a click</h1>' +
			'<h2>We\'ve chosen the best food from the best restaurants. We save your order, delivery and payment info, so reordering is as easy as the click of a button.</h2>' +
		'</div>' +
		'<div class="home-welcome home-welcome-touch">' +
			'<h1>Order the best food with a tap</h1>' +
			'<h2>We\'ve chosen the best food from the best restaurants. We save your order, delivery and payment info, so reordering is as easy as a tap of a button.</h2>' +
		'</div>' +
	'</div>' +
	'<div class="enter-location">' +
		'<form class="button-letseat-formform" onsubmit="return false;">' +
		'<table class="button-letseat-table" cellpadding="0" cellspacing="0">' +
			'<tr>' +
				'<td style="width: 100%;"><input type="text" class="location-address" placeholder="Enter your zip code or full address" '+ ($(window).width() >= 768 ? 'autofocus="autofocus"' : '') + '></td>' +
				/*
				'<td>' +
					'<div class="location-detect">' +
						'<div class="location-detect-icon"></div>' +
						'<div class="location-detect-loader"></div>' +
					'</div>' +
				'</td>' +
				*/
			'</tr>' +
		'</table>' +
		'</form>' +
	'</div>' +
	'<div class="divider"></div>' +
	'<button class="button-letseat-form button-bottom"><div>Let\'s Eat!</div></button>' +
	'<div class="error-location" style="display: none;">' +
		'<div class="home-welcome home-welcom-error"><h1>Oh no! We aren\'t quite ready in <span class="loc-your-area change-location-inline">your area</span>.</h1></div>' +
		'<div class="content-item-locations">' +
			'<h1>Our most popular locations</h1>' +
		'</div>' +
		'<div class="content-padder-before"></div>' +
		'<div class="content-padder">' +
			'<div class="meal-items">' + top + '</div></div>' +
	'</div>');

	$( '.change-location-inline' ).live( 'click', function(){
		App.forceHome = true;
		App.loadHome();
		$('input').blur();
	} );


	//$('.location-address').val($.cookie('entered_address'));
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

	$('.loc-your-area').html(App.loc.reverseGeocodeCity || 'your area');

	if( App.showReset ){
		App.signin.passwordHelp.reset.init();
	}
};

App.page.community = function(id) {

	App.lastCommunity = id;
	App.currentPage = 'community';

	$( '.config-icon' ).removeClass( 'config-icon-mobile-hide' );

	App.cache('Community', id, function() {
		App.community = this;

		App.track('Community page loaded', {community: App.community.name});

		document.title = App.community.name + ' Food Delivery | Order Food from ' + (App.community.name_alt ? App.community.name_alt : 'Local') + ' Restaurants | Crunchbutton';

		var slogan = App.slogans[Math.floor(Math.random()*App.slogans.length)];
		var sloganReplace = App.community.prep + ' ' + App.community.name;
		var tagline = App.tagline.replace('%s', sloganReplace);
		slogan = slogan.replace('%s', sloganReplace);

		$('.main-content').html(
			'<div class="home-tagline"><h1>' + slogan + '</h1><h2>' + tagline + '</h2></div>' +
			'<div class="content-padder-before"></div><div class="content-padder"><div class="meal-items"></div></div>'
		);

		var rs = this.restaurants();

		if (rs.length == 4) {
			$('.content').addClass('short-meal-list');
		} else {
			$('.content').removeClass('short-meal-list');
		}
		$('.content').removeClass('smaller-width');

		for (var x in rs) {
			var restaurant = $('<div class="meal-item'+ (!rs[x].open() ? ' meal-item-closed' : '') +'" data-id_restaurant="' + rs[x]['id_restaurant'] + '" data-permalink="' + rs[x]['permalink'] + '"></div>');
			var restaurantContent = $('<div class="meal-item-content">');

			restaurantContent
				.append('<div class="meal-pic" style="background: url(' + rs[x]['img64'] + ');"></div>')
				.append('<h2 class="meal-restaurant">' + rs[x]['name'] + '</h2>')
				.append('<h3 class="meal-food">' + (rs[x].short_description || ('Top Order: ' + (rs[x].top() ? (rs[x].top().top_name || rs[x].top().name) : ''))) + '</h3>');

			if (rs[x].open()) {
				if (rs[x].delivery != '1') {
					restaurantContent.append('<div class="meal-item-tag">Take out only</div>');
				} else if (rs[x].isAboutToClose()) {
					restaurantContent.append('<div class="meal-item-tag about-to-close">Hurry, closes in ' + rs[x].isAboutToClose() +' min!</div>');
				} else if (!rs[x].delivery_fee) {
					// restaurantContent.append('<div class="meal-item-tag">Free Delivery</div>');
				}
			} else {
				restaurantContent.append('<div class="meal-item-tag-closed">Opens in a few hours</div>');
			}

			restaurant
				.append('<div class="meal-item-spacer"></div>')
				.append(restaurantContent);

			$('.meal-items').append(restaurant);
		}

	});
};

App.page.foodDelivery = function() {

	App.currentPage = 'food-delivery';
	App.loc.lat = ( App.loc.lat && App.loc.lat != 0 ) ? App.loc.lat : parseFloat( $.cookie( 'location_lat' ) );
	App.loc.lon = ( App.loc.lon && App.loc.lon != 0 ) ? App.loc.lon : parseFloat( $.cookie( 'location_lon' ) );

	$( '.config-icon' ).removeClass( 'config-icon-mobile-hide' );

	// Go home you don't have lat neither lon
	if( !App.loc.lat || !App.loc.lon ){
		App.forceHome = true;
		App.loadHome();
		$('input').blur();
		return;
	}

	document.title = 'Food Delivery | Order Food from Local Restaurants | Crunchbutton';

	var slogan = App.slogans[Math.floor(Math.random()*App.slogans.length)];
	var sloganReplace = '[some slogan here]';
	var tagline = App.tagline.replace('%s', sloganReplace);
	slogan = slogan.replace('%s', sloganReplace);

	var url = App.service + 'restaurants?lat=' + App.loc.lat + '&lon=' + App.loc.lon;
	$.getJSON( url ,function(json) {

		// There is no restaurant near to the user. Go home and show the error.
		if( typeof json['restaurants'] == 'undefined' || json['restaurants'].length == 0 ){
			App.forceHome = true;
			App.showErrorLocation = true;
			App.loadHome();
			$('input').blur();
			return;
		}

		// TODO: define the headline
		$('.main-content').html(
			'<div class="home-tagline"><h1>' + slogan + '</h1><h2>' + tagline + '</h2></div>' +
			'<div class="content-padder-before"></div><div class="content-padder"><div class="meal-items"></div></div>'
		);

		var rs = json.restaurants;
		if (rs.length == 4) {
			$('.content').addClass('short-meal-list');
		} else {
			$('.content').removeClass('short-meal-list');
		}
		$('.content').removeClass('smaller-width');
		App.hasLocation = true;
		for (var x in rs) {

			var restaurant = $('<div class="meal-item'+ (!rs[x]._open ? ' meal-item-closed' : '') +'" data-id_restaurant="' + rs[x]['id_restaurant'] + '" data-permalink="' + rs[x]['permalink'] + '"></div>');
			var restaurantContent = $('<div class="meal-item-content">');

			restaurantContent
				.append('<div class="meal-pic" style="background: url(' + rs[x]['img64'] + ');"></div>')
				.append('<h2 class="meal-restaurant">' + rs[x].name + '</h2>')
				.append('<h3 class="meal-food">' + (rs[x].short_description || ('Top Order: ' + (rs[x].top_name ? (rs[x].top_name || rs[x].top_name) : ''))) + '</h3>');

			if (rs[x]._open) {
				if (rs[x].delivery != '1') {
					restaurantContent.append('<div class="meal-item-tag">Take out only</div>');
				} else if (!rs[x].delivery_fee) {
					// restaurantContent.append('<div class="meal-item-tag">Free Delivery</div>');
				}
			} else {
				restaurantContent.append('<div class="meal-item-tag-closed">Opens in a few hours</div>');
			}

			restaurant
				.append('<div class="meal-item-spacer"></div>')
				.append(restaurantContent);

			$('.meal-items').append(restaurant);
		}
 });
};

App.page.restaurant = function(id) {

	App.currentPage = 'restaurant';
	App.cartHighlightEnabled = false;

	$('.content').addClass('smaller-width');
	$('.content').removeClass('short-meal-list');

	App.cache('Restaurant', id, function() {
		if (App.restaurant && App.restaurant.permalink != id) {
			App.cart.resetOrder();
		}

		App.restaurant = this;

		App.track('Restaurant page loaded', {restaurant: App.restaurant.name});
		document.title = App.restaurant.name + ' | Food Delivery | Order from Local Restaurants | Crunchbutton';

		$('.main-content').html(
			App.suggestion.tooltipContainer( 'mobile' ) +
			'<div class="cart-summary cart-summary-detail" data-role="header" data-position="fixed"><div class="cart-summary-icon"></div><div class="cart-summary-item-count"><span></span></div><div class="cart-summary-items"></div></div>' +
			'<div class="restaurant-name"><h1>' + App.restaurant.name + '</h1></div>' +
			(App.restaurant.image ? '<div class="restaurant-pic-wrapper"><div class="restaurant-pic" style="background: url(' + App.restaurant.img + ');"></div></div>' : '') +
			'<div class="main-content-readable">' +
				'<div class="cart-items"><div class="restaurant-item-title text-your-order">Your Order</div><div class="your-order-label" style="font-weight: bold; display: none;">(we\'ve chosen the most popular order, but you can order anything you want)</div><div class="divider"></div><div class="delivery-minimum-error">Add $<span class="delivery-min-diff">' + parseFloat(App.restaurant.delivery_min -  App.cart.total()).toFixed(2) + '</span> from menu to meet delivery minimum.</div><div class="cart-items-content"></div></div>' +
				'<div class="restaurant-items"></div>' +
				'<div class="divider"></div>' +
			'</div>' +
			'<div class="restaurant-payment-div"></div>'
		);

		var
			categories = App.restaurant.categories(),
			dishes, list;

		$('.restaurant-items').append('<div class="content-item-name content-item-main-name"><h1>Add to your order' + App.suggestion.tooltipContainer( 'desktop' ) +  '</h1></div>')

		for (var x in categories) {
			dishes = categories[x].dishes();

			list = $('<ul class="resturant-dishes resturant-dish-container" date-id_category="' + categories[x].id_category + '"></ul>');

			$('.restaurant-items').append('<div class="restaurant-item-title">' + categories[x].name + (categories[x].loc == '1' ? (' at ' + App.community.name) : '') + '</div>',
				list
			);

			for (var xx in dishes) {
				var dish = $('<li><a href="javascript:;" data-id_dish="' + dishes[xx].id_dish + '"><span class="dish-name">' + dishes[xx].name + '</span><span class="dish-price">($' + dishes[xx].price + ')</span></a></li>');
				list.append(dish);
			}
		}

		$('.restaurant-items').append( App.suggestion.link() );


		$('.cart-items').append('<div class="default-order-check"><input type="checkbox" id="default-order-check" checked><label for="default-order-check">Make this your default order for ' + App.restaurant.name + '</label></div>');

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

		if (App.config.user.presets) {

			App.drawPay(this);

			$('.payment-form').hide();

			var dp = $('<div class="delivery-payment-info main-content-readable"></div>');

			dp.append('<div class="dp-display-phone dp-display-item"><label>Your phone number:</label> ' + (App.config.user.phone ? App.phone.format(App.config.user.phone) : '<i>no phone # provided</i>') + '</div>');

			var paying = $(
					'<div class="dp-display-payment dp-display-item ">' +
						'<label>You are paying <span class="cash-order-aprox"></span> :</label> ' +
						'<span class="cart-breakdownDescription"></span> ' +
						'for a total of <span class="cart-total">$0.00</span> ' +
						'<span class="cart-paymentType"></span>' +
					'</div>');

			dp.append(paying);

			if (App.config.user.delivery_type == 'delivery' && App.restaurant.delivery == '1') {
				dp.append('<div class="dp-display-address dp-display-item"><label>Your food will be delivered to:</label><br />' + (App.config.user.address ? App.config.user.address.replace("\n",'<br />') : '<i>no address provided</i>') + '</div>');
			} else {
				dp.append('<div class="dp-display-address dp-display-item"><label>Address:</label> <i>takeout</i></div>');
			}

			dp.append('<div class="dp-display-address dp-display-item"><a href="javascript:;"><i>Change delivery or payment details</i></a></div>');

			dp.append('<div class="divider"></div>');

			$('.main-content').append(dp);
			$('.delivery-tip-amount').html(App.order.tip ? App.order.tip + '%' : 'no');

		} else {
			App.drawPay(this);
		}

		// Appends the suggestion's form
		$('.main-content').append( App.suggestion.html() );

		// As the div restaurant-items has position:absolute this line will make sure the footer will not go up.
		$('.body').css( { 'min-height' : $('.restaurant-items').height() } )

		setTimeout(function() {
			var total = App.cart.updateTotal();
			App.suggestion.init();
		},200);

		App.cartHighlightEnabled = false;
	});

};

/**
 * Adds the DOM elements for the payment information
 *
 * Adds order amount info, delivery info, payment info DOM (including tip%
 * selector. They method is called before the App.cart.updateTotal() method
 * gets called, so there is no information about the amount to pay yet.
 *
 * @returns void
 */
App.drawPay = function(restaurant)
{
	$('.main-content').append(
		'<form class="payment-form main-content-readable">' +
			'<div class="content-item-name"><h1>Your Info</h1></div>' +
			'<div class="your-info-label">(enter this once, and we\'ll save it for next time)</div>' +
			'<div class="delivery-info-container"></div><div class="divider"></div>' +
			'<div class="payment-info-container"></div><div class="divider"></div>' +
			'<div class="payment-total">' +
				'You\'re paying <span class="cash-order-aprox"></span> ' +
				'<span class="cart-breakdownDescription"></span> ' +
				'for a total of <span class="cart-total"></span> ' +
				'<span class="cart-paymentType"></span>' +
			' </div>' +
		'</form>' +

		'<div class="button-bottom-wrapper" data-role="footer" data-position="fixed"><button class="button-submitorder-form button-bottom"><div>Get Food</div></button></div>'
	);

	var fieldError = '';

	if (restaurant.delivery == '1' && restaurant.takeout == '1') {
		var deliveryInfo = '<label class="pay-title-label">Delivery Info</label>' +
			'<div class="input-item toggle-wrapper clearfix">' +
				'<a href="javascript:;" class="delivery-toggle-delivery toggle-item delivery-only-text">delivery</a> <span class="toggle-spacer delivery-only-text">or</span> <a href="javascript:;" class="delivery-toggle-takeout toggle-item">takeout</a>' +
			'</div>';
	} else if (restaurant.delivery == '1') {
		var deliveryInfo = '<label class="pay-title-label">Delivery Info</label>';
	} else {
		var deliveryInfo = '<label class="pay-title-label">Takeout Info</label>';
	}
	$('.delivery-info-container').append(

		'<div class="personal-info field-container">' +
			deliveryInfo +
			'<div class="divider"></div>' +
			'<label>Name</label>' +
			'<div class="input-item"><input type="text" name="pay-name" tabindex="2"></div><div class="divider"></div>' +

			'<label>Phone #</label>' +
			'<div class="input-item"><input type="tel" name="pay-phone" tabindex="3"></div><div class="divider"></div>' +
/*
Issue 13: Removed the password for while
			'<div class="password-field">' +
				'<label>Password (optional)</label>' +
				'<div class="input-item"><input type="password" name="pay-password" tabindex="4">' +
				'</div><div class="divider"></div>' +
			'</div>' +
*/
			'<label class="delivery-only">Address</label>' +
			'<div class="input-item delivery-only"><textarea name="pay-address" tabindex="5"></textarea></div>' +
			fieldError +
			'<div class="divider"></div>' +

			'<label>Notes</label>' +
			'<div class="input-item"><textarea name="notes" tabindex="6"></textarea></div><div class="divider"></div>' +

		'</div>'
	);

	$('.payment-info-container').append(

		'<div class="payment-info field-container">' +

			'<label class="pay-title-label">Payment Method</label>' +
			'<div class="input-item toggle-wrapper">' +
				'<a href="javascript:;" class="pay-toggle-credit toggle-item"><span>card</span></a> <span class="toggle-spacer">or</span>  <a href="javascript:;" class="pay-toggle-cash toggle-item"><span>cash</span></a>' +
			'</div><div class="divider"></div>' +

			'<div class="payment-card-info card-only"><p>Your credit card information is <br />super secure and encrypted.<br /><br /></p>' +
				'<div class="card-icons">' +
					'<img src="/assets/images/payment/Visa-40.png" alt="visa">' +
					'<img src="/assets/images/payment/Mastercard-40.png" alt="master card">' +
					'<img src="/assets/images/payment/Amex-40.png" alt="american express">' +
					'<img src="/assets/images/payment/Discover-40.png" alt="discover card">' +
				'</div>' +
			'</div>' +

			'<label class="card-only">Credit card #</label>' +
			'<div class="input-item card-only"><input type="tel" name="pay-card-number" tabindex="6"></div><div class="divider"></div>' +

			'<label class="card-only">Expiration</label>' +
			'<div class="input-item card-only">' +
				'<select name="pay-card-month" tabindex="7"><option>Month</option></select>' +
				'<select name="pay-card-year" tabindex="8"><option>Year</option></select><div class="divider"></div>' +
			'</div>' +

			'<div class="divider"></div><label class="card-only">Tip</label>' +
			'<div class="input-item card-only">' +
				'<select name="pay-tip" tabindex="9"></select>' +
				'<div class="divider"></div>' +
			'</div>' +
		'</div>'
	);

	var tips = [0,5,10,15,20,25];
	for (var x in tips) {
		$('[name="pay-tip"]').append('<option value="' + tips[x] + '">' + tips[x] + '%</option>');
	}
	for (var x=1; x<=12; x++) {
		$('[name="pay-card-month"]').append('<option>' + x + '</option>');
	}
	var date = new Date().getFullYear();
	for (var x=date; x<=date+20; x++) {
		$('[name="pay-card-year"]').append('<option>' + x + '</option>');
	}

	if (App.order['pay_type'] == 'cash') {
		App.trigger.cash();
	} else {
		App.trigger.credit();
	}

	if (App.order['delivery_type'] == 'takeout' || restaurant.delivery != '1') {
		App.trigger.takeout();
	} else {
		App.trigger.delivery();
	}
	$('[name="pay-tip"]').val(App.order.tip);
	$('[name="pay-name"]').val(App.config.user.name);
	$('[name="pay-phone"]').val(App.phone.format(App.config.user.phone));
	$('[name="pay-address"]').val(App.config.user.address || App.loc.enteredLoc);
	$('[name="pay-card-number"]').val(App.config.user.card);
	$('[name="pay-card-month"]').val(App.config.user.card_exp_month);
	$('[name="pay-card-year"]').val(App.config.user.card_exp_year);

	if (App.config.user && App.config.user.presets && App.config.user.presets[App.restaurant.id_restaurant]) {
		try {
			$('[name="notes"]').val(App.config.user.presets[App.restaurant.id_restaurant].notes);
		} catch (e) {}
	}

	if (!App.config.user.id_user) {
		App.config.user.address = App.loc.enteredLoc;
		App.loc.enteredLoc = '';
	}
/*
Issue 13: Removed the password for while
	App.signup.checkLogin();

	$( 'input[name=pay-phone]' ).live( 'change', function(){
		App.signup.checkLogin();
	} );
*/
};

App.page.order = function(id) {

	App.currentPage = 'order';
	App.cache('Order', id, function() {

		if (!this.uuid) {
			History.replaceState({},'Crunchbutton','/');
		}

		document.title = 'Crunchbutton - Your Order';
		App.order = this;

		var message, order = this;

		if (App.justCompleted) {
			App.justCompleted = false;
		}
		message = 'You Just Ordered Awesome Food!';

		$('.content').addClass('smaller-width');

		$('.main-content').css('width','auto');
		$('.main-content').html(
			'<div class="content-padder-before"></div>' +
			'<div class="order-info content-padder main-content-readable"></div>'
		);
		$('.order-info').html(
			'<span class="order-thanks-message">'+ message +'</span>' +
			'<br /><br />'
		);

		if (this.delivery_type == 'delivery') {
			$('.order-info').append('<b>Your delivery address:</b><br />' + this.address + '<br /><br />');
		} else {
			$('.order-info').append('<b>Takeout order</b><br /><br />');
		}

		$('.order-info').append('<b>Your phone #:</b><br />' + App.phone.format(this.phone) + '<br /><br />');

		$('.order-info').append('<b>Your order:</b>' + order._message + '<br /><br />');

		if (order.notes) {
			$('.order-info').append('<i>' + order.notes + '<br /><br />');
		}

		if (this.pay_type == 'card') {
			$('.order-info').append('<b>Your total:</b><br />$' + parseFloat(this.final_price).toFixed(2) + '<br /><br />');
		} else {
			$('.order-info').append('<b>Your approximate total</b>:<br />$' + parseFloat(this.final_price).toFixed(2) + '<br /><br />');
		}

		App.cache('Restaurant',order.id_restaurant, function() {
			$('.order-info').append('For updates on your order, please call<br />' + this.name + ': <b>' + this.phone + '</b><br /><br />');
			$('.order-info').append('To reach Crunchbutton, send a text to (646) 783-1444<br />or call <b>(800) 242-1444</b><br /><br />');
			$('.order-info').append('<span class="order-thanks-message">We\'ve saved your order for easy 1 click ordering next time.</span><br /><br />');
			if( !App.config.user.has_auth ){
				$('.order-info').append('<span class="signup-call-to-action"></span>');
				$('.signup-call-to-action').html( 'If you add a password, your favorite food can be 1 click ordered anywhere, including <a href="http://crunchbutton.com">crunchbutton.com</a> on your phone.' +
													'<a href="javascript:;" class="signup-add-password-button">Add a password now</a>' );
				$( '.signup-add-password-button' ).live( 'click', function(){
					App.signup.show( false );
				} );
			} else {
				$('.order-info').append( 'You can 1 click order anywhere, including <a href="http://crunchbutton.com">crunchbutton.com</a> on your phone.' );
			}
		});
	});
};

App.page.legal = function() {
	App.currentPage = 'legal';
	$.getJSON('/api/legal',function(json) {
		$('.main-content').html(json.data);
		App.refreshLayout();
	});
};

App.page.help = function() {
	App.currentPage = 'help';
	$.getJSON('/api/help',function(json) {
		$('.main-content').html(json.data);
		App.refreshLayout();
	});
};

App.page.orders = function() {

	App.currentPage = 'orders';

	$.getJSON('/api/user/orders',function(json) {

		$('.main-content').html(
			'<div class="main-content-readable">' +
				'<div class="restaurant-item-title">order history</div>' +
				'<ul class="resturant-dishes resturant-dish-container your-orders"></ul>' +
			'</div>'
		);

		var count = 0, restaurants = {};
		var orders = '';
		for (var x in json) {
			restaurants[json[x].id_restaurant] = true;
		}
		var triggerComplete = function() {
			count++;
			var y = 0;
			for (var i in restaurants) {
				y++;
			}

			if (count != y) {
				return;
			}
			for (var x in json) {
				var date = json[x].date.replace(/^[0-9]+-([0-9]+)-([0-9]+) ([0-9]+:[0-9]+):[0-9]+$/i,'$1/$2 $3');
				var order = '<li><a href="javascript:;" data-id_order="' + json[x].uuid + '"><span class="dish-name">' + App.cached['Restaurant'][json[x].id_restaurant].name + '</span><span class="dish-price">' + date + '</span></a></li>';
				orders += order;
			}
			$('.resturant-dishes').append(orders);

		};

		for (var x in restaurants) {
			App.cache('Restaurant',x,function() {
				triggerComplete();
			});
		}

		var signupFacebook = '<a href="javascript:;" class="signup-add-facebook-button">Connect with Facebook</a>';
		if( App.signin.facebook.isLogged || App.config.user.facebook ){
			signupFacebook = '';
		}

		var bottomMenu = '<div class="order-options">' +
										signupFacebook +
										'<a href="javascript:;" class="signout-button">Sign out</a>' +
										'<div class="divider"></div>' +
									'</div>';

		$( '.main-content-readable' ).append( bottomMenu );
		if( !App.bottomMenuBinded ){

			$( '.signout-button' ).live( 'click', function(){
				App.signin.signOut();
			} );

			$( '.signup-add-facebook-button' ).live( 'click', function(){
				// App.signup.facebook( true );
				App.signin.facebook.login();
			} );

			App.bottomMenuBinded = true;
		}

		App.refreshLayout();

	});
};

App.loadPage = function() {
	App.signin.checkUser();
	var
		url = History.getState().url.replace(/http(s)?:\/\/.*?\/(.*)/,'$2').replace('//','/'),
		path = url.split('/');

	if (!path[path.length-1]) {
		delete path[path.length-1];
	}

	if (!App.config) {
		return;
	}

	// hide whatever we have
	if (App._pageInit) {
		$('.main-content').css('visibility','0');
	} else {
		App._pageInit = true;
	}

	// force to a specific community
	if (!url) {
		App.loc.process();
		return;
	}

	var restaurantRegex = new RegExp('^\/(restaurant)|(' + App.restaurants.permalink + ')/', 'i');

	switch (true) {
		case /^legal/i.test(url):
			App.page.legal();
			break;

		case /^help/i.test(url):
			App.page.help();
			break;

		case /^orders/i.test(url):
			App.page.orders();
			break;

		case /^order/i.test(url):
			App.page.order(path[1]);
			break;

		case /^reset/i.test(url):
			App.page.resetPassword( path );
			break;

		case restaurantRegex.test(url):
			App.page.restaurant(path[1]);
			break;

		case new RegExp( App.restaurants.permalink +  '$', 'i' ).test(url):
			App.page.foodDelivery();
			break;

		default:
			App.routeCommunity( path[ 0 ] );
			$('.nav-back').removeClass('nav-back-show');
			$('.footer').removeClass('footer-hide');
			// App.page.community(App.community.permalink);
			setTimeout(scrollTo, 80, 0, 1);
			setTimeout( function(){ App.signin.checkUser(); }, 300 );
			//return;
			break;
	}

	if (App.config.env == 'live') {
		$('.footer').addClass('footer-hide');
	}
	$('.nav-back').addClass('nav-back-show');
	$( '.config-icon' ).addClass( 'config-icon-mobile-hide' );
	App.refreshLayout();
	$('.main-content').css('visibility','1');
	setTimeout(scrollTo, 80, 0, 1);
	setTimeout( function(){ App.signin.checkUser(); }, 300 );
};

App.refreshLayout = function() {

	setTimeout(function() {
		scrollTo(0, 1);
		return;

		// a really stupid fix for ios and fixed position with fadein
		var el = $('.cart-summary');

		if (el.length) {
			if (App.cartTimer) {
				clearTimeout(App.cartTimer);
			} else {
				var top = el.css('top');
				el.css('position','relative');
				el.css('top',0);
			}

			App.cartTimer = setTimeout(function() {
				el.css('top','43px');
				el.css('position','fixed');
				App.cartTimer = null;
			}, 1);

		}
	}, 80);
};

App.track = function() {
	if (App.config.env != 'live') {
		return;
	}
	if (arguments[0] == 'Ordered') {
		$('img.conversion').remove();
		mixpanel.people.track_charge(arguments[1].total);
		var i = $('<img class="conversion" src="https://www.googleadservices.com/pagead/conversion/996753959/?value=' + Math.floor(arguments[1].total) + '&amp;label=-oawCPHy2gMQp4Sl2wM&amp;guid=ON&amp;script=0&url=' + History.getState().url + '">').appendTo($('body'));
	}
	if (arguments[1]) {
		mixpanel.track(arguments[0],arguments[1]);
	} else {
		mixpanel.track(arguments[0]);
	}
};

App.identify = function() {

	if (App.config.env != 'live') {
		return;
	}
	if (!App._identified && App.config.user.uuid) {
		mixpanel.identify(App.config.user.uuid);
		App._identified = true;
	}
};

App.cart = {
	uuidInc: 0,

	items:   {},

	uuid: function() {
		var id = 'c-' + App.cart.uuidInc;
		App.cart.uuidInc++;
		return id;
	},

	add: function(item) {
		var
			id = App.cart.uuid(),
			opt = App.cached['Dish'][item].options(),
			options = [];

		if (arguments[1]) {
			options = arguments[1].options;
		} else {
			for (var x in opt) {
				if (opt[x]['default'] == 1) {
					options[options.length] = opt[x].id_option;
				}
			}
		}

		App.cart.items[id] = {
			id: item,
			options: options
		};

		var el = $('<div class="cart-item cart-item-dish" data-cart_id="' + id + '"></div>');
		el.append('<div class="cart-button cart-button-remove"><span></span></div>');

		el.append('<div class="cart-item-name">' + App.cache('Dish',item).name + ' <span class="cart-item-description">' + (App.cache('Dish',item).description != null ? App.cache('Dish',item).description : '') + '</span></div>');

		if (App.cached['Dish'][item].options().length) {
			el.append('<div class="cart-item-config"><a href="javascript:;">Customize</a></div>');
		}

		el.hide();

		$('.cart-items-content').append(el);
		//el.fadeIn();
		el.show();

		App.cart.updateTotal();

		App.track('Dish added');
	},

	clone: function(item) {
		var
			cartid = item.attr('data-cart_id'),
			cart = App.cart.items[cartid],
			newoptions = [];

		for (var x in cart.options) {
			newoptions[newoptions.length] = cart.options[x];
		}
		App.cart.add(cart.id, {
			options: newoptions
		});

		App.track('Dish cloned');
	},

	remove: function(item) {
		var
			cart = item.attr('data-cart_id');

		App.track('Dish removed');

		delete App.cart.items[cart];

		item.remove();
		$('.cart-item-customize[data-id_cart_item="' + cart + '"]').remove();

		App.cart.updateTotal();
	},

	/**
	 * Gets called after the cart is updarted to refresh the total
	 *
	 * @todo Gets called many times before the cart is updated, on load, and shouldn't
	 *
	 * @return void
	 */
	updateTotal: function() {
		var
			totalText  = '$' + this.total(),
			tipText    = '',
			feesText   = '',
			totalItems = 0,
			hasFees    = ((App.restaurant.delivery_fee && App.order.delivery_type == 'delivery') || App.restaurant.fee_customer) ? true : false;

		for (var x in App.cart.items) {
			totalItems++;
		}

		/* If the user changed the delivery method to takeout and the payment is card
		 * the default tip will be 0%. If the delivery method is delivery and the payment is card
		 * the default tip will be 15% (variable App.order.tip).
		 * If the user had changed the tip value the default value will be chosed one.
		 */
		var wasTipChanged = false;
		if( App.order.delivery_type == 'takeout' && App.order['pay_type'] == 'card' ){
			if( typeof App.order.tipHasChanged == 'undefined' ){
				App.order.tip = 0;
				wasTipChanged = true;
			}
		} else if( App.order.delivery_type == 'delivery' && App.order['pay_type'] == 'card' ){
			if( typeof App.order.tipHasChanged == 'undefined' ){
				App.order.tip = ( App.config.user.last_tip ) ? App.config.user.last_tip : 15; // Default value is 15
				wasTipChanged = true;
			}
		}

		if( wasTipChanged ){
			$('[name="pay-tip"]').val( App.order.tip );
			// Forces the recalculation of total because the tip was changed.
			totalText  = '$' + this.total();
		}

		if (App.restaurant.meetDeliveryMin() && App.order.delivery_type == 'delivery') {
			$('.delivery-minimum-error').show();
			$('.delivery-min-diff').html(App.restaurant.deliveryDiff());

		} else {
			$('.delivery-minimum-error').hide();
		}

		$('.cart-summary-item-count span').html(totalItems);

		/* If no items, hide payment line
		 * .payment-total      line for new customers
		 * .dp-display-payment is for stored customers
		 */
		if (!this.subtotal()) {
			$('.payment-total, .dp-display-payment').hide();
		} else {
			$('.payment-total, .dp-display-payment').show();
		}

		var breakdown    = App.cart.totalbreakdown();
		var extraCharges = App.cart.extraChargesText(breakdown);
		if (extraCharges) {
			$('.cart-breakdownDescription').html('$' + this.subtotal().toFixed(2) + ' (+'+ extraCharges +')' );
		} else {
			$('.cart-breakdownDescription').html('$' + this.subtotal().toFixed(2));
		}

		$('.cart-total').html(totalText);


		/**
		 * Crunchbutton doesnt collect the cash, the restaurant will ring up
		 * the order in their register, which may have different prices. The
		 * restaurant collects the cash, so its posible things may be
		 * different. This differs from when its card, crunchbutton collects
		 * the money directly so the price cant vary
		 */
		if (App.order['pay_type'] == 'card') {
			$('.cash-order-aprox').html('');
			$('.cart-paymentType').html('by card');
		} else {
			$('.cash-order-aprox').html('approximately');
			$('.cart-paymentType').html('');
		}

		if (App.cartHighlightEnabled && $('.cart-summary').css('display') != 'none') {
			$('.cart-summary').removeClass('cart-summary-detail');
			$('.cart-summary').effect('highlight', {}, 500, function() {
				$('.cart-summary').addClass('cart-summary-detail');
			});
		}

		if ($('.cart-total').html() == totalText) {
			//return;
		}

		if (!totalItems) {
			$('.default-order-check').hide();
		} else {
			$('.default-order-check').show();
		}

		var
			totalItems = {},
			name,
			text = '';
		$('.cart-summary-items').html('');

		for (var x in App.cart.items) {
			name = App.cached['Dish'][App.cart.items[x].id].name;
			if (totalItems[name]) {
				totalItems[name]++;
			} else {
				totalItems[name] = 1;
			}
		}

		for (x in totalItems) {
			text += x;
			if (totalItems[x] > 1) {
				text += '&nbsp;(' + totalItems[x] + ')';
			}
			text += ',&nbsp;&nbsp;';
		}

		$('.cart-summary-items').html(text.substr(0,text.length-13));

		$('.cart-item-customize-price').each(function() {
			var dish = $(this).closest('.cart-item-customize').attr('data-id_cart_item'),
				option = $(this).closest('.cart-item-customize-item').attr('data-id_option'),
				cartitem = App.cart.items[dish],
				opt = App.cached['Option'][option],
				price = opt.optionPrice(cartitem.options);

			$(this).html(App.cart.customizeItemPrice(price));
		});

	},

	customizeItemPrice: function(price) {
		return price != '0.00' ? '&nbsp;($' + price.toFixed(2) + ')' : '';
	},

	customize: function(item) {
		var
			cart = item.attr('data-cart_id'),
			old = $('.cart-item-customize[data-id_cart_item="' + cart + '"]');

		if (old.length) {
			old.remove();
		} else {
			var
				el = $('<div class="cart-item-customize" data-id_cart_item="' + cart + '"></div>').insertAfter(item),
				cartitem = App.cart.items[cart],
				obj = App.cached['Dish'][cartitem.id],
				opt = obj.options();

			for (var x in opt) {
				if (opt[x].id_option_parent) {
					continue;
				}
				if (opt[x].type == 'check') {

					var price = opt[x].optionPrice(cartitem.options);
					var check = $('<input type="checkbox" class="cart-customize-check">');

					if ($.inArray(opt[x].id_option, cartitem.options) !== -1) {
						check.attr('checked','checked');
					}
					var option = $('<div class="cart-item-customize-item" data-id_option="' + opt[x].id_option + '"></div>')
						.append(check)
						.append('<label class="cart-item-customize-name">' +
							opt[x].name + (opt[x].description || '') +
							'</label><label class="cart-item-customize-price">' +
							App.cart.customizeItemPrice(price) + '</label>'
						);
					el.append(option);

				} else if (opt[x].type == 'select') {

					var select = $('<select class="cart-customize-select">');
					for (var i in opt) {

						if (opt[i].id_option_parent == opt[x].id_option) {

							var option = $('<option value="' + opt[i].id_option + '">' + opt[i].name + (opt[i].description || '') + (opt[i].price != '0.00' || opt[x].price_linked == '1' ? (' ($' + (parseFloat(opt[i].price) + parseFloat(obj.price)).toFixed(2) + ')') : '') + '</option>');
							if ($.inArray(opt[i].id_option, cartitem.options) !== -1) {
								option.attr('selected','selected');
							}
							select.append(option);
						}
					}
					var option = $('<div class="cart-item-customize-item" data-id_option="' + opt[x].id_option + '"></div>')
						.append('<label class="cart-item-customize-select-name">' + opt[x].name + (opt[x].description || '') + '</label>')
						.append(select);

					el.append(option);

				}
			}
		}

		App.track('Dish customized');
	},

	customizeItem: function(item) {

		var
			cart = item.closest('.cart-item-customize').attr('data-id_cart_item'),
			cartitem = App.cart.items[cart],
			customitem = item.closest('.cart-item-customize-item'),
			opt = customitem.attr('data-id_option');

		if (opt) {
			if (item.hasClass('cart-customize-select')) {

				var obj = App.cached['Dish'][cartitem.id],
					opts = obj.options();

				for (var i in opts) {
					if (opts[i].id_option_parent != opt) {
						continue;
					}
					for (var x in cartitem.options) {
						if (cartitem.options[x] == opts[i].id) {
							cartitem.options.splice(x, 1);
							break;
						}
					}
				}

				cartitem.options[cartitem.options.length] = item.val();

			} else if(item.hasClass('cart-customize-check')) {

				if (item.is(':checked')) {
					cartitem.options[cartitem.options.length] = opt;
				} else {
					for (var x in cartitem.options) {
						if (cartitem.options[x] == opt) {
							cartitem.options.splice(x, 1);
							break;
						}
					}
				}
			}
		}
		console.log(cartitem.options);

		App.cart.updateTotal();

	},

	/**
	 * subtotal, delivery, fee, taxes and tip
	 *
	 * @category view
	 */
	extraChargesText: function(breakdown) {
		var elements = [];
		var text     = '';
		if (breakdown.delivery) {
			elements.push('$' + breakdown.delivery.toFixed(2) + ' delivery');
		}

		if (breakdown.fee) {
			elements.push('$' + breakdown.fee.toFixed(2) + ' fee');
		}
		if (breakdown.taxes) {
			elements.push('$' + breakdown.taxes.toFixed(2) + ' taxes');
		}
		if (breakdown.tip) {
			elements.push('$' + breakdown.tip.toFixed(2) + ' tip');
		}

		if (elements.length) {
			if (elements.length > 2) {
				var lastOne  = elements.pop();
				var elements = [elements.join(', ')];
				elements.push(lastOne);
			}
			var text     =  elements.join(' and ');
		}
		return text;
	},

	getCart: function() {
		var cart = [];
		for (x in App.cart.items) {
			cart[cart.length] = App.cart.items[x];
		}
		return cart;
	},

	/**
	 * Submits the cart order
	 *
	 * @returns void
	 */
	submit: function() {
		if (App.busy.isBusy()) {
			return;
		}

		App.busy.makeBusy();

		var read = $('.payment-form').length ? true : false;

		if (read) {
			App.config.user.name  = $('[name="pay-name"]').val();
			App.config.user.phone = $('[name="pay-phone"]').val().replace(/[^\d]*/gi,'');
			if (App.order['delivery_type'] == 'delivery') {
				App.config.user.address = $('[name="pay-address"]').val();
			}
			App.order.tip = $('[name="pay-tip"]').val();
		}

		var order = {
			cart:          App.cart.getCart(),
			pay_type:      App.order['pay_type'],
			delivery_type: App.order['delivery_type'],
			restaurant:    App.restaurant.id,
			make_default:  $('#default-order-check').is(':checked'),
			notes:         $('[name="notes"]').val(),
			lat: 		   App.loc.lat,
			lon: 		   App.loc.lon
		};

		if (order.pay_type == 'card') {
			order.tip = App.order.tip ? App.order.tip : '15';
		}

		if (read) {
			order.address  = App.config.user.address;
			order.phone    = App.config.user.phone;
			order.name     = App.config.user.name;
/*
Issue 13: Removed the password for while
			order.password = $( 'input[name=pay-password]' ).val( );
*/
			if (App.order.cardChanged) {
				order.card = {
					number: $('[name="pay-card-number"]').val(),
					month: $('[name="pay-card-month"]').val(),
					year: $('[name="pay-card-year"]').val()
				};
			} else {
				order.card = {};
			}
		}

		console.log('ORDER:',order);

		var errors = {};

		if (!order.name) {
			errors['name'] = 'Please enter your name.';
		}

		if (!App.phone.validate(order.phone)) {
			errors['phone'] = 'Please enter a valid phone #.';
		}

		if (order.delivery_type == 'delivery' && !order.address) {
			errors['address'] = 'Please enter an address.';
		}

		if (order.pay_type == 'card' && ((App.order.cardChanged && !order.card.number) || (!App.config.user.id_user && !order.card.number))) {
			errors['card'] = 'Please enter a valid card #.';
		}

		if (!App.cart.hasItems()) {
			errors['noorder'] = 'Please add something to your order.';
		}

		if (!$.isEmptyObject(errors)) {
			var error = '';
			for (var x in errors) {
				error += errors[x] + "\n";
			}
			$('body').scrollTop($('.payment-form').position().top-80);
			alert(error);
			App.busy.unBusy();
			App.track('OrderError', errors);
			return;
		}

		$.ajax({
			url: App.service + 'order',
			data: order,
			dataType: 'json',
			type: 'POST',
			complete: function(json) {

				json = $.parseJSON(json.responseText);

				if (json.status == 'false') {
					var error = '';
					for (x in json.errors) {
						error += json.errors[x] + "\n";
					}
					App.track('OrderError', json.errors);
					alert(error);

				} else {
					if (json.token) {
						$.cookie('token', json.token, { expires: new Date(3000,01,01), path: '/'});
					}

					$('.link-orders').show();

					order.cardChanged = false;
					App.justCompleted = true;

					var totalItems = 0;

					for (var x in App.cart.items) {
						totalItems++;
					}

					$.getJSON('/api/config', App.processConfig);
					App.cache('Order',json.uuid,function() {
						App.track('Ordered', {
							'total':this.final_price,
							'subtotal':this.price,
							'tip':this.tip,
							'restaurant': App.restaurant.name,
							'paytype': this.pay_type,
							'ordertype': this.order_type,
							'user': this.user,
							'items': totalItems
						});

						var loc = '/order/' + this.uuid;
						History.pushState({},loc,loc);
					});
				}
				App.busy.unBusy();
			}
		});
	}, // end App.cart.submit()

	subtotal: function() {
		var
			total = 0,
			options;

		for (var x in App.cart.items) {
			total += parseFloat(App.cached['Dish'][App.cart.items[x].id].price);
			options = App.cart.items[x].options;

			for (var xx in options) {
				var option = App.cached['Option'][options[xx]];
				if (option === undefined) continue; // option does not exist anymore
				total += parseFloat(option.optionPrice(options));
			}
		}
		total = App.ceil(total);
		return total;
	},

	/**
	 * delivery cost
	 *
	 * @return float
	 */
	_breackDownDelivery: function() {
		var delivery = 0;
		if (App.restaurant.delivery_fee && App.order.delivery_type == 'delivery') {
			delivery = parseFloat(App.restaurant.delivery_fee);
		}
		delivery = App.ceil(delivery);
		return delivery;
	},

	/**
	 * Crunchbutton service
	 *
	 * @return float
	 */
	_breackDownFee: function(feeTotal) {
		var fee = 0;
		if (App.restaurant.fee_customer) {
			fee = (feeTotal * (parseFloat(App.restaurant.fee_customer)/100));
		}
		fee = App.ceil(fee);
		return fee;
	},

	_breackDownTaxes: function(feeTotal) {
		var taxes = (feeTotal * (App.restaurant.tax/100));
		taxes = App.ceil(taxes);
		return taxes;
	},

	_breakdownTip: function(total) {
		var tip = 0;
		if (App.order['pay_type'] == 'card') {
			tip = (total * (App.order.tip/100));
		}
		tip = App.ceil(tip);
		return tip;
	},

	total: function() {
		var
			total = 0,
			dish,
			options,
			feeTotal    = 0,
			totalItems  = 0,
			finalAmount = 0
		;

		var breakdown = this.totalbreakdown();
		total        = breakdown.subtotal;
		feeTotal     = total;
		feeTotal    += breakdown.delivery;
		feeTotal    += breakdown.fee;
		finalAmount  = feeTotal + breakdown.taxes;
		finalAmount += this._breakdownTip(total);

		return App.ceil(finalAmount).toFixed(2);
	},

	/**
	 * Returns the elements that calculates the total
	 *
	 * breakdown elements are: subtotal, delivery, fee, taxes and tip
	 *
	 * @return array
	 */
	totalbreakdown: function() {
		var elements = {};
		var total    = this.subtotal();
		var feeTotal = total;

		elements['subtotal'] = this.subtotal();
		elements['delivery'] = this._breackDownDelivery();
		feeTotal            += elements['delivery'];
		elements['fee']      = this._breackDownFee(feeTotal);
		feeTotal            += elements['fee'];
		elements['taxes']    = this._breackDownTaxes(feeTotal);
		elements['tip']      = this._breakdownTip(total);
		return elements;
	},

	resetOrder: function() {
		App.cart.items = {};
		$('.cart-items-content, .cart-total').html('');
	},

	reloadOrder: function() {
		var cart = App.cart.items;
		App.cart.resetOrder();
		App.cart.loadFlatOrder(cart);
	},

	loadFlatOrder: function(cart) {
		for (var x in cart) {
			App.cart.add(cart[x].id,{
				options: cart[x].options ? cart[x].options : []
			});
		}
	},

	loadOrder: function(order) {
		// @todo: convert this to preset object
		try {
			if (order) {
				var dishes = order['_dishes'];
				for (var x in dishes) {
					var options = [];
					for (var xx in dishes[x]['_options']) {
						options[options.length] = dishes[x]['_options'][xx].id_option;
					}
					if (App.cached.Dish[dishes[x].id_dish] != undefined) {
						App.cart.add(dishes[x].id_dish,{
							options: options
						});
					}

				}
			}
		} catch (e) {
			console.log(e.stack);
			// throw e;
		}
		App.cart.updateTotal();
	},

	hasItems: function() {
		if (!$.isEmptyObject(App.cart.items)) {
			return true;
		}
		return false;
	}
};


App.busy = {
	isBusy: function() {
		return $('.app-busy').length ? true : false;
	},
	makeBusy: function() {
		//el.addClass('button-bottom-disabled');
		var busy = $('<div class="app-busy"></div>')
				.append($('<div class="app-busy-loader"><div class="app-busy-loader-icon"></div></div>'))


		$('body').append(busy);
	},
	unBusy: function() {
		$('.app-busy').remove();
		//el.removeClass('button-bottom-disabled');
	}
};


App.test = {
	card: function() {
		$('[name="pay-card-number"]').val('4242424242424242');
		$('[name="pay-card-month"]').val('1');
		$('[name="pay-card-year"]').val('2020');

		$('[name="pay-name"]').val('MR TEST');
		$('[name="pay-phone"]').val('***REMOVED***');
		$('[name="pay-address"]').val("123 main\nsanta monica ca");

		App.order.cardChanged = true;
	},
	logout: function() {
		$.getJSON('/api/logout',function(){ location.reload()});
	},
	cart: function() {
		alert(JSON.stringify(App.cart.items));
	},
	clearloc: function() {
		$.cookie('community', '', { expires: new Date(3000,01,01), path: '/'});
		$.cookie('location_lat', '', { expires: new Date(3000,01,01), path: '/'});
		$.cookie('location_lon', '', { expires: new Date(3000,01,01), path: '/'});
		location.href = '/';
	}
};

App.processConfig = function(json) {
	App.config = json;
	if (App.config.user) {
		App.identify();
		App.order['pay_type'] = App.config.user['pay_type'];
		App.order['delivery_type'] = App.config.user['delivery_type'];
		App.order['tip'] = App.config.user['tip'] || 15;
	}
};

App.loc = {
	distance: function(params) {

		var R = 6371; // Radius of the earth in km
		var dLat = (params.to.lat - params.from.lat).toRad();

		var dLon = (params.to.lon - params.from.lon).toRad();
		var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
			Math.cos(params.from.lat.toRad()) * Math.cos(params.to.lat.toRad()) *
			Math.sin(dLon/2) * Math.sin(dLon/2);
		var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
		var d = R * c; // Distance in km

		return d;
	},
	getClosest: function() {
		var closest;
		if (App.loc.lat) {
			for (x in App.communities) {
				App.communities[x].distance = App.loc.distance({
					from: {lat: App.loc.lat, lon: App.loc.lon},
					to: {lat: parseFloat(App.communities[x].loc_lat), lon: parseFloat(App.communities[x].loc_lon)}
				});
				if (!closest || App.communities[x].distance < closest.distance) {
					closest = App.communities[x];
				}
			}
		}
		return closest;
	},
	closest: function(complete) {
		if (google && google.loader && google.loader.ClientLocation) {
			App.loc.lat = google.loader.ClientLocation.latitude;
			App.loc.lon = google.loader.ClientLocation.longitude;

			complete();
		}
	},
	getLocation: function() {
		$('.location-detect-loader').show();
		$('.location-detect-icon').hide();

		var complete = function() {
			App.track('Locations Shared', {
				lat: App.loc.lat,
				lon: App.loc.lon
			});
			App.loc.reverseGeocode(function() {
				$('.location-detect-loader').hide();
				$('.location-detect-icon').show();
				$('.button-letseat-form').click();
			});
		};

		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position){
				App.loc.lat = position.coords.latitude;
				App.loc.lon = position.coords.longitude;
				complete();
			}, complete, {maximumAge: 60000, timeout: 5000, enableHighAccuracy: true});
		}
	},
	setFormattedLoc: function(results, raw) {
		if (raw) {
			App.loc.reverseGeocodeCity = raw;
		} else {
			switch (results[0].types[0]) {
				default:
				case 'administrative_area_level_1':
					App.loc.reverseGeocodeCity = results[0].address_components[0].long_name;
					break;
				case 'locality':
					App.loc.reverseGeocodeCity = results[0].address_components[0].long_name + ', ' + results[0].address_components[2].short_name;
					break;
				case 'street_address':
					App.loc.reverseGeocodeCity = results[0].address_components[2].long_name + ', ' + results[0].address_components[4].short_name;
					break;
				case 'postal_code':
					App.loc.reverseGeocodeCity = results[0].address_components[1].long_name + ', ' + results[0].address_components[3].short_name;
					break;
				case 'route':
					App.loc.reverseGeocodeCity = results[0].address_components[1].long_name + ', ' + results[0].address_components[3].short_name;
					break;
			}
		}
		$('.loc-your-area').html(App.loc.reverseGeocodeCity || 'your area');
	},
	preProcess: function() {
		if (google.loader.ClientLocation) {
			if (!$.cookie('location_lat')) {
				App.loc.lat = google.loader.ClientLocation.latitude;
				App.loc.lon = google.loader.ClientLocation.longitude;

				if (google.loader.ClientLocation.address.country_code == 'US' && google.loader.ClientLocation.address.region) {
					App.loc.setFormattedLoc(null, google.loader.ClientLocation.address.city + ', ' + google.loader.ClientLocation.address.region.toUpperCase());
				} else {
					App.loc.setFormattedLoc(null, google.loader.ClientLocation.address.city + ', ' + google.loader.ClientLocation.address.country_code);
				}
			}
		}
	},
	process: function() {
		var did = false;
		if (App.config.user && !App.forceHome) {

			if ($.cookie('location_lat')) {
				App.loc.lat = parseFloat($.cookie('location_lat'));
				App.loc.lon = parseFloat($.cookie('location_lon'));

			} else if (App.config.user.location_lat) {
				App.loc.lat = parseFloat(App.config.user.location_lat);
				App.loc.lon = parseFloat(App.config.user.location_lon);
			}
			var loc = '/' + App.restaurants.permalink;
			History.pushState({}, 'Crunchbutton', loc);			
			return;
		}

		if (!did && !App.forceHome && navigator.geolocation) {
			var complete = function() {
				$('.button-letseat-form').click();
			};
			navigator.geolocation.getCurrentPosition(function(position){
				App.loc.lat = position.coords.latitude;
				App.loc.lon = position.coords.longitude;

				App.track('Locations Shared', {
					lat: App.loc.lat,
					lon: App.loc.lon
				});

				complete();
			}, complete, {maximumAge: 60000, timeout: 5000, enableHighAccuracy: true});
		}

		if (!did) {
			App.forceHome = false;
			App.page.home();
		}

	},
	geocode: function(complete) {

		var geocoder = new google.maps.Geocoder();
		var forceLoc = null;

		App.track('Location Entered', {
			address: $('.location-address').val().toLowerCase()
		});

		switch ($('.location-address').val().toLowerCase()) {
			case 'yale':
			case 'new haven':
				return App.routeCommunity( 'yale' );
				// forceLoc = App.communities.yale.permalink;
				break;
			case 'brown':
			case 'providence':
				return App.routeCommunity( 'providence' );
				// forceLoc = App.communities.providence.permalink;
				break;
			case 'harvard':
			case 'cambridge':
			case 'the game':
			case 'hahvahd':
			case 'boston':
			case 'somerville':
				return App.routeCommunity( 'harvard' );
				// forceLoc = App.communities.harvard.permalink;
				break;
			case 'dc':
			case 'gwu':
			case 'gw':
			case 'george washington':
			case 'george washington university':
			case 'the district':
			case 'district of columbia':
			case 'foggy bottom':
			case 'georgetown':
			case 'gu':
			case 'georgetown university':
				return App.routeCommunity( 'gw' );
				// forceLoc = App.communities.gw.permalink;
				break;
			case 'la':
			case 'los angeles':
			case 'venice':
			case 'playa':
			case 'loyola':
			case 'lmu':
			case 'ucla':
			case 'santa monica':
			case 'sm':
			case 'mdr':
			case 'marina del rey':
			case 'culver':
				if (App.communities['los-angeles']) {
					return App.routeCommunity( 'los-angeles' );
					// forceLoc = App.communities['los-angeles'].permalink;
				}
				break;
		}

		if (forceLoc) {
			App.community = null;
			var loc = '/' + forceLoc;
			History.pushState({}, 'Crunchbutton', loc);
			return;
		}

		geocoder.geocode({'address': $('.location-address').val()}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				App.loc.lat = results[0].geometry.location.lat();
				App.loc.lon = results[0].geometry.location.lng();	
				var loc = '/' + App.restaurants.permalink;
				$.cookie('location_lat', App.loc.lat, { expires: new Date(3000,01,01), path: '/'});
				$.cookie('location_lon', App.loc.lon, { expires: new Date(3000,01,01), path: '/'});
				App.loc.setFormattedLoc( results );
				History.pushState({}, 'Crunchbutton', loc);
			} else {
				$('.location-address').val('').attr('placeholder','Oops! We couldn\'t find that address!');
			}
			complete();
		});
	},
	reverseGeocode: function(complete) {

		App.track('Location Reverse Geocode', {
			lat: App.loc.lat,
			lon: App.loc.lon
		});

		if (App.loc.reverseGeocodeResults) {
			$('.location-address').val(App.loc.reverseGeocodeResults);
			complete();
			return;
		}

		var geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(App.loc.lat, App.loc.lon);

		geocoder.geocode({'latLng': latlng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[1]) {
					$('.location-address').val(results[0].formatted_address);
				} else {
					$('.location-address').val('Where are you?!');
				}

				App.loc.reverseGeocodeResults = results[0].formatted_address;
				App.loc.setFormattedLoc(results);
				complete();
				setTimeout(function() {
					App.loc.reverseGeocodeResults = null;
				}, 1000 * 60 * 2);

			} else {
				$('.location-address').val('Oh no! We couldn\'t locate you');
				$('.location-detect-loader').hide();
				$('.location-detect-icon').show();
			}

		});
	}
}

App.trigger = {
	delivery: function() {
		$('.delivery-toggle-takeout').removeClass('toggle-active');
		$('.delivery-toggle-delivery').addClass('toggle-active');
		$('.delivery-only').show();
		App.order['delivery_type'] = 'delivery';
		App.cart.updateTotal();
	},
	takeout: function() {
		$('.delivery-toggle-delivery').removeClass('toggle-active');
		$('.delivery-toggle-takeout').addClass('toggle-active');
		$('.delivery-only, .field-error-zip').hide();
		App.order['delivery_type'] = 'takeout';
		App.cart.updateTotal();
	},
	credit: function() {
		$('.pay-toggle-cash').removeClass('toggle-active');
		$('.pay-toggle-credit').addClass('toggle-active');
		$('.card-only').show();
		App.order['pay_type'] = 'card';
		App.cart.updateTotal();
	},
	cash: function() {
		$('.pay-toggle-credit').removeClass('toggle-active');
		$('.pay-toggle-cash').addClass('toggle-active');
		$('.card-only').hide();
		App.order['pay_type'] = 'cash';
		App.cart.updateTotal();
	}
}

$(function() {
	$('.button-letseat-formform').live('submit', function () {
		$('.button-letseat-form').click();
		return false;
	});

	$('.button-letseat-form').live('click', function() {
		
		var complete = function() {
			var closest = App.loc.getClosest();

			if (closest) {
				if (closest.distance < 25) {
					App.community = closest;
					var loc = '/' + closest.permalink;
					App.community = null;
					History.pushState({}, 'Crunchbutton', loc);
					$.cookie('community', closest.permalink, { expires: new Date(3000,01,01), path: '/'});
					$.cookie('location_lat', App.loc.lat, { expires: new Date(3000,01,01), path: '/'});
					$.cookie('location_lon', App.loc.lon, { expires: new Date(3000,01,01), path: '/'});

					App.track('Location Success', {
						lat: App.loc.lat,
						lon: App.loc.lon,
						address: $('.location-address').val(),
						community: closest.permalink
					});

				} else {

					App.track('Location Error', {
						lat: App.loc.lat,
						lon: App.loc.lon,
						address: $('.location-address').val()
					});
					return;
				}
			}
		};

		if ($('.location-address').val() && $('.location-address').val() != App.loc.reverseGeocodeResults) {
			App.loc.enteredLoc = $('.location-address').val();
			$.cookie('entered_address', App.loc.enteredLoc, { expires: new Date(3000,01,01), path: '/'});
			App.loc.geocode(complete);
		} else if (App.loc.lat) {
			complete();
		} else {
//			$('.location-address').val('').attr('placeholder','Hey! Enter your address here');
		}
	});

	$('.delivery-toggle-delivery').live('click',function() {
		App.trigger.delivery();
		App.track('Switch to delivery');
	});

	$('.delivery-toggle-takeout').live('click',function() {
		App.trigger.takeout();
		App.track('Switch to takeout');
	});

	$('.pay-toggle-credit').live('click',function() {
		App.trigger.credit();
		App.track('Switch to card');
	});

	$('.pay-toggle-cash').live('click',function() {
		App.trigger.cash();
		App.track('Switch to cash');
	});

	$('.location-detect').live('click', function() {
		App.loc.getLocation();
	});

	$('.location-detect').live({
		mousedown: function() {
			$(this).addClass('location-detect-click');
		},
		touchstart: function() {
			$(this).addClass('location-detect-click');
		},
		mouseup: function() {
			$(this).removeClass('location-detect-click');
		},
		touchend: function() {
			$(this).removeClass('location-detect-click');
		}
	});

	$('.meal-item-content').live({
		mousedown: function() {
			if (App.busy.isBusy()) {
				return;
			}

			if (navigator.userAgent.toLowerCase().indexOf('ios') > -1) {
				return;
			}
			$(this).addClass('meal-item-down');
			var self = $(this);
			var r = self.closest('.meal-item').attr('data-permalink');
			var c = self.closest('.meal-item').attr('data-permalink-community');

			setTimeout(function() {
				if (r) {
					App.loadRestaurant(r);
				} else if (c) {
					App.cache('Community',c, function() {
						App.community = this;

						if (App.community.id_community) {
							History.pushState({},c,c);
						}
					});
				}
			},100);
		},
		mouseup: function() {
			$(this).removeClass('meal-item-down');
		},
		touchstart: function(e) {
			if (navigator.userAgent.toLowerCase().indexOf('android') > -1) {
				//return;
			}
			App.startX = event.touches[0].pageX;
			App.startY = event.touches[0].pageY;

			$(this).addClass('meal-item-down');
		},
		touchmove: function(e) {
			App.touchX = event.touches[0].pageX;
			App.touchY = event.touches[0].pageY;
		},
		touchend: function(e) {
			if (navigator.userAgent.toLowerCase().indexOf('android') > -1) {
				//return;
			}
			if (App.busy.isBusy()) {
				return;
			}

			var maxDistance = 10;
			var r = $(this).closest('.meal-item').attr('data-permalink');
			var c = $(this).closest('.meal-item').attr('data-permalink-community');

			if (Math.abs(App.startX-App.touchX) < maxDistance && Math.abs(App.startY-App.touchY) < maxDistance) {
				if (r) {
					App.loadRestaurant(r);
				} else if (c) {
					History.pushState({},c,c);
					App.routeCommunity(c);
				}
			}
			$(this).removeClass('meal-item-down');
		}
	});

	$('.resturant-dish-container a').live('click',function() {
		if ($(this).attr('data-id_dish')) {
			App.cart.add($(this).attr('data-id_dish'));

		} else if ($(this).hasClass('restaurant-menu')) {
			return;
		}
	});

	$('.your-orders a').live('click',function() {
		if ($(this).attr('data-id_order')) {
			History.pushState({},'Crunchbutton - Your Order', '/order/' + $(this).attr('data-id_order'));
		}
	});

	$('.cart-button-remove').live('click',function() {
		App.cart.remove($(this).closest('.cart-item'));
	});

	$('.cart-button-add').live('click',function() {
		App.cart.clone($(this).closest('.cart-item'));
	});

	$('.cart-item-config a').live('click',function() {
		App.cart.customize($(this).closest('.cart-item'));
	});

	$('.button-submitorder').live('click',function() {
		App.cart.submit($(this));
	});
	$('.button-submitorder-form').live('click',function() {
		App.cart.submit($(this),true);
	});

	$('.button-deliver-payment, .dp-display-item a').live('click',function() {
		$('.payment-form').show();
		$('.delivery-payment-info, .content-padder-before').hide();
	});

	$('.button-bottom').live({
		mousedown: function() {
			$(this).addClass('button-bottom-click');
		},
		touchstart: function() {
			$(this).addClass('button-bottom-click');
		}
	});
	$('.button-bottom').live({
		mouseup: function() {
			$(this).removeClass('button-bottom-click');
		},
		touchend: function() {
			$(this).removeClass('button-bottom-click');
		}
	});

	$('.cart-customize-check, .cart-customize-select').live('change',function() {
		App.cart.customizeItem($(this));
	});

	$('.cart-item-customize-item label').live('click', function() {
		$(this).prev('input').click();
	});

	$('[name="pay-tip"]').live('change',function() {
		App.order.tip = $(this).val();
		App.order.tipHasChanged = true;
		var total = App.cart.total();
		App.cart.updateTotal();
	});

	$('.nav-back').live('click',function() {
		History.back();
		return;
		switch (App.currentPage) {
			case 'restaurant':
				if (App.community) {
					History.pushState({}, 'Crunchbutton - ' + App.community.name, '/' + App.community.permalink);
				}
				break;

			case 'order':
				App.loadRestaurant(App.order.id_restaurant);
				break;

			case 'community':
				break;

			case 'legal':
				History.back();
				break;
		}
	});

	$('.link-home').live('click',function() {
		if (App.hasLocation) {
			History.pushState({}, 'Crunchbutton', '/' + App.restaurants.permalink );
		} else {
			App.forceHome = true;
			App.loadHome();
			$('input').blur();
		}
	});

	$('[name="pay-card-number"], [name="pay-card-month"], [name="pay-card-year"]').live('change', function() {
		App.order.cardChanged = true;
	});

	$('.link-help').live('click',function(e) {
		History.pushState({}, 'Crunchbutton - About', '/help');
	});

	$('.link-legal').live('click',function() {
		History.pushState({}, 'Crunchbutton - Legal', '/legal');
	});

	$('.link-orders').live('click',function() {
		History.pushState({}, 'Crunchbutton - Orders', '/orders');
	});

	$('[name="pay-phone"]').live('keyup', function(e) {
		$(this).val( App.phone.format($(this).val()) );
	});

	// make sure we have our config loaded
	var haveConfig = function(json) {
		App.processConfig(json);
		App._init = true;
		App.loadPage();
	};

	if (App.config) {
		haveConfig(App.config)
	} else {
		$.getJSON('/api/config', haveConfig);
	}

	$('.cart-summary').live('click', function() {
		$('body').scrollTop($('.cart-items').position().top-80);
	});

	var unHideBars = function() {
		$('[data-position="fixed"]').show();
	}
	$('select, input, textarea').live('focus', function() {
		if ($(window).width() >= 768 || navigator.userAgent.toLowerCase().indexOf('android') > -1 || $(this).hasClass('location-address')) {
			return;
		}
		clearTimeout(App.unHideBars);
		$('[data-position="fixed"]').hide();
	});
	$('select, input, textarea').live('blur', function() {
		if ($(window).width() >= 768) {
			return;
		}
		clearTimeout(App.unHideBars);
		setTimeout(unHideBars, 100);
	});

	var checkForDistance = function() {
		if (App.order['delivery_type'] == 'takeout') {
			return;
		}
	};

	$('[name="pay-address"]').live('blur', function() {
		clearTimeout(App.checkForDistance);
		App.checkForDistance = setTimeout(checkForDistance, 100);
	});

	$('[name="pay-address"]').live('change', function() {
		clearTimeout(App.checkForDistance);
		App.checkForDistance = setTimeout(checkForDistance, 1000);
	});

	$('.config-icon').live('click', function() {
		App.forceHome = true;
		App.loadHome();
		$('input').blur();
	});

	$('[name="pay-address"], [name="pay-name"], [name="pay-phone"], [name="pay-card-number"], [name="notes"]').live('change', function() {
		App.config.user.name = $('[name="pay-name"]').val();
		App.config.user.phone = App.phone.format($('[name="pay-phone"]').val());
		App.config.user.address = $('[name="pay-address"]').val();
		App.config.user.card = $('[name="pay-card-number"]').val();
		App.config.user.notes = $('[name="notes"]').val();
		App.config.user.card_exp_month = $('[name="pay-card-month"]').val();
		App.config.user.card_exp_year = $('[name="pay-card-year"]').val();
	});

	App.signin.init();
	App.signup.init();
	App.modal.shield.init();

});

/**************************
*  Suggestion's methods
**************************/

App.suggestion.init = function(){

	$( '.suggestion-link' ).live( 'click', function() {
		App.suggestion.show();
	} );

	$( '.suggestion-form-button' ).live( 'click', function( e ){
		App.suggestion.send();
	} );

	$( '.suggestion-form' ).submit(function() {
		return false;
	} );

	// ToolTip
	$( '.tooltip-help-mobile' ).live( 'click', function( e ) {
		if( $( '.tooltip-help-content-mobile' ).is(':visible') ){
			return;
		}
		setTimeout( function(){
			$( '.tooltip-help-content-mobile' ).show();
		}, 100 );
	} );
	$( '.tooltip-help-desktop' ).live( 'click', function() {
		if( $( '.tooltip-help-content-desktop' ).is(':visible') ){
			return;
		}
		setTimeout( function(){
			$( '.tooltip-help-content-desktop' ).show();
		}, 100 );
	} );
	$( '.tooltip-help-content' ).live( 'click', function( e ){
		e.stopPropagation();
	} );

	$( 'body' ).live( 'click', function(){
		$( '.tooltip-help-content-mobile:visible' ).hide();
		$( '.tooltip-help-content-desktop:visible' ).hide();
	} );
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

	$.ajax({
		type: "POST",
		dataType: 'json',
		data: data,
		url: suggestionURL,
		success: function(content) {
			App.suggestion.message( '<h1>Awesome, thanks!!</h1>' +
															'<div class="suggestion-thanks-text">If you really really wanna make order it RIGHT NOW, call us at 800-242-1444</div>' );
		}
	});
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

	return '<span class="tooltip-help-' + device + '-container"><span class="tooltip-help tooltip-help-' + device + '"><span>?</span></span>' +
											'<div class="tooltip-help-content tooltip-help-content-' + device + '">' +
												help +
											'</div></span>';
}

/**************************
*  Signin's methods
**************************/
App.signin.init = function(){

	$( '.wrapper' ).append( App.signin.html() );

	$( '.signin-facebook-button' ).live( 'click', function( e ){
		App.signin.facebook.login();
	} );

	$( '.signin-form-button' ).live( 'click', function( e ){
		App.signin.sendForm();
	} );

	$( '.signin-password-help' ).live( 'click', function( e ){
		App.signin.passwordHelp.show();
	} );

	$( '.signin-password-help-back' ).live( 'click', function( e ){
		App.signin.passwordHelp.hide();
	} );

	$( '.signin-password-help-button' ).live( 'click', function( e ){
		App.signin.passwordHelp.sendForm();
	} );

	$( '.signin-help-form' ).submit(function() {
		return false;
	} );

	$( '.suggestion-form' ).submit(function() {
		return false;
	} );

	$( '.signin-icon' ).live( 'click', function() {
		App.signin.show();
	} );

	$( '.signup-link' ).live( 'click', function() {
		App.dialogForceStayShield = true;
		App.signup.show( false );
		$( '.signin-container' ).dialog( 'close' );
	} );

	$( '.sign-in-icon' ).live( 'click', function() {
		if( App.config.user.id_user ){
			History.pushState({}, 'Crunchbutton - Orders', '/orders');
		} else {
			App.signin.show();
		}
	} );

	$( '.signout-icon' ).live( 'click', function() {
		App.signin.signOut();
	} );

	$( '.signin-user' ).live( 'click', function() {
		History.pushState({}, 'Crunchbutton - Orders', '/orders');;
	} );

	History.Adapter.bind(window,'statechange',function() {
		App.signin.checkUser();
	});

	App.signin.facebook.init();
}

App.signin.html = function(){
	return '' +
	'<div class="signin-container">' +
		'<div class="signin-form-container">' +
			'<div class="signin-form-options">' +
				'<form class="signin-form">' +
					'<h1 class="signup-link">Sign up</h1>' +
					'<h1>Sign in</h1>' +
					'<input type="text" maxlength="250" name="signin-email" placeholder="email or phone" tabindex="10" />' +
					'<div class="divider"></div>' +
					'<input type="password" maxlength="250" name="signin-password" placeholder="password" tabindex="10" />' +
					'<div class="divider"></div>' +
					'<div class="signin-error">' +
						'Your email or password were incorrect.' +
					'</div>' +
					'<a href="javascript:;" class="signin-password-help">Password help?</a>' +
					'<a href="javascript:;" class="signin-form-button">Log in</a>' +
					'<div class="divider"></div>' +
				'</form>' +
				'<div class="signin-facebook-container">' +
					'<div class="signin-facebook">' +
						'<a href="javascript:;" class="signin-facebook-button">' +
							'<span class="signin-facebook-icon"></span>' +
							'<span class="signin-facebook-text">Login with Facebook</span>' +
							'<div class="divider"></div>' +
						'</a>' +
					'</div>' +
					'<div class="signin-facebook-message">' +
						'Just a sec...' +
					'</div>' +
				'</div>' +
			'</div>' +
			'<div class="signin-help-container">' +
				'<form class="signin-help-form">' +
					'<h1>Password help?</h1>' +
					'<input type="text" maxlength="250" name="password-help-email" placeholder="email or phone" tabindex="10" />' +
					'<div class="divider"></div>' +
					'<div class="password-help-error"></div>' +
					'<a href="javascript:;" class="signin-password-help-back">Never mind</a>' +
					'<a href="javascript:;" class="signin-password-help-button">Reset</a>' +
					'<div class="divider"></div>' +
				'</form>' +
				'<div class="signin-password-help-message"></div>' +
			'</div>' +
		'</div>' +
	'</div>';
}

App.signin.sendForm = function(){
	// Checks it fhe login is a phone
	var login = $( 'input[name=signin-email]' ).val();
	login = login.replace(/[^\d]*/gi,'')
	if( !App.phone.validate( login ) ){
		// It seems not to be a phone number, lets check if it is a email
		login = $.trim( $( 'input[name=signin-email]' ).val() );
		if( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( login ) ){
			login = false;
		}
	}
	if( !login ){
		alert( 'Please enter a valid email or phone.' );
		$( 'input[name=signin-email]' ).focus();
		return;
	}

	if( $.trim( $( 'input[name=signin-password]' ).val() ) == '' ){
		alert( 'Please enter your password.' );
		$( 'input[name=signin-password]' ).focus();
		return;
	}
	var email = login,
			password = $.trim( $( 'input[name=signin-password]' ).val() ),
			url = App.service + 'user/auth';
	$('.signin-error').hide();
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'email' : email, 'password' : password },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				$('.signin-error').fadeIn();
			} else{
				App.config.user = json;
				App.signin.checkUser();
				$( '.signin-container' ).dialog( 'close' );
				// If the user is at the restaurant's page - reload it
				if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
					App.page.restaurant( App.restaurant.permalink );
				}
			}
		}
	} );
}

App.signin.signOut = function(){
	if( confirm( 'Confirm sign out?' ) ){
		if( App.signin.facebook.isLogged ){
			FB.logout( function(){
				$.getJSON('/api/logout',function(){
					$( '.signout-icon' ).hide();
					location.href = '/';
				} );
			} );
		} else {
			$.getJSON('/api/logout',function(){
				$( '.signout-icon' ).hide();
				location.href = '/';
			} );
		}
	}
}


App.signin.facebook = {};
App.signin.facebook.running = false;
App.signin.facebook.init = function(){}
App.signin.facebook.processStatus = function( session ){
	if ( session.status === 'connected' && session.authResponse ) {
		App.signin.facebook.isLogged = true;
		if( App.signin.facebook.shouldAuth ){
			FB.api( '/me', { fields: 'name' }, function( response ) {
				if ( response.error ) {
					return;
				}
				if( response.id ){
					App.signin.facebook.shouldAuth
					$( '.signin-facebook-message' ).show();
					$( '.signup-facebook-message' ).show();
					$( '.signin-facebook' ).hide();
					$( '.signup-facebook' ).hide();
					// Just call the user api, this will create a facebook user
					var url = App.service + 'user/facebook';
					if( !App.signin.facebook.running ){
						App.signin.facebook.running = true;
						$.ajax( {
							type: 'GET',
							url: url,
							dataType: 'json',
							success: function( json ){
								App.signin.facebook.running = true;
								if( json.error ){
									if( json.error == 'facebook id already in use' ){
										alert( 'Sorry, It seems the facebook user is already related with other user.' );
									}
								} else {
									App.config.user = json;
									App.signin.checkUser();
								}
								$( '.signin-container' ).dialog( 'close' );
								$( '.signup-container' ).dialog( 'close' );
								// If the user is at the restaurant's page - reload it
								if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
									App.page.restaurant( App.restaurant.permalink );
								}
								if( App.currentPage == 'orders' ){
									App.page.orders()								
								}
							}
						} );
					}
				}
			});
		}
	}
}
App.signin.facebook.login = function() {
	App.signin.facebook.shouldAuth = true;
	FB.login( App.signin.facebook.processStatus,{ scope:'email' } );
};

App.signin.show = function(){
	App.signin.passwordHelp.hide();
	$( '.signin-facebook-message' ).hide();
	$( '.signin-facebook' ).show();
	/* if( App.signin.facebook.isLogged ){
		$( '.signin-facebook-container' ).hide();
	} else {
		$( '.signin-facebook-container' ).show();
	} */
	setTimeout( function(){
			/* Shows the shield */
			App.modal.shield.show();
			$( 'input[name=signin-email]' ).val( '' );
			$( 'input[name=signin-password]' ).val( '' );
			$('.signin-error').hide();
			$( '.signin-container' )
				.dialog( {
					dialogClass: 'modal-fixed-dialog',
					width: App.modal.contentWidth(),
					close: function( event, ui ) { App.modal.shield.close(); },
					open: function( event, ui ) { $( '.signin-email' ).focus(); }
				} );
		}, 100 );
}

App.signin.checkUser = function(){
	// If the user is logged
	if( App.config.user.id_user ){
		// $( '.signin-user' ).html( 'Hi, ' + App.config.user.name );
		$( '.signin-user' ).show();
		$( '.signin-icon' ).hide();
		$( '.signout-icon' ).hide();
		$( '.signin-box-header' ).addClass( 'signin-box-header-min' );
	} else {
		$( '.signin-user' ).hide();
		$( '.signin-icon' ).show();
		$( '.signup-icon' ).show();
		$( '.signout-icon' ).hide();
		$( '.signin-box-header' ).removeClass( 'signin-box-header-min' );
	}
	if( App.currentPage == 'home' ){
		$( '.config-icon' ).addClass( 'config-icon-desktop-hide' );
	} else {
		$( '.config-icon' ).removeClass( 'config-icon-desktop-hide' );
	}
}

App.signin.passwordHelp = {};

App.signin.passwordHelp.show = function(){
	if( $.trim( $( 'input[name=signin-email]' ).val() ) != '' ){
		$( 'input[name=password-help-email]' ).val( $.trim( $( 'input[name=signin-email]' ).val() ) );
	}
	$( '.signin-password-help-button' ).show();
	$( '.signin-password-help-back' ).show();
	$( '.signin-help-container' ).show();
	$( '.signin-form-options' ).hide();
	$( '.signin-password-help-message' ).hide();
	$( '.signin-password-help-message' ).html( '' );
	$( 'input[name=password-help-email]' ).focus();
}

App.signin.passwordHelp.hide = function(){
	$( '.signin-help-container' ).hide();
	$( '.signin-form-options' ).show();
}

App.signin.passwordHelp.sendForm = function(){
	// Checks it fhe login is a phone
	var login = $( 'input[name=password-help-email]' ).val();
	login = login.replace(/[^\d]*/gi,'')
	if( !App.phone.validate( login ) ){
		// It seems not to be a phone number, lets check if it is a email
		login = $.trim( $( 'input[name=password-help-email]' ).val() );
		if( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( login ) ){
			login = false;
		}
	}
	if( !login ){
		alert( 'Please enter a valid email or phone.' );
		$( 'input[name=password-help-email]' ).focus();
		return;
	}
	$( '.password-help-error' ).html( '' );
	$( '.password-help-error' ).hide();
	var url = App.service + 'user/reset';
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'email' : login },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'user is not registred' ){
					$( '.password-help-error' ).html( 'Sorry, that email/phone is not registered with us.' );
					$( '.password-help-error' ).fadeIn();
					$( 'input[name=password-help-email]' ).focus()
				}
			} else {
				if( json.success = 'success' ){
					$( '.signin-password-help-message' ).show();
					$( '.signin-password-help-button' ).hide();
					$( '.signin-password-help-back' ).hide();
					$( '.signin-password-help-message' ).html( 'You will receive a code to reset your password! It will expire in 24 hours.' );
				}
			}
		}
	} );
}

App.signin.passwordHelp.reset = {};

App.signin.passwordHelp.reset.init = function(){
	setTimeout( function(){
		/* Shows the shield */
		App.modal.shield.show();
		$( '.password-reset-container' )
			.dialog( {
				dialogClass: 'modal-fixed-dialog',
				width: App.modal.contentWidth(),
				close: function( event, ui ) { App.modal.shield.close(); App.signin.passwordHelp.reset.close(); },
				open: function( event, ui ) { $( 'input[name=password-reset-code]' ).focus(); }
			} );
		$( '.password-reset-code-button' ).live( 'click', function(){
			App.signin.passwordHelp.reset.sendForm();
		} );
		$( '.password-change-button' ).live( 'click', function(){
			App.signin.passwordHelp.reset.change();
		} );
		$( '.password-reset-form' ).submit(function() {
			return false;
		} );
		$( '.password-change-form' ).submit(function() {
			return false;
		} );
	}, 100 );
}

App.signin.passwordHelp.reset.sendForm = function(){
	$( '.password-reset-code-error' ).html( '' );
	$( '.password-reset-code-error' ).hide();
	var code = $.trim( $( 'input[name=password-reset-code]' ).val() );
	if( code == '' ){
		alert( 'Please enter the reset code.' );
		$( 'input[name=password-reset-code]' ).focus();
		return;
	}
	var url = App.service + 'user/code-validate';
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'code' : code },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'invalid code' ){
					$( '.password-reset-code-error' ).html( 'Sorry, this code is invalid.' );
				}
				if( json.error == 'expired code' ){
					$( '.password-reset-code-error' ).html( 'Sorry, this code is expired.' );
				}
				$( '.password-reset-code-error' ).fadeIn();
				$( 'input[name=password-reset-code]' ).focus()
			} else {
				if( json.success = 'valid code' ){
					$( '.password-reset-block' ).hide();
					$( '.password-change-block' ).show();
					$( 'input[name=password-new]' ).focus();
				}
			}
		}
	} );
}

App.signin.passwordHelp.reset.change = function(){
	var code = $.trim( $( 'input[name=password-reset-code]' ).val() );
	var password = $.trim( $( 'input[name=password-new]' ).val() );
	if( password == '' ){
		alert( 'Please enter your password.' );
		$( 'input[name=password-new]' ).focus();
		return;
	}
	var url = App.service + 'user/change-password';
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'code' : code, 'password' : password },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'invalid code' ){
					$( '.password-change-error' ).html( 'Sorry, this code is invalid.' );
				}
				if( json.error == 'expired code' ){
					$( '.password-change-error' ).html( 'Sorry, this code is expired.' );
				}
				$( '.password-change-error' ).fadeIn();
			} else {
				if( json.success = 'password changed' ){
					$( '.password-change-message' ).fadeIn();
					$( '.password-change-block' ).find( 'h1' ).html( 'Done!' );
					$( '.password-change-message' ).html( 'Your password has changed!' );
					App.signin.passwordHelp.hasChanged = true;
				}
			}
			$( 'input[name=password-new]' ).hide();
			$( '.password-change-button' ).hide();
		}
	} );
}

App.signin.passwordHelp.reset.close = function(){
	if( App.signin.passwordHelp.hasChanged ){
		location.href = '/';
	}
}

App.signin.passwordHelp.reset.html = function( path ){
	var code = ( path.length > 1 ) ? ( path[ 1 ] ? path[ 1 ] : '' ) : '';
	return '' +
	'<div class="password-reset-container">' +
			'<div class="password-reset-block">' +
				'<form class="password-reset-form">' +
					'<h1>Password reset</h1>' +
					'<input type="text" maxlength="250" name="password-reset-code" value="' + code + '" placeholder="enter the reset code" tabindex="10" />' +
					'<div class="divider"></div>' +
					'<div class="password-reset-code-error"></div>' +
					'<a href="javascript:;" class="password-reset-code-button">Reset</a>' +
					'<div class="divider"></div>' +
				'</form>' +
			'</div>' +
			'<div class="password-change-block">' +
				'<form class="password-change-form">' +
					'<h1>Type your new password</h1>' +
					'<input type="password" maxlength="250" name="password-new" value="" placeholder="password" tabindex="10" />' +
					'<div class="divider"></div>' +
					'<div class="password-change-error"></div>' +
					'<a href="javascript:;" class="password-change-button">Change</a>' +
					'<div class="divider"></div>' +
					'<div class="password-change-message"></div>' +
				'</form>' +
			'</div>' +
		'</div>' +
	'</div>';
}

/**************************
*  Signup's methods
**************************/
App.signup = {};

App.signup.init = function(){

	$( '.wrapper' ).append( App.signup.html() );

	$( '.signup-icon' ).live( 'click', function(){
		App.signup.show( false );
	} );

	$( '.signup-form-button' ).live( 'click', function(){
		App.signup.sendForm();
	} );

	$( '.signup-facebook-button' ).live( 'click', function(){
		App.signin.facebook.login();
	} );

	$( '.signin-link' ).live( 'click', function(){
		App.dialogForceStayShield = true;
		App.signin.show();
		$( '.signup-container' ).dialog( 'close' );
	} );

	$( '.signup-form' ).submit(function() {
		return false;
	} );

}

App.signup.html = function(){
	return '' +
	'<div class="signup-container">' +
		'<div class="signup-form-container">' +
			'<div class="signup-form-options">' +
				'<form class="signup-form">' +
					'<h1 class="signin-link">Sign in</h1>' +
					'<h1>Sign up</h1>' +
					'<input type="text" maxlength="250" name="signup-email" placeholder="email address" tabindex="10" />' +
					'<div class="divider"></div>' +
					'<input type="password" maxlength="250" name="signup-password" placeholder="password" tabindex="10" />' +
					'<div class="divider"></div>' +
					'<a href="javascript:;" class="signup-form-button">Save</a>' +
					'<div class="divider"></div>' +
					'<div class="signup-error"></div>' +
				'</form>' +
				'<div class="signup-facebook-container">' +
					'<div class="signup-facebook">' +
						'<a href="javascript:;" class="signup-facebook-button">' +
							'<span class="signup-facebook-icon"></span>' +
							'<span class="signup-facebook-text">Signup with Facebook</span>' +
							'<div class="divider"></div>' +
						'</a>' +
					'</div>' +
					'<div class="signup-facebook-message">' +
						'Just a sec...' +
					'</div>' +
				'</div>' +
			'</div>' +
			'<div class="signup-success-container">' +
				'<h1>Well done!</h1>' +
				'<div class="signup-success">' +
					'Now you can use your <strong class="success-phone"></strong>!' +
				'</div>' +
			'</div>' +
		'</div>' +
	'</div>';
}

App.signup.show = function( justFacebook ){
	$( '.signup-facebook' ).show();
	$( '.signup-facebook-message' ).hide();
	if( App.config.user.facebook ){
		$( '.signup-facebook-container' ).hide();
	} else {
		$( '.signup-facebook-container' ).show();
	}
	setTimeout( function(){
			/* Shows the shield */
			App.modal.shield.show();
			// $( 'input[name=signup-email]' ).val( App.config.user.phone );
			$( 'input[name=signup-password]' ).val( '' );
			$( '.signup-form-options' ).show();
			$( '.signup-success-container' ).hide();
			if( justFacebook ){
				$( '.signup-form' ).hide();
			} else {
				$( '.signup-form' ).show();
			}
			$( '.signin-error' ).hide();
			$( '.signup-container' )
				.dialog( {
					dialogClass: 'modal-fixed-dialog',
					width: App.modal.contentWidth(),
					close: function( event, ui ) { App.modal.shield.close(); },
					open: function( event, ui ) { $( '.signup-phone' ).focus(); }
				} );
		}, 100 );
}

App.signup.checkLogin = function(){
	var login = $( 'input[name=pay-phone]' ).val().replace(/[^\d]*/gi,'');
	if( App.phone.validate( login ) ){
		var url = App.service + 'user/verify/' + login	
		$.getJSON( url, function( json ) {
			if( json.error ){
				if( json.error == 'user exists' ){
					$( 'input[name=pay-password]' ).val( '' );
					$( '.password-field' ).hide();
				}
			} else {
				$( '.password-field' ).fadeIn();
				$( 'input[name=pay-password]' ).val( '' );
				$( 'input[name=pay-password]' ).focus();
			}
		} );
	} else {
		$( 'input[name=pay-password]' ).val( '' );
		$( '.password-field' ).hide();
	}
}

App.signup.sendForm = function(){
	login = $.trim( $( 'input[name=signup-email]' ).val() );
	if( !/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( login ) ){
		login = false;
	}
	if( !login ){
		alert( 'Please enter a valid email email address.' );
		$( 'input[name=signup-email]' ).focus();
		return;
	}

	if( $.trim( $( 'input[name=signup-password]' ).val() ) == '' ){
		alert( 'Please enter your password.' );
		$( 'input[name=signup-password]' ).focus();
		return;
	}
	var password = $.trim( $( 'input[name=signup-password]' ).val() ),
			url = App.service + 'user/create/local';
	$( '.signup-error' ).hide();
	$.ajax( {
		type: 'POST',
		url: url,
		data: { 'email' : login, 'password' : password },
		dataType: 'json',
		success: function( json ){
			if( json.error ){
				if( json.error == 'user exists' ){
					$('.signup-error').html( 'It seems that the email is already registered!' );
				}
				$('.signup-error').fadeIn();
			} else{
				App.config.user = json;
				$( '.success-phone' ).html( login );
				$( '.signup-call-to-action' ).hide();
				$( '.signup-form-options' ).hide();
				$( '.signup-success-container' ).show();
				App.signin.checkUser();
				// If the user is at the restaurant's page - reload it
				if( App.currentPage == 'restaurant' && App.restaurant.permalink ){
					App.page.restaurant( App.restaurant.permalink );
				}
			}
		}
	} );
}

App.modal.shield.resize = function(){
	if( App.modal.shield.isVisible ){
		$( '.modal-shield' ).width( $( window ).width() );
		/* Plus 60 due to iphone's title bar. */
		$( '.modal-shield' ).height( $( window ).height() + 60 );
	}
}

App.modal.shield.init = function(){
	$( '.wrapper' ).append( '<div class="modal-shield"></div>' );
	$( window ).resize( function() {
			App.modal.shield.resize();
	} );
}

App.modal.shield.show = function(){
	$( '.modal-shield' ).show();
	App.modal.shield.isVisible = true;
	App.modal.shield.resize();
}

App.modal.shield.close = function(){
	if( App.dialogForceStayShield ){
		App.dialogForceStayShield = false;
		return;
	}
	$( '.modal-shield' ).hide();
	App.modal.shield.isVisible = false;
}

App.modal.contentWidth = function(){
	if( $( window ).width() > 700 ){
		return 280;
	}
	if( $( window ).width() <= 700 ){
		return $( window ).width() - 50;
	}
}

google.load('maps', '3',  {callback: App.loc.preProcess, other_params: 'sensor=false'});