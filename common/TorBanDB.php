<?php

/* Kristov Atlas 2014 */

include_once('FlatFileDatabase.php');
include_once('Logger.php');
include_once('Util.php');
	
class TorBanDB
{
	const DEFAULT_FILENAME = '../common/torbandb.txt';
	
	const DEBUG_ON = TRUE;
	
	/* array-hash keys to lookup values in the deserialized database */
	const LAST_ENTRY_ID_KEY = 'lastEntryID';
	const AVERAGE_NUM_IPS_KEY = 'avgNumIPsInIntersection';
	const INTERSECTION_LOOKUP_KEY = 'intersections';
	const IP_ADDRESS_STATS_KEY = 'ipaddrstats';
	const IP_ADDDRESS_MOST_COMMON_PORT_KEY = 'mostcommonport'; //TODO: Not yet implemented
	const IP_ADDRESS_FIRST_SEEN_KEY = 'firstseen';
	const IP_ADDRESS_LAST_SEEN_KEY = 'lastseen';
	const IP_ADDRESS_NUM_ROUNDS_MISSING_KEY= 'numroundsmissing';
	
	const VALUE_NOT_YET_SET = -1;
	const DATE_FORMAT = 'Y/m/d H:i:s';
	
	protected $fileDB;
	
	protected $open;
	
	/* Contents of the database that are extracted from serialized storage */
	public $currentEntryID;
	public $avgNumIPsInIntersection;
	public $intersectionList; //intersectionList[lookup_id] = array-list 
	public $ipAddressStats; //ipAddressStats[ip_address] => most_common_port, first_seen, last_seen, num_rounds_missing
	
	public function __construct()
	{
		if (TorBanDB::DEBUG_ON)
		{
			$this->debugLog = new Logger(Logger::LEVEL_DEBUG);
		}
		else
		{
			$this->debugLog = new Logger(Logger::LEVEL_OFF);
		}
		
		$this->open = FALSE; //Database is not open until open() is called.	
	}
	
	//Initializes the database, deserializing data from storage and setting class attributes
	public function open()
	{
		$this->fileDB = new FlatFileDatabase(TorBanDB::DEFAULT_FILENAME);
		
		/* Fill object from database storage */
		$this->currentEntryID = $this->fileDB->get(TorBanDB::LAST_ENTRY_ID_KEY); //the last ID used
		if (!isset($this->currentEntryID))
		{
			$this->debugLog->log("Couldn't find " . TorBanDB::LAST_ENTRY_ID_KEY . " in the database. " .
				"Resetting value to 0.\n");
			$this->currentEntryID = 0;
		}
		$this->avgNumIPsInIntersection = $this->fileDB->get(TorBanDB::AVERAGE_NUM_IPS_KEY);
		if (!isset($this->avgNumIPsInIntersection))
		{
			$this->debugLog->log("Couldn't find " . TorBanDB::AVERAGE_NUM_IPS_KEY . " in the database. " .
				"Resetting value to " . TorBanDB::VALUE_NOT_YET_SET . ".\n");
			$this->avgNumIPsInIntersection = TorBanDB::VALUE_NOT_YET_SET;
		}
		$this->intersectionList = $this->fileDB->get(TorBanDB::INTERSECTION_LOOKUP_KEY); // all intersection data
		if (!is_array($this->intersectionList))
		{
			$this->debugLog->log("No previous data for IP address intersections found. Starting with blank slate.\n");
			$this->intersectionList = array();
		}
		$this->ipAddressStats = $this->fileDB->get(TorBanDB::IP_ADDRESS_STATS_KEY);
		if (!is_array($this->ipAddressStats))
		{
			$this->debugLog->log("Couldn't find any previous data for IP addresses. Starting with blank slate.\n");
			$this->ipAddressStats = array();
		}
		
		$this->open = TRUE;
		
		/*
		print "DEBUG: ";
		var_dump($this);
		print "\n";
		*/
	}
	
	//Closes the database, writing all changes to disk.
	//Returns: Result of call to file_puts_contents()
	public function close()
	{
		$this->open = FALSE;
		return $this->fileDB->commitToFile();
	}
	
