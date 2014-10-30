<?php

/* Kristov Atlas 2014 */

include_once('CSVReader.php');
include_once('../common/Logger.php');

class TorStatusReader
{
	const TOR_STATUS_URL = 'http://torstatus.blutmagie.de/ip_list_exit.php/Tor_ip_list_EXIT.csv';
	const DEBUG_ON = TRUE;
	
	protected $debugLog;
	
	protected $csvReader;
	
	public function __construct()
	{
		if (TorStatusReader::DEBUG_ON)
		{
			$this->debugLog = new Logger(Logger::LEVEL_DEBUG);
		}
		else
		{
			$this->debugLog = new Logger(Logger::LEVEL_OFF);
		}
		
		$this->csvReader = new CSVReader();
	}
	
	public function getIPList()
	{
		$exitNodeIPArray = $this->csvReader->get(TorStatusReader::TOR_STATUS_URL);
		return $exitNodeIPArray;
	}
}

?>