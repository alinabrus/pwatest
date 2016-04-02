<?php

class FieldFilter {
	protected $name;
	protected $value;
	protected $option;
	
	const OPTION_EQUAL = 'equal';
	const OPTION_CONTAINS = 'contains';
	const OPTION_STARTS = 'starts';
	const OPTION_ENDS = 'ends';
	
	public static $supportedOptions = array(self::OPTION_EQUAL, self::OPTION_CONTAINS, self::OPTION_STARTS, self::OPTION_ENDS); 
	
	public function __construct($name, $value, $option = self::OPTION_EQUAL) {
		$this->name = $name;
		$this->value = $value;
		if ( ! empty($option) && in_array($option, self::$supportedOptions))
			 $this->option = $option;
	}
	
	public function __get($property) {
		if (isset($this->$property)) {
			return $this->$property;
		}
		return null;
	}
}

class FieldFiltersArray {
	protected $items = array();
	
	public function __get($property) {
		if (isset($this->$property)) {
			return $this->$property;
		}
		return null;
	}
	
	public function addFilter($name, $value, $option = null) {
		$this->items[] = new FieldFilter($name, $value, $option);
	}
	
	public function clear($name = null) {
		if ( ! empty($name))
			unset($this->items[$name]); 
		else
			$this->items = array();
	}
	
}

class API_Model extends CI_Model {
	
    protected $_name;
    protected $_primary;
    protected $_primary_auto_increment = true;
    protected $_fields = array();
    protected $_fields_initial;
	protected $_encryption_fields = array();
	protected $_filters;
	
    protected $encryption_key = null;

    public function __construct() {
        parent::__construct();
        $this->_filters = new FieldFiltersArray();
    }
    
    private function setFieldsInitial() {
    	if ( ! isset($this->_fields_initial)) {
    		$this->_fields_initial = $this->_fields;
    	}
    }
     
