/*
App.directive('esPopoverShow', function($parse, $log) {
	return {
		restrict: 'A',

        link: function (scope, el, attrs) {
        	el.addClass('mx-popover');
            el.on('click', function() {
            	el.trigger('showEvent');
            });
            scope.$on('mx-popover-hide', function() {
            	el.trigger('hideEvent');
            });
            
        }
    };
});

App.directive('esPopoverHide', function($parse, $log) {
	return {
		restrict: 'A',

        link: function (scope, el, attrs) {
        	//el.contents().addClass('mx-popover');
        	el.on('click', function(event) {
            	var target = angular.element(event.target);
            	if ( ! target.hasClass('mx-popover') && ! target.parents('.mx-popover').length) scope.$broadcast('mx-popover-hide');
            });
            
        }
    };
});

App.directive('esPopoverKeep', function($parse, $log) {
	return {
		restrict: 'A',

        link: function (scope, el, attrs) {        	
        	el.addClass('mx-popover');           
        }
    };
});
*/
/////////////////////////////////////////////////////////////////////

App.directive('esPopoverHide', function($parse, $log, $timeout) {
	return {
		restrict: 'A',

        link: function (scope, el, attrs) {
        	
        	//$log.debug('attrs = ', attrs);
        	if (attrs.mxPopoverHide) {
        		//var popoverSwitch = $parse(attrs['mx-popover-hide'])(scope);
        		scope.$watch(attrs.mxPopoverHide, function(newValue, oldValue) {
	    			if (newValue !== oldValue) {
	    				$log.debug('newValue = ', newValue);
	    	            
	    	            if (newValue) {
	    	            	$timeout(function() {
	    	            		if (el.attr('mx-popover-toggler') == 1) {
	    	                		el.trigger('hideEvent');
	    	            			el.attr('mx-popover-toggler', 0);
	    	                	}
	    	            	});
	    	            }
	    			}
	    		});
	        } //if
        	
        }
    };
});

App.directive('esPopover_1', function($parse, $log, $anchorScroll, $location) {
	return {
		restrict: 'A',

        link: function (scope, el, attrs) {
        	
        	if ( ! el.hasClass('mx-popover-caller')) 
        	el.on('click', function() {
        		//el.trigger('showEvent'); 
        		
        		var popCallers = angular.element('.mx-popover-caller');
        		angular.forEach(popCallers, function (caller, index) {
        			var callerEl = angular.element(caller);
        			if ( ! angular.equals(el, callerEl) && callerEl.attr('mx-popover-toggler') == 1) {
        				callerEl.trigger('hideEvent');
        				callerEl.attr('mx-popover-toggler', 0);
                	}
        		});
        		
        		//$log.debug('el = ', el);
        		//$log.debug('mx-popover-toggler = ', el.attr('mx-popover-toggler'));
        		if (el.attr('mx-popover-toggler') == 1) {
            		el.trigger('hideEvent');
        			el.attr('mx-popover-toggler', 0);
            	}
            	else {
            		el.trigger('showEvent');
            		el.attr('mx-popover-toggler', 1);
            	} 
            });
        	
        	el.addClass('mx-popover-caller');
            
            scope.$on('mx-popover-hide', function() {
            	//$log.debug('mx-popover-toggler = ', el.attr('mx-popover-toggler'));
            	if (el.attr('mx-popover-toggler') == 1) {
            		el.trigger('hideEvent');
            		el.attr('mx-popover-toggler', 0);
            	}
            });
            
            angular.element('body').on('click', function(event) {
            	var target = angular.element(event.target);
            	if ( 
            		! (target.hasClass('mx-popover-caller') || target.parents('.mx-popover-caller').length)
            		&& ! (target.hasClass('popover') || target.parents('.popover').length)
            	) {
            		//$log.debug('target = ', target);
            		scope.$broadcast('mx-popover-hide');
            	}
            });
            
            el.on('showEvent', function(event) {
            	var target = angular.element(event.target);
            	//$log.debug('showEvent: target = ', target);
            	//$log.debug('showEvent: attrs = ', attrs);
            	var options = scope.$eval(attrs.mxPopover);
            	if (options && options.templateHash) {
            		$location.hash(options.templateHash);
            		$anchorScroll();
            	}
            });
            
        }
    };
});

