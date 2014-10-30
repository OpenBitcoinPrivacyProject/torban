<?php

/* Kristov Atlas 2014 */

include_once('ThrottledJSONReader.php');
include_once('../common/Logger.php');
include_once('StringUtil.php');

class BitNodesReader
{
	const BITNODE_SNAPSHOT_URL = 'https://getaddr.bitnodes.io/api/v1/snapshots/';
	const DEBUG_ON = FALSE;
	
	protected $debugLog;
	
	protected $jsonReader;
	
	public function __construct()
	{
		if (BitNodesReader::DEBUG_ON)
		{
			$this->debugLog = new Logger(Logger::LEVEL_DEBUG);
		}
		else
		{
			$this->debugLog = new Logger(Logger::LEVEL_OFF);
		}
		
		$this->jsonReader = new ThrottledJSONReader();
	}
	
	private function getLatestSnapshotURL()
	{
		$json = $this->jsonReader->getJSON(BitNodesReader::BITNODE_SNAPSHOT_URL);
		return $json['results'][0]['url'];
	}
	
	public function getIPList()
	{
		$snapshotURL = $this->getLatestSnapshotURL();
		
		$ipAddrList = array();
		
		$json = $this->jsonReader->getJSON($snapshotURL);
		foreach ($json['nodes'] as $ipAddrAndPort => $details)
		{
			$ipAddr = StringUtil::get_substr_before_colon($ipAddrAndPort);
			$this->debugLog->log("Found IP address in BitNodes.io list: $ipAddr\n");
			array_push($ipAddrList, $ipAddr);
		}
		
		return $ipAddrList;
	}
}

?>