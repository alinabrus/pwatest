<?php  

$customer_id = array(
		INSTANCE_DEV		=> 	'',
		INSTANCE_QA		=> 	'',
		INSTANCE_PROD	=>	'',
);
$config['customer_id'] = isset($customer_id[INSTANCE]) ? $customer_id[INSTANCE] : '';

$api_key = array(
		INSTANCE_DEV		=> 	'', 
		INSTANCE_QA		=> 	'',
		INSTANCE_PROD	=>	''
);
$config['api_key'] = isset($api_key[INSTANCE]) ? $api_key[INSTANCE] : '';

$config['api_url'] = 'https://rest.telesign.com';

$config['sms_template'] = 'Your App verification code is: $$CODE$$';