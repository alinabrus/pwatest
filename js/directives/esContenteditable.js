App
.directive('esContenteditable', ['$sce','$parse', '$log', function($sce, $parse, $log) {
	  return {
		    restrict: 'A', // only activate on element attribute
		    require: '?ngModel', // get a hold of NgModelController
		    link: function(scope, element, attrs, ngModel) 
		    {
		      if (!ngModel) return; // do nothing if no ng-model
		      
		      var placeholder = element.html();
		      
		      element.on('click', function() {
		    	  if (element.html() == placeholder)
		    		  element.html('');
		      });

		      element.on('keydown', function() {
		    	  if (element.html() == placeholder)
		    		  element.html('');
		      });
		      
		      ngModel.$formatters.push(function (modelValue) {
		    	  // what you return here will be passed to the text field
		    	  //$log.debug('$formatters: ', modelValue, placeholder);
		    	  return (modelValue ? modelValue : placeholder);
		      });
		      ngModel.$parsers.push(function (viewValue) {
		    	  // put the inverse logic, to transform formatted data into model data
		    	  // what you return here, will be stored in the $scope
		    	  //$log.debug('$parsers: ', viewValue, placeholder);
		    	  viewValue = viewValue.replace(/(.*)<br>$/, '$1');
		    	  //$log.debug('--- $parsers: ', viewValue);
		    	  return (viewValue == placeholder ? '' : viewValue);
		      });
			   
		      // Specify how UI should be updated
		      ngModel.$render = function() {
		        element.html($sce.getTrustedHtml(ngModel.$viewValue || ''));
		      };
		      // Listen for change events to enable binding
		      element.on('blur keyup change', function() {
		        scope.$evalAsync(read);
		      });
		      
		      // Write data to the model
		      function read() {
		        var html = element.html();
		        // When we clear the content editable the browser leaves a <br> behind
		        // If strip-br attribute is provided then we strip this out
		        //if ( attrs.stripBr && html == '<br>' ) 
		        if (html == '<br>')
		        {
		          html = '';
		        }
		        ngModel.$setViewValue(html);
		      }
		      
		    }
  };
}]);