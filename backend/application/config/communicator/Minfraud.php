<?php  

$user_id = array(
		INSTANCE_DEV	=> 	'',
		INSTANCE_QA		=> 	'',
		INSTANCE_PROD	=>	'',
);
$config['user_id'] = isset($user_id[INSTANCE]) ? $user_id[INSTANCE] : '';

$license_key = array(
		INSTANCE_DEV	=> 	'',
		INSTANCE_QA		=> 	'',
		INSTANCE_PROD	=>	''
);
$config['license_key'] = isset($license_key[INSTANCE]) ? $license_key[INSTANCE] : '';


