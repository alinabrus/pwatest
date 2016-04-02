App
	.service('GuidGenerator', function($log){
		
		this.getUniqueCode = function () {
			var result       = '';
	        var words        = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
	        var max_position = words.length - 1;
	            for( i = 0; i < 20; ++i ) {
	                position = Math.floor ( Math.random() * max_position );
	                result = result + words.substring(position, position + 1);
	            }
			return result;
		}
	});