

var page = require('webpage').create();

var url = phantom.libraryPath.match(/arzynik/i) ? 'http://crunchbutton.localhost/' : 'http://localhost/';
console.log(url);

page.open(url, function(status) {

/*
	var title = page.evaluate(function() {
		return document.body.innerText;
	});
	console.log('>> BODY >> ' + title);
*/

	//console.log('>> CONTENT >> ' + page.content);
	console.log('A');
	//phantom.exit(0);
	return;
});
