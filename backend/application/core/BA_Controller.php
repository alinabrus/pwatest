<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BA_Controller extends CI_Controller
{
	protected $params = array();
	protected $callHandlerErrors = array();
	protected $accessFlag = FALSE;
	protected $accessGroup = array(ACCESS_GROUP_PUBLIC);
	protected $resourceAccessGroup = array();
	protected $debugLogName = 'debug_log';
	protected $errorLogName = 'error_log';
	
	public function __construct()
	{
		parent::__construct();
		
		$this->params = $this->_input();
		
		//$this->session->set_userdata('user_group', null);
		$accessGroup = $this->session->userdata('user_group');
		if (DEBUG) logmes(__METHOD__.' $accessGroup = ', $accessGroup, $this->debugLogName);
		if ( ! empty($accessGroup)) {
			$this->accessGroup = is_array($accessGroup) ? $accessGroup : array($accessGroup);
		}
	}
	
	function _remap($method, $args)
	{		
		if (DEBUG) logmes(__METHOD__.' $method = ', $method, $this->debugLogName);
		
		$this->accessFlag = $this->urlAccess($this->getUrlAccessGroup(uri_string()), $this->accessGroup);
		
		if ( !  $this->accessFlag && $method != 'api') {
			redirect(base_url(), 'location');
		}
		else 
		{
			//$this->load->library('profiler');
			
			if (method_exists($this, $method))
					call_user_func_array(array(&$this, $method), array_slice($this->uri->rsegments, 2));
			else 
				redirect(base_url(), 'location');
			/*
			if ($this->config->item('enable_profiler')) 
				$this->profiler->run();
			*/
		}
	}
	
	protected function getUrlAccessGroup($uriString) 
	{
		if (DEBUG) logmes(__METHOD__.' $uriString = ', $uriString, $this->debugLogName);
		
		if (empty($uriString)){
			$accessGroup = ACCESS_GROUP_PUBLIC;
		}
		else {
			$uriString = '/'.trim($uriString,'/').'/';
			
			$this->config->load('url_access', true);
			$config = $this->config->item('url_access');
			
			$accessGroup = ACCESS_GROUP_AUTHORIZED.'_init';
			if (is_array($config)) {
				foreach ($config as $urlPattern => $urlAccessGroup) {
					$urlPattern = str_replace('/','\/', $urlPattern);
					if ($res = preg_match("/$urlPattern/", $uriString)) $accessGroup = $urlAccessGroup; 
					//logmes(__METHOD__.' $res = ', $res, 'debug_log_'.__CLASS__.'_'.__FUNCTION__);
				}
			}
		}
		$accessGroup = is_array($accessGroup) ? $accessGroup : array($accessGroup);
		$this->resourceAccessGroup = $accessGroup;
		if (DEBUG) logmes(__METHOD__.' $resourceAccessGroup = ', $this->resourceAccessGroup, $this->debugLogName);
		
		return $this->resourceAccessGroup;
	}
		
	protected function urlAccess(array $resourceAccessGroup, array $userAccessGroup) 
	{
		if (DEBUG) logmes(__METHOD__.' $userAccessGroup = ', $userAccessGroup, $this->debugLogName);
		
		if (in_array(ACCESS_GROUP_AUTHORIZED, $resourceAccessGroup) && implode(',', $userAccessGroup) != ACCESS_GROUP_PUBLIC) 
			return TRUE;
		
		//if ( ! in_array($userAccessGroup, $resourceAccessGroup) && ! in_array(ACCESS_GROUP_PUBLIC, $resourceAccessGroup))
		$match = array_intersect($userAccessGroup, $resourceAccessGroup);
		if (empty($match) && ! in_array(ACCESS_GROUP_PUBLIC, $resourceAccessGroup))
		{	
			if (DEBUG) logmes(__METHOD__.' Unacceptable $userAccessGroup for non-public resource ', "", $this->debugLogName);
			return FALSE;
		}
		return TRUE;
	}
	
	protected function _input()
	{
		if ($post = $this->input->post()) {
			foreach ($post as $param => &$value) {
				$decoded_value = is_string($value) ? json_decode($value, true) : null;
				$value = $decoded_value === null ? $value : $decoded_value;
			}
		}
		//logmes(__METHOD__.' 1 $post = ',$post,'debug_log_'.__CLASS__);
		
		//if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($post))
		if (empty($post)) 
    		$post = json_decode(file_get_contents('php://input'), true);
		
		//logmes(__METHOD__.' 2 $post = ',$post,'debug_log_'.__CLASS__);
		
		$params = array_merge($this->_getVars(), is_array($post) ? $post : array());
		if ( !is_array($_REQUEST) && !empty($_REQUEST) ) {
			$params['_request'] = $_REQUEST;
		}
		//logmes(__METHOD__.' $params = ',$params,'debug_log_'.__CLASS__);
		return (object) $params;
	}
	
	public function api ($args = null)
	{
		$method = array_shift($args);
		//logmes(__METHOD__.' $method = ',$method, $this->debugLogName);
		//logmes(__METHOD__.' $args = ',$args, $this->debugLogName);
		
		$params = (array) $this->params;
		$params = array_merge($params, $args);
		$methodPrefix = ''; //'_api_';
		
		$method = empty($method) && isset($params['method']) ? $params['method'] : $method;
		
		if (empty($method)) 
			throw new Exception("Method '$method' is not found", ERR_METHOD_NOT_FOUND);
			//return $this->error("Method '$method' is not found", ERR_METHOD_NOT_FOUND);
		else 
			$method = $methodPrefix.$method;
		
		$result = $this->call($method, $params);

		if (DEBUG) {
			logmes(__METHOD__.'  $params = ',$params, $this->debugLogName);
			logmes(__METHOD__.'  $method = ',$method, $this->debugLogName);
			logmes(__METHOD__.'  $result = ',$result, $this->debugLogName);
			logmes(__METHOD__.'  $this->callHandlerErrors = ',$this->callHandlerErrors, $this->debugLogName);
		}
		
		if ($result === FALSE && ! empty($this->callHandlerErrors[$method])) {
			$exitCode = $this->callHandlerErrors[$method][0]['errCode'];
			$message = $this->callHandlerErrors[$method][0]['errMessage'];
			throw new Exception($message, $exitCode);
			//return $this->error($message, $exitCode);
		} 
		
		return $result;
	}
		
	public function api_old ($method = null)
	{
		//$method = '_api_'.$this->input->post('method');
		//$result = call_user_func_array(array(&$this, "_api_$method"), array());
		
		$params = $this->_input();
		
		logmes(__METHOD__.'  $params = ',$params,'debug_log_'.__CLASS__);
		//print_var_name($FooBar);
		
		$exitCode = ERR_NONE;
		$methodPrefix = '_api_';
		
		$method = empty($method) && isset($params['method']) ? $params['method'] : $method;
		
		if (empty($method)) 
			$exitCode = ERR_METHOD_NOT_FOUND;
		else 
			$method = $methodPrefix.$method;
			
		if ( ! $this->accessFlag) 
			 $exitCode = ERR_UNAUTHORIZED;
		
		$response = array(
			API_RESPONSE_KEY_EXIT_CODE => $exitCode,
			API_RESPONSE_KEY_MESSAGE => '',
			API_RESPONSE_KEY_MESSAGE_LABEL_ID => '',
			API_RESPONSE_KEY_DATA => array()
		);
		
		if ($exitCode == ERR_NONE)  
		{
			$result = $this->call($method, $params);
			
			logmes(__METHOD__.'  $method = ',$method,'debug_log_'.__CLASS__);
			logmes(__METHOD__.'  $result = ',$result,'debug_log_'.__CLASS__);
			logmes(__METHOD__.'  $this->callHandlerErrors = ',$this->callHandlerErrors,'debug_log_'.__CLASS__);
			
			if ($result === FALSE) {
				if (empty($this->callHandlerErrors[$method])) {
					$exitCode = ERR_UNKNOWN;
				}
				else {
					$exitCode = $this->callHandlerErrors[$method][0]['errCode'];
					$message = $this->callHandlerErrors[$method][0]['errMessage'];
				}
				$response[API_RESPONSE_KEY_EXIT_CODE] = $exitCode;
				if (isset($message)) $response[API_RESPONSE_KEY_MESSAGE] = $message;
			}
			else {
				$result = is_array($result) ? $result : array($result);
				foreach ($result as $key => $value) 
				{
					if (in_array($key, array_keys($response))) 
						$response[$key] = $value;
					else 
						$response[API_RESPONSE_KEY_DATA][$key] = $value;
				}
			}
		}		
		
		$constants = get_defined_constants();
		foreach($constants as $name => $value) {
			if (strpos($name, 'ERR_') === 0 && $value == $response[API_RESPONSE_KEY_EXIT_CODE])
				$response[API_RESPONSE_KEY_MESSAGE_LABEL_ID] = $name;
		}
		
		logmes(__METHOD__.'  $response = ',$response,'debug_log_'.__CLASS__);
		$this->output->set_output( json_encode($response) );
	}
	
	/**
	 * returns $_GET vars; workaround for special cases where it REALLY is
	 * required. Don't use where possible.
	 * @return	array
	 */
	protected function _getVars()
	{
		$rawParams = ltrim(strstr(get_instance()->input->server('REQUEST_URI'), '?'), '?');
		if ( !empty($rawParams) ) {
			$params = array();
			parse_str($rawParams, $params);
			return $params;
		}
		return array();
	}
	
	/**
	 * Universal interface to invoke methods into controller
	 * Intended for generic calls from browsers / requests from external systems.
	 * @param	string	$type		call method
	 * @param	array	$params		parameters for called method
	 * 								if need subarray, use syntax like item[prop1]=123&item[prop2]=asd
	 * @return	mixed
	 */
	public function call($type, array $params = array())
	{
		if ($this->callHandlerExists($type)) {
			return $this->callHandler($type, $params);
		}
		else {
			$this->callHandlerErrors[$type][] = array(
						'errCode' => ERR_METHOD_NOT_FOUND,
						'errMessage' => "Method $type is not found in ".get_class($this)." controller."
					);
			return FALSE;
		}
	}
	protected function callHandlerExists($type) {
		if ( !method_exists($this, $type) ) {
			return FALSE;
		}
		$methodRef = new ReflectionMethod($this, $type);
		return ( !($methodRef->isConstructor() || $methodRef->isDestructor() || $methodRef->isAbstract())
				&& $methodRef->isPublic() );
	}
	protected function callHandler($type, array $params = array())
	{
		$methodRef = new ReflectionMethod($this, $type);
		$args = $methodRef->getParameters();
		$callArgs = array();
		foreach ($args as $arg) {
			$value = NULL;
			if ( !array_key_exists($arg->name, $params) ) {
				if ($arg->isOptional()) {
					$value = $arg->getDefaultValue();
				} else {
					// behave like PHP here: assume NULL and don't give fatal
					$this->callHandlerErrors[$type][] = array(
						'errCode' => ERR_MISSING_PARAMETER,
						'errMessage' => "Missing required parameter for {$methodRef->class}->{$methodRef->name}({$arg->name})"
					);
					return FALSE;
				}
			} else {
				$value = $params[$arg->name];
			}
			$callArgs[$arg->getPosition()] = $value;
		}
		return $methodRef->invokeArgs($this, $callArgs);
	}
	
}

/* End of file BA_Controller.php */
/* Location:  */