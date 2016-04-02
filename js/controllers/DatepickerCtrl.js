App.controller('DatepickerCtrl', function ($scope, $log, $window) {
  
  
  $scope.today = function() {
    $scope.dt = new Date();
  };
  //$scope.today();
  	
  $scope.clear = function () {
    $scope.dt = null;
  };

  // Disable weekend selection
  $scope.disabled = function(date, mode) {
    return false; //( mode === 'day' && ( date.getDay() === 0 || date.getDay() === 6 ) );
  };
  
  $scope.toggleMin = function() {
    //$scope.minDate = $scope.minDate ? null : new Date();
    $scope.minDate = $scope.minDate ? $scope.minDate : new Date();
  };
  $scope.toggleMin();
  
  $scope.toggleMax = function() {
	  $scope.maxDate = new Date();
	  $scope.maxDate.setFullYear($scope.maxDate.getFullYear() + 2);
  };
  $scope.toggleMax();
  
  $scope.minNow = function() {
	$scope.minDate = new Date();
    //$log.debug('minNow: $scope.minDate = ', $scope.minDate);
  };
  
  $scope.minPast = function() {
	  $scope.minDate = new Date();
	  $scope.minDate.setFullYear($scope.minDate.getFullYear() - 2);
	  //$log.debug('minPast: $scope.minDate = ', $scope.minDate);
  };
  
  $scope.open = function($event) {
    $event.preventDefault();
    $event.stopPropagation();

    $scope.opened = true;
    
    //$log.debug('open: $scope.minDate = ', $scope.minDate);
    //$log.debug('open: $scope.maxDate = ', $scope.maxDate);
  };
  $scope.locale = $window.navigator.userLanguage || $window.navigator.language;
  $log.debug('$scope.locale = ', $scope.locale);
  
  $scope.startOfWeekDay = moment().locale($scope.locale).startOf('week').day();    
  
  $scope.dateOptions = {
    //formatYear: 'yy',
    startingDay: $scope.startOfWeekDay
  };

  //$scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
  //$scope.format = $scope.formats[0];
  
  //$log.debug('$scope.minDate = ', $scope.minDate);
  //$log.debug('$scope.maxDate = ', $scope.maxDate);
  
});