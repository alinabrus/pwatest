App
.factory('AdminStatService', 
function($http, $log, $window, $q, $timeout, $parse, SETTINGS){
		
		var adminStatService = {};
		
		adminStatService.getTotalRegStat = function (params) {
			
			if ( ! params) params = {by_account_type: false, start_date: null, end_date: null, period: 'M'};
			
			var deferred = $q.defer();
				
			$http.post(SETTINGS.apiUrlRegistrationStat, params)
		      .success(function(response, status, headers, config) {
		    	 // $log.debug('getTotalRegStat: ', response); 
		    	  deferred.resolve(response);
			  })
			  .error(function(response, status, headers, config) {
				  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		adminStatService.getCurrentRegStat = function (period) {
			
			if ( ! period) period = 7; 

			var now = new Date();
			var periodAgo = new Date(now.getTime() - (60*60*24*period*1000));
			periodAgo = periodAgo.toISOString().slice(0, 10).replace('T', ' ');
			$log.debug('weekAgo: ', periodAgo);
				
			params = {by_account_type: true, start_date: periodAgo, end_date: null, period: 'W'};
				
			var deferred = $q.defer();
				
			$http.post(SETTINGS.apiUrlRegistrationStat, params)
		      .success(function(response, status, headers, config) {
		    	 // $log.debug('getCurrentRegStat: ', response); 
		    	  
		    	  deferred.resolve(response);
			  })
			  .error(function(response, status, headers, config) {
				  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		adminStatService.getDonationsStat = function (params) {
			
			if ( ! params) params = {startDate: null, endDate: null, period: 'M'};
			
			var deferred = $q.defer();
				
			$http.post(SETTINGS.apiUrlDonationStat, params)
		      .success(function(response, status, headers, config) {
		    	  //$log.debug('getDonationsStat: ', response); 
		    	  deferred.resolve(response);
			  })
			  .error(function(response, status, headers, config) {
				  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		return adminStatService;
});
