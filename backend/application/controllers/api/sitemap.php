<?php defined('BASEPATH') OR exit('No direct script access allowed');

class sitemap extends API_Controller {
	
	public function sitemap_create() {
		$this->load->helper('sitemap');
		$this->load->model('_model'); // !!! change model to appropriate one 
		$result = sitemap_create($this->_model);
		if ($result) {
			readfile(FCPATH."files/sitemap.xml");
			//$this->output->set_output($result);
		}
		else return $result;
	}

}
