<?php

include('../include/main.php');

$PATH_IMAGES .= 'coupons/';

if(isset($_GET['do']))
{
	switch($_GET['do'])
	{		
		case 'get_list_image' :
			$dir = $PATH_IMAGES;
			
			$IMG = array();
			$result = mysql_query('select coupon_image
									from
										coupon
									where
										coupon_status = "0"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$IMG[ $row['coupon_image'] ] = 1;
				}
			}
			mysql_free_result($result);

			$images = array();
			$d = dir($dir);
			while($name = $d->read())
			{
				if(!preg_match('/\.(jpg|gif|png)$/', $name)) continue;
				$size = filesize($dir.$name);
				$lastmod = filemtime($dir.$name)*1000;
				
				$name = utf8_encode($name);
				
				if(!isset($IMG[ $name ]))
				{
					$images[] = array(
										'name' => strtolower($name),
										'filename'	=>	$name,
										'size' => $size,
										'lastmod' => $lastmod, 
										'url' => $dir.$name,
										'thumb_url' => $dir.$name
									);
				}
			}
			$d->close();
			$o = array(
						'images'=>$images
					);
			echo json_encode($o);
		break;
		default :
			
		break;
	}
	exit();
}

if(isset($_POST['do']))
{
	switch($_POST['do'])
	{		
		case 'get_liste_coupon' :
			$res = '';
			
			$nb_elem = $BOUTIQUE_NB_ELEM_COL * $BOUTIQUE_NB_ELEM_LIG;
			$page = $_POST['page'];
			
			$_POST['search'] = utf8_decode($_POST['search']);
		
			$sql_search = '';
			if(isset($_POST['search']) && $_POST['search']!='')
			{
				$sql_search = ' and (
										coupon_name LIKE "%'.$_POST['search'].'%"
										or
										coupon_code LIKE "%'.$_POST['search'].'%"
									) ';
			}
		
			$COUPON = array();
			$sql = 'select * from coupon
						where
							coupon_status != "1"
							'.$sql_search.'
						order by
							coupon_name';
			$result = mysql_query($sql);
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$COUPON[ $row['coupon_id'] ] = array(
															'name'			=>	$row['coupon_name'],
															'code'			=>	$row['coupon_code'],
															'status'		=>	$row['coupon_status'],
															'image'			=>	$row['coupon_image'],
															'url'			=>	$row['coupon_url'],
															'description'	=>	$row['coupon_description']
														);
				}
			}
			
			if(md5($sql) != $_POST['sql'])
				$page = 0;
			
			$res .= '<input type="hidden" id="toolbar_info_current_sql" value="'.md5($sql).'"></input>';
			$res .= '<input type="hidden" id="toolbar_info_nb_result" value="'.mysql_num_rows($result).' coupon'.((mysql_num_rows($result)>1)?'s':'').'"></input>';
			
			$max_page = 1;
			if(ceil(mysql_num_rows($result) / $nb_elem) > 1)
				$max_page = ceil(mysql_num_rows($result) / $nb_elem);
			if($page >= $max_page)
				$page = $max_page - 1;
				
			$res .= '<input type="hidden" id="toolbar_info_current_page" value="'.$page.'"></input>';
			$res .= '<input type="hidden" id="toolbar_info_max_page" value="'.$max_page.'"';
			
			mysql_free_result($result);
			
			$count = 0;
			$run_count = 0;
			$coupon_count = 0;
			$res .= '<div>';
			$res .= '<table class="table_produits">';
			foreach($COUPON as $id => $coupon)
			{
				if($count >= $page*$nb_elem && $count < ($page*$nb_elem + $nb_elem))
				{
					if($coupon_count == 0)
					{
						$res .= '<tr>';
					}
					else
					{
						if($run_count == $BOUTIQUE_NB_ELEM_COL)
						{
							$res .= '</tr><tr>';
							$run_count = 0;
						}
					}
						
					$etat_coupon = '';
					if($coupon['status']=="2")
						$etat_coupon = ' style="color:red;" title="Coupon d&eacute;sactiv&eacute;" ';
						
					$url_info = '';
					if($coupon['url'] != '')
					{
						if(substr(strtolower($coupon['url']),0,4)!='http')
							$coupon['url'] = 'http://'.$coupon['url'];
						$url_info = ' style="color:blue;text-decoration:underline;cursor:pointer;" title="'.$coupon['url'].'" onclick="window.open(\''.$coupon['url'].'\');" ';
					}
					
					$image = '../images/no_photo.png';
					if($coupon['image'] != '' && file_exists($PATH_IMAGES.$coupon['image']))
						$image = $PATH_IMAGES.$coupon['image'];
					
					$res .= '<td>';
					$res .= '<div align="center"><img class="product_img" src="'.$image.'"></img></div>';
					$res .= '<div><table class="table_button" '.$etat_coupon.'><tr>';
					if($coupon['status']=="2")
						$res .= '<td><img src="../images/delete.png" style="cursor:default;"></img></td>';
					$res .= '<td class="coupon_name" '.$url_info.'>'.$coupon['name'].'</td>';
					$res .= '<td><button onclick="edit_panel_coupon(\'edit\',\''.$id.'\')" title="modifier le coupon"><img src="../images/wrench.png"></img></button></td>';
					$res .= '<td><button onclick="delete_panel_coupon(\''.$id.'\')" title="Supprimer le coupon"><img src="../images/trash.png"></image></td>';
					$res .= '</tr></table></div>';
					
					$res .= '<input type="hidden" id="liste_coupon_'.$id.'_status" value="'.$coupon['status'].'"></input>';
					$res .= '<input type="hidden" id="liste_coupon_'.$id.'_name" value="'.$coupon['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_coupon_'.$id.'_code" value="'.$coupon['code'].'"></input>';
					$res .= '<input type="hidden" id="liste_coupon_'.$id.'_image" value="'.$coupon['image'].'"></input>';	
					$res .= '<input type="hidden" id="liste_coupon_'.$id.'_url" value="'.$coupon['url'].'"></input>';					
					$res .= '<input type="hidden" id="liste_coupon_'.$id.'_description" value="'.$coupon['description'].'"></input>';
					
					$res .= '</td>';
					
					$run_count ++;
					$coupon_count ++;
				}
				$count ++;
			}
			if($coupon_count > 0)
				$res .= '</tr>';
			$res .= '</table>';
			$res .= '</div>';
			
			$res .= '<input type="hidden" id="path_images" value="'.$PATH_IMAGES.'"></input>';
			
			echo utf8_encode($res);
		break;
		case 'valide_panel_coupon' :
			$result = mysql_query('select coupon_id
									from
										coupon
									where
										coupon_id != "'.$_POST['id'].'"
										and
										coupon_code = "'.trim_str($_POST['code']).'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
				echo 1;
			else
				echo 0;
			mysql_free_result($result);			
		break;
		case 'save_panel_coupon' :
			switch($_POST['action'])
			{
				case 'new' :
					mysql_query('insert into coupon
									(
										coupon_name,
										coupon_code,
										coupon_status,
										coupon_image,
										coupon_url,
										coupon_description,
										coupon_date_maj
									)
									values
									(
										"'.trim_str($_POST['name']).'",
										"'.trim_str($_POST['code']).'",
										"'.$_POST['status'].'",
										"'.$_POST['image'].'",
										"'.trim_str($_POST['url']).'",
										"'.trim_str($_POST['description']).'",
										NOW()
									)');
				break;
				case 'edit' :
					mysql_query('update coupon
									set
										coupon_name = "'.trim_str($_POST['name']).'",
										coupon_code = "'.trim_str($_POST['code']).'",
										coupon_status = "'.$_POST['status'].'",
										coupon_image = "'.$_POST['image'].'",
										coupon_url = "'.trim_str($_POST['url']).'",
										coupon_description = "'.trim_str($_POST['description']).'",
										coupon_date_maj = NOW()
									where
										coupon_id = "'.$_POST['id'].'"');
				break;
			}
			echo mysql_error();
		break;
		case 'delete_panel_coupon' :
			mysql_query('update coupon
							set
								coupon_status = "1"
							where
								coupon_id = "'.$_POST['id'].'"');
			echo mysql_error();
		break;
		default :
			
		break;
	}
	exit();
}

?>