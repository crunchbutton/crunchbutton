
/**
 * generate ab formulas
 */
App.AB = {
	options: {
		tagline: [
			{
				name: 'tagline-for-free',
				tagline: 'Order the top food %s. For free. <br /> After you order, everything is saved for future 1 click ordering. <br /><strong>Choose a restaurant:</strong>'
			},
			{
				name: 'tagline-no-free',
				tagline: 'Order the top food %s. <br /> After you order, everything is saved for future 1 click ordering. <br /><strong>Choose a restaurant:</strong>'		
			}
		],
		slogan: [
			{
				name: 'slogan-push-food',
				slogan: 'Push a button. Get Food.'
			}
		],
		restaurantPage: [
			{
				name: 'restaurant-page-noimage'
			},
			{
				name: 'restaurant-page-image',
				disabled: true
			}
		],
		dollarSign: [
			{
				name : 'show'
			},
			{
				name : 'hide'
			}
		],
		changeablePrice: [
			{
				name : 'show'
			},
			{
				name : 'hide'
			}
		]
	},
	init: function() {
		if (!App.config.ab) {
			// we dont have ab variables. generate them
			App.AB.create(true);
		}
		App.AB.load();
	},
	create: function(clear) {
		if (clear) {
			App.config.ab = {};
		}
		
		_.each(App.AB.options, function(option, key) {
			if (App.config.ab[key]) {
				return;
			}
			var opts = _.filter(App.AB.options[key], function(o) { return o.disabled ? false : true; });
			var opt = opts[Math.floor(Math.random()*opts.length)];
			App.config.ab[key] = opt.name
			App.trackProperty('AB-' + key, opt.name);
		});
		
		App.AB.save();
		console.log(App.config.ab);
		
	},
	load: function() {
		App.slogan = _.findWhere(App.AB.options.slogan, {name: App.config.ab.slogan});
		App.tagline = _.findWhere(App.AB.options.tagline, {name: App.config.ab.tagline});

		if (!App.slogan || !App.tagline) {
			App.AB.create(true);
			App.AB.load(true);
		}
	},
	save: function() {
		$.ajax({
			url: App.service + 'config',
			data: {ab: App.config.ab},
			dataType: 'json',
			type: 'POST',
			complete: function(json) {

			}
		});
	}
};
