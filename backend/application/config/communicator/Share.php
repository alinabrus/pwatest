<?php  

$twitter_url = array(
		INSTANCE_DEV	=> 	'https://urls.api.twitter.com/1/urls/count.json?url=',
		INSTANCE_QA		=> 	'https://urls.api.twitter.com/1/urls/count.json?url=',
		INSTANCE_PROD	=>	'https://urls.api.twitter.com/1/urls/count.json?url=',
);
$config['twitter_url'] = isset($twitter_url[INSTANCE]) ? $twitter_url[INSTANCE] : '';
$facebook_url = array(
		INSTANCE_DEV	=> 	'https://graph.facebook.com/?id=',
		INSTANCE_QA		=> 	'https://graph.facebook.com/?id=',
		INSTANCE_PROD	=>	'https://graph.facebook.com/?id=',
);
$config['facebook_url'] = isset($facebook_url[INSTANCE]) ? $facebook_url[INSTANCE] : '';