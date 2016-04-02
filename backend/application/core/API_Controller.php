<?php
require('BA_Controller.php');

class API_Controller extends BA_Controller {
	
    protected $trusted_requester = FALSE;
    protected $client_tz_offset;
    //protected $client_id;
    //protected $client_secret;
    //protected $uid;
    //protected $user;
    protected $requestsLogName = 'requests';
    
    public static $statusMessages = array(
		ERR_NONE => array('httpStatus' => 200, 'message' => 'Success'),
		ERR_UNKNOWN => array('httpStatus' => 500, 'message' => 'Unknown error'),
		ERR_MISSING_PARAMETER => array('httpStatus' => 500, 'message' => 'Required parameter is missing'),
		ERR_INVALID_PARAMETER => array('httpStatus' => 500, 'message' => 'Unsupported parameter'),
		ERR_INVALID_PARAMETER_VALUE => array('httpStatus' => 200, 'message' => 'Invalid parameter value'),
		ERR_INVALID_CREDENTIALS => array('httpStatus' => 401, 'message' => 'Invalid credentials'),
		ERR_UNAUTHORIZED => array('httpStatus' => 401, 'message' => 'Access denied to unauthorized user'),
		ERR_DB_DML_ERROR => array('httpStatus' => 500, 'message' => 'Database error occurred on server'),
    	ERR_USER_NOT_FOUND => array('httpStatus' => 500, 'message' => 'User is not found'),
    	ERR_ORGANIZATION_NOT_FOUND => array('httpStatus' => 500, 'message' => 'Organization is not found'),
    	ERR_CAMPAIGN_NOT_FOUND => array('httpStatus' => 500, 'message' => 'Campaign is not found')
	);

    public function __construct() {
    	parent::__construct();
    	
    	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    		$headers = function_exists('apache_request_headers') ? var_export(apache_request_headers(), TRUE) : '';
    		$body = var_export(@file_get_contents('php://input'), TRUE);
    		logmes('uri_string() = ', uri_string(), $this->requestsLogName);
	    	logmes('$headers = ', $headers, $this->requestsLogName);
	    	logmes('$body = ', $body, $this->requestsLogName);
    	}
    	
    	//$this->params = json_decode(file_get_contents('php://input'));
    	        
        if (isset($this->params->token) && $this->params->token == md5('vjrhsqdfkmc'))
			$this->accessGroup = array(ACCESS_GROUP_TESTER);
        
        $this->client_tz_offset = $this->input->cookie('tz_offset');
        if (empty($this->client_tz_offset)){
          $this->session->set_userdata('client_tz_offset', 0);
          $this->session->set_userdata('client_tz_offset_in_minute', 0);
        }
        else 
        {
          $this->session->set_userdata('client_tz_offset', $this->client_tz_offset);
          $this->session->set_userdata('client_tz_offset_in_minute', -1*$this->client_tz_offset);
        }
//        logmes('client_tz_offset = ',$this->session->userdata('client_tz_offset'), 'at');

