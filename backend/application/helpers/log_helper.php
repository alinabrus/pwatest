<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function logmes($msg, $var='#none', $filename='', $level = 'debug')
{
	static $n = 0;

	if (empty($filename)) {
		static $ci;
    	if (!is_object($ci)) $ci = &get_instance();
		$filename = 'log-'.str_replace('/','-',$ci->uri->uri_string);
	}
	$filepath = APPPATH."logs/$filename-".date('Y-m-d').EXT;
	$message  = '';

	if ( ! file_exists($filepath))
	{
		$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
	}

	if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE)) /* @fopen($filepath, FOPEN_WRITE_CREATE) */
	{
		return FALSE;
	}

	if ($n++==0) $message .= "\n-- -----------------------------------------------------\n\n";
	if ($var!='#none') $msg .= var_export($var,true);
	$message .= '-- '.$level.' '.(($level == 'INFO') ? ' -' : '-').' '.date('y-m-d G:i:s'). ' --> '.$msg."\n";

	flock($fp, LOCK_EX);
	fwrite($fp, $message);
	flock($fp, LOCK_UN);
	fclose($fp);

	@chmod($filepath, FILE_WRITE_MODE);
	return TRUE;
}

function email_send($email, $subject, $message, $config=array(),$attachments=array()) {
  static $ci;
  if (!is_object($ci)) $ci = &get_instance();
  
  $ci->load->library('email', $config);
    
  $ci->email->clear(TRUE);      
  $attachments = is_array($attachments) ? $attachments : array($attachments);
  
  foreach ( $attachments as $att) {
  	$ci->email->attach($att);
  }  
 
  $ci->email->from($ci->config->item('admin_email'), $ci->config->item('site_name'));
  $ci->email->to($email);
  $ci->email->subject($subject);
  $ci->email->message($message);
  
  return $ci->email->send();    
}

function get_path($path)
{
	$basepath = dirname(BASEPATH);
	if (strpos($path,$basepath) === FALSE) $path = "$basepath/".trim($path,'/');
	if ( ! file_exists($path))
	{
		$old_mask = umask(0);
		if ( ! mkdir($path,0777, TRUE)) {
			log_message('error', __METHOD__.' : Can\'t create directory ['.$path.'].');
			$path = FALSE;
		}
		umask($old_mask);
		if ($old_mask != umask())  log_message('error', __METHOD__.' : An error occured while changing back the umask');
	}
	return $path;
}
	
/* End of file */