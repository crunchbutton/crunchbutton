// admin_restaurant.js


var DEBUG = {}

var UTIL = {
	show_validation_warnings : function(validation_warnings) {
		// validation_warnings is an ordered array, each item is [element, 'string']
		$('[data-step]').removeAttr('data-step');
		$('[data-intro]').removeAttr('data-intro');
		for(i = 0; i < validation_warnings.length; i++) {
			$(validation_warnings[i][0])
					.attr('data-step', i+1)
					.attr('data-intro', validation_warnings[i][1]);
		}
		introJs().start();
	},
	cssprop : function($e, id) {
		return parseInt($e.css(id), 10);
	},
	slide_swap : function($set1, $set2, duration) {
    var $set3 = $set2.last().nextAll();
    
    var mb_prev = UTIL.cssprop($set1.first().prev(), "margin-bottom");
    if (isNaN(mb_prev)) mb_prev = 0;
    var mt_next = UTIL.cssprop($set2.last().next(), "margin-top");
    if (isNaN(mt_next)) mt_next = 0;

    var mt_1 = UTIL.cssprop($set1.first(), "margin-top");
    var mb_1 = UTIL.cssprop($set1.last(), "margin-bottom");
    var mt_2 = UTIL.cssprop($set2.first(), "margin-top");
    var mb_2 = UTIL.cssprop($set2.last(), "margin-bottom");

    var h1 = $set1.last().offset().top + $set1.last().outerHeight() - $set1.first().offset().top;
    var h2 = $set2.last().offset().top + $set2.last().outerHeight() - $set2.first().offset().top;

    move1 = h2 + Math.max(mb_2, mt_1) + Math.max(mb_prev, mt_2) - Math.max(mb_prev, mt_1);
    move2 = -h1 - Math.max(mb_1, mt_2) - Math.max(mb_prev, mt_1) + Math.max(mb_prev, mt_2);
    move3 = move1 + $set1.first().offset().top + h1 - $set2.first().offset().top - h2 + 
        Math.max(mb_1,mt_next) - Math.max(mb_2,mt_next);
        
    // let's move stuff
    $set1.css('position', 'relative');
    $set2.css('position', 'relative');
    $set3.css('position', 'relative');    
    $set1.animate({'top': move1}, {duration: duration});
    $set3.animate({'top': move3}, {duration: duration/2});
    $set2.animate({'top': move2}, {duration: duration, complete: function() {
            // rearrange the DOM and restore positioning when we're done moving          
            $set1.insertAfter($set2.last())
            $set1.css({'position': 'static', 'top': 0});
            $set2.css({'position': 'static', 'top': 0});
            $set3.css({'position': 'static', 'top': 0});
        }
    });
	},
	toggle_visibility : function(item) {
		$(item).slideToggle(100);
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

		// broadcasting functionality
		$(self.dom).on('change', '.broadcast', function(event) {
			element = event.target;
			class_names = element.className.split(/\s+/);
			$.each(class_names, function(i, class_name) {
				if(!/^broadcast-/.exec(class_name)) return;
				if(element.type === 'text')
					$(self.dom).find('.' + class_name).val($(element).val());
				else if(['radio','checkbox'].indexOf(element.type) !== -1) {
					$(self.dom).find('.' + class_name).prop('checked',
						$(element).is(':checked'));
				}
			});
		});
		self.get_broadcast_class_name = function(sub_dom, selector, field_name, item_id) {
			return 'broadcast-' + field_name + '-' + item_id;
		};
		self.clear_broadcast = function(sub_dom, selector) {
			element = $(sub_dom).find(selector);
			if(!element.className) return;
			class_names = element.className.split(/\s+/);
			$.each(class_names, function(i, class_name) {
				if(/^broadcast/.exec(class_name)) $(element).removeClass(class_name);
			});
		};
		self.add_broadcasting_field = function(sub_dom, selector, field_name, item_id) {
			self.clear_broadcast(sub_dom, selector);
			$(sub_dom).find(selector)
				.addClass('broadcast')
				.addClass(
						self.get_broadcast_class_name(sub_dom, selector, field_name, item_id));
		};

		$('#add-menu-category').click(function() { self.add_category({duration:100}); });
		self.add_category = function(args) {
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
				duration = args.duration || 0;
				$(category_dom).hide().show(duration);
				this.apply_category(category_dom, category);
				UTIL.focus_input(category_dom);
		};

		this.add_option = function(option_group_dom, option, args) {
			args = args || {};
			option_dom = $('#menu-option-template').clone(true);
			$(option_dom).removeAttr('id');
			$(option_dom).addClass('menu-option-' + option.id_option);
			$(option_group_dom)
					.find('.admin-menu-options-container').first()
					.append(option_dom);
			duration = args.duration || 0;
			$(option_dom).hide().show(duration);
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

			// set up broadcasting
			self.add_broadcasting_field(
					option_dom,
					'input.admin-menu-option-name',
					'option-name',
					option.id_option);
			self.add_broadcasting_field(
					option_dom,
					'input.admin-menu-option-price',
					'option-price',
					option.id_option);
			self.add_broadcasting_field(
					option_dom,
					'input.admin-menu-option-default',
					'option-default',
					option.id_option);

			if(!option.name) UTIL.focus_input(option_dom);
			$(option_dom).find('.admin-menu-option-default')
					.prop('checked', parseInt(option['default']));
			$(option_dom).find('.delete-option').click(function() {
				$(option_dom).slideUp(100, function(){$(option_dom).remove();});
			});

			$(option_dom).find('.move_option_down').first().click(function() {
				next_option_dom = $(option_dom).next('.admin-menu-option');
				if(!next_option_dom.length) return;
        UTIL.slide_swap($(option_dom), $(next_option_dom), 100);
			});
			$(option_dom).find('.move_option_up').first().click(function() {
				prev_option_dom = $(option_dom).prev('.admin-menu-option');
				if(!prev_option_dom.length) return;
        UTIL.slide_swap($(prev_option_dom), $(option_dom), 100);
			});
		};

		this.add_option_group = function(dish_dom, option_group, args) {
			args = args || {};
			option_group_dom = $('#menu-option-group-template').clone(true);
			$(option_group_dom).removeAttr('id');
			$(option_group_dom).addClass('menu-option-group-' + option_group.id_option);
			$(dish_dom).find('.dish-option-groups-container').first().append(option_group_dom);
			duration = args.duration || 0;
			$(option_group_dom).hide().show(duration);
			this.apply_option_group(option_group_dom, option_group);
		};
		this.apply_option_group = function(option_group_dom, option_group) {
			fields = ['description', 'id', 'id_dish_option', 'id_option', 'name', 'price', 'sort', 'default', 'type'];
			for(i in fields) {
				field = fields[i];
				$(option_group_dom).find('.admin-menu-option-group-' + field).val(option_group[field]);
			}
			if(/^(?:basic options|checkbox options)$/i.exec(option_group.name)) {
				$(option_group_dom).find('.admin-menu-option-group-name').prop('disabled', true);
				$(option_group_dom).find('.admin-menu-option-group-type').prop('disabled', true);
				$(option_group_dom).find('.admin-menu-option-group-price').hide();
				$(option_group_dom).find('.admin-menu-option-group-description').hide();
				$(option_group_dom).find('.admin-menu-option-group-type').val('check');
				$(option_group_dom).find('.delete-option-group').hide();
				$(option_group_dom).find('.move_option_group_down').hide();
				$(option_group_dom).find('.move_option_group_up').hide();
				$(option_group_dom).find('.not-visible-for-basic-option-group').hide();
				$(option_group_dom).find('.add-option').hide();
				$(dish_dom).find('.add-checkbox-option').first().click(function() {
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
					},
					{duration:100});
				});
			}
			else {

				// set up broadcasting
				self.add_broadcasting_field(
						option_group_dom,
						'input.admin-menu-option-group-name',
						'option-group-name',
						option_group.id_option);

				// evidently only 'select' boxes render properly on front end for now
				$(option_group_dom).find('.admin-menu-option-group-type').prop('disabled', true);
				$(option_group_dom).find('.admin-menu-option-group-type').val('select');

				// move up/down, don't forget not to swap with basic options
				$(option_group_dom).find('.move_option_group_down').first().click(function() {
					next_option_group_dom = $(option_group_dom).next('.admin-menu-option-group');
					if(!next_option_group_dom.length) return;
					UTIL.slide_swap($(option_group_dom), $(next_option_group_dom), 100);
				});
				$(option_group_dom).find('.move_option_group_up').first().click(function() {
					prev_option_group_dom = $(option_group_dom).prev('.admin-menu-option-group:has(.admin-menu-option-group-name:enabled)');
					if(!prev_option_group_dom.length) return;
					UTIL.slide_swap($(prev_option_group_dom), $(option_group_dom), 100);
				});
			}
			if(!option_group.name) UTIL.focus_input(option_group_dom);
			$(option_group_dom).find('.delete-option-group').click(function() {
				$(option_group_dom).slideUp(100, function(){$(option_group_dom).remove();});
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
				},
				{duration:100});
			});
		};

		this.add_options_to_dish = function(dish_dom, options_to_add) {

			// if has a parent, it's an option in an option group
			// if has a child, it's an option group
			// if neither, it's a basic option
			// have to loop through twice because we don't know what order they might come in

			existing_options_in_dish = [];
			$(dish_dom).find('.admin-menu-option-group').each(function(i, e) {
				existing_options_in_dish.push($(e).find('.admin-menu-option-group-id').val());
			});
			$(dish_dom).find('.admin-menu-option').each(function(i, e) {
				existing_options_in_dish.push($(e).find('.admin-menu-option-id').val());
			});
			option_groups = [];
			options = [];
			parent_ids = [];
			for(i in options_to_add) {
				o = options_to_add[i];
				if(o.id_option_parent && !/basic/i.exec(o.id_option_parent)) {
					options.push(o);
					parent_ids.push(o.id_option_parent);
					continue;
				}
				if(/basic/i.exec(o.id)) {
					parent_ids.push(o.id);
				}
			}
			for(i in options_to_add) {
				o = options_to_add[i];
				if(o.id_option_parent && !/basic/i.exec(o.id_option_parent)) continue;
				if(parent_ids.indexOf(o.id) === -1) {
					o.id_option_parent = 'BASIC';
					options.push(o);
					continue;
				}
				else {
					option_groups.push(o);
					continue;
				}
			}

			while(option_groups.length) {
				option_group = option_groups.splice(0, 1)[0];
				if(existing_options_in_dish.indexOf(option_group.id) !== -1) continue;
				self.add_option_group(dish_dom, option_group);
			}
			while(options.length) {
				option = options.splice(0, 1)[0];
				if(existing_options_in_dish.indexOf(option.id) !== -1) continue;
				option_group_dom = $(dish_dom).find('.menu-option-group-' + option.id_option_parent).first();
				self.add_option(option_group_dom, option);
			}
		};
		this.add_dish = function(category_dom, dish, args) {
			args = args || {};
			dish_dom = $('#menu-dish-template').clone(true);
			$(dish_dom).removeAttr('id');
			$(dish_dom).addClass('menu-dish-' + dish.id_dish);
			duration = args.duration || 0;
			$(category_dom).find('.restaurant-dishes-container').first().append(dish_dom);
			$(dish_dom).hide().show(duration);
			this.apply_dish(dish_dom, dish);
		};

		this.copy_options_to_dish = function(dish_dom_from, dish_to_id) {
			dish_dom_to = $('.admin-menu-dish-id[value=' + dish_to_id + ']')
				.closest('.admin-menu-dish');
			dummy_category_obj = { _dishes : [] };
			this.flush_dish(dummy_category_obj, dish_dom_from);
			dish_from = dummy_category_obj._dishes[0];
			options = dish_from._options;
			if(!options) return;
			self.add_options_to_dish(dish_dom_to, options);
		};

		this.move_dish_to_category = function(dish_dom, category_id) {
			category_dom = $('.admin-menu-category-id[value=' + category_id + ']')
					.closest('.admin-menu-category');
			$(dish_dom).appendTo($(category_dom).find('.restaurant-dishes-container'));
			$(dish_dom).hide().show(100);
		};

		this.copy_dish_to_category = function(dish_dom, category_id) {
			category_dom = $('.admin-menu-category-id[value=' + category_id + ']')
					.closest('.admin-menu-category');
			dummy_category_obj = { _dishes : [] };
			this.flush_dish(dummy_category_obj, dish_dom);
			dish = dummy_category_obj._dishes[0];
			dish.id = dish.id_dish = UTIL.create_unique_id();
			this.add_dish(category_dom, dish, {duration:100});
		};

		this.dish_lightning = function(dish_dom) {

			dish_id = $(dish_dom).find('input.admin-menu-dish-id').val();
			dish_name = $(dish_dom).find('input.admin-menu-dish-name').val();
			category_id = $(dish_dom)
					.closest('.admin-menu-category')
					.find('input.admin-menu-category-id').val();

			modal_dom = $('#admin-dish-lightning-modal');
			$(modal_dom).dialog({
				modal : true,
				show : { effect : 'drop', duration : 100, direction : 'up', },
				hide : { effect : 'drop', duration : 100, direction : 'up', },
				resizable : false,
				width : '400px',
			});

			// override some of the stupid jqueryui crap
			$('.ui-widget-overlay').click(function() {
				$('#admin-dish-lightning-modal').dialog('close');
			});
			$('.ui-dialog-titlebar').addClass('hidden');
			$('#admin-dish-lightning-modal').find('.lightning-cancel').click(function() {
				$('#admin-dish-lightning-modal').dialog('close');
			});

			$(modal_dom).find('.admin-modal-title').text(dish_name);
			
			select_element_1 = $(modal_dom)
					.find('select.move-dish-to-category-select')
					.html('');
			select_element_2 = $(modal_dom)
					.find('select.copy-dish-to-category-select')
					.html('');
			select_element_3 = $(modal_dom)
					.find('select.copy-options-to-dish-select')
					.html('<option value=>Choose...</option>');

			$('.admin-menu-category').each(function(i, category_dom) {
				c_id = $(category_dom).find('input.admin-menu-category-id').val();
				c_name = $(category_dom).find('input.admin-menu-category-name').val();
				if(!c_id || !c_name) return;
				$(select_element_1)
						.append('<option></option>').find('option').last()
						.val(c_id).text(c_name);
				$(select_element_2)
						.append('<option></option>').find('option').last()
						.val(c_id).text(c_name);
				category_optgroup_3 = $(select_element_3)
						.append('<optgroup></optgroup>')
						.find('optgroup').last()
						.attr('label', c_name);
				$(category_dom)
						.find('.admin-menu-dish').each(function(j, dish_dom) {
								d_id = $(dish_dom).find('input.admin-menu-dish-id').val();
								d_name = $(dish_dom).find('input.admin-menu-dish-name').val();
								if(!d_id || !d_name) return;
								$(category_optgroup_3).append('<option></option>')
										.find('option').last()
										.val(d_id).text(d_name);
						});
			});
			select_element_1.find('option[value=' + category_id + ']')
					.attr('disabled', 'disabled');
			select_element_2.val(category_id);
			select_element_3.find('option[value=' + dish_id + ']')
					.attr('disabled', 'disabled');

			$(modal_dom).find('.move-dish-to-category-button').first()
				.off('click.move-dish-to-category-button')
				.on(
					'click.move-dish-to-category-button',
					(function() {
						return function() {
							self.move_dish_to_category(
									dish_dom,
									$(modal_dom).find('select.move-dish-to-category-select').val());
							$(modal_dom).dialog('close');
						};
					})());
			$(modal_dom).find('.copy-dish-to-category-button').first()
				.off('click.copy-dish-to-category-button')
				.on(
					'click.copy-dish-to-category-button',
					(function() {
						return function() { 
							self.copy_dish_to_category(
									dish_dom, 
									$(modal_dom).find('select.copy-dish-to-category-select').val());
							$(modal_dom).dialog('close');
						};
					})());
			$(modal_dom).find('.copy-options-to-dish-button').first()
				.off('click.copy-options-to-dish-button')
				.on(
					'click.copy-options-to-dish-button',
					(function() {
						return function() {
							dish_to_id = $(modal_dom).find('select.copy-options-to-dish-select').val();
							if(!dish_to_id) return;
							self.copy_options_to_dish(
									dish_dom, dish_to_id);
							$(modal_dom).dialog('close');
						};
					})());
		};

		this.apply_dish = function(dish_dom, dish) {
			// 'view' button
			$(dish_dom).find('.details-button').first().click(function() {
				UTIL.toggle_visibility(dish_dom.find('.admin-menu-dish-details'));
			});
			$(dish_dom).find('.delete-menu-dish').first().click(function() {
				$(dish_dom).slideUp(100, function(){$(dish_dom).remove();});
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
				},
				{duration:100});
			});

			$(dish_dom).find('.dish_lightning').first().click(function() {
				self.dish_lightning(dish_dom);
			});

			$(dish_dom).find('.move_dish_down').first().click(function() {
				next_dish_dom = $(dish_dom).next('.admin-menu-dish');
				if(!next_dish_dom.length) return;
        UTIL.slide_swap($(dish_dom), $(next_dish_dom), 100);
			});
			$(dish_dom).find('.move_dish_up').first().click(function() {
				prev_dish_dom = $(dish_dom).prev('.admin-menu-dish');
				if(!prev_dish_dom.length) return;
        UTIL.slide_swap($(prev_dish_dom), $(dish_dom), 100);
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
			$(dish_dom).find('.admin-menu-dish-expand-view')
          .prop('checked', parseInt(dish['expand_view']));

			if(!dish.name) UTIL.focus_input(dish_dom);

			options = dish._options;
			if(!options) options = [];
			options.unshift({
				basic_group : 1,
				id : 'BASIC',
				id_dish_option : null,
				id_option : 'BASIC',
				id_option_parent : null,
				name : 'Checkbox Options',
				price : '0.00',
				price_linked : '0',
				prices : [],
				sort : '0',
				type : 'check',
			});
			self.add_options_to_dish(dish_dom, options);

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
				$(category_dom).slideUp(100, function(){$(category_dom).remove();});
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
						},
						{duration:100});
			});
			$(category_dom).find('.move_category_down').first().click(function() {
				next_category_dom = $(category_dom).next('.admin-menu-category');
				if(!next_category_dom.length) return;
				UTIL.slide_swap($(category_dom), $(next_category_dom), 100);
			});
			$(category_dom).find('.move_category_up').first().click(function() {
				prev_category_dom = $(category_dom).prev('.admin-menu-category');
				if(!prev_category_dom.length) return;
				UTIL.slide_swap($(prev_category_dom), $(category_dom), 100);
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
		};
		this.flush_option = function(dish, option_group, option_dom) {
			option = {};
			fields = ['id', 'id_dish_option', 'id_option', 'description', 'sort', 'name', 'price'];
			for(i in fields) {
				field = fields[i];
				option[field] = $(option_dom).find('.admin-menu-option-' + field).val();
			}
			if(!option['name']) return;
			option['type'] = 'check';
			option['prices'] = [];
			option['price_linked'] = '0';
			option['id_option_parent'] = option_group.id_option;
			if(/(?:basic|checkbox)/i.exec(option['id_option_parent']))
				option['id_option_parent'] = 'BASIC';
			option['id_restaurant'] = ADMIN.id_restaurant;
			option['default'] = $(option_dom).find('.admin-menu-option-default').is(':checked') ? '1' : '0';
			if(dish._options.length === 0) {
				option['sort'] = 1;
			}
			else {
				option['sort'] = dish._options[dish._options.length - 1]['sort'] + 1;
			}
			dish._options.push(option);
		};
		this.flush_option_group = function(dish, option_group_dom) {
			option_group = {};
			fields = ['description', 'id', 'id_dish_option', 'id_option', 'name', 'price', 'sort', 'type'];
			for(i in fields) {
				field = fields[i];
				option_group[field] = $(option_group_dom).find('.admin-menu-option-group-' + field).val();
			}
			if(!option_group['name']) return;
			option_group['default'] = '0';
			option_group['id_option_parent'] = null;
			option_group['id_restaurant'] = ADMIN.id_restaurant;
			option_group['price_linked'] = '0';
			option_group['prices'] = [];

			if(option_group['type'] === 'select') {
				if($(option_group_dom)
						.find('input.admin-menu-option-default')
						.filter(':checked')
						.length < 1) {
					$(option_group_dom)
							.find('input.admin-menu-option-default')
							.first()
							.prop('checked', 1);
				}
			}
			$(option_group_dom).find('.admin-menu-option').each(function(index, option_dom) {
				self.flush_option(dish, option_group, option_dom);
			});
      if(/^(?:basic options|checkbox options)$/i.exec(option_group.name)) { return; }
			if($(option_group_dom).find('.admin-menu-option').length === 0) { return; }

			if(dish._options.length === 0) {
				option_group['sort'] = 1;
			}
			else {
				option_group['sort'] = dish._options[dish._options.length - 1]['sort'] + 1;
			}
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
			dish['expand_view'] = $(dish_dom).find('.admin-menu-dish-expand-view')
					.is(':checked') ? '1' : '0';

			$(dish_dom).find('.admin-menu-option-group').each(function(index, option_group_dom) {
				self.flush_option_group(dish, option_group_dom);
			});
			if(category._dishes.length === 0) {
				dish['sort'] = 1;
			}
			else {
				dish['sort'] = category._dishes[category._dishes.length - 1]['sort'] + 1;
			}
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
			if(categories.length === 0) {
				category['sort'] = 1;
			}
			else {
				category['sort'] = categories[categories.length - 1]['sort'] + 1;
			}
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

		$('#add-notification').click(function(){self.add_notification({duration:100});});

		self.add_notification = function(args) {
			w = new WIDGET.notification();
			$(self.dom).append(w.dom);
			duration = args.duration || 0;
			$(w.dom).hide().show(duration);
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
					value: 'main phone', // TODO update this when backend supports it
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
			if(data.type === 'confirmation') {
				$(self.dom).find('input[name=val]').prop('disabled', true);
			}
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
		self.text = args.text || ['active', 'inactive'];
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
			$(self.dom).text(self.text[0]);
		};
		this.set_inactive = function() {
			$(self.dom).removeClass('admin-toggle-active');
			$(self.dom).addClass('admin-toggle-inactive');
			$(self.dom).text(self.text[1]);
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
		w = UTIL.create_widget('toggle', $('#restaurant-open-for-business-container'), {
				text : ['open', 'closed'],
				field_name : 'open_for_business',
		});
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
          data: { restaurant:ADMIN.restaurant, obj:'restaurant' },
					id	 : this.id_restaurant,
				},
				function(rsp) {
					if(!ADMIN.restaurant_original) {
						ADMIN.restaurant_original = UTIL.deep_copy(rsp);
					}
					ADMIN.restaurant = rsp;
					window.history.pushState(null, null, ADMIN.restaurant.id_restaurant);
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
		validation_warnings = DOM_MAP.validate();
		if(validation_warnings.length) {
			console.log(validation_warnings);
			UTIL.show_validation_warnings(validation_warnings);
			return;
		}
		DOM_MAP.flush();
		console.log(ADMIN.restaurant);
		if(!ADMIN.save_is_safe) {
			UTIL.show_msg('There are errors somewhere.');
			return;
		}
		ASYNC.req(
				{ 
					type : 'sav',
          data : { 
						// uncomment here and in controller to use serialization
            // serialized_data : $.param(ADMIN.restaurant),
						data : ADMIN.restaurant,
            obj : 'restaurant'
          },

				},
				function(rsp) {
					ADMIN.restaurant = rsp.data;
					DOM_MAP.apply();
					UTIL.show_msg('Restaurant saved.');
				});
	},
};

var DOM_MAP = {
	// an adapter specific to this page and this object
	apply : function() {
		for(item in this.map.onclick) {
			$(item).unbind('click');
			$(item).click(this.map.onclick[item]);
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
	validate : function() {
		validation_warnings = [];
		for(item in DOM_MAP.map.validate) {
			$(item).each(function(index, element) {
				rsp = DOM_MAP.map.validate[item](element);
				if(!rsp) return;
				validation_warnings.push([element, rsp]);
			});
		}
		return validation_warnings;
	},
	map : {
		onclick : { // map html elements to functions
			'#save-button'		: ADMIN.restaurant_save,
			'#revert-button'	: ADMIN.restaurant_revert,
			'#legacy-button'	: UTIL.go_to_legacy_view,
			'#view-on-site-button' : UTIL.go_to_site_view,
			'#copy-tue-from-mon' : UTIL.copy_field('#restaurant-hours-mon','#restaurant-hours-tue'),
			'#copy-wed-from-tue' : UTIL.copy_field('#restaurant-hours-tue','#restaurant-hours-wed'),
			'#copy-thu-from-wed' : UTIL.copy_field('#restaurant-hours-wed','#restaurant-hours-thu'),
			'#copy-fri-from-thu' : UTIL.copy_field('#restaurant-hours-thu','#restaurant-hours-fri'),
			'#copy-sat-from-fri' : UTIL.copy_field('#restaurant-hours-fri','#restaurant-hours-sat'),
			'#copy-sun-from-sat' : UTIL.copy_field('#restaurant-hours-sat','#restaurant-hours-sun'),
		},
		validate : {
			'input#restaurant-permalink' : function(element) {
				if(!/^[-a-z\d]+$/.exec($(element).val()))
					return 'Use lowercase letters, numbers and dashes.';
			},
			'input[id^=restaurant-hours-]' : function(element) {
				val = $(element).val();
				if(/^(?: *|Closed)$/i.exec(val)) return;
				val = val.replace(/\(.*?\)/g, '');
				segments = val.split(/(?:and|,)/);
				for(i in segments) {
					if(!/^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i.exec(segments[i])) {
						return 'Unable to figure out what this time means.';
					}
				}
			},
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
				'#button-fax' : {
					apply : function(restaurant, element) { 
						$(element).attr('href', '/admin/restaurants/' + restaurant.id_restaurant + '/fax');
					},
					flush : function(restaurant, element) { },
				},
				'#button-pay' : {
					apply : function(restaurant, element) { 
						$(element).attr('href', '/admin/restaurants/' + restaurant.id_restaurant + '/pay');
					},
					flush : function(restaurant, element) { },
				},
				'#view-on-site-button' : {
					apply : function(restaurant, element) { 
						$(element).attr('href', '/food-delivery/' + restaurant.id_restaurant);
					},
					flush : function(restaurant, element) { },
				},
				'#view-on-site-button' : {
					apply : function(restaurant, element) { 
						$(element).attr('href', '/food-delivery/' + restaurant.id_restaurant);
					},
					flush : function(restaurant, element) { },
				},
				'#restaurant-image' : {
					apply : function(restaurant, element) {
						$(element).find('a').attr(
								'href', 
								'/admin/restaurants/' + restaurant.id_restaurant + '/image');
						$(element).find('img').attr('src', restaurant.img);
					},
					flush : function(restaurant, element) { },
				},
				'#restaurant-address' : {
					apply : function(restaurant, element) { element.focusout(); },
					flush : function(restaurant, element) { },
				},
				'#restaurant-hours' : {
					apply : function(restaurant, element) {
						hours = restaurant._hours;
						// 1. convert everything to 'hours from Monday morning midnight'
						hfmmm = []; // pairs of [start,finish]
						days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
						for(day in hours) {
							dayhours = days.indexOf(day)*2400;
							for(i in hours[day]) {
								segment = hours[day][i];
								m0 = /(\d+):(\d+)/.exec(segment[0]);
								m1 = /(\d+):(\d+)/.exec(segment[1]);
								hfmmm.push({
									b : (dayhours + parseInt(m0[1], 10) * 100 + parseInt(m0[2], 10)),
									e : (dayhours + parseInt(m1[1], 10) * 100 + parseInt(m1[2], 10)),
								});
							}
						}
						// 2. sort
						hfmmm.sort(function(a,b) { return (a.b<b.b?-1:(a.b>b.b?1:0)); });
						// 3. merge things that should be merged
						for(i = 0; i < hfmmm.length-1; i++) {
							if(hfmmm[i+1].b <= hfmmm[i].e) {
								// merge these two segments
								hfmmm[i].e = hfmmm[i+1].e;
								hfmmm.splice(i+1,1);
								i--;
								continue;
							}
						}
						// 4. render to display
						$('input[id^=restaurant-hours-]').val('Closed');
						for(i in hfmmm) {
							segment = hfmmm[i];
							day = days[Math.floor(segment.b/2400)];
							element = $('input#restaurant-hours-' + day);
							if(element.val() === 'Closed') {
								element.val('');
							}
							else {
								element.val(element.val() + ', ');
							}
							while(segment.b > 2400) {
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
								if(h === 0) { shorthand = 'midnight'; ampm = 'AM'; }
								else if(h === 12) { shorthand = 'noon'; ampm = 'PM'; }
								else if(h === 24) { shorthand = 'midnight'; ampm = 'AM'; h_fmt = '12'; }
								else if(h < 12) { ampm = 'AM'; }
								else if(h < 24) { h_fmt = '' + (h - 12); ampm = 'PM'; }
								else { ampm = 'AM'; h_fmt = '' + (h - 24); }
								if(m) { m_fmt = ':' + UTIL.pad_number(m, 2); }
								fmt = '' + (h_fmt || h) + m_fmt + ' ' + ampm;
								if(shorthand) fmt = fmt + ' (' + shorthand + ')';
								return fmt;
							};
							segment.b_fmt = format_time(segment.b);
							segment.e_fmt = format_time(segment.e);
							segment.fmt = segment.b_fmt + ' - ' + segment.e_fmt;
							element.val(element.val() + segment.fmt);
						}
					},
					flush : function(restaurant, element) {
						_hours = {};
						days = ['mon','tue','wed','thu','fri','sat','sun'];
						for(day in days) {
							val = $('input#restaurant-hours-' + days[day]).val();
							if(/^(?: *|closed)$/i.exec(val)) continue;
							save_one_time_segment = function(val) {
								val = val.replace(/\(.*?\)/g, '');
								val = val.replace(/midnight/i, '0:00 AM');
								val = val.replace(/noon/i, '12:00 PM');
								m = /^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i.exec(val)
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
							};
							segments = val.split(/(?:and|,)/);
							for(i in segments) save_one_time_segment(segments[i]);
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



