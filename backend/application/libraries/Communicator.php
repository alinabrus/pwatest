<?php

if ( ! defined('CM_BASE_PATH')) define('CM_BASE_PATH', dirname(__FILE__) . '/Communicator/');
if ( ! defined('CM_LIBS_PATH')) define('CM_LIBS_PATH', CM_BASE_PATH . '/libs/');
if ( ! defined('CM_MODULES_PATH')) define('CM_MODULES_PATH', CM_BASE_PATH . '/modules/');

require_once(CM_BASE_PATH . "CmModule.php");
require_once(CM_BASE_PATH . "CmLogger.php");

require_once(CM_LIBS_PATH . 'curl.php');

class Communicator
{
	protected $module;
	protected $logger;
	
	public function __construct() {
		//logmes(__METHOD__,'','aaa');
	}

	public function init ($moduleName, array $config = array(), $model = null) {
		try {
			
			$this->logger = new CmLogger('communicator_'.$moduleName);
			//logmes(__METHOD__,$this->logger,'aaa');
			$moduleName = ucfirst($moduleName);
			
			if(@file_exists(CM_MODULES_PATH . "Cm$moduleName.php")) include_once(CM_MODULES_PATH . "Cm$moduleName.php");
	        else 
	        	throw new Exception(__METHOD__.": Module $moduleName does not exists");
			
	        static $CI;
	        if (function_exists('get_instance')) {
	        	if ( ! is_object($CI)) 
	        		$CI = &get_instance();
	        }
	        if (is_object($CI)) {
	        	$path = APPPATH."config/communicator/$moduleName.php";
	        	
	        	if ( ! file_exists($path))
	        		throw new Exception("Communicator config $moduleName is not exists!");
	        	
	        	//if(@file_exists($path)) {
	        		$CI->load->config("communicator/$moduleName", true);
	        		$config = $CI->config->item("communicator/".$moduleName);
	        	//}
	        	//logmes(__METHOD__.' config path: ',$path,'aaa');
	        	//logmes(__METHOD__.' config: ',$config,'aaa');
	        }
	        
	        $module = "Cm$moduleName";
	        $this->module = new $module($config, $this->logger, $model);
	        
	        //$result = true;
        }
        catch (Exception $e) {
        	$result = false;
        	$this->logger->log("Unable to initialize communicator module $moduleName. ". $e->getMessage() . " Config: ", $config);
        }
        $this->logger->log("Communicator module $moduleName initialized. ", $config);
        //return $result;
        return $this;
	}
	
	public function request ($type, array $params = array()) {
		try {
			if ( ! is_object($this->module))
				throw new Exception(__METHOD__.": Communicator module has not been initialized.");
			
			return $this->module->request($type, $params);
		}
		catch (Exception $e) {
			$this->logger->log("Communicator $type request error in module ". get_class($this->module) .". ". $e->getMessage().". Params: ", $params);
			throw $e;
		}
	}
	
	public function curl_request ($type, $url, array $params = array(), array $headers = array()) {
		try {
			$this->logger = new CmLogger('communicator');
			
			$curl = new Curl();
			$curl->set_certificate(false, false);
			//$curl->follow_redirects = false;
			
			if (strtolower($type) == 'get') 
				$response = $curl->get($url, $params, $headers);
			elseif (strtolower($type) == 'post')
				$response = $curl->post($url, $params, $headers);
			
			if ( ! $response)
				throw new Exception($curl->error);
			else 
				$this->logger->log("Communicator $type curl_request response: ". $response.". Params: ", $params);
			
			return $response;
		}
		catch (Exception $e) {
			$this->logger->log("Communicator $type curl_request error: ". $e->getMessage().". Params: ", $params);
			throw $e;
		}
	}
	
}
