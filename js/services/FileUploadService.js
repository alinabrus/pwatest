App
.service('FileUploadService', ['$http', '$q', '$log', function ($http, $q, $log) {
	
	var fileUploadService = {};
		
	fileUploadService.uploadData = function(files, postdata, uploadUrl)
	{
		var deferred = $q.defer();
				
		$http({
            method: 'POST',
            url: uploadUrl,
            //IMPORTANT!!! You might think this should be set to 'multipart/form-data' 
            // but this is not true because when we are sending up files the request 
            // needs to include a 'boundary' parameter which identifies the boundary 
            // name between parts in this multi-part request and setting the Content-type 
            // manually will not set this boundary parameter. For whatever reason, 
            // setting the Content-type to 'false' will force the request to automatically
            // populate the headers properly including the boundary parameter.
            headers: { 'Content-Type': undefined },
            //This method will allow us to change how the data is sent up to the server
            // for which we'll need to encapsulate the model data in 'FormData'
            transformRequest: function (data) {
                var formData = new FormData();
                //need to convert our json object to a string version of json otherwise
                // the browser will do a 'toString()' on the object which will result 
                // in the value '[Object object]' on the server.
                //formData.append("model", angular.toJson(data.model));
                angular.forEach(data.model, function(value, key) {
            		if (angular.isObject(value)) 
            			value = angular.toJson(value);
            		formData.append(key, value);
            	});
                //$log.debug(data.files);
                angular.forEach(data.files, function(value, key) {
                	//$log.debug('--------------------------------------------------- fileUploadService.uploadData file: ', key, value);
                	if ( ! angular.isObject(value)) return;
                	/*
                	if (value.thumbUrl)
                		formData.append(key + '_thumb', value.thumbUrl);
                	*/
                	formData.append(key, value, value.name);
                	//$log.debug('--------------------------------------------------- fileUploadService.uploadData formData: ', formData.get(key));
            	});
                /*
                for (var i = 0; i < data.files.length; i++) {
                    //add each file to the form data and iteratively name them
                    formData.append("file" + i, data.files[i]);
                }*/
                return formData;
            },
            //Create an object that contains the model and files which will be transformed
            // in the above transformRequest method
            data: { model: postdata, files: files }
        }).
        success(function (response, status, headers, config) {
        	deferred.resolve(response);
        }).
        error(function (response, status, headers, config) {
        	deferred.resolve(response);
        });
		return deferred.promise;
	}
    
	return fileUploadService;
    
}]);