<?php defined('BASEPATH') OR exit('No direct script access allowed');

class sitemap extends CI_Controller {
	
	public function sitemap_create() {
		$this->load->helper('sitemap');
		$this->load->model('mx_campaigns_model');
		$result = sitemap_create($this->mx_campaigns_model);
		if ($result) {
			readfile(FCPATH."files/sitemap.xml");
			//$this->output->set_output($result);
		}
		else return $result;
	}

}
