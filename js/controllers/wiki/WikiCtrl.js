App
.controller('WikiCtrl', 
['$scope', '$window', '$log', '$state', '$rootScope', '$parse', 'SETTINGS', 'SessionService', 'WikiService', 
function ($scope, $window, $log, $state, $rootScope, $parse, SETTINGS, SessionService, WikiService) {
		
	$log.debug('----------------------- WikiCtrl ', $scope.state);
	$log.debug(' WikiService.rawData.item = ', WikiService.rawData.item);
	
}]);