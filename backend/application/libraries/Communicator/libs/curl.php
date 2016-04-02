<?php

/* TODO: SSL management functionality is basic, so it need to be extended for advanced usage */

class CurlResponse {
    
    public $body = '';
    public $headers = array();
    
    function __construct($response) {
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
        preg_match_all($pattern, $response, $matches);
        $headers_string = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));
        $this->body = str_replace($headers_string, '', $response);
        $version_and_status = array_shift($headers);
        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
        $this->headers['Http-Version'] = $matches[1];
        $this->headers['Status-Code'] = $matches[2];
        $this->headers['Status'] = $matches[2].' '.$matches[3];
        foreach ($headers as $header) {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
        }
    }
    
    function __toString() {
        return $this->body;
    }
    
}

class Curl {

	public $debug = false;
    public $port;
    public $referer;
    public $user_agent;    
    public $cookie_file;
    public $follow_redirects = true;
    
    private $auth_options;
    private $ssl_options = array();
    private $proxy_options = array();
    
    public $headers = array();
    public $options = array();

    private $info;
    private $request;
    private $error = '';
    
    function __construct($user_agent = "auto") {
        if($user_agent != "") {
            if($user_agent == "auto") $this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Curl/PHP '.PHP_VERSION;
            else $this->user_agent = $user_agent;
        }
    }
    
    public function auth($username, $password) {
        $this->auth_options = "$username:$password";
    }
    
    public function set_cookie($cookie_prefix) {
        $tmp = sys_get_temp_dir();
        $this->cookie_file = ((substr($tmp, -1) == "/" || substr($tmp, -1) == "\\") ? $tmp : $tmp."/").$cookie_prefix;
    }
    
    public function set_proxy($host, $auth = "", $auth_type = "basic", $type = "http", $tunnel = false) {
        $this->proxy_options["host"] = $host;                                       // host:port
        $this->proxy_options["auth"] = $auth;                                       // username:password
        $this->proxy_options["auth_type"] = "CURLAUTH_".strtoupper($auth_type);     // basic | ntlm
        $this->proxy_options["type"] = "CURLPROXY_".strtoupper($type);              // http | socks5
        $this->proxy_options["tunnel"] = $tunnel;                                   // true | false
    }
    
    public function set_certificate($verify_host = false, $verify_peer = false, $cert = "") {
        $this->ssl_options["verify_host"] = $verify_host;   // 1 | 2 | false
        $this->ssl_options["verify_peer"] = $verify_peer;   // true | false
        $this->ssl_options["cert"] = $cert;                 // path to cert
    }
    
    public function get($url, $vars = array(), $headers = array()) {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }
        return $this->request('GET', $url, NULL, $headers);
    }

    public function post($url, $vars = array(), $headers = array()) {
        return $this->request('POST', $url, $vars, $headers);
    }

    public function put($url, $vars = array(), $headers = array()) {
        return $this->request('PUT', $url, $vars, $headers);
    }

    public function delete($url, $vars = array(), $headers = array()) {
        return $this->request('DELETE', $url, $vars, $headers);
    }
        
    public function head($url, $vars = array(), $headers = array()) {
        return $this->request('HEAD', $url, $vars, $headers);
    }

    public function info() {
        return $this->info;
    }

    public function error() {
        return $this->error;
    }
    
    public function request($method, $url, $vars = array(), $headers = array()) {
        $this->error = '';
        $this->request = curl_init();
        if (is_array($vars)) $vars = http_build_query($vars, '', '&');

        $this->set_request_method($method);
        $this->set_request_options($url, $vars);
        $this->set_request_headers($headers);
        
        $response = curl_exec($this->request);
        logmes(__METHOD__.'  $response = ',$response,'debug_log_'.__CLASS__);
        
        if ($response) {
            $response = new CurlResponse($response);
        } else {
            $this->error = curl_errno($this->request).' - '.curl_error($this->request);
        }
		
        if ($this->debug) 
        	$this->info = curl_getinfo($this->request, CURLINFO_HEADER_OUT);
        else 
        	$this->info = curl_getinfo($this->request);
        curl_close($this->request);
        
        logmes(__METHOD__.' url: ', $url, 'debug_log_'.__CLASS__);
        logmes(__METHOD__.' vars: ', $vars, 'debug_log_'.__CLASS__);
        logmes(__METHOD__.' headers: ', $headers, 'debug_log_'.__CLASS__);
        //if ($this->info['http_code']!=200) 
        logmes(__METHOD__.' response: ', $response, 'debug_log_'.__CLASS__);
        logmes(__METHOD__.' error: ',$this->error(),'debug_log_'.__CLASS__);
        logmes(__METHOD__.' curl info: ',$this->info,'debug_log_'.__CLASS__);
       	logmes(__METHOD__.' apache_request_headers: ',apache_request_headers(),'debug_log_'.__CLASS__);
        
		return $response;
    }
    
    private function set_request_headers($extraHeaders = array()) {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key.': '.$value;
        }
    	foreach ($extraHeaders as $key => $value) {
    		if (is_int($key)) $headers[] = $value;
            else $headers[] = $key.': '.$value;
        }
        curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
    }
    
    private function set_request_method($method) {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->request, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                /* bug fix: curl does not properly restores GET after POST when using HTTPGET option */
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, 'GET');
                break;
            case 'POST':
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, 'POST');
                //curl_setopt($this->request, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }
    
    private function set_request_options($url, $vars) {
        /* increase default buffer size to properly handle big datasets, other issues related are eliminated in php 5.3+ */
        curl_setopt($this->request, CURLOPT_BUFFERSIZE, 65536);

        curl_setopt($this->request, CURLOPT_URL, $url);
        if (!empty($vars)) curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        if ($this->cookie_file) {
            curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
        }
        
        if ($this->port) curl_setopt($this->request, CURLOPT_PORT, $this->port);
        if ($this->referer) curl_setopt($this->request, CURLOPT_REFERER, $this->referer);
        if ($this->follow_redirects) curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
        if ($this->user_agent) curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->auth_options) curl_setopt($this->request, CURLOPT_USERPWD, $this->auth_options);
        
        if(!empty($this->ssl_options)) {
            curl_setopt($this->request, CURLOPT_SSL_VERIFYHOST, $this->ssl_options["verify_host"]);
            curl_setopt($this->request, CURLOPT_SSL_VERIFYPEER, $this->ssl_options["verify_peer"]);
            if($this->ssl_options["cert"] != "")
                curl_setopt($this->request, CURLOPT_CAINFO, $this->ssl_options["cert"]);
        }
        
        if(!empty($this->proxy_options)) {
            curl_setopt($this->request, CURLOPT_PROXY, $this->proxy_options["host"]);
            curl_setopt($this->request, CURLOPT_PROXYUSERPWD, $this->proxy_options["auth"]);
            curl_setopt($this->request, CURLOPT_PROXYAUTH, $this->proxy_options["auth_type"]);
            curl_setopt($this->request, CURLOPT_PROXYTYPE, $this->proxy_options["type"]);
            curl_setopt($this->request, CURLOPT_HTTPPROXYTUNNEL, $this->proxy_options["tunnel"]);
        }
        
        if ($this->debug) curl_setopt($this->request, CURLINFO_HEADER_OUT, true);
        
        foreach ($this->options as $option => $value) {
            curl_setopt($this->request, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
        }
    }

}

/* EOF */