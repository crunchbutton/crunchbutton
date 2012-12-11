var App = {};

var server = '/';


$(function() {


	var Player = Backbone.Model.extend({
		idAttribute: 'id_player',
		urlRoot: server + 'player',
		party: function() {
			return CreatureCollection.filter({team: 1});
		},
		creatures: function() {
			return this._creatures;
		}
	});

	var Creature = Backbone.Model.extend({
		idAttribute: 'id_creature',
		localStorage: new Backbone.LocalStorage('Creature')
	});

	var CreatureCollection = Backbone.Collection.extend({
		model: Creature,
		localStorage: new Backbone.LocalStorage('CreatureCollection')
	});

	var Event = Backbone.Model.extend({
		idAttribute: 'id_event',
		urlRoot: server + 'event',
		zones: function() {
			if (!this.__zones) {
				this.__zones = [];
				for (x in this.attributes._zones) {
					this.__zones[this.__zones.length] = new Zone(this.attributes._zones[x]);
				}
			}
			return this.__zones;
		}
	});

	var EventCollection = Backbone.Collection.extend({
		model: Event
	});
	
	var Zone = Backbone.Model.extend({
		idAttribute: 'id_zone',
		urlRoot: server + 'zone',
		quests: function() {
			if (!this.__quests) {
				this.__quests = [];
				for (x in this.attributes._quests) {
					this.__quests[this.__quests.length] = new Quest(this.attributes._quests[x]);
				}
			}
			return this.__quests;
		}
	});
	
	var ZoneCollection = Backbone.Collection.extend({
		model: Zone
	});
	
	var Quest = Backbone.Model.extend({
		idAttribute: 'id_quest',
		urlRoot: server + 'quest',
		progress: function() {
			var self = this;
			$.getJSON(server + 'quest/' + this.attributes.id_quest + '/progress', function(res, status) {
				if (status == 'success') {
					var quest = res.quest;
					var loot = res.loot;
					for (var x in quest) {
						self.attributes[x] = quest[x];
					}
					self._view.loot = loot;
					self.trigger('change');
				}
			});
		}
	});

	var MainView = Backbone.View.extend({
		id: 'mainView',
		el: $('body'),
		subviews:null,
		subviewsNavigator:null,

		render:function () {
			var html = this.template();
			this.el.innerHTML = html;

			App.navigator = new BackStack.StackNavigator({
				el: '#wrapper'
			});
			App.navigator.defaultPushTransition = new BackStack.NoEffect();

			Backbone.history.start({pushState: true});

		},
		initialize: function (args) {
			this.template = _.template($('#root-template').html());
		}
	});
	
	var ErrorView = Backbone.View.extend({
		el: $('body'),
		render:function (er) {
			this.el.innerHTML  = this.template({
				error: er
			});
		},
		initialize: function (args) {
			this.template = _.template($('#error-template').html());
		}
	});

	var UpdateView = Backbone.View.extend({
		className: 'update content',
		el: $('body'),
		render : function() {
			this.el.innerHTML = this.template();

			var $topLoader = $('#updateLoader').percentageLoader({
				width: 256,
				height: 256,
				controllable: true,
				progress: 0,
				onProgressUpdate: function(val) {
					$topLoader.setValue(Math.round(val * 100.0));
				}
			});

			this.loader = $topLoader;

		},
		initialize: function (args) {
			this.template = _.template($('#update-template').html());
		}
	});

	var NewsView = Backbone.View.extend({
		className: 'news content',
		model: 'Event',
		render : function() {
			var html = this.template({
				player: App.player,
				events: App.events
			});
			this.$el.html(html);
		},
		events: {
			'click .event-link': 'eventClick'
		},
		eventClick: function(e) {
			var event = App.events.get(
				$(e.toElement).attr('data-id_event')
			);
			App.router.navigate('/event/' + event.attributes.id_event, {trigger: true});
		},
		initialize: function (args) {
			this.template = _.template($('#news-template').html());
			//_.bindAll(this, 'changeName');
			//this.model.bind('change:name', this.changeName);
		}
	});

	var CreaturesView = Backbone.View.extend({
		tagName : 'div',
		className: 'creatures content',
		render : function() {
			var html = this.template({
				player: App.player
			});
			this.el.innerHTML = html;
		},
		initialize: function (args) {
			this.template = _.template($('#creatures-template').html());
			//_.bindAll(this, 'changeName');
			//this.model.bind('change:name', this.changeName);
		}
	});

	var SettingsView = Backbone.View.extend({
		tagName : 'div',
		className: 'settings content',
		render : function() {
			this.el.innerHTML = this.template();
		},
		initialize: function (args) {
			this.template = _.template($('#settings-template').html());
			//_.bindAll(this, 'changeName');
			//this.model.bind('change:name', this.changeName);
		}
	});

	var StoryView = Backbone.View.extend({
		tagName : 'div',
		className: 'story content',
		render : function() {
			this.el.innerHTML = this.template({
				events: App.events
			});
		},
		events: {
			'click .event-link': 'eventClick'
		},
		eventClick: function(e) {
			var event = App.events.get(
				$(e.toElement).attr('data-id_event')
			);
			App.router.navigate('/event/' + event.attributes.id_event, {trigger: true});
		},
		initialize: function (args) {
			this.template = _.template($('#story-template').html());
			//_.bindAll(this, 'changeName');
			//this.model.bind('change:name', this.changeName);
		}
	});

	var EventView = Backbone.View.extend({
		tagName : 'div',
		className: 'events content',
		model: 'Event',
		render : function() {
			if (!this.model.attributes.name) {
				return;
			}
			var html = this.template({
				event: this.model,
			});
			this.el.innerHTML = html;
		},
		events: {
			'click .zone-link': 'zoneClick'
		},
		zoneClick: function(e) {
			App.router.navigate('/zone/' + $(e.toElement).attr('data-id_zone'), {trigger: true});
		},
		modelChanged: function() {
			this._view.render();
		},
		initialize: function (args) {
			this.template = _.template($('#event-template').html());
			this.model.bind('change', this.modelChanged);
		}
	});
	
	var ZoneView = Backbone.View.extend({
		className: 'zones content',
		model: 'Zone',
		render : function() {
			if (!this.model.attributes.name) {
				return;
			}
			var html = this.template({
				zone: this.model
			});
			this.el.innerHTML = html;
		},
		events: {
			'click .quest-link': 'questClick'
		},
		questClick: function(e) {
			App.router.navigate('/quest/' + $(e.toElement).attr('data-id_quest'), {trigger: true});
		},
		modelChanged: function() {
			this._view.render();
		},
		initialize: function (args) {	
			this.template = _.template($('#zone-template').html());
			this.model.bind('change', this.modelChanged);
		}
	});
	
	var QuestView = Backbone.View.extend({
		className: 'quests content',
		model: 'Quest',
		loot: [],
		render : function() {
			if (!this.model.attributes.name) {
				return;
			}

			var html = this.template({
				quest: this.model,
				loot: this.loot
			});
			this.el.innerHTML = html;
		},
		events: {
			'click .quest-progress': 'questProgressClick'
		},
		questProgressClick: function(e) {
			this.model.progress();
		},
		modelChanged: function() {
			this._view.render();
		},
		initialize: function (args) {	
			this.template = _.template($('#quest-template').html());
			this.model.bind('change', this.modelChanged);
		}
	});

	var HomeView = Backbone.View.extend({
		className: 'home content',
		render : function() {
			this.el.innerHTML = this.template();
		},
		initialize: function (args) {
			this.template = _.template($('#home-template').html());
			//_.bindAll(this, 'changeName');
			//this.model.bind('change:name', this.changeName);
		}
	});

	var PlayerView = Backbone.View.extend({
		className: 'player content',
		render: function() {
			// prevents triggering model changes prior to stack push
			if (!this.model.attributes.name) {
				return;
			}
			var html = this.template({
				player: this.model
			});
			this.el.innerHTML = html;
		},
		modelChanged: function() {
			this._view.render();
		},
		initialize: function (args) {
			this.template = _.template($('#player-template').html());
			this.model.bind('change', this.modelChanged);
		}
	});

	var AppRouter = Backbone.Router.extend({
		routes: {
			'event/:id': 'getEvent',
			'player/:id': 'getPlayer',
			'player': 'getPlayer',
			'quest/:id': 'getQuest',
			'zone/:id': 'getZone',
			'me': 'getPlayer',
			'settings': 'getSettings',
			'creatures': 'getCreatures',
			'game': 'getStory',
			'news': 'defaultRoute',
			'home': 'getHome',
			'items': 'getItems',
			'forceupdate': 'getUpdate',
			'*actions': 'defaultRoute',
		}
	});
	
	App.router = new AppRouter;
	
	App.router.on('route:getItems', function(actions) {
	
		var zone = new Zone({id_zone: actions});
		var itemsView = new ItemsView({
			model: zone
		});
		zone._view = zoneView;
		zone.fetch();

		App.navigator.pushView(zoneView);
	});
	
	App.router.on('route:getZone', function(actions) {
	
		var zone = new Zone({id_zone: actions});
		var zoneView = new ZoneView({
			model: zone
		});
		zone._view = zoneView;
		zone.fetch();

		App.navigator.pushView(zoneView);
	});
	
	App.router.on('route:getQuest', function(actions) {
	
		var quest = new Quest({id_quest: actions});
		var questView = new QuestView({
			model: quest
		});
		quest._view = questView;
		quest.fetch();

		App.navigator.pushView(questView);
	});

	App.router.on('route:defaultRoute', function(actions) {
		App.navigator.pushView(NewsView);
	});

	App.router.on('route:getUpdate', function(actions) {
		localStorage.clear();
		$.getJSON(server + 'start', function(res, status) {
			if (status == 'success') {
				AppUpdate(res);
			}
		});
	});

	App.router.on('route:getSettings', function(actions) {
		App.navigator.pushView(SettingsView);
	});

	App.router.on('route:getCreatures', function(actions) {
		App.navigator.pushView(CreaturesView);
	});

	App.router.on('route:getStory', function(actions) {
		App.navigator.pushView(StoryView);
	});

	App.router.on('route:getHome', function(actions) {
		App.navigator.pushView(HomeView);
	});

	App.router.on('route:getEvent', function(actions) {
		var event = new Event({id_event: actions});
		var eventView = new EventView({
			model: event
		});
		event._view = eventView;
		eventView.model.fetch();

		App.navigator.pushView(eventView);
	});

	App.router.on('route:getPlayer', function(actions) {
		if (actions) {
			var player = new Player({id_player: actions});
			var playerView = new PlayerView({
				model: player
			});
			player._view = playerView;
			playerView.model.fetch();
		} else {
			var playerView = new PlayerView({
				model: App.player
			});
		}
		App.navigator.pushView(playerView);
	});

	var AppInit = function(res) {
		localStorage.setItem('version', res.version);

		var player = res.player;
		var creatures = res.player.creatures;
		delete player.creatures;

		for (var x in creatures) {
			var creature = new Creature(creatures[x].creature);
			creature.save();
		}

		App.player = new Player(player);
		App.events = new EventCollection(res.events);
		App.zones = new ZoneCollection();

		var main = new MainView;
		main.render();
	};

	var AppUpdate = function(res) {
		AppInit(res);
	};

	$.getJSON(server + 'config', function(res, status) {
		if (status == 'success') {
			var currentVersion = localStorage.getItem('version');
			if (currentVersion != res.version) {
				// need to update
				AppUpdate(res);
			} else {
				AppInit(res);
			}
		}
	}).error(function() {
		var main = new ErrorView;
		main.render('The server is currently down. Sorry!');
	})
});
