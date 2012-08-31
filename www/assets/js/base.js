if (typeof(console) == 'undefined') {
	console = {
		log: function() { return null; }
	};
}

if (typeof(Number.prototype.toRad) === 'undefined') {
	Number.prototype.toRad = function() {
		return this * Math.PI / 180;
	}
}

var History = window.History;

History.Adapter.bind(window,'statechange',function() {
	var State = History.getState();
	History.log(State.data, State.title, State.url);
	if (!App.config) return;
	if (App._init) {
		App.loadPage();
	}
});

if (!typeof(App) == 'undefined') {
	App = {};
}

App.request = function(url, complete) {
	$.getJSON(url,function(json) {
		complete(json);
	});
};

App.cache = function(type, id) {
	var finalid, args = arguments, complete, partComplete;

	complete = args[2] ? args[2] : function() {};

	partComplete = function() {
		if (this.uuid) {
			App.cached[type][id.uuid] = this;
			App.cached[type][id] = this;
		}
		if (this.permalink) {
			App.cached[type][id.permalink] = this;
			App.cached[type][id] = this;
		}
		complete.call(this);
	}

	if (typeof(id) == 'object') {
		//App.cached[type][id.id] = id;

		eval('App.cached[type][id.id] = new '+type+'(id,partComplete)');
		finalid = id.id;

	} else if (!App.cached[type][id]) {
		eval('App.cached[type][id] = new '+type+'(id,partComplete)');

	} else {
		complete.call(App.cached[type][id]);
	}

	// only works sync (Ti)
	return App.cached[type][finalid || id];

};