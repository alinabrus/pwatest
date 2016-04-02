<?php
class API_acc_model extends API_Model {
	
	protected $account_group;
	
	public function __construct() {	
		parent::__construct();
		$this->load->library('ion_auth');
	}
	
	public function get_account_rules($new_account = true) {
		
		$ion_tables = $this->config->item('tables','ion_auth');
		
		return array(
				array('field'=>'contact_first_name', 'label'=>'Contact First Name',
						'rules'=>'required|trim|xss_clean'
				),
				array('field'=>'contact_last_name', 'label'=>'Contact Last Name',
						'rules'=>'required|trim|xss_clean'
				),
				array('field'=>'contact_phone', 'label'=>'Contact Phone',
						'rules'=>'trim|xss_clean'
				),
				array('field'=>'contact_email', 'label'=>'Contact Email',
						'rules'=> ($new_account ? 
									'required|trim|xss_clean|valid_email|is_unique['.$ion_tables['users'].'.email]' : 
									'required|trim|xss_clean|valid_email'
									),
						//'error_messages' => array('is_unique' => 'This email address has already been used for registration. Please login with that email or register using a different email address.')
				),
				array('field'=>'password', 'label'=>'Password',
						//'rules'=>'required|trim|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']'
						'rules'=> ($new_account ? 
									'required|trim|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']' : 
									'trim|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']'
									)
				)
		);
	}
	
	public function add_account($contact_email, $password, $contact_first_name = null, $contact_last_name = null, $contact_phone = null, $contact_phone_verified = 0)
	{	
		try {
			
			$this->db->trans_start();
			
			$primary = $this->{$this->_primary};
			if (empty($primary)) 
				throw new Exception('Adding account to not existent entity');
			
			$group = $this->ion_auth->where('name', $this->account_group)->groups()->row();
			$group_ids = array($group->id);
			
			$contact_email = strtolower($contact_email);
			$additional_data = array(
				'first_name' => $contact_first_name,
				'last_name' => $contact_last_name,
				'phone' => $contact_phone,
				'phone_verified' => $contact_phone_verified
			);
			$acc_users_id = $this->ion_auth->register($contact_email, $password, $contact_email, $additional_data, $group_ids);
			
			$query = "INSERT INTO mx_accounts_map (acc_users_id, {$this->_primary}, status, confirmation_code) 
						VALUES ({$acc_users_id}, {$this->{$this->_primary}}, ".ACC_STATUS_INITIAL.", md5(acc_users_id))";
			$this->db->query($query);
			
			$this->db->trans_complete();
			
			return $acc_users_id; // $this->db->trans_status();
		}
		catch (Exception $e) {
			if (method_exists($this->db, 'trans_complete_rollback'))
				$this->db->trans_complete_rollback();
			throw $e;
		}
	}
	
	public function register($contact_email, $password, $contact_first_name = null, $contact_last_name = null, $contact_phone = null, $contact_phone_verified = 0)
	{
		try {
			$this->db->trans_start();
			
			$this->{$this->_primary} = null;
			$this->save();
			$acc_users_id = $this->add_account($contact_email, $password, $contact_first_name, $contact_last_name, $contact_phone, $contact_phone_verified);
		
			$this->db->trans_complete();
		
			//return $this->db->trans_status();
			return $this->db->trans_status() ? 
					array(
						'acc_users_id' => $acc_users_id,
						$this->_primary => $this->{$this->_primary}
					) : 
					false;
		}
		catch (Exception $e) {
			if (method_exists($this->db, 'trans_complete_rollback'))
				$this->db->trans_complete_rollback();
			throw $e;
		}
	}
	
	public function update($user_id, $contact_email, $password, $contact_first_name = null, $contact_last_name = null, $contact_phone = null, $contact_phone_verified = 0)
	{
		try {
			$this->db->trans_start();
			
			$result = $this->save();
			if ( ! $result) 
				throw new Exception(null, ERR_DB_DML_ERROR);
			
			$contact_email = strtolower($contact_email);			
			/*$data = array(
					'first_name' => $contact_first_name,
					'last_name'  => $contact_last_name,
					'email' 	 => $contact_email,
					'phone'      => $contact_phone,
			);*/
			$data = array();
			if ( ! is_null($contact_first_name)) $data['first_name'] = $contact_first_name;
			if ( ! is_null($contact_last_name)) $data['last_name'] = $contact_last_name;
			if ( ! is_null($contact_phone)) $data['phone'] = $contact_phone;
			if ( ! is_null($contact_phone_verified)) $data['phone_verified'] = $contact_phone_verified;
			if ( ! empty($contact_email)) $data['email'] = $contact_email;
			if ( ! empty($password)) $data['password'] = $password;
			$data['ip_address'] = $this->input->ip_address();
			
			$result = $this->ion_auth->update($user_id, $data);
			if ( ! $result) 
				throw new Exception('Failed to update account. '.$this->ion_auth->messages());
		
			$this->db->trans_complete();
		
			return $this->db->trans_status();
		}
		catch (Exception $e) {
			if (method_exists($this->db, 'trans_complete_rollback'))
				$this->db->trans_complete_rollback();
			throw $e;
		}
	}
	
	public function get_accounts($owner_id = null) {
		if (empty($owner_id)) 
			$owner_id = $this->{$this->_primary};
			
		$query = "SELECT a.id AS acc_users_id,
							a.first_name AS contact_first_name,
							a.last_name AS contact_last_name,
							a.email AS contact_email,
							a.phone AS contact_phone,
							a.phone_verified AS contact_phone_verified,
							am.status
							FROM mx_acc_users a
							INNER JOIN mx_accounts_map am ON (
							a.id = am.acc_users_id
							AND am.{$this->_primary} = '$owner_id'
							)";
		$rs = $this->db->query($query);
		return  $rs ? $rs->result() : $rs;
	}
	
	public function get_list_with_includes(array $where = array(), $limit = null, $offset = null, $order_by = null, 
											array $filters = array(), array $fields = array(), array $includes = array())
	{		 
		$resultObj = $this->get_list($where, $limit, $offset, $order_by, $filters, $fields);
		
		if ( ! empty($includes)) {
			foreach ($resultObj->data as &$row) {
				if (in_array('accounts', $includes)) {
					$row->accounts =  $this->get_accounts($row->{$this->_primary});
				}
			}
		}
		
		return $resultObj;
	}
	
	public function change_password ($acc_users_id, $current_password, $new_password) {
		$user = $this->ion_auth->user($acc_users_id)->row();
		$identity = $user->email;
		
		$change = $this->ion_auth->change_password($identity, $current_password, $new_password);
		if ( ! $change) 
			throw new Exception($this->ion_auth->errors());
			
		return $change;
	}
	
	public function deactivate_accounts () {
		$acc_owner_id = $this->{$this->_primary};
		
		if ( ! empty($acc_owner_id)) {
			$this->load->model('mx_accounts_map_model');
			
			$this->mx_accounts_map_model->{$this->_primary} = $acc_owner_id;
			$rs = $this->mx_accounts_map_model->read();
			if ( ! $rs) return false; 
			foreach ($rs as $row) {
				$this->ion_auth->deactivate($row->acc_users_id);
			}
			return true;;
		}
	}
	
	public function activate_accounts () {
		$acc_owner_id = $this->{$this->_primary};
	
		if ( ! empty($acc_owner_id)) {
			$this->load->model('mx_accounts_map_model');
				
			$this->mx_accounts_map_model->{$this->_primary} = $acc_owner_id;
			$rs = $this->mx_accounts_map_model->read();
			if ( ! $rs) return false;
			foreach ($rs as $row) {
				$this->ion_auth->activate($row->acc_users_id);
			}
			return true;;
		}
	}
	
}
