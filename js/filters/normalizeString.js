App
.filter('normalize', function () {
	return function (input) {
		if (input) {
			return input.toLowerCase().replace(/[^a-z|0-9_]/g, '_');
		}
	};
});