App
.config(function(SETTINGS, $authProvider) {
	$authProvider.yahoo({
		clientId: SETTINGS.yahooClientId,
		url: '/api/yahoo/auth'
    });
})
.factory('YahooService', function($http, $log, $window, $q, $timeout, $auth, SETTINGS){
		var yahooService = {};
		
		yahooService.getContacts = function () {
			
			var deferred = $q.defer();
			
			$timeout(function() {
				var provider = 'yahoo';
				$auth.authenticate(provider)
		        .then(function(response) {
		        	$log.debug('You have successfully signed in with ' + provider);
		        	//$log.debug('$auth.authenticate response: ', response);
		        	//$log.debug('YahooService $auth.authenticate: response.data.contacts = ', response.data.contacts);
		        	deferred.resolve({result: response.data.contacts, error: null});
		        })
		        .catch(function(response) {
		          $log.error(provider, ' $auth.authenticate: ', response);
		          deferred.resolve({result: null, error: 'yahooService.getContacts error occured'});
		        });
			});
			
			return deferred.promise;
		};
		
		return yahooService;
});
