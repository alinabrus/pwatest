<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function send_email (array $recipients, $email_template_name, $subject, $from_name = null, $attachment_file_path = null, $message = null, $use_basic_email_tpl = null) 
{
	
		static $ci;
  		if (!is_object($ci)) $ci = &get_instance();
				
		$ci->load->config('ion_auth', TRUE);
		$ci->load->library(array('ion_auth', 'email'));
		$email_config = $ci->config->item('email_config', 'ion_auth');
		//logmes('$email_config = ', $email_config, 'aaa');
		if ($ci->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
		{
			$ci->email->initialize($email_config);
		}
		
		if(empty($from_name))
			$from_name = $ci->config->item('from_name', 'ion_auth');
		
		$replacements_pattern = '/%(.+?)%/';
		
		$from_name_replacements_count = preg_match_all($replacements_pattern, $from_name, $from_name_replacements);
		//logmes('$from_name_replacements = ',$from_name_replacements,'aaa');
		if ($from_name_replacements_count === false)
			return false;
		
		$subject_replacements_count = preg_match_all($replacements_pattern, $subject, $subject_replacements);
		//logmes('$subject_replacements = ',$subject_replacements,'aaa');
		if ($subject_replacements_count === false)
			return false;
		
		$from_name_tpl = $from_name;
		$subject_tpl = $subject;
		$sendings_success_count = 0; 
		$sendings_error_count = 0; 
		$sendings_error_recipients = $sendings_errors = array();
		foreach ($recipients as $recipient) {
			
			if ( ! isset($recipient->email)) 
				$recipient->email = '';
			
			$internal_email_flag = false;
			$internal_recipients = array('admin', 'info', 'watcher');
			foreach ($internal_recipients as $ir) {
				$internal_email_flag = true;
				if (isset($recipient->{'is_'.$ir}) && $recipient->{'is_'.$ir} && isset($email_config[$ir.'_email']))
					$recipient->email = $email_config[$ir.'_email'];
			}
			
			$logname = 'email__'.$email_template_name;
			$subject = $subject_tpl;
			
			try {
				
				if ($from_name_replacements_count > 0) {
					for ($i = 0; $i < $from_name_replacements_count; $i++) {
						$replacement_key = $from_name_replacements[1][$i];
						if (isset($recipient->data[$replacement_key]))
							$from_name = str_replace($from_name_replacements[0][$i], $recipient->data[$replacement_key], $from_name);
						else
							throw new Exception("'{$replacement_key}' is not found in recipient's data. Email sending cancelled.");
					}
				}
				if ($subject_replacements_count > 0) {
					for ($i = 0; $i < $subject_replacements_count; $i++) {
						$replacement_key = $subject_replacements[1][$i];
						if (isset($recipient->data[$replacement_key]))
							$subject = str_replace($subject_replacements[0][$i], $recipient->data[$replacement_key], $subject);
						else 
							throw new Exception("'{$replacement_key}' is not found in recipient's data. Email sending cancelled.");
					}
				}
				$basic_email_tpl = $ci->config->item('basic_email_template', 'ion_auth');
				$data = $recipient->data;
				$data['site_title'] = $ci->config->item('site_title', 'ion_auth');
				
				if ( ! empty($email_template_name)) {
					$data['email_body_tpl'] = $ci->config->item($email_template_name, 'ion_auth');
				}
				else {
					$data['email_body'] = $message;
				}
				
				if (is_null($use_basic_email_tpl))
					$use_basic_email_tpl = ! $internal_email_flag;
					
				$email_templates_path = $ci->config->item('email_templates', 'ion_auth');
				
				if ($use_basic_email_tpl)
					$message = $ci->load->view($email_templates_path.$basic_email_tpl, $data, true);
				else 
					$message = $ci->load->view($email_templates_path.$data['email_body_tpl'], $data, true);
				
				$ci->email->clear();
				//$ci->email->from($ci->config->item('admin_email', 'ion_auth'), $from_name);
				$from_email = ! empty($email_config['admin_email']) ? 
											(is_array($email_config['admin_email']) ? $email_config['admin_email'][0] : $email_config['admin_email']) : 
											$ci->config->item('admin_email', 'ion_auth');
				$ci->email->from($from_email, $from_name);
				
				$ci->email->to($recipient->email);
				if (	(isset($recipient->is_admin) && $recipient->is_admin) 
						|| (isset($recipient->is_info) && $recipient->is_info) 
						|| (isset($recipient->send_cc) && $recipient->send_cc)
				)  {
					$ci->email->cc($email_config['cc_email']);
					$ci->email->bcc($email_config['bcc_email']);
				}
				$ci->email->subject($subject);
				$ci->email->message($message);
				
				logmes('---------------------------------------------','',$logname);
				logmes('$recipient->email = ',$recipient->email,$logname);
				logmes('-----------------------------------','',$logname);
				logmes('$subject = ',$subject,$logname);
				logmes('---------------------------------------------','',$logname);
				logmes('$message = ',$message,$logname);
				logmes('---------------------------------------------','',$logname);
				logmes('$attachment_file_path = ',$attachment_file_path,$logname);
				logmes('---------------------------------------------','',$logname);
				
				//ap: quick add-on for MAXL-138
				if($attachment_file_path)
					$ci->email->attach($attachment_file_path);
				
				$sent_result = $ci->email->send();
				if ( ! $sent_result) {
					$msg = 'Failed to send email "'.$subject.'".';
					//$msg = $ci->email->print_debugger();
					throw new Exception($msg);
				}
				
			}
			catch (Exception $e) {
				$sent_result = false;
				logmes('Exception: ',$e->getMessage(),$logname);
				$sendings_error_recipients[] = $recipient->email;
				$email = is_array($recipient->email) ? implode(',', $recipient->email) : $recipient->email;
				if (empty($email)) {
					$email = $recipient;
					if (isset($email->data)) unset($email->data); 
					$email = var_export($email, true);
					logmes('Exception recipient: ',$email,$logname);
				}
				$sendings_errors[] = "Recipient ".$email.". ".$e->getMessage();
			}
			
			if ($sent_result) {
				$sendings_success_count++;
			}
			else {
				$sendings_error_count++;
			}
		}
		
		return array(
					'sendings_success_count' => $sendings_success_count,
					'sendings_error_count' => $sendings_error_count,
					'sendings_error_recipients' => $sendings_error_recipients,
					'sendings_errors' => $sendings_errors
				);
}
	
/* End of file */