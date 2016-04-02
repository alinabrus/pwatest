<?php  

$stripe_secret_key = array(
	INSTANCE_DEV		=> 	'',
	INSTANCE_QA		=> 	'',
	INSTANCE_PROD	=>	''
);

$config['stripe_secret_key'] = isset($stripe_secret_key[INSTANCE]) ? $stripe_secret_key[INSTANCE] : '';
$config['stripe_currency'] = 'usd';