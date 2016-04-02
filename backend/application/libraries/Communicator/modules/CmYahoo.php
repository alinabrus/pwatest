<?php
/*
*/
require_once CM_LIBS_PATH . 'curl.php';

class CmYahoo extends CmModule
{
	protected $curl;
	protected $accessToken;
	
	public function __construct($config, $logger, $model = null) {
		parent::__construct($config, $logger, $model);
		$this->curl = new Curl();
		$this->curl->set_certificate(false, false);
	}
	
	public function auth($params) {
		$this->logger->log(__METHOD__.". Params: ", $params);
		
		$accessTokenUrl = 'https://api.login.yahoo.com/oauth2/get_token';
		$vars = array (
			'code' => $params['code'],
			'redirect_uri' => $params['redirectUri'],
			'grant_type' => 'authorization_code'
		);
		$authHeader = "Authorization: Basic ".base64_encode($params['clientId'].':'.$this->config['client_secret']);
		$response = $this->curl->post($accessTokenUrl, $vars, array($authHeader));
		$this->logger->log(__METHOD__.". Response: ", $response);
		
		//return array('token' => '123');
		return json_decode($response->body);
	}
	
	public function contacts($params) {
		$this->logger->log(__METHOD__.". Params: ", $params);
	
		$authResponse = $this->auth($params);
		$this->logger->log(__METHOD__.". authResponse: ", $authResponse);
		if (isset($authResponse->error)) 
			throw new Exception('Yahoo authorization error: '.$authResponse->error.' ('.$authResponse->error_description.')');
			
		$url = "https://social.yahooapis.com/v1/user/{$authResponse->xoauth_yahoo_guid}/contacts";
		$vars = array (
				'format' => 'json',
				'count' => 'max'
		);
		$authHeader = "Authorization: Bearer ".$authResponse->access_token;
		$response = $this->curl->get($url, $vars, array($authHeader));
		$response = json_decode($response->body);
		//$this->logger->log(__METHOD__.". Response: ", $response);
		
		$contacts = array();
		foreach ($response->contacts->contact as $contact) {
			//$this->logger->log(__METHOD__.". contact: ", $contact);
			if ( ! empty($contact->fields)) {
				$fields = $contact->fields[0];
				$contact_add = array();
				foreach($contact->fields as $field)
				{
					if ($field->type === 'email') {
						//$contacts[] = array('id' => $fields->id, 'value' => $fields->value);
						$contact_add['id'] = $field->id;
						$contact_add['value'] = $field->value;
					}
					if($field->type === 'name') {
						$contact_add['name'] = $field->value;
					}
				}
				$contacts[] = $contact_add;
			}
		}
		$this->logger->log(__METHOD__.". Contacts: ", $contacts);
		return array('token' => '123', 'contacts' => $contacts);
	}
	
}

/* EOF */