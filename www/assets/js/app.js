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
	slogans: ['Order the best food %s with a click'],
	tagline: 'We\'ve chosen the best food from the best restaurants %s. We save your order, delivery and payment info, so reordering is as easy as the click of a button. Click on a restaurant below to get started.',
	service: '/api/',
	cached: {},
	cart: {},
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
	_init: false,
	_pageInit: false
};

App.loadRestaurant = function(id) {
	App.cache('Restaurant',id,function() {
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
			var loc = '/' + App.community.permalink + '/' + this.permalink;
			History.pushState({}, 'Crunchbutton - ' + this.name, loc);
		}
	});
};

App.loadCommunity = function(id) {

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


App.loadHome = function() {
	App.currentPage = 'home';
	History.pushState({}, 'Crunchbutton', '/');
	
	App.loc.lat = 0;
	App.loc.lon = 0;

	if ($('.enter-location').length) {
		$('.location-address').val('');
		$('.error-location').fadeOut(100, function() {
			$('.enter-location, .button-letseat-form').fadeIn();
		});
	}
};

App.page.home = function() {
	document.title = 'Crunchbutton';

	$('.nav-back').removeClass('nav-back-show');

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
		'<div class="home-welcome home-welcom-error"><h1>Oh no! We aren\'t quite ready in <span class="loc-your-area">your area</span>. Come back next time you are hungry!</h1></div>' +
		'<div class="content-item-locations">' +
			'<h1>Our most popular locations</h1>' +
		'</div>' +
		'<div class="content-padder-before"></div>' +
		'<div class="content-padder">' +
			'<div class="meal-items">' + top + '</div></div>' +
	'</div>');


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
};

App.page.community = function(id) {

	App.lastCommunity = id;
	App.currentPage = 'community';

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
				.append('<h3 class="meal-food">Top Order: ' + (rs[x].top() ? (rs[x].top().top_name || rs[x].top().name) : '') + '</h3>');

			if (rs[x].open()) {
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
		document.title = App.restaurant.name + ' | ' + App.community.name + ' Food Delivery | Order from ' + (App.community.name_alt ? App.community.name_alt : 'Local') + ' Restaurants | Crunchbutton';

		$('.main-content').html(
			'<div class="cart-summary cart-summary-detail" data-role="header" data-position="fixed"><div class="cart-summary-icon"></div><div class="cart-summary-item-count"><span></span></div><div class="cart-summary-items"></div></div>' +
			'<div class="restaurant-name"><h1>' + App.restaurant.name + '</h1></div>' + 
			(App.restaurant.image ? '<div class="restaurant-pic-wrapper"><div class="restaurant-pic" style="background: url(' + App.restaurant.img + ');"></div></div>' : '') + 
			'<div class="main-content-readable">' + 
				'<div class="restaurant-items"></div>' + 
				'<div class="cart-items"><div class="restaurant-item-title text-your-order">Your Order</div><div class="your-order-label" style="font-weight: bold; display: none;">(we\'ve chosen the most popular order, but you can order anything you want)</div><div class="divider"></div><div class="delivery-minimum-error">Please add $<span class="delivery-min-diff">' + parseFloat(App.restaurant.delivery_min -  App.cart.total()).toFixed(2) + '</span> more to your order to meet the delivery minimum of $' + parseFloat(App.restaurant.delivery_min).toFixed(2) + '.</div><div class="cart-items-content"></div></div>' + 
				'<div class="divider"></div>' + 
			'</div>' + 
			'<div class="restaurant-payment-div"></div>'
		);

		var
			categories = App.restaurant.categories(),
			dishes, list;
			
		$('.restaurant-items').append('<div class="content-item-name content-item-main-name"><h1>Add to your order</h1></div>')
	
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

		if (App.config.user.id_user) {
			App.drawPay(this);
			$('.payment-form').hide();

			var dp = $('<div class="delivery-payment-info main-content-readable"></div>')
				.append('<div class="dp-display-phone dp-display-item"><label>Your phone number:</label> <a href="javascript:;">' + (App.config.user.phone ? App.phone.format(App.config.user.phone) : '<i>no phone # provied</i>') + '</a></div>');
			var paying = $('<div class="dp-display-payment dp-display-item"><label>You are paying:</label> <span class="cart-total">$0.00</span></div>');
			if (App.config.user.pay_type == 'card') {
				paying.append('&nbsp;incl tax<span class="includes-fees"></span> and <a href="javascript:;"><span class="delivery-tip-amount">15%</span> tip</a>, by <a href="javascript:;">card</a>');
			} else {
				paying.append('&nbsp;incl tax<span class="includes-fees"></span>&nbsp;using <a href="javascript:;">cash</a>');			
			}
			dp.append(paying);
			if (App.config.user.delivery_type == 'delivery') {
				dp.append('<div class="dp-display-address dp-display-item"><label>Your food will be delivered to:</label><br /><a href="javascript:;">' + (App.config.user.address ? App.config.user.address.replace("\n",'<br />') : '<i>no address provided</i>') + '</a></div>');
			} else {
				dp.append('<div class="dp-display-address dp-display-item"><label>Deliver to:</label> <a href="javascript:;"><i>takeout</i></a></div>');			
			}
			dp.append('<div class="divider"></div>');
	
			$('.main-content').append(dp);
			$('.delivery-tip-amount').html(App.order.tip ? App.order.tip + '%' : 'no');

		} else {
			App.drawPay(this);
		}
		setTimeout(function() {
			var total = App.cart.updateTotal();
		},200);

		App.cartHighlightEnabled = false;
	});

};

