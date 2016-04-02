<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

if ( ! defined('DB_ERROR_LOG')) define('DB_ERROR_LOG', 'db_error_log__[mysqli_ba_driver]');

define('DB_DRIVER_DEBUG', TRUE);
define('DB_DRIVER_TEST', FALSE);
define('DB_DRIVER_LOG', 'db_driver_log__[mysqli_ba_driver]');

/**
 * MySQLi Database Adapter Class - MySQLi only works with PHP 5
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_mysqli_ba_driver extends CI_DB {

	var $dbdriver = 'mysqli';

	// The character used for escaping
	var $_escape_char = '`';

	// clause and character used for LIKE escape sequences - not used in MySQL
	var $_like_escape_str = '';
	var $_like_escape_chr = '';

	/**
	 * The syntax to count rows is slightly different across different
	 * database engines, so this string appears in each driver and is
	 * used for the count_all() and count_all_results() functions.
	 */
	var $_count_string = "SELECT COUNT(*) AS ";
	var $_random_keyword = ' RAND()'; // database specific random keyword

	/**
	 * Whether to use the MySQL "delete hack" which allows the number
	 * of affected rows to be shown. Uses a preg_replace when enabled,
	 * adding a bit more processing to all queries.
	 */
	var $delete_hack = TRUE;

	// whether SET NAMES must be used to set the character set
	var $use_set_names;
	
	// --------------------------------------------------------------------
	
	var $_err_code;
	var $_err_message;
	
	// --------------------------------------------------------------------
	
	/**
	 * Non-persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	/*
	function db_connect()
	{
		if ($this->port != '')
		{
			return @mysqli_connect($this->hostname, $this->username, $this->password, $this->database, $this->port);
		}
		else
		{
			return @mysqli_connect($this->hostname, $this->username, $this->password, $this->database);
		}

	}
	*/
	function db_connect()
	{
		$port = $this->port != '' ? $this->port : null;
		$link = @mysqli_init();
		@mysqli_options($link, MYSQLI_OPT_LOCAL_INFILE, true);
		@mysqli_real_connect($link, $this->hostname, $this->username, $this->password, $this->database, $port);
		return $link;
	}

	// --------------------------------------------------------------------

	/**
	 * Persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_pconnect()
	{
		return $this->db_connect();
	}

	// --------------------------------------------------------------------

	/**
	 * Reconnect
	 *
	 * Keep / reestablish the db connection if no queries have been
	 * sent for a length of time exceeding the server's idle timeout
	 *
	 * @access	public
	 * @return	void
	 */
	function reconnect()
	{
		if (mysqli_ping($this->conn_id) === FALSE)
		{
			$this->conn_id = FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Select the database
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_select()
	{
		$result =  @mysqli_select_db($this->conn_id, $this->database);
		if (DB_DRIVER_TEST)	$this->trans_test();
		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Set client character set
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	resource
	 */
	function _db_set_charset($charset, $collation)
	{
		if ( ! isset($this->use_set_names))
		{
			// mysqli_set_charset() requires MySQL >= 5.0.7, use SET NAMES as fallback
			$this->use_set_names = (version_compare(mysqli_get_server_info($this->conn_id), '5.0.7', '>=')) ? FALSE : TRUE;
		}

		if ($this->use_set_names === TRUE)
		{
			return @mysqli_query($this->conn_id, "SET NAMES '".$this->escape_str($charset)."' COLLATE '".$this->escape_str($collation)."'");
		}
		else
		{
			return @mysqli_set_charset($this->conn_id, $charset);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Version number query string
	 *
	 * @access	public
	 * @return	string
	 */
	function _version()
	{
		return "SELECT version() AS ver";
	}

	// --------------------------------------------------------------------

	/**
	 * Execute the query
	 *
	 * @access	private called by the base class
	 * @param	string	an SQL query
	 * @return	resource
	 */
	function _execute($sql)
	{
		$sql = $this->_prep_query($sql);
		$result = @mysqli_query($this->conn_id, $sql);
		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep the query
	 *
	 * If needed, each database adapter can prep the query string
	 *
	 * @access	private called by execute()
	 * @param	string	an SQL query
	 * @return	string
	 */
	function _prep_query($sql)
	{
		// "DELETE FROM TABLE" returns 0 affected rows This hack modifies
		// the query so that it returns the number of affected rows
		if ($this->delete_hack === TRUE)
		{
			if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
			{
				$sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
			}
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Begin Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	
	 function trans_begin($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		// Reset the transaction failure flag.
		// If the $test_mode flag is set to TRUE transactions will be rolled back
		// even if the queries produce a successful result.
		$this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;

		//$this->simple_query('SET AUTOCOMMIT=0');
		//$this->simple_query('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
		$this->query('SET AUTOCOMMIT=0', FALSE, FALSE);
		$this->query('START TRANSACTION', FALSE, FALSE); // can also be BEGIN or BEGIN WORK
		
		return TRUE;
	} 
	/*
	function trans_begin($test_mode = FALSE)
	{
		if (DB_DRIVER_DEBUG) {
			//logmes(__METHOD__.' $backtrace = ', $this->_get_backtrace_info(), DB_DRIVER_LOG);
			//logmes(__METHOD__.' <<< $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}
		
		$this->_trans_depth += 1;
		
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 1)
		{
			if (DB_DRIVER_DEBUG) {
				logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
			}
			return TRUE;
		}
		
		$this->_trans_status = TRUE;
		
		// Reset the transaction failure flag.
		// If the $test_mode flag is set to TRUE transactions will be rolled back
		// even if the queries produce a successful result.
		$this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;
		
		$this->query('SET AUTOCOMMIT=0', FALSE, FALSE);
		$this->query('START TRANSACTION', FALSE, FALSE); // can also be BEGIN or BEGIN WORK
		
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		return TRUE;
	}
	*/
	// --------------------------------------------------------------------

	/**
	 * Commit Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	/*
	 function trans_commit()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		//$this->simple_query('COMMIT');
		//$this->simple_query('SET AUTOCOMMIT=1');
		$this->query('COMMIT', FALSE, FALSE);
		$this->query('SET AUTOCOMMIT=1', FALSE, FALSE);
		return TRUE;
	}
	*/
	function trans_commit()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}
	
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}
		
		//$this->simple_query('COMMIT');
		//$this->simple_query('SET AUTOCOMMIT=1');
		$this->query('COMMIT', FALSE, FALSE);
		$this->query('SET AUTOCOMMIT=1', FALSE, FALSE);
		return TRUE;
	}
	/*
	function trans_commit()
	{
		if (DB_DRIVER_DEBUG) {
			//logmes(__METHOD__.' <<< $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}
		
		if ($this->_trans_depth > 0) 
		{
			$this->_trans_depth -= 1;
			// When transactions are nested we only begin/commit/rollback the outermost ones
			if ($this->_trans_depth > 0)
			{
				if (DB_DRIVER_DEBUG) {
					logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
				}
				return TRUE;
			}
	
			$this->query('COMMIT', FALSE, FALSE);
			$this->query('SET AUTOCOMMIT=1', FALSE, FALSE);
		}
		
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		return TRUE;
	}
	*/
	// --------------------------------------------------------------------

	/**
	 * Rollback Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	/*
	 function trans_rollback()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		//$this->simple_query('ROLLBACK');
		//$this->simple_query('SET AUTOCOMMIT=1');
		$this->query('ROLLBACK', FALSE, FALSE);
		$this->query('SET AUTOCOMMIT=1', FALSE, FALSE);
		return TRUE;
	} 
	*/
	function trans_rollback()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}
	
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}
		
		//$this->simple_query('ROLLBACK');
		//$this->simple_query('SET AUTOCOMMIT=1');
		$this->query('ROLLBACK', FALSE, FALSE);
		$this->query('SET AUTOCOMMIT=1', FALSE, FALSE);
		return TRUE;
	}
	
	/*
	function trans_rollback()
	{
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' <<< $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}
		
		//if ($this->_trans_depth > 0) 
		{		
			$this->query('ROLLBACK', FALSE, FALSE);
			$this->query('SET AUTOCOMMIT=1', FALSE, FALSE);
			
			//$this->_trans_depth -= 1;
		}
		if ($this->_trans_depth == 1)
			$this->_trans_depth = 0;
		
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		return TRUE;
	}
	*/
	// --------------------------------------------------------------------
	
		function trans_complete_rollback()
		{
			//$this->trans_rollback();
			$this->_trans_status = FALSE;
			if ($this->_trans_depth > 1) 
				$this->_trans_depth = 1;
			elseif ($this->_trans_depth == 0) 
				return TRUE; 
				
			return $this->trans_complete();
		}	
		
	// --------------------------------------------------------------------	

	/**
	 * Escape String
	 *
	 * @access	public
	 * @param	string
	 * @param	bool	whether or not the string will be used in a LIKE condition
	 * @return	string
	 */
	function escape_str($str, $like = FALSE)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}

		if (function_exists('mysqli_real_escape_string') AND is_object($this->conn_id))
		{
			$str = mysqli_real_escape_string($this->conn_id, $str);
		}
		elseif (function_exists('mysql_escape_string'))
		{
			$str = mysql_escape_string($str);
		}
		else
		{
			$str = addslashes($str);
		}

		// escape LIKE condition wildcards
		if ($like === TRUE)
		{
			$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Affected Rows
	 *
	 * @access	public
	 * @return	integer
	 */
	function affected_rows()
	{
		return @mysqli_affected_rows($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @access	public
	 * @return	integer
	 */
	function insert_id()
	{
		return @mysqli_insert_id($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * "Count All" query
	 *
	 * Generates a platform-specific query string that counts all records in
	 * the specified database
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function count_all($table = '')
	{
		if ($table == '')
		{
			return 0;
		}

		$query = $this->query($this->_count_string . $this->_protect_identifiers('numrows') . " FROM " . $this->_protect_identifiers($table, TRUE, NULL, FALSE));

		if ($query->num_rows() == 0)
		{
			return 0;
		}

		$row = $query->row();
		$this->_reset_select();
		return (int) $row->numrows;
	}

	// --------------------------------------------------------------------

	/**
	 * List table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @access	private
	 * @param	boolean
	 * @return	string
	 */
	function _list_tables($prefix_limit = FALSE)
	{
		$sql = "SHOW TABLES FROM ".$this->_escape_char.$this->database.$this->_escape_char;

		if ($prefix_limit !== FALSE AND $this->dbprefix != '')
		{
			$sql .= " LIKE '".$this->escape_like_str($this->dbprefix)."%'";
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Show column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	string
	 */
	function _list_columns($table = '')
	{
		return "SHOW COLUMNS FROM ".$this->_protect_identifiers($table, TRUE, NULL, FALSE);
	}

	// --------------------------------------------------------------------

	/**
	 * Field data query
	 *
	 * Generates a platform-specific query so that the column data can be retrieved
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	object
	 */
	function _field_data($table)
	{
		return "DESCRIBE ".$table;
	}

	// --------------------------------------------------------------------

	/**
	 * The error message string
	 *
	 * @access	private
	 * @return	string
	 */
	function _error_message()
	{
		return mysqli_error($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * The error message number
	 *
	 * @access	private
	 * @return	integer
	 */
	function _error_number()
	{
		return mysqli_errno($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _escape_identifiers($item)
	{
		if ($this->_escape_char == '')
		{
			return $item;
		}

		foreach ($this->_reserved_identifiers as $id)
		{
			if (strpos($item, '.'.$id) !== FALSE)
			{
				$str = $this->_escape_char. str_replace('.', $this->_escape_char.'.', $item);

				// remove duplicates if the user already included the escape
				return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
			}
		}

		if (strpos($item, '.') !== FALSE)
		{
			$str = $this->_escape_char.str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item).$this->_escape_char;
		}
		else
		{
			$str = $this->_escape_char.$item.$this->_escape_char;
		}

		// remove duplicates if the user already included the escape
		return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
	}

	// --------------------------------------------------------------------

	/**
	 * From Tables
	 *
	 * This function implicitly groups FROM tables so there is no confusion
	 * about operator precedence in harmony with SQL standards
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	function _from_tables($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = array($tables);
		}

		return '('.implode(', ', $tables).')';
	}

	// --------------------------------------------------------------------

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	function _insert($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
	}

	// --------------------------------------------------------------------

	/**
	 * Insert_batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	function _insert_batch($table, $keys, $values)
	{
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ".implode(', ', $values);
	}

	// --------------------------------------------------------------------


	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	function _replace($table, $keys, $values)
	{
		return "REPLACE INTO ".$table." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
	}
	
	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @param	array	the orderby clause
	 * @param	array	the limit clause
	 * @return	string
	 */
	function _update($table, $values, $where, $orderby = array(), $limit = FALSE)
	{
		foreach ($values as $key => $val)
		{
			$valstr[] = $key." = ".$val;
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		$orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';

		$sql = "UPDATE ".$table." SET ".implode(', ', $valstr);

		$sql .= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" ", $where) : '';

		$sql .= $orderby.$limit;

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @return	string
	 */
	function _update_batch($table, $values, $index, $where = NULL)
	{
		$ids = array();
		$where = ($where != '' AND count($where) >=1) ? implode(" ", $where).' AND ' : '';

		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field != $index)
				{
					$final[$field][] =  'WHEN '.$index.' = '.$val[$index].' THEN '.$val[$field];
				}
			}
		}

		$sql = "UPDATE ".$table." SET ";
		$cases = '';

		foreach ($final as $k => $v)
		{
			$cases .= $k.' = CASE '."\n";
			foreach ($v as $row)
			{
				$cases .= $row."\n";
			}

			$cases .= 'ELSE '.$k.' END, ';
		}

		$sql .= substr($cases, 0, -2);

		$sql .= ' WHERE '.$where.$index.' IN ('.implode(',', $ids).')';

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 * If the database does not support the truncate() command
	 * This function maps to "DELETE FROM table"
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	string
	 */
	function _truncate($table)
	{
		return "TRUNCATE ".$table;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the where clause
	 * @param	string	the limit clause
	 * @return	string
	 */
	function _delete($table, $where = array(), $like = array(), $limit = FALSE)
	{
		$conditions = '';

		if (count($where) > 0 OR count($like) > 0)
		{
			$conditions = "\nWHERE ";
			$conditions .= implode("\n", $this->ar_where);

			if (count($where) > 0 && count($like) > 0)
			{
				$conditions .= " AND ";
			}
			$conditions .= implode("\n", $like);
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		return "DELETE FROM ".$table.$conditions.$limit;
	}

	// --------------------------------------------------------------------

	/**
	 * Limit string
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @access	public
	 * @param	string	the sql query string
	 * @param	integer	the number of rows to limit the query to
	 * @param	integer	the offset value
	 * @return	string
	 */
	function _limit($sql, $limit, $offset)
	{
		$sql .= "LIMIT ".$limit;

		if ($offset > 0)
		{
			$sql .= " OFFSET ".$offset;
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Close DB Connection
	 *
	 * @access	public
	 * @param	resource
	 * @return	void
	 */
	function _close($conn_id)
	{
		@mysqli_close($conn_id);
	}

	// ----------------------------------------------------------------------------------
	// adding methods to override parent ones (system\database\DB_driver.php)
	// <<<-----------------------------------------------------------------------------
	
	/**
	 * Execute the query
	 *
	 * Accepts an SQL string as input and returns a result object upon
	 * successful execution of a "read" type query.  Returns boolean TRUE
	 * upon successful execution of a "write" type query. Returns boolean
	 * FALSE upon failure, and if the $db_debug variable is set to TRUE
	 * will raise an error.
	 *
	 * @access	public
	 * @param	string	An SQL query string
	 * @param	array	An array of binding data
	 * @return	mixed
	 */
	/*
	 function query($sql, $binds = FALSE, $return_object = TRUE)
	{
		if ($sql == '')
		{
			if ($this->db_debug)
			{
				log_message('error', 'Invalid query: '.$sql);
				return $this->display_error('db_invalid_query');
			}
			return FALSE;
		}

		// Verify table prefix and replace if necessary
		if ( ($this->dbprefix != '' AND $this->swap_pre != '') AND ($this->dbprefix != $this->swap_pre) )
		{
			$sql = preg_replace("/(\W)".$this->swap_pre."(\S+?)/", "\\1".$this->dbprefix."\\2", $sql);
		}

		// Compile binds if needed
		if ($binds !== FALSE)
		{
			$sql = $this->compile_binds($sql, $binds);
		}

		// Is query caching enabled?  If the query is a "read type"
		// we will load the caching class and return the previously
		// cached query if it exists
		if ($this->cache_on == TRUE AND stristr($sql, 'SELECT'))
		{
			if ($this->_cache_init())
			{
				$this->load_rdriver();
				if (FALSE !== ($cache = $this->CACHE->read($sql)))
				{
					return $cache;
				}
			}
		}

		// Save the  query for debugging
		if ($this->save_queries == TRUE)
		{
			$this->queries[] = $sql;
		}

		// Start the Query Timer
		$time_start = list($sm, $ss) = explode(' ', microtime());

		// Run the Query
		if (FALSE === ($this->result_id = $this->simple_query($sql)))
		{
			if ($this->save_queries == TRUE)
			{
				$this->query_times[] = 0;
			}

			// This will trigger a rollback if transactions are being used
			$this->_trans_status = FALSE;

			if ($this->db_debug)
			{
				// grab the error number and message now, as we might run some
				// additional queries before displaying the error
				$error_no = $this->_error_number();
				$error_msg = $this->_error_message();

				// We call this function in order to roll-back queries
				// if transactions are enabled.  If we don't call this here
				// the error message will trigger an exit, causing the
				// transactions to remain in limbo.
				$this->trans_complete();

				// Log and display errors
				log_message('error', 'Query error: '.$error_msg);
				return $this->display_error(
										array(
												'Error Number: '.$error_no,
												$error_msg,
												$sql
											)
										);
			}

			return FALSE;
		}

		// Stop and aggregate the query time results
		$time_end = list($em, $es) = explode(' ', microtime());
		$this->benchmark += ($em + $es) - ($sm + $ss);

		if ($this->save_queries == TRUE)
		{
			$this->query_times[] = ($em + $es) - ($sm + $ss);
		}

		// Increment the query counter
		$this->query_count++;

		// Was the query a "write" type?
		// If so we'll simply return true
		if ($this->is_write_type($sql) === TRUE)
		{
			// If caching is enabled we'll auto-cleanup any
			// existing files related to this particular URI
			if ($this->cache_on == TRUE AND $this->cache_autodel == TRUE AND $this->_cache_init())
			{
				$this->CACHE->delete();
			}

			return TRUE;
		}

		// Return TRUE if we don't need to create a result object
		// Currently only the Oracle driver uses this when stored
		// procedures are used
		if ($return_object !== TRUE)
		{
			return TRUE;
		}

		// Load and instantiate the result driver

		$driver			= $this->load_rdriver();
		$RES			= new $driver();
		$RES->conn_id	= $this->conn_id;
		$RES->result_id	= $this->result_id;

		if ($this->dbdriver == 'oci8')
		{
			$RES->stmt_id		= $this->stmt_id;
			$RES->curs_id		= NULL;
			$RES->limit_used	= $this->limit_used;
			$this->stmt_id		= FALSE;
		}

		// oci8 vars must be set before calling this
		$RES->num_rows	= $RES->num_rows();

		// Is query caching enabled?  If so, we'll serialize the
		// result object and save it to a cache file.
		if ($this->cache_on == TRUE AND $this->_cache_init())
		{
			// We'll create a new instance of the result object
			// only without the platform specific driver since
			// we can't use it with cached data (the query result
			// resource ID won't be any good once we've cached the
			// result object, so we'll have to compile the data
			// and save it)
			$CR = new CI_DB_result();
			$CR->num_rows		= $RES->num_rows();
			$CR->result_object	= $RES->result_object();
			$CR->result_array	= $RES->result_array();

			// Reset these since cached objects can not utilize resource IDs.
			$CR->conn_id		= NULL;
			$CR->result_id		= NULL;

			$this->CACHE->write($sql, $CR);
		}

		return $RES;
	}
	 */
	
	function query($sql, $binds = FALSE, $return_object = TRUE)
	{
		$sql = trim($sql);
		if ( strtoupper(substr($sql,0,6)) !== 'SELECT' //&& strtoupper(substr($sql,0,8)) !== 'ROLLBACK'
				&& $this->trans_enabled && ($this->_trans_depth > 1) 
				&& ($this->_trans_status === FALSE)
		) return FALSE;
		
		if (DB_DRIVER_DEBUG && strtoupper(substr($sql,0,6)) !== 'SELECT') {
			logmes(__METHOD__.' $sql = ', $sql, DB_DRIVER_LOG);
		}
		
		if ($sql == '')
		{
			if ($this->db_debug)
			{
				log_message('error', 'Invalid query: '.$sql);
				return $this->display_error('db_invalid_query');
			}
			return FALSE;
		}
	
		// Verify table prefix and replace if necessary
		if ( ($this->dbprefix != '' AND $this->swap_pre != '') AND ($this->dbprefix != $this->swap_pre) )
		{
			$sql = preg_replace("/(\W)".$this->swap_pre."(\S+?)/", "\\1".$this->dbprefix."\\2", $sql);
		}
	
		// Compile binds if needed
		if ($binds !== FALSE)
		{
			$sql = $this->compile_binds($sql, $binds);
		}
	
		// Is query caching enabled?  If the query is a "read type"
		// we will load the caching class and return the previously
		// cached query if it exists
		if ($this->cache_on == TRUE AND stristr($sql, 'SELECT'))
		{
			if ($this->_cache_init())
			{
				$this->load_rdriver();
				if (FALSE !== ($cache = $this->CACHE->read($sql)))
				{
					return $cache;
				}
			}
		}
	
		// Save the  query for debugging
		if ($this->save_queries == TRUE)
		{
			$this->queries[] = $sql;
		}
	
		// Start the Query Timer
		$time_start = list($sm, $ss) = explode(' ', microtime());
	
		// Run the Query
		if (FALSE === ($this->result_id = $this->simple_query($sql)))
		{
			if ($this->save_queries == TRUE)
			{
				$this->query_times[] = 0;
			}
	
			// This will trigger a rollback if transactions are being used
			$this->_trans_status = FALSE;
			
			// grab the error number and message now, as we might run some
			// additional queries before displaying the error
			$this->_err_code = $this->_error_number();
			$this->_err_message = $this->_error_message();
			
			$this->db_error_log($sql);
				
			if ($this->db_debug)
			{
				// We call this function in order to roll-back queries
				// if transactions are enabled.  If we don't call this here
				// the error message will trigger an exit, causing the
				// transactions to remain in limbo.
				$this->trans_complete();
				
				// Log and display errors
				log_message('error', 'Query error: '.$error_msg);
				return $this->display_error(
						array(
								'Error Number: '.$this->_err_code,
								$this->_err_message,
								$sql
						)
				);
			}
	
			return FALSE;
		}
	
		// Stop and aggregate the query time results
		$time_end = list($em, $es) = explode(' ', microtime());
		$this->benchmark += ($em + $es) - ($sm + $ss);
	
		if ($this->save_queries == TRUE)
		{
			$this->query_times[] = ($em + $es) - ($sm + $ss);
		}
	
		// Increment the query counter
		$this->query_count++;
	
		// Was the query a "write" type?
		// If so we'll simply return true
		if ($this->is_write_type($sql) === TRUE)
		{
			// If caching is enabled we'll auto-cleanup any
			// existing files related to this particular URI
			if ($this->cache_on == TRUE AND $this->cache_autodel == TRUE AND $this->_cache_init())
			{
				$this->CACHE->delete();
			}
	
			return TRUE;
		}
	
		// Return TRUE if we don't need to create a result object
		// Currently only the Oracle driver uses this when stored
		// procedures are used
		if ($return_object !== TRUE)
		{
			return TRUE;
		}
	
		// Load and instantiate the result driver
	
		$driver			= $this->load_rdriver();
		$RES			= new $driver();
		$RES->conn_id	= $this->conn_id;
		$RES->result_id	= $this->result_id;
	
		if ($this->dbdriver == 'oci8')
		{
			$RES->stmt_id		= $this->stmt_id;
			$RES->curs_id		= NULL;
			$RES->limit_used	= $this->limit_used;
			$this->stmt_id		= FALSE;
		}
	
		// oci8 vars must be set before calling this
		$RES->num_rows	= $RES->num_rows();
	
		// Is query caching enabled?  If so, we'll serialize the
		// result object and save it to a cache file.
		if ($this->cache_on == TRUE AND $this->_cache_init())
		{
			// We'll create a new instance of the result object
			// only without the platform specific driver since
			// we can't use it with cached data (the query result
			// resource ID won't be any good once we've cached the
			// result object, so we'll have to compile the data
			// and save it)
			$CR = new CI_DB_result();
			$CR->num_rows		= $RES->num_rows();
			$CR->result_object	= $RES->result_object();
			$CR->result_array	= $RES->result_array();
	
			// Reset these since cached objects can not utilize resource IDs.
			$CR->conn_id		= NULL;
			$CR->result_id		= NULL;
	
			$this->CACHE->write($sql, $CR);
		}
	
		return $RES;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Start Transaction
	 *
	 * @access	public
	 * @return	void
	 */
	/*
	 function trans_start($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return FALSE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			$this->_trans_depth += 1;
			return;
		}

		$this->trans_begin($test_mode);
	} 
	*/
	function trans_start($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return FALSE;
		}
		
		if (DB_DRIVER_DEBUG) {
			//logmes(__METHOD__.' <<< $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			$this->_trans_depth += 1;
			return;
		}
	
		$this->trans_begin($test_mode);
		
		$this->_trans_depth += 1;
		
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
	}
	/*
	function trans_start($test_mode = FALSE)
	{
		if (DB_DRIVER_DEBUG) {
			//logmes(__METHOD__.' <<< $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}

		$this->trans_begin($test_mode);
		
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
	}
	*/
	// --------------------------------------------------------------------
	
	/**
	 * Complete Transaction
	 *
	 * @access	public
	 * @return	bool
	 */
	/*
	 function trans_complete()
	{
		if ( ! $this->trans_enabled)
		{
			return FALSE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 1)
		{
			$this->_trans_depth -= 1;
			return TRUE;
		}

		// The query() function will set this flag to FALSE in the event that a query failed
		if ($this->_trans_status === FALSE)
		{
			$this->trans_rollback();

			// If we are NOT running in strict mode, we will reset
			// the _trans_status flag so that subsequent groups of transactions
			// will be permitted.
			if ($this->trans_strict === FALSE)
			{
				$this->_trans_status = TRUE;
			}

			log_message('debug', 'DB Transaction Failure');
			return FALSE;
		}

		$this->trans_commit();
		
		return TRUE;
	}
	*/
	
	function trans_complete()
	{
		if ( ! $this->trans_enabled)
		{
			return FALSE;
		}
	
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' <<< $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
				
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 1)
		{
			$this->_trans_depth -= 1;
			return TRUE;
		}
		
		$this->_trans_depth -= 1;
		
		logmes(__METHOD__.' --- $this->_trans_status = ', (boolean)$this->_trans_status, DB_DRIVER_LOG);
	
		// The query() function will set this flag to FALSE in the event that a query failed
		if ($this->_trans_status === FALSE)
		{
			$this->trans_rollback();
	
			// If we are NOT running in strict mode, we will reset
			// the _trans_status flag so that subsequent groups of transactions
			// will be permitted.
			if ($this->trans_strict === FALSE)
			{
				$this->_trans_status = TRUE;
			}
	
			log_message('debug', 'DB Transaction Failure');
			
			$this->_trans_status = TRUE;
			
			if (DB_DRIVER_DEBUG) {
				logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
			}
			return FALSE;
		}
	
		$this->trans_commit();
		
		$this->_trans_status = TRUE;
	
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		return TRUE;
	}
	
	function trans_complete__()
	{
		if (DB_DRIVER_DEBUG) {
			//logmes(__METHOD__.' <<< $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		if ( ! $this->trans_enabled)
		{
			return FALSE;
		}
	
		// When transactions are nested we only begin/commit/rollback the outermost ones
		/*
		if ($this->_trans_depth > 1)
		{
			$this->_trans_depth -= 1;
			return TRUE;
		}
		*/
		// The query() function will set this flag to FALSE in the event that a query failed
		if ($this->_trans_status === FALSE)
		{
			$this->trans_rollback();
				
			// If we are NOT running in strict mode, we will reset
			// the _trans_status flag so that subsequent groups of transactions
			// will be permitted.
			/*
			if ($this->trans_strict === FALSE)
			{
				$this->_trans_status = TRUE;
			}
			*/
			log_message('error', 'DB Transaction Failure');
			if (function_exists('logmes')) {
				$errPath = $this->_get_backtrace_info();
				$errPath = "\n \t".implode("\n \t <- ",$errPath);
				logmes("DB Transaction Failure : $errPath",'',DB_ERROR_LOG);
				logmes("----------------------------------------------------------",'',DB_ERROR_LOG);
			}
			
			if (DB_DRIVER_DEBUG) {
				logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
			}
			return FALSE;
		}
		
		$this->trans_commit();
		
		if (DB_DRIVER_DEBUG) {
			logmes(__METHOD__.' >>> $this->_trans_depth = ', (string)$this->_trans_depth, DB_DRIVER_LOG);
		}
		
		return TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Fetch MySQL Field Names
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	array
	 */
	function list_fields($table = '')
	{
		// Is there a cached result?
		if (isset($this->data_cache['field_names'][$table]))
		{
			return $this->data_cache['field_names'][$table];
		}
	
		if ($table == '')
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_field_param_missing');
			}
			return FALSE;
		}
	
		if (FALSE === ($sql = $this->_list_columns($this->_protect_identifiers($table, TRUE, NULL, FALSE))))
		{
			if ($this->db_debug)
			{
				return $this->display_error('db_unsupported_function');
			}
			return FALSE;
		}
	
		$query = $this->query($sql);
		if ( ! $query) return array();
	
		$retval = array();
		foreach($query->result_array() as $row)
		{
			if (isset($row['COLUMN_NAME']))
			{
				$retval[] = $row['COLUMN_NAME'];
			}
			else
			{
				$retval[] = current($row);
			}
		}
	
		$this->data_cache['field_names'][$table] = $retval;
		return $this->data_cache['field_names'][$table];
	}
	
	// --------------------------------------------------------------------
	// adding methods to override parent ones
	// --------------------------------------------------------------->>>
	
	function last_error_code()
	{
		return $this->_err_code;
	}
	
	function last_error()
	{
		$error = new stdClass;
		$error->code = $this->_err_code;
		$error->message = $this->_err_message;
		return $error;
	}
	
	function _get_backtrace_info() {
		$backtrace = debug_backtrace();
		$errPath = array(); $i=0;
		if (is_array($backtrace)) {
			foreach($backtrace as $trace) {
				if (isset($trace['class']) && $trace['class'] == get_class($this)) continue;
				$tmsg = isset($trace['file']) ? ''.str_replace(dirname(BASEPATH),'',$trace['file']) : '';
				$tmsg .= isset($trace['line']) ? ' [at line '.$trace['line'].'] ' : '';
				$tmsg .= isset($trace['class']) ?
				(isset($trace['file']) ? ' calls ' : '').$trace['class']
				: '';
				$tmsg .= isset($trace['type']) ? $trace['type'] : ' calls function ';
				$tmsg .= isset($trace['function']) ? $trace['function'] : '';
				$errPath[] = $tmsg;
				if ( ! isset($trace['file'])) break;
			}
		}
		return $errPath;
	}
	
	function db_error_log($problemSql)
	{		
		$message = "-----------------  Query error  ---------- \n $problemSql \n\n";
		$message .= "Error code : {$this->_err_code} \n";
		$message .= "Error : {$this->_err_message} \n\n";
		
		$errPath = $this->_get_backtrace_info();
		
		$errPathPrefix = 'Error path';
		$errPath = "\n $errPathPrefix [".date('G:i')."]\t".implode("\n $errPathPrefix [".date('G:i')."] \t <- ",$errPath);
		
		$message .= "$errPathPrefix : $errPath \n";
		
		if (function_exists('logmes'))
		{
			logmes('', $message, DB_ERROR_LOG);
			logmes('', "------------------------------------------- ", DB_ERROR_LOG);
		}
		else {
			$message .= "------------------------------------------- \n";
			log_message('error', $message);
		}
	}
	
	function trans_test() {
		$this->trans_test_2(); // manual rollback in Exception handler
		$this->trans_test_1(); // success
		$this->trans_test_3(); // failed query
		$this->trans_test_4();	// failed query
		$this->trans_test_5();	// manual rollback 
	}
	
	function trans_test_1() {
		$driver_test_table = '_tmp_mysqli_ba_test';
		
		logmes(__METHOD__.' TEST ', '----------------------<<<', DB_DRIVER_LOG);
		
		$query = "DROP TABLE IF EXISTS $driver_test_table";
		$this->query($query);
		
		$query = "CREATE TABLE IF NOT EXISTS $driver_test_table (
							id INT NOT NULL AUTO_INCREMENT, 
							a INT NULL, 
							b INT NULL,
							PRIMARY KEY (id)
						)";
		$this->query($query);
		
		try {
			
			$this->trans_start(); // @@@@@@@@@
			
			$query = "INSERT INTO $driver_test_table (a, b) VALUES (1, 2)";
			$this->query($query);
			
			$this->trans_begin(); //---------
			
			$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
			$this->query($query);
			
			$this->trans_commit(); //---------
			
			$this->trans_start(); ////////////
			
			$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
			$this->query($query);
			
			$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
			$this->query($query);
			
			$this->trans_complete(); ///////////
			
			$this->trans_complete(); // @@@@@@@@@
		
		}
		catch (Exception $e) {
			$this->trans_rollback();
		}
		
		logmes(__METHOD__.' TEST ', '___________________DATA: ', DB_DRIVER_LOG);
		$query = "SELECT * FROM $driver_test_table";
		$rs = $this->query($query);
		if ($rs) 
			logmes(__METHOD__.' $driver_test_table data: ', $rs->result(), DB_DRIVER_LOG);
		logmes(__METHOD__.' TEST ', '---------------------->>>', DB_DRIVER_LOG);
		
		$query = "DROP TABLE IF EXISTS $driver_test_table";
		$this->query($query);
	}
	
	function trans_test_2() {
		$driver_test_table = '_tmp_mysqli_ba_test';
	
		logmes(__METHOD__.' TEST ', '----------------------<<<', DB_DRIVER_LOG);
	
		$query = "DROP TABLE IF EXISTS $driver_test_table";
		$this->query($query);
	
		$query = "CREATE TABLE IF NOT EXISTS $driver_test_table (
		id INT NOT NULL AUTO_INCREMENT,
		a INT NULL,
		b INT NULL,
		PRIMARY KEY (id)
		)";
		$this->query($query);
	
		try {
			
		$this->trans_start(); // @@@@@@@@@
	
		$query = "INSERT INTO $driver_test_table (a, b) VALUES (1, 2)";
		$this->query($query);
	
		$this->trans_begin(); //---------
	
		$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
		$this->query($query);
			
		//throw new Exception('');
	
		$this->trans_commit(); //---------
	
		$this->trans_start(); ////////////
	
		$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
		$this->query($query);
	
		$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
		$this->query($query);
	
		$this->trans_complete(); ///////////
		
		throw new Exception('');
	
		$this->trans_complete(); // @@@@@@@@@
	
		}
		catch (Exception $e) {
			//$this->trans_rollback();
			//$this->trans_complete();
			$this->trans_complete_rollback();
		}
	
		logmes(__METHOD__.' TEST ', '___________________DATA: ', DB_DRIVER_LOG);
		$query = "SELECT * FROM $driver_test_table";
		$rs = $this->query($query);
			if ($rs) logmes(__METHOD__.' $driver_test_table data: ', $rs->result(), DB_DRIVER_LOG);
			logmes(__METHOD__.' TEST ', '---------------------->>>', DB_DRIVER_LOG);
	
			$query = "DROP TABLE IF EXISTS $driver_test_table";
			$this->query($query);
	}
	
	function trans_test_3() {
		$driver_test_table = '_tmp_mysqli_ba_test';
		
		logmes(__METHOD__.' TEST ', '----------------------<<<', DB_DRIVER_LOG);
		
		$query = "DROP TABLE IF EXISTS $driver_test_table";
		$this->query($query);
		
		$query = "CREATE TABLE IF NOT EXISTS $driver_test_table (
		id INT NOT NULL AUTO_INCREMENT,
		a INT NULL,
		b INT NULL,
		PRIMARY KEY (id)
		)";
		$this->query($query);
		
		try {
			
		$this->trans_start(); // @@@@@@@@@
			
		$query = "INSERT INTO $driver_test_table (a, b) VALUES (1, 2)";
		$this->query($query);
			
		$this->trans_begin(); //---------
			
		$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
		$this->query($query);
			
		$this->trans_commit(); //---------
			
		$this->trans_start(); ////////////
			
		$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
		$this->query($query);
			
		$query = "UPDATE xxx SET a = a+1, b = b+1";
		$this->query($query);
			
		$this->trans_complete(); ///////////
			
		$this->trans_complete(); // @@@@@@@@@
		
		}
		catch (Exception $e) {
		$this->trans_rollback();
		}
		
		logmes(__METHOD__.' TEST ', '___________________DATA: ', DB_DRIVER_LOG);
		$query = "SELECT * FROM $driver_test_table";
		$rs = $this->query($query);
		if ($rs)
			logmes(__METHOD__.' $driver_test_table data: ', $rs->result(), DB_DRIVER_LOG);
			logmes(__METHOD__.' TEST ', '---------------------->>>', DB_DRIVER_LOG);
		
		$query = "DROP TABLE IF EXISTS $driver_test_table";
		$this->query($query);
	}
	
	function trans_test_4() {
		$driver_test_table = '_tmp_mysqli_ba_test';
	
		logmes(__METHOD__.' TEST ', '----------------------<<<', DB_DRIVER_LOG);
	
		$query = "DROP TABLE IF EXISTS $driver_test_table";
		$this->query($query);
	
		$query = "CREATE TABLE IF NOT EXISTS $driver_test_table (
		id INT NOT NULL AUTO_INCREMENT,
		a INT NULL,
		b INT NULL,
		PRIMARY KEY (id)
		)";
		$this->query($query);
	
		try {
			
			$this->trans_start(); // @@@@@@@@@
				
			$query = "INSERT INTO $driver_test_table (a, b) VALUES (1, 2)";
			$this->query($query);
				
			$this->trans_begin(); //---------
				
			$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
			$this->query($query);
				
			$this->trans_commit(); //---------
				
			$this->trans_start(); ////////////
				
			$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
			$this->query($query);
				
			$query = "UPDATE yyy SET a = a+1, b = b+1";
			$this->query($query);
				
			$this->trans_complete(); ///////////
				
			$this->trans_complete(); // @@@@@@@@@
	
		}
		catch (Exception $e) {
			$this->trans_rollback();
		}
		
		if ( ! $this->trans_status())
			logmes(__METHOD__.' $this->last_error(): ', $this->last_error(), DB_DRIVER_LOG);
	
		logmes(__METHOD__.' TEST ', '___________________DATA: ', DB_DRIVER_LOG);
		$query = "SELECT * FROM $driver_test_table";
			$rs = $this->query($query);
			if ($rs)
				logmes(__METHOD__.' $driver_test_table data: ', $rs->result(), DB_DRIVER_LOG);
			logmes(__METHOD__.' TEST ', '---------------------->>>', DB_DRIVER_LOG);
	
		$query = "DROP TABLE IF EXISTS $driver_test_table";
			$this->query($query);
	}
	
	function trans_test_5() {
		$driver_test_table = '_tmp_mysqli_ba_test';
	
		logmes(__METHOD__.' TEST ', '----------------------<<<', DB_DRIVER_LOG);
	
		$query = "DROP TABLE IF EXISTS $driver_test_table";
		$this->query($query);
	
		$query = "CREATE TABLE IF NOT EXISTS $driver_test_table (
		id INT NOT NULL AUTO_INCREMENT,
		a INT NULL,
		b INT NULL,
		PRIMARY KEY (id)
		)";
		$this->query($query);
	
		try {
			
		$this->trans_start(); // @@@@@@@@@
	
		$query = "INSERT INTO $driver_test_table (a, b) VALUES (1, 2)";
		$this->query($query);
	
		$this->trans_begin(); //---------
	
		$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
			$this->query($query);
	
		$this->trans_rollback(); //---------
	
		$this->trans_start(); ////////////
	
		$query = "UPDATE $driver_test_table SET a = a+1, b = b+1";
		$this->query($query);
	
		$query = "UPDATE yyy SET a = a+1, b = b+1";
		$this->query($query);
	
			$this->trans_complete(); ///////////
	
				$this->trans_complete(); // @@@@@@@@@
	
		}
		catch (Exception $e) {
		$this->trans_rollback();
		}
	
		if ( ! $this->trans_status())
			logmes(__METHOD__.' $this->last_error(): ', $this->last_error(), DB_DRIVER_LOG);
	
		logmes(__METHOD__.' TEST ', '___________________DATA: ', DB_DRIVER_LOG);
		$query = "SELECT * FROM $driver_test_table";
			$rs = $this->query($query);
				if ($rs)
				logmes(__METHOD__.' $driver_test_table data: ', $rs->result(), DB_DRIVER_LOG);
				logmes(__METHOD__.' TEST ', '---------------------->>>', DB_DRIVER_LOG);
	
		$query = "DROP TABLE IF EXISTS $driver_test_table";
		$this->query($query);
	}
	
}


/* End of file mysqli_driver.php */
/* Location: ./system/database/drivers/mysqli/mysqli_driver.php */