App
.config(function($stateProvider, $urlRouterProvider, $ocLazyLoadProvider) {
	
	$ocLazyLoadProvider.config({
	    //debug: true
	});
	
	
	//var $log = $logProvider.$get(); //doesn't work
	var $log =  angular.injector(['ng']).get('$log');
	
	var dependenciesLoad = function($ocLazyLoad, dependencies) {
		var $log =  angular.injector(['ng']).get('$log');
		var $q = angular.injector(['ng']).get('$q');
		var deferred = $q.defer();
		//$log.debug('dependenciesLoad: dependencies = ', dependencies);
		var deps = {
            	name: 'esApp',
                files: dependencies
             };
        var loadResult = $ocLazyLoad.load(deps); 
        //$log.debug('dependenciesLoad: loadResult = ', loadResult);
        var onError = function(response){
        	$log.error('Dependencies load error: ', response);
        };
        var retryCount = 0;
        var onErrorRetry = function(response){
        	var error = {message: response, retryCount: retryCount};
        	$log.error('Dependencies load error: ', error);
        	retryCount++;
        	/*
        	$log.debug('dependenciesLoad: retryCount = ', retryCount);
        	if (retryCount < 3)
        		loadResult = $ocLazyLoad.load(deps).then(function(){}, onErrorRetry);
        	else 
        		loadResult = $ocLazyLoad.load(deps).then(function(){}, onError);
        	*/
        	if (retryCount < 4) {
        		//$log.debug('dependenciesLoad: retryCount = ', retryCount);
	        	var $http = angular.injector(['ng']).get('$http');
	        	var promises = [];
	        	angular.forEach(deps.files, function(url, key){
	        		url = url.replace('__loadtest__','');
	        		var p = $http.get(url).error(onErrorRetry);
	        		promises.push(p);
	        	});
	        	$q.all(promises).then(function(){
	        		deferred.resolve();
	        	});
        	}
        };
        loadResult.then(function(){
				        	deferred.resolve();
				        }, onErrorRetry);
        //return loadResult;
        return deferred.promise;
    };
		
	$urlRouterProvider.otherwise("/");
	
	$stateProvider
    .state('login', {
        url: "/login",
        templateUrl: "tpl/states/auth_login.html",
        controller: 'LoginCtrl',
        data: {title: 'Login'}
    })
    .state('logout', {
        url: "/logout"
    })
    .state('forgot_password', {
        url: "/forgot_password",
        templateUrl: "tpl/states/auth_forgot_password.html",
        controller: 'LoginCtrl',
        data: {title: 'Forgot Password'}
    })
    .state('reset_password', {
        url: "/reset_password/:code",
        templateUrl: "tpl/states/auth_reset_password.html",
        controller: 'LoginCtrl',
        data: {title: 'Reset Password'}
    })
    .state('registration_confirmed', {
        url: "/registration/confirm/:code",
        templateUrl: "tpl/states/auth_login.html",
        controller: 'LoginCtrl',
        data: {title: 'Registration Confirmation'}
    })
    .state('home', {
        url: "/",
        templateUrl: "tpl/states/home.html",
        controller: 'HomeCtrl',
        resolve: {
    		loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
                return $ocLazyLoad.load({
                	name: 'esApp',
                    files: [
                            'js/controllers/HomeCtrl.js'
                            ]
                 }); 
            }]
    	},
    	data: {title: 'Home'}
    })
    .state('wiki', {
        url: "/wiki",
        templateUrl: "tpl/states/wiki/wiki.html",
        controller: 'WikiCtrl',
        resolve: {
    		loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
                return $ocLazyLoad.load({
                	name: 'esApp',
                    files: [
                            'js/services/WikiService.js', 
                            'js/controllers/wiki/WikiCtrl.js'
                            ]
                 }); 
            }]
    	},
    	data: {title: 'Wiki'}
    })
    
    ////////////////////////////////////////////////////////////////
    .state('wiki.step1', {
    	parent: 'wiki',
        url: "/step1",
        /*
        views: {
        	'wiki' : {
        		templateUrl: "tpl/states/wiki/step1.html",
                controller: 'WikiStep1Ctrl',
                resolve: {
            		loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
            			var deps = [
	                                'js/controllers/wiki/WikiStep1Ctrl.js'
	                                ];
            			return dependenciesLoad($ocLazyLoad, deps);
                    }] 
            	}
        	}
        },
        */
        templateUrl: "tpl/states/wiki/step1.html",
        controller: 'WikiStep1Ctrl',
        resolve: {
    		loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
    			var deps = [
                            'js/controllers/wiki/WikiStep1Ctrl.js'
                            ];
    			return dependenciesLoad($ocLazyLoad, deps);
            }] 
    	},
    	data: {title: 'Step 1'}
    })
    .state('wiki.step2', {
    	parent: 'wiki',
        url: "/step2",
        templateUrl: "tpl/states/wiki/step2.html",
        controller: 'WikiStep2Ctrl',
        resolve: {
    		loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
    			var deps = [
                            'js/controllers/wiki/WikiStep2Ctrl.js'
                            ];
    			return dependenciesLoad($ocLazyLoad, deps);
            }] 
    	},
    	data: {title: 'Step 2'}
    })
    .state('wiki.step3', {
    	parent: 'wiki',
        url: "/step3",
        templateUrl: "tpl/states/wiki/step3.html",
        controller: 'WikiStep3Ctrl',
        resolve: {
    		loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
    			var deps = [
                            'js/controllers/wiki/WikiStep3Ctrl.js'
                            ];
    			return dependenciesLoad($ocLazyLoad, deps);
            }] 
    	},
    	data: {title: 'Step 2'}
    })
    ////////////////////////////////////////////////////////////////
    
    .state('search', {
    	url: "/search",
        templateUrl: "tpl/states/search.html",
        controller: 'SearchCtrl',
        resolve: {
    		loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
    			var deps = [
                            'js/controllers/SearchCtrl.js'
                            ];
    			return dependenciesLoad($ocLazyLoad, deps);
            }] 
    	},
    	data: {title: 'Search'}
    })
    .state('marketplace', {
    	url: "/marketplace",
        templateUrl: "tpl/states/marketplace.html",
        controller: 'MarketplaceCtrl',
        resolve: {
    		loadMyCtrl: ['$ocLazyLoad', function($ocLazyLoad) {
    			var deps = [
                            'js/controllers/MarketplaceCtrl.js'
                            ];
    			return dependenciesLoad($ocLazyLoad, deps);
            }] 
    	},
    	data: {title: 'Marketplace'}
    })
	;	
});
