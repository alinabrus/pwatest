App
.factory('EmailContactsService', 
function($http, $log, $window, $q, $timeout, $parse, SessionService, FileUploadService, LocationService, SETTINGS, YahooService){
		
		var emailContactsService = {};
        
        emailContactsService.gmail_contact = 
        {        
	        disabled : false, 
	        getList: function () {
	        	var deferred = $q.defer();
	            var config = {
	              'client_id': '984342428068-0hefi3e2d7gujt11lugn5ic2v9ugn8ql.apps.googleusercontent.com',
	              'scope': 'https://www.google.com/m8/feeds'
	            };
	            gapi.auth.authorize(config, function() {
	              var contacts = emailContactsService.gmail_contact.fetch(gapi.auth.getToken());
	              deferred.resolve(contacts);
	            });
	            return deferred.promise;
	         },
	         fetch: function (token) {
	        	 var deferred = $q.defer();
	             $.ajax({
	                    url: "https://www.google.com/m8/feeds/contacts/default/full?access_token=" + token.access_token + "&alt=json",
	                    dataType: "jsonp",
	                    success:function(data) {
	                    	var entry = data.feed.entry;
	                    	var mailCollection = [];
	                    	for(var i = 0; i < entry.length; i++)
	                    	{
	                    		if (!entry[i].gd$email) continue;
	                    		var contact = entry[i].gd$email[0];
	                    		var mailObject = { 
	                    			address: contact.address, 
	                    			name: entry[i].title.$t ? entry[i].title.$t : contact.address
	                    		};
	                    		mailCollection.push(mailObject);
	                    	}
	                    	emailContactsService.gmail_contact.disabled = true;
	                    	deferred.resolve(mailCollection);
	                    }
	             });
	             return deferred.promise;
	          }
        };
        
        emailContactsService.yahoo_contact = 
        { 
            disabled : false, 
            getList : function() 
            { 
            	var deferred = $q.defer();
            	YahooService.getContacts()
            	.then(function(response) {
            		var entry = response.result;
            		var mailCollection = [];
            		for (var i = 0; i < entry.length; i++) {
            			var mailObject = {
            				address : entry[i].value,
            				name : (entry[i].name ? (entry[i].name.givenName + ' ' + entry[i].name.familyName) : entry[i].value)
            			};
            			mailCollection.push(mailObject);
                    }
                    emailContactsService.yahoo_contact.disabled = true;
                    deferred.resolve(mailCollection);
            	});
            	return deferred.promise;
            }
        }; 
        
        emailContactsService.hotmail_contact = 
        {
            disabled : false, 
            init: function() 
            { 
               WL.init({
                   client_id: SETTINGS.hotmailOptions.clientId,
                   redirect_uri: SETTINGS.hotmailOptions.redirect_url,
                   scope: ["wl.basic", "wl.contacts_emails"],
                   response_type: "token"
               });
            }, 
            getList : function() 
            { 
            	emailContactsService.hotmail_contact.init();
            	
            	var deferred = $q.defer();
            	WL.login({scope: ["wl.basic", "wl.contacts_emails"]})
            	.then(function (response) {
		       			WL.api({
		       	            path: "me/contacts",
		       	            method: "GET"
		       	        }).then(function (response) {
			       	            	console.log(response.data);
			       	            	var entry = response.data;
			       	            	var mailCollection = [];
			       	            	for(var i = 0; i < entry.length; i++)
			       	            	{
			       	            		var contact = entry[i].emails;
			       	            		var mailObject = { 
			       	            			address: contact.personal,
			       	            			name: (entry[i].name ? entry[i].name : contact.personal)
			       	            		}
			       	            		mailCollection.push(mailObject);
			       	            	}
			       	            	emailContactsService.hotmail_contact.disabled = true;  
			       	            	deferred.resolve(mailCollection);
			       	            },
			       	            function (responseFailed) {
			       	            	//console.log(responseFailed);
			       	            }
		       	        	); 
            	});
            	return deferred.promise;
            }
        };
        
        //emailContactsService.hotmail_contact.init();
        
        //////////////////////////////////////////////////
        /*
        emailContactsService.mailCollection = [];
        
        emailContactsService.addToMailCollection = function(item, callbackFn)
        { 
            if(typeof item != 'string' && !item.address) return;
            else
                if(typeof item == 'string') 
                {
                    var EmailCollection = item.replace(/\s/,'').split(',');
                    for(var i = 0; i < EmailCollection.length; i ++)
                    {
                        emailContactsService.addToMailCollection({
                            address : EmailCollection[i],
                            name : EmailCollection[i],
                            checked : true
                        });
                    }
                    callbackFn(emailContactsService.mailCollection);
                    return;
                    
                }
                
            
            for(var i = 0; i < emailContactsService.mailCollection.length; i++)
            { 
                if(emailContactsService.mailCollection[i].email.toLowerCase() == item.address.toLowerCase()) return;
            }
            var newEmail = {
                email : item.address,
                name : (item.name == '')?item.address:item.name,
                checked : true
            };
            emailContactsService.mailCollection.push(newEmail);
            
        };
        
        emailContactsService.gmail_contact_old = {
            
            disabled : false , 
            auth: function (callbackFn) {
                var config = {
                  'client_id': '984342428068-0hefi3e2d7gujt11lugn5ic2v9ugn8ql.apps.googleusercontent.com',
                  'scope': 'https://www.google.com/m8/feeds'
                };
                gapi.auth.authorize(config, function() {
                  emailContactsService.gmail_contact.fetch(gapi.auth.getToken(),callbackFn);
                });
              },
             fetch: function (token, callbackFn) {
                 $.ajax({
                        url: "https://www.google.com/m8/feeds/contacts/default/full?access_token=" + token.access_token + "&alt=json",
                        dataType: "jsonp",
                        success:function(data) {
	                          console.log(data.feed.entry);
	                          var entry = data.feed.entry;
	                          for(var i = 0; i < entry.length; i++)
	                          {
	                        	  if (!entry[i].gd$email) continue;
	                        	  var mailObject = entry[i].gd$email[0];
	                        	  emailContactsService.addToMailCollection({ address: mailObject.address, name: entry[i].title.$t });
	                          }
	                          emailContactsService.gmail_contact.disabled = true;
	                          callbackFn(emailContactsService.mailCollection);
                        }
                    });
              }
        }; 
		
        emailContactsService.yahoo_contact_old = { 
             disabled : false , 
             login : function(callbackFn) 
             { 
               YahooService.getContacts()
                     .then(function(response) 
                     {
                          callbackFn(response);
                          var entry = response.result;
                          for(var i = 0; i < entry.length; i++)
                          {
                            var mailObject = {
                                address : entry[i].value,
                                name : (entry[i].name)?entry[i].name.givenName + ' ' + entry[i].name.familyName
                                                                    :entry[i].value
                            };
                            emailContactsService.addToMailCollection(mailObject);
                         }
                         emailContactsService.yahoo_contact.disabled = true;
                         callbackFn(emailContactsService.mailCollection);
                     });
             }
        };    
            
        emailContactsService.hotmail_contact_old = {
             
             disabled : false , 
             init: function() 
             { 
                WL.init({
                    client_id: SETTINGS.hotmailOptions.clientId,
                    redirect_uri: SETTINGS.hotmailOptions.redirect_url,
                    scope: ["wl.basic", "wl.contacts_emails"],
                    response_type: "token"
                });
             }, 
             login : function(callbackFn) 
             { 
               WL.login({
        	        scope: ["wl.basic", "wl.contacts_emails"]
        	    }).then(function (response) 
        	    {
        			WL.api({
        	            path: "me/contacts",
        	            method: "GET"
        	        }).then(
        	            function (response) {
                                //your response data with contacts 
        	            	console.log(response.data);
                                  var entry = response.data;
                                  for(var i = 0; i < entry.length; i++)
                                  {
                                    var mailObject = entry[i].emails;
                                    emailContactsService.addToMailCollection({ address: mailObject.personal,
                                                                    name :entry[i].name });
                                  }
                            
                            emailContactsService.hotmail_contact.disabled = true;
                            callbackFn(emailContactsService.mailCollection);
                            
        	            },
        	            function (responseFailed) {
        	            	//console.log(responseFailed);
        	            }
        	        ); 
                });
            },
        };
        */
        ///////////////////////////////////////////
        
		return emailContactsService;
});
