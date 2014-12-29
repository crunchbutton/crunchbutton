/* this is a partial example if we ever decide to use Jasmin 2.0+ */

var assert = require('chai').assert;
var page = require('webpage').create();
var url = phantom.libraryPath.match(/arzynik/i) ? 'http://crunchbutton.localhost/' : 'http://localhost/';

describe("Page output", function() {
	
	var content;
	
	beforeEach(function (done) {
		page.open(url, function(status) {
			console.log(page.content);
			content = page.content;
			done();
		});
	});

    it("should have a closing html tag", function() {

		console.log(page.content);
		//assert.equals(page.content.match(/\<\/html\>/i) ? true : false, true);
		expect('asd').toBe(false);
		
    });
 });
