App.directive('esUniquenessRequired', function() {
    return {
 
      restrict: 'A',
 
      // require NgModelController, i.e. require a controller of ngModel directive
      require: 'ngModel',
 
      // create linking function and pass in our NgModelController as a 4th argument
      link: function(scope, element, attrs, ctrl) {
    	  
    	  function customValidator(ngModelValue) {
	    	    
    		  	var fn = scope.$eval(attrs.mxUniquenessRequired);
	    	    
	    	    fn(ngModelValue)
    	        .then(function(result){
    	        	ctrl.$setValidity('mxuniquenessValidator', !!result.result);
				}); 
    	 
    	        // we need to return our ngModelValue, to be displayed to the user(value of the input)
    	        return ngModelValue;
    	    }
    	  	
    	  	// we need to add our customValidator function to an array of other(build-in or custom) functions
    	    // it would be worth investigating how much
    	    // effect does this have on the performance of the app
    	    ctrl.$parsers.push(customValidator);
    	  
      }
    };
});