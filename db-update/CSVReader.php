<?php

include_once('simple_html_dom.php');
include_once('../common/Logger.php');

class CSVReader
{
	const SLEUTH_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';
	
	protected $debugLog;
	const DEBUG_ON = TRUE;
	
	public function __construct()
	{
		if (CSVReader::DEBUG_ON)
		{
			$this->debugLog = new Logger(Logger::LEVEL_DEBUG);
		}
		else
		{
			$this->debugLog = new Logger(Logger::LEVEL_OFF);
		}
	}
	
	public function get($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, CSVReader::SLEUTH_AGENT);
		$curl_scraped_page = curl_exec($ch);
		
		$this->debugLog->log("Curl scraped page is " . strlen($curl_scraped_page) . " bytes long.\n");
		
		if ($curl_scraped_page === FALSE)
		{
			die("Could not read URL '$url' " . curl_error($ch) . "\n");
		}
		else
		{
			$html_source = $curl_scraped_page;
			$html_dom = new simple_html_dom();
			$html_dom->load($html_source);
			
			$ipArray = explode(" ", $html_dom);
			return $ipArray;
		}
	}
}

?>