App
.service('FileInputService', ['$parse', '$filter', '$log', '$timeout', 'SETTINGS', function ($parse, $filter, $log, $timeout, SETTINGS) {
	
	var fileInputService = {};
	
	fileInputService.scope = {};
		
	fileInputService.fileinputInit = function(scope){
		
		fileInputService.scope = scope;
		
		$(".mx-fileinput-img").each(function(){
			var browseLabel = $(this).attr('data-mx-button-text');
			if (typeof browseLabel == 'undefined') browseLabel = 'Upload';
			
			var browseClass = $(this).attr('data-mx-browse-class');
			if ( ! browseClass) browseClass = 'btn btn-warning';
			
			var options = {
				previewSettings: {image: {}, other: {}, initialPreview: ''},
				//overwriteInitial: true,
				allowedFileTypes: ['image'], 
				allowedFileExtensions: ['gif','png','jpg','jpeg'],
				maxFileSize: SETTINGS.fileinputMaxFileSize, //kB, 0 - unlimited, default
				showRemove: false,
				showUpload: false,
				showPreview: false,
				showCaption: false,
				buttonLabelClass: '',
				browseLabel: browseLabel,
				browseIcon: '', //'<i class="glyphicon glyphicon-folder-open"></i> &nbsp;',
				browseClass: browseClass //'btn btn-primary',
			};
			
			var customOptions = $parse($(this).attr('data-mx-custom'))(scope);
			if ( ! customOptions) customOptions = {};
			//$log.debug('---- fileinput customOptions = ', customOptions);
			
			//var optionsExt = angular.extend({}, options, customOptions);
			angular.extend(options, customOptions);
			$log.debug('---- fileinput options = ', options);
			
			$(this).fileinput(options);
		});
		
		$(".mx-fileinput-img-preview").each(function(){
			var previewImgSrc = $parse($(this).attr('data-mx-model'))(scope);
			var previewThumbSrc = $parse($(this).attr('data-mx-thumb-model'))(scope);
			//var previewImgSrc = scope.$eval($(this).attr('data-mx-model'));
			//$log.debug('data-mx-model = ', $(this).attr('data-mx-model'));
			//$log.debug('previewImgSrc = ', previewImgSrc);
			var previewFile = $parse($(this).attr('data-mx-blob-model'))(scope);
			//$log.debug('data-mx-blob-model = ', $(this).attr('data-mx-blob-model'));
			//$log.debug('previewFile = ', previewFile);
			var elName = $(this).attr('name');
			
			var imgFileExtensions = ['gif','png','jpg','jpeg'];
			
			if (previewFile) {
				var reader  = new FileReader();
				reader.onloadend = function () {
					scope.$apply(function(scope) {
						previewImgSrc = reader.result;
						//$log.debug('reader = ', reader);
						//var imgEl = angular.element('img.file-preview-image[name="' + elName + '_preview"]');
						//imgEl.attr('src', previewImgSrc);
						if (previewFile.thumbUrl && (previewFile.type == 'application/postscript' || previewFile.type == 'application/pdf'))
							var previewHtml = '<img name="' + elName + '_preview" src="' + previewFile.thumbUrl + '" class="file-preview-image" style="width:200px; height:auto;" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">';
						else if (previewFile.type.search('image') > -1)
							var previewHtml = '<img name="' + elName + '_preview" src="' + previewImgSrc + '" class="file-preview-image" style="width:200px; height:auto;" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">';
						else
							var previewHtml = '<object name="' + elName + '_preview" class="file-object" style="width:200px; height:auto;" type="' + previewFile.type + '" data="' + previewImgSrc + '">' +
												'<param value="true" name="controller">' +
												'<param value="true" name="allowFullScreen">' +
												'<param value="always" name="allowScriptAccess">' +
												'<param value="false" name="autoPlay">' +
												'<param value="false" name="autoStart">' +
												'<param value="high" name="quality">' +
												'<div class="file-preview-other">' +
													'<span class="file-icon-4x">' +
													'<i class="glyphicon glyphicon-file"></i>' +
													'</span>' +
												'</div>' +
												'</object>';
						
						previewHtml +=			'<div class="file-thumbnail-footer">' +
													'<div class="file-footer-caption" title="' + previewFile.name + '">' + previewFile.name + '</div>' +
												'</div>';
						var previewBlockEl = angular.element('#' + elName + '_preview_block');
						previewBlockEl.html(previewHtml);
			    	});
				};
				reader.readAsDataURL(previewFile); 
			}
			else if (previewImgSrc) {
				var previewFileType = '';
				if (previewImgSrc.search('.pdf') > -1)
					previewFileType = 'application/pdf';
				else if (previewImgSrc.search('.ai') > -1 || previewImgSrc.search('.eps') > -1)
					previewFileType = 'application/postscript';
				else 
					for (var i in imgFileExtensions) {
						if (previewImgSrc.search('.' + imgFileExtensions[i]) > -1) {
							previewFileType = 'image/' + imgFileExtensions[i];
						}
					}
				/*
				if (previewFileType.search('image') > -1)
					var initialPreviewHtml = '<img name="' + elName + '_preview" src="' + previewImgSrc + '" class="file-preview-image" style="width:200px; height:auto;" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">';
				else if (previewThumbSrc && (previewFileType == 'application/postscript' || previewFileType == 'application/pdf'))
					var initialPreviewHtml = '<img name="' + elName + '_preview" src="' + previewThumbSrc + '" class="file-preview-image" style="width:200px; height:auto;" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">';
				*/
				if (previewThumbSrc)
					var initialPreviewHtml = '<img name="' + elName + '_preview" src="' + previewThumbSrc + '" class="file-preview-image" style="width:200px; height:auto;" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">';
				else if (previewFileType.search('image') > -1)
					var initialPreviewHtml = '<img name="' + elName + '_preview" src="' + previewImgSrc + '" class="file-preview-image" style="width:200px; height:auto;" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">';
				else 
					var initialPreviewHtml = '<object name="' + elName + '_preview" class="file-object" style="width:200px; height:auto;" type="' + previewFileType + '" data="' + previewImgSrc + '">' +
										'<param value="true" name="controller">' +
										'<param value="true" name="allowFullScreen">' +
										'<param value="always" name="allowScriptAccess">' +
										'<param value="false" name="autoPlay">' +
										'<param value="false" name="autoStart">' +
										'<param value="high" name="quality">' +
										'<div class="file-preview-other">' +
											'<span class="file-icon-4x">' +
											'<i class="glyphicon glyphicon-file"></i>' +
											'</span>' +
										'</div>' +
										'</object>';
				
				initialPreviewHtml +=			'<div class="file-thumbnail-footer">' +
											'<div class="file-footer-caption" title="' + previewImgSrc + '">' + previewImgSrc + '</div>' +
										'</div>';
				//var initialPreviewHtml = '<img name="' + elName + '_preview" src="' + previewImgSrc + '" class="file-preview-image" style="width:200px; height:auto;" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">';
				//$log.debug('initialPreviewHtml = ', initialPreviewHtml);
			}
			
			var customOptions = $parse($(this).attr('data-mx-custom'))(scope);
			if ( ! customOptions) customOptions = {};
			//$log.debug('---- fileinput customOptions = ', customOptions);
			
			var maxFileSize = SETTINGS.fileinputMaxFileSize ? SETTINGS.fileinputMaxFileSize/1024 : 10; // Mb
			var minImgWidth = customOptions.minImageWidth ? customOptions.minImageWidth : null;
			var initialMessageHtml = 
			//'<img name="' + elName + '_preview" src="' + previewImgSrc + '" class="file-preview-image" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">'
			'<div id="' + elName + '_preview_block"><div class="file-preview-image file-upload-info"><span>' +
				'Max image file size - ' + maxFileSize + 'MB,'; 
			if (minImgWidth) initialMessageHtml += '</br>min image width - ' + minImgWidth + 'px.';
			initialMessageHtml += '</span></div></div>';
			//var clearEl = el.parents().find('.file-preview').find('.mx-fileinput-clear');
			var clearFnName = 'mxFileinputClear_' + $filter('normalize')(elName);
			var clearFnScript =	'<script language="javascript"> \n' +
								'function ' + clearFnName + '(clearEl){ \n' +
									//'console.log(\'clearFn called\'); \n' + 
									'var fileinputEl = angular.element(\'.mx-fileinput-img-preview[name='+elName+']\'); \n' + 
									//'console.log(fileinputEl); \n' +
									'fileinputEl.fileinput(\'clear\'); \n' +
			        				'fileinputEl.fileinput(\'refresh\', {initialPreview: \'' + initialMessageHtml + '\'}); \n' +
			        				'fileinputEl.scope().fileinputInitHandlers(fileinputEl.scope()); \n' +
			        				'clearEl = fileinputEl.parents(\'.file-input\').find(\'.mx-fileinput-clear\'); \n' +
			        				//'console.log(\'clearEl = \', clearEl); \n' +
			        				'clearEl.addClass(\'hide\'); \n' +
			        			'} ' +
								'</script> \n';
			$(this).prepend(clearFnScript);
			
			var browseLabel = $(this).attr('data-mx-button-text');
			if (typeof browseLabel == 'undefined') browseLabel = 'Upload';
			
			var browseClass = $(this).attr('data-mx-browse-class');
			if ( ! browseClass) browseClass = 'btn btn-warning';
			
			var previewStyle = '';
			var hidePreview = $(this).attr('data-mx-hide-preview');
			if (typeof hidePreview != 'undefined') 
				previewStyle = 'style="display: none;"';
			//console.log('@@@@___hidePreview: ', hidePreview);
			
			var clearLabel = 'Clear';
			if (customOptions.clearLabel) 
				clearLabel = customOptions.clearLabel;
			
			var options = {
				layoutTemplates: {
				    preview: '<div class="file-preview {class}" ' + previewStyle + '>\n' +
				    //'    <div class="close fileinput-remove">Clear</div>\n' +
			        //'    <!-- fileinput-remove --><div class="close mx-fileinput-clear" onclick="$(\'.mx-fileinput-img-preview[name='+elName+']\').fileinput(\'reset\');">Clear</div>\n' +
				    '    <!-- fileinput-remove --><div class="close mx-fileinput-clear ' + (previewImgSrc || previewFile ? '' : 'hide') + '" onclick="' + clearFnName + '($(this));">' + clearLabel + '</div>\n' +
				    '    <div class="{dropClass}">\n' +
			        '    <div class="file-preview-thumbnails">\n' +
			        '    </div>\n' +
			        '    <div class="clearfix"></div>' +
			        '    <div class="file-preview-status text-center text-success"></div>\n' +
			        '    <div class="kv-fileinput-error"></div>\n' +
			        '    </div>\n' +
			        '</div>'
				},
				initialPreview: [
				    initialPreviewHtml ? initialPreviewHtml : initialMessageHtml
				], 
				previewSettings: {image: {width: "200px", height: "auto"}, other: {}, initialPreview: initialMessageHtml},
				initialPreviewShowDelete: false,
				//overwriteInitial: true,
				//allowedFileTypes: ['image'], 
				allowedFileExtensions: imgFileExtensions, 
				maxFileSize: SETTINGS.fileinputMaxFileSize, //kB, 0 - unlimited, default
				initialPreviewShowDelete: true,
				showRemove: false,
				showUpload: false,
				showPreview: true,
				showCaption: false,
				buttonLabelClass: '',
				browseLabel: browseLabel,
				browseIcon: '', //'<i class="glyphicon glyphicon-folder-open"></i> &nbsp;',
				browseClass: browseClass //'btn btn-primary'
			};
			
			//var optionsExt = angular.extend({}, options, customOptions);
			angular.extend(options, customOptions);
			//$log.debug('---- fileinput options = ', options);
			
			$(this).fileinput(options);
			if (previewImgSrc || previewImgSrc)
				$(this).fileinput('disable');
		});
		
		$(".mx-fileinput-csv").each(function(){
			var browseLabel = $(this).attr('data-mx-button-text');
			if (typeof browseLabel == 'undefined') browseLabel = 'Upload';
			
			var browseClass = $(this).attr('data-mx-browse-class');
			if ( ! browseClass) browseClass = 'btn btn-warning';
			
			var options = {
				layoutTemplates: {
				    preview: '<div class="file-preview {class}">\n' +
				    '    <div class="file-preview-status text-center text-success"></div>\n' +
			        '    <div class="kv-fileinput-error"></div>\n' +
			        '    </div>\n' +
			        '</div>'
				},
				//overwriteInitial: true,
				//allowedFileTypes: ['text'], 
				allowedFileExtensions: ['csv', 'xls', 'xlsx', 'txt'],
				maxFileSize: SETTINGS.fileinputMaxFileSize, //kB, 0 - unlimited, default
				showRemove: false,
				showUpload: false,
				showPreview: false,
				showCaption: false,
				buttonLabelClass: '',
				//mainClass: '',
				browseLabel: browseLabel,
				browseIcon: '', //'<i class="glyphicon glyphicon-folder-open"></i> &nbsp;',
				browseClass: browseClass //'btn btn-primary'
			};
			
			var customOptions = $parse($(this).attr('data-mx-custom'))(scope);
			if ( ! customOptions) customOptions = {};
			//$log.debug('---- fileinput customOptions = ', customOptions);
			
			//var optionsExt = angular.extend({}, options, customOptions);
			angular.extend(options, customOptions);
			//$log.debug('---- fileinput options = ', options);
			
			$(this).fileinput(options);
		});
		
		$(".mx-fileinput-csv-preview").each(function(){
			
			var customOptions = $parse($(this).attr('data-mx-custom'))(scope);
			if ( ! customOptions) customOptions = {};
			$log.debug('---- fileinput customOptions = ', customOptions);
			
			var previewSrc = $parse($(this).attr('data-mx-model'))(scope);
			//var previewImgSrc = $scope.$eval($(this).attr('data-mx-model'));
			//$log.debug('data-mx-model = ', $(this).attr('data-mx-model'));
			//$log.debug('previewImgSrc = ', previewImgSrc);
			var elName = $(this).attr('name');
			var el = $(this);
			
			if (previewSrc) {
				//var initialPreviewHtml = '<img name="' + elName + '_preview" src="' + previewImgSrc + '" class="file-preview-image" style="width:200px; height:auto;" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">';
				//$log.debug('initialPreviewHtml = ', initialPreviewHtml);
			}
			else { 
				var previewFile = $parse($(this).attr('data-mx-blob-model'))(scope);
				//$log.debug('data-mx-blob-model = ', $(this).attr('data-mx-blob-model'));
				$log.debug('previewFile = ', previewFile);
				if (previewFile) {
					////////////////////////////////////
					$log.debug('---- fileinput: typeof customOptions.previewContent = ', typeof customOptions.previewContent);
					if (typeof customOptions.previewContent == 'string') {
						/*
						var initialPreviewHtml = 
							//'<img name="' + elName + '_preview" src="' + previewImgSrc + '" class="file-preview-image" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">'
							'<div id="' + elName + '_preview_block">' +
								//'<pre class="file-preview-text" style="height:136px;"></pre>' + 
							'</div>';
						*/
						var reader  = new FileReader();
						reader.onloadend = function () {
							scope.$apply(function(scope) {
								
								var previewPreHtml = 
								'<pre class="file-preview-text">' + customOptions.previewContent + '</pre>' + //reader.result.substring(0,2000)
								'<div class="file-thumbnail-footer">'+
									'<div class="file-caption-name" title="' + previewFile.name + '">' + previewFile.name + '</div></div>'+
									//'<div class="file-footer-caption" title="' + previewFile.name + '">' + previewFile.name + '</div></div>'+
								'</div>';
								$log.debug('previewPreHtml = ', previewPreHtml);
								var previewBlockEl = angular.element('#' + elName + '_preview_block');
								previewBlockEl.html(previewPreHtml);
								//$log.debug('---------------', el);
								//$log.debug('---------------', el.parents('.file-input').find('.file-preview'));
								el.parents('.file-input').find('.file-preview').show();
								el.parents('.file-input').find('.mx-fileinput-clear').show();
								
								if (typeof customOptions.initialPreviewLoadedHandler == 'function')
									customOptions.initialPreviewLoadedHandler();
							});
						};
						reader.readAsText(previewFile); 
					}
					else {
						var initialPreviewHtml = 
							//'<img name="' + elName + '_preview" src="' + previewImgSrc + '" class="file-preview-image" alt="' + $(this).attr('data-initial-caption') + '" title="' + $(this).attr('data-initial-caption') + '">'
							'<div id="' + elName + '_preview_block">' +
								//'<pre class="file-preview-text" style="height:136px;"></pre>' + 
							'</div>';
						var reader  = new FileReader();
						reader.onloadend = function () {
							scope.$apply(function(scope) {
								//$log.debug('reader.result = ', reader.result);
								var previewPreHtml = '<pre class="file-preview-text" style="height:136px;">' + reader.result + '</pre>' + //reader.result.substring(0,2000)
														'<div class="file-thumbnail-footer">'+
															'<div class="file-footer-caption" title="' + previewFile.name + '">' + previewFile.name + '</div>'+ //'</div></div>'+
														'</div>';
								//$log.debug('previewPreHtml = ', previewPreHtml);
								var previewBlockEl = angular.element('#' + elName + '_preview_block');
								previewBlockEl.html(previewPreHtml);
								
								if (typeof customOptions.initialPreviewLoadedHandler == 'function')
									customOptions.initialPreviewLoadedHandler();
					    	});
						};
						reader.readAsText(previewFile); 
					}
					////////////////////////////////////
				}
				else {
					//previewImgSrc = 'http://placehold.it/' + $(this).attr('data-mx-placehold-size');
					//previewImgSrc = 'assets/images/upload_mes.jpg'; 
				} 
			}
			
			var initialMessageHtml = [];
			var clearFnName = 'mxFileinputClear_' + $filter('normalize')(elName);
			var clearFnScript =	'<script language="javascript"> \n' +
								'function ' + clearFnName + '(clearEl){ \n' +
									//'console.log(\'clearFn called\'); \n' + 
									'var fileinputEl = angular.element(\'.mx-fileinput-csv-preview[name='+elName+']\'); \n' + 
									//'console.log(fileinputEl); \n' +
									'fileinputEl.fileinput(\'clear\'); \n' +
			        				'fileinputEl.fileinput(\'refresh\', {initialPreview: \'' + initialMessageHtml + '\'}); \n' +
			        				'fileinputEl.scope().fileinputInitHandlers(fileinputEl.scope()); \n' +
			        				'clearEl = fileinputEl.parents(\'.file-input\').find(\'.mx-fileinput-clear\'); \n';
									//'console.log(\'clearEl = \', clearEl); \n';
			if (initialPreviewHtml) clearFnScript += 'clearEl.addClass(\'hide\'); \n';
			clearFnScript += 	'} ' +
								'</script> \n';
			$(this).prepend(clearFnScript);
			
			var browseLabel = $(this).attr('data-mx-button-text');
			if (typeof browseLabel == 'undefined') browseLabel = 'Upload';
			
			var browseClass = $(this).attr('data-mx-browse-class');
			if ( ! browseClass) browseClass = 'btn btn-warning';
						
			var clearLabel = 'Clear';
			if (customOptions.clearLabel) 
				clearLabel = customOptions.clearLabel;
			
			var previewContent = (typeof customOptions.previewContent == 'string') ? 
					'    <div class="file-preview-frame">\n' +
					'    <div id="' + elName + '_preview_block">\n' +
					'	 <pre class="file-preview-text">' + customOptions.previewContent + '</pre>' + 
					'	 <div class="file-thumbnail-footer">'+
					'		<div class="file-caption-name">{caption}</div>\n' +
					'	 </div>' +
					/*
					//'<div tabindex="-1" class="form-control file-caption {class}">\n' +
			        '   <div class="file-caption-name">{caption}</div>\n' +
			        //'</div>' +
			        */
			        '	 </div>' +
					'    </div>\n' :
					'    <div class="file-preview-thumbnails">\n' +
			        '    </div>\n';
			
			var options = {
				layoutTemplates: {
				    preview: '<div class="file-preview {class}">\n' +
				    //'    <div class="close fileinput-remove">Clear</div>\n' +
			        //'    <!-- fileinput-remove --><div class="close mx-fileinput-clear" onclick="$(\'.mx-fileinput-img-preview[name='+elName+']\').fileinput(\'reset\');">Clear</div>\n' +
				    //'    <!-- fileinput-remove --><div class="close mx-fileinput-clear ' + (previewSrc || previewFile ? '' : 'hide') + '" onclick="' + clearFnName + '($(this));">' + clearLabel + '</div>\n' +
				    '    <!-- fileinput-remove --><div class="close mx-fileinput-clear" onclick="' + clearFnName + '($(this));">' + clearLabel + '</div>\n' +
				    '    <div class="{dropClass}">\n' +
				    previewContent + 
			        '    <div class="clearfix"></div>' +
			        '    <div class="file-preview-status text-center text-success"></div>\n' +
			        '    <div class="kv-fileinput-error"></div>\n' +
			        '    </div>\n' +
			        '</div>',
			        icon: '', //'<span class="glyphicon glyphicon-file kv-caption-icon"></span>',
			        zoom: ''
				},
				/*initialPreview: [
								    initialPreviewHtml ? initialPreviewHtml : initialMessageHtml
								],*/
				initialPreview: initialPreviewHtml ? [initialPreviewHtml] : [],				
				/*
				initialPreview: "<div class='file-preview-text' title='NOTES.txt'>" +
			    "This is the sample text file content upto wrapTextLength of 250 characters" +
			    "<span class='wrap-indicator' onclick='$(\"#show-detailed-text\").modal(\"show\")' title='NOTES.txt'>[…]</span>" +
			    "</div>", */
				//overwriteInitial: true,
				//allowedFileTypes: ['text'], 
				allowedFileExtensions: ['csv', 'xls', 'xlsx', 'txt'],
				maxFileSize: SETTINGS.fileinputMaxFileSize, //kB, 0 - unlimited, default
				showRemove: false,
				showUpload: false,
				showPreview: true,
				showCaption: false,
				buttonLabelClass: '',
				//mainClass: '',
				browseLabel: browseLabel,
				browseIcon: '', //'<i class="glyphicon glyphicon-folder-open"></i> &nbsp;',
				browseClass: browseClass //'btn btn-primary'
			};
			
			//var optionsExt = angular.extend({}, options, customOptions);
			angular.extend(options, customOptions);
			//$log.debug('---- fileinput options = ', options);
			
			$(this).fileinput(options);
		});
				
		fileInputService.fileinputInitHandlers(scope);
		
	};
	
	fileInputService.fileloadedHandler = function(event, file, previewId, index, reader) {
		var scope = fileInputService.scope;
		$log.debug('event = ', event);
		var el = angular.element(event.target);
		scope.$apply(function(scope) {
			$parse(el.attr('data-mx-blob-model')).assign(scope, file);
			if (el.attr('data-mx-thumb-model'))
				$parse(el.attr('data-mx-thumb-model')).assign(scope, null);
			/*
			if (file.thumbUrl)
				$parse(el.attr('data-mx-thumb-model')).assign(scope, file.thumbUrl);
			*/
			$log.debug(el.attr('data-mx-model'), ' = ', $parse(el.attr('data-mx-model'))(scope));
			$log.debug(el.attr('data-mx-blob-model'), ' = ', $parse(el.attr('data-mx-blob-model'))(scope));
    	});
		var clearEl = angular.element('#' + previewId).parents('.file-preview').find('.mx-fileinput-clear');
		clearEl.removeClass('hide');
		$log.debug(previewId,'--------', clearEl);
		scope.$emit('mx-fileloaded');
		//if ( ! el.hasClass('mx-fileinput-img-preview'))
			el.fileinput('disable');
	};
	
	fileInputService.fileclearHandler = function(event) {
		var scope = fileInputService.scope;
		//$log.debug('event = ', event);
		//$log.debug('$(event.target) = ', $(event.target));
		//$(event.target).fileinput('refresh', {initialPreview: [initialMessageHtml] });
		//$(this).fileinput('refresh', {initialPreview: [initialMessageHtml] });
		var el = angular.element(event.target);
		//$log.debug('el = ', el);
		
		scope.$apply(function(scope) {
		//$timeout(function() {	
			$parse(el.attr('data-mx-model')).assign(scope, null);
			$parse(el.attr('data-mx-blob-model')).assign(scope, null);
	    	//var previewFile = $parse(el.attr('data-mx-blob-model'))(scope);
			//$log.debug('reset: data-mx-blob-model = ', el.attr('data-mx-blob-model'));
			//$log.debug('reset: ', el.attr('data-mx-blob-model'),' = ', previewFile);
    	});
		//var clearEl = el.parents().find('.file-preview').find('.mx-fileinput-clear');
		//clearEl.addClass('hide');
		//$log.debug('--------', clearEl);
		el.fileinput('enable');
	};
	
	fileInputService.fileuploaderrorHandler = function(event, data, previewId, index) {
		var scope = fileInputService.scope;
		$log.debug('event = ', event);
		$log.debug('data = ', data);
		//////////////// clear data-mx-blob-model ////////////////
		
		var el = angular.element(event.target);
		$log.debug('el = ', el);
		scope.$apply(function(scope) {
	    	var previewFile = $parse(el.attr('data-mx-blob-model'))(scope);
			$log.debug('fileuploaderror: data-mx-blob-model = ', el.attr('data-mx-blob-model'));
			$log.debug('fileuploaderror: ', el.attr('data-mx-blob-model'),' = ', previewFile);
			//if (previewFile)
				$parse(el.attr('data-mx-blob-model')).assign(scope, null);
			//delete scope[el.attr('data-mx-blob-model')];
    	});
		el.fileinput('enable');
    	
	};
	
	fileInputService.fileerrorHandler = function(event, data, previewId, index) {
		var scope = fileInputService.scope;
		$log.debug('event = ', event);
		$log.debug('data = ', data);
		//////////////// clear data-mx-blob-model ////////////////
		
		var el = angular.element(event.target);
		$log.debug('el = ', el);
		scope.$apply(function(scope) {
	    	var previewFile = $parse(el.attr('data-mx-blob-model'))(scope);
			$log.debug('fileerror: data-mx-blob-model = ', el.attr('data-mx-blob-model'));
			$log.debug('fileerror: ', el.attr('data-mx-blob-model'),' = ', previewFile);
			//if (previewFile)
				$parse(el.attr('data-mx-blob-model')).assign(scope, null);
			//delete scope[el.attr('data-mx-blob-model')];
    	});
    	
	};
	
	fileInputService.fileimageloadedHandler = function(event, data, previewId, index) {
		var scope = fileInputService.scope;
		$log.debug('event = ', event);
		$log.debug('data = ', data);
		
		var fileinputEl = angular.element(event.target);
		var previewFile = $parse(fileinputEl.attr('data-mx-blob-model'))(scope);
		$log.debug('fileimageloaded: ', fileinputEl.attr('data-mx-blob-model'),' = ', previewFile);
		if ( ! previewFile) {
			//fileinputEl.fileinput('clear');
			fileinputEl.fileinput('reset');
		}
		else {
			fileinputEl.fileinput('disable');
		}
	};
	
	fileInputService.fileselectHandler = function(event, data, filename) {
		var scope = fileInputService.scope;
		$log.debug('event = ', event);
		$log.debug('data = ', data);
		$log.debug('filename = ', filename);
		
	};
	
	fileInputService.fileinputInitHandlers = function(scope){
		
		$(".mx-fileinput").each(function(){
			var el = $(this);
			//$log.debug('---- fileinput element = ', el);
						
			var customEvents = {};
			customEvents.fileselect = fileInputService.fileselectHandler;
			customEvents.fileloaded = fileInputService.fileloadedHandler;
			customEvents.fileclear = fileInputService.fileclearHandler;
			customEvents.fileuploaderror = fileInputService.fileuploaderrorHandler;
			customEvents.fileerror = fileInputService.fileerrorHandler;
			customEvents.fileimageloaded = fileInputService.fileimageloadedHandler;
			angular.forEach(customEvents, function(eventHandler, eventName) {
				//$log.debug('---- fileinput event handler: ', eventName, eventHandler);
				$(this).off(eventName, eventHandler);
				$(this).on(eventName, eventHandler);
			});
			
			customEvents = $parse($(this).attr('data-mx-custom-events'))(scope);
			//$log.debug('---- fileinput customEvents = ', customEvents);
			angular.forEach(customEvents, function(eventHandler, eventName) {
				//$log.debug('---- fileinput event handler: ', eventName, eventHandler, el);
				el.off(eventName, eventHandler);
				el.on(eventName, eventHandler);
			});
		});
	};
    
	return fileInputService;
    
}]);