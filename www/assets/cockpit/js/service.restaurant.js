NGApp.factory('RestaurantService', function( $rootScope, $resource, $routeParams, ResourceFactory) {

	var service = {};

	// this is the stuff for the restaurant order placement
	// and some for restaurant settlement. not sure what is used
	var restaurants = $resource( App.service + 'restaurants/:action', { action: '@action' }, {
			'list' : { 'method': 'GET', params : { 'action' : 'list' }, isArray: true },
			'no_payment_method' : { 'method': 'GET', params : { 'action' : 'no-payment-method' }, isArray: true },
			'paid_list' : { 'method': 'GET', params : { 'action' : 'paid-list' }, isArray: true },
			'order_placement' : { 'method': 'GET', params : { 'action' : 'order-placement' } },
			'eta' : { 'method': 'GET', params : { 'action' : 'eta' }, isArray: true },
			'weight_adjustment' : { 'method': 'GET', params : { 'action' : 'weight-adjustment' }, isArray: true },
			'save_weight' : { 'method': 'POST', params : { 'action' : 'save-weight' } },
			'save_notes_to_driver' : { 'method': 'POST', params : { 'action' : 'save-notes-to-driver' } },
		}
	);

	var restaurant = ResourceFactory.createResource( App.service + 'restaurants/:id_restaurant', { id_restaurant: '@id_restaurant'}, {
		'load' : {
			url: App.service + 'restaurant/:id_restaurant',
			method: 'GET',
			params : {}
		},
		'restaurant_query' : {
			method: 'GET',
			params : {}
		},
	});

	var payinfo = $resource( App.service + 'restaurant/payinfo/:action/:id_restaurant', { action: '@action' }, {
			'payment_method' : { 'method': 'GET', params : { 'action' : 'payment-method' } },
			'stripe_status' : { 'method': 'GET', params : { 'action' : 'stripe-status' } },
			'send_verification_info' : { 'method': 'GET', params : { 'action' : 'stripe-send-verification-info' } },
			'payment_method_save' : { 'method': 'POST', params : { 'action' : 'payment-method' } },
			'balanced_to_sprite' : { 'method': 'POST', params : { 'action' : 'balanced-to-stripe' } },
			'balanced_to_sprite_account' : { 'method': 'POST', params : { 'action' : 'balanced-to-stripe' } },
			'stripe' : { 'method': 'POST', params : { 'action' : 'stripe' } },
		}
	);

	service.list = function(params, callback) {
		restaurant.restaurant_query(params).$promise.then(function success(data, responseHeaders) {
			callback(data);
		});
	}

	service.weight_adjustment = function( params, callback ){
		restaurants.weight_adjustment( params,  function( data ){
			callback( data );
		} );
	}

	service.save_weight = function( params, callback ){
		restaurants.save_weight( params,  function( data ){
			callback( data );
		} );
	}

	service.save_notes_to_driver = function( params, callback ){
		restaurants.save_notes_to_driver( params,  function( data ){
			callback( data );
		} );
	}


	service.get = function(id_restaurant, callback) {
		restaurant.load({id_restaurant: id_restaurant}, function(data) {
			callback(data);
		});
	}

	service.shortlist = function( callback ){
		restaurants.list( function( data ){
			callback( data );
		} );
	}

	service.order_placement = function( callback ){
		restaurants.order_placement( function( data ){
			callback( data );
		} );
	}

	service.no_payment_method = function( callback ){
		restaurants.no_payment_method( function( data ){
			callback( data );
		} );
	}

	service.payment_method = function( id_restaurant, callback ){
		payinfo.payment_method( { id_restaurant: id_restaurant },  function( data ){
			callback( data );
		} );
	}

	service.stripe_status = function( id_restaurant, callback ){
		payinfo.stripe_status( { id_restaurant: id_restaurant },  function( data ){
			callback( data );
		} );
	}

	service.send_verification_info = function( id_restaurant, callback ){
		payinfo.send_verification_info( { id_restaurant: id_restaurant },  function( data ){
			callback( data );
		} );
	}

	service.payment_method_save = function( params, callback ){
		payinfo.payment_method_save( params,  function( data ){
			callback( data );
		} );
	}

	service.balanced_to_sprite = function( id_restaurant, callback ){
		payinfo.balanced_to_sprite( { id_restaurant: id_restaurant },  function( data ){
			callback( data );
		} );
	}

	service.balanced_to_sprite_account = function( params, callback ){
		payinfo.balanced_to_sprite_account( params,  function( data ){
			callback( data );
		} );
	}


	service.stripe = function( params, callback ){
		payinfo.stripe( params,  function( data ){
			callback( data );
		} );
	}



	service.paid_list = function( callback ){
		restaurants.paid_list( function( data ){
			callback( data );
		} );
	}

	service.eta = function( callback ){
		restaurants.eta( function( data ){
			callback( data );
		} );
	}


	service.yesNo = function(){
		var methods = [];
		methods.push( { value: false, label: 'No' } );
		methods.push( { value: true, label: 'Yes' } );
		return methods;
	}

	service.summaryMethod = function(){
		var methods = [];
		methods.push( { value: 'fax', label: 'Fax' } );
		methods.push( { value: 'email', label: 'Email' } );
		methods.push( { value: 'no summary', label: 'Does Not Need Summary' } );
		return methods;
	}

	service.paymentMethod = function(){
		var methods = [];
		methods.push( { value: 'check', label: 'Check' } );
		methods.push( { value: 'deposit', label: 'Deposit' } );
		methods.push( { value: 'no payment', label: 'Does Not Need Payment' } );
		return methods;
	}

	service.accountType = function(){
		var methods = [];
		methods.push( { value: 'individual', label: 'Individual' } );
		methods.push( { value: 'corporation', label: 'Corporation' } );
		return methods;
	}

	return service;
} );

