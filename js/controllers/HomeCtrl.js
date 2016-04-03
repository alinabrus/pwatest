App
.controller('HomeCtrl', 
['$scope', '$window', '$log', '$state', '$rootScope', '$parse', 'SETTINGS', 'SessionService',
function ($scope, $window, $log, $state, $rootScope, $parse, SETTINGS, SessionService) {
	
	$scope.filterService = '0x180A';
	
	$scope.bleConnectorTest = function (filterService) {
		  
		  let options = {filters: []};

		  //let filterService = '';
		  if (filterService.startsWith('0x')) {
		    filterService = parseInt(filterService, 16);
		  }
		  if (filterService) {
		    options.filters.push({services: [filterService]});
		  }
		  
		  $log.debug('Requesting Bluetooth Device...');
		  navigator.bluetooth.requestDevice(options)
		  .then(device => {
			  $log.debug('> Name:             ' + device.name);
			  $log.debug('> Id:               ' + device.id);
			  $log.debug('> Device Class:     ' + device.deviceClass);
			  $log.debug('> Vendor Id Source: ' + device.vendorIDSource);
			  $log.debug('> Vendor Id:        ' + device.vendorID);
			  $log.debug('> Product Id:       ' + device.productID);
			  $log.debug('> Product Version:  ' + device.productVersion);
			  $log.debug('> UUIDs:            ' + device.uuids.join('\n' + ' '.repeat(20)));
		    if (device.adData) {
		    	$log.debug('> Tx Power:         ' + device.adData.txPower + ' dBm');
		    	$log.debug('> RSSI:             ' + device.adData.rssi + ' dBm');
		    }
		  })
		  .catch(error => {
			  $log.debug('Argh! ' + error);
		  });
		};
		
		
		if ('bluetooth' in navigator) {
			$scope.bleConnectorTest($scope.filterService);
	    }
		else {
			$log.error('Web Bluetooth is not enabled! Check chrome://flags/#enable-web-bluetooth');
		}
		
				
}]);