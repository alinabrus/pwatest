<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/*
 |--------------------------------------------------------------------------
 | Application constants
 |--------------------------------------------------------------------------
 |
*/
define('DEBUG', TRUE);

define('JWT_CONSUMER_KEY', '3d26b0b17065c2cf29c06c010184c684');
define('JWT_CONSUMER_SECRET', 'forget_me_not');
define('JWT_CONSUMER_TTL', 86400);

define('ACCESS_GROUP_AUTHORIZED', 'auth');
define('ACCESS_GROUP_PUBLIC', 'pub');
define('ACCESS_GROUP_SUPERADMIN', 'superadmin');
define('ACCESS_GROUP_ADMIN', 'admin');
define('ACCESS_GROUP_MODERATOR', 'moderator');
define('ACCESS_GROUP_USER', 'members');
define('ACCESS_GROUP_TESTER', 'tester');
/*
define('API_RESPONSE_KEY_EXIT_CODE', 'exitCode');
define('API_RESPONSE_KEY_MESSAGE', 'message');
define('API_RESPONSE_KEY_MESSAGE_LABEL_ID', 'messageLabelId');
define('API_RESPONSE_KEY_DATA', 'data');
*/

define('ACC_GROUP_USERS', 'users');
define('ACC_GROUP_ADMINS', 'admins');

define('PHONE_STATUS_BLACKLISTED', 0);
define('PHONE_STATUS_WHITELISTED', 1);

define('DATA_FILTER_VALUE_UNFILTERED', 'grid_unfiltered');

define('FILES_TMP_PATH', 'files/tmp/');

define('FILES_WEB_LOGO_MAX_WIDTH', 850); //500

define('FILES_AD_PICTURE_MAX_WIDTH', 850);
define('FILES_FB_IMG_MIN_WIDTH', 200);
define('FILES_FB_IMG_MIN_HEIGHT', 200);
define('FILES_FB_IMG_RECOMMENDED_MIN_WIDTH', 602);
define('FILES_FB_IMG_RECOMMENDED_MIN_HEIGHT', 315);
define('FILES_FB_IMG_ASPECT_RATIO', 1.91);

define('DONATION_STATUS_ACTIVE', 1);
define('DONATION_STATUS_DELETED', 0);

define('DONATION_NOT_REFUNDED', 0);
define('DONATION_REFUNDED_PARTIALLY', 1);
define('DONATION_REFUNDED_FULLY', 2);

define('DONATION_TYPE_IN_CASH', 0);
define('DONATION_TYPE_DONATE', 1);
define('DONATION_TYPE_STORE_SELL', 2);

define('DONATION_SHIPPING_COST', 5);
define('DONATION_TAX_PERCENT', 7.63);

define('FTP_HOST', '52.4.153.49');
define('FTP_USER', 'maxletics');
define('FTP_PASS', 'UvLLsArAQWAyh6Kb0IL');
define('FTP_FOLDER', 'ftp'); // /home/maxletics/ftp
define('FTP_TEST_FOLDER', 'ftp/test'); // /home/maxletics/ftp/test

define('CAMP_IMG_FTP_NONE', 0);
define('CAMP_IMG_FTP_WAIT', 1);
define('CAMP_IMG_FTP_UPLOADING', 2);
define('CAMP_IMG_FTP_UPLOADED', 3);

define('CAMP_STORE_IMG_LOCALLY_STORED_MAX_SIZE', 5242880);
//define('CAMP_STORE_IMG_LOCALLY_STORED_MAX_SIZE', 5000);

/*
 |--------------------------------------------------------------------------
 | Application Error Codes
 |--------------------------------------------------------------------------
*/

define('ERR_NONE',0);
define('ERR_UNKNOWN',1000);

define('ERR_PHP_ERROR', 1000000);

define('ERR_METHOD_NOT_FOUND', 1001);
define('ERR_MISSING_PARAMETER',1002);
define('ERR_INVALID_PARAMETER',1003);
define('ERR_INVALID_PARAMETER_VALUE', 1004);
define('ERR_VALIDATION_FAILED', 1005);

define('ERR_ACCESS_DENIED', 2000);
define('ERR_INVALID_CREDENTIALS', 2010);
define('ERR_UNAUTHORIZED', 2011);

define('ERR_DB_DML_ERROR', 3000);

define('ERR_USER_NOT_FOUND', 4000);
define('ERR_ORGANIZATION_NOT_FOUND', 4001);
define('ERR_CAMPAIGN_NOT_FOUND', 4002);
define('ERR_SPONSOR_NOT_FOUND', 4003);
define('ERR_DONATION_NOT_FOUND', 4005);

define('ERR_IMPORT_LOAD_FAILURE', 5000);

define('ERR_IMPORT_VALIDATION_KEY_MISSING', 5001);
define('ERR_IMPORT_VALIDATION_FAILED', 5002);

define('ERR_IMPORT_FILE_FORMAT_FAILURE', 5003);

define('ERR_IMPORT_FILE_UNKNOWN_EXTENTION', 5004);

define('ERR_FILE_UPLOAD', 6000);
define('ERR_FILE_UPLOAD_LOW_RESOLUTION_PDFIMAGES', 6100);

define('ERR_EMAIL_SENDING', 7000);

/*
 |--------------------------------------------------------------------------
 | 
 |--------------------------------------------------------------------------
*/

define('ACC_STATUS_INITIAL', 0);
define('ACC_STATUS_CONFIRMATION_RESUEST_SENT', 1);
define('ACC_STATUS_CONFIRMATION_RESUEST_SENDING_FAILED', 2);
define('ACC_STATUS_CONFIRMED', 3);




/* End of file constants.php */
/* Location: ./application/config/constants.php */