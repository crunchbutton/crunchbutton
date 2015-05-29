/*
	Jasmin 2.0+
*/

var assert = require('chai').assert;

describe('Page', function() {
	var content = phantom.content;
	beforeEach(function (done) {
		content = phantom.content;
		done();
	});

    it('should finish rendering', function() {
		//assert.equals(page.content.match(/\<\/html\>/i) ? true : false, true);
		expect(content.match(/facebook-jssdk/i)).toBeTruthy();
    });

    it('should be javascript bundled', function() {
		expect(content.match(/bundle.js/i)).toBeTruthy();
    });
    
    it('should be css bundled', function() {
		expect(content.match(/bundle.css/i)).toBeTruthy();
    });
    
    it('should contain App object', function(done) {
		var service = page.evaluate(function() {
			return App.service;
		});
		expect(service).toBeTruthy();
		done();
    });
    
    it('should contain an Angular rootscope object', function(done) {
		var service = page.evaluate(function() {
			return App.rootScope.$id;
		});
		expect(service).toBeTruthy();
		done();
    });
 });