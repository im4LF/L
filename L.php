<?php

define('L_PATH', realpath(dirname(__FILE__)));
$__lr = array();

function L()
{
	global $__lr;
	
	$alias = 'default';
	
	$params = func_get_args();
	return call_user_func_array(array($__lr[$alias], 'log'), $params);
}

/**
 * Logger factory
 * 
 * @return object
 */
function LF($dsn)
{
	$config = @parse_url($dsn);
	if (isset($config['query']))
	{
		parse_str($config['query'], $config['params']);
		unset($config['query']);
	}
	
	$driver_name = 'L'.ucfirst($config['scheme']);
	$class_name = $driver_name.'_Driver';
	
	if (!class_exists($class_name))
	{
		$driver_file = L_PATH.DIRECTORY_SEPARATOR.$driver_name.'.driver.php';	
		if (!file_exists($driver_file))
			throw new LException('Driver ['.$class_name.'] not found ('.$driver_file.')');
		
		require $driver_file;
	}
	
	$object = new $class_name($config); 
	return $object->alias('default');
}

class LAny_Driver
{
	protected $_alias;
	
	function alias($alias = null)
	{
		global $__lr;
		
		if (is_null($alias))
			return $this->_alias;
			
		$this->_alias = $alias;
		$__lr[$alias] = $this;
		return $this;
	}
}

class LException extends Exception
{
	function __construct($message, $code = 0) 
	{
		parent::__construct($message, $code);
    }
}