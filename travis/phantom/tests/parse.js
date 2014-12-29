var page = require('webpage').create();

describe('Page', function() {
	
	var content = false;

	beforeEach(function() {
		page.open(phantom.url, function(status) {
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
		
		waitsFor(function() {
			return content;
		});

	    runs(function() {
			expect(content.match(/facebook-jssdk/i)).toBeTruthy();
	    });
    });
    
    it('should be javascript bundled', function() {
		
		waitsFor(function() {
			return content;
		});

	    runs(function() {
		    console.log(content);
			expect(content.match(/bundle.js/i)).toBeTruthy();
	    });
    });
    
    it('should be css bundled', function() {
		
		waitsFor(function() {
			return content;
		});

	    runs(function() {
			expect(content.match(/bundle.css/i)).toBeTruthy();
	    });
    });
    
    it('should contain App object', function() {
	    
	    var done = false;
		
		waitsFor(function() {
			return content;
		});

	    runs(function() {
			var service = page.evaluate(function() {
				return App.service;
			});
			expect(service).toBeTruthy();
			done = true;
	    });
	    
		waitsFor(function() {
			return done;
		});
    });
    
    it('should contain an Angular rootscope object', function() {
	    
	    var done = false;
		
		waitsFor(function() {
			return content;
		});

	    runs(function() {
			var service = page.evaluate(function() {
				return App.rootScope.$id;
			});
			expect(service).toBeTruthy();
			done = true;
	    });
	    
		waitsFor(function() {
			return done;
		});
    });
 });
