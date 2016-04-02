App
.controller('EsAppCtrl', 
['$scope', '$location', '$log', 'FileInputService', 'AuthService', 'SessionService', 'SETTINGS', '$rootScope', '$parse', '$state', '$stateParams', '$cookies', '$cookieStore', 'noty', '$anchorScroll', '$window', '$q', '$interval',
function ($scope, $location, $log, FileInputService, AuthService, SessionService, SETTINGS, $rootScope, $parse, $state, $stateParams, $cookies, $cookieStore, noty, $anchorScroll, $window, $q, $interval) {
	
	var d = new Date();
	//$cookieStore.put('tz_offset', d.getTimezoneOffset());
	//$cookies.tz_offset = d.getTimezoneOffset();
	$.cookie('tz_offset', d.getTimezoneOffset(), {path: '/' });
	
	$scope.isAuthenticated = AuthService.isAuthenticated;
	$scope.session = SessionService;
	$scope.baseUrl = SETTINGS.baseUrl;
	$scope.state = $state;
	$scope.prevState = $state;
	$scope.passwMinlength = SETTINGS.passwMinlength;
	$scope.passwMaxlength = SETTINGS.passwMaxlength;
	$scope.cookieUser = $cookieStore.get('user');
	$scope.bgClass = 'bg_pic';
	$scope.appTitle = SETTINGS.appTitle;
	
	$scope.accGroupAdmin			= SETTINGS.accGroupAdmin;
	/*
	$scope.accGroupOrganizations	= SETTINGS.accGroupOrganizations;
	$scope.accGroupSponsors			= SETTINGS.accGroupSponsors;
	$scope.accGroupGuardians		= SETTINGS.accGroupGuardians;
	*/
	
	$scope.isAuthenticated();
	$scope.session.setAnonymousCode();
	
	//noty.show('Test message','success'); //'error', 'warning', 'information'
	
	$scope.standalone = null;
	
	$scope.$on('$stateChangeSuccess', 
		function(event, toState, toParams, fromState, fromParams){ 
	    //event.preventDefault(); 
	    // transitionTo() promise will be rejected with 
	    // a 'transition prevented' error
		//$log.debug('stateChangeSuccess: ', toState);
		
		$scope.standalone = (toState.data && !toState.data.standalone ? false : true);
        //$log.debug('__________$scope.standalone = ', $scope.standalone);
		
		if (toState.name == 'reset_password') {
			if (!$rootScope.csrf && $scope.state.params.code) 
				AuthService.forgottenPasswordCheck($scope.state.params.code)
				.then(function(response){
					if (response.result) {
						$rootScope.csrf = response.result.csrf;
					}
					else {
						$scope.resetPasswordResult = !!response.result;
						if (response.error) 
							$scope.errMessage = response.error.message;
					}
				});	
		}
		/*
		var authStates = ['login', 'registration_confirmed', 'forgot_password', 'reset_password'];
		if (authStates.indexOf(toState.name) > -1)
			$scope.bgClass = 'bg_2';
		else 
			$scope.bgClass = toState.name;
		*/
		$scope.prevState = fromState;
	});
	
	// --------------------------------- ## 
	$rootScope.$on('$stateChangeStart', 
		function(event, toState, toParams, fromState, fromParams){ 
		$log.debug('toState.name: ', toState.name);
		$log.debug('cookie user: ', $cookieStore.get('user'));
		$log.debug('cookie tz_offset: ', $cookieStore.get('tz_offset'));
        $log.debug('toParams  ', toParams);
        /*
        if (toState.name == 'organization_profile') {
			event.preventDefault(); 
			AuthService.isAuthenticated().then(function(result){
				$log.debug('isAuthenticated: ', result);
				if (result && SessionService.user && (
						( SessionService.user.userRole == SETTINGS.accGroupOrganizations && ( !toParams.org_tag || SessionService.user.accountMap.organizations_tag == toParams.org_tag) ) 
						|| ( SessionService.user.userRole == SETTINGS.accGroupAdmin && toParams.org_tag )
					)
				) {
					if (!toParams.org_tag) 
						toParams.org_tag = SessionService.user.accountMap.organizations_tag;
					$state.go('organization_private_profile', toParams);
					$log.debug('SessionService.user: ', SessionService.user);
				}
				else {
					//$state.go('organization_public_profile', toParams);
					//$state.go('campaign_public_profile', {camp_tag: toParams.org_tag});
					$state.go('organizations_search', {key: toParams.org_tag});
					$log.debug('SessionService.user: ', SessionService.user);
				}
			});
		}
		else if (toState.name == 'profile') {
			event.preventDefault(); 
			AuthService.isAuthenticated().then(function(result){
				$log.debug('isAuthenticated: ', result);
				if (result && SessionService.user && SessionService.user.userRole == SETTINGS.accGroupGuardians) {
					$state.go('guardian_profile', toParams);
				}
				else if (result && SessionService.user && SessionService.user.userRole == SETTINGS.accGroupOrganizations) {
					$state.go('organization_profile', toParams);
				} 
			});
		}
		else if (toState.name == 'organization_private_profile') {
			//event.preventDefault(); 
			AuthService.isAuthenticated().then(function(result){
				$log.debug('isAuthenticated: ', result);
				if (result && SessionService.user && (
						( SessionService.user.userRole == SETTINGS.accGroupOrganizations && ( !toParams.org_tag || SessionService.user.accountMap.organizations_tag == toParams.org_tag) ) 
						|| ( SessionService.user.userRole == SETTINGS.accGroupAdmin && toParams.org_tag )
					)
				) {
					if (!toParams.org_tag) 
						toParams.org_tag = SessionService.user.accountMap.organizations_tag;
					//$state.go('organization_private_profile', toParams);
					$log.debug('SessionService.user: ', SessionService.user);
				}
				else {
					event.preventDefault(); 
					//$state.go('organization_public_profile', toParams);
					//$state.go('campaign_public_profile', {camp_tag: toParams.org_tag});
					$state.go('organizations_search', {key: toParams.org_tag});
					$log.debug('SessionService.user: ', SessionService.user);
				}
			});
		}
		else */if (toState.name == 'logout') {
			event.preventDefault(); 
			$scope.logout();
		}	
		
	});
	
	$scope.logout = AuthService.logout;
	
	$rootScope.fileinputInit = FileInputService.fileinputInit;
	$rootScope.fileinputInitHandlers = FileInputService.fileinputInitHandlers;
    
    $scope.getScope = function(selector){
	    return angular.element(selector).scope();
	};
	
	$scope.isset = function(anyvar) {
		return !!anyvar;
	};
	
	$scope.scrollTo = function(id) {
	     $location.hash(id);
	     $anchorScroll();
	}
	
	$rootScope.validate = function (form) {
		var deferred = $q.defer();
		
		//if (OrganizationService.validate) {
			if (typeof form == 'string') 
				form = $parse(form)($scope);
			$log.debug('form = ', form);
			if (form) {
				var formPendingPromise = $interval(function() {
					if ( ! form.$pending) {
						$log.debug('~~~ form.$valid = ', form.$valid);
						$scope.$broadcast('show-errors-check-validity');
						deferred.resolve(form.$valid);
						$interval.cancel(formPendingPromise);
					}
					
				}, 100);	
			/*}
			else deferred.resolve(true);*/
		}
		else deferred.resolve(true);
		
		return deferred.promise;
	}
	
	$rootScope.go = function(nextStateName, stateParams, form, event) {
		if (event) 
			angular.element(event.target).focus();
		var nextState = $scope.state.get(nextStateName);
		
		//$log.debug('------- nextState = ', nextState);
		//$log.debug('--- stateParams = ', stateParams);
		if (!stateParams) {
			stateParams = {};
			//stateParams.action = '';
		}
		/*
		if ($scope.editMode) {
			stateParams.tag = $scope.state.params.tag;
			stateParams.action = '';
		}
		else if ($scope.addCampaignMode) {
			stateParams.tag = $scope.state.params.tag;
			stateParams.action = 'add';
		}
		*/
		$rootScope.validate(form).then(function(formValidity){
			if (formValidity){
		      $rootScope.$state.go(nextStateName, stateParams);
			}
		});
	};
	
	$rootScope.showErrorsReset = function(){
		$scope.$broadcast('show-errors-reset');
	};
	
}]);