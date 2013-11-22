/**
 * cafe mini game controller
 */
 
NGApp.controller('CafeCtrl', function ($scope, $http) {
	var
		gameStart = null,
		timer = null,
		messageTimeout = null,
		enabled = false,
		round = 0,
		startTimer = null,
		ms = 0,
		level = 1,
		currentXp = 0;
	
	// get the item by key value
	var getItem = function(key) {
		for (var x in items) {
			if (items[x].id == key) {
				return items[x];
			}
		}
		return null;
	};

	// handles presses on the buttons. triggers error or success
	$scope.buttonPress = function(id) {
		if (!$scope.requested || !enabled) {
			return;
		}

		var clicked = getItem(id);

		if ($scope.requested.id == clicked.id) {
			$scope.stats.success++;
			if ($scope.stats.success >= rounds[round].required) {
				win();
				return;
			}
			App.playAudio('cafe-good');
			showMessage('Yay. Keep going!','good');	
			requestPress();

		} else {
			enabled = false;
			$scope.stats.errors++;
			App.playAudio('cafe-fail');
			showMessage('You suck! Wait 2 seconds!','error');
			setTimeout(function() {
				enabled = true;
			},2000);
		}
	};
	
	// show a message for a limited period of time
	var showMessage = function(message, type) {
		clearTimeout(messageTimeout);
		$scope.message = {};
		$scope.message[type] = message;

		messageTimeout = setTimeout(function() {
			$scope.$apply(function() {
				$scope.message = null;
			});
		},2000);
	};
	
	// pluck a new item out of the array of 6 for the user to press
	var requestPress = function() {
		var cloned = $scope.items.slice(0);

		if (!$scope.requested) {
			var newrequest = $.pluck(cloned,1);
			newrequest = newrequest[0];
		} else {
			var previous = newrequest = $scope.requested;
			while (newrequest.id == previous.id) {
				newrequest = $.pluck(cloned,1);
				newrequest = newrequest[0];
			}
		}

		if ($scope.$$phase) {
			$scope.requested = newrequest;
		} else {
			$scope.$apply(function($scope) {
				$scope.requested = newrequest;
			});
		}
	};
	
	// triggered when the time runs out
	var loose = function(mseconds) {
		ms = mseconds;
		App.playAudio('cafe-loose');
		$scope.stop();

		if ($scope.$$phase) {
			$scope.message = {error: 'You loose.'};

		} else {
			$scope.$apply(function($scope) {
				$scope.message = {error: 'You loose.'};
			});
		}
	};
	
	// triggered when everything is presed
	var win = function() {
		var now = new Date;
		ms = now.getTime() - gameStart.getTime();
		App.playAudio('cafe-win');
		$scope.stop(true);

		if ($scope.$$phase) {
			$scope.message = {good: 'You win! Try the next round!'};

		} else {
			$scope.$apply(function($scope) {
				$scope.message = {good: 'You win! Try the next round!'};
			});
		}
	};
	
	// interva that updates the stopwatch
	var updateTimer = function() {
		var now = new Date;
		var diffMs = now.getTime() - gameStart.getTime();
		var diff = Math.round(diffMs / 10).pad(4);
		
		if (diffMs >= rounds[round].time) {
			loose(diffMs);
			return;
		}
		
		diff = (diff + ' ').slice(0,2) + ':' + (diff + ' ').slice(2,4);

		if ($scope.$$phase) {
			$scope.stats.timer = diff;
		} else {
			$scope.$apply(function($scope) {
				$scope.stats.timer = diff;
			});
		}
	};
	
	// create a score based on time, errors, and successes
	var createScore = function() {
		var timeBonus = ((rounds[round].time - ms) / 1000) * 20;
		var penalty = ($scope.stats.errors || 0) * 100;
		var goals = ($scope.stats.success || 0) * 1000;

		var score = Math.round(((timeBonus - penalty + goals) * rounds[round].scoreMultiplier) / 10);

		return score < 0 ? 0 : score;
	};
	
	// get the level based on curent levels xp
	var getLevel = function() {
		var levels = 40;
		var xp_for_first_level = 1000;
		var xp_for_last_level = 1000000;
		var B = Math.log(xp_for_last_level / xp_for_first_level) / (levels - 1);
		var A = xp_for_first_level / (Math.exp(B) - 1.0);

		for (var i = 1; i <= levels; i++) {
			var old_xp = Math.round(A * Math.exp(B * (i - 1)));
			var new_xp = Math.round(A * Math.exp(B * i));
			console.log(i,(new_xp - old_xp));
		}
	};

	

	// array of rounds in order
	var rounds = [
		{
			name: 'Round 1',
			time: 20000,
			required: 3,
			descriptions: true,
			scoreMultiplier: .2
		},
		{
			name: 'Round 2',
			time: 20000,
			required: 7,
			descriptions: true,
			scoreMultiplier: 1.1
		},
		{
			name: 'Round 3',
			time: 20000,
			required: 10,
			descriptions: false,
			scoreMultiplier: 1.3
		},
		{
			name: 'Round 4',
			time: 20000,
			required: 13,
			descriptions: false,
			scoreMultiplier: 1.5
		},
		{
			name: 'Round 5',
			time: 20000,
			required: 15,
			descriptions: false,
			scoreMultiplier: 1.7
		},
		{
			name: 'Round 6',
			time: 25000,
			required: 27,
			descriptions: false,
			scoreMultiplier: 2.0
		},
		{
			name: 'Round 7',
			time: 20000,
			required: 25,
			descriptions: false,
			scoreMultiplier: 5.0
		},
		{
			name: 'Round 8',
			time: 20000,
			required: 30,
			descriptions: false,
			scoreMultiplier: 5.0
		},
		{
			name: 'Round 9',
			time: 20000,
			required: 100,
			descriptions: false,
			scoreMultiplier: 100
		}
	];

	// array of posible items
	var items = [
		{
			name: 'Wenzel',
			id: 'wenzel'
		},
		{
			name: 'Spicy With',
			id: 'spicywith'
		},
		{
			name: 'All Meat Pizza',
			id: 'allmeatpizza'
		},
		{
			name: 'Mega Burger',
			id: 'megaburger'
		},
		{
			name: 'Curry Rice',
			id: 'curryrice'
		},
		{
			name: 'Steak Sandwich',
			id: 'steaksandwich'
		},
		{
			name: 'Spicy Tuna Roll',
			id: 'spicytunaroll'
		},
		{
			name: 'Nachos',
			id: 'nachos'
		},
		{
			name: 'Shrimp Tacos',
			id: 'shrimptacos'
		},
		{
			name: 'Orange Chicken',
			id: 'orangechicken'
		},
		{
			name: 'Chicken Parm Sandwich',
			id: 'chickenparmsandwich'
		},
		{
			name: 'Chicken Tikka Masala',
			id: 'chickentikkamasala'
		},
		{
			name: 'Pad Thai',
			id: 'padthai'
		},
		{
			name: 'Pancakes',
			id: 'pancakes'
		},
		{
			name: 'Boring Salad',
			id: 'boringsalad'
		}
	];

	// stop the game
	$scope.stop = function(win) {

		clearTimeout(startTimer);
		clearTimeout(messageTimeout);
		clearInterval(timer);
		
		$scope.score = createScore();
		
		App.log.game({
			user: App.config.user.id,
			game: 'cafe',
			score: $scope.score,
			round: rounds[round],
			level: level,
			time: ms,
			errors: $scope.stats.errors,
			success: $scope.stats.success
		});

		ms = 0;
		gameStart = null;
		$scope.message = null;
		$scope.requested = null;
		enabled = false;
		$scope.running = false;
		$scope.items = [];
		$scope.stats.time = (rounds[round].time / 1000) + ':00';

		if (win) {
			round++;
		}
	};

	// start the game
	$scope.start = function() {
		if (!rounds[round]) {
			App.alert('I think you beat it...');
			return;
		}
		$scope.items = $.pluck(items,6);
		$scope.message = {good: 'Starting in 3 seconds!'};

		$scope.stats = {
			timer: '00:00',
			round: rounds[round],
			errors: 0,
			success: 0
		};

		startTimer = setTimeout(function() {
			App.playAudio('cafe-start');
			gameStart = new Date();
			timer = setInterval(function() {
				updateTimer();
			},10);
		
			if ($scope.$$phase) {
				$scope.message = null;
			} else {
				$scope.$apply(function($scope) {
					$scope.message = null;
				});
			}
			
			requestPress();
			enabled = true;			
		},3000);
		$scope.running = true;
	};
	
	
	$scope.timer = '00:00';
	$scope.allitems = items;
});