App.directive('esUiGridEditFileChooser',
		    ['gridUtil', 'uiGridConstants', 'uiGridEditConstants','$timeout', '$log',
		      function (gridUtil, uiGridConstants, uiGridEditConstants, $timeout, $log) {
		        return {
		          scope: true,
		          require: ['?^uiGrid', '?^uiGridRenderContainer'],
		          compile: function () {
		            return {
		              pre: function ($scope, $elm, $attrs) {
		            	
		              },
		              post: function ($scope, $elm, $attrs, controllers) {
		                var uiGridCtrl, renderContainerCtrl;
		                if (controllers[0]) { uiGridCtrl = controllers[0]; }
		                if (controllers[1]) { renderContainerCtrl = controllers[1]; }
		                var grid = uiGridCtrl.grid;

		                var handleFileSelect = function( event ){
		                  var target = event.srcElement || event.target;

		                  if (target && target.files && target.files.length > 0) {
		                    /**
		                     *  @ngdoc property
		                     *  @name editFileChooserCallback
		                     *  @propertyOf  ui.grid.edit.api:ColumnDef
		                     *  @description A function that should be called when any files have been chosen
		                     *  by the user.  You should use this to process the files appropriately for your
		                     *  application.
		                     *
		                     *  It passes the gridCol, the gridRow (from which you can get gridRow.entity),
		                     *  and the files.  The files are in the format as returned from the file chooser,
		                     *  an array of files, with each having useful information such as:
		                     *  - `files[0].lastModifiedDate`
		                     *  - `files[0].name`
		                     *  - `files[0].size`  (appears to be in bytes)
		                     *  - `files[0].type`  (MIME type by the looks)
		                     *
		                     *  Typically you would do something with these files - most commonly you would
		                     *  use the filename or read the file itself in.  The example function does both.
		                     *
		                     *  @example
		                     *  <pre>
		                     *  editFileChooserCallBack: function(gridRow, gridCol, files ){
		                     *    // ignore all but the first file, it can only choose one anyway
		                     *    // set the filename into this column
		                     *    gridRow.entity.filename = file[0].name;
		                     *
		                     *    // read the file and set it into a hidden column, which we may do stuff with later
		                     *    var setFile = function(fileContent){
		                     *      gridRow.entity.file = fileContent.currentTarget.result;
		                     *    };
		                     *    var reader = new FileReader();
		                     *    reader.onload = setFile;
		                     *    reader.readAsText( files[0] );
		                     *  }
		                     *  </pre>
		                     */
		                    if ( typeof($scope.col.colDef.editFileChooserCallback) === 'function' ) {
		                      $scope.col.colDef.editFileChooserCallback($scope.row, $scope.col, target.files);
		                    } /* else {
		                      gridUtil.logError('You need to set colDef.editFileChooserCallback to use the file chooser');
		                    }*/

		                    target.form.reset();
		                    $scope.$emit(uiGridEditConstants.events.END_CELL_EDIT);
		                  } else {
		                    $scope.$emit(uiGridEditConstants.events.CANCEL_CELL_EDIT);
		                  }
		                };

		                $elm[0].addEventListener('change', handleFileSelect, false);  // TODO: why the false on the end?  Google
		                /*
		                $log.debug('grid = ', grid);
		                $log.debug('$elm = ', $elm);
		                $log.debug('$attrs = ', $attrs);
		                */
		                $scope.$on(uiGridEditConstants.events.BEGIN_CELL_EDIT, function () {
		                  /*
		                  $elm[0].focus();
		                  //$elm[0].select();
		                  $elm.on('focusout', function (evt) {
		                	  $log.debug('focusout = ', evt.target);
		                	  //$scope.$emit(uiGridEditConstants.events.END_CELL_EDIT);
				          });
				          */
		                  //var id = $attrs['id'];
		                  //$elm.attr('for',id);
		                  /*
		                  var inputEl = angular.element($elm[0]).find('input:first');
		                  inputEl.attr('id',id);
		                  $log.debug('inputEl = ', inputEl);
		                  
		                  inputEl.focus();
		                  inputEl.select();
		                  
		                  inputEl.on('blur', function (evt) {
		                	$log.debug('blur: ', evt.target);
		                	$scope.$emit(uiGridEditConstants.events.END_CELL_EDIT);
			              });
		                  */
		                  
		                  /*
		                  $scope.$on('mx-fileloaded', function (evt) {
		                    $scope.$emit(uiGridEditConstants.events.END_CELL_EDIT);
		                  });
		                  */
		                  
		                });
		              }
		            };
		          }
		        };
		      }]);