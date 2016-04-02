App
.factory('TwitterService', 
function($http, $log, $window, $q, $timeout, $parse, SessionService, FileUploadService, LocationService, SETTINGS){
    
        var twitterService = {};
        twitterService.sharer = { 
            count : 0,
            countUrl : '',
            shareUrl : '',
            twitterUrl : 'http://twitter.com/share?url=',
            share : function(text, base_url, camp_tag) 
            { 
                if(typeof text == 'undefined') text = "Check this out!";
                if(typeof base_url != 'undefined') {
                	if(typeof camp_tag != 'undefined')
                		twitterService.sharer.setLinks(base_url, camp_tag);
                	else {
                		twitterService.sharer.shareUrl = encodeURIComponent(base_url);
                		twitterService.sharer.countUrl = base_url;
                	}
                }	
                var twUrl = twitterService.sharer.twitterUrl+twitterService.sharer.shareUrl+'&text=' + encodeURIComponent(text); 
                var left = screen.width/2 - 200;
    	        var top = screen.height/2 - 250;
            	left = left + 50;
        		top = top + 50;
        		var twPopup = $window.open(twUrl, 'tw', "top=" + top + ",left=" + left + ",width=640,height=480");
        		/*
        		var popupWinAttrs = "status=no, width='640', height='480', resizable=yes, toolbar=no, menubar=no, scrollbars=no, location=no, directories=no";
        		var twPopup = window.open(twUrl,'tw', popupWinAttrs);//'tw', "top=" + top + ",left=" + left + ",width=400,height=500");
                console.log('twPopup',twPopup);
                var interval = window.setInterval(function() {
                    try {
                        if (twPopup == null || twPopup.closed) {
                            window.clearInterval(interval);
                           twitterService.sharer.getCount();
                        }
                    }
                    catch (e) {
                    }
                }, 1000);
                //twPopup.onbeforeunload = function() { $scope.social.twitter.getCount();}; //Mobile Safari does not support "beforeunload"
        		*/
            },
            setLinks : function(base_url,camp_tag, call_get_count) 
            { 
                twitterService.sharer.countUrl = base_url + '/backend/ogtags/camp/' + camp_tag;
                twitterService.sharer.shareUrl = base_url + '/mxsocial/' + camp_tag;
                if(call_get_count)
                    twitterService.sharer.getCount();
            },
            getCount : function() 
            { 
            	return; // because of shutting down of share count feature by Twitter
                $http.post(SETTINGS.apiSocialShareCount, {
                    url : twitterService.sharer.countUrl,
                    social : 'twitter'
                })
                    .then(
                    function successRequest(response){
                        $log.debug('url = SETTINGS.apiSocialShareCount, response = ', response);
                        if(response.data && response.data[0] && response.data[0].count)
                        {
                            console.log('twitter counter', response.data[0].count);
                           twitterService.sharer.count = response.data[0].count;
                        }
                    },
                    function errorRequest(response){
                        console.log('Twitter count error : ',response);
                    });
            }
        };
        
		return twitterService;
});

		
