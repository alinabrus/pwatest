<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Profiler Sections
| -------------------------------------------------------------------------
| This file lets you determine whether or not various sections of Profiler
| data are displayed when the Profiler is enabled.
| Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/profiling.html
|
*/

/*protected $_available_sections = array(
										'benchmarks',
										'get',
										'memory_usage',
										'post',
										'uri_string',
										'controller_info',
										'queries',
										'http_headers',
										'session_data',
										'config'
										);
*/

$config['config'] = FALSE;
$config['controller_info'] = FALSE;
$config['http_headers'] = FALSE;
$config['queries'] = FALSE;     // logs them anyway, double-logs them if enabled

/* End of file profiler.php */
/* Location: ./application/config/profiler.php */