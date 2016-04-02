App.directive('esInputMask', function($parse, $log) {
	return {
        restrict: 'AC',
        link: function (scope, el, attrs) {
        	var maskOptions = scope.$eval(attrs.mxInputMask);
        	el.inputmask(maskOptions);
            el.on('change', function() {
            	//scope.$eval(attrs.ngModel + "='" + el.val() + "'");
            	//$log.debug('maskOptions = ', maskOptions);
            	var val = el.val();
            	if (maskOptions.alias && maskOptions.alias === 'currency') {
            		var val = (parseFloat(val) === 0.0 ? '' : val).replace(/,/g,'');
            		//$log.debug('val = ', val);
            		el.val(val);
            	}
                $parse(el.attr('ng-model')).assign(scope, val);
            });
        }
    };
});