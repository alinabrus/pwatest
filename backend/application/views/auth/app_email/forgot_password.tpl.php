<?php 
$reset_password_url = str_replace('/backend', '', site_url('#/reset_password/'. $forgotten_password_code));
$email_body_tpl = 'forgot_password.tpl.php';
include ('_mx_email.tpl.php'); 
?>