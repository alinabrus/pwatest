App
	.factory('TeleSignService', function($http, $log, $window, $q, $timeout, SessionService, FileUploadService, SETTINGS){
		var teleSignService = {};
		
		teleSignService.request = function (url, params) {
			
			var deferred = $q.defer();
				
			$http.post(url, params)
		      .success(function(response, status, headers, config) {
		    	  deferred.resolve(response);
			  })
			  .error(function(response, status, headers, config) {
				  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		teleSignService.smartVerify = function (phoneNumber, verifyMethod) {
			var params = {phone_number: phoneNumber, verify_method: verifyMethod};
			return teleSignService.request(SETTINGS.apiUrlPhoneVerify, params);
		};
		
		teleSignService.checkVerifyCode = function (referenceId, verifyCode) {
			var params = {reference_id: referenceId, verify_code: verifyCode};
			return teleSignService.request(SETTINGS.apiUrlPhoneStatus, params);
		};
		
		return teleSignService;
});
