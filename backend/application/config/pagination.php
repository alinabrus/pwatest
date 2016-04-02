<?php

$config['num_links'] = 1;
$config['full_tag_open'] = '<ul class="pagination">';
$config['full_tag_close'] = '</ul>';
$config['num_tag_open'] = '<li>';
$config['num_tag_close'] = '</li>';
$config['cur_tag_open'] = '<li  class="active"><a>';
$config['cur_tag_close'] = '</a></li>';
$config['prev_link'] = '&larr; Prev';
$config['prev_tag_open'] = '<li>';
$config['prev_tag_close'] = '</li>';
$config['next_link'] = 'Next &rarr;';
$config['next_tag_open'] = '<li>';
$config['next_tag_close'] = '</li>';
$config['last_link'] = 'Last';
$config['last_tag_open'] = '<li>';
$config['last_tag_close'] = '</li>';
$config['first_link'] = 'First';
$config['first_tag_open'] = '<li>';
$config['first_tag_close'] = '</li>';
//$config['display_pages'] = FALSE;

// -- to be overriden in controller --
$config['base_url'] = base_url();
$config['total_rows'] = 1000;
$config['per_page'] = 10;

/* End of file autoload.php */