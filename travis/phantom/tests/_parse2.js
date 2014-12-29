

var assert = require('chai').assert;
var page = require('webpage').create();
var url = phantom.libraryPath.match(/arzynik/i) ? 'http://crunchbutton.localhost/' : 'http://localhost/';

/*

page.open(url, function(status) {
	//console.log(page.content);
	//assert.equals(page.content.match(/\<\/html\>/i) ? true : false, true);
	assert.equal(1, 1, 'equal');
		console.log('B');
	//phantom.exit(0);
	return;
});
*/


describe("Page output", function() {
	
	beforeEach
	
	var content = false;

    it("should finish rendering", function() {
	    
		var done = false;
	    runs(function(){
			console.log(url);
			page.open(url, function(status) {
				
				var onPageReady = function() {
					expect(page.content.match(/facebook-jssdk/i)).toBeTruthy();
					done = true;
				};
				
				function checkReadyState() {
			        setTimeout(function () {
			            var readyState = page.evaluate(function () {
			                return document.readyState;
			            });
			
			            if ("complete" === readyState) {
			                onPageReady();
			            } else {
			                checkReadyState();
			            }
			        });
			    }
			
			    checkReadyState();

			});
	    });

		waitsFor(function(){
			return done;
		});
		
    });
 });
