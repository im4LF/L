<?php

class LFile_Driver extends LAny_Driver
{
	protected $_path;
	protected $_dir;
	protected $_file;
	protected $_date_format = '[d/M/Y H:i:s]';
	protected $_initialized = false; 
	
	function __construct($config)
	{
		$this->_path = $config['host'].$config['path'];
	}
	
	protected function _segmentKey($key)
	{
		$this->_dir = $this->_path.'/'.substr($key, 0, 2).'/'.substr($key, 2, 2);
		$this->_file = $this->_dir.'/'.substr($key, 4).'.log';
	}
	
	function run($key)
	{
		$key = md5($key);
		$this->_segmentKey($key);
		
		return $key;
	}
	
	function read($key)
	{
		$key = md5($key);
		$this->_segmentKey($key);
		
		if (!file_exists($this->_file))
			return false;
		
		return file_get_contents($this->_file);
	}
	
	function log()
	{
		if (!$this->_initialized)
		{
			if (!file_exists($this->_dir))
			{
				$old = umask(0); 
				mkdir($this->_dir, 0777, true);
				umask($old);
			}
			
			@unlink($this->_file);
			touch($this->_file);
			chmod($this->_file, 0666);
			
			$this->_initialized = true;
		}
		
		$args = func_get_args();
		$nargs = func_num_args();
		$trace = debug_backtrace();
		$caller = $trace[2];
	
		$buf = date($this->_date_format) . ' ' . $caller['file'] . ':' . $caller['line']."\n";
		
		$f = fopen($this->_file, 'a');
		
		for ($i=0; $i<$nargs; $i++)
			$buf .= trim(print_r($args[$i], true))."\n";
			
		fputs($f, $buf);
		
		fclose($f);
	}
}