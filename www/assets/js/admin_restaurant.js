// admin_restaurant.js

// TODO
// 
// features
//	 menu
//	 change hours so you can do multiples
//	 allow for validation functions for each input field that get called on change
//		 possibly use intro.js
//
// nice to have
//	 include link to img editing
//	 improvements from github issue
//
// known issues
//	 pulling menu from db when saving omits inactive items
//	


var DEBUG = {}

var UTIL = {
	toggle_visibility : function(item) {
		$(item).slideToggle(150);
	},
	focus_input : function(dom) {
		$(dom).find('input[type=input]').first().focus();
	},
	create_unique_id : (function() {
		var uniq = 0;
		return function() { return 'uniq_' + uniq++; };
	})(),
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
		if (null == obj || 'object' != typeof obj) return obj;

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

		throw new Error('Unable to copy obj! Its type is not supported.');
	},
	copy_field : function(from,to) {
		return function() { $(to).val($(from).val()); };
	},
	create_widget : function(widget_type, dom_parent, args) {
		w = new WIDGET[widget_type](null, args);
		dom_parent.append(w.dom);
		DOM_MAP.map.data.widget.push(w);
		return w;
	},
};


var WIDGET = {
	menu : function(dom_id) {
		var template = '#menu-template';
		var type = 'menu';
		var self = this;
		self.dom_id = dom_id;
		self.dom = $(template).clone(true);
		if(dom_id) $(self.dom).attr('id', dom_id);
		else $(self.dom).removeAttr('id');

		self.widgets = {}; // to keep track of any js objects
		// TODO
		//	 basic write functionality
		//   save expanded status
		//	 change sort order of items
		//	 copy items between parent items
		//	 unlink linked items

		$('#add-menu-category').click(function() { self.add_category(); });
		self.add_category = function() {
				category_id = UTIL.create_unique_id();
				category = {
					_dishes : [],
					id : category_id,
					id_category : category_id,
					loc : '0',
					name : '',
					sort : null
				};
				category_dom = $('#menu-category-template').clone(true);
				$(category_dom).removeAttr('id');
				$(category_dom).addClass('menu-category-' + category.id_category);
				$(self.dom).find('.admin-menu-categories').first().append(category_dom);
				this.apply_category(category_dom, category);
				UTIL.focus_input(category_dom);
		};

		this.add_option = function(option_group_dom, option) {
			option_dom = $('#menu-option-template').clone(true);
			$(option_dom).removeAttr('id');
			$(option_dom).addClass('menu-option-' + option.id_option);
			$(option_group_dom)
					.find('.admin-menu-options-container').first()
					.append(option_dom);
			this.apply_option(option_dom, option);
		};
		this.apply_option = function(option_dom, option) {
			fields = ['description', 'id', 'id_dish_option', 'id_option', 'name', 'price', 'sort', 'default'];
			for(i in fields) {
				field = fields[i];
				$(option_dom).find('.admin-menu-option-' + field).val(option[field]);
			}

			siblings = option_dom
					.closest('.admin-menu-option-group')
					.find('.admin-menu-option input.admin-menu-option-default');
			if(siblings.length === 1) {
				// this is the first one
				option_group_type = $(option_dom).closest('.admin-menu-option-group').find('.admin-menu-option-group-type').val();
				if(option_group_type === 'select') option_type = 'radio';
				if(option_group_type === 'check') option_type = 'checkbox';
				$(option_dom).find('input.admin-menu-option-default')
						.attr('name', UTIL.create_unique_id())
						.attr('type', option_type);
			}
			else {
				// copy first sibling
				$(option_dom).find('input.admin-menu-option-default')
						.attr('name', siblings.first().attr('name'))
						.attr('type', siblings.first().attr('type'));
			}

			if(!option.name) UTIL.focus_input(option_dom);
			$(option_dom).find('.admin-menu-option-default')
					.prop('checked', parseInt(option['default']));
			$(option_dom).find('.delete-option').click(function() {
				$(option_dom).remove();
			});
		};

		this.add_option_group = function(dish_dom, option_group) {
			option_group_dom = $('#menu-option-group-template').clone(true);
			$(option_group_dom).removeAttr('id');
			$(option_group_dom).addClass('menu-option-group-' + option_group.id_option);
			$(dish_dom).find('.dish-option-groups-container').first().append(option_group_dom);
			this.apply_option_group(option_group_dom, option_group);
		};
		this.apply_option_group = function(option_group_dom, option_group) {
			fields = ['description', 'id', 'id_dish_option', 'id_option', 'name', 'price', 'sort', 'default', 'type'];
			for(i in fields) {
				field = fields[i];
				$(option_group_dom).find('.admin-menu-option-group-' + field).val(option_group[field]);
			}
			if(/^basic options$/i.exec(option_group.name)) {
				$(option_group_dom).find('.admin-menu-option-group-name').prop('disabled', true);
				$(option_group_dom).find('.admin-menu-option-group-price').hide();
				$(option_group_dom).find('.admin-menu-option-group-description').hide();
				$(option_group_dom).find('.admin-menu-option-group-type').prop('disabled', true);
				$(option_group_dom).find('.admin-menu-option-group-type').val('check');
				$(option_group_dom).find('.delete-option-group').hide();
			}
			else {
				// evidently only 'select' boxes render properly on front end for now
				$(option_group_dom).find('.admin-menu-option-group-type').prop('disabled', true);
				$(option_group_dom).find('.admin-menu-option-group-type').val('select');
			}
			if(!option_group.name) UTIL.focus_input(option_group_dom);
			$(option_group_dom).find('.delete-option-group').click(function() {
				option_group_dom.remove();
			});
			$(option_group_dom).find('.admin-menu-option-group-type').change(function() {
				option_group_type = $(option_group_dom).find('.admin-menu-option-group-type').val();
				if(option_group_type === 'select') option_type = 'radio';
        if(option_group_type === 'check') option_type = 'checkbox';
				$(option_group_dom)
						.find('.admin-menu-option input.admin-menu-option-default')
						.attr('type', option_type);
			});
			$(option_group_dom).find('.add-option').click(function() {
				id_option = UTIL.create_unique_id();
				id_dish_option = UTIL.create_unique_id();
				self.add_option(option_group_dom, {
					default : '0',
					description : null,
					id : id_option,
					id_option : id_option,
					id_dish_option : id_dish_option,
					id_option_parent : $(option_group_dom).find('.admin-menu-option-group-id').val(),
					name : '',
					price : '0.00',
					prices : [],
					sort : null,
					type : 'check',
				});
			});
		};

		this.apply_dish = function(dish_dom, dish) {
			// 'view' button
			$(dish_dom).find('.details-button').first().click(function() {
				UTIL.toggle_visibility(dish_dom.find('.admin-menu-dish-details'));
			});
			$(dish_dom).find('.delete-menu-dish').first().click(function() {
				dish_dom.remove();
			});
			$(dish_dom).find('.add-option-group').first().click(function() {
				id_option_group = UTIL.create_unique_id();
				id_dish_option = UTIL.create_unique_id();
				self.add_option_group(dish_dom, {
					default : '0',
					description : null,
					id : id_option_group,
					id_option : id_option_group,
					id_dish_option : id_dish_option,
					id_option_parent : null,
					name : '',
					price : '0',
					prices : [],
					sort : null,
					type : 'select',
				});
			});

			// active toggle
			w_active = new WIDGET.toggle();
			w_id = UTIL.create_unique_id();
			self.widgets[w_id] = w_active;
			w_active.set_id(w_id);
			$(dish_dom).find('.admin-menu-dish-active-button').replaceWith(w_active.dom);
			$(dish_dom).find('.admin-menu-dish-active-id').val(w_id);
			w_active.val(dish.active);

			// basic fields
			fields = ['name','id_dish','id','description','price','sort','top','top_name','image'];
			for(i in fields) {
				field = fields[i];
				$(dish_dom).find('.admin-menu-dish-' + field).val(dish[field]);
			}

			if(!dish.name) UTIL.focus_input(dish_dom);

			// if has a parent, it's an option in an option group
			// if has a child, it's an option group
			// if neither, it's a basic option
			// have to loop through twice because we don't know what order they might come in

			basic_option_group_id = 'dish_' + dish.id_dish + '_basic_options';
			option_groups = [];
			options = [];
			parent_ids = [];
			for(i in dish._options) {
				o = dish._options[i];
				if(o.id_option_parent) {
					options.push(o);
					parent_ids.push(o.id_option_parent);
					continue;
				}
			}
			for(i in dish._options) {
				o = dish._options[i];
				if(o.id_option_parent) continue;
				if(parent_ids.indexOf(o.id) === -1) {
					o.id_option_parent = basic_option_group_id;
					options.push(o);
					continue;
				}
				else {
					option_groups.push(o);
					continue;
				}
			}

			option_groups.unshift({
				basic_group : 1,
				id : basic_option_group_id,
				id_dish_option : basic_option_group_id,
				id_option : basic_option_group_id,
				id_option_parent : null,
				name : 'Basic Options',
				price : '0.00',
				price_linked : '0',
				prices : [],
				sort : '0',
				type : 'check',
			});
			while(option_groups.length) {
				option_group = option_groups.splice(0, 1)[0];
				this.add_option_group(dish_dom, option_group);
			}
			while(options.length) {
				option = options.splice(0, 1)[0];
				option_group_dom = $(dish_dom).find('.menu-option-group-' + option.id_option_parent).first();
				this.add_option(option_group_dom, option);
			}
		};
		this.add_dish = function(category_dom, dish) {
			dish_dom = $('#menu-dish-template').clone(true);
			$(dish_dom).removeAttr('id');
			$(dish_dom).addClass('menu-dish-' + dish.id_dish);
			$(category_dom).find('.restaurant-dishes-container').first().append(dish_dom);
			this.apply_dish(dish_dom, dish);
		};
		this.apply_category = function(category_dom, category) {
			fields = ['name','id','loc','sort'];
			for(i in fields) {
				field = fields[i];
				$(category_dom).find('.admin-menu-category-' + field).val(category[field]);
			}
			for(i in category._dishes) {
				dish = category._dishes[i];
				this.add_dish(category_dom, dish);
			}
			$(category_dom).find('.delete-menu-category').first().click(function() {
				category_dom.remove();
			});
			$(category_dom).find('.add-menu-dish').first().click(function() {
				dish_id = UTIL.create_unique_id();
				self.add_dish(
						category_dom,
						{
							active : '1',
							description : null,
							id : dish_id,
							id_dish : dish_id,
							name : '',
							price : '10.00',
							sort : null,
							top : '0',
							top_name : null,
							type : 'dish',
						});
			});
		};
		this.apply = function(restaurant) {
			this.reset_dom();
			for(i in restaurant._categories) {
				category = restaurant._categories[i];
				category_dom = $('#menu-category-template').clone(true);
				$(category_dom).removeAttr('id');
				$(category_dom).addClass('menu-category-' + category.id_category);
				$(self.dom).find('.admin-menu-categories').first().append(category_dom);
				this.apply_category(category_dom, category);
			}
			// TODO figure out and indicate linked items
		};
		this.flush_option = function(dish, option_group, option_dom) {
			option = {};
			fields = ['id', 'id_dish_option', 'id_option', 'description', 'sort', 'name', 'price'];
			for(i in fields) {
				field = fields[i];
				option[field] = $(option_dom).find('.admin-menu-option-' + field).val();
			}
			option['type'] = 'check';
			option['prices'] = [];
			option['price_linked'] = '0';
			option['id_option_parent'] = option_group.id_option;
			if(/basic/i.exec(option['id_option_parent']))
				option['id_option_parent'] = 'BASIC';
			option['id_restaurant'] = ADMIN.id_restaurant;
			option['default'] = $(option_dom).find('.admin-menu-option-default').is(':checked') ? '1' : '0';
			dish._options.push(option);
		};
		this.flush_option_group = function(dish, option_group_dom) {
			option_group = {};
			fields = ['description', 'id', 'id_dish_option', 'id_option', 'name', 'price', 'sort', 'type'];
			for(i in fields) {
				field = fields[i];
				option_group[field] = $(option_group_dom).find('.admin-menu-option-group-' + field).val();
			}
			option_group['default'] = '0';
			option_group['id_option_parent'] = null;
			option_group['id_restaurant'] = ADMIN.id_restaurant;
			option_group['price_linked'] = '0';
			option_group['prices'] = [];

			$(option_group_dom).find('.admin-menu-option').each(function(index, option_dom) {
				self.flush_option(dish, option_group, option_dom);
			});
      if(/^basic options$/i.exec(option_group.name)) { return; }
			if($(option_group_dom).find('.admin-menu-option').length === 0) { return; }
			dish._options.push(option_group);
		};
		this.flush_dish = function(category, dish_dom) {
			dish = {};
			fields = ['id', 'id_dish', 'sort', 'top', 'top_name', 'image', 'name', 'price', 'description'];
			for(i in fields) {
				field = fields[i];
				dish[field] = $(dish_dom).find('.admin-menu-dish-' + field).val();
			}
			w_active = self.widgets[$(dish_dom).find('.admin-menu-dish-active-id').val()];
			dish['active'] = w_active.val();
			dish['id_category'] = category.id_category;
			dish['id_restaurant'] = ADMIN.id_restaurant;
			dish['type'] = 'dish';
			dish['_options'] = [];
			$(dish_dom).find('.admin-menu-option-group').each(function(index, option_group_dom) {
				self.flush_option_group(dish, option_group_dom);
			});
			category._dishes.push(dish);
		};
		this.flush_category = function(categories, category_dom) {
			category = {};
			fields = ['name', 'id', 'loc', 'sort'];
			for(i in fields) {
				field = fields[i];
				category[field] = $(category_dom).find('.admin-menu-category-' + field).val();
			}
			category['id_category'] = category.id;
			category['id_restaurant'] = ADMIN.id_restaurant;
			category._dishes = [];
			$(category_dom).find('.admin-menu-dish').each(function(index, dish_dom) {
				self.flush_dish(category, dish_dom);
			});
			categories.push(category);
		};
		this.flush = function(restaurant) {
			categories = [];
			$(self.dom).find('.admin-menu-category').each(function(index, category_dom) {
				self.flush_category(categories, category_dom);
			});
			restaurant._categories = categories;
		};
		this.validate = function(invalid_list) { 
			// TODO
		};
		this.remove = function() { self.dom.remove(); }
		this.reset_dom = function() {
			$(self.dom).find('.admin-menu-categories').empty();
		};
	},
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
					type: 'confirmation',
					value: 'main phone only', // TODO update this when backend supports it
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
	toggle : function(dom_id, args) {
		args = args || {};
		var template = '#toggle-template';
		var type = 'toggle';
		var self = this;
		self.field_name = args.field_name || 'active';
		self.dom_id = dom_id;
		self.dom = $(template).clone(true);
		if(dom_id) $(self.dom).attr('id', dom_id);
		else $(self.dom).removeAttr('id');
		this.set_id = function(dom_id) {
			self.dom_id = dom_id;
			$(self.dom).attr('id', dom_id);
		};
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
				return $(self.dom).hasClass('admin-toggle-active') ? '1':'0';
			}
		};
		this.apply = function(obj) { this.val(obj[self.field_name]); };
		this.flush = function(obj) { obj[self.field_name] = this.val(); };
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
		w = UTIL.create_widget('toggle', $('#restaurant-cash-container'), {field_name:'cash'});
		w = UTIL.create_widget('toggle', $('#restaurant-credit-container'), {field_name:'credit'});
		w = UTIL.create_widget('toggle', $('#restaurant-delivery-container'), {field_name:'delivery'});
		w = UTIL.create_widget('toggle', $('#restaurant-takeout-container'), {field_name:'takeout'});
		w = UTIL.create_widget('notifications', $('#notifications-container'));
		w = UTIL.create_widget('menu', $('#restaurant-menu-container'));
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
					obj	: 'restaurant',
					id	 : this.id_restaurant,
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
		},
		validate_data : {
			'input#restaurant-permalink' : ADMIN.restaurant_validate(ADMIN.restaurant_validate_functions.permalink),
		},
		data : { // map html elements to js restaurant object data parts
			text : {
				'input#restaurant-name' : ['name'],
				'input#restaurant-permalink' : ['permalink'],
				'input#restaurant-short-description' : ['short_description'],
				'input#restaurant-phone' : ['phone'],
				'input#restaurant-email' : ['email'],
				'textarea#restaurant-address' : ['address'],
				'textarea#restaurant-notes' : ['notes'],
				'textarea#restaurant-notes-todo' : ['notes_todo'],
				'textarea#restaurant-notes-owner' : ['notes_owner'],
				'select#restaurant-timezone' : ['timezone'],
				'select#restaurant-delivery-min-amt' : ['delivery_min_amt'],
				'input#restaurant-lat' : ['loc_lat'],
				'input#restaurant-lng' : ['loc_long'],
				'input#restaurant-delivery-min' : ['delivery_min'],
				'input#restaurant-delivery-fee' : ['delivery_fee'],
				'input#restaurant-delivery-radius' : ['delivery_radius'],
				'input#restaurant-delivery-estimated-time' : ['delivery_estimated_time'],
				'input#restaurant-pickup-estimated-time' : ['pickup_estimated_time'],
				'input#restaurant-fee-restaurant' : ['fee_restaurant'],
				'input#restaurant-fee-customer' : ['fee_customer'],
				'input#restaurant-tax' : ['tax'],
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
								segment_end	 = segment[1];
								if(/^0?0:00$/.exec(segment_end)) segment_end = '24:00';
								if(/^0?0:00$/.exec(segment_begin) &&
									 !/^24:00$/.exec(segment_end)		) {
									segment_day = days[(days.indexOf(days[day])+6)%(days.length)];
									segment_begin = '24:00';
									segment_end = segment_end.replace(
											/(\d+):/,
											parseInt(segment_end)+24+':');
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
								$('input#restaurant-hours-' + days[day]).val('Closed');
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
							if(begin_ampm === 'am' && begin_h === 12) { begin_h = begin_h - 12; }
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



