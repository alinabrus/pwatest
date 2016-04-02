App
.factory('RegImageService', 
function($http, $log, $window, $q, $timeout, $parse, SessionService, FileUploadService, LocationService, SETTINGS){
		
		var regImageService = {};
		regImageService.drawImage = function(file, callbackFn) 
        { 
           // var index = (typeof imageIndex == 'undefined')?$scope.imageCount-1:imageIndex;
            var previewFile = file;
            
				console.log("blob before", previewFile);
				if (previewFile) {
					var reader  = new FileReader();
					reader.onloadend = function () {
                                callbackFn(reader.result);
					};
					reader.readAsDataURL(previewFile); 
				}
        };
		
		

		return regImageService;
});
