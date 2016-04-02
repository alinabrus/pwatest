<?php
//require_once CM_LIBS_PATH . 'external/php_telesign-master/telesign/api.class.php';
require_once CM_LIBS_PATH . 'telesign.php';

class CmTelesign extends CmModule
{
	
	private function _get_verify_object() {
		return new VerifySmart($this->config['customer_id'],
								$this->config['api_key'],
								$auth_method = "hmac-sha256", //accepted: "hmac-sha1", "hmac-sha256"
								$this->config['api_url'], //'https://rbox.telesign.com',
								$request_timeout = 5, // seconds
								$headers = array(),
								$curl_options = array(CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false)
								);
	}
	
	public function verify_smart($params) {
		$this->logger->log(__METHOD__.". Params: ", $params);
		
		$more = array();
		if (isset($this->config['sms_template']))
			$more['template'] = $this->config['sms_template']; //'Your Maxletics verification code is: $$CODE$$';
		
		$verify = $this->_get_verify_object();
		$response = (object) $verify->smart($params['phone_number'], $ucid = 'TRVF', $more, $params['verify_method']); //'13105551212'
		$this->logger->log(__METHOD__.". Response: ", $response);
		
		if ( ! empty($response->errors)) {
			$err = array();
			foreach ($response->errors as $error) {
				$err[] = implode(' | ', $error);
			}
			$err = implode(', ', $err);
			throw new Exception($err);
		}
		/*
		sleep(30);
		$response = (object) $verify->status($response->reference_id);
		$this->logger->log(__METHOD__.". Response: ", $response);
		*/
		/*
		$score_response = (object) $verify->score($params['phone_number'], $ucid = 'TRVF'); //'13602091207'
		$this->logger->log(__METHOD__.". Score Response: ", $score_response);
		*/
		return $response;
	}
	
	public function get_status($params) {
		$this->logger->log(__METHOD__.". Params: ", $params);
	
		$verify = $this->_get_verify_object();
	
		$response = (object) $verify->status($params['reference_id'], $params['verify_code']);
		$this->logger->log(__METHOD__.". Response: ", $response);
		
		return $response;
	}
	
	public function get_risk_score($params) {
		$this->logger->log(__METHOD__.". Params: ", $params);
	
		$verify = $this->_get_verify_object();
	
		$originating_ip = isset($params['originating_ip']) ? $params['originating_ip'] : null;
		$response = (object) $verify->score($params['phone_number'], $ucid = 'TRVF', $originating_ip); //'13602091207'
		$this->logger->log(__METHOD__.". Response: ", $response);
	
		return $response;
	}
	
}

/* EOF */