App.drawPay = function(restaurant) {
	var total = App.cart.total();

	$('.main-content').append(
		'<form class="payment-form main-content-readable">' + 
		'<div class="content-item-name"><h1>Your Info</h1></div>' + 
		'<div class="your-info-label">(enter this once, and we\'ll save it for next time)</div>' +
		'<div class="delivery-info-container"></div><div class="divider"></div>' + 
		'<div class="payment-info-container"></div><div class="divider"></div>' + 
		'<div class="payment-total">Your <span class="cash-order-aprox"></span> total is <span class="cart-total">$' + total + '</span> (incl tax<span class="includes-tip"></span>)</div>' +
		'</form>' + 

		'<div class="button-bottom-wrapper" data-role="footer" data-position="fixed"><button class="button-submitorder-form button-bottom"><div>Get Food</div></button></div>'
	);
	
	var fieldError = App.community.id_community == 'gw' ? '<div class="field-error">Include ZIP code</div>' : '';

	$('.delivery-info-container').append(

		'<div class="personal-info field-container">' + 
		
			'<label class="pay-title-label">Delivery Info</label>' + 
			'<div class="input-item toggle-wrapper clearfix">' +
				'<a href="javascript:;" class="delivery-toggle-delivery toggle-item delivery-only-text">delivery</a> <span class="toggle-spacer delivery-only-text">or</span> <a href="javascript:;" class="delivery-toggle-takeout toggle-item">takeout</a>' + 
			'</div><div class="divider"></div>' + 

			'<label>Name</label>' + 
			'<div class="input-item"><input type="text" name="pay-name" tabindex="2"></div><div class="divider"></div>' + 
	
			'<label>Phone #</label>' + 
			'<div class="input-item"><input type="tel" name="pay-phone" tabindex="3"></div><div class="divider"></div>' + 
	
			'<label class="delivery-only">Deliver to</label>' + 
			'<div class="input-item delivery-only"><textarea name="pay-address" tabindex="4"></textarea></div>' + 
			fieldError + 
			'<div class="divider"></div>' +
			
			'<label>Notes</label>' + 
			'<div class="input-item"><textarea name="notes" tabindex="5"></textarea></div><div class="divider"></div>' + 

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

	if (App.order['delivery_type'] == 'takeout') {
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
			console.log(App.config.user.presets[App.restaurant.id_restaurant]);
			$('[name="notes"]').val(App.config.user.presets[App.restaurant.id_restaurant].notes);
		} catch (e) {}
	}
	
	if (!restaurant.delivery) {
		$('.delivery-only-text').hide();
	}
	
	if (!App.config.user.id_user) {
		App.config.user.address = App.loc.enteredLoc;
		App.loc.enteredLoc = '';
	}

};

App.page.order = function(id) {

	App.currentPage = 'order';
	console.log('order');
	App.cache('Order', id, function() {

		if (!this.uuid) {
			History.replaceState({},'Crunchbutton','/');
		}

		document.title = 'Crunchbutton - Your Order';
		App.order = this;

		var message, order = this;

		if (App.justCompleted) {
			App.justCompleted = false;
			message = 'Thanks, ' + this.name + '!';
		} else {
			message = 'Your order';
		}

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
		
		$('.order-info').append('<b>Your phone #:</b><br />' + this.phone + '<br /><br />');
		
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
		App.refreshLayout();

	});
};

