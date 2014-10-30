<?php

class StringUtil
{

	public static function get_substr_before_colon($str)
	{
		$index = strpos($str, ':');
		if ($index === FALSE)
		{
			return $str;;
		}
		else
		{
			return substr($str, 0, $index);
		}
	}
}

?>