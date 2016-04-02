<?php

class CurlMultiChannel {

	protected $inProgress = FALSE;
	protected $jobQueue = array();

	protected $master = NULL;

	protected $activeJobs = array();

	public $maxConnections = 5;
	/**
	 * Time in seconds to wait until connection activity before re-starting cycle.
	 * Basically it is max time before calling $callback in launch()
	 * @var int
	 */
	public $selectTimeout = 1;

	public function __construct($maxConnections = 5) {
		$this->master = curl_multi_init();
		$this->maxConnections = $maxConnections;
	}

	public function __destruct() {
		curl_multi_close($this->master);
	}

	public function __get($prop) {
		if (isset($this->$prop)) {
			return $this->$prop;
		}
		trigger_error('Undefined property: '.__CLASS__.'::$'.$prop, E_USER_NOTICE);
		return NULL;
	}

	/**
	 * Adds a job for CURL to the queue
	 * @param	CurlJob		$job	Job definition. Url, curl options, function to process result, arguments
	 * @param	bool		$lifo	Run soonest on TRUE (default), put to the end of queue otherwise
	 * @return	bool
	 */
	public function addJob(CurlJob $job, $lifo = TRUE)
	{
		if ( !$job->onAdd() ) {
			return FALSE;
		}
		if ($lifo) {
			$this->jobQueue[] = $job;
		} else {
			array_unshift($this->jobQueue, $job);
		}
		return TRUE;
	}

	/**
	 * Launches queue processing.
	 * It won't stop until queue is empty, even if it grows during execution
	 * (i.e. callbacks add more jobs)
	 * @param callable	$callback	function to call after every iteration
	 * 								passes:	array $finishedJobs (that completed since last call),
	 * 										array $startedJobs (picked since last call; note they might have already finished too)
	 * 										array $activeJobs (currently executing)
	 */
	public function launch($callback = NULL)
	{
		if ($this->inProgress) {
			// prevent accidental recursion
			return;
		}
		if ($callback && !is_callable($callback)) {
			$callback = NULL;
		}
		$this->inProgress = TRUE;
		$channels = $freeChannels = array_fill(0, $this->maxConnections, NULL);
		$this->activeJobs = array();
		$running = 0;
		do {
			$startedJobs = array();
			$finishedJobs = array();

			// pick jobs for free channels:
			while ( !(empty($freeChannels) || empty($this->jobQueue)) ) {
				$job = array_pop($this->jobQueue); // pick a job

				// take free channel and (re)init it as necessary:
				$chi = key($freeChannels);
				if ( !empty($channels[$chi]) && $job->forceNewConnection ) {
					curl_close($channels[$chi]);
					$channels[$chi] = curl_init();
				} elseif ( empty($channels[$chi]) ) {
					$channels[$chi] = curl_init();
				}

				// let job set options for cURL handle:
				if ($job->onStart($channels[$chi])) {
					$startedJobs[] = $this->activeJobs[$chi] = $job;
					curl_multi_add_handle($this->master, $channels[$chi]);
					unset($freeChannels[$chi]);
				}
					// in case it fails this channel will be used for next job
			}
			$pending = count($this->activeJobs);

			// launch them:
			if ($pending > 0) {
				while(($mrc = curl_multi_exec($this->master, $running)) == CURLM_CALL_MULTI_PERFORM);
					// poke it while it wants to
				curl_multi_select($this->master, $this->selectTimeout);
					// wait for some activity, don't eat CPU
				while(($mrc = curl_multi_exec($this->master, $running)) == CURLM_CALL_MULTI_PERFORM);
					// do whatever there is to do after waiting
				while ($running < $pending && ($info = curl_multi_info_read($this->master))) {
					// some connection(s) finished, locate that job and run response handler:
					$pending--;
					if ( ($chi = array_search($info['handle'], $channels)) === FALSE) {
						continue;	// impossible, but...
					}

					$finishedJobs[] = $this->activeJobs[$chi];	// for statistics callback
					$completeResult = $this->activeJobs[$chi]->onComplete(curl_multi_getcontent($channels[$chi]));
					$freeChannels[$chi] = NULL;
					curl_multi_remove_handle($this->master, $channels[$chi]);
					unset($this->activeJobs[$chi]);
						// free up this channel
					if ($completeResult instanceof CurlJob) {	// wants to re-join or add successor
						$this->addJob($completeResult);
					}
				}
			}

			if ($callback) {
				call_user_func($callback, $finishedJobs, $startedJobs, $this->activeJobs);
			}
		} while (
			($running > 0 && ($mrc == CURLM_CALL_MULTI_PERFORM || $mrc == CURLM_OK))
			|| !empty($this->jobQueue)
		);

		// close channels
		foreach ($channels as $channel) {
			if ( !empty($channel) ) {
				curl_close($channel);
			}
		}
		$this->inProgress = FALSE;
	}
}