App.loadPage = function() {
	var
		url = History.getState().url.replace(/http(s)?:\/\/.*?\/(.*)/,'$2').replace('//','/'),
		path = url.split('/');

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
			App.page.order();
			break;

		default:
			if (!App.community) {
				// force load of community reguardless of landing (this contains everything we need)
				App.loadCommunity(path[0]);
				return;
			}
			break;
	}

	if (App.community) {
		var communityRegex = new RegExp('^\/' + App.community.permalink + '$', 'i');
		var restaurantRegex = new RegExp('^\/(restaurant)|(' + App.community.permalink + ')/', 'i');
	}

	switch (true) {

		case /^order\//i.test(url):
			App.page.order(path[1]);
			break;
			
		case /^legal/i.test(url):
			App.page.legal();
			break;
			
		case /^help/i.test(url):
			App.page.help();
			break;
			
		case /^orders/i.test(url):
			App.page.orders();
			break;
			
		case restaurantRegex.test(url):
			App.page.restaurant(path[1]);
			break;
			
		case communityRegex.test(url):
		default:
			$('.nav-back').removeClass('nav-back-show');
			$('.footer').removeClass('footer-hide');
			App.page.community(App.community.permalink);
			setTimeout(scrollTo, 80, 0, 1);
			return;
			break;
	}
	if (App.config.env == 'live') {
		$('.footer').addClass('footer-hide');
	}
	$('.nav-back').addClass('nav-back-show');
	App.refreshLayout();
	$('.main-content').css('visibility','1');
	setTimeout(scrollTo, 80, 0, 1);
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
	if (arguments[1]) {
		mixpanel.track(arguments[0],arguments[1]);
	} else {
		mixpanel.track(arguments[0]);
	}
};

