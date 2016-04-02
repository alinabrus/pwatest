App.directive('esValidator', function($log, $q, $timeout) {
    return {
 
      restrict: 'A',
 
      // require NgModelController, i.e. require a controller of ngModel directive
      require: 'ngModel',
 
      link: function(scope, element, attrs, ctrl) {
    	  
    	  var DEBUG = false;
    	  
    	  if (DEBUG) $log.debug('-------------- mxValidator: element = ', element);
    	  if (DEBUG) $log.debug('-------------- mxValidator: attrs = ', attrs);
    	  if (DEBUG) $log.debug('-------------- mxValidator: ctrl = ', ctrl);
	  	
    	  var fn = scope.$eval(attrs.mxValidator);
    	  
    	  if (typeof fn != 'function') {
    		  $log.error('mxValidator Error: ', fn, 'is not a function.');
    		  return false;
    	  }
    	  
    	  var validEmpty = true;
    	  
    	  if (DEBUG) $log.debug('-------------- mxValidator: validEmpty = ', validEmpty);
    	  
    	  if (attrs.mxValidatorType && attrs.mxValidatorType == 'async') {
    		  ctrl.$asyncValidators.mxValidator = function(modelValue, viewValue) {
    			  
    			  if (DEBUG) $log.debug('-------------- mxValidator: async mode');
    			  
    			  if (ctrl.$isEmpty(modelValue) && validEmpty) {
    				  return $q.when();
    			  }
    			  var def = $q.defer();
    			  /*
    			  $timeout(function() {
    				  $log.debug('-------------- mxValidator: timeout');
			          if (validEmpty) {
			        	  if (!ctrl.$isEmpty(modelValue)) {
				            def.resolve();
				          } else {
				            def.reject();
				          }
			          }
    				  else 
    					  def.reject();
			          
    			  }, 2000);
    			  */
    			  fn(modelValue).then(function (result) {
    				  if (DEBUG) $log.debug('-------------- mxValidator: result = ', !!result.result);
    				  if (!!result.result) 
    					  def.resolve();
    				  else 
    					  def.reject();
    			  });
    			  
    			  return def.promise;
    		  };
    	  }
    	  else {
    		  ctrl.$validators.mxValidator = function(modelValue, viewValue) {
    			  var result = fn(modelValue);
    			  if (DEBUG) $log.debug('-------------- mxValidator: result = ', result);
    			  return !!result;
    		  };
    	  }
    	  
      }
    };
});

//////////////////////////////////////////////////////////////////////

App.directive('mxValidators', function($log, $q, $timeout) {
    return {
 
      restrict: 'A',
 
      // require NgModelController, i.e. require a controller of ngModel directive
      require: 'ngModel',
 
      link: function(scope, element, attrs, ctrl) {
    	  
    	  var DEBUG = false;
    	  
    	  if (DEBUG) $log.debug('-------------- mxValidator: element = ', element);
    	  if (DEBUG) $log.debug('-------------- mxValidator: attrs = ', attrs);
    	  if (DEBUG) $log.debug('-------------- mxValidator: ctrl = ', ctrl);
	  	
    	  var validators = scope.$eval(attrs.mxValidators);
    	  
    	  angular.forEach(validators, function(validator, key){
    		  
    		  if (DEBUG) $log.debug('-------------- mxValidator: validator = ', key, validator);
    		  
    		  var validatorType = validator.type;
    		  var fn = validator.fn;
    		  
    		  if (typeof fn != 'function') {
	    		  $log.error('mxValidator Error: ', fn, 'is not a function.');
	    		  return false;
	    	  }
	    	  
	    	  var validEmpty = true;
	    	  
	    	  if (DEBUG) $log.debug('-------------- mxValidator: validEmpty = ', validEmpty);
	    	  
	    	  if (validatorType == 'async') {
	    		  ctrl.$asyncValidators['mxValidator_' + key] = function(modelValue, viewValue) {
	    			  
	    			  if (DEBUG) $log.debug('-------------- mxValidator: async mode');
	    			  
	    			  if (ctrl.$isEmpty(modelValue) && validEmpty) {
	    				  return $q.when();
	    			  }
	    			  var def = $q.defer();
	    			  /*
	    			  $timeout(function() {
	    				  $log.debug('-------------- mxValidator: timeout');
				          if (validEmpty) {
				        	  if (!ctrl.$isEmpty(modelValue)) {
					            def.resolve();
					          } else {
					            def.reject();
					          }
				          }
	    				  else 
	    					  def.reject();
				          
	    			  }, 2000);
	    			  */
	    			  fn(modelValue).then(function (result) {
	    				  if (DEBUG) $log.debug('-------------- mxValidator: result = ', !!result.result);
	    				  if (!!result.result) 
	    					  def.resolve();
	    				  else 
	    					  def.reject();
	    			  });
	    			  
	    			  return def.promise;
	    		  };
	    	  }
	    	  else {
	    		  ctrl.$validators['mxValidator_' + key] = function(modelValue, viewValue) {
	    			  var result = fn(modelValue);
	    			  if (DEBUG) $log.debug('-------------- mxValidator: result = ', result);
	    			  return !!result;
	    		  };
	    	  }
    	  
    	});
    	  
      }
    };
});