class CurlJob {
	const HTTP_CONTINUE	= 100;
	const HTTP_OK		= 200;

	public $response;	// remote response
	public $error;
	public $info;		// curl_getinfo  result
	public $ext;		// any data used by callbacks
	public $raw = NULL;	// raw request/response if $debug = true

	public $forceNewConnection = FALSE;
	public $url = '';
	public $method = 'GET';
	public $query = NULL;  // GET parameters
	public $post = array();
	public $headers = array();
	public $options = array();	// any additional CURLOPT_*
	public $callback = NULL;

	public $debug = FALSE;
	public static $globalDebug = FALSE;

	protected $handle = NULL;

	// $_properties are used internally in locked state, when job is in the queue:
	protected $_url;
	protected $_method;
	protected $_query;
	protected $_post;
	protected $_headers;
	protected $_options;

	protected $_callback;

	protected $lock = FALSE;	// lock when added to queue
	protected $lockProperties = array('url','method','query','post','headers','options','callback');
		// list of properties to lock; just a helper for least lines of code

	/**
	 * Cunstructs a job. Any of arguments listed may be modified directly on the object any time.
	 * When job is added to queue it locks these values. They're still accsessible, but won't
	 * won't affect anything until it re-joins the queue.
	 * @param	function	$callback
	 * @param	string		$url
	 * @param	array		$post
	 * @param	array		$headers
	 * @param	string		$method		HTTP method
	 */
	public function __construct($callback = NULL, $url = '', array $post = array(), array $headers = array(), $method = 'GET', array $options = array()) {
	    $this->debug = self::$globalDebug;
		$this->init($callback, $url, $post, $headers, $method, $options);
	}
	public function init($callback = NULL, $url = '', array $post = array(), array $headers = array(), $method = 'GET', array $options = array() ) {
		$this->callback = $callback;
		$this->url = $url;
		$this->post = $post;
		$this->headers = $headers;
		$this->method = $method;
		$this->options = $options;
	}

	/**
	 * Tells the object it's about to join the queue.
	 * @return	bool	ready or not
	 */
	public function onAdd() {
		$this->response = FALSE;
		if ( !($call = is_callable($this->callback)) || empty($this->url) || $this->lock ) {
			$this->error = "CurlJob init error [ URL: {$this->url}; Callback: {$this->callback}; Locked: {$this->lock} ]";
			if ($call) {
				// tell callback about this mishandling
				call_user_func($this->callback, $this);
			}
			return FALSE;
		}

		$this->lock = TRUE;
		$this->handle = NULL;
		foreach ($this->lockProperties as $name) {
			$this->{'_'.$name} = $this->{$name};
		}

		return TRUE;
	}

	/**
	 * Init given cURL handle with options.
	 * @param	curl_resource			$handle
	 * @return	curl_resource|FALSE
	 */
	public function onStart($handle) {
		if (empty($handle) || !$this->lock) {
			return FALSE;
		}
		$this->handle = $handle;

		// prepare cURL options:
		$query = '';
		if ( !empty($this->_query) ) {
		    $query = (stripos($this->_url, '?') !== FALSE) ? '&' : '?';
		    $query .= (is_string($this->_query) ? $query : http_build_query($this->_query, '', '&'));
		}
		$options = array(
			CURLOPT_URL				=> $this->_url . $query,
			CURLOPT_RETURNTRANSFER	=> TRUE,
			CURLOPT_HEADER			=> TRUE
		);

		switch (strtoupper($this->_method)) {
			case 'POST':
				$options[CURLOPT_POST] = TRUE;
				$options[CURLOPT_CUSTOMREQUEST] = 'POST';
					// in case $handle was already initialized wtih _CUSTOMREQUEST previously
				break;

			default:
				$options[CURLOPT_POST] = FALSE;
				$options[CURLOPT_CUSTOMREQUEST] = strtoupper($this->_method);
		}

		if ( !empty($this->_post) ) {
			$options[CURLOPT_POSTFIELDS] = http_build_query($this->_post, '', '&');
		}
		if ( !empty($this->_headers) ) {
			$headers = array();
			foreach ($this->_headers as $name => $value) {
				$headers[] = $name.': '.$value;
			}
			$options[CURLOPT_HTTPHEADER] = $headers;
		}

		if ($this->debug) {
			$options[CURLINFO_HEADER_OUT] = TRUE;
		}

		$options = $this->_options + $options;		// union; takes left value on key conflict
		curl_setopt_array($this->handle, $options);

		$this->response = FALSE;
		$this->error = FALSE;
		if ($this->debug) {
			$this->raw = (object)array(
			    'options' => self::_exportOptions($options),
				'request' => isset($options[CURLOPT_POSTFIELDS]) ? $options[CURLOPT_POSTFIELDS] : '',
				'response'=> ''
			);
			// no way to track posted content later, have to save it here
		}

		return $this->handle;
	}

