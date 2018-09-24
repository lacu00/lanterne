<?php

function trim_str($str)
{
	$str = trim($str);
	if($str == '0000-00-00 00:00:00')
		$str = '';
	elseif(substr($str,0,1)=='=')
		$str = '.'.$str;
		
	if(strstr($str," ") !== false)
	{
		$str_clean = '';
		for($i = 0; $i < strlen($str); $i++)
		{
			if(md5( $str[$i] ) != 'e5ea7fb51ff27a20c3f622df66b9acdc')
				$str_clean .= $str[$i];
		}
		$str = $str_clean;
	}
	
	$str = addslashes( $str );
	$str = str_replace("\\0",'',$str);
		
	return ($str);
}

function dateXlsToPhp($date)
{
	$ret = '';
	if($date > 20000 && $date < 60000)
	{
		$timeZone = 'Etc/GMT';
		$dateSrc = date( 'Y-m-d H:i:s' , ($date - 25569)*24*60*60 );

		$dateTime = new DateTime($dateSrc); 
		$dateTime->setTimeZone(new DateTimeZone($timeZone)); 
		$ret = $dateTime->format('Y-m-d H:i:s'); 
	}
	return $ret;
}

function convert_type($type)
{
	switch($type)
	{
		case 'COMPUTER' :
			$type = 'SERVEUR';
		break;
		case 'NETWORK COMPONENTS' :
			$type = 'RESEAU';
		break;
		case 'STORAGE' :
			$type = 'AUTRE';
		break;
	}
	
	return $type;
}

function convert_subtype($type)
{
	switch($type)
	{
		case 'VIRTUAL SERVERS' :
			$type = 'SERVER';
		break;
		case '(LOAD BALANCER) LB' :
			$type = 'LOAD BALANCER';
		break;
	}
	
	return $type;
}

function getIndex($x,$y)
{
	if($y == -1)
	{
		if($x < 26)
			$ret = chr( 65+$x );
		elseif($x < 52)
			$ret = 'A'.chr( 65 + ($x-26) );
		else
			$ret = 'B'.chr( 65 + ($x-52) );
	}
	else
	{
		if($x < 26)
			$ret = chr( 65+$x ).$y;
		elseif($x < 52)
			$ret = 'A'.chr( 65 + ($x-26) ).$y;
		else
			$ret = 'B'.chr( 65 + ($x-52) ).$y;
	}
	
	return $ret;
}

?>