App
.controller('LoginCtrl', 
['$scope', '$window', '$log', '$state', '$rootScope', 'SETTINGS', 'AuthService', 'SessionService', '$cookies', '$cookieStore',
function ($scope, $window, $log, $state, $rootScope, SETTINGS, AuthService, SessionService, $cookies, $cookieStore) {
		
		$scope.user = {
				'username': '',
				'password': '',
				'remember': 0
		};
		
		$scope.authResult = null;
		$scope.errMessage = '';
		$scope.baseUrl = SETTINGS.baseUrl;
		
		/*
		AuthService.isAuthenticated().then(function(result){
			$log.debug('isAuthenticated: ', result);
			if (result && SessionService.user)
				$state.go('profile');
		});
		*/
		if (SessionService.user)
				$state.go('profile');
		
		$scope.login = function(credentials) {
			$scope.$broadcast('show-errors-check-validity');
			if ( ! $scope.loginForm.$valid) return;
			
			AuthService.login(credentials)
			.then(function(response){
				$scope.authResult = !!response.result; //AuthService.isAuthenticated() can be used after AuthService.profile() call
				if ($scope.authResult) {
					//$window.location.href = SETTINGS.baseUrl;
					$scope.state.go('profile');
				}
				else if (response.error) 
					$scope.errMessage = response.error.message;
			});
		};
		
		//---------------------------------
		
		$scope.forgotPasswordFlag = false;
		$scope.resetPasswordEmailResult = null;
		$scope.resetPasswordResult = null;
		
		$scope.forgotPassword = function(email) {
			
			$scope.resetPasswordEmailResult = null;
			
			$scope.$broadcast('show-errors-check-validity');
			if ( ! $scope.forgotPasswordForm.$valid) return;
			
			AuthService.forgotPassword(email)
			.then(function(response){
				$scope.resetPasswordEmailResult = !!response.result; //AuthService.isAuthenticated() can be used after AuthService.profile() call
				if (response.result) {
					$scope.errMessage = response.result.message;
					//$scope.errMessage = response.result.email_send_result.forgotten_password_code; // -------- for testing purpose
				}
				if (response.error) 
					$scope.errMessage = response.error.message;
			});
		};
		
		$scope.resetPassword = function(new_password) {
			$scope.$broadcast('show-errors-check-validity');
			if ( ! $scope.resetPasswordForm.$valid) return;
			
			AuthService.resetPassword($scope.state.params.code, new_password, $rootScope.csrf)
			.then(function(response){
				$scope.resetPasswordResult = !!response.result;
				/*if (response.result) 
					$scope.errMessage = response.result.message;
				if (response.error) {
					if (response.error.code == 1002) //ERR_MISSING_PARAMETER
						$scope.errMessage = 'Unable to reset password.';
					else 
						$scope.errMessage = response.error.message;
				}*/
				if ($scope.resetPasswordResult) 
					$scope.errMessage = 'Password successfully changed. Logged out.';
				else 
					$scope.errMessage = 'Unable to reset password.';
			});
		};
		
		////////////////////////////////////////////////////////////
		
		$scope.confirmRegistrationResult = null;
		$scope.confirmRegistrationError = null;
		$scope.confirmationUserName = '';
		
		$scope.confirmRegistration = function () {
			AuthService.confirmRegistration($scope.state.params.code)
			.then(function(response){
				$scope.confirmRegistrationResult = !!response.result;
				if ($scope.confirmRegistrationResult) {
					$scope.confirmationUserName = response.result.first_name + ' ' + response.result.last_name;
				}
				if (response.error) 
					$scope.confirmRegistrationError = response.error.message;
			});
		};
		
		$scope.go = function(nextStateName) {
			$state.go(nextStateName);
		};
		
}]);

