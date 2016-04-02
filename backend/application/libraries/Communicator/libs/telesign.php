<?php
require_once CM_LIBS_PATH . 'external/php_telesign-master/telesign/api.class.php';

class VerifySmart extends Verify {
	
	public function smart($phone_number, $ucid = 'TRVF', $more = array(), 
											$preference = null, $ignore_risk = null, $language = null, 
											$originating_ip = null, $caller_id = null, $verify_code = null, $sms_template = null) 
	{
		if (!empty($ucid)) $more['ucid'] = $ucid;
		if (!empty($originating_ip)) $more['originating_ip'] = $originating_ip;
		if (!empty($caller_id)) $more['caller_id'] = $caller_id;
		if (!empty($language)) $more['language'] = $language;
		if (!empty($preference)) $more['preference'] = $preference;
		if (!empty($ignore_risk)) $more['ignore_risk'] = $ignore_risk;
		
		if ($preference == 'sms' && (!empty($sms_template) || !empty($more['template']))) {
			if (!empty($sms_template)) $more['template'] = $sms_template;
			return $this->verify($phone_number, $verify_code, 'sms', $more);
		}
		else
			return $this->verify($phone_number, $verify_code, 'smart', $more);
	}
	
	protected function _submit_and_get_response($post_data = "") {

		//curl_setopt($this->curl, CURLOPT_HEADER, TRUE);
		
		// apply all http headers (curl wants them in a flat array)
		$headers = array();
		$this->curl_headers['Content-Type'] = $this->content_type;
		foreach ($this->curl_headers as $hname => $hval) {
			$headers[] = $hname . ": " . $hval;
		}
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

		// curl settings for POST
		if (strlen($post_data)) {
			curl_setopt($this->curl, CURLOPT_POST, TRUE);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post_data);
		} else {
			curl_setopt($this->curl, CURLOPT_POST, FALSE);
		}

		// run the curl and get information
		$this->raw_response = curl_exec($this->curl);
		$this->curl_error_num = curl_errno($this->curl);
		$this->curl_error_desc = curl_error($this->curl);
	
		
		$this->info = curl_getinfo($this->curl);
		//$this->error = $this->curl_error_num.' ['.$this->curl_error_desc.']';
		$this->error = array('code' => $this->curl_error_num, 'description' => 'curl: '.$this->curl_error_desc);
		
		//logmes('$this = ',$this,'aaa');
        logmes(__METHOD__, '--------------------------------------------------', 'debug_log_'.__CLASS__);
        logmes(__METHOD__.' headers: ', $headers, 'debug_log_'.__CLASS__);
        logmes(__METHOD__.' post_data: ', $post_data, 'debug_log_'.__CLASS__);
        if ($this->info['http_code']!=200) 
        	logmes(__METHOD__.' response: ', $this->raw_response, 'debug_log_'.__CLASS__);
        logmes(__METHOD__.' error: ',json_encode($this->error),'debug_log_'.__CLASS__);
        logmes(__METHOD__.' curl info: ',$this->info,'debug_log_'.__CLASS__);
       	//logmes(__METHOD__.' apache_request_headers: ',apache_request_headers(),'debug_log_'.__CLASS__);
		
		// if there is error then return empty string
		if ($this->curl_error_num) {
			//return "";
			$response = array('errors' => array($this->error));
			return json_encode($response);
		}

		return $this->raw_response;
	}
	
	public function score($phone_number, $ucid = 'TRVF', $originating_ip = null) 
	{
		$resource = "/v1/phoneid/score/" . $phone_number;
		$url = $this->api_url . $resource . "?ucid=" . $ucid . (empty($originating_ip) ? "" : ("&originating_ip=" . $originating_ip));
		curl_setopt($this->curl, CURLOPT_URL, $url);
	
		$this->method = "GET";
		$this->content_type = "text/plain";
	
		$this->_sign($resource);
		return json_decode($this->_submit_and_get_response(), TRUE);
	}
	
}

/* EOF */
	