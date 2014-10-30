<?php

/* Kristov Atlas 2014 */

class ThrottledJSONReader
{
	
	const SLEUTH_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';
	const NUM_SEC_DELAY_BEFORE_REQUEST = 0;
	
	const MAX_NUM_REATTEMPTS = 20;
	
	protected $debugLog;
	const DEBUG_ON = TRUE;
	
	public function __construct()
	{
		if (ThrottledJSONReader::DEBUG_ON)
		{
			$this->debugLog = new Logger(Logger::LEVEL_DEBUG);
		}
		else
		{
			$this->debugLog = new Logger(Logger::LEVEL_OFF);
		}
	}
	
	public function getJSON($url)
	{
		#keep trying to decode page until it works or we die()
		for ($i = 0; $i < ThrottledJSONReader::MAX_NUM_REATTEMPTS; $i++)
		{
			$jsonString = $this->fetchPage($url);
			$json = json_decode($jsonString, TRUE);
			if (!is_null($json))
			{
				#decoded successfully
				return $json;
			}
		}
		
		die("Could not decode JSON for acquired page in ThrottledJSONReader.php after " . ThrottledJSONReader::MAX_NUM_REATTEMPTS . " attempts.\n");
	}
	
	private function fetchPage($url)
	{
		#Throttle
		$this->debugLog->log("Sleeping for " . ThrottledJSONReader::NUM_SEC_DELAY_BEFORE_REQUEST . " seconds.\n");
		sleep(ThrottledJSONReader::NUM_SEC_DELAY_BEFORE_REQUEST);
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, ThrottledJSONReader::SLEUTH_AGENT);
		$curl_scraped_page = curl_exec($ch);
		
		$this->debugLog->log("Curl scraped page is " . strlen($curl_scraped_page) . " bytes long.\n");
		
		if ($curl_scraped_page === FALSE)
		{
			return NULL;
		}
		else
		{
			return $curl_scraped_page;
		}
	}
}

?>