App
	.service('SessionService', function($log, $cookies, $cookieStore, GuidGenerator){
		/*
		this.create_old = function (sessionId, userId, userRole, userFName, userLName, userEmail, userPhone, accountMap) {
			this.id = sessionId;
			this.user = {
				userId: userId,
				userName: userFName + ' ' + userLName,
				userFName: userFName,
				userLName: userLName,
				userEmail: userEmail,
				userPhone: userPhone,
				userRole: angular.isArray(userRole) && userRole.length > 0 ? userRole[0] : userRole,
				accountMap: accountMap
			};
			
			$cookieStore.put('user', this.user);
			//$cookies.user = user;
			//$log.debug('$cookies = ', $cookieStore.get('user'));
		};
		*/
		this.create = function (sessionId, user) {
			this.id = sessionId;
			this.user = user;
			
			$cookieStore.put('user', this.user);
			//$cookies.user = user;
			//$log.debug('$cookies = ', $cookieStore.get('user'));
		};
		
		this.setAnonymousCode = function () {
			this.anonymous_code = $cookieStore.get('anonymous_code');
			if (!this.anonymous_code) {
				this.anonymous_code = GuidGenerator.getUniqueCode();
				$cookieStore.put('anonymous_code', this.anonymous_code);
			}
		}
		
		this.destroy = function () {
			this.id = null;
			this.user = null;
			
			$cookieStore.remove('user');
			//$cookies.user = null;
		};
		 
	});