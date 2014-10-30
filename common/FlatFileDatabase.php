<?php

/* Kristov Atlas 2014 */

//Father, forgive them, for they do not know what they are doing.

class FlatFileDatabase
{
	public $filename;
	protected $dbcontents;
	
	public function __construct($filename)
	{
		$this->filename = $filename;
		
		if (!is_readable($filename))
		{
			die ("Could not read from specified database file '$filename'\n");
		}
		
		$this->dbcontents = unserialize(file_get_contents($filename));
	}
	
	public function set($key, $value)
	{
		$this->dbcontents{$key} = $value;
	}
	
	public function get($key)
	{
		return $this->dbcontents{$key};
	}
	
	private function writeToFile()
	{
		return file_put_contents($this->filename, serialize($this->dbcontents));
	}
	
	public function commitToFile()
	{
		return $this->writeToFile();
	}
}
	
?>