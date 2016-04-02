App
	.service('AuthService', function($window, $http, $log, $q, $timeout, $cookies, $cookieStore, noty, SessionService, SETTINGS){
		var authService = {};
				
		authService.login_old = function (credentials) {
			var deferred = $q.defer();
			
			SessionService.destroy();
			
			$http.post(SETTINGS.apiUrlLogin, credentials)
			/*
		    .then(function (response){ 
		    	$log.debug('response = ', response); 
		    	deferred.resolve(response.data);
		    });
			*/
			.success(function(response, status, headers, config) {
				
				authService.profile().then(function(response){
					deferred.resolve(response);
				});
				//deferred.resolve(response);
			})
			.error(function(response, status, headers, config) {
				deferred.resolve(response);
			});
			
			return deferred.promise;
		};
		
		authService.login = function (credentials) {
			var deferred = $q.defer();
			
			SessionService.destroy();
			
			var loginPromise; 
			
			if (credentials.jwt)
				loginPromise = $http.post(SETTINGS.apiUrlLoginJwt, credentials);
			else 
				loginPromise = $http.post(SETTINGS.apiUrlLogin, credentials);
			
			loginPromise
			/*
		    .then(function (response){ 
		    	$log.debug('response = ', response); 
		    	deferred.resolve(response.data);
		    });
			*/
			.success(function(response, status, headers, config) {
				
				authService.profile().then(function(response){
					deferred.resolve(response);
				});
				//deferred.resolve(response);
			})
			.error(function(response, status, headers, config) {
				deferred.resolve(response);
			});
			
			return deferred.promise;
		};
		  
		authService.isAuthenticated = function () {
			//return !!SessionService.userId;
			//return !!SessionService.user;
			
			var deferred = $q.defer();
			
			var user = $cookieStore.get('user');
			if (user) {
				//SessionService.create(null, user.userId, user.userRole, user.userFName, user.userLName, user.userEmail, user.userPhone, user.accountMap);
				SessionService.create(null, user);
				//deferred.resolve(!!user);
				deferred.resolve(!!SessionService.user);
			}
			else {
				authService.profile().then(function(response){
					deferred.resolve(!!SessionService.user);
				});
			}
			return deferred.promise;
		};
		
		/*
		authService.profile_old = function (refreshFlag) {
			
			var params = refreshFlag ? {refreshFlag: refreshFlag} : {};
			
			var deferred = $q.defer();
			$http.post(SETTINGS.apiUrlProfile, params)
		      .success(function(response, status, headers, config) {
		    	  $log.debug('authService.profile : ', response); 
		    	  if (response.result) {
		    		  var accountMap = {
			    		organizations_id: (response.result.organizations_id ? response.result.organizations_id : null),
			    		organizations_tag: (response.result.organizations_tag ? response.result.organizations_tag : null),
			    		sponsors_id: (response.result.sponsors_id ? response.result.sponsors_id : null),
			    		guardians_id: (response.result.guardians_id ? response.result.guardians_id : null)
			    	  };
		    		  SessionService.create(response.result.session_id, 
		    				  				response.result.user_id, 
		    				  				response.result.user_group, 
		    				  				response.result.user.first_name, 
		    				  				response.result.user.last_name, 
		    				  				response.result.user.email, 
		    				  				response.result.user.phone,
		    				  				accountMap);
		    	  }
		    	  else {
		    		  SessionService.destroy();
		    	  }
		    	  deferred.resolve(response);
		    	  //$log.debug(SessionService); 
			  })
			  .error(function(response, status, headers, config) {
				  //$log.debug(response); 
		    	  SessionService.destroy();
		    	  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		*/
		authService.profile = function (refreshFlag) {
			
			var params = refreshFlag ? {refreshFlag: refreshFlag} : {};
			
			var deferred = $q.defer();
			$http.post(SETTINGS.apiUrlProfile, params)
		      .success(function(response, status, headers, config) {
		    	  $log.debug('authService.profile : ', response); 
		    	  if (response.result) {
		    		  var accountMap = {
			    		organizations_id: (response.result.organizations_id ? response.result.organizations_id : null),
			    		organizations_tag: (response.result.organizations_tag ? response.result.organizations_tag : null),
			    		sponsors_id: (response.result.sponsors_id ? response.result.sponsors_id : null),
			    		guardians_id: (response.result.guardians_id ? response.result.guardians_id : null)
			    	  };
		    		  var userRole = response.result.user_group;
		    		  var user = {
		    					userId: response.result.user_id,
		    					userName: response.result.user.first_name + ' ' + response.result.user.last_name,
		    					userFName: response.result.user.first_name,
		    					userLName: response.result.user.last_name,
		    					userEmail: response.result.user.email,
		    					userPhone: response.result.user.phone,
		    					userRole: angular.isArray(userRole) && userRole.length > 0 ? userRole[0] : userRole,
		    					accountMap: accountMap,
		    					jwt: response.result.jwt
		    				};
		    		  SessionService.create(response.result.session_id, user);
		    	  }
		    	  else {
		    		  SessionService.destroy();
		    	  }
		    	  deferred.resolve(response);
		    	  //$log.debug(SessionService); 
			  })
			  .error(function(response, status, headers, config) {
				  //$log.debug(response); 
		    	  SessionService.destroy();
		    	  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		authService.logout = function (redirectOnSuccess) {
			var deferred = $q.defer();
			$http.post(SETTINGS.apiUrlLogout, null)
		      .success(function(response, status, headers, config) {
		    	  //$log.debug(response); 
		    	  if (response.result) {
		    		  //OrganizationService.init();
		    		  SessionService.destroy();
		    		  
		    		  redirectOnSuccess = (typeof redirectOnSuccess == 'undefined' ? true : false);
		    		  if (redirectOnSuccess && SETTINGS.logoutRedirectUrl) {
		    			  $window.location.href = SETTINGS.logoutRedirectUrl;
		    		  }
		    	  }
		    	  deferred.resolve(response);
		      })
		      .error(function(response, status, headers, config) {
				  //$log.debug(response); 
		    	  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		authService.register = function (user) {
			
			var deferred = $q.defer();
			var result = {result: false, error: null};
			
			$http.post(SETTINGS.apiUrlRegistration, $.param(user))
		      .success(function(response, status, headers, config) {
		    	  $log.debug(response); 
		    	  result = {result: true, error: null};
		    	  deferred.resolve(result);
			  })
			  .error(function(response, status, headers, config) {
				  $log.debug('authService.register error', response); 
				  var err = (typeof response.error.message == 'string' ? response.error.message : 'Unknown error')
				  result = {result: false, error: err};
				  deferred.resolve(result);
			  });
			return deferred.promise;
		};
		
		authService.request = function (url, params) {
			
			var deferred = $q.defer();
				
			$http.post(url, params)
		      .success(function(response, status, headers, config) {
		    	  deferred.resolve(response);
			  })
			  .error(function(response, status, headers, config) {
				  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		authService.forgotPassword = function (email) {
			
			var deferred = $q.defer();
			
			$http.post(SETTINGS.apiUrlForgotPassword, {email: email})
		    .success(function(response, status, headers, config) {
				deferred.resolve(response);
			})
			.error(function(response, status, headers, config) {
				deferred.resolve(response);
			});
			
			return deferred.promise;
		};
		
		authService.forgottenPasswordCheck = function (forgottenPasswordCode) {
			
			var deferred = $q.defer();
			
			$http.post(SETTINGS.apiUrlForgottenPasswordCheck, {code: forgottenPasswordCode})
		    .success(function(response, status, headers, config) {
				deferred.resolve(response);
			})
			.error(function(response, status, headers, config) {
				deferred.resolve(response);
			});
			
			return deferred.promise;
		};
		
		authService.resetPassword = function (forgottenPasswordCode, newPassword, csrf) {
			
			var deferred = $q.defer();
			
			$http.post(SETTINGS.apiUrlResetPassword, {code: forgottenPasswordCode, new_password: newPassword, csrf: csrf})
		    .success(function(response, status, headers, config) {
				deferred.resolve(response);
			})
			.error(function(response, status, headers, config) {
				deferred.resolve(response);
			});
			
			return deferred.promise;
		};
		
		authService.confirmRegistration = function (confirmationCode) {
			
			var deferred = $q.defer();
			
			$http.post(SETTINGS.apiUrlConfirmRegistration, {confirmation_code: confirmationCode})
		    .success(function(response, status, headers, config) {
				deferred.resolve(response);
			})
			.error(function(response, status, headers, config) {
				deferred.resolve(response);
			});
			
			return deferred.promise;
		};
		
		authService.accountEmailUniquenessCheck = function (contactEmail, userId) {
			var params = {
				contact_email: contactEmail, acc_users_id: userId
			};
			
			var deferred = $q.defer();
			
			$http.post(SETTINGS.apiUrlAccEmailUniquenessCheck, params)
		      .success(function(response, status, headers, config) {
		    	  deferred.resolve(response);
			  })
			  .error(function(response, status, headers, config) {
				  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		authService.confirm = function (text) {
			
			var deferred = $q.defer();
			
			noty.callNoty({
				text: text,
				layout: 'center',
				//template: '<div class="noty_message"><span class="noty_text"></span><div class="noty_close"></div></div>',
				modal: true,
				buttons: [
					{addClass: 'btn btn-primary', text: 'Ok', onClick: function($noty) {
							// this = button element, $noty = $noty element
							$noty.close();
							//noty.callNoty({text: 'You clicked "Ok" button', type: 'success'});
							deferred.resolve(true);
						}
					},
					{addClass: 'btn btn-danger', text: 'Cancel', onClick: function($noty) {
							$noty.close();
							//noty.callNoty({text: 'You clicked "Cancel" button', type: 'error'});
							deferred.resolve(false);
						}
					}
				]
			});
			return deferred.promise;
		};
		
		authService.appSettingsSave = function (settings) {
			var params = {settings: settings};
			return authService.request(SETTINGS.apiUrlAppSettingsSave, params);
		};
		
		authService.appSettingsRead = function () {
			return authService.request(SETTINGS.apiUrlAppSettingsRead, {});
		};
		
		authService.widgetVisitCounter = function (widgetRefId) {
			$http.get(SETTINGS.apiUrlWidgetCounter + '/' + widgetRefId + '/visit', {params:{referrer: document.referrer}});
		};
		
		/*
		authService.downloadFile = function (link) {
			var deferred = $q.defer();
			$http.post(SETTINGS.backendUrlDownloadFile, {link: link})
		      .success(function(response, status, headers, config) {
		    	  //$log.debug(response); 
		    	  deferred.resolve(response);
		      })
		      .error(function(response, status, headers, config) {
				  //$log.debug(response); 
		    	  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		*/
		
		return authService;
});
