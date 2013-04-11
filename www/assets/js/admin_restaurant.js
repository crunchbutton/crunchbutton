// admin_restaurant.js

// TODO
// allow for check functions for each input field that get called on change
//   e.g. permalink has only allowed characters

// weird field TODOs
//   order notification methods
//   food items
//   delivery options
//   more information
//   admin shit
var DEBUG = {}

var UTIL = {
	pad_number : function(num, pad) {
		str = '' + num;
		while(str.length < pad) str = '0' + str;
		return str;
	},
	getJSONVal : function(base, path) {
		// base is the base js object
		// path is an array of selectors
		if(path.length === 0) return base;
		return UTIL.getJSONVal(base[path[0]], path.splice(1));
	},
	setJSONVal : function(base, path, val) {
		// base is the base js object
		// path is an array of selectors
		if(path.length === 1) base[path[0]] = val;
		else setJSONVal(base[path[0]], path.splice(1), val);
	},
	go_to_legacy_view : function() {
		legacy_url = document.location.href.replace(
				/\/restaurants/,'/restaurants/legacy');
		document.location.href = legacy_url;
	},
	show_msg : function(msg) {
		console.log(msg);
		$('#admin-msg').text(msg);
		$('#float-bottom-left-container').stop().hide().fadeIn(250).delay(4000).fadeOut(250);
	},
	deep_copy : function(obj) {
		// Handle the 3 simple types, and null or undefined
		if (null == obj || "object" != typeof obj) return obj;

		// Handle Date
		if (obj instanceof Date) {
				var copy = new Date();
				copy.setTime(obj.getTime());
				return copy;
		}

		// Handle Array
		if (obj instanceof Array) {
				var copy = [];
				for (var i = 0, len = obj.length; i < len; i++) {
						copy[i] = UTIL.deep_copy(obj[i]);
				}
				return copy;
		}

		// Handle Object
		if (obj instanceof Object) {
				var copy = {};
				for (var attr in obj) {
						if (obj.hasOwnProperty(attr)) copy[attr] = UTIL.deep_copy(obj[attr]);
				}
				return copy;
		}

		throw new Error("Unable to copy obj! Its type isn't supported.");
	},
	copy_field : function(from,to) {
		return function() { $(to).val($(from).val()); };
	},
	create_widget : function(widget_type, dom_parent) {
		w = new WIDGET[widget_type]();
		dom_parent.append(w.dom);
		DOM_MAP.map.data.widget.push(w);
		return w;
	},
};


