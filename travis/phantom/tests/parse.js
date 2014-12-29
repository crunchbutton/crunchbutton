var page = require('webpage').create();
var url = phantom.url;

describe('Page output', function() {
	
	var content = false;

	beforeEach(function() {
		page.open(url, function(status) {
			var onPageReady = function() {
				content = page.content;
			};
			
			function checkReadyState() {
		        setTimeout(function() {
		            var readyState = page.evaluate(function() {
		                return document.readyState;
		            });
		
		            if (readyState === 'complete') {
		                onPageReady();
		            } else {
		                checkReadyState();
		            }
		        });
		    }
		
		    checkReadyState();
		});
	});

    it('should finish rendering', function() {
	    
		var done = false;
		
		waitsFor(function(){
			return content;
		});

	    runs(function(){
			expect(content.match(/facebook-jssdk/i)).toBeTruthy();
	    });
    });
 });