App.directive('esPopover', function($parse, $log, $anchorScroll, $location) {
	return {
		restrict: 'A',

        link: function (scope, el, attrs) {
        	
        	var options = scope.$eval(attrs.mxPopover);
        	        	
        	if ( ! el.hasClass('mx-popover-caller')) 
            	el.on('click tap', function() {
            		/*
            		$log.debug('click: el = ', el);
            		$log.debug('click: attrs = ', attrs);
            		$log.debug('click: mx-popover-toggler = ', el.attr('mx-popover-toggler'));
            		if (options.templateCloser) {
            			//$log.debug('click: options.templateCloser.indexOf(\'this\') = ', options.templateCloser.indexOf('this'));
            		}
            		*/
            		if ( ! el.attr('mx-popover-toggler') || el.attr('mx-popover-toggler') == 0) {
            			var popoverEnable = attrs.popoverEnable ? scope.$eval(attrs.popoverEnable) : true;
            			//$log.debug('click: popoverEnable = ', popoverEnable);
            			if (popoverEnable) {
	                		el.trigger('showEvent');
	                		el.attr('mx-popover-toggler', 1);
            			}
                	} 
            		else if (el.attr('mx-popover-toggler') == 1 && (options.templateCloser && options.templateCloser.indexOf('this') > -1)) {
            			//scope.$broadcast('mx-popover-hide', 'this');
            			el.trigger('hideEvent');
    					el.attr('mx-popover-toggler', 0);
            		}
                });
        	
        	el.addClass('mx-popover-caller');
            
            scope.$on('mx-popover-hide', function(event, senderSelector) {
            	var target = angular.element(senderSelector);
            	if ( senderSelector == 'this' || target.hasClass('mx-popover-caller') || target.parents('.mx-popover-caller').length )
            		return;
            	//$log.debug('mx-popover-hide: mx-popover-toggler = ', el.attr('mx-popover-toggler'));
            	if (el.attr('mx-popover-toggler') == 1) {
            		//$log.debug('mx-popover-hide: senderSelector = ', senderSelector);
            		//$log.debug('mx-popover-hide: el = ', el);
                	//$log.debug('mx-popover-hide: options = ', options);
            		if (options && options.templateCloser) {
            			if (options.templateCloser.indexOf(senderSelector) > -1) {
            				el.trigger('hideEvent');
        					el.attr('mx-popover-toggler', 0);
            			}
            		}
            		else if (senderSelector == 'body') {
            			el.trigger('hideEvent');
            			el.attr('mx-popover-toggler', 0);
            		}
            	}
            });
            
            ////////////////////////////////////
            
            var bodyClickHandler = function(event) {
            	var target = angular.element(event.target);
            	//$log.debug('bodyClickHandler: target = ', target);
            	if ( 
            		! (target.hasClass('mx-popover-caller') || target.parents('.mx-popover-caller').length)
            		&& ! (target.hasClass('popover') || target.parents('.popover').length)
            	) {
            		//$log.debug('--- bodyClickHandler: target = ', target);
            		scope.$broadcast('mx-popover-hide', 'body');
            	}
            };
            
            if (options && options.templateCloser) {
				if (options.templateCloser.indexOf('body') > -1)
					angular.element('body').on('click tap', bodyClickHandler);
			}
			else 
				angular.element('body').on('click tap', bodyClickHandler);
            
            ////////////////////////////////////
            
            el.on('showEvent', function(event) {
            	//$log.debug('showEvent: mx-popover-toggler = ', el.attr('mx-popover-toggler'));
            	var target = angular.element(event.target);
            	//$log.debug('showEvent: target = ', target);
            	//$log.debug('showEvent: attrs = ', attrs);
            	if (options && options.templateHash) {
            		$location.hash(options.templateHash);
            		$anchorScroll();
            	}
            	if (options && options.templateCloser) {
    				for (var i in options.templateCloser) {
    					var senderSelector = options.templateCloser[i];
    					if (senderSelector === 'this' || senderSelector === 'body') continue;
    					//$log.debug('showEvent: templateCloser = ', angular.element(senderSelector));
    					angular.element(senderSelector)
    					.attr('mx-popover-hide-sender', senderSelector)
    					.on('click tap', function(event) {
    						//scope.$broadcast('mx-popover-hide', $(this).attr('mx-popover-hide-sender'));
    						
    						var elem = angular.element($(this).attr('mx-popover-hide-sender'));
    						if ( 
			            		! (elem.hasClass('mx-popover-caller') || elem.parents('.mx-popover-caller').length)
			            	) {
    							scope.$broadcast('mx-popover-hide', $(this).attr('mx-popover-hide-sender'));
			            	}
			            	
    		            });
    				}
    			}
            });
            
        }
    };
});