        //$this->_init();
        //logmes('all_userdata = ',$this->session->all_userdata(),$this->debugLogName);
        //logmes('tz_offset = ',$this->input->cookie('tz_offset'),$this->debugLogName);
    }
    
    protected function _init(){
        $hostname = gethostbyaddr($this->input->ip_address());
        $this->load->config('allowed_ip');
        $config = $this->config->config;
        if (isset($config["allowed_ip"])) {
            $this->trusted_requester = in_array($this->input->ip_address(), $config['allowed_ip']);
            if (DEBUG) logmes(__METHOD__.' requester ip : ', $this->input->ip_address(), $this->debugLogName);
            if (DEBUG) logmes(__METHOD__.' $this->trusted_requester = ', $this->trusted_requester, $this->debugLogName);
        }
    }
	
    
	function _remap($method, $args)
	{				
        array_unshift($args, $method);
        $method = 'api';
        
        /*if (method_exists($this, $method)) 
        {*/
			try {
        		
				$this->accessFlag = $this->urlAccess($this->getUrlAccessGroup(uri_string()), $this->accessGroup);
				
				if ( ! $this->accessFlag) {
					//redirect(base_url(), 'location');
					$this->error('Access denied', ERR_ACCESS_DENIED);
				}
				else {
		            	//logmes(__METHOD__.' $args = ', $args, $this->debugLogName);
		            	
		            	$res = call_user_func_array(array($this, $method), array($args));
		            	logmes('Response = ', $res, $this->requestsLogName);
		            	
		                $this->result($res);
			    }
			} catch (Exception $e) {
				$errCode = $e->getCode();
				$errCode = empty($errCode) ? ERR_UNKNOWN : $errCode;
				
				if ($this->config->item('enable_profiler')) {
					$this->load->library('profiler');
					$this->profiler->run();
				}
				
				$this->notify($errCode, $e->getMessage(), $e->__toString());
				
				return $this->error($e->getMessage(), $errCode, $e->__toString());
			}
			
			if ($this->config->item('enable_profiler')) {
				$this->load->library('profiler');
				$this->profiler->run();
			}
		/*	
        } else {
            show_404(__CLASS__.'/'.$method);
        }*/
	}
	
    	
    protected function result($result, $statusCode = 200, $statusMessage = 'Ok')
    {
        $this->output->set_status_header($statusCode, $statusMessage);
        $this->output->set_header('Content-Type: application/json');
        $this->output->set_output(json_encode(array(
            'result' => $result,
            'error' => NULL
        )));
    }

    protected function error($message = '', $code = ERR_UNKNOWN, $info = NULL, $statusCode = 500, $statusMessage = 'Internal Server Error')
    {
    	if ( ! isset(self::$statusMessages[$code])) {
			logmes(__METHOD__.' Warning! Received exit code is not set in '.__CLASS__.'::statusMessages : code  ', $code, $this->debugLogName);
		}
		else {
			$status = self::$statusMessages[$code];
			$statusCode = $status['httpStatus'];
    		$message = empty($message) ? $status['message'] : $message;
		}
		
		$messageLabelId = 'ERR_UNKNOWN';
		$constants = get_defined_constants();
		foreach($constants as $name => $value) {
			if (strpos($name, 'ERR_') === 0 && $value == $code) {
				$messageLabelId = $name;
				break;
			}
		}
		
		if (empty($message)) {
			$message = $messageLabelId;
		}
		if (empty($info)) 
			$info = 'Request parameters = '.var_export($this->params, true);
		/*
		logmes(__METHOD__.'  $statusCode = ',$statusCode, $this->debugLogName);
		logmes(__METHOD__.'  $statusMessage = ',$statusMessage, $this->debugLogName);
		logmes(__METHOD__.'  $code = ',$code, $this->debugLogName);
		logmes(__METHOD__.'  $message = ',$message, $this->debugLogName);
		*/	
		////////////////////////////////////////////////////////////////////////////
        
        $this->output->set_status_header($statusCode, $statusMessage);
        $this->output->set_header('Content-Type: application/json');
        $this->output->set_output(json_encode(array(
            'result' => NULL,
            'error' => array('code' => $code, 'message' => $message, 'labelId' => $messageLabelId, 'info' => $info)
        )));
    }

    protected function notify ($errCode, $errMessage, $errInfo = '') 
    {
    	logmes('['.current_url().'] '.__METHOD__, '____________________', $this->errorLogName);
    	logmes('controller = ', $this->router->fetch_class(), $this->errorLogName);
    	logmes('$errCode = ', $errCode, $this->errorLogName);
    	logmes('$errMessage = ', $errMessage, $this->errorLogName);
    	logmes('$errInfo = ', $errInfo, $this->errorLogName);
    	logmes('$params = ', $this->params, $this->errorLogName);
    	logmes(__METHOD__, '________________________________________________________________________', $this->errorLogName);
    	
    	$controller = $this->router->fetch_class();
    	$method = $this->router->fetch_method();
    	
    	$this->load->config('ion_auth', TRUE);
    	$error_email_sources = $this->config->item('error_email_sources', 'ion_auth');
    	$path = "/$controller/$method";
    	if ( ! empty($error_email_sources) && in_array($path, $error_email_sources)) {
	    	$this->load->helper('mx_email');
	    	$subject = 'MX '.INSTANCE.' error notification';
	    	$recipient = new stdClass;
	    	$recipient->is_watcher = true;
	    	$recipient->data = array(
	    			'path' => $path,
	    			'errCode' => $errCode,
	    			'errMessage' => $errMessage,
	    			'errInfo' => $errInfo,
	    			'params' => var_export($this->params, true)
	    	);
	    	$sendResult = (object) send_email (array($recipient), $tpl_name = 'error_email_template', $subject);
    	}
    }

}