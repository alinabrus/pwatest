App
.factory('uiGridService', function ($http, $rootScope) {

var factory = {};

factory.gridState = {};

factory.getGridHeight = function(gridOptions) {

    var length = gridOptions.data.length;
    var rowHeight = 30;
    var headerHeight = 40;
    var filterHeight = gridOptions.enableFiltering ? 78 : 40;
    
    return length * rowHeight + headerHeight + filterHeight + 10 +"px";
}
factory.removeUnit = function(value, unit) {
    return value.replace(unit, '');
}
/*
factory.saveGridState = function(gridName, state) {
	factory.gridState[gridName] = state;
}
*/
return factory;

});