	public function __set($name, $value){
		$this->setFieldsInitial();
        if(method_exists($this, "set_$name")){
            $this->{"set_$name"}($value);
        } elseif(array_key_exists($name, $this->_fields)){
            $this->_fields[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name){
    	$this->setFieldsInitial();
        if(method_exists($this, "get_$name")){
            return $this->{"get_$name"}();
        } elseif(array_key_exists($name, $this->_fields)){
            return $this->_fields[$name];
        }else {
            return parent::__get($name);
        }
    }
    
    public function get_fields(){
    	return $this->_fields;
    }
    
    public function get_primary(){
    	return $this->_primary;
    }
    
	public function set_encryption_key($data){
        $this->encryption_key = $data;
    }

    public function set_all_field_FilterDefinition($filter, $table_alias = null, $field_expression_list = array()){
        foreach ($this->_fields as $key => $value) {
    		if (
    				is_array($field_expression_list) 
    				and ! empty($field_expression_list)
    				and array_key_exists($key,$field_expression_list)
    		)
    			$filter->setFilterDefinition(null, $field_expression_list[$key],$key);
    		else
    			$filter->setFilterDefinition($table_alias, $key);
        }
    }
    
    public function get_select_field_list($field_expression_list = array()){
    	if (!is_array($field_expression_list) or empty($field_expression_list)){
    	  return $this->_fields;
    	}
    	else {
    		$out_field_list = array();
    		foreach ($this->_fields as $key => $value) {
    			if (array_key_exists($key,$field_expression_list))
    				$out_field_list[$field_expression_list[$key].' AS '.$key] = $value;
    			else 
    				$out_field_list[$key] = $value;
    		}    		
    		//logmes('at',$out_field_list,'qwe');
    		return $out_field_list;
    	}
    }
        
	public function read ($where = array(), $limit = null, $offset = null, $order_by = null, $table = null, $key_expr = null, $check_web_logo = null)
    {
    	//logmes(' backtrace_info : ',$this->db->_get_backtrace_info(),__CLASS__);
    	
    	$table = empty($table) ? $this->_name : $table;
    	$key_expr = empty($key_expr) && ! empty($this->encryption_key) ? '"'.$this->encryption_key.'"' : $key_expr;
    	
		$field_list = array();
		//foreach ($this->_fields as $key => $value) {
		$new_field_list = $this->get_select_field_list();
		foreach ($new_field_list as $key => $value) {
			if (array_key_exists($key, $this->_encryption_fields) && ! empty($key_expr)) {
				//log_message('debug', __METHOD__.' $key = '.var_export($key, true));
				$field_list[] = 'CAST(AES_DECRYPT('.$key.', '.$key_expr.') AS CHAR) AS '.$key;
			}
			else {
				$field_list[] = $key;
			}
		}
    	$field_list = implode(', ', $field_list);
    	
    	//log_message('debug', __METHOD__.' $field_list = '.var_export($field_list, true));
    	
        $this->db->select($field_list, false)->from($table);

        if (is_array($where) && count($where)>0) 
        {
        	foreach ($where as $key => $value) {
        		if (is_array($value)) {
        			if (empty($value)) 
        				return array();
        			else 	
        				$this->db->where_in($key, $value);
        			
        			unset($where[$key]);
        		}     
        	}
        	$this->db->where($where);
        } 
        else { 
	        if(empty($check_web_logo))
	        {
	            $where = array();
	            //foreach ($this->_fields as $key => $value){
	            foreach ($new_field_list as $key => $value){
	                if ( ! is_null($value) && strlen($value)>0){
	                    $where[$key] = $value;
	                }
	            }
	            $this->db->where($where);
	        }
        }
        
        foreach ($this->_filters->items as $filter) {
        	$option = 'none'; //FieldFilter::OPTION_EQUAL;
        	switch ($filter->option) {
        		case FieldFilter::OPTION_EQUAL:
        			$option = 'none';
        			break;
				case FieldFilter::OPTION_CONTAINS: 
        			$option = 'both';
        			break;
				case FieldFilter::OPTION_STARTS:
					$option = 'after';
        			break;
        		case FieldFilter::OPTION_ENDS:
        			$option = 'before';
        			break;
        	}
        	$this->db->like($filter->name, $filter->value, $option);
        }
        
        if ( ! is_null($limit) && is_numeric($limit)){
	        if ( ! is_null($offset) && is_numeric($offset))
	            $this->db->limit($limit, $offset);
	        else 
            	$this->db->limit($limit);
        }
    	if( ! empty($order_by)){
    		//$order_by = preg_replace("/(.*)(\s+asc\s{0,},|\s+desc\s{0,},|\s+asc\s{0,}|\s+desc\s{0,})(.*)/iU", 'CAST($1 AS CHAR) $2 $3 ', $order_by);
    		//log_message('debug', __METHOD__.' $order_by = '.var_export($order_by, true));
    		
			$this->db->order_by($order_by);
		}	
        $result =  $this->db->get();
        
        if ( ! $result) return $result;
		
        return $result->result();
    }
    
    public function read_count ($where = array(), $table = null)
    {
    	$table = empty($table) ? $this->_name : $table;
    	    	 
    	$this->db->select('COUNT(*) as cnt', false)->from($table);
    
    	if (is_array($where) && count($where)>0)
    	{
    		foreach ($where as $key => $value) {
    			if (is_array($value)) {
    				if (empty($value))
    					return array();
    				else
    					$this->db->where_in($key, $value);
    				 
    				unset($where[$key]);
    			}
    		}
    		$this->db->where($where);
    	} else {
    		$where = array();
    		foreach ($this->_fields as $key => $value){
    			if ( ! is_null($value) && strlen($value)>0){
    				$where[$key] = $value;
    			}
    		}
    		$this->db->where($where);
    	}
    
    	foreach ($this->_filters->items as $filter) {
    		$option = 'none'; //FieldFilter::OPTION_EQUAL;
    		switch ($filter->option) {
    			case FieldFilter::OPTION_EQUAL:
    				$option = 'none';
    				break;
    			case FieldFilter::OPTION_CONTAINS:
    				$option = 'both';
    				break;
    			case FieldFilter::OPTION_STARTS:
    				$option = 'after';
    				break;
    			case FieldFilter::OPTION_ENDS:
    				$option = 'before';
    				break;
    		}
    		$this->db->like($filter->name, $filter->value, $option);
    	}
    
    	$result =  $this->db->get();
    	
    	if ( ! $result) return $result;
    
    	return $result->row()->cnt;
    }
    
    public function get_list(array $where = array(), $limit = null, $offset = null, $order_by = null, array $filters = array(), array $fields = array())
    {
    	if ( ! empty($fields)) {
    		foreach ($this->_fields as $field => $val) {
    			if ( ! in_array($field, $fields)) unset($this->_fields[$field]);
    		}
    	}
    	
    	if ( ! empty($filters)) {
    		foreach ($filters as $filter) {
    			if (isset($filter['name']) && isset($filter['value'])) {
    				$option = isset($filter['option']) ? $filter['option'] : null;
    				$this->_filters->addFilter($filter['name'], $filter['value'], $option);
    			}
    		}
    	}
    	
    	$result = $this->read($where, $limit, $offset, $order_by);
    	 
    	$resultObj = new stdClass;
        $resultObj->data = $result;
        $resultObj->count = $limit ? $this->read_count($where) : count($result);
        
        return $resultObj;
    }
    
    public function get_list_with_includes(array $where = array(), $limit = null, $offset = null, $order_by = null,
    		array $filters = array(), array $fields = array(), array $includes = array())
    {
    	
    	$methods = get_class_methods($this);
    	
    	foreach ($includes as $include) {
    		$get_list_method = 'get_list__'.$include;
    		if (in_array($get_list_method, $methods))
    			return $this->$get_list_method($limit, $offset, $order_by, $filters, $fields);
    	}
    	
    	$resultObj = $this->get_list($where, $limit, $offset, $order_by, $filters, $fields);
    
    	if ( ! empty($includes)) {
    		foreach ($resultObj->data as &$row) {
    			if (in_array('the_field', $includes)) {
    				//$row->the_field =  $this->get_the_field($row->{$this->_primary});
    			}
    		}
    	}
    
    	return $resultObj;
    }
    
	public function save($query_type = null)
	{
		//log_message('debug', __METHOD__.' $this->_fields = '.var_export($this->_fields, true));
		$data = $encrypted_data = $field_expr = array();
		foreach ($this->_fields as $key => $value) {
			 if ( ! is_null($value) && strlen($value)>0) {
				if (array_key_exists($key, $this->_encryption_fields) && ! empty($this->encryption_key)) {
					$value = 'AES_ENCRYPT("'.$value.'","'.$this->encryption_key.'")';
					$field_expr[] = $key.'='.$value;
					$encrypted_data[$key] = $value;
				}
				else {
                        //$field_expr[] = $key.' = "'.$value.'" ';
                        //$data[$key] = $value;
                        if ($value[0] === '#') {
                        	$value = substr($value, 1);
							$field_expr[] = $key.' = '.$value;
							$data[$key] = $value;
                        }
                        else {
							$field_expr[] = $key.' = '.$this->db->escape($value);
							$data[$key] = $this->db->escape($value);
                        }
                }
			}
		}
		$field_expr = implode(', ', $field_expr);
		
		//log_message('debug', __METHOD__.' $data = '.var_export($data, true));
		//log_message('debug', __METHOD__.' $encrypted_data = '.var_export($encrypted_data, true));
		
		$primary = $this->{$this->_primary};
		/*
		$query_type = empty($query_type) && $this->_primary_auto_increment && empty($primary) ? 
								'insert' : (
									empty($query_type) && $this->_primary_auto_increment ? 'update' : (
										empty($query_type) && empty($primary) ? ''
									)
								);*/
		$query_type = ! empty($query_type) ? strtoupper($query_type) : (
									$this->_primary_auto_increment && empty($primary) ? 'INSERT' : (
										$this->_primary_auto_increment ? 'UPDATE' : 'INSERT'
											//(empty($primary) ? false : 'REPLACE')
									)
								);
								
		if 	($query_type === false) return false;							
		
		//if ( ! empty($primary))
		if ($query_type == 'UPDATE') 
        {
			$sql = 'UPDATE '.$this->_name.' SET '.$field_expr.' WHERE '.$this->_primary.'="'.$this->{$this->_primary}.'"';
            //log_message('debug', __METHOD__.' $sql = '.var_export($sql, true));
			$result = $this->db->query($sql);
		}
        else 
        {
			$sql_fields = implode(', ', array_keys($data));
			//$sql_values = empty($data) ? '' : "'".implode("', '", array_values($data))."'";
			$sql_values = empty($data) ? '' : implode(", ", array_values($data));
			if ( ! empty($encrypted_data)) {
				$encrypted_sql_fields = implode(', ', array_keys($encrypted_data));
				$encrypted_sql_values = implode(', ', array_values($encrypted_data));
				
				$sql_fields = empty($sql_fields) ? '' : $sql_fields.', ';
				$sql_fields .= $encrypted_sql_fields;
				
				$sql_values = empty($sql_values) ? '' : $sql_values.', ';
				$sql_values .= $encrypted_sql_values;
			}
        	$sql = "INSERT INTO ".$this->_name."  (".$sql_fields.") VALUES (".$sql_values.")";
        	//log_message('debug', __METHOD__.' $sql = '.var_export($sql, true));
			$result = $this->db->query($sql);

            if ($result)
				$this->{$this->_primary} = $this->db->insert_id();
		}
		
		return $result;
    }
    
    public function delete($where) {
    	if ( ! empty($where)) {
    		$this->db->where($where); 
			return $this->db->delete($this->_name); 
		}
		else 
			return false;
    }
    
    public function MakeClientTZOffsetSQL($datename)
    {
        $client_minute = $this->session->userdata('client_tz_offset_in_minute');
    	//logmes('! - ',$client_minute,'qqq');
        if(! empty($client_minute) && is_numeric($client_minute))
             	return "DATE_ADD(".$datename.", INTERVAL ".$this->session->userdata('client_tz_offset_in_minute')." MINUTE)";
        else       	
                return "DATE_ADD(".$datename.", INTERVAL 0 MINUTE)";
       
    }
    
    public function clear($full = false) {
    	$this->setFieldsInitial();
    	if (is_array($this->_fields_initial)) {
	    	foreach ($this->_fields_initial as $field=>$value) {
	    		$this->$field = ($full ? null : $value); 
	    	}
    	}
    }
    
} 