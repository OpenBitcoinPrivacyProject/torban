<?php

/* Kristov Atlas 2014 */

include_once('BitNodesReader.php');
include_once('TorStatusReader.php');
include_once('../common/Logger.php');

class IntersectionListFetcher
{
	
	protected $debugLog;
	const DEBUG_ON = TRUE;
	
	public function __construct()
	{
		if (IntersectionListFetcher::DEBUG_ON)
		{
			$this->debugLog = new Logger(Logger::LEVEL_DEBUG);
		}
		else
		{
			$this->debugLog = new Logger(Logger::LEVEL_OFF);
		}
	}
	
	/* Returns array list of IP addresses currently connected to the Bitcoin network (according to bitnodes.io) that are also Tor exit nodes.*/
	public function getIntersection()
	{
		$bnReader = new BitNodesReader();
		$torReader = new TorStatusReader();

		$bnResults = $bnReader->getIPList();
		$this->debugLog->log("Retreived " . sizeof($bnResults) . " addresses from BitNodes.io.\n");

		$torResults = $torReader->getIPList();
		$this->debugLog->log("Retreived " . sizeof($torResults) . " addresses from Tor\n");	

		$matches = array();
		foreach ($bnResults as $bnIPAddr)
		{
			foreach ($torResults as $torIPAddr)
			{
				if (trim($bnIPAddr) == trim($torIPAddr))
				{
					//use hash-array to eliminate duplicate entries
					$matches{$bnIPAddr} = 1;
				}
			}
		}
		
		return $matches;
	}
}

?>