NGApp.factory( 'RestaurantOrderPlacementService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var orders = $resource( App.service + 'order/:action/:id_restaurant', { action: '@action', id_restaurant: '@id_restaurant' }, {
				'process' : { 'method': 'POST' },
				'get' : { 'method': 'GET' },
				'list' : { 'method': 'GET', params: { 'action' : 'restaurant-list-last' } },
			}
		);

	var restaurant = $resource( App.service + 'restaurant/orderplacement/:action/:id_restaurant', { action: '@action', id_restaurant: '@id_restaurant' }, {
				'get' : { 'method': 'GET' },
				'status' : { 'method': 'GET', params: { 'action': 'status' } },
				'all' : { 'method': 'GET', params: { 'action' : 'all' }, isArray: true },
			}
		);

	service.get = function( callback ){
		orders.get( { 'action': $routeParams.id }, function( data ){
			callback( data );
		} );
	}

	service.list = function( id_restaurant, callback ){
		orders.list( { id_restaurant: id_restaurant }, function( data ){
			callback( data );
		} );
	}

	service.restaurant = {
		get : function( id_restaurant, callback ){
			restaurant.get( { id_restaurant: id_restaurant }, function( data ){
				callback( data );
			} );
		},
		status : function( id_restaurant, callback ){
			restaurant.status( { id_restaurant: id_restaurant }, function( data ){
				callback( data );
			} );
		},
		all : function ( callback ){
			restaurant.all( function( data ){
				callback( data );
			} );
		}
	}

	service.calcTotal = function( order, restaurant ){

			var _fee = function( total ){
				if ( restaurant.fee_customer ) {
					return App.ceil( total * ( parseFloat( restaurant.fee_customer ) / 100 ) );
				}
				return 0;
			}

			var _tax = function( total ){
				return ( total * ( restaurant.tax / 100 ) );
			}

			var _markup = function( total ){
				if( restaurant.delivery_service_markup ){
					return App.ceil( ( total * ( restaurant.delivery_service_markup / 100 ) ) );
				}
				return 0;
			}

			var _delivery = function(){
				return App.ceil( parseFloat( restaurant.delivery_fee ) );
			}

			var _tip = function( total ){
				if( order.tip_type == 'percent' ){
					if( order.tip ){
						return App.ceil( ( total * ( order.tip / 100 ) ) );
					} else {
						return 0;
					}

				}
				return order.tip;
			}

			var total = order.subtotal + _markup( order.subtotal );
			var totalWithoutMarkup = order.subtotal;
			var delivery = _delivery();
			total += delivery;
			var fee = _fee( total );
			total += fee;
			if(restaurant.delivery_service){
				totalWithoutMarkup += delivery;
			}
			total += _tax( totalWithoutMarkup );
			total += _tip( total );
			return App.ceil( total ).toFixed( 2 );
	}


	service.process = function( order, card, callback ){

		var process = function(){
			orders.process( order, function( data ){ callback( data ); } );
		}

		if( order.pay_type == 'card' ){
			App.tokenizeCard( { name: order.name, number: card.number, expiration_month: card.month, expiration_year: card.year, security_code: null },
												function( status ) {
													if ( !status.status ) {
														callback( { error: status.error } );
														return;
													}
													order.card = status;
													process();
												} );

		} else {
			process();
		}
	}

	service.tipPercents = function(){
		var tips = [];
		tips.push( { value: 0, label: '0%' } );
		tips.push( { value: 10, label: '10%' } );
		tips.push( { value: 15, label: '15%' } );
		tips.push( { value: 18, label: '18%' } );
		tips.push( { value: 20, label: '20%' } );
		tips.push( { value: 25, label: '25%' } );
		tips.push( { value: 30, label: '30%' } );
		return tips;
	}

	service.cardYears = function(){
		var years = [];
		years.push( { value: '', label: 'Year' } );
		var date = new Date().getFullYear();
		for ( var x = date; x <= date + 20; x++ ) {
			years.push( { value: x.toString(), label: x.toString() } );
		}
		return years;
	}

	service.cardMonths = function(){
		var months = [];
		months.push( { value: '', label: 'Month' } );
		for ( var x = 1; x <= 12; x++ ) {
			months.push( { value: x.toString(), label: x.toString() } );
		}
		return months;
	}

	return service;
} );


