<?php

include('../include/main.php');

$PATH_IMAGES .= 'logos/';

if(isset($_GET['do']))
{
	switch($_GET['do'])
	{		
		case 'get_list_image' :
			$dir = $PATH_IMAGES;
			
			$IMG = array();
			$result = mysql_query('select logo_image
									from
										logo
									where
										logo_status = "0"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$IMG[ $row['logo_image'] ] = 1;
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
			echo (json_encode($o));
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
		case 'get_liste_logo' :
			$res = '';
			
			$nb_elem = $BOUTIQUE_NB_ELEM_COL * $BOUTIQUE_NB_ELEM_LIG;
			$page = $_POST['page'];
			
			$_POST['search'] = utf8_decode($_POST['search']);
			
			$sql_search = '';
			if(isset($_POST['search']) && $_POST['search']!='')
			{
				$sql_search = ' and (
										logo_name LIKE "%'.$_POST['search'].'%"
										or
										logo_code LIKE "%'.$_POST['search'].'%"
									) ';
			}
			
			$LOGO = array();
			$sql = 'select * from logo
							where
								logo_status != "1"
								'.$sql_search.'
							order by
								logo_name';
			$result = mysql_query($sql);
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$LOGO[ $row['logo_id'] ] = array(
															'name'			=>	$row['logo_name'],
															'code'			=>	$row['logo_code'],
															'status'		=>	$row['logo_status'],
															'image'			=>	$row['logo_image'],
															'description'	=>	$row['logo_description'],
															'url'			=>	$row['logo_url']
														);
				}
			}
			
			if(md5($sql) != $_POST['sql'])
				$page = 0;
			
			$res .= '<input type="hidden" id="toolbar_info_current_sql" value="'.md5($sql).'"></input>';
			$res .= '<input type="hidden" id="toolbar_info_nb_result" value="'.mysql_num_rows($result).' logo'.((mysql_num_rows($result)>1)?'s':'').'"></input>';
			
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
			$logo_count = 0;
			$res .= '<div>';
			$res .= '<table class="table_produits">';
			foreach($LOGO as $id => $logo)
			{
				if($count >= $page*$nb_elem && $count < ($page*$nb_elem + $nb_elem))
				{
					if($logo_count == 0)
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
						
					$etat_logo = '';
					if($logo['status']=="2")
						$etat_logo = ' style="color:red;" title="Logo d&eacute;sactiv&eacute;" ';
						
					$url_info = '';
					if($logo['url'] != '')
					{
						if(substr(strtolower($logo['url']),0,4)!='http')
							$logo['url'] = 'http://'.$logo['url'];
						$url_info = ' style="color:blue;text-decoration:underline;cursor:pointer;" title="'.$logo['url'].'" onclick="window.open(\''.$logo['url'].'\');" ';
					}
					
					$image = '../images/no_photo.png';
					if($logo['image'] != '' && file_exists($PATH_IMAGES.$logo['image']))
						$image = $PATH_IMAGES.$logo['image'];
					
					$res .= '<td>';
					$res .= '<div align="center"><img class="product_img" src="'.$image.'"></img></div>';
					$res .= '<div><table class="table_button" '.$etat_logo.'><tr>';
					if($logo['status']=="2")
						$res .= '<td><img src="../images/delete.png" style="cursor:default;"></img></td>';
					$res .= '<td class="logo_name" '.$url_info.'>'.$logo['name'].'</td>';
					$res .= '<td><button onclick="edit_panel_logo(\'edit\',\''.$id.'\')" title="modifier le logo"><img src="../images/wrench.png"></img></button></td>';
					$res .= '<td><button onclick="delete_panel_logo(\''.$id.'\')" title="Supprimer le logo"><img src="../images/trash.png"></image></td>';
					$res .= '</tr></table></div>';
					
					$res .= '<input type="hidden" id="liste_logo_'.$id.'_status" value="'.$logo['status'].'"></input>';
					$res .= '<input type="hidden" id="liste_logo_'.$id.'_name" value="'.$logo['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_logo_'.$id.'_code" value="'.$logo['code'].'"></input>';
					$res .= '<input type="hidden" id="liste_logo_'.$id.'_image" value="'.$logo['image'].'"></input>';
					$res .= '<input type="hidden" id="liste_logo_'.$id.'_description" value="'.$logo['description'].'"></input>';	
					$res .= '<input type="hidden" id="liste_logo_'.$id.'_url" value="'.$logo['url'].'"></input>';					
					
					$res .= '</td>';
					
					$run_count ++;
					$logo_count ++;
				}
				$count ++;
			}
			if($logo_count > 0)
				$res .= '</tr>';
			$res .= '</table>';
			$res .= '</div>';
			
			$res .= '<input type="hidden" id="path_images" value="'.$PATH_IMAGES.'"></input>';
			
			echo utf8_encode($res);
		break;
		case 'valide_panel_logo' :
			$result = mysql_query('select logo_id
									from
										logo
									where
										logo_id != "'.$_POST['id'].'"
										and
										logo_code = "'.trim_str($_POST['code']).'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
				echo 1;
			else
				echo 0;
			mysql_free_result($result);			
		break;
		case 'save_panel_logo' :
			switch($_POST['action'])
			{
				case 'new' :
					mysql_query('insert into logo
									(
										logo_name,
										logo_code,
										logo_status,
										logo_image,
										logo_url,
										logo_description,
										logo_date_maj
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
					mysql_query('update logo
									set
										logo_name = "'.trim_str($_POST['name']).'",
										logo_code = "'.trim_str($_POST['code']).'",
										logo_status = "'.$_POST['status'].'",
										logo_image = "'.$_POST['image'].'",
										logo_url = "'.trim_str($_POST['url']).'",
										logo_description = "'.trim_str($_POST['description']).'",
										logo_date_maj = NOW()
									where
										logo_id = "'.$_POST['id'].'"');
				break;
			}
			echo mysql_error();
		break;
		case 'delete_panel_logo' :
			mysql_query('update logo
							set
								logo_status = "1"
							where
								logo_id = "'.$_POST['id'].'"');
			echo mysql_error();
		break;
		default :
			
		break;
	}
	exit();
}

?>