App.directive('esImgPreview', function($parse, $log) {
    return {
      restrict: 'A',
      link: function(scope, element, attrs, ctrl) {
    	
    	  //$log.debug('^^^^^ directive mxImgPreview: attrs.mxImgPreview = ', attrs.mxImgPreview);
    	  //var options = scope.$eval(attrs.mxImgPreview);
    	  var previewFile = $parse(attrs.mxImgPreview)(scope);
    	  //$log.debug('^^^^^ directive mxImgPreview: previewFile = ', previewFile);
    	  
    	  if (typeof previewFile == 'string') {
    		  angular.element(element).attr('src', previewFile);
    	  }
    	  else if (previewFile) {
  			var reader  = new FileReader();
  			reader.onloadend = function () {
  				angular.element(element).attr('src', reader.result);
  			};
  			reader.readAsDataURL(previewFile);
    	  }
    	  
      }
    };
});