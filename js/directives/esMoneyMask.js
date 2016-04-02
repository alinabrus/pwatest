App.directive('esMoneyMask1', function($parse, $log) {
	return {
		restrict: 'A',
		 
	    // require NgModelController, i.e. require a controller of ngModel directive
	    require: 'ngModel',
	      
        link: function (scope, el, attrs, ctrl) {
        	//var maskOptions = scope.$eval(attrs.mxInputMask);
        	/*
        	var maskOptions = {
        			alias: 'currency', 
        			prefix:'', 
        			placeholder: '', 
        			radixPoint:'.', 
        			groupSeparator: ',',  
        			digits: 2, 
        			autoGroup: true, 
        			digitsOptional: false, 
        			allowPlus: false, 
        			allowMinus: false
        	};
        	*/
        	/////////////////////////
        	/*
        	currency: {
                prefix: "$ ",
                groupSeparator: ",",
                alias: "numeric",
                placeholder: "0",
                autoGroup: !0,
                digits: 2,
                digitsOptional: !1,
                clearMaskOnLostFocus: !1
            },
            decimal: {
                alias: "numeric"
            },
            integer: {
                alias: "numeric",
                digits: 0,
                radixPoint: ""
            },
            percentage: {
                alias: "numeric",
                digits: 2,
                radixPoint: ".",
                placeholder: "0",
                autoGroup: !1,
                min: 0,
                max: 100,
                suffix: " %",
                allowPlus: !1,
                allowMinus: !1
            }
            */
        	var maskOptions = {
        			prefix: ''
        	};
        	el.inputmask('currency', maskOptions);
        	
        	var changeHandler = function() {
            	//scope.$eval(attrs.ngModel + "='" + el.val() + "'");
            	//$log.debug('maskOptions = ', maskOptions);
            	var val = el.val();
            	//if (maskOptions.alias && maskOptions.alias === 'currency') {
            		val = (parseFloat(val) > 0 ? val : '').replace(/,/g,'');
            		//$log.debug('val = ', val);
            		el.val(val);
            		if (val === '') {
            			ctrl.$setViewValue(val);
            			if(el.attr('required')) ctrl.$setValidity('required', false);
            		}
            	//}
                $parse(el.attr('ng-model')).assign(scope, val);
                
            };
            el.on('change', changeHandler);
            el.on('blur', changeHandler);
            
        }
    };
});

App.directive('mxMoneyMask2', function($parse, $log) {
	return {
		restrict: 'A',
		 
	    // require NgModelController, i.e. require a controller of ngModel directive
	    require: 'ngModel',
	      
        link: function (scope, el, attrs, ctrl) {
        	
        	var options = scope.$eval(attrs.mxMoneyMask);
            //$log.debug('options = ', options);
            
            var defaults =
    		{
    			prefix: 'US$ ',
                suffix: '',
    			centsSeparator: '.',
    			thousandsSeparator: ',',
    			limit: false,
    			centsLimit: 2,
    			clearPrefix: false,
                clearSufix: false,
    			allowNegative: false,
    			insertPlusSign: false,
    			clearOnEmpty:false
    		};
        	var maskOptions = {
    			prefix: '',
    			//suffix: '.oo',
    			//clearSufix: true,
    			centsLimit: (options && typeof options.centsLimit != 'undefined' ? options.centsLimit : 2),
    			clearPrefix: true, 
    			clearOnEmpty: true
        	};
        	
        	el.priceFormat(maskOptions);  
        	
        	var changeHandler = function() {
            	//scope.$eval(attrs.ngModel + "='" + el.val() + "'");
            	//$log.debug('maskOptions = ', maskOptions);
            	var val = el.val();
            	//if (maskOptions.alias && maskOptions.alias === 'currency') {
            		val = (parseFloat(val) > 0 ? val : '').replace(/,/g,'');
            		//$log.debug('val = ', val);
            		el.val(val);
            		if (val === '') {
            			ctrl.$setViewValue(val);
            			if(el.attr('required')) ctrl.$setValidity('required', false);
            		}
            	//}
                $parse(el.attr('ng-model')).assign(scope, val);
                
            };
            el.on('change', changeHandler);
            el.on('blur', changeHandler);
            
        }
    };
});