var WIDGET = {
	notifications : function(dom_id) {
		var template = '#notifications-template';
		var type = 'notifications';
		var self = this;
		self.dom_id = dom_id;
		self.dom = $(template).clone(true);
		if(dom_id) $(self.dom).attr('id',dom_id);
		else $(self.dom).removeAttr('id');
		self.notification_widgets = [];

		$('#add-notification').click(function(){self.add_notification();});

		self.add_notification = function() {
			w = new WIDGET.notification();
			$(self.dom).append(w.dom);
			self.notification_widgets.push(w);
		},
		this.val = function(arg) {
			return null;
		},
		this.apply = function(restaurant) {
			for(i in self.notification_widgets) {
				self.notification_widgets[i].remove();
			}
			notifications = restaurant._notifications || {};
			notifications['confirmation'] = {
					active: restaurant.confirmation,
					id: null,
					id_notification: null,
					id_restaurant: restaurant.id, 
					type: "confirmation",
					value: "main phone only", // TODO update this when backend supports it
			};
			self.notification_widgets = [];
			for(i in notifications) {
				w = new WIDGET.notification();
				$(self.dom).append(w.dom);
				w.apply(notifications[i]);
				self.notification_widgets.push(w);
			}
		},
		this.flush = function(restaurant) {
			restaurant._notifications = [];
			restaurant.confirmation = null;
			for(i in self.notification_widgets) {
				w = self.notification_widgets[i];
				data = w.flush();
				if(data.type === 'confirmation') {
					restaurant.confirmation = data.active;
					continue;
				}
				if(!data.value || data.value==='') continue;
				restaurant._notifications.push(data);
			}
		},
		this.remove = function() {
			self.dom.remove();
		};
	},
	notification : function(dom_id) {
		var template = '#notification-template';
		var type = 'notification';
		var self = this;
		self.dom_id = dom_id;
		self.dom = $(template).clone(true);
    if(dom_id) $(self.dom).attr('id',dom_id);
    else $(self.dom).removeAttr('id');

		self.w_active = new WIDGET.toggle();
		self.id_notification = null;

		$(self.dom).find('.admin-toggle-active').replaceWith(self.w_active.dom);

    this.val = function(arg) {
			return null;
		}
		this.apply = function(data) {
			self.w_active.val(data.active);
			self.id_notification = data.id_notification;
			$(self.dom).find('select').val(data.type);
			$(self.dom).find('input[name=val]').val(data.value);
    },
    this.flush = function() {
			data = {
				active : self.w_active.val(),
				id : self.id_notification,
				id_notification : self.id_notification,
				type : $(self.dom).find('select').val(),
				value : $(self.dom).find('input[name=val]').val(),
			};
			return data;
    },
    this.remove = function() {
      self.dom.remove();
    };
	},
	toggle : function(dom_id) {
		var template = '#toggle-template';
		var type = 'toggle';
		var self = this;
		self.dom_id = dom_id;
		self.dom = $(template).clone(true);
		if(dom_id) $(self.dom).attr('id',dom_id);
		else $(self.dom).removeAttr('id');
		this.set_active = function() {
			$(self.dom).addClass('admin-toggle-active');
			$(self.dom).removeClass('admin-toggle-inactive');
			$(self.dom).text('active');
		};
		this.set_inactive = function() {
			$(self.dom).removeClass('admin-toggle-active');
			$(self.dom).addClass('admin-toggle-inactive');
			$(self.dom).text('inactive');
		};
		this.toggle_active = function() {
			self.dom.hasClass('admin-toggle-active')?
					self.set_inactive():
					self.set_active();
		};
		this.val = function(arg) {
			if(arg!==undefined) {
				parseInt(arg) ? this.set_active() : this.set_inactive();
			}
			else {
				return $(self.dom).hasClass('admin-toggle-active') ? "1":"0";
			}
		};
		this.apply = function(obj) { this.val(obj.active); };
		this.flush = function(obj) { obj.active = this.val(); };
		this.remove = function() { self.dom.remove(); };
		this.dom.click(self.toggle_active);
	},
};


