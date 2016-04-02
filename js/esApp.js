"use strict";

angular.module('esWrapper', ['esApp', 'ui.bootstrap'])
.config(['$tooltipProvider', function($tooltipProvider){
    $tooltipProvider.setTriggers({
        //'click': 'hideEvent' //'click': 'blur'
    	'click': 'click',
    	'showEvent': 'hideEvent'
    });
}]);

var App = angular.module('esApp',['ngRoute', 
                                    'ui.router', 'ngSanitize', 
                                    'ui.mask', 'ui.validate', //'ui.utils', 
                                    'oc.lazyLoad', 'ui.bootstrap.showErrors', 
                                    'ui.grid', 
                                    	'ui.grid.edit', 'ui.grid.rowEdit', 'ui.grid.cellNav', //'ui.grid.pinning'
                                    	'ui.grid.resizeColumns', 'ui.grid.pagination', 'ui.grid.autoResize', 
                                    	'ui.grid.expandable', 'ui.grid.selection', 'ui.grid.exporter', 'ui.grid.saveState',
                                    'notyModule', 'satellizer', 'infinite-scroll', 
                                    'ngCookies', 'socialLinks', 'googlechart', 'colorpicker.module'
                                    ], 
                                    function($compileProvider) {
    									//$compileProvider.imgSrcSanitizationWhitelist('app://');
    									$compileProvider.imgSrcSanitizationWhitelist(/^\s*(https?|ftp|file|chrome-extension):|data:image\//);
    								}
)
.value('THROTTLE_MILLISECONDS', 500)       // infinite-scroll: throttling scroll events                             
.constant('BASE_URL', window.location.protocol + '//' + window.location.host)
.constant('SETTINGS', {
		INSTANCE_DEV : 'dev106',
		INSTANCE_QA : 'qa',
		INSTANCE_PROD : 'prod',
		VERSION : '0'
})
.config(['BASE_URL', 'SETTINGS', function(BASE_URL, SETTINGS)  {

	//////////////////////
	
	SETTINGS.appTitle = 'ESpade';
	
	var hostGroups = [
			{
				client_host: 'local.espade',
				admin_host: 'admin.local.espade',
				instance_alias: SETTINGS.INSTANCE_DEV,
			    countryCallingCode: '38',
			    passwMinlength : 3,
				passwMaxlength : 20,
        		logoutRedirectUrl : BASE_URL
			}
	];
		
	for (var i = 0; i < hostGroups.length; i++) {
		if (window.location.host != hostGroups[i].client_host && window.location.host != hostGroups[i].admin_host)
			continue;
		
		SETTINGS.INSTANCE = hostGroups[i].instance_alias;
		SETTINGS.INSTANCE_CLIENT_HOST = hostGroups[i].client_host;
		SETTINGS.INSTANCE_ADMIN_HOST = hostGroups[i].admin_host;
		
		SETTINGS.countryCallingCode = hostGroups[i].countryCallingCode;
		SETTINGS.passwMinlength = hostGroups[i].passwMinlength;
		SETTINGS.passwMaxlength = hostGroups[i].passwMaxlength;
		SETTINGS.logoutRedirectUrl = hostGroups[i].logoutRedirectUrl;
		break;
	}
	
	SETTINGS.clientBaseUrl = window.location.protocol + '//' + SETTINGS.INSTANCE_CLIENT_HOST;
	SETTINGS.adminBaseUrl = window.location.protocol + '//' + SETTINGS.INSTANCE_ADMIN_HOST;
		
	SETTINGS.fileinputMaxFileSize = 10240; //kB, 0 - unlimited
	
	SETTINGS.adminGridsPageSize = 50;
	SETTINGS.adminGridsPageSizes = [10, 25, 50];
	
	SETTINGS.accGroupAdmin = 'admin';
	
	SETTINGS.accStatusConfirmed = 3;
	
	
	SETTINGS.baseUrl = BASE_URL;
	//SETTINGS.logoutRedirectPath = '/';
	SETTINGS.adminLogoutRedirectPath = '/login';
	
	SETTINGS.backendUrlDownloadFile = BASE_URL + '/backend/main/file_download';
	
	SETTINGS.baseUrl = BASE_URL;
	
	SETTINGS.filesBaseUrl 					= BASE_URL + '/api/files';
	
	SETTINGS.apiUrlLogin 					= BASE_URL + '/api/auth/login';
	SETTINGS.apiUrlLoginJwt 				= BASE_URL + '/api/auth/login_jwt';
	SETTINGS.apiUrlLogout 					= BASE_URL + '/api/auth/logout';
	SETTINGS.apiUrlProfile 					= BASE_URL + '/api/auth/profile';
	SETTINGS.apiUrlConfirmRegistration		= BASE_URL + '/api/auth/confirm_registration';
	SETTINGS.apiUrlForgotPassword 			= BASE_URL + '/api/auth/forgot_password';
	SETTINGS.apiUrlForgottenPasswordCheck 	= BASE_URL + '/api/auth/forgotten_password_check';
	SETTINGS.apiUrlResetPassword 			= BASE_URL + '/api/auth/reset_password';
	SETTINGS.apiUrlAppSettingsSave 			= BASE_URL + '/api/auth/app_settings_save';
	SETTINGS.apiUrlAppSettingsRead 			= BASE_URL + '/api/auth/app_settings_read';
	
	//admin api
	SETTINGS.apiUrlRegistrationStat 		= BASE_URL + '/api/auth/get_registrations_stat';
		
	SETTINGS.apiUrlAccEmailUniquenessCheck 	= BASE_URL + '/api/auth/account_email_uniqueness_check';

    
	SETTINGS.ERR_EMAIL_SENDING				= 7000;
	SETTINGS.ERR_INVALID_PARAMETER_VALUE	= 1004;
	SETTINGS.ERR_VALIDATION_FAILED			= 1005;
	SETTINGS.ERR_CAMPAIGN_NOT_FOUND			= 4002;
	SETTINGS.ERR_FILE_UPLOAD				= 6000;
	SETTINGS.ERR_FILE_UPLOAD_LOW_RESOLUTION_PDFIMAGES = 6100;
	
	SETTINGS.ERR_FILE_UPLOAD_MESSAGE_TPL = 'We are sorry, there was an unknown error with your image upload. %EDIT_LINK% Thanks!';
	SETTINGS.ERR_UNKNOWN_MESSAGE = 'We are sorry. There has been a problem with your registration.';
	
	SETTINGS.gridUnfilteredFilterValue = 'grid_unfiltered';
	
	SETTINGS.admin = {
		gridDebug: false
	};
	
}])

.config(function($stateProvider, $urlRouterProvider, $ocLazyLoadProvider) {
	
	$ocLazyLoadProvider.config({
	    //debug: true
	});

	//$urlRouterProvider.otherwise("/");

})
/*
.config(function($provide) {
	$provide.decorator('ngModelDirective', ["$delegate", function($delegate) {
	    var ngModel = $delegate[0];
	    var controller = ngModel.controller;

	    ngModel.controller = ['$scope', '$element', '$attrs', '$injector', function(scope, element, attrs, $injector) {
	        var $interpolate = $injector.get('$interpolate');
	        attrs.$set('name', $interpolate(attrs.name || '')(scope));
	        $injector.invoke(controller, this, {
	            '$scope': scope,
	            '$element': element,
	            '$attrs': attrs
	        });
	    }];

	    return $delegate;
	}]);

	$provide.decorator('formDirective', ["$delegate", function($delegate) {
	    var form = $delegate[0];
	    var controller = form.controller;

	    form.controller = ['$scope', '$element', '$attrs', '$injector', function(scope, element, attrs, $injector) {
	        var $interpolate = $injector.get('$interpolate');
	        attrs.$set('name', $interpolate(attrs.name || attrs.ngForm || '')(scope));
	        $injector.invoke(controller, this, {
	            '$scope': scope,
	            '$element': element,
	            '$attrs': attrs
	        });
	    }];

	    return $delegate;
	}]);
})
*/
.config(['showErrorsConfigProvider', function(showErrorsConfigProvider) {
  showErrorsConfigProvider.showSuccess(true);
}])
.config(['$httpProvider', function ($httpProvider) {
  //$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
  //$httpProvider.defaults.headers.common['Accept'] = 'application/json, text/plain';
	$httpProvider.defaults.cache = true;
	
	$httpProvider.interceptors.push(['SETTINGS', function(SETTINGS){
	    return {
	        request: function(config){
	            if(config.url.indexOf('tpl/') > -1){
	                var separator = config.url.indexOf('?') === -1 ? '?' : '&';
	                config.url = config.url + separator + 'v=' + SETTINGS.VERSION; //Date.now();
	            }
	            return config;
	        }
	    };
	}]);
}])
.run(function($ocLazyLoad, $log, $rootScope, $state, $stateParams){
	/*
	$ocLazyLoad.load(['js/services/SessionService.js', 'js/services/AuthService.js'])
	.then(function(){
		$log.debug('loaded');
	});
	*/
	
	$rootScope.$state = $state;
    $rootScope.$stateParams = $stateParams;
})

.run(function($rootScope, $location, $window, $anchorScroll, $log, SETTINGS, SessionService) {
    $rootScope.page = {
    	setTitle: function(title) {
            this.title = title ? SETTINGS.appTitle + ' - ' + title : 'Maxletics';
            //$log.debug('setTitle: ', title);
        }
    };
    
    $rootScope.$on('$stateChangeSuccess', function(event, toState, toParams, fromState, fromParams) {
    	//$log.debug('run: toState = ', toState);
    	if (toState.data) {
    		$rootScope.page.setTitle(toState.data.title);
    	}
    	if($location.hash()) $anchorScroll(); 
    	
    	if ($window.ga) {
    		//$log.debug('run: path = ', $location.path());
    		$window.ga('send', 'pageview', { page: $location.path() });
    	}
    });
    
})

;


