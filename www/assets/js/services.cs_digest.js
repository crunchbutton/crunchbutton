/**
 * Created by mmeyers on 3/4/15.
 */

NGApp.factory('CSDigestService', function($resource) {

    var service = {};

    var feedback = $resource(App.service + 'digest/cs', {}, {

        'save' : {
            url: App.service + 'digest/cs',
            method: 'POST',
            params : {}
        }

    });

    service.post = function(params, callback) {
        feedback.save(params, function(data) {
            console.log(params);
            callback(data);
        });
    }

    return service;

});