<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends API_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('ion_auth','form_validation'));
		$this->form_validation->set_error_delimiters($prefix = '', $suffix = ' ');
	}
	
	public function create_user($username, $email, $password){
		
		$tables = $this->config->item('tables','ion_auth');
		
		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique['.$tables['users'].'.email]');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');
		
		$email = strtolower($email);
		$additional_data = array();
		
		//throw new Exception('fff', ERR_UNKNOWN);
		if ($result = $this->form_validation->run_custom(compact('username', 'email', 'password'))) {
			$result = $this->ion_auth->register($username, $password, $email, $additional_data);
		}
		if ( ! $result) {
			$err_message = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
			//$err_message = strip_tags($err_message);
			throw new Exception($err_message, ERR_UNKNOWN);
		}
		return $result;
	}
	
	private function set_userdata() 
	{
		$user = $this->ion_auth->user()->row();
		
		$user_fields = (array)$user;
		$userdata = new stdClass;
		$fields = array_intersect_key($user_fields, array('first_name'=>null, 'last_name'=>null, 'email'=>null, 'phone'=>null));
		foreach ($fields as $key=>$value) {
			$userdata->$key = $value;
		}
		$this->session->set_userdata('user', $userdata);
		
		$user_groups = $this->ion_auth->get_users_groups($user->id)->result();
		$user_groups_list = array();
		foreach ($user_groups as $group_obj) {
			$user_groups_list[] = $group_obj->name;
		}
		$this->session->set_userdata('user_name', $user->first_name.' '.$user->last_name);
			
		// ----- TESTING -------
		//$user_groups_list = ACC_GROUP_GUARDIANS;
			
		$this->session->set_userdata('user_group', $user_groups_list);
		
		/*
		$app_settings = $this->app_settings_read();
		$app_settings = empty($app_settings) ? array() : $app_settings[0];
		$this->session->set_userdata('app_settings', $app_settings);
		*/
	}

	public function login($username, $password, $remember = false) {
		if ($this->ion_auth->login($username, $password, $remember)) {
			$this->set_userdata();
			return $this->session->userdata('user_group');
		}
		else 
			throw new Exception('Invalid login or password', ERR_UNAUTHORIZED);
	}
	
	public function login_jwt($jwt, $remember = false) {
		
		$this->load->library("JWT");
		$jwToken = $this->jwt->decode($jwt, JWT_CONSUMER_SECRET);
		
		logmes('$jwToken = ',$jwToken,'aaa');
		
		if ($this->ion_auth->login($username, $password, $remember)) {
			$this->set_userdata();
			return $this->session->userdata('user_group');
		}
		else
			throw new Exception('Invalid login or password', ERR_UNAUTHORIZED);
	}
	
	public function confirm_registration ($confirmation_code) {
		
		if (empty($confirmation_code)) 
			throw new Exception('Invalid confirmation code');
		
		// appropriate data manipulation steps here 
		
		return true;
	}
	
	public function profile($refreshFlag = false) {
		
		if ($this->session->userdata('user_id') || $this->ion_auth->login_remembered_user() || in_array(ACCESS_GROUP_TESTER, $this->accessGroup)) 
		{
			if ($refreshFlag) 
				$this->set_userdata();
			
			$userdata = $this->session->all_userdata();
			
			$this->load->library("JWT");
			$jwToken = new stdClass();
			$jwToken->consumerKey = JWT_CONSUMER_KEY;
			$jwToken->ttl = JWT_CONSUMER_TTL;
			$jwToken->issuedAt = date(DATE_ISO8601, strtotime("now"));
			$jwToken->user_id = $userdata['user_id'];
			$jwToken->user = $userdata['user'];
			
			$userdata['jwt'] = $this->jwt->encode($jwToken, JWT_CONSUMER_SECRET);
			
			return $userdata;
		}
		else {
			$this->ion_auth->logout();
			//throw new Exception('', ERR_UNAUTHORIZED);
			return false;
		}
	}
	
	public function logout() {
		return $this->ion_auth->logout();
	}
	
	private function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);
	
		return $key.'#'.$value; //array($key => $value);
	}
	
	private function _valid_csrf_nonce($csrf)
	{
		//logmes('$csrf = ',$csrf,'aaa');
		//logmes('flashdata = ',$this->session->flashdata('csrfkey').'#'.$this->session->flashdata('csrfvalue'),'aaa');
		if ($csrf == $this->session->flashdata('csrfkey').'#'.$this->session->flashdata('csrfvalue'))
			return TRUE;
		else
			return FALSE;
	}
	
	// step 1
	public function forgot_password($email, $username = null)
	{
		$identity_field = $this->config->item('identity', 'ion_auth');
		$identity_field_label = ucfirst($identity_field);
		//setting validation rules by checking wheather identity is username or email
		$rules = array();
		if ($identity_field == 'username') 
			$rules[] = array('field'=>'email', 'label'=>$identity_field_label, 'rules'=>'required');
		else
			$rules[] = array('field'=>'email', 'label'=>$identity_field_label, 'rules'=>'required|valid_email');
		
		if ($this->form_validation->run_custom(compact($identity_field), $rules) == false) {
			$message = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
			throw new Exception($message, ERR_INVALID_PARAMETER_VALUE);
		}
		else {
			// get identity from username or email
			$identity = $this->ion_auth->where($identity_field, strtolower($email))->users()->row();
			if (empty($identity)) {
				//$message = "Given $identity_field_label is not found";
				$message = "You have entered wrong $identity_field_label.";
				throw new Exception($message, ERR_USER_NOT_FOUND);
			}
	
			//run the forgotten password method to email an activation code to the user
			$forgotten = $this->ion_auth->forgotten_password($identity->$identity_field);
			if ($forgotten) {
				//if there were no errors
				return array(
						'email_send_result' => $forgotten,
						'message' => $this->ion_auth->messages()
				); 
			}
			else
				throw new Exception($this->ion_auth->errors(), ERR_UNKNOWN);
		}
	}
	
	// step 2
	public function forgotten_password_check($code) 
	{
		//logmes(__METHOD__.' userdata = ',$this->session->all_userdata(),'aaa');
		if (!$code)
			throw new Exception('', ERR_ACCESS_DENIED);
		
		$user = $this->ion_auth->forgotten_password_check($code);
		if ($user) 
			return array (
				'csrf' => $this->_get_csrf_nonce()
			);
		else 
			throw new Exception($this->ion_auth->errors(), ERR_USER_NOT_FOUND);
	}
	
	// step 3
	public function reset_password($code, $new_password, $csrf)
	{
		//logmes(__METHOD__.' userdata = ',$this->session->all_userdata(),'aaa');
		$rules = array(
			array('field'=>'new_password', 'label'=>$this->lang->line('reset_password_validation_new_password_label'), 
					'rules'=>'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']'),
			//array('field'=>'new_password_confirm', 'label'=>$this->lang->line('reset_password_validation_new_password_label'), 'rules'=>'required')
		);
		
		if ($this->form_validation->run_custom(compact('new_password'), $rules) == false) {
			$message = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
			throw new Exception($message, ERR_INVALID_PARAMETER_VALUE);
		}
		else {
			// do we have a valid request?
			if ($this->_valid_csrf_nonce($csrf) === FALSE)
			{
				//something fishy might be up
				$this->ion_auth->clear_forgotten_password_code($code);
				throw new Exception($this->lang->line('error_csrf'), ERR_ACCESS_DENIED);
			}
			else {
				// finally change the password
				$user = $this->ion_auth->forgotten_password_check($code);
				if ( ! $user)
					throw new Exception($this->ion_auth->errors(), ERR_USER_NOT_FOUND);
				
				$identity = $user->{$this->config->item('identity', 'ion_auth')};

				$change = $this->ion_auth->reset_password($identity, $new_password);
				if ($change) {
					//if the password was successfully changed
					//$this->session->set_flashdata('message', $this->ion_auth->messages());
					$this->logout();
					return array(
						'password_reset_result' => true,
						'message' => 'Password successfully changed. Logged out.' //$this->ion_auth->messages()
					); 
				}
				else 
					throw new Exception($this->ion_auth->errors());
			}
		}
	}
	
	public function account_email_uniqueness_check($contact_email, $acc_users_id = null) 
	{
		$ion_tables = $this->config->item('tables','ion_auth');
		$model_name = $ion_tables['users'];
		/*
		$rules = array(
							array('field'=>'contact_email', 'label'=>"Contact Email '{$contact_email}'",
									'rules'=>'is_unique['.$model_name.'.email]'
							)
						);
		return $this->form_validation->run_custom(array('contact_email' => $contact_email), $rules);
		*/
		$this->load->model($model_name);
		$where = array(
			'email' => $contact_email
		);
		if ( ! is_null($acc_users_id))
			$where["id <> $acc_users_id"] = null;
			
		return ($this->$model_name->read_count($where) > 0 ? false : true);
	}
	
	
	
	public function admin_confirm_registration($account_type, $account_owner_id) {
		
		if (empty($account_type) || empty($account_owner_id)) 
			throw new Exception('Invalid account confirmation parameters');
			
		$this->load->model('mx_accounts_map_model');
		
		// appropriate data manipulation steps here 
		
		return true;
	}
	
	public function app_settings_save ($settings = array()) {
		if (empty($settings))
			throw new Exception(null, ERR_INVALID_PARAMETER_VALUE);
			
		// appropriate data manipulation steps here 
		
		return true;
	}
	
	public function app_settings_read () {		
		/*
		$setup_model_name = '';
		$this->load->model($setup_model_name);
		$result =  $this->$setup_model_name->read();
		if ($result === false)
			throw new Exception(null, ERR_DB_DML_ERROR);
		return empty($result) ? array() : $result[0];
		*/
		return array();
	}
	
}