	public function getAvgNumIPsInIntersection()
	{
		if (!$this->open) { die("Database has not been opened yet.\n"); }
		
		return $this->avgNumIPsInIntersection;
	}
	
	public function getIntersectionListForID($id)
	{
		if (!$this->open) { die("Database has not been opened yet.\n"); }
		
		$intersectionList = $this->intersectionList[$id];
		
		$this->debugLog->log("Retrieved " . sizeof($intersectionList) . " intersections for lookup id $id.\n");
		
		return $intersectionList;
	}
	
	//Side-effect: Increments currentEntryID
	//Does not write to disk until close() is called.
	public function storeNextIntersectionList($newIntersectionList)
	{
		if (!$this->open) { die("Database has not been opened yet.\n"); }
		
		//Increment current ID
		$this->currentEntryID++;
		
		//Update avgNumIPsInIntersection
		$this->updateAvgNumIPsInIntersection($newIntersectionList);
		
		//Update ipAddressStats
		$this->updateIPAddressStats($newIntersectionList);
		
		//Store new list of intersecting IP addresses
		$this->intersectionList{$this->currentEntryID} = $newIntersectionList;
			
		//update $this->fileDB with new class member data
		$this->setAll();
	}
	
	private function updateIPAddressStats($newIntersectionList)
	{
		$now = time();
		//Update stats for all IP Addresses seen during this round
		foreach ($newIntersectionList as $ipAddress => $val)
		{
			if (!is_array($this->ipAddressStats[$ipAddress]))
			{
				//IP Address never seen before, must be new around here!
				$this->ipAddressStats[$ipAddress] = array();
				$this->ipAddressStats[$ipAddress][TorBanDB::IP_ADDRESS_FIRST_SEEN_KEY] = $now;
				$this->ipAddressStats[$ipAddress][TorBanDB::IP_ADDRESS_LAST_SEEN_KEY] = $now;
				$this->ipAddressStats[$ipAddress][TorBanDB::IP_ADDRESS_NUM_ROUNDS_MISSING_KEY] = 0;
			}
			else
			{
				//Update stats for previously seen IP Address
				$this->ipAddressStats[$ipAddress][TorBanDB::IP_ADDRESS_LAST_SEEN_KEY] = $now;
			}
		}
		
		//Look for previously seen IP Addresses that may have been dropped during this round
		foreach ($this->ipAddressStats as $oldIPAddress => $val)
		{
			$foundThisOldIPAddress = FALSE;
			foreach ($newIntersectionList as $newIPAddress => $val)
			{
				if ($oldIPAddress == $newIPAddress)
				{
					$this->debugLog->log("Previously seen IP address $oldIPAddress is still live.\n");
					$foundThisOldIPAddress = TRUE;
					break;
				}
			}
			if (!$foundThisOldIPAddress)
			{
				//This old IP address once seen is not seen this round. Update stats
				$this->ipAddressStats[$oldIPAddress][TorBanDB::IP_ADDRESS_NUM_ROUNDS_MISSING_KEY]++;
				$this->debugLog->log("Previously seen IP address $oldIPAddress is missing from this " .
					"round. Now missing " . 
					$this->ipAddressStats[$oldIPAddress][TorBanDB::IP_ADDRESS_NUM_ROUNDS_MISSING_KEY] .
					" times.\n");
			}
		}
	}
	
	private function updateAvgNumIPsInIntersection($newIntersectionList)
	{
		//Update avgNumIPsInIntersection
		if ($this->avgNumIPsInIntersection == TorBanDB::VALUE_NOT_YET_SET)
		{
			//Average hasn't been previously set (this is the first time)
			$this->avgNumIPsInIntersection = sizeof($newIntersectionList);
		}
		else
		{
			$numPreviousIntersectionsStored = sizeof($this->intersectionList);
			
			$newAvg = (($this->avgNumIPsInIntersection * $numPreviousIntersectionsStored)
				+ sizeof($newIntersectionList)) * 1.0 / ($numPreviousIntersectionsStored + 1);
			$this->avgNumIPsInIntersection = $newAvg;
		}
	}
	
