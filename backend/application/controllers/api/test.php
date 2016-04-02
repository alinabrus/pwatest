<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {
	
	private $token;
	
	public function __construct()
	{
		parent::__construct();
		$this->token = md5('vjrhsqdfkmc'); //$this->session->userdata('token');
	}
	
	public function phpinfo(){
		phpinfo();
	}

	public function index()
	{	
		/*
		if ( ! empty($this->token)) {
			$postdata = array('token' => $this->token);
    		$url = base_url()."service/me";
    		$callResult	= $this->_post_call_test($url, $postdata);
			//logmes(__METHOD__.'  $callResult = ',$callResult,'debug_log_'.__CLASS__);
			$output = json_decode($callResult['output']);
			if ( ! isset($output->data->requesterObject->token)) 
				$this->token = null;
		}
		if (empty($this->token)) $this->_login();
		*/
		
		
		$data['data'] = array();
		$data['showTestForm'] = true;
		$data['token'] = $this->token;
		
		//logmes(__METHOD__.'  $data = ',$data,'debug_log_'.__CLASS__);
		$this->load->view('test', $data);
	}
	
	public function ws(){
		$this->load->view('wstest');
	}
	
	public function test_api_call()
	{
		$url = base_url().trim($this->input->post('query_string'),'/');
		//logmes(__METHOD__.'  $url = ',$url,'debug_log_'.__CLASS__);
		
		$token = $this->input->post('token');
		$postdata = $this->input->post('post_data');
		//logmes(__METHOD__.'  $postdata = ',$postdata,'debug_log_'.__CLASS__);
		//logmes(__METHOD__.'  json_decode($postdata) = ',json_decode($postdata),'debug_log_'.__CLASS__);
		
		if ( is_null(json_decode($postdata)) ) 
		{
			$postdata = str_replace("\n",'&',trim($postdata));
			$postdata = preg_replace("/\s*(=)\s*/",'$1',$postdata);
			/*
			$passwordPattern = "/(password=)(.*)(&.*|$)/U";
			preg_match($passwordPattern,$postdata,$matches);
			//logmes(__METHOD__.'  $matches = ',$matches,'debug_log_'.__CLASS__);
			if (isset($matches[2]))
				$postdata = preg_replace($passwordPattern,'${1}'.md5($matches[2]).'${3}',$postdata);
			//logmes(__METHOD__.'  $postdata = ',$postdata,'debug_log_'.__CLASS__);
			*/
			if ( ! empty($token)) {
				$postdata = empty($postdata) ? "token=$token" : $postdata."&token=$token";
			}
		}	
		$callResult	= $this->_post_call_test($url, $postdata);
		
		//return $callResult['output'];
		
		$data = $callResult; 
		$output = json_decode($callResult['output']);
		//logmes(__METHOD__.'  $output = ',$output,'debug_log_'.__CLASS__);
		if (isset($output->data->token)) { 
			$this->token = $output->data->token;
			$this->session->set_userdata('token',$this->token);
		}
		//else $this->token = null;
		
		$data['token'] = $this->token;
		
		//logmes(__METHOD__.'  $data = ',$data,'debug_log_'.__CLASS__);
		foreach($data as $key=>&$value){
    		if (is_array($value)) { 
    			//logmes(__METHOD__.'  $value = ',$value,'debug_log_'.__CLASS__);
    			continue;
    		}
    		//$value = strip_tags($value);
    		$value = preg_replace('/([\{\}])/', '$1</br>', $value);
    		$value = preg_replace('/([,])/', '$1 ', $value);
    		$value = preg_replace('/(<\/br>)+/', '</br>', $value);
    		$value = preg_replace('/(\"debuginfo\")/', '</br>$1', $value);
    	}
    	//logmes(__METHOD__.'  $data = ',$data['output'],'debug_log_'.__CLASS__);
    	
    	$data = json_encode($data);
    	//return $data;
    	$this->output->set_output($data);
	}
	
	public function _post_call_test ($url, $postdata=array(), $referer=null, $returnTransfer=true) {
    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returnTransfer);
		if ( ! empty($referer)) curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		if ( ! isset($postdata)) {
			curl_setopt($ch, CURLOPT_POST, false);
		}
		else {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($postdata)?http_build_query($postdata):$postdata);
		}
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		//$headers = function_exists('apache_request_headers') ? var_export(apache_request_headers(), TRUE) : '';
		//logmes(__METHOD__.'  $headers = ',$headers,'debug_log_'.__CLASS__);
    	//logmes(__METHOD__.'  $postdata = ',$postdata,'debug_log_'.__CLASS__);
    	
		//logmes(__METHOD__.'  $info = ',$info,'debug_log_'.__CLASS__);
    	//logmes(__METHOD__.' $output = ', $output, 'debug_log_'.__CLASS__);
    	$data['info'] = $info;
    	$data['output'] = $output;
		
    	return $data; 
    }
    
	private function _login() 
	{
		$postdata = array (
    		'user_name' => '',
			'password' => ''
		);
    	$url = base_url()."service/login/";
    	//$url = base_url()."service/login_anonymous/";
    	
    	$callResult	= $this->_post_call_test($url, $postdata);
		//logmes(__METHOD__.' $callResult = ', $callResult, 'debug_log_'.__CLASS__);
		$authResult = json_decode($callResult['output']);
		if (is_object($authResult) && $authResult->code == '0') { 
			$this->token = $authResult->data->token;
			$this->session->set_userdata('token',$this->token);
		}
    	return $callResult;
    }
    
    public function login() 
	{
    	$data['data'] = $this->_login();
		$this->load->view('test', $data);
    }
        
    public function imagick() {
    	header('Content-type: image/jpeg');
    	
    	$imgpath = get_path(FILES_ORG_PATH);
    	$imgpath = rtrim($imgpath, '/').'/'.'image.pdf';
    	
    	$imgurl = base_url().'files/org/image.jpg';
    	//$imgurl = 'https://www.youtube.com/embed/Pd8hJRIW31w';
    	    	 
    	logmes('$imgpath = ', $imgpath, 'aaa');
    	$image = new Imagick($imgpath);
    	
    	// If 0 is provided as a width or height parameter,
    	// aspect ratio is maintained
    	$image->thumbnailImage(100, 0);
    	
    	echo $image;
    }
    
    public function video_thumb() {
    	/*
    	 https://www.youtube.com/embed/Pd8hJRIW31w
    	 https://img.youtube.com/vi/Pd8hJRIW31w/0.jpg
    	
    	 https://vimeo.com/channels/staffpicks/9940327
    	 http://vimeo.com/api/v2/video/9940327.json
    	 */
    	//$campaign['video_url'] = 'https://youtube.com/embed/1Xn92U3W01E';
    	$campaign['video_url'] = 'https://player.vimeo.com/video/141488937';
    	
    	$video_id = basename($campaign['video_url']);
    	
    	$thumb_url = null;
    	if (strpos($campaign['video_url'], 'youtube')) {
    		$thumb_url = "https://img.youtube.com/vi/$video_id/0.jpg";
    	}
    	elseif (strpos($campaign['video_url'], 'vimeo')) {
    		$video_data_url = "http://vimeo.com/api/v2/video/$video_id.json";
    		$video_data = json_decode(file_get_contents($video_data_url));
    		if ( ! empty($video_data))
    			$thumb_url = $video_data[0]->thumbnail_large;
    	}
    	
    	print_r($thumb_url);
    }
    
    	
}
/* End of file */