	/**
	 * Called when HTTP request completes.
	 * @param	string	$response	Content received
	 * @return	mixed	Unused yet
	 */
	public function onComplete($response)
	{
		$this->lock = FALSE;	// job may need to be re-added by callback, so unlock immediately
		$this->info = curl_getinfo($this->handle);
		if ($this->debug) {
			$this->raw->request = $this->info['request_header'].$this->raw->request;	// prepend sent headers
			$this->raw->response = $response;
		}
		if ( !empty($response) ) {
			$this->_parseResponse($response);
		} else {
			$this->error = curl_errno($this->handle).' - '.curl_error($this->handle);
		}
		return call_user_func($this->_callback, $this);
	}

	/**
	 * Check if there's an error with current request and trigger it as PHP error.
	 * @param string|array	$expectedStatus		HTTP status code(s)
	 * @param boolean		$expectedBody		Body must not be empty
	 * @param boolean		$triggerError		Trigger PHP error
	 * @return string|FALSE
	 */
	public function checkError($expectedStatus = self::HTTP_OK, $expectedBody = TRUE, $triggerError = TRUE) {
		if ( !is_array($expectedStatus) ) {
			$expectedStatus = array($expectedStatus);
		}
		$error = FALSE;
		if (empty($this->response)) {
			$error = "Connection error {$this->error} to";
		} elseif ($expectedBody && empty($this->response->body)) {
			$error = 'Empty response from';
		} elseif ( !in_array($this->response->headers['Status-Code'], $expectedStatus) ) {
			$error = 'Unexpected status '.$this->response->headers['Status'].' from';
		}
		if ($error) {
			$error .= " [{$this->url}; post: ".var_export($this->post, TRUE).']';
			if ($triggerError) {
				trigger_error($error, E_USER_ERROR);
			}
			return $error;
		}
		return FALSE;
	}

	/**
	 * Parses full response into headers and body.
	 * @param string	$response
	 * @return boolean
	 */
	protected function _parseResponse($response)
	{
		do {
			$headersPos = strpos($response, "\r\n\r\n");
			if ($headersPos === FALSE) {
				return FALSE;
			}
			$rawHeaders = substr($response, 0, $headersPos);
			$matches = array();
			preg_match('#^HTTP/(\d\.\d)\s(\d{3})\s(.*?)\s*$#m', $rawHeaders, $matches);
			$response = substr($response, $headersPos + 4);	// 4 = length of CRLFCRLF delimiter
		} while (intval($matches[2]) == self::HTTP_CONTINUE);

		$headers = $headersArr = array();
		$headers['Http-Version'] = $matches[1];
		$headers['Status-Code'] = intval($matches[2]);
		$headers['Status'] = $matches[2].' '.$matches[3];

		$body = $response;
		$status = strpos($rawHeaders, "\r\n");
		$rawHeaders = substr($rawHeaders, $status + 2);

		if (preg_match_all('/^([^:]+):\s(.*?)\s*$/m', $rawHeaders, $matches)) {
			foreach ($matches[0] as $id => $nonsense) {
				$headers[$matches[1][$id]] = $matches[2][$id];
				$headersArr[] = array($matches[1][$id], $matches[2][$id]);    // for repeating headers like Set-Cookie
			}
		}

		$this->response = (object)array(
			'body'		=> $body,
			'headers'	=> $headers,
		    'headersArr'=> $headersArr
		);
		return TRUE;
	}

	protected static function _exportOptions($options)
	{
	    static $constants;
	    if ($constants === NULL) {
	        $constants = get_defined_constants(TRUE);
	        $constants = $constants['curl'];
	    }
	    $tmp = array();
	    foreach ($constants as $key => $const) {
	        foreach ($options as $option => $value) {
	            if ($option === $const) {
	                if ( !key_exists($option, $tmp) ) {
	                    $tmp[$option] = array('keys' => array(), 'value' => $value);
	                }
	                $tmp[$option]['keys'][] = $key;
	            }
	        }
	    }
	    $result = array();
	    foreach ($tmp as $option) {
	        $result[implode('|', $option['keys'])] = $option['value'];
	    }
	    return $result;
	}
}


// EOF