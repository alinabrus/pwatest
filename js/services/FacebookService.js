App
.factory('FacebookService', 
function($http, $log, $window, $q, $timeout, $interval, $parse, SessionService, FileUploadService, LocationService, SETTINGS){
		
		var facebookService = {};
		facebookService.facebook = { 
                sharer : { 
                    count : 0,
                    requestLink : 'https://graph.facebook.com/?id=',
                    //link : $scope.baseUrl + '/social/' + $scope.state.params.camp_tag + '/0',
                    link : '',
                    setLink : function(url, call_get_count) 
                    { 
                        facebookService.facebook.sharer.link = url;
                        if(call_get_count)
                            facebookService.facebook.sharer.getCount();
                    },
                    getCount : function(callback,count) 
                    {    
                         $http.post(SETTINGS.apiSocialShareCount, {
                            url : facebookService.facebook.sharer.link,
                            social : 'facebook'
                        })
                            .then(
                            function successRequest(response){
                                if(response.data && response.data[0].shares)
                                {
                                   facebookService.facebook.sharer.count = response.data[0].shares;
                                 //  if(typeof callback != 'undefined')
                                  //  callback(response.data[0].count);
                                }
                            },
                            function errorRequest(response){
                                console.log('Facebook count error : ',response);
                            });
                       /*var link = facebookService.facebook.sharer.requestLink + encodeURIComponent(facebookService.facebook.sharer.link);
                      
                        FB.api({
                            method: 'fql.query',
                            query: 'SELECT share_count FROM link_stat WHERE url = "'+encodeURIComponent(facebookService.facebook.sharer.link)+'"'
                        }, function(res){
                           // if(typeof res != 'undefined' && typeof res.isArray != 'undefined')
                           console.log(res[0].share_count);
                           if(typeof res != 'undefined')
                           {
                                facebookService.facebook.sharer.count = res[0].share_count;
                                if(typeof callback != 'undefined')
                                    callback(res[0].share_count);
                           }
                           else 
                           {
                                if(typeof count != 'undefined' && count < 1)
                                facebookService.facebook.sharer.getCount(callback,1);
                           }
                             
                        },
                        function(error)
                        {
                            console.log('share_count error: ',error);
                        });
                      /* $http.get(link)
                            .then(function successRequest(response){
                                console.log('Facebook :success',response);
                            }, 
                            function errorRequest(response){
                                console.log('Facebook :error',response);
                            });*/
                    },
                    share_old : function(orgName, orgTag, campName, campDescription, imageUrl) 
                    {
                        var link = facebookService.facebook.sharer.link;
                        
                        //parse text from html(string)
                        var div = document.createElement('div');
                        div.innerHTML = campDescription;
                        var __campDescription = div.textContent || div.innerText;
                        
                        
                        var shareOptions = {
                            method: 'feed',
                            name: orgName,
                            link: link,
                            caption: campName,
                            description: __campDescription
                        }
                        if(typeof imageUrl != 'undefined' && imageUrl != null && imageUrl != '' )
                            shareOptions.picture = imageUrl;
                        console.log('shareOptions', shareOptions);
                        FB.ui(shareOptions, function(){
                            facebookService.facebook.sharer.getCount();
                        });
                    },
                    share_prev : function() 
                    {
                        var link = facebookService.facebook.sharer.link + '?fbrefresh=' + Date.now();
                        console.log('share: link = ', link);
                        FB.ui({
                            display: 'popup',
                            method: 'share',
                            href: link
                          }, function(response){
                        	  facebookService.facebook.sharer.getCount();
                          });
                    },
                    share : function() 
                    {
                    	/*                    	
						from https://developers.facebook.com/tools/debug/og/object : 
							https://www.facebook.com/dialog/share?app_id=1134815619882799&redirect_uri=http%3A%2F%2Fmaxletics.automatic.com.ua%2F&display=popup&href=http%3A%2F%2Fmaxletics.automatic.com.ua%2Fmxsocial%2Fab4_fundraising_campaign
						from FB.ui:
							https://www.facebook.com/v2.4/dialog/share?app_id=1134815619882799&display=popup&e2e=%7B%7D&href=http%3A%2F%2Fmaxletics.automatic.com.ua%2Fmxsocial%2Fab4_fundraising_campaign%2F20160126135040%3Ffbrefresh%3D1453806435123&locale=en_US&next=http%3A%2F%2Fstaticxx.facebook.com%2Fconnect%2Fxd_arbiter.php%3Fversion%3D42%23cb%3Df1ad99264cf94b2%26domain%3Dmaxletics.automatic.com.ua%26origin%3Dhttp%253A%252F%252Fmaxletics.automatic.com.ua%252Ff19672d6207b326%26relation%3Dopener%26frame%3Df2cca5a731c84fa%26result%3D%2522xxRESULTTOKENxx%2522&sdk=joey&version=v2.4  
                    	*/
                    	var link = facebookService.facebook.sharer.link; // + '?fbrefresh=' + Date.now();
                        //$window.open('https://www.facebook.com/dialog/share?app_id=' + SETTINGS.facebookAppID + '&display=popup&href=' + encodeURIComponent(link)); //&redirect_uri=http%3A%2F%2Fmaxletics.automatic.com.ua%2F
                        var fbUrl = 'https://www.facebook.com/dialog/share?app_id=' + SETTINGS.facebookAppID + '&display=popup&href=' + encodeURIComponent(link);
                        var left = screen.width/2 - 200;
                    	var top = screen.height/2 - 250;
                    	/*
                        var fbPopup = $window.open(fbUrl, 'fb', "top=" + top + ",left=" + left + ",width=400,height=500");
                    	console.log('fbPopup = ', fbPopup);
                    	fbPopup.onunload = function () {
                    		console.log('fbPopup.onunload');
                    		facebookService.facebook.sharer.getCount();
                    	};
                    	*/
                    	var fbPopup;
                    	var stop = $interval(function() {
                    		if (!fbPopup)
                    			fbPopup = $window.open(fbUrl, 'fb', "top=" + top + ",left=" + left + ",width=640,height=480");
                    		else {
	                    		try {
	                                if (fbPopup == null || fbPopup.closed) {
	                                    facebookService.facebook.sharer.getCount();
	                            		$interval.cancel(stop);
	                                    stop = undefined;
	                                }
	                            }
	                            catch (e) {
	                            }
                    		}
                    	}, 100);
                    },
                    share__ : function() 
                    {
                        var link = facebookService.facebook.sharer.link;
                        
                        var shareOptions = {
                    		method: 'feed',
                    	    name: 'Name',
                    	    //caption: '',
                    	    description: 'descript abc',
                    	    //link: link,
                    	    /*
                    	    link: 'https://www.youtube.com/watch?v=Pd8hJRIW31w',
                    	    source: 'https://www.youtube.com/v/Pd8hJRIW31w?autoplay=1',
                    	    picture: 'http://img.youtube.com/vi/Pd8hJRIW31w/0.jpg',
                    	    */
                    	    link : 'https://www.youtube.com/watch?v=nCD2hj6zJEc',
                    	    source: 'https://www.youtube.com/v/nCD2hj6zJEc?autoplay=1',
                    	    picture: 'http://img.youtube.com/vi/nCD2hj6zJEc/0.jpg',
                    	    
                    	};
                        console.log('shareOptions', shareOptions);
                        FB.ui(shareOptions, function(){
                            facebookService.facebook.sharer.getCount();
                        });
                    },
                    share__1 : function() 
                    {
                        var link = facebookService.facebook.sharer.link;
                        var shareOptions = {
                        		name: 'Name',
                        	    //caption: '',
                        	    description: 'descript abc',
                        	    link: link,
                        	    //source: 'https://www.youtube.com/embed/Pd8hJRIW31w'
                        	    //picture: 'http://img.youtube.com/vi/Pd8hJRIW31w/0.jpg',
                        	    picture: 'http://img.youtube.com/vi/nCD2hj6zJEc/0.jpg',
                        	    //link : 'https://www.youtube.com/watch?v=nCD2hj6zJEc',
                        	    source: 'https://www.youtube.com/v/nCD2hj6zJEc?autoplay=1'
                        	};
                        FB.login(function(resp){
                        	$log.debug('FB.login resp: ', resp);
                        	  // Note: The call will only work if you accept the permission request
                        	  FB.api('/feed', 'post', shareOptions, function(response){
	                              	$log.debug('FB.api respnonse: ', response);
	                                facebookService.facebook.sharer.getCount();
	                            });
                        	}, {scope: 'publish_actions'});
                    },
                    share__2: function() {
                    	var link = facebookService.facebook.sharer.link;
                    	var shareOptions = {
                        		name: 'Name',
                        	    //caption: '',
                        	    description: 'descript abc',
                        	    link: link,
                        	    //source: 'https://www.youtube.com/embed/Pd8hJRIW31w'
                        	    //picture: 'http://img.youtube.com/vi/Pd8hJRIW31w/0.jpg',
                        	    picture: 'http://img.youtube.com/vi/nCD2hj6zJEc/0.jpg',
                        	    //link : 'https://www.youtube.com/watch?v=nCD2hj6zJEc',
                        	    source: 'https://www.youtube.com/v/nCD2hj6zJEc?autoplay=1'
                        	};
                    	FB.getLoginStatus(function(response) {
                            if (response.status === 'connected') {
                            	return FB.login(function(response) {
                                	if (response.authResponse) {
                                		if(response.status == 'connected') {
                                			FB.api('/me/feed', 'post', shareOptions, function(response){
            	                              	$log.debug('FB.api respnonse: ', response);
            	                                facebookService.facebook.sharer.getCount();
            	                            });
                                        }
                                    }
                                }, {scope: facebookService.facebook.extendedPermissions, return_scopes: true});
                              } else if (response.status === 'not_authorized') {
                                  return FB.login(function(response) {
                                	if (response.authResponse) {
                                		if(response.status == 'connected') {
                                			FB.api('/me/feed', 'post', shareOptions, function(response){
            	                              	$log.debug('FB.api respnonse: ', response);
            	                                facebookService.facebook.sharer.getCount();
            	                            });
                                        }
                                    }
                                }, {scope: facebookService.facebook.extendedPermissions, return_scopes: true});
                              } else {
                                  return FB.login(function(response) {
                                	if (response.authResponse) {
                                		if(response.status == 'connected') {
                                			FB.api('/me/feed', 'post', shareOptions, function(response){
            	                              	$log.debug('FB.api respnonse: ', response);
            	                                facebookService.facebook.sharer.getCount();
            	                            });
                                        }
                                    }
                                }, {scope: facebookService.facebook.extendedPermissions, return_scopes: true});  
                                //console.log('Some problem with facebook: ', response)
                              }
                        });
                    }
                },
                albumsCollection : [],
                selectedAlbumIndex : -1,
                extendedPermissions : 'email, public_profile, publish_actions, user_photos',
                createWindow : function()
                {
                    var params = "width=420,height=230,resizable=yes,scrollbars=yes,status=yes";
                    window.open('https://www.facebook.com/sharer/sharer.php?u=='+SETTINGS.clientBaseUrl+'/social/'+$scope.organization.tag+'/0',
                        'Twitter Share', params);
                },
                saveImage : function(url,imageCount, callbackFn) 
                { 
                   
                            var image = new Image();
                            image.setAttribute('crossOrigin', 'anonymous');
                            var canvas = document.createElement('canvas');
                            console.log('canvas',canvas);
                            var cContext = canvas.getContext('2d');
                            image.onload = function() 
                            { 
                                
                                canvas.width =  this.width;
                                canvas.height =  this.height;
                                cContext.save(); //saves the state of canvas
                                cContext.clearRect(0, 0, canvas.width, canvas.height); //clear the canvas
                                cContext.drawImage(image, 0,0); //draw the image ;)
                                cContext.restore(); //restore the state of canvas
                                canvas.toBlob(function(blob) {
                                    var returnFile =  
                                        new File([blob], 'camp_logos_' + (imageCount+1) + '.png', {type:"image/png"});
                                        callbackFn(returnFile);
                                }, "image/png");
                                //console.log('campaign', $scope.campaign.files.logos);
                                //$scope.imageCount ++;
                                //$scope.facebook.gotoViewWithApply('PhotoPreview');
                                delete canvas;
                            };
                            image.src = url;
                            
                },
                share : function(orgName, orgTag, campName, campDescription, imageUrl) 
                {
                    var link = SETTINGS.clientBaseUrl + '/#/' + orgTag;
                    
                    var shareOptions = {
                        method: 'feed',
                        name: orgName,
                        link: link,
                        caption: campName,
                        description: campDescription
                    }
                    if(typeof imageUrl != 'undefined' && imageUrl != '' )
                        shareOptions.picture = imageUrl;
                    console.log('shareOptions', shareOptions);
                    FB.ui(shareOptions);
                },
                chooseAlbum : function(callbackFn) 
                { 
                  if(facebookService.facebook.albumsCollection.length > 0)
                  {  
                      //$scope.facebook.gotoView('Albums');
                      callbackFn();
                      return true;
                  }
                  
                  FB.getLoginStatus(function(response) {
                        if (response.status === 'connected') {
                            facebookService.facebook.getAlbums(response.authResponse.userID,callbackFn);
                          } else if (response.status === 'not_authorized') {
                              return facebookService.facebook.login(callbackFn);  
                          } else {
                              return facebookService.facebook.login(callbackFn);  
                            //console.log('Some problem with facebook: ', response)
                          }
                    });
                },
                login : function(callbackFn) 
                { 
                    console.log('login',callbackFn);
                    FB.login(function(response) 
                    {
                    	if (response.authResponse) 
                        {
                    		if(response.status == 'connected')
                            {
                                console.log('Login start');
                                facebookService.facebook.getAlbums(response.authResponse.userID, callbackFn);
                            }
                        }
                    }, {scope: facebookService.facebook.extendedPermissions, return_scopes: true});
                },
                getAlbums : function(userID, callbackFn)
                {
                    console.log('getAlbums',callbackFn);
                    FB.api(
                          "/"+userID+"/albums/",
                          function (response) 
                          {
                              if (response && !response.error) 
                              {
                                    console.log('albums', response);
                                    for(i = 0; i < response.data.length; i ++)
                                    { 
                                        var newAlbum = {
                                             index: i,
                                             id:response.data[i].id, 
                                             name:response.data[i].name,
                                             imageCollection: []
                                        };
                                        facebookService.facebook.albumsCollection.push(newAlbum);
                                        facebookService.facebook.getAlbumPicture(response.data[i].id, i, (i >= response.data.length-1), callbackFn);  
                                    }
                              }
                          });
                
                },
                getAlbumPicture : function(albumID, index, lastFlag, callbackFn)
                {
                    console.log('getAlbumPicture',callbackFn);
                     FB.api(
                        "/"+albumID+"/picture/",
                        function (response) {
                            if (response && !response.error) {
                                    facebookService.facebook.albumsCollection[index].image = response.data.url;
                                    console.log('Step ',index, lastFlag);
                                    if(lastFlag)
                                        callbackFn();//facebookService.facebook.gotoViewWithApply('Albums');                                                                                                                                                                                  
                                }
                            }
                        );
                },
                getAlbumImages : function(albumID, albumIndex, callbackFn) 
                { 
                    facebookService.facebook.selectedAlbumIndex = albumIndex;
                    if(facebookService.facebook.albumsCollection[albumIndex].imageCollection.length > 0)
                    {
                        //facebookService.facebook.gotoView('Photos');     
                        callbackFn();                     
                        return true;
                    }
                    FB.api(
                        "/"+albumID+"/photos?fields=source,url,place,message",
                        function (response) {
                            if (response && !response.error) {
                                console.log('image response', response);                                
                                     for(i = 0; i < response.data.length; i++)
                                     {
                                        var newImage = {
                                            id : response.data[i].id,
                                            url : response.data[i].source                                               
                                        } 
                                      facebookService.facebook.albumsCollection[albumIndex].imageCollection.push(newImage);
                                     }   
                                     callbackFn();  
                                     //facebookService.facebook.gotoViewWithApply('Photos');                                                                                                                                                                            
                                }
                            }
                      );
                },
                gotoViewWithApply: function(stage)
                {
                    /*$scope.$apply(function($scope)
                    {
                        $scope.facebook.gotoView(stage);
                    });*/
                },
                gotoView: function(stage)
                {
                    
                        /*console.log($scope.facebook.albumsCollection);
                        switch(stage)
                        {
                          case 'Albums':
                                $scope.template = $scope.picturePreviewTemplate[2];
                             break;
                          case 'Photos':   
                                $scope.template = $scope.picturePreviewTemplate[3];
                             break;
                          case 'PhotoPreview':
                                $scope.template = $scope.picturePreviewTemplate[0];
                                break;
                          default: 
                             break;
                        }*/
                }
        };
	
				
		facebookService.init = function () {
                FB.init({
                    appId      : SETTINGS.facebookAppID,
                    xfbml      : true,
                    version    : 'v2.4',
                    status : true, // check login status
                    cookie : true, // enable cookies to allow the server to access the session
                });
		
		};
		facebookService.init();
		
		

		return facebookService;
});
