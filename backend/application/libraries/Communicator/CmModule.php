<?php

class CmModule
{
	protected $config;
	protected $logger;
	protected $model;
	
	public function __construct($config, $logger,  $model = null) {
		$this->config = $config;
		$this->logger = $logger;
		$this->model = $model;
	}
	
	public function request ($type, array $params = array()) {
		$response = new stdClass;
		try {
			$response->result = call_user_func_array(array($this, $type), $params);
			$response->error = null;
		}
		catch (Exception $e) {
			$this->logger->log("Communicator $type request error in module ". get_class($this) .". ". $e->getMessage().". Params: ", $params);
			$response->result = null;
			$response->error = $e->getMessage();
		}
		return $response;
	}
		
}
