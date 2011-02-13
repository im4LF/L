<?php

class LMysql_Driver extends LAny_Driver
{
	protected $_config;
	protected $_action;
	protected $_link;
	protected $_key;
	protected $_initialized = false; 
	
	function __construct($config)
	{
		$this->_config = $config;
		$this->_config['path'] = substr($this->_config['path'], 1);
	}
	
	protected function _throwException()
	{
		if (is_resource($this->_link))
		{
			$message = mysql_error($this->_link);
			$code = mysql_errno($this->_link);
		}
		else
		{
			$message = mysql_error();
			$code = mysql_errno();
		}
		
		throw new LException('Action: '.$this->_action."\n".$message, $code);
	}
	
	function run($key)
	{
		$this->_key = $key;
		
		return $key;
	}
	
	function read($key)
	{
	}
	
	function log()
	{
		if (!$this->_initialized)
		{
			$this->_action = 'connect';
			if (false === ($this->_link = @mysql_connect($this->_config['host'], $this->_config['user'], $this->_config['pass'])))
				$this->_throwException();
			
			$this->_action = 'select database';
			if (false === @mysql_select_db($this->_config['path'], $this->_link))
				$this->_throwException();
				
			if (isset($this->_config['params']['encoding'])) 
			{
				$this->_action = 'set charset';
				$success = false;
				if (function_exists('mysql_set_charset')) 
					$success = @mysql_set_charset($this->_config['params']['encoding'], $this->_link);
				else
					$success = @mysql_query('SET NAMES "'.$this->_config['params']['encoding'].'"', $this->_link);
				
				if (!$success)
					$this->_throwException();
			}
			
			$sql = 'SHOW TABLES FROM `'.$this->_config['path'].'` LIKE "'.$this->_config['params']['table'].'"';
			$this->_action = 'detect logs table';
			if (false === ($res = @mysql_query($sql, $this->_link)))	
				$this->_throwException();
				
			if (mysql_num_rows($res) == 0)
			{
				$sql = 'CREATE TABLE `'.$this->_config['params']['table'].'` (
					`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`key` TEXT NOT NULL ,
					`date` DATETIME NOT NULL ,
					`file` TEXT NOT NULL ,
					`line` INT NOT NULL ,
					`message` LONGTEXT NOT NULL
				) ENGINE = MYISAM'.(isset($this->_config['params']['encoding']) ? ' CHARACTER SET '.$this->_config['params']['encoding'] : '');
				$this->_action = 'create logs table';
				if (false === ($res = @mysql_query($sql, $this->_link)))
					$this->_throwException();
			}
			
			$this->_initialized = true;
		}
		
		$args = func_get_args();
		$nargs = func_num_args();
		$trace = debug_backtrace();
		$caller = $trace[2];
		
		$buf = '';
		for ($i=0; $i<$nargs; $i++)
			$buf .= trim(print_r($args[$i], true))."\n";
		
		$sql = 'INSERT INTO `'.$this->_config['params']['table'].'` (id, `key`, `date`, `file`, `line`, `message`) VALUES(
			NULL, 
			"'.mysql_real_escape_string($this->_key).'", 
			NOW(), 
			"'.mysql_real_escape_string($caller['file']).'", 
			'.$caller['line'].',
			"'.mysql_real_escape_string($buf).'" 
		)';
		
		$this->_action = 'execute query: ['.$sql.']';
		if (false === ($res = @mysql_query($sql, $this->_link)))	
			$this->_throwException();
	}
}