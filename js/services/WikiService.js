App
.factory('WikiService', function($http, $log, $window, $q, $timeout, SETTINGS){
		
		var wikiService = {};
		
		wikiService.debug = {
								on: true,
								validate: true,
								testinfo: true
							};
		
		wikiService.item = {};
		wikiService.rawData = {};
		wikiService.rawData.item = {};
		
		wikiService.init = function () {
			
			wikiService.item = {};
			wikiService.rawData.item = {};
			
			if (wikiService.debug.testinfo) 
			{
				wikiService.rawData.item = {
					name: 'Test 1',
					description: 'Test 1 description'
				};
				
			}
		};
		wikiService.init();
		
		return wikiService;
});