var ADMIN = {
	restaurant_init : function(id_restaurant) {
		// once per pageload
		this.id_restaurant = id_restaurant;
		this.load_restaurant_by_id();
	},
	create_widgets : function() {
		// removes old widgets and creates new blank ones
		while(DOM_MAP.map.data.widget.length) {
			w = DOM_MAP.map.data.widget.pop().remove();
		}
		w = UTIL.create_widget('toggle', $('#restaurant-active-container'));
		w = UTIL.create_widget('notifications', $('#notifications-container'));
		$('#restaurant-address').focusout(function() {
			ASYNC.geocode($('#restaurant-address').val(), function(data) {
				if(!data || !data.length) { return; }
				$('#restaurant-address').val(data[0].formatted_address);
				$('#restaurant-lat').val(data[0].geometry.location.lat());
				$('#restaurant-lng').val(data[0].geometry.location.lng());
				map = new google.maps.Map(
						$('#restaurant-map')[0],
						{
							zoom : 14, 
							mapTypeId : google.maps.MapTypeId.ROADMAP
						});
				map.setCenter(data[0].geometry.location);
				infowindow = new google.maps.Marker({
						map:map, position:data[0].geometry.location,
				});
			});
		});
	},
	load_restaurant_by_id : function() {
		// may be called multiple times per pageload
		this.create_widgets();
		ASYNC.req(
				{
					type : 'api',
					obj  : 'restaurant',
					id   : this.id_restaurant,
				},
				function(rsp) {
					if(!ADMIN.restaurant_original) {
						ADMIN.restaurant_original = UTIL.deep_copy(rsp);
					}
					ADMIN.restaurant = rsp;
					DOM_MAP.apply();
					ADMIN.save_is_safe = true;
					UTIL.show_msg('Restaurant loaded.');
				});
	},
	restaurant_revert : function() {
		ADMIN.restaurant = ADMIN.restaurant_original;
		DOM_MAP.apply();
		ADMIN.restaurant_save();
	},
	restaurant_save : function() {
		DOM_MAP.flush();
		console.log(ADMIN.restaurant);
		if(!ADMIN.save_is_safe) {
			UTIL.show_msg('There are errors somewhere.');
			return;
		}
		ASYNC.req(
				{ 
					type : 'sav',
					data: { restaurant:ADMIN.restaurant, obj:'restaurant' },
				},
				function(rsp) {
					ADMIN.restaurant = rsp.data;
					DOM_MAP.apply();
					UTIL.show_msg('Restaurant saved.');
				});
	},
	restaurant_validate : function(validation_function) {
		return function(evnt) {
			val = $(evnt.currentTarget).val();
			is_valid = validation_function(val);
			if(is_valid) {
				$(evnt.currentTarget).addClass('valid');
				$(evnt.currentTarget).removeClass('invalid');
			}
			else {
				$(evnt.currentTarget).removeClass('valid');
				$(evnt.currentTarget).addClass('invalid');
			}
		}
	},
	restaurant_validate_functions : {
		permalink : function(val) { return /^[\da-zA-Z_-]+$/.exec(val); },
	},
};

