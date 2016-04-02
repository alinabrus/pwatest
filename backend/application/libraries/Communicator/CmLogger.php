<?php

class CmLogger
{
	protected $logName;
	
	public function __construct($logName) {
		//logmes(__METHOD__,'','aaa');
		$this->logName = $logName;
	}
	
	public function log($message, $data = null) {
		if (function_exists('logmes')) {
			logmes($message, $data, $this->logName);
		}
	}
	
}
