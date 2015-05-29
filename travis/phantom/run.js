var fs = require('fs');
var sys = require('system');

fs.changeWorkingDirectory(phantom.libraryPath);

phantom.injectJs('./lib/jasmine.js');

var jasmine = window.jasmine = jasmineRequire.core(jasmineRequire);

phantom.injectJs('./lib/jasmine-console.js');

phantom.url = phantom.libraryPath.match(/arzynik/i) ? 'http://crunchbutton.localhost/' : 'http://localhost/';



var jasmineEnv = jasmine.getEnv();


var describe = jasmineEnv.describe;
var beforeEach = jasmineEnv.beforeEach;
var it = jasmineEnv.it;
var expect = jasmineEnv.expect;
var waitsFor = jasmineEnv.waitsFor;
var runs = jasmineEnv.runs;

/*
jasmineEnv.addReporter(new jasmine.ConsoleReporter(
    function(e) {
	    console.log(e);
	},
    function (reporter) {
        phantom.exit(reporter.results().failedCount > 0 ? 1 : 0);
    },
    true, true
));
*/
var ConsoleReporter = jasmineRequire.ConsoleReporter();
var options = {
	timer: new jasmine.Timer, 
	showColors: true,
	onComplete: function(success) {
		phantom.exit(success ? 0 : 1);
	},
	print: function (e) {
		console.log(e);
		//console.log.apply(console,arguments)
	}
};
consoleReporter = new ConsoleReporter(options); // initialize ConsoleReporter
jasmine.getEnv().addReporter(consoleReporter); //add reporter to execution environment

var tests = './tests/';
var list = fs.list(tests);

for (var x = 0; x < list.length; x++){
	if (!fs.isDirectory(list[x]) && list[x].charAt(0) != '_') {
		console.log('Reading file: ' + tests + list[x]);
		phantom.injectJs(tests + list[x]);
		//require(tests + list[x]);
	}       
}

jasmineEnv.updateInterval = 1000;



var page = require('webpage').create();
page.settings.webSecurityEnabled = false;

page.onResourceError = function(resourceError) {
  console.log('Unable to load resource (#' + resourceError.id + 'URL:' + resourceError.url + ')');
  console.log('Error code: ' + resourceError.errorCode + '. Description: ' + resourceError.errorString);
};


console.log('Reading ' + phantom.url);

page.open(phantom.url, function(status) {
	phantom.content = page.content;
	jasmineEnv.execute();
});








