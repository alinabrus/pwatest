<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| URL Access rules
|--------------------------------------------------------------------------
|
|
*/
defined('ACCESS_GROUP_AUTHORIZED') ? : define('ACCESS_GROUP_AUTHORIZED', 'auth');
defined('ACCESS_GROUP_PUBLIC') ? : define('ACCESS_GROUP_PUBLIC', 'pub');
defined('ACCESS_GROUP_TESTER') ? : define('ACCESS_GROUP_TESTER', 'tester');

//$config['manage/(.*)/api'] = ACCESS_GROUP_AUTHORIZED;
//$config['manage/user/api/login'] = ACCESS_GROUP_PUBLIC;

$config['(.*)/api'] = ACCESS_GROUP_AUTHORIZED;

$config['/api/test/'] = ACCESS_GROUP_PUBLIC;
$config['/auth/'] = ACCESS_GROUP_PUBLIC;
$config['/register/'] = ACCESS_GROUP_PUBLIC;

$config['/org/'] = ACCESS_GROUP_PUBLIC;
$config['/org/campaign_add'] = ACCESS_GROUP_AUTHORIZED;

$config['/admin/'] = array(ACCESS_GROUP_SUPERADMIN);

$config['/auth/admin_confirm_registration'] = array(ACCESS_GROUP_ADMIN);


/* End of file url_access.php */
/* Location: ./application/config/url_access.php */