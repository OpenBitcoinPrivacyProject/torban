<?php

/* Kristov Atlas 2014 */

include_once('../common/TorBanDB.php');
include_once('../common/Util.php'); 

class IntersectionStatusHTMLWriter
{
	protected $db;
	
	const DEBUG_ON = TRUE;
	
	public function __construct()
	{
		if (IntersectionStatusHTMLWriter::DEBUG_ON)
		{
			$this->debugLog = new Logger(Logger::LEVEL_DEBUG);
		}
		else
		{
			$this->debugLog = new Logger(Logger::LEVEL_OFF);
		}
		
		$this->db = new TorBanDB();
	}
	
	/*
		In HTML format, writes a table listing all IP addresses ever seen
		as both connected Bitcoin nodes and Tor Exit Nodes.
	*/
	public function write()
	{
		$this->db->open();
		
		IntersectionStatusHTMLWriter::print_header();
		
		$ipAddressesSeen = $this->db->getListOfAllIPAddrs();
		foreach ($ipAddressesSeen as $ipAddress)
		{
			$data = $this->db->getIPAddrData($ipAddress);
			$firstSeen = Util::format_time($data[TorBanDB::IP_ADDRESS_FIRST_SEEN_KEY]);
			$lastSeen = Util::format_time($data[TorBanDB::IP_ADDRESS_LAST_SEEN_KEY]);
			$lastSeenMinutesAgo = Util::format_minutes(time() - 
				$data[TorBanDB::IP_ADDRESS_LAST_SEEN_KEY]);
			$numRoundsOmitted = $data[TorBanDB::IP_ADDRESS_NUM_ROUNDS_MISSING_KEY];
			
			print(
				"\t\t\t<tr>\n" .
				"\t\t\t\t<td>$ipAddress</td>\n" .
				"\t\t\t\t<td>$firstSeen</td>\n" .
				"\t\t\t\t<td>$lastSeen ($lastSeenMinutesAgo min ago)</td>\n" .	
				"\t\t\t\t<td>$numRoundsOmitted</td>\n" .
				"\t\t\t</tr>\n" .
				"");
		}
		
		IntersectionStatusHTMLWriter::print_footer();
		
		$this->db->close();
	}
	
	private static function print_header()
	{
		print(
			"<html>\n" .
			"\t<body>\n" .
			"\t\t<table border=\"1\">\n" .
			"\t\t\t<tr>\n" . 
			"\t\t\t\t<td><u>IP Address</u></td>\n" .
			"\t\t\t\t<td><u>First Seen</u></td>\n" .
			"\t\t\t\t<td><u>Last Seen</u></td>\n" .
			"\t\t\t\t<td><u>Total # Rounds Omitted</u></td>\n" .
			"\t\t\t</tr>\n" .
			"");		
	}
	
	private static function print_footer()
	{
		print(
		"\t\t</table>\n" .
		"\t</body>\n" .
		"</html>" .
		"");
	}
}

?>