App.directive('mxMoneyMask3', function($parse, $log) {
	return {
		restrict: 'A',
		 
	    // require NgModelController, i.e. require a controller of ngModel directive
	    require: 'ngModel',
	      
        link: function (scope, elem, attrs, ctrl) {
        	
        	var maskOptions = {
        			prefix: ''
        	};
        	elem.inputmask('currency', maskOptions);  
        	
        	ctrl.$formatters.unshift(function (modelValue) {
            	// what you return here will be passed to the text field
            	//$log.debug('ctrl.$formatters.unshift ', elem[0].value);
        		
        		//elem[0].value = ctrl.$modelValue * 100;
        		/*
        		elem[0].value = modelValue;
                elem.priceFormat(maskOptions);
                //$log.debug('--- ctrl.$formatters.unshift ', elem[0].value);
                return elem[0].value;
                */
        		//return parseFloat(elem[0].value);
            });
            ctrl.$parsers.unshift(function (viewValue) {
            	// put the inverse logic, to transform formatted data into model data
                // what you return here, will be stored in the $scope
            	//$log.debug('ctrl.$parsers.unshift ', elem[0].value);
                //elem.priceFormat(maskOptions);      
                if(elem.attr('required') && !elem[0].value) {
                	ctrl.$setViewValue(elem[0].value);
                	ctrl.$setValidity('required', false);
                }
                if(elem[0].value!= '' && elem[0].value < 0.5)
                {
                    ctrl.$setValidity("minDonationValue", false);
                }
                else
                    ctrl.$setValidity("minDonationValue", true);
                //$log.debug('--- ctrl.$parsers.unshift ', elem[0].value);
                return elem[0].value.replace(/,/g,'');
            });
            
        }
    };
});

App.directive('mxMoneyMask', function ($parse, $log) {
    return {
        require: '?ngModel',
        link: function (scope, elem, attrs, ctrl) {
            if (!ctrl) return;
            
            var options = scope.$eval(attrs.mxMoneyMask);
            //$log.debug('options = ', options);
            
            var defaults =
    		{
    			prefix: 'US$ ',
                suffix: '',
    			centsSeparator: '.',
    			thousandsSeparator: ',',
    			limit: false,
    			centsLimit: 2,
    			clearPrefix: false,
                clearSufix: false,
    			allowNegative: false,
    			insertPlusSign: false,
    			clearOnEmpty:false
    		};
        	var maskOptions = {
        		limit: 9,	
    			prefix: '',
    			//suffix: '.oo',
    			//clearSufix: true,
    			centsLimit: (options && typeof options.centsLimit != 'undefined' ? options.centsLimit : 2),
    			clearPrefix: true, 
    			clearOnEmpty: true
        	};
        	
        	//elem.priceFormat(maskOptions);  
        	
        	ctrl.$formatters.unshift(function (modelValue) {
            	// what you return here will be passed to the text field
            	//$log.debug('ctrl.$formatters.unshift ', elem[0].value);
        		
        		//elem[0].value = ctrl.$modelValue * 100;
        		/*
        		elem[0].value = modelValue;
                elem.priceFormat(maskOptions);
                //$log.debug('--- ctrl.$formatters.unshift ', elem[0].value);
                return elem[0].value;
                */        		
        		
        		elem[0].value = parseFloat((ctrl.$modelValue * 100).toFixed(2));
        		if (elem[0].value == 0 || isNaN(elem[0].value)) {
        			elem[0].value = '';
        		}
        		else {
        			elem.priceFormat(maskOptions);
        		}
        		return elem[0].value;
            });
        	
            ctrl.$parsers.unshift(function (viewValue) {
            	// put the inverse logic, to transform formatted data into model data
                // what you return here, will be stored in the $scope
            	//$log.debug('ctrl.$parsers.unshift ', elem[0].value);
            	
            	elem.priceFormat(maskOptions);   
            	
                if(elem.attr('required') && !elem[0].value) {
                	ctrl.$setViewValue(elem[0].value);
                	ctrl.$setValidity('required', false);
                }
                if(elem[0].value!= '' && elem[0].value < 0.5)
                {
                    ctrl.$setValidity("minDonationValue", false);
                }
                else
                    ctrl.$setValidity("minDonationValue", true);
                //$log.debug('--- ctrl.$parsers.unshift ', elem[0].value);
                return elem[0].value.replace(/,/g,'');
            });
            /*
            var changeHandler = function() {
            	elem.priceFormat(maskOptions);      
                if(elem.attr('required') && !elem[0].value) {
                	ctrl.$setViewValue(elem[0].value);
                	ctrl.$setValidity('required', false);
                }
                if(elem[0].value!= '' && elem[0].value < 0.5)
                {
                    ctrl.$setValidity("minDonationValue", false);
                }
                else
                    ctrl.$setValidity("minDonationValue", true);
                //$log.debug('--- ctrl.$parsers.unshift ', elem[0].value);
                var val = elem[0].value.replace(/,/g,'');
            	
                $parse(elem.attr('ng-model')).assign(scope, val);
            };
            //elem.on('keyup', changeHandler);
            elem.on('blur', changeHandler);
        	*/
        }
    };
});