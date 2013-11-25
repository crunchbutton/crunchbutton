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
		startSeconds = 0,
		ms = 0,
		level = 1,
		currentXp = 0;
	
	// get the item by key value
	var getItem = function(key) {
		for (var x in $scope.allitems) {
			if ($scope.allitems[x].id == key) {
				return $scope.allitems[x];
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
			showMessage('You suck! Wait 1 second!','error', 1000);
			setTimeout(function() {
				enabled = true;
			},1000);
		}
	};
	
	// show a message for a limited period of time
	var showMessage = function(message, type, time) {
		clearTimeout(messageTimeout);
		
		if ($scope.$$phase) {
			$scope.message = {};
			$scope.message[type] = message;
		} else {
			$scope.$apply(function($scope) {
				$scope.message = {};
				$scope.message[type] = message;
			});
		}

		messageTimeout = setTimeout(function() {
			$scope.$apply(function() {
				$scope.message = null;
			});
		},time || 2000);
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
	var lose = function(mseconds) {
		ms = mseconds;
		App.playAudio('cafe-lose');
		$scope.stop();

		if ($scope.$$phase) {
			$scope.message = {error: 'You lose.'};

		} else {
			$scope.$apply(function($scope) {
				$scope.message = {error: 'You lose.'};
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
			lose(diffMs);
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

		var score = Math.round((((timeBonus - penalty + goals) * rounds[round].scoreMultiplier) / 10) * $scope.difficulty);

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
			name: 'Training',
			time: 20000,
			required: 3,
			descriptions: true,
			scoreMultiplier: .2
		},
		{
			name: '2',
			time: 20000,
			required: 5,
			descriptions: true,
			scoreMultiplier: 1.5
		},
		{
			name: '2',
			time: 20000,
			required: 7,
			descriptions: true,
			scoreMultiplier: 1.7
		},
		{
			name: '3',
			time: 20000,
			required: 10,
			descriptions: false,
			scoreMultiplier: 2
		},
		{
			name: '4',
			time: 20000,
			required: 13,
			descriptions: false,
			scoreMultiplier: 2.3
		},
		{
			name: '5',
			time: 20000,
			required: 15,
			descriptions: false,
			scoreMultiplier: 5
		},
		{
			name: '6',
			time: 20000,
			required: 17,
			descriptions: false,
			scoreMultiplier: 2.7
		},
		{
			name: '7',
			time: 20000,
			required: 18,
			descriptions: false,
			scoreMultiplier: 2.8
		},
		{
			name: '8',
			time: 20000,
			required: 19,
			descriptions: false,
			scoreMultiplier: 5.0
		},
		{
			name: '9',
			time: 20000,
			required: 20,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '10',
			time: 20000,
			required: 21,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '10',
			time: 20000,
			required: 22,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '11',
			time: 20000,
			required: 23,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '12',
			time: 20000,
			required: 24,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '13',
			time: 20000,
			required: 25,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '14',
			time: 20000,
			required: 26,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '14',
			time: 20000,
			required: 27,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '15',
			time: 20000,
			required: 28,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: '16',
			time: 20000,
			required: 29,
			descriptions: false,
			scoreMultiplier: 100
		},
		{
			name: 'THE END',
			time: 20000,
			required: 30,
			descriptions: false,
			scoreMultiplier: 100
		}
	];

	// array of posible items
	$scope.allitems = [
		{
			name: 'Wenzel',
			id: 'wenzel'
		},
		{
			name: 'Spicy With',
			id: 'spicywith'
		},
		{
			name: 'Pizza',
			id: 'pizza'
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
			name: 'Spicy Tuna Roll',
			id: 'spicytunaroll'
		},
		{
			name: 'Steak Sandwich',
			id: 'steaksandwich'
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
		
		$scope.stats.win = win;
		$scope.stats.score = $scope.score;

		ms = 0;
		startSeconds = 0;
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
			// they beat this difficulty
			App.alert('I think you beat it...');
			return;
		}
		$scope.home = false;

		if ($scope.difficulties[$scope.difficulty].random) {
			var cloned = $scope.allitems.slice(0);
			$scope.items = $.pluck(cloned,$scope.difficulties[$scope.difficulty].items);
		} else {
			$scope.items = $scope.allitems.slice(0, $scope.difficulties[$scope.difficulty].items);
		}
		$scope.message = {good: 'Starting in 3!'};

		$scope.stats = {
			timer: '00:00',
			round: rounds[round],
			errors: 0,
			success: 0
		};
		
		startSeconds = 0;

		startTimer = setInterval(function() {
			startSeconds++;
			if (startSeconds == 3) {
				clearInterval(startTimer);
				App.playAudio('cafe-start');
				gameStart = new Date();
				timer = setInterval(function() {
					updateTimer();
				},10);
			
				showMessage('Go!!','good');	
				
				requestPress();
				enabled = true;
			} else {
				showMessage('Starting in ' + (3 - startSeconds) + '!','good');	
			}
		},1000);
		$scope.running = true;
	};
	
	$scope.difficulty = '0';
	$scope.timer = '00:00';
	
	$scope.home = true;
	
	$scope.difficulties = [
		{
			name: 'Easy',
			items: 4,
			random: false
		},
		{
			name: 'Medium',
			items: 6,
			random: false
		},
		{
			name: 'Hard',
			items: 6,
			random: true
		}
	];
});