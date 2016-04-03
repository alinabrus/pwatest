App
.controller('HomeCtrl', 
['$scope', '$window', '$log', '$state', '$rootScope', '$parse', '$interval', 'SETTINGS', 'SessionService',
function ($scope, $window, $log, $state, $rootScope, $parse, $interval, SETTINGS, SessionService) {
	
	$scope.filterService = ''; //'0x180A';
	$scope.log = '';
	
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
			  
			  $scope.log += '</br> > Name:             ' + device.name;
			  $scope.log += '</br> > Id:               ' + device.id;
		  	  $scope.log += '</br> > Device Class:     ' + device.deviceClass;
			  $scope.log += '</br> > Vendor Id Source: ' + device.vendorIDSource;
			  $scope.log += '</br> > Vendor Id:        ' + device.vendorID;
			  $scope.log += '</br> > Product Id:       ' + device.productID;
			  $scope.log += '</br> > Product Version:  ' + device.productVersion;
			  $scope.log += '</br> > UUIDs:            ' + device.uuids.join('\n' + ' '.repeat(20));
		    if (device.adData) {
		    	$log.debug('> Tx Power:         ' + device.adData.txPower + ' dBm');
		    	$log.debug('> RSSI:             ' + device.adData.rssi + ' dBm');
		    	
		    	$scope.log += '</br> > Tx Power:         ' + device.adData.txPower + ' dBm';
				$scope.log += '</br> > RSSI:             ' + device.adData.rssi + ' dBm';
		    }
		  })
		  .catch(error => {
			  $log.debug('Argh! ' + error);
			  $scope.log += '</br> Argh! ' + error;
		  });
		};
		
		
		if ('bluetooth' in navigator) {
			$interval(function(){
				$scope.log += '</br> + ';
				$scope.bleConnectorTest($scope.filterService);
			}, 200);
			//$scope.bleConnectorTest($scope.filterService);
	    }
		else {
			$log.error('Web Bluetooth is not enabled! Check chrome://flags/#enable-web-bluetooth');
			$scope.log += ' Web Bluetooth is not enabled! Check chrome://flags/#enable-web-bluetooth';
			//$scope.log = $sce.trustAsHtml('</br> Web Bluetooth is not enabled! Check <a href="chrome://flags/#enable-web-bluetooth">here</a>');
			$interval(function(){
				$scope.log += '</br> - ';
			}, 200);
		}
		
				
}]);