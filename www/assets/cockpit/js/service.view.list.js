
NGApp.factory('ViewListService', function($location, $timeout) {

	var service = {};

	service.view = function(params) {
		var scope = params.scope;

		var query = $location.search();

		scope.query = {
			limit: query.limit || (App.isMobile() ? 5 : 20),
			page: query.page || 1
		};
		scope.query.page = parseInt(scope.query.page);

		var watch = function() {
			$location.search(scope.query);
			update();
		};

		// @todo: this breaks linking to pages
		var inputWatch = function() {
			if (scope.query.page != 1) {
				scope.query.page = 1;
			} else {
				watch();
			}
		};

		scope.watch = function(vars) {
			for (var x in vars) {
				scope.query[x] = query[x] || vars[x];
				scope.$watch('query.' + x, inputWatch);
			}
		};

		scope.count = 0;
		scope.pages = 0;

		scope.$watch('query.limit', inputWatch);
		scope.$watch('query.page', watch);

		scope.setPage = function(page) {
			scope.query.page = page;
			App.scrollTop(0);
		};

		scope.sort = function(by) {
			if (scope.query.sort == by) {
				scope.query.sort = '-' + by;
			} else {
				scope.query.sort = by;
			}
		};

		var updater = function(){};

		scope.update = function(fn) {
			if (fn) {
				updater = fn;
			} else {
				update();
			}
		};


		scope.loader = false;

		scope.complete = function(d) {
			scope.count = d.count;
			scope.pages = d.pages;

			if (scope.loader) {
				clearTimeout(scope.loader);
				scope.loader = setTimeout(function() {
					scope.scope.$apply(function() {
						scope.loading = false;
					});
				},100);
			} else {
				scope.loading = false;
			}
		};

		var update = function() {
			scope.loading = true;
			updater();
		};

		scope.focus('#search');

		scope.watch(params.watch);
		scope.update(params.update);
	}

	return service;
});
