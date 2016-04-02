<?php  

$client_id = array(
		INSTANCE_DEV	=> 	'',
		INSTANCE_QA		=> 	'',
		INSTANCE_PROD	=>	'',
);
$config['client_id'] = isset($client_id[INSTANCE]) ? $client_id[INSTANCE] : '';

$client_secret = array(
		INSTANCE_DEV	=> 	'', 
		INSTANCE_QA		=> 	'',
		INSTANCE_PROD	=>	''
);
$config['client_secret'] = isset($client_secret[INSTANCE]) ? $client_secret[INSTANCE] : '';
