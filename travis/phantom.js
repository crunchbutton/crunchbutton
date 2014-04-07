var page = require('webpage').create();
page.open('http://localhost', function(status) {
	var title = page.evaluate(function() {
		return document.body.innerText;
	});
	console.log('>> BODY ' + title);
	phantom.exit();
});