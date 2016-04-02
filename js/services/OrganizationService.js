App
.factory('OrganizationService', 
function($http, $log, $window, $q, $timeout, $parse, $filter, SessionService, FileUploadService, LocationService, SETTINGS){
		
		var organizationService = {
				
				validate: true,
				testinfo: false,
				verifyPhone: true,
				
				organization: {},
				account: {},
				campaign: {},
				
				rawRegistrationData: {
					organization: {},
					account: {},
					campaign: {}
				},
				
				modelsToSkip: [],
				defaults: {},
				
				registrationData: {
					data: {
						organization: {},
						account: {},
						campaign: {}
					},
					files: {}
				},
				
				themeList: [
			                    { name:'Crowd Roaring Red', color :'198,12,48', tag: 'crowd_roaring_red', isDark: true}, 
			                    { name:'Round Robin Royal', color :'0,50,121', tag: 'round_robin_royal', isDark: true}, 
			                    { name:'Gridiron Green', color :'0,131,72', tag: 'gridiron_green', isDark: true}, 
			                    { name:'Beat the Buzzer Black', color :'0,0,0', tag: 'beat_the_buzzer_black', isDark: true}, 
			                    { name:'No-hitter Navy', color :'0,34,68', tag: 'no_hitter_navy', isDark: true}, 
			                    { name:'Power Play Purple', color :'85,37,130', tag: 'power_play_purple', isDark: true}, 
			                    { name:'Match Point Maroon', color :'151,35,63', tag: 'match_point_maroon', isDark: true}, 
			                    { name:'Over the Fence Orange', color :'255,127,0', tag: 'over_the_fence_orange', isDark: false}, 
			                    { name:'Set & Spike Silver', color :'165,172,175', tag: 'set_spike silver', isDark: false}, 
			                    { name:'Bases Loaded Blue', color :'108,173,223', tag: 'bases_loaded_blue', isDark: false}, 
			                    { name:'Go to the Goal Gold', color :'253,185,39', tag: 'go_to_the_goal_gold', isDark: false}
			                ]
		};
		
		organizationService.campTypeSeasonFundraising 	= SETTINGS.campTypeSeasonFundraising;
		organizationService.campTypeFundraising 		= SETTINGS.campTypeFundraising;
		organizationService.campTypeSponsorship 		= SETTINGS.campTypeSponsorship;
        
    	organizationService.campTypeSixWeek = SETTINGS.campTypeSixWeek;
    	organizationService.campTypeThreeWeek = SETTINGS.campTypeThreeWeek;
        organizationService.campTypeOpenEnded = SETTINGS.campTypeOpenEnded;
        
		
		organizationService.campStatusInitial	= SETTINGS.campStatusInitial;
		organizationService.campStatusSuspended	= SETTINGS.campStatusSuspended;
		organizationService.campStatusLaunched	= SETTINGS.campStatusLaunched;
		organizationService.campStatusClosed	= SETTINGS.campStatusClosed;
				
		organizationService.init = function () {
			
			organizationService.organization = {};
			organizationService.account = {};
			organizationService.campaign = {};
			
			organizationService.rawRegistrationData.organization = {
					'name': '',//'', // 'Boston Blades',
					'city': '',//'', // 'Boston',
					'state': '',//'', // 'MA',
					'zip': '',//'', // '12345',
					'address_line1': '',//'', //'Address line 1',
					'address_line2': '',//'', //'Address line 2',
					'phone': '',//'', //'1234567890',
					'web_logo': '', //SETTINGS.baseUrl + '/api/files/org/1/web_logo.jpeg',
					'registration_close_date': null, //'2015-05-05',
					'files': {},
                    'tag' : '',
                    mailCollection: [],
                    'tmp': {
						mailCollection: [],
						contacts: []
                    }
                    /*'share_promise' :
                    {
                        'facebook' : false,
                        'twitter' : false
                    },*/
			};
			organizationService.rawRegistrationData.account = {
					'country_calling_code': SETTINGS.countryCallingCode,
					'contact_first_name':  '',//'', // 'bbb',
					'contact_last_name': '',//'', // 'mmm',
					'contact_phone': '',//'', // '6556345577',
					'contact_email':'',//'', // 'bbb@mail.com',
					'password': '',//'', // '123'
					'tmp': {}
			};
			organizationService.rawRegistrationData.campaign = {
					'campaign_name': '',//'',
					'campaign_description': '',//'',
					'cost': null,
					'players_number': '', 
					'campaign_type': SETTINGS.campTypeSixWeek,
					'team_store_flag':'1',
					'donations_flag': '1',
					'start_date': null,
					'end_date': null,
					'status': SETTINGS.campStatusLaunched,
					'files': {
					   'logos' : [],
					},
                    'imageCount' : 0,
                    'imageCountMax' : 2,
					'season_start_date': null,
					'season_end_date': null,
					'campaign_start_date': null,
					'campaign_end_date': null,
					'v2_flag': 1,
					'tmp': {}
			};
			
			organizationService.defaults.campaign = {
					'campaign_name': 'Default Fundraising Campaign',
					'campaign_description': 'Default Fundraising Campaign to help collect money for team activities',
					'cost': 0,
					'players_number': '', // 11,
					'campaign_type': SETTINGS.campTypeFundraising,
					'team_store_flag': '1',
					'donations_flag': '1',
					'default_flag': '1',
					'start_date': null, //  '2015-04-05',
					'end_date': null, //  '2015-06-05',
					'status': SETTINGS.campStatusLaunched,
					'files': {}
			};
			
			if (organizationService.testinfo) {
				organizationService.rawRegistrationData.organization = {
						'name':  'AB5',
						'city':  'Boston',
						'state':  'MA',
						'zip':  '12345',
						'address_line1': 'Address line 1',
						'address_line2': 'Address line 2',	
						'phone': '0000000000', //'0960727224',
						'web_logo': '', //SETTINGS.baseUrl + '/api/files/org/1/web_logo.jpeg',
						'registration_close_date': null, //'2015-05-05',
						'files': {},
	                    'tag' : 'ab5',
	                    mailCollection: [],
	                    'tmp': {
							mailCollection: [],
							contacts: []
						}
	                    /*'share_promise' :
	                    {
	                        'facebook' : false,
	                        'twitter' : false
	                    }*/
				};
				organizationService.rawRegistrationData.account = {
						'country_calling_code': SETTINGS.countryCallingCode,
						'contact_first_name':  'Aaa',
						'contact_last_name':  'Bbbbbbbb',
						'contact_phone':  '0000000000', //'0960727224',
						'contact_email':  'ab5@mail.com',
						'password':  '111',
						'tmp': {}
				};
				organizationService.rawRegistrationData.campaign = {
						//'campaigns_id': 265,
						'tag': '',
						'campaign_name': 'Fundraising Campaign',
						'campaign_description': '',//'Fundraising Campaign to help collect money for team activities',
						'cost': 20000,
						'players_number': '11',
						'campaign_type': SETTINGS.campTypeOpenEnded,
						'team_store_flag': '1',
						'donations_flag': '1',
						'default_flag': '1',
						'sport_type': 'Badminton',
						'start_date': '2015-10-11',
						'end_date': '2015-11-11',
						'status': SETTINGS.campStatusLaunched,
						'files': {
						   'logos' : [],
						},
	                    'imageCount' : 0,
	                    'imageCountMax' : 2,
						'season_start_date': '2015-10-01',
						'season_end_date': '2015-12-01',
						'campaign_start_date': '2015-10-11',
						'campaign_end_date': '2015-11-11',
						'v2_flag': 1,
						'tmp': {}
				};
			}
			
			organizationService.modelsToSkip = [];
			
			organizationService.registrationData = {
				data: {
					organization: {},
					account: {},
					campaign: {}
				},
				files: {}
			};
		};
		organizationService.init();
		
		organizationService.request = function (url, params) {
			
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
		
		organizationService.prepareRegistrationData = function(organization, account, campaign) {
			/*
			if (organizationService.modelsToSkip && angular.isArray(organizationService.modelsToSkip)) {
				angular.forEach(organizationService.modelsToSkip, function(value, key) {
                	//$log.debug(key, value);
					var model = $parse(value)(organizationService); //.registrationData.data
					var modelDefault = $parse(value)(organizationService.defaults);
					//$log.debug(model);
					//$log.debug(modelDefault);
					if (angular.isDefined(model)) {
						if (angular.isDefined(modelDefault))
							angular.copy(modelDefault, model);
						else 
							model = {};
					}
				});
			}
			*/
			if ( ! organization) organization = organizationService.rawRegistrationData.organization;
			if ( ! account) account = organizationService.rawRegistrationData.account;
			if ( ! campaign) campaign = organizationService.rawRegistrationData.campaign;
			
			account.country_calling_code = SETTINGS.countryCallingCode;
			
			if (campaign.campaign_type == SETTINGS.campTypeSeasonFundraising) {
				campaign.start_date = campaign.season_start_date ? campaign.season_start_date : null;
				campaign.end_date = campaign.season_end_date ? campaign.season_end_date : null;
			}
			else {
				campaign.start_date = campaign.campaign_start_date ? campaign.campaign_start_date : null;
				campaign.end_date = campaign.campaign_end_date ? campaign.campaign_end_date : null;
			}
			
			campaign.team_store_flag = (campaign.files && campaign.files.store_logo || campaign.store_logo) ? 1 : 0;
			
			/*
			$log.debug('prepareRegistrationData: organization = ', organization);
			$log.debug('prepareRegistrationData: account = ', account);
			$log.debug('prepareRegistrationData: campaign = ', campaign);
			*/
			var skipCampaign = (organizationService.modelsToSkip && organizationService.modelsToSkip.indexOf('campaign') > -1) ? true : false;
			
			var regData = {
				organization: angular.copy(organization),
				account: angular.copy(account),
				campaign: ( skipCampaign ? 
							angular.copy(organizationService.defaults.campaign) : 
							(campaign.campaign_name ? angular.copy(campaign) : {})	
							)
			};
			
			if ( ! regData.campaign.tag && regData.campaign.campaign_name)
				regData.campaign.tag = regData.organization.tag + '_' + $filter('normalize')(regData.campaign.campaign_name);
			
			var files = {};
			if (organization.files) {
				angular.forEach(organization.files, function(file, key) {
					files['org_' + key] = file;
					var filename = file ? file.name : null;
					regData.organization[key] = filename;
				});
			}
			if ( ! skipCampaign && campaign.files) {
				angular.forEach(campaign.files, function(file, key) {
					//$log.debug(key, ': file = ', file);
					
					if (file.removed) return;
					
					if (angular.isArray(file)) {
						angular.forEach(file, function(fileitem, index) {
							if ( ! angular.isObject(fileitem)) return;
							var filekey = 'camp_' + key + '__' + index;
							files[filekey] = fileitem;
							var filename = fileitem ? fileitem.name : null;
							//regData.campaign[filekey] = filename;
							if (fileitem.thumbUrl)
								regData.campaign[filekey + '_thumb'] = fileitem.thumbUrl;
							if (regData.campaign.main_logo_index == index)
								regData.campaign.main_logo_index = filekey;
						});
					}
					else if (angular.isObject(file)){
						files['camp_' + key] = file;
						var filename = file ? file.name : null;
						regData.campaign[key] = filename;
						if (file.thumbUrl)
							regData.campaign['camp_' + key + '_thumb'] = file.thumbUrl;
					}
				});
			}
			//$log.debug('files = ', files);
			
			/*if (regData.campaign.campaign_type == SETTINGS.campTypeFundraising) {
				regData.campaign.start_date = null;
				regData.campaign.end_date = null;
				regData.campaign.start_date_formatted = null;
				regData.campaign.end_date_formatted = null;
			}
			else {*/
				if (regData.campaign.start_date) {
					//regData.campaign.start_date_formatted = moment(new Date(regData.campaign.start_date)).format('M/DD/YYYY');
					regData.campaign.start_date = moment(new Date(regData.campaign.start_date)).format('YYYY-MM-DD');
				}
				if (regData.campaign.end_date) {
					//regData.campaign.end_date_formatted = moment(new Date(regData.campaign.end_date)).format('M/DD/YYYY');
					regData.campaign.end_date = moment(new Date(regData.campaign.end_date)).format('YYYY-MM-DD');
				}
			//}
			
			if (regData.organization.registration_close_date) {
				if (skipCampaign || regData.campaign.campaign_type === organizationService.campTypeFundraising) 
					delete regData.organization.registration_close_date;
				else {
					//regData.organization.registration_close_date_formatted = moment(new Date(regData.organization.registration_close_date)).format('M/DD/YYYY');
					regData.organization.registration_close_date = moment(new Date(regData.organization.registration_close_date)).format('YYYY-MM-DD');
				}
			}
			
			for (i = 0; i < LocationService.regions.length; i++) {
				if (regData.organization.state === LocationService.regions[i].code) {
					regData.organization.state_name = LocationService.regions[i].name;
					break;
				}
			}
			
			organizationService.registrationData.data = regData;
			organizationService.registrationData.files = files;
			
			delete organizationService.registrationData.data.organization.files;
			delete organizationService.registrationData.data.campaign.files;
			
			delete organizationService.registrationData.data.organization.tmp;
			delete organizationService.registrationData.data.account.tmp;
			delete organizationService.registrationData.data.campaign.tmp;
			
			//$log.debug('prepareRegistrationData: registrationData = ', organizationService.registrationData);
		};
		
		organizationService.ftpputCampStoreImg = function (orgId, campId, newCampaignFlag) {
			var params = {owner_id: orgId, campaign_id: campId, new_campaign_flag: newCampaignFlag};
			return organizationService.request(SETTINGS.apiUrlFtpputCampStoreImg, params);
		};
		
		organizationService.register = function (organization, account, campaign, registrationApiUrl) {
						
			registrationApiUrl = registrationApiUrl ? registrationApiUrl : SETTINGS.apiUrlOrgRegistration;
			organizationService.prepareRegistrationData(organization, account, campaign);
			
			$log.debug('organizationService.registrationData.files = ', organizationService.registrationData.files);
			$log.debug('organizationService.registrationData.data = ', organizationService.registrationData.data);
			
			var responsePromise = FileUploadService.uploadData(
										organizationService.registrationData.files, 
										organizationService.registrationData.data, 
										registrationApiUrl
									);
			responsePromise.then(function(response){
				if (response.result) {
					organizationService.ftpputCampStoreImg(response.result.organization_id, response.result.campaign_id, true);
					organizationService.init();
				}
			});
			return responsePromise;
		};
		
		organizationService.prepareCampaignData = function() {
			
			var files = {};
			
			angular.forEach(organizationService.campaign.files, function(file, key) {
				//$log.debug(key, ': file = ', file);
				
				if (key == 'logos' || file.removed) return;
				
				if (angular.isArray(file)) {
					angular.forEach(file, function(fileitem, index) {
						if ( ! angular.isObject(fileitem)) return;
						var filekey = 'camp_' + key + '__' + index;
						files[filekey] = fileitem;
						var filename = fileitem ? fileitem.name : null;
						//organizationService.campaign[filekey] = filename;
						if (fileitem.thumbUrl)
							organizationService.campaign[filekey + '_thumb'] = fileitem.thumbUrl;
					});
				}
				else if (angular.isObject(file)) {
					files['camp_' + key] = file;
					var filename = file ? file.name : null;
					organizationService.campaign[key] = filename;
					if (file.thumbUrl)
						organizationService.campaign['camp_' + key + '_thumb'] = file.thumbUrl;
				}
			});
						
			angular.forEach(organizationService.campaign.logos, function(img, key) {
				//$log.debug(key, ': img = ', img);
				var filekey = 'camp_logos__' + key;
				
				if (typeof img.files_index != 'undefined') {
					var fileitem = organizationService.campaign.files.logos[img.files_index];
					if ( ! angular.isObject(fileitem)) return;
					files[filekey] = fileitem;
					if (fileitem.thumbUrl)
						organizationService.campaign[filekey + '_thumb'] = fileitem.thumbUrl;
				}
				
				if (organizationService.campaign.main_logo_index == key) {
					if (typeof img.campaigns_images_id != 'undefined')
						organizationService.campaign.main_logo_index = img.campaigns_images_id;
					else if (typeof img.files_index != 'undefined') {
						organizationService.campaign.main_logo_index = filekey;
					}
				}
			});
			//$log.debug('files = ', files);
			
			if (organizationService.campaign.campaign_type == SETTINGS.campTypeSeasonFundraising) {
				organizationService.campaign.start_date = organizationService.campaign.season_start_date ? organizationService.campaign.season_start_date : null;
				organizationService.campaign.end_date = organizationService.campaign.season_end_date ? organizationService.campaign.season_end_date : null;
			}
			else {
				organizationService.campaign.start_date = organizationService.campaign.campaign_start_date ? organizationService.campaign.campaign_start_date : null;
				organizationService.campaign.end_date = organizationService.campaign.campaign_end_date ? organizationService.campaign.campaign_end_date : null;
			}
			
			/*if (organizationService.campaign.campaign_type == SETTINGS.campTypeFundraising) {
				organizationService.campaign.start_date = null;
				organizationService.campaign.end_date = null;
			}
			else {*/
				var startDate = new Date(organizationService.campaign.start_date);
				var endDate = new Date(organizationService.campaign.end_date);
				
				organizationService.campaign.start_date = moment(startDate).format('YYYY-MM-DD');
				organizationService.campaign.end_date = moment(endDate).format('YYYY-MM-DD');
			//}
			
			organizationService.campaign.default_flag = organizationService.campaign.campaign_name.search('Default') > -1 ? 1 : 0;
			
			organizationService.campaign.team_store_flag = (organizationService.campaign.files.store_logo  || organizationService.campaign.store_logo) ? 1 : 0;
			
			var campaignData = {
					data: {
						owner_id: organizationService.organization.organizations_id,
						campaign: angular.copy(organizationService.campaign)
					},
					files: files
			};
			
			campaignData.data.campaign.files = null;
			/*
			angular.forEach(campaignData.data.campaign.logos, function(img, key) {
				//$log.debug(key, ': img = ', img);
				if (campaignData.data.campaign.main_logo_index == key) {
					if (typeof img.campaigns_images_id != 'undefined')
						campaignData.data.campaign.main_logo_index = img.campaigns_images_id;
					else if (typeof img.files_index != 'undefined') {
						//var file = campaignData.data.campaign.files.logos[img.files_index];
						var filekey = 'camp_logos_' + img.files_index;
						campaignData.data.campaign.main_logo_index = filekey;
					}
				}
			});
			*/
			$log.debug('campaignData = ', campaignData);
			
			return campaignData;
		};
		
		///////////////////////////////------------------------------------------------------------------------------------<<<
                
		organizationService.saveCampaign = function (newCampaignFlag) {
						
			var campaignData = organizationService.prepareCampaignData();
			
			//$log.debug('campaignData.files = ', campaignData.files);
			//$log.debug('campaignData.data = ', campaignData.data);
			
			var apiUrl = newCampaignFlag ? SETTINGS.apiUrlOrgCampaignAdd : SETTINGS.apiUrlOrgCampaignUpdate;
			
			var responsePromise = FileUploadService.uploadData(
						campaignData.files, 
						campaignData.data, 
						apiUrl
					);
			responsePromise.then(function(response){
				if (response.result) {
					var campaign_id = response.result.campaign_id;
					organizationService.ftpputCampStoreImg(campaignData.data.owner_id, campaign_id, newCampaignFlag);
				}
			});
			return responsePromise;
		};
		
		organizationService.saveCampaignStoreData = function (orgId, campaignId, storeUrl, storeId, storeName, store_donation_percent) {
			var apiUrl = SETTINGS.apiUrlOrgCampaignUpdate;
			var params = {owner_id: orgId, campaign: {
												campaigns_id: campaignId,
												team_store_url: storeUrl,
												inksoft_store_id: storeId,
												store_name: storeName,
												store_donation_percent : store_donation_percent
											}, selective_update: true};
			return organizationService.request(apiUrl, params);
		};
		
		organizationService.saveCampaignOrdNumber = function (orgId, campaignId, ordNumber, status) {
			var apiUrl = SETTINGS.apiUrlOrgCampaignUpdate;
			var params = {owner_id: orgId, campaign: {
												campaigns_id: campaignId,
												display_ord_number: ordNumber,
												display_ord_number_priority: 0,
												status: status
											}, selective_update: true};
			return organizationService.request(apiUrl, params);
		};
		
		organizationService.closeCampaign = function (campId) {
			
			var params = {
				owner_id: organizationService.organization.organizations_id,
				//campaign: {campaigns_id: campId, status: SETTINGS.campStatusClosed}
				campaigns_id: campId, 
				status: SETTINGS.campStatusClosed
			};
			
			var deferred = $q.defer();
				
			$http.post(SETTINGS.apiUrlOrgCampaignSetStatus, params)
		      .success(function(response, status, headers, config) {
		    	  angular.forEach(organizationService.organization.campaigns, function(campaign, key){
		    		  if (campaign.campaigns_id == campId) {
		    			  campaign.status = SETTINGS.campStatusClosed;
		    		  }
		    	  });
		    	  deferred.resolve(response);
			  })
			  .error(function(response, status, headers, config) {
				  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		organizationService.orgNameUniquenessCheck = function (orgName, orgId) {
			var params = {org_name: orgName, organizations_id: orgId};
			return organizationService.request(SETTINGS.apiUrlOrgNameUniquenessCheck, params);
		};
		
		organizationService.orgTagUniquenessCheck = function (orgTag, orgId) {
			var params = {org_tag: orgTag, organizations_id: orgId};
			return organizationService.request(SETTINGS.apiUrlOrgTagUniquenessCheck, params);
		};
		
		organizationService.campTagUniquenessCheck = function (campTag, campId) {
			var params = {camp_tag: campTag, campaigns_id: campId};
			return organizationService.request(SETTINGS.apiUrlCampTagUniquenessCheck, params);
		};
		
		organizationService.campNameUniquenessCheck = function (orgId, campName, campId) {
			var params = {organizations_id: orgId, camp_name: campName, campaigns_id: campId};
			return organizationService.request(SETTINGS.apiUrlCampNameUniquenessCheck, params);
		};
		
		organizationService.storeIdUniquenessCheck = function (storeId) {
			var params = {store_id: storeId};
			return organizationService.request(SETTINGS.apiUrlStoreIdUniquenessCheck, params);
		};
		
		organizationService.storeNameUniquenessCheck = function (storeName) {
			var params = {store_name: storeName};
			return organizationService.request(SETTINGS.apiUrlStoreNameUniquenessCheck, params);
		};
				
		organizationService.getOrgList = function (params) {
			if ( ! params) params = {limit: null};
			var url = params.csv ? SETTINGS.apiUrlOrgListCsv : SETTINGS.apiUrlOrgList;
			
			return organizationService.request(url, params);
		};
		
		organizationService.getOrgProfile = function (orgTag, includes, filters, includeClosedCampaigns) {
			
			if ( ! includes) includes = [];
			if ( ! filters) filters = [];
			if (orgTag) {
				for (var i in filters) {
					if (filters[i].name == 'tag') {
						delete filters[i];
						break;
					}
				}
				var orgTagFilter = {
					name: 'tag', 
					value: orgTag,
					option: 'equal'
				};
				filters.push(orgTagFilter);
			}
			var params = {
				filters: filters,
				includes: includes, 
				campaigns_status: (includeClosedCampaigns ? null : SETTINGS.campStatusLaunched)
			};
			
			var deferred = $q.defer();
				
			$http.post(SETTINGS.apiUrlOrgList, params)
		      .success(function(response, status, headers, config) {
		    	  $log.debug('getOrgProfile: ', response); 
		    	  if ( response.result.data.length == 1) {
		    		  organizationService.organization = response.result.data[0];
		    		  if (organizationService.organization.accounts && organizationService.organization.accounts.length > 0)
		    			  organizationService.account = organizationService.organization.accounts[0];
		    		  //$log.debug('organizationService.organization: ', organizationService.organization); 
		    		  //$log.debug('organizationService.account: ', organizationService.account);
		    		  deferred.resolve(response);
				  }
		    	  else {
		    		  organizationService.organization = {};
		    		  for (var i in params.filters) {
							if (params.filters[i].name == 'status') {
								delete params.filters[i];
								break;
							}
					  }
		    		  params.filters.push({
		  				name: 'status', 
						value: SETTINGS.orgStatusDeleted,
						option: 'equal'
		    		  });
		    		  params.includes = [];
		    		  $http.post(SETTINGS.apiUrlOrgList, params)
		    		  .success(function(response2, status, headers, config) {
		    			  $log.debug('deleted getOrgProfile: ', response); 
		    			  if ( response2.result.data.length == 1) {
		    				  response.org_deleted = 1;
		    			  }
		    			  deferred.resolve(response);
		    		  })
		    		  .error(function(response, status, headers, config) {
						  deferred.resolve(response);
					  });
		    	  }
		    	  
			  })
			  .error(function(response, status, headers, config) {
				  organizationService.organization = {};
				  deferred.resolve(response);
			  });
			return deferred.promise;
		};
		
		organizationService.uploadRawOrgMembersList = function (orgId, files) {
			return FileUploadService.uploadData(
					files, //{org_members_list: file}
					{org_id: orgId}, 
					SETTINGS.apiUrlUploadRawOrgMembersList
				);
		}
		organizationService.uploadOrgMembersList = function (importLogId, orgId, files) {
			return FileUploadService.uploadData(
					files, //{org_members_list: file}
					{import_log_id: importLogId, org_id: orgId}, 
					SETTINGS.apiUrlUploadOrgMembersList
				);
		}
		
		/////////////////////////////////////////////////
		
		organizationService.getCampaign = function (campaignId, campaignTag, orgTag) {
			return organizationService.request(SETTINGS.apiUrlOrgCampaignGet, {campaigns_id: campaignId, camp_tag: campaignTag, org_tag: orgTag});
		};
		
		organizationService.getCampaignsList = function (params) {
			if ( ! params) params = {limit: null};
			return organizationService.request(SETTINGS.apiUrlOrgCampaignList, params);
		};
		
		/////////////////////////////////////////////////
		
		organizationService.donation = {
				supporter_public_access_flag: 1,
				amount_public_access_flag: 1
		};
		
		organizationService.donationTestCard = {
				'card_number': '4242424242424242',
				'cvc': '564',
				'exp_month': '12',
				'exp_year': '17'
		};
		
		organizationService.donate = function () {
			var params = {donation: organizationService.donation};
			return organizationService.request(SETTINGS.apiUrlDonate, params);
		};
		
		organizationService.getDonations = function (campaignId, donationFields, params) {
			/*var params = {
				filters: [{
					name: 'campaigns_id', 
					value: campaignId,
					option: 'equal'
				}]
			};*/
			if ( ! params) params = {limit: null};
			params.campaign_id = campaignId;
			if (donationFields) 
				params.fields = donationFields;
			
			d = new Date();
			params.tz_offset = d.getTimezoneOffset();
			
			//console.log(params);
			//SETTINGS.apiUrlDonationList
			return organizationService.request(SETTINGS.apiUrlCampaignDonations, params);
		};

		organizationService.getDonatorsCount = function (campaign_Id) {
			var params = {campaign_id: campaign_Id};
			return organizationService.request(SETTINGS.apiUrlCampaignDonatorsCount, params);
		};
		
		organizationService.addDonationLike = function (donation_likes_id, donations_id, userid, anonymousCode) {
			var params = {donation_likes_id: donation_likes_id, donations_id: donations_id, acc_users_id: userid, anonymous_code: anonymousCode};
			//$log.debug('params', params);
			return organizationService.request(SETTINGS.apiUrlAddDonationLike, params);
		};

		
		organizationService.getDonationsTotal = function (campaign_Id) {
			var params = {campaign_id: campaign_Id};
			return organizationService.request(SETTINGS.apiUrlCampaignDonationsTotal, params);
		};
		
		organizationService.deleteDonation = function (donationId) {
			var params = {donations_id: donationId};
			return organizationService.request(SETTINGS.apiUrlDonationDelete, params);
		};
		
		organizationService.updateManualDonationDate = function (donationId, donationDate) {
			var params = {donations_id: donationId, donation_date: donationDate};
			return organizationService.request(SETTINGS.apiUrlDonationDateUpdate, params);
		};
		
		organizationService.updateDonationCampaign = function (donationId, campaignId) {
			var params = {donations_id: donationId, campaigns_id: campaignId};
			return organizationService.request(SETTINGS.apiUrlDonationCampaignUpdate, params);
		};
		
		/////////////////////////////////////////////////
		
		organizationService.shareByEmail = function (sharerEmail, sharerName, recipients, linkToShare, orgTag, campTag) {
			var params = {
					sharer_email: sharerEmail, 
					sharer_name: sharerName, 
					recipients: recipients, 
					link_to_share: linkToShare,
					org_tag: orgTag,
                    camp_tag : campTag
			};
			return organizationService.request(SETTINGS.apiUrlOrgShareByEmail, params);
		};
		
		organizationService.deleteOrganization = function (organizationId) {
			var params = {organizations_id: organizationId};
			return organizationService.request(SETTINGS.apiUrlOrgDelete, params);
		};
		
		organizationService.deleteCampaign = function (orgId, campId) {
			var params = {owner_id: orgId, campaigns_id: campId};
			return organizationService.request(SETTINGS.apiUrlOrgCampaignDelete, params);
		};
		
		organizationService.setOrgStatus = function (organizationId, status) {
			var params = {organizations_id: organizationId, status: status};
			return organizationService.request(SETTINGS.apiUrlOrgSetStatus, params);
		};
		
		organizationService.setOrgFlag = function (organizationId, flagName, flagValue) {
			var params = {organizations_id: organizationId, flag_name: flagName, flag_value: flagValue};
			return organizationService.request(SETTINGS.apiUrlOrgSetFlag, params);
		};
		
		organizationService.setCampaignStatus = function (orgId, campId, status) {
			var params = {owner_id: orgId, campaigns_id: campId, status: status};
			return organizationService.request(SETTINGS.apiUrlOrgCampaignSetStatus, params);
		};
		
		organizationService.getNewWidgetRefId = function () {
			var params = {};
			return organizationService.request(SETTINGS.apiUrlWidgetRefId, params);
		};
		
		organizationService.getWidgetStat = function (params) {
			if ( ! params) params = {limit: null};
			return organizationService.request(SETTINGS.apiUrlWidgetStat, params);
		};
		
		organizationService.getFraudDetectionData = function (params) {
			if ( ! params) params = {limit: null};
			return organizationService.request(SETTINGS.apiUrlFraudDetection, params);
		};
		
		organizationService.getPhoneManagementData = function (params) {
			if ( ! params) params = {limit: null};
			return organizationService.request(SETTINGS.apiUrlPhoneManagement, params);
		};
		
		organizationService.addPhoneManagementEntry = function (phoneNumber, phoneStatus) {
			var params = {phone_management_id: null, phone_number: phoneNumber, status: phoneStatus};
			return organizationService.request(SETTINGS.apiUrlPhoneManagementSaveEntry, params);
		};
		
		organizationService.deletePhoneManagementEntry = function (phoneManagementId) {
			var params = {phone_management_id: phoneManagementId};
			return organizationService.request(SETTINGS.apiUrlPhoneManagementDeleteEntry, params);
		};
		
		organizationService.MarketoSync = function (organizationId) {
			var params = {organizations_id: organizationId};
			return organizationService.request(SETTINGS.apiUrlOrgMarketoSync, params);
		};

		organizationService.getCampaignTheme = function (color_theme_tag) {
			var ret = { name:'', color :'#000000', tag: '', isDark: true}; 
			angular.forEach(organizationService.themeList, function(theme, key){
				//$log.debug(theme.tag,color_theme_tag,theme);
			     if (theme.tag == color_theme_tag)
			    	 ret = theme;
			    });
			return ret;
		};
		
		return organizationService;
});
