// dev account alina.brus.11@gmail.com|ifgjrkzr
// prod account maxletics.app@gmail.com|YtpyfrjvtW44

App
	.factory('MailboxlayerService', function($http, $log, $window, $q, $timeout, SETTINGS){
		var mailboxlayerService = {};
		
		mailboxlayerService.apiUrl = 'https://apilayer.net/api/check';
		
		mailboxlayerService.request = function (params) {
			
			if ( ! params) params = {};
			params.access_key = SETTINGS.mailboxlayerApiKey;
			
			$log.debug('mailboxlayerService.request params = ', params);
			
			var deferred = $q.defer();
				
			$http.get(mailboxlayerService.apiUrl, {params: params})
		      .success(function(response, status, headers, config) {
		    	  deferred.resolve({response: response, status: status});
			  })
			  .error(function(response, status, headers, config) {
				  deferred.resolve({response: response, status: status});
			  });
			return deferred.promise;
		};
		
		mailboxlayerService.verify = function (email) {
			var params = {email: email};
			return mailboxlayerService.request(params);
		};
		
		return mailboxlayerService;
});
