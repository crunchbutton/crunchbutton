


NGApp.factory('NotificationService', function($http) {

	var service = {};
	
	var ms = 5000;

	service.check = function(fn) {
		if (!window.Notification || App.isCordova) {
			return;
		}

		Notification.requestPermission(function (status) {
			if (Notification.permission !== status) {
				Notification.permission = status;
			}
			if (!fn) {
				return;
			}
			if (Notification.permission === 'granted') {
				fn(true);
			} else {
				fn(false);
			}
		});
	};
	
	service.notify = function(title, body, icon, fn) {
		if (!window.Notification || App.isCordova || document.hasFocus()) {
			return;
		}
		
		icon = icon || '/assets/cockpit/images/notification.png';
		
		service.check(function() {
			var en = new Notification(title, { 
				body: body,
				icon: icon
			});
			
			setTimeout(function() {
				en.close();
			}, ms);
	
			en.onclick = function() {
				window.focus();
				if (fn) {
					fn();
				}
			}
		});
	};

	return service;
});
