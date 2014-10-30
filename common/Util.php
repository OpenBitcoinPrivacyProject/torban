<?php

/* Kristov Atlas */

class Util
{
	public static function format_time($time)
	{
		date_default_timezone_set('UTC');
		return date(TorBanDB::DATE_FORMAT, $time) . ' UTC';
	}	
	
	public static function format_minutes($time)
	{
		date_default_timezone_set('UTC');
		return date('i', $time);
	}
}	
	
	
?>