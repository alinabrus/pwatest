App
.controller('HomeCtrl', 
['$scope', '$window', '$log', '$state', '$rootScope', '$parse', '$interval', 'SETTINGS', 'SessionService',
function ($scope, $window, $log, $state, $rootScope, $parse, $interval, SETTINGS, SessionService) {
	
	$scope.filterService = 'fda50693-a4e2-4fb1-afcf-c6eb07647825'; //'0x1819'; //location and navigation
	$scope.log = '';
	
	$scope.bleConnectorTest = function (filterService) {
		  
		  let options = {filters: []};

		  //let filterService = '';
		  if (filterService.startsWith('0x')) {
		    filterService = parseInt(filterService, 16);
		  }
		  if (filterService) {
		    //options.filters.push({services: [filterService]});
		    options.filters.push({services: [parseInt('0x10002', 16), parseInt('0x8479', 16),'fda50693-a4e2-4fb1-afcf-c6eb07647825']});
		  }
		  /*
		  let filterName = '';//document.getElementById('name').value;
		  if (filterName) {
		    options.filters.push({name: filterName});
		  }

		  let filterNamePrefix = document.getElementById('namePrefix').value;
		  if (filterNamePrefix) {
		    options.filters.push({namePrefix: filterNamePrefix});
		  }
		  */
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
		    return device.connectGATT();
		  })
		  .catch(error => {
			  $log.debug('Argh! ' + error);
			  $scope.log += '</br> Argh! ' + error;
		  });
		};
		
		$scope.bleConnectorTestActivate = function() 
		{
			//var el = angular.element(event.target);
			var el = angular.element('#testBtn');
			el.on('click', 
			function(event) {
			    event.stopPropagation();
			    event.preventDefault();
			    /*
			    if (isWebBluetoothEnabled()) {
			      onFormSubmit();
			    }
			    */
			    if ('bluetooth' in navigator) {
					//$interval(function(){
						$scope.log += '</br> + ';
						$scope.bleConnectorTest($scope.filterService);
					//}, 1000);
					//$scope.bleConnectorTest($scope.filterService);
			    }
				else {
					$log.error('Web Bluetooth is not enabled! Check chrome://flags/#enable-web-bluetooth');
					$scope.log += ' Web Bluetooth is not enabled! Check chrome://flags/#enable-web-bluetooth';
					//$scope.log = $sce.trustAsHtml('</br> Web Bluetooth is not enabled! Check <a href="chrome://flags/#enable-web-bluetooth">here</a>');
					$interval(function(){
						$scope.log += '</br> - ';
					}, 1000);
				}
			});
			
		};
		//$scope.bleConnectorTestActivate();
				
}]);