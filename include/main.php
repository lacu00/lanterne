<?php

ini_set('default_charset','utf-8');

error_reporting("E_ALL & ~E_DEPRECIATED");
//ini_set('max_execution_time',0);
//ini_set('memory_limit', '1024M');
include('../db.php');

mysql_connect($DB_HOST,$DB_LOGIN,$DB_MDP);
mysql_select_db($DB_NAME);

if(isset($_POST['do']))
{	
	switch($_POST['do'])
	{
		case 'trace_log' :		
			trace_log($_POST['type'] , $_POST['code'] , $_POST['details']);
		break;
		case 'ajax_search' :
			$limit_search	= 10;
			$SQL_limite		= 100;
			$i = 0;

			$_POST['val'] = utf8_decode($_POST['val']);
			
			$sql = 'select 
						'.$_POST['type'].'_name as name
					from
						'.$_POST['type'].'
					where
						'.$_POST['type'].'_name LIKE "%'.$_POST['val'].'%"
						and '.$_POST['type'].'_status = 0
					order by 
						'.$_POST['type'].'_name
					LIMIT '.$SQL_limite;
			$result = mysql_query($sql);
			echo mysql_error();
			$res = "";
			$nbRes = 0;

			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$res .= '<div onclick="select_ajax_search_line(\''.$i.'\');" class="ajax_search" id="ajax_search_line_'.$i.'" onmouseover="survole_ajax_search_result(\''.$i.'\')" onmouseout="quitte_ajax_search_result(\''.$i.'\')">';
					$res .= apply_dictionary($row['name']);
					$res .= '</div>';
					$res .= '<input type="hidden" value="'.$row['name'].'" id="ajax_search_line_'.$i.'_name"></input>';
					
					$nbRes++;
					$i++;
					
					if($nbRes == $limit_search)
						break;
				}
			}
			echo utf8_encode($res);
			mysql_free_result($result);
		break;
	}
}

function apply_dictionary($str_in)
{
	$str_out = str_replace(array('oe','OE'),array('&oelig;','&OElig;'),$str_in);
	
	return $str_out;
}

function trim_str($str)
{
	$str = trim($str);
	
	if($str == '0000-00-00 00:00:00')
	{	
		$str = '';	
	}

	elseif(substr($str,0,1)=='=')
		$str = '.'.$str;
		
	$str = str_replace(array('"','%u200B','’','%u2019'),array("''",'',"'","'"),$str);
		
	return addslashes( $str );
}

function get_arbo( $type , $include_desactivated = true )
{
	$ARBO = array(
					'parent' 	=> array(),
					'child'		=> array()
				);	
	
	$sql_des = '';
	if(!$include_desactivated)
		$sql_des = ' and arborescence_status != 2 ';
	
	$result = mysql_query('select *,
								if(arborescence_position = 0,987654321,arborescence_position) as arborescence_position
							from arborescence 
							where 
								arborescence_status != 1
								and arborescence_type = "'.$type.'"
								'.$sql_des.'
							order by
								arborescence_parent,
								arborescence_position,
								arborescence_name');
	echo mysql_error();
	if(mysql_num_rows($result)>0)
	{
		while($row = mysql_fetch_assoc($result))
		{
			$row['arborescence_position'] = ($row['arborescence_position'] == "987654321")?0:$row['arborescence_position'];
			$arbo = array(
							'id'			=>	$row['arborescence_id'],
							'parent'		=>	$row['arborescence_parent'],
							'status'		=>	$row['arborescence_status'],
							'name'			=>	$row['arborescence_name'],
							'code'			=>	$row['arborescence_code'],
							'position'		=>	$row['arborescence_position'],
							'description'	=>	$row['arborescence_description'],
							'backcolor'		=>	$row['arborescence_backcolor'],
							'fontcolor'		=>	$row['arborescence_fontcolor'],
							'date_maj'		=>	$row['arborescence_date_maj'],
							'image'			=>	$row['arborescence_image']
						);
						
			foreach($arbo as $key => $val)
				$arbo[$key] = utf8_encode($val);
			
			if(!isset($ARBO['parent'][ $arbo['parent'] ]))
				$ARBO['parent'][ $arbo['parent'] ] = array();
			$ARBO['parent'][ $arbo['parent'] ][ $arbo['id'] ] = $arbo;
			$ARBO['child'][ $arbo['id'] ] = $arbo;
		}
	}
	mysql_free_result($result);
	
	return $ARBO;
}

function get_profondeur_max( $ARBO_child )
{
	$max = 0;
	
	foreach($ARBO_child as $id => $arbo)
	{
		$parent = $arbo['parent'];
		$temp_max = 0;
		while(isset( $ARBO_child[ $parent ] ))
		{
			$temp_max ++;
			if($temp_max > $max)
				$max = $temp_max;
			$parent = $ARBO_child[ $parent ]['parent'];
		}
	}	

	return $max;
}

function get_child_list( $arbo, $parent, $list )
{
	if(isset($arbo['parent'][ $parent ]))
	{
		foreach($arbo['parent'][ $parent ] as $id => $child)
		{
			if(!isset($list['distinct'][ $id ]))
			{
				$list[ $id ] = $child['name'];
				
				if(isset($arbo['parent'][ $id ]))
					$list = get_child_list( $arbo, $id, $list );
			}
		}
	}
	
	return $list;
}

function get_parent_list( $arbo, $child, $list )
{
	if(isset($arbo['child'][ $child ]) && $arbo['child'][ $child ]['parent'] != "0")
	{
		$list[ $arbo['child'][ $child ]['parent'] ] = 1;
		
		$list = get_child_list( $arbo, $arbo['child'][ $child ]['parent'], $list );
	}
	
	return $list;
}

function get_list_elem($type)
{
	$elem = array();
	switch($type)
	{
		case 'logo' :
		case 'coupon' :
			$path_image = '';
			switch($type)
			{
				case 'logo' : 	$path_image = '../ftp_images/logos/';	break;
				case 'coupon' : $path_image = '../ftp_images/coupons/';	break;
			}
		
			$result = mysql_query('select * from '.$type.' where '.$type.'_status = 0');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$image = '../images/no_photo.png';
					if($row[$type.'_image'] != '' && file_exists($path_image.$row[$type.'_image']))
						$image = $path_image.$row[$type.'_image'];
				
					$elem[ $row[$type.'_id'] ] = array(
															'name'			=>	$row[$type.'_name'],
															'code'			=>	$row[$type.'_code'],
															'image'			=>	$image,
															'description'	=>	$row[$type.'_description'],
															'date_maj'		=>	$row[$type.'_date_maj']
														);
				}
			}
			mysql_free_result($result);
		break;
	}
	
	return $elem;
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

function trace_log( $type, $code, $details )
{
	mysql_query('insert into log
					(
						log_type,
						log_details,
						log_item_code,
						log_date,
						log_ip
					)
					values
					(
						"'.$type.'",
						"'.$details.'",
						"'.$code.'",
						NOW(),
						"'.$_SERVER['REMOTE_ADDR'].'"
					)');
	echo mysql_error();
}

?>