App.cart = {
	uuidInc: 0,
	items: {},
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
	updateTotal: function() {
		var
			totalText = '$' + App.cart.total(),
			tipText = '',
			feesText = '',
			totalItems = 0,
			hasFees = ((App.restaurant.delivery_fee && App.order.delivery_type == 'delivery') || App.restaurant.fee_customer) ? true : false;

		for (var x in App.cart.items) {
			totalItems++;
		}

		if (App.restaurant.meetDeliveryMin() && App.order.delivery_type == 'delivery') {
			$('.delivery-minimum-error').show();
			$('.delivery-min-diff').html(parseFloat(App.restaurant.delivery_min -  App.cart.total()).toFixed(2));

		} else {
			$('.delivery-minimum-error').hide();
		}

		$('.cart-summary-item-count span').html(totalItems);

		$('.cart-total').html(totalText);
		
		if (App.order['pay_type'] == 'card') {
			tipText = hasFees ? ', tip and fees' : ' and tip';
			feesText = hasFees ? ', fees' : '';
			$('.cash-order-aprox').html('');
		} else {
			tipText = hasFees ? ' and fees' : '';
			feesText = hasFees ? ' and fees' : '';
			$('.cash-order-aprox').html('approximate');
		}
		
		if (App.cartHighlightEnabled && $('.cart-summary').css('display') != 'none') {
			$('.cart-summary').removeClass('cart-summary-detail');
			$('.cart-summary').effect('highlight', {}, 500, function() {
				$('.cart-summary').addClass('cart-summary-detail');
			});
		}
		
		$('.includes-tip').html(tipText);
		$('.includes-fees').html(feesText);
		
		if ($('.cart-total').html() == totalText) {
			//return;
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
		return price != '0.00' ? '&nbsp;($' + price + ')' : '';
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
						.append('<label class="cart-item-customize-name">' + opt[x].name + (opt[x].description || '') + '</label><label class="cart-item-customize-price">' + App.cart.customizeItemPrice(price) + '</label>');
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
	getCart: function() {
		var cart = [];
		for (x in App.cart.items) {
			cart[cart.length] = App.cart.items[x];
		}
		return cart;
	},
	submit: function(el) {
		if (App.busy.isBusy()) {
			return;
		}

		App.busy.makeBusy();

		var read = $('.payment-form').length ? true : false;

		if (read) {
			App.config.user.name = $('[name="pay-name"]').val();
			App.config.user.phone = $('[name="pay-phone"]').val().replace(/[^\d]*/gi,'');
			if (App.order['delivery_type'] == 'delivery') {
				App.config.user.address = $('[name="pay-address"]').val();
			}
			App.order.tip = $('[name="pay-tip"]').val();
		}

		var order = {
			cart: App.cart.getCart(),
			pay_type: App.order['pay_type'],
			delivery_type: App.order['delivery_type'],
			restaurant: App.restaurant.id,
			make_default: $('#default-order-check').is(':checked'),
			notes: $('[name="notes"]').val(),
			lat: App.loc.lat,
			lon: App.loc.lon
		};
		
		if (order.pay_type == 'card') {
			order.tip = App.order.tip ? App.order.tip : '15';
		}

		if (read) {
			order.address = App.config.user.address;
			order.phone = App.config.user.phone;
			order.name = App.config.user.name;

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
	},
	subtotal: function() {
		var
			total = 0,
			options;

		for (var x in App.cart.items) {
			total += parseFloat(App.cached['Dish'][App.cart.items[x].id].price);
			options = App.cart.items[x].options;

			for (var xx in options) {
				total += parseFloat(App.cached['Option'][options[xx]].optionPrice(options));
			}
		}

		return total;
	},
	total: function() {
		var
			total = 0,
			dish,
			options,
			feeTotal = 0,
			totalItems = 0;

		total = App.cart.subtotal();
		feeTotal = total;

		if (App.restaurant.delivery_fee && App.order.delivery_type == 'delivery') {
			feeTotal += parseFloat(App.restaurant.delivery_fee);
		}
		
		if (App.restaurant.fee_customer) {
			feeTotal += (feeTotal * (parseFloat(App.restaurant.fee_customer)/100));
		}
		
		var final = feeTotal + (feeTotal * (App.restaurant.tax/100));

		if (App.order['pay_type'] == 'card') {
			final += (total * (App.order.tip/100));
		}

		//return final.toFixed(2);
		return App.ceil(final).toFixed(2);
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
					App.cart.add(dishes[x].id_dish,{
						options: options
					});
				}
			}
		} catch (e) { console.log(e.stack); }
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

			if (App.loc.lat) {
				closest = App.loc.getClosest();
				
				if (closest) {
					if (closest.distance < 25) {
						did = true;
						App.community = closest;
						var loc = '/' + closest.permalink;
						App.community = null;
						History.pushState({}, 'Crunchbutton', loc);
					}
				}
			}
		}
		
		if (!did && !App.forceHome && navigator.geolocation) {
			var complete = function() {
				$('.button-letseat-form').click();
			};
			navigator.geolocation.getCurrentPosition(function(position){
				App.loc.lat = position.coords.latitude;
				App.loc.lon = position.coords.longitude;
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

		switch ($('.location-address').val().toLowerCase()) {
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
				App.community = null;
				var loc = '/' + App.communities.gw.permalink;
				History.pushState({}, 'Crunchbutton', loc);
				return;
				break;
		}

		geocoder.geocode({'address': $('.location-address').val()}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				App.loc.lat = results[0].geometry.location.Xa ? results[0].geometry.location.Xa : results[0].geometry.location.Ya;
				App.loc.lon = results[0].geometry.location.Za ? results[0].geometry.location.Za : results[0].geometry.location.Ya;
				App.loc.setFormattedLoc(results);
			} else {
				$('.location-address').val('').attr('placeholder','Oops! We couldn\'t find that address!');
				console.log('Geocode was not successful for the following reason: ' + status);
			}
			console.log(App.loc.lat, App.loc.lon);
			complete();
		});
	},
	reverseGeocode: function(complete) {
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
		$('.delivery-only').hide();
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

				} else {
					$('.enter-location, .button-letseat-form').fadeOut(100, function() {
						$('.error-location').fadeIn();
					});
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
					App.loadCommunity(c);
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
		if (App.lastCommunity) {
			History.pushState({}, 'Crunchbutton', '/' + App.lastCommunity);
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
		History.pushState({}, 'Crunchbutton - Legal', '/help');
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
	
});

google.load('maps', '3',  {callback: App.loc.preProcess, other_params: 'sensor=false'});