	//Calls db->set() on all member variables for this class
	private function setAll()
	{
		$this->fileDB->set(TorBanDB::LAST_ENTRY_ID_KEY, $this->currentEntryID);
		$this->fileDB->set(TorBanDB::INTERSECTION_LOOKUP_KEY, $this->intersectionList);
		$this->fileDB->set(TorBanDB::AVERAGE_NUM_IPS_KEY, $this->avgNumIPsInIntersection);
		$this->fileDB->set(TorBanDB::IP_ADDRESS_STATS_KEY,  $this->ipAddressStats);
	}
	
	//Debugging function
	public function printAllIntersections()
	{
		if (!$this->open) { die("Database has not been opened yet.\n"); }
		
		foreach ($this->intersectionList as $id => $intersectionList)
		{
			print "$id:\n";
			foreach ($intersectionList as $ipAddr => $val)
			{
				print "\t$ipAddr\n";
			}
		}
	}
	
	//Debugging function
	public function printAllIPAddrStats()
	{
		if (!$this->open) { die("Database has not been opened yet.\n"); }
		
		foreach ($this->ipAddressStats as $oldIPAddress => $val)
		{
			print "$oldIPAddress:\n";
			print "\tFirst Seen: " .
				Util::format_time($this->ipAddressStats[$oldIPAddress][TorBanDB::IP_ADDRESS_FIRST_SEEN_KEY]) .
				"\n";
			print "\tLast Seen: " . 
				Util::format_time($this->ipAddressStats[$oldIPAddress][TorBanDB::IP_ADDRESS_LAST_SEEN_KEY]) .
				"\n";
			print "\tMissing for " . 
				$this->ipAddressStats[$oldIPAddress][TorBanDB::IP_ADDRESS_NUM_ROUNDS_MISSING_KEY] . 
				" rounds total.\n";
		}
	}
	
	public function getListOfAllIPAddrs()
	{
		$list = array();
		foreach ($this->ipAddressStats as $oldIPAddress => $arr)
		{
			array_push($list, $oldIPAddress);
		}
		return $list;
	}
	
	public function getIPAddrData($ipAddress)
	{
		return $this->ipAddressStats[$ipAddress];
	}
	
	/* 
		Looking back through the last N entries stored in the database, which IP addresses
		  were present at one point, but went missing in later entries? e.g.
		entry 1:
			0.0.0.0
			192.168.1.1
	
		entry 2:
			0.0.0.0
	
		entry 3:
			1.1.1.1
			192.168.1.1
	
		returns: 0.0.0.0, 192.168.1.1
	
		Assumption: The entries will be contiguously indexed -- no gaps.
	*/
	/* never finished this function...
	public function listNumIPsDropped($numberOfRecentEntriesToSearch)
	{
		if (!$this->open) { die("Database has not been opened yet.\n"); }
		if ($numberOfRecentEntriesToSearch <= 0) { die("Invalid number of entries to search for dropped IPs.\n"); }
		
		//Determine the starting place to search
		$firstEntryIDToSearch = $this->currentEntryID - $numberOfRecentEntriesToSearch;
		if (!isset($this->intersectionList[$firstEntryIDToSearch]))
		{
			//Can't find the starting place; maybe there aren't as many entries as we're attempting
			// to search. Simply start from the beginning and do as many as we can.
			reset($this->intersectionList);
			$firstEntryKeyToSearch = key($this->intersectionList);
		}
		
		$seenIPAddrs = array(); //non-duplicate list
		$missingIPAddrs = array(); //non-duplicate list
		
		$entryIDToSearch = $firstEntryIDToSearch;
		$numEntriesToSearchRemaining = $numberOfRecentEntriesToSearch;
		while ($numEntriesToSearchRemaining > 0)
		{
			$numEntriesToSearchRemaining--;
			
			if (isset($this->intersectionList[$entryIDToSearch]))
			{
				//Look through this list of IP addresses to make sure every previously seen IP
				// address is accounted for
				foreach($this->intersectionList[$entryIDToSearch] as $ipAddress => $val)
				{
					//Add to list of IP addresses we've seen
					$seenIPAddrs{$ipAddress} = 1;
				}
			}
			else
			{
				#ran out of entries to search. Maybe they are not contiguous for some reason? :(
				break;
			}
			
			$entryIDtoSearch++; //increment to searching next ID for next iteration of while-loop
		}
	}
	*/
}
?>