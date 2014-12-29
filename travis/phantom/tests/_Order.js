var page = require('webpage').create();
var url = phantom.url + 'food-delivery/some-test-restaurant';

describe('Page', function() {
	
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

    it('should match something', function() {
		
		waitsFor(function() {
			return content;
		});

	    runs(function() {
			expect(content.match(/SOMETHING/i)).toBeTruthy();
	    });
    });
        
    it('should contain some js object', function() {
	    
	    var done = false;
		
		waitsFor(function() {
			return content;
		});

	    runs(function() {
			var service = page.evaluate(function() {
				return SOMEVARIABLE;
			});
			expect(service).toBeTruthy();
			done = true;
	    });
	    
		waitsFor(function() {
			return done;
		});
    });

 });
