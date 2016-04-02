<?php
/*
*/
require_once CM_LIBS_PATH . 'curl.php';

class CmShare extends CmModule
{
	protected $curl;
	protected $accessToken;
	
	public function __construct($config, $logger, $model = null) {
		parent::__construct($config, $logger, $model);
		$this->curl = new Curl();
		$this->curl->set_certificate(false, false);
	}
	
	public function share_count($params) {
		$this->logger->log(__METHOD__.". Params: ", $params);
	
        $url = '';
		switch($params['social'])
        {
            case 'twitter':
                    $url = $this->config['twitter_url'];
                break;
            case 'facebook' :
                    $url = $this->config['facebook_url'];
                break;
            default : 
                break;
        }
		$url = $url.urlencode($params['url']);
		$authHeader = "Authorization: Bearer 123";
		$this->logger->log(__METHOD__.". url: ", $url);
		$response = $this->curl->get($url);//, array(), array($authHeader));
		$response = json_decode($response->body);
		$this->logger->log(__METHOD__.". Response: ", $response);
		
		
		return array($response);
	}
	
}

/* EOF */