NGApp.factory( 'RestaurantEditService', function( $rootScope, $resource, $routeParams ) {

	var service = {};

	var load = $resource( App.service + 'restaurant/edit/:action/:permalink', { action: '@action', permalink: '@permalink' }, {
				'get' : { 'method': 'GET' },
			}
		);

	service.load = {
		new : function( callback ){
			load.get( { 'action': 'new' }, function( data ){
				callback( data );
			} );
		},
		basic : function( permalink, callback ){
			load.get( { 'permalink': permalink, 'action': 'basic' }, function( data ){
				callback( data );
			} );
		},
		hours : function( permalink, callback ){
			load.get( { 'permalink': permalink, 'action': 'hours' }, function( data ){
				callback( data );
			} );
		},
		delivery : function( permalink, callback ){
			load.get( { 'permalink': permalink, 'action': 'delivery' }, function( data ){
				callback( data );
			} );
		},
		notes : function( permalink, callback ){
			load.get( { 'permalink': permalink, 'action': 'notes' }, function( data ){
				callback( data );
			} );
		},
		notifications : function( permalink, callback ){
			load.get( { 'permalink': permalink, 'action': 'notifications' }, function( data ){
				callback( data );
			} );
		},
		menu : function( permalink, callback ){
			load.get( { 'permalink': permalink, 'action': 'menu' }, function( data ){
				callback( data );
			} );
		}
	}

	var save = $resource( App.service + 'restaurant/edit/:action/:permalink', { action: '@action', permalink: '@permalink' }, {
				'post' : { 'method': 'POST' },
			}
		);

	service.save = {
		basic : function( data, callback ){
			data.action = 'basic';
			save.post( data, function( data ){
				callback( data );
			} );
		},
		delivery : function( data, callback ){
			data.action = 'delivery';
			save.post( data, function( data ){
				callback( data );
			} );
		},
		notes : function( data, callback ){
			data.action = 'notes';
			save.post( data, function( data ){
				callback( data );
			} );
		},
		notifications : function( data, callback ){
			data.action = 'notifications';
			save.post( data, function( data ){
				callback( data );
			} );
		},
		hours : function( data, callback ){
			data.action = 'hours';
			save.post( data, function( data ){
				callback( data );
			} );
		},
	}

	service.yesNo = function(){
		var options = [];
		options.push( { value: false, label: 'No' } );
		options.push( { value: true, label: 'Yes' } );
		return options;
	}

	service.active = function(){
		var options = [];
		options.push( { value: true, label: 'Active' } );
		options.push( { value: false, label: 'Inactive' } );
		return options;
	}

	service.timezones = function(){
		var timezones = [];
		timezones.push( { value: 'America/New_York', label: 'Eastern' } );
		timezones.push( { value: 'America/Chicago', label: 'Central' } );
		timezones.push( { value: 'America/Denver', label: 'Mountain' } );
		timezones.push( { value: 'America/Phoenix', label: 'Arizona (no DST)' } );
		timezones.push( { value: 'America/Los_Angeles', label: 'Pacific' } );
		return timezones;
	}

	service.notificationType = function(){
		var notificationType = [];
		notificationType.push( { value: 'sms', label: 'SMS' } );
		notificationType.push( { value: 'email', label: 'Email' } );
		notificationType.push( { value: 'phone', label: 'Phone' } );
		notificationType.push( { value: 'url', label: 'URL' } );
		notificationType.push( { value: 'fax', label: 'Fax' } );
		notificationType.push( { value: 'admin', label: 'Admin' } );
		notificationType.push( { value: 'stealth', label: 'Stealth Fax' } );
		return notificationType;
	}

	service.deliveryRadiusType = function(){
		var deliveryRadiusType = [];
		deliveryRadiusType.push( { value: 'restaurant', label: 'Restaurant' } );
		deliveryRadiusType.push( { value: 'community', label: 'Community' } );
		return deliveryRadiusType;
	}

	service.deliveryMinAmount = function(){
		var deliveryMinAmount = [];
		deliveryMinAmount.push( { value: 'total', label: 'Total' } );
		deliveryMinAmount.push( { value: 'subtotal', label: 'Subtotal' } );
		return deliveryMinAmount;
	}

	var sort = function( list, item, direction ){
		if( item &&  direction ){
			var old_position = item.sort;
			var new_position = null;
			if( direction == 'up' ){
				new_position = item.sort - 1;
			} else {
				new_position = item.sort + 1;
			}
			new_index = new_position - 1;
			old_index = old_position - 1;
			var current_item = list[ new_index ];
			list[ new_index ] = list[ old_index ];
			list[ old_index ] = current_item;
		}
		if( list.length ){
			for( var i = 0; i < list.length; i++ ){
				list[ i ].sort = ( i + 1 );
				list[ i ].show_up = true;
				list[ i ].show_down = true;
				// @remove -- remove it before commit
				// list[ i ].expanded = true;
			}
			if( list ){
				list[ 0 ].show_up = false;
				list[ list.length - 1 ].show_down = false;
			}
		}
		return list;
	}

	service.menu = {
		sort: {
			category: function( categories, category, direction ){
				return sort( categories, category, direction );
			},
			dish: function( dishes, dish, direction ){
				return sort( dishes, dish, direction );
			},
			option: function( options, option, direction ){
				return sort( options, option, direction );
			}
		},
		parse: {
			dish: function( dishes ){
				for( x in dishes ){
					var dish = dishes[ x ];
					dish.options = { selects:[], checkboxes:[] };
					var options = dish._options;
					var checkboxes = {};
					var selects = {};
					if( options ){
						for( y in options ){
							var option = options[ y ];
							if( option.type == 'select' ){
								option.options = [];
								dish.options.selects.push( option );
							}
							if( option.type == 'check' && !option.id_option_parent ){
								option.options = [];
								dish.options.checkboxes.push( option );
							}
						}
						for( y in options ){
							var option = options[ y ];
							if( option.type == 'check' && option.id_option_parent ){
								for( z in dish.options.selects ){
									if( dish.options.selects[ z ].id_option == option.id_option_parent ){
										dish.options.selects[ z ].options.push( option );
									}
								}
							}
						}
					}

					if( dish.options.checkboxes.length ){
						dish.options.checkboxes = service.menu.sort.option( dish.options.checkboxes );
					}

					if( dish.options.selects.length ){
						dish.options.selects = service.menu.sort.option( dish.options.selects );
						for( w in dish.options.selects ){
							if( dish.options.selects[ w ].options && dish.options.selects[ w ].options.length ){
								dish.options.selects[ w ].options = service.menu.sort.option( dish.options.selects[ w ].options );
							}
						}
					}

					dishes[ x ] = dish;
				}
				return service.menu.sort.dish( dishes );
			}
		}
	}

	// Hours
	service.hours = {
		saveIsSafe: true,
		validate: function ( hours ){
								_hours = {};
								days = ['mon','tue','wed','thu','fri','sat','sun'];
								service.hours.saveIsSafe = true;
								for(day in days) {
									val = hours[ days[ day ] ];
									if(/^(?: *|closed)$/i.exec(val)) continue;
									save_one_time_segment = function(val) {
										val = val.replace(/\(.*?\)/g, '');
										val = val.replace(/midnight/i, '0:00 AM');
										val = val.replace(/noon/i, '12:00 PM');
										m = /^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i.exec(val)
										if(!m) {
											App.alert('Unrecognized time format.');
											service.hours.saveIsSafe = false;
											return;
										}
										begin_h = parseInt(m[1]);
										begin_m = parseInt(m[2]) || 0;
										begin_ampm = m[3].toLowerCase();
										end_h = parseInt(m[4]);
										end_m = parseInt(m[5]) || 0;
										end_ampm = m[6].toLowerCase();
										if(begin_ampm === 'am' && begin_h === 12) {
											begin_h = begin_h - 12;
											if(end_h === 12 && end_ampm === 'am' && end_m > begin_m) {
												end_h = end_h - 12;
											}
										}
										if(begin_ampm === 'pm' && begin_h < 12) { begin_h = begin_h + 12; }
										if(end_ampm === 'am' && end_h === 12) { end_h = end_h + 12; }
										if(end_ampm === 'pm' && end_h < 12) { end_h = end_h + 12; }
										if(end_ampm === 'am' && end_h < begin_h) { end_h = end_h + 24; }

										if(!(days[day] in _hours)) { _hours[days[day]] = [] }
										if(end_h*100 + end_m > 2400) {
											// split into two times
											today = days[day];
											tomorrow = days[(days.indexOf(today)+1)%(days.length)];
											if(!(tomorrow in _hours)) { _hours[tomorrow] = [] }
											_hours[today].push([
													'' + begin_h + ':' + padNumber(begin_m,2),
													'24:00']);
											_hours[tomorrow].push([
													'0:00',
													'' + (end_h-24) + ':' + padNumber(end_m,2)]);
										}
										else {
											// just the one
											_hours[days[day]].push([
													'' + begin_h + ':' + padNumber(begin_m,2),
													'' + end_h + ':' + padNumber(end_m,2)]);
										}
									};
									segments = val.split(/(?:and|,)/);
									for(i in segments) save_one_time_segment(segments[i]);
								}
							return _hours;
		},
		parse: function( hours ){
						var _hours = {};
						// 1. convert everything to 'hours from Monday morning midnight'
						hfmmm = []; // pairs of [start,finish]
						days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
						for(day in hours) {
							dayhours = days.indexOf(day)*2400;
							for(i in hours[day]) {
								segment = hours[day][i];
								m0 = /(\d+):(\d+)/.exec(segment[0]);
								b = (dayhours + parseInt(m0[1], 10) * 100 + parseInt(m0[2], 10));
								m1 = /(\d+):(\d+)/.exec(segment[1]);
								e = (dayhours + parseInt(m1[1], 10) * 100 + parseInt(m1[2], 10));
								hfmmm.push({b : b, e : e});
							}
						}
						// 2. sort
						hfmmm.sort(function(a,b) { return (a.b<b.b?-1:(a.b>b.b?1:0)); });
						// 3. merge things that should be merged
						for(i = 0; i < hfmmm.length-1; i++) {
							if(hfmmm[i+1].b <= hfmmm[i].e       &&
								 hfmmm[i+1].e - hfmmm[i].b < 3600  ) {
								// merge these two segments
								hfmmm[i].e = hfmmm[i+1].e;
								hfmmm.splice(i+1,1);
								i--;
								continue;
							}
						}
						days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
						for( x in days ) {
							_hours[ days[ x ] ] = 'Closed';
						}

						for(i in hfmmm) {
							segment = hfmmm[i];
							day = days[Math.floor(segment.b/2400)];

							if( _hours[day] === 'Closed') {
								_hours[day] = '';
							}
							else {
								_hours[day] = _hours[day] + ', ';
							}
							while(segment.b >= 2400) {
								segment.b -= 2400;
								segment.e -= 2400;
							}
							format_time = function(t) {
								// input: a time like 234 indicating 2:34 AM
								h = Math.floor(t/100);
								m = t-100*h;
								h_fmt = '';
								m_fmt = '';
								ampm = '';
								shorthand = '';
								if(h === 0) { h_fmt = '' + (h + 12); shorthand = 'midnight'; ampm = 'AM'; }
								else if(h === 12) { shorthand = 'noon'; ampm = 'PM'; }
								else if(h === 24) { shorthand = 'midnight'; ampm = 'AM'; h_fmt = '12'; }
								else if(h < 12) { ampm = 'AM'; }
								else if(h < 24) { h_fmt = '' + (h - 12); ampm = 'PM'; }
								else { ampm = 'AM'; h_fmt = '' + (h - 24); }
								if(m) { m_fmt = ':' + padNumber(m, 2); }
								fmt = '' + (h_fmt || h) + m_fmt + ' ' + ampm;
								if(shorthand) fmt = fmt + ' (' + shorthand + ')';
								return fmt;
							};
							segment.b_fmt = format_time(segment.b);
							segment.e_fmt = format_time(segment.e);
							segment.fmt = segment.b_fmt + ' - ' + segment.e_fmt;
							_hours[day] = _hours[day] + segment.fmt;
						}
						return _hours;
		}
	}

	var padNumber = function(num, pad) {
		str = '' + num;
		while(str.length < pad) str = '0' + str;
		return str;
	}

	return service;
} );