var DOM_MAP = {
	// an adapter specific to this page and this object
	apply : function() {
		for(item in this.map.onclick) {
			$(item).unbind('click');
			$(item).click(this.map.onclick[item]);
		}
		for(item in this.map.validate_data) {
			$(item).unbind('change');
			$(item).change(this.map.validate_data[item]);
		}
		for(item in this.map.data.text) {
			$(item).val(
					UTIL.getJSONVal(ADMIN.restaurant, this.map.data.text[item]));
		}
		for(item in this.map.data.func) {
			this.map.data.func[item].apply(ADMIN.restaurant, $(item));
		}
		for(item in this.map.data.widget) {
			this.map.data.widget[item].apply(ADMIN.restaurant);
		}
	},
	flush : function() {
		for(item in this.map.data.text) {
			UTIL.setJSONVal(
					ADMIN.restaurant, 
					this.map.data.text[item], 
					$(item).val());
		}
		for(item in this.map.data.func) {
			this.map.data.func[item].flush(ADMIN.restaurant, $(item));
		}
		for(item in this.map.data.widget) {
			this.map.data.widget[item].flush(ADMIN.restaurant);
		}
	},
	map : {
		onclick : { // map html elements to functions
			'#save-button'		: ADMIN.restaurant_save,
			'#revert-button'	: ADMIN.restaurant_revert,
			'#legacy-button'	: UTIL.go_to_legacy_view,
			'#copy-tue-from-mon' : UTIL.copy_field('#restaurant-hours-mon','#restaurant-hours-tue'),
			'#copy-wed-from-tue' : UTIL.copy_field('#restaurant-hours-tue','#restaurant-hours-wed'),
			'#copy-thu-from-wed' : UTIL.copy_field('#restaurant-hours-wed','#restaurant-hours-thu'),
			'#copy-fri-from-thu' : UTIL.copy_field('#restaurant-hours-thu','#restaurant-hours-fri'),
			'#copy-sat-from-fri' : UTIL.copy_field('#restaurant-hours-fri','#restaurant-hours-sat'),
			'#copy-sun-from-sat' : UTIL.copy_field('#restaurant-hours-sat','#restaurant-hours-sun'),
			'#view-map' : function() {
				lat = $('#restaurant-lat').val();
				lng = $('#restaurant-lng').val();
				url = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&z=14';
				console.log(url); // TODO


			},
		},
		validate_data : {
			'input#restaurant-permalink' : ADMIN.restaurant_validate(ADMIN.restaurant_validate_functions.permalink),
		},
		data : { // map html elements to js data parts
			text : {
				'input#restaurant-name' : ['name'],
				'input#restaurant-permalink' : ['permalink'],
				'input#restaurant-phone' : ['phone'],
				'textarea#restaurant-address' : ['address'],
				'select#restaurant-timezone' : ['timezone'],
				'input#restaurant-lat' : ['loc_lat'],
				'input#restaurant-lng' : ['loc_long'],
			},
			widget : [], // a list of widgets supporting 'apply' and 'flush' funcs etc
			func : {
				'#restaurant-address': {
					apply : function(restaurant, element) { element.focusout(); },
					flush : function(restaurant, element) { },
				},
				'#restaurant-hours' : {
					apply : function(restaurant, element) {
						_hours = ADMIN.restaurant._hours;
						days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
						if(!_hours) {
							for(day in days) {
								$('input#restaurant-hours-' + days[day]).val('Closed');
							}
							return;
						}
						segments_all = [];
						segments_uni = {};
						for(day in days) segments_uni[days[day]] = {
							begin : { h : null, m : null },
							end : { h : null, m : null },
						};
						for(day in days) {
							if(!_hours[days[day]]) { continue; }
							for(i in _hours[days[day]]) {
								segment = _hours[days[day]][i];
								segment_day = days[day];
								segment_begin = segment[0];
								segment_end   = segment[1];
								if(/^0?0:00$/.exec(segment_end)) segment_end = "24:00";
								if(/^0?0:00$/.exec(segment_begin) &&
								   !/^24:00$/.exec(segment_end)    ) {
									segment_day = days[(days.indexOf(days[day])+6)%(days.length)];
									segment_begin = "24:00";
									segment_end = segment_end.replace(
											/(\d+):/,
											parseInt(segment_end)+24+":");
								}
								m = /(\d+):(\d+)/.exec(segment_begin);
								this_segment_begin_h = parseInt(m[1]);
								this_segment_begin_m = parseInt(m[2]);
								m = /(\d+):(\d+)/.exec(segment_end);
								this_segment_end_h = parseInt(m[1]);
								this_segment_end_m = parseInt(m[2]);
								if(segments_uni[segment_day]['begin']['h'] === null ||
									 segments_uni[segment_day]['begin']['h'] > this_segment_begin_h) {
									segments_uni[segment_day]['begin']['h'] = this_segment_begin_h;
									segments_uni[segment_day]['begin']['m'] = this_segment_begin_m;
									segments_uni[segment_day]['valid'] = true;
								}
								if(segments_uni[segment_day]['end']['h'] === null ||
									 segments_uni[segment_day]['end']['h'] < this_segment_end_h) {
									segments_uni[segment_day]['end']['h'] = this_segment_end_h;
									segments_uni[segment_day]['end']['m'] = this_segment_end_m;
									segments_uni[segment_day]['valid'] = true;
								}
							}
						}
						for(day in days) {
							if(!(segments_uni[days[day]].valid)) {
								$('input#restaurant-hours-' + days[day]).val("Closed");
								continue;
							}
							begin = segments_uni[days[day]].begin;
							end = segments_uni[days[day]].end;
							for(time in [begin,end]) {
								t = [begin,end][time];
								if(t.h === 0) { t.shorthand = 'midnight'; t.ampm = 'AM'; }
								if(t.h === 24) { t.h = 12; t.shorthand = 'midnight'; t.ampm = 'AM'; }
								else if(t.h === 12) { t.shorthand = 'noon'; t.ampm = 'PM'; }
								else if(t.h < 12) t.ampm = 'AM';
								else if(t.h < 24) { t.ampm = 'PM'; t.h = t.h - 12; }
								else { t.ampm = 'AM'; t.h = t.h - 24; }
								t.fmt = t.h + ':' + UTIL.pad_number(t.m,2) + ' ' + t.ampm;
								if(t.shorthand) t.fmt = t.fmt + ' (' + t.shorthand + ')';
							}
							today_fmt = begin.fmt + ' - ' + end.fmt;
							$('input#restaurant-hours-' + days[day]).val(today_fmt);
						}
					},
					flush : function(restaurant, element){
						_hours = {};
						days = ['mon','tue','wed','thu','fri','sat','sun'];
						for(day in days) {
							val = $('input#restaurant-hours-' + days[day]).val();
							if(/^(?: *|closed)$/i.exec(val)) continue;
							val = val.replace(/\(.*?\)/g, '');
							val = val.replace(/midnight/i, '0:00 AM');
							val = val.replace(/noon/i, '12:00 PM');
							m = /^(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i.exec(val)
							if(!m) {
								UTIL.show_msg('Unrecognized time format.');
								ADMIN.save_is_safe = false;
								return;
							}
							begin_h = parseInt(m[1]);
							begin_m = parseInt(m[2]) || 0;
							begin_ampm = m[3].toLowerCase();
							end_h = parseInt(m[4]);
							end_m = parseInt(m[5]) || 0;
							end_ampm = m[6].toLowerCase();
							if(begin_ampm === "am" && begin_h === 12) { begin_h = begin_h - 12; }
							if(begin_ampm === "pm" && begin_h < 12) { begin_h = begin_h + 12; }
							if(end_ampm === "am" && end_h === 12) { end_h = end_h + 12; }
							if(end_ampm === "pm" && end_h < 12) { end_h = end_h + 12; }
							if(end_ampm === "am" && end_h < begin_h) { end_h = end_h + 24; }

							if(!(days[day] in _hours)) { _hours[days[day]] = [] }
							if(end_h*100 + end_m > 2400) {
								// split into two times
								today = days[day];
								tomorrow = days[(days.indexOf(today)+1)%(days.length)];
								if(!(tomorrow in _hours)) { _hours[tomorrow] = [] }
								_hours[today].push([
										'' + begin_h + ':' + UTIL.pad_number(begin_m,2),
										'24:00']);
								_hours[tomorrow].push([
										'0:00',
										'' + (end_h-24) + ':' + UTIL.pad_number(end_m,2)]);
							}
							else {
								// just the one
								_hours[days[day]].push([
										'' + begin_h + ':' + UTIL.pad_number(begin_m,2),
										'' + end_h + ':' + UTIL.pad_number(end_m,2)]);
							}
						}
						restaurant._hours = _hours;
					},
				},
			},
		},
	},
};

var ASYNC = {
	// handles all js network requests
	cfg : {
		api : {
			url : function(req) { return '/api/' + req.obj + '/' + req.id; },
			method : 'get',
		},
		sav : {
			url : function() { return '/admin/save'; },
			method : 'post',
		},
	},
	req : function(req, callback) {
		console.log(req);
		$.ajax({
			url: this.cfg[req.type].url(req),
			method: this.cfg[req.type].method,
			data: req.data ? req.data : '',
			dataType: 'json',
		}).done(function(rsp) {
			console.log(rsp);
			if(rsp.result && rsp.result != 'OK') {
				UTIL.show_msg('Error: ' + rsp.error);
			}
			else {
				callback(rsp);
			}
		});
	},
	geocode : function(address, callback) {
		g = new google.maps.Geocoder();
		g.geocode({address:address},function(r,s) {
			if(s === 'ZERO_RESULTS') return;
			if(s !== 'OK') {
				UTIL.show_msg('Geocoding error.');
				console.log(s);
				console.log(r);
				return;
			}
			callback(r);
		});
	},
};



