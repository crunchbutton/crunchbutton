var fs = require('fs');
var sys = require('system');

fs.changeWorkingDirectory(phantom.libraryPath);

phantom.injectJs('./lib/jasmine.js');
phantom.injectJs('./lib/jasmine-console.js');

phantom.url = phantom.libraryPath.match(/arzynik/i) ? 'http://crunchbutton.localhost/' : 'http://localhost/';

var jasmineEnv = jasmine.getEnv();

jasmineEnv.addReporter(new jasmine.ConsoleReporter(
    function(e) {
	    console.log(e);
	},
    function (reporter) {
        phantom.exit(reporter.results().failedCount > 0 ? 1 : 0);
    },
    true, true
));

var tests = './tests/';
var list = fs.list(tests);

for (var x = 0; x < list.length; x++){
	if (!fs.isDirectory(list[x]) && list[x].charAt(0) != '_') {
		console.log('Reading file: ' + tests + list[x]);
		//phantom.injectJs(tests + list[x]);
		require(tests + list[x]);
	}       
}

jasmineEnv.updateInterval = 1000;
jasmineEnv.execute();