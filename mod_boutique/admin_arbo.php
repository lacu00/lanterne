<?php

include('../include/main.php');

$PATH_IMAGES .= 'rubriques/';

function get_list_arbo($ARBO, $parent)
{
	global $PATH_IMAGES;

	$res = '';
	
	$count = 0;
	if(isset($ARBO['parent'][ $parent ]))
	{
		$max = sizeof($ARBO['parent'][ $parent ]);
		foreach($ARBO['parent'][ $parent ] as $id => $arbo)
		{
			foreach($arbo as $key => $val)
				$arbo[ $key ] = ($val);
		
			$res .= '<div>';
			$res .= '<table><tr>';
			
			if($arbo['parent'] != "0")
				$res .= '<td style="padding:5px;"><img src="../images/arrow_right.png"></img></td>';
			
			$res .= '<td onmouseover="survole_arbo(\'over\',\''.$arbo['id'].'\');" onmouseout="survole_arbo(\'out\',\''.$arbo['id'].'\');"><div class="arbo_tree_cell">';
			$res .= '<div class="arbo_name" style="background-color:'.$arbo['backcolor'].';color:'.$arbo['fontcolor'].';">';
			if($arbo['status'] == 2)
				$res .= '<img src="../images/delete.png"></img>';
			$res .= $arbo['name'];
			$res .= ' ('.$arbo['position'].')';
			
			$image = '../images/no_photo.png';
			if($arbo['image'] != '' && file_exists($PATH_IMAGES.$arbo['image']))
				$image = $PATH_IMAGES.$arbo['image'];
			
			$res .= '
				<div id="arbo_image_'.$arbo['id'].'" class="thumb-wrap" style="visibility:hidden;position:absolute;margin-top:20px;margin-left:160px;">
					<img style="max-width:160px;max-height:120px;" src="'.$image.'">
				</div>
			';
			$res .= '</div>';
			$res .= '<div align="center"><table><tr>';
			$res .= '<td><button title="&Eacute;diter la rubrique" onclick="edit_panel_edit_arbo(\'edit\',\''.$arbo['id'].'\',\''.$arbo['parent'].'\');"><img src="../images/table_edit.png"></img></button></td>';
			$res .= '<td><button title="Supprimer la rubrique" onclick="valide_delete_panel_edit_arbo(\''.$arbo['id'].'\');"><img src="../images/trash.png"></img></button></td>';
			$res .= '<td><button title="Ajouter une sous-rubrique" onclick="edit_panel_edit_arbo(\'new\',\'0\',\''.$arbo['id'].'\');"><img src="../images/add.png"></img></button></td>';
			$res .= '<td><button title="D&eacute;placer la rubrique" onclick="move_panel_edit_arbo(\''.$arbo['id'].'\');"><img src="../images/folder_go.png"></img></button></td>';
			$res .= '</tr></table></div>';
			$res .= '</td>';
			$res .= '<td>';
			
			$res .= get_list_arbo($ARBO, $arbo['id']);
			
			$res .= '</div></td>';
			$res .= '</tr></table>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_parent" value="'.$arbo['parent'].'"></input>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_status" value="'.$arbo['status'].'"></input>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_name" value="'.$arbo['name'].'"></input>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_code" value="'.$arbo['code'].'"></input>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_position" value="'.$arbo['position'].'"></input>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_description" value="'.$arbo['description'].'"></input>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_backcolor" value="'.$arbo['backcolor'].'"></input>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_fontcolor" value="'.$arbo['fontcolor'].'"></input>';
			$res .= '<input type="hidden" id="list_arbo_content_'.$arbo['id'].'_image" value="'.$arbo['image'].'"></input>';
			$res .= '</div>';
			
			if($arbo['parent'] == "0" && $count < $max - 1)
				$res .= '<hr />';
			
			$count ++;
		}
	}
	
	return $res;
}

if(isset($_GET['do']))
{
	switch($_GET['do'])
	{
		case 'get_list_image' :
			$dir = $PATH_IMAGES;

			$IMG = array();
			$result = mysql_query('select arborescence_image
									from
										arborescence
									where
										arborescence_status = "0"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$IMG[ $row['arborescence_image'] ] = 1;
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
		case 'valide_panel_edit_arbo' :
			$result = mysql_query('select arborescence_id from
										arborescence
									where
										arborescence_id != "'.$_POST['id'].'"
										and arborescence_code = "'.trim_str($_POST['code']).'"
										and arborescence_type = "'.$_POST['type'].'"
									and
										arborescence_status = 0');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
				echo '1';
			else
				echo '0';
			mysql_free_result($result);
		break;
		case 'save_panel_edit_arbo' :
			switch($_POST['action'])
			{
				case 'new' :					
					mysql_query('insert into arborescence
									(
										arborescence_type,
										arborescence_name,
										arborescence_code,
										arborescence_status,
										arborescence_backcolor,
										arborescence_fontcolor,
										arborescence_description,
										arborescence_parent,
										arborescence_position,
										arborescence_date_maj,
										arborescence_image
									)
									values
									(
										"'.$_POST['type'].'",
										"'.trim_str($_POST['name']).'",
										"'.trim_str($_POST['code']).'",
										"'.$_POST['status'].'",
										"'.$_POST['backcolor'].'",
										"'.$_POST['fontcolor'].'",
										"'.trim_str($_POST['description']).'",
										"'.$_POST['parent'].'",
										"'.$_POST['position'].'",
										NOW(),
										"'.$_POST['image'].'"
									)');
					echo mysql_error();
				break;
				case 'edit' :
					mysql_query('update arborescence
									set
										arborescence_name = "'.trim_str($_POST['name']).'",
										arborescence_code = "'.trim_str($_POST['code']).'",
										arborescence_status = "'.$_POST['status'].'",
										arborescence_backcolor = "'.$_POST['backcolor'].'",
										arborescence_fontcolor = "'.$_POST['fontcolor'].'",
										arborescence_description = "'.trim_str($_POST['description']).'",
										arborescence_position = "'.$_POST['position'].'",
										arborescence_date_maj = NOW(),
										arborescence_image = "'.$_POST['image'].'"
									where
										arborescence_id = "'.$_POST['id'].'"');
					echo mysql_error();
				break;
			}
			echo mysql_error();
		break;
		case 'get_list_arbo' :
			$res = '';
				
			$ARBO = get_arbo($_POST['type']);

			$res .= get_list_arbo($ARBO, 0);
			
			$res .= '<input type="hidden" id="path_images" value="'.$PATH_IMAGES.'"></input>';
				
			echo $res;
		break;
		case 'valide_delete_panel_edit_arbo' :
			$_POST['type'] = strtolower($_POST['type']);
			
			$nb_arbo = 0;
			$result = mysql_query('select * from arborescence where
										arborescence_status != 1
									and
										arborescence_parent = "'.$_POST['id'].'"');
			echo mysql_error();
			$nb_arbo = mysql_num_rows($result);			
			mysql_free_result($result);
			
			$nb_elem = 0;
			$result = mysql_query('select * from '.$_POST['type'].' where
									'.$_POST['type'].'_status != 1
									and
									'.$_POST['type'].'_arborescence = "'.$_POST['id'].'"');
			echo mysql_error();
			$nb_elem = mysql_num_rows($result);
			mysql_free_result($result);
			
			if($nb_elem > 0 || $nb_arbo > 0)
			{
				$res = 'Suppression impossible car des elements sont lies a cette rubrique :';
				if($nb_arbo > 0)
					$res .= "\r\n ".$nb_arbo.' sous-rubrique'.(($nb_arbo>1)?'s':'');
				if($nb_elem > 0)
					$res .= "\r\n ".$nb_elem.' '.$_POST['type'].(($nb_elem>1)?'s':'');	
				echo $res;
			}
			else
				echo "1";
		break;
		case 'delete_panel_edit_arbo' :
			mysql_query('update arborescence set arborescence_status = "1" where
				arborescence_id = "'.$_POST['id'].'"');
			echo mysql_error();
		break;
		case 'move_panel_edit_arbo' :
			$res = '';
			
			$ARBO = get_arbo( $_POST['type'], false );
			$profondeur_max = get_profondeur_max( $ARBO['child'] );
			
			$list = array();
			$list[ $_POST['id'] ] = 1;
			$list = get_parent_list( $ARBO, $_POST['id'], $list );
			
			$res .= '<table><tr>';
			$res .= '<td id="rubrique_move_range_0_content">';
			$res .= '<input type="hidden" id="select_rubrique_move_value" value="0"></input>';
			$res .= '<select id="select_rubrique_move_range_0" onchange="select_rubrique_move(\'0\');">';
			$res .= '<option value="0">Positionner la rubrique &agrave; la racine</option>';
			
			if(isset($ARBO['parent'][0]))
			{
				foreach($ARBO['parent'][0] as $id => $arbo)
				{
					$ok = 1;
					foreach($list as $key => $val)
					{
						if($id == $key)
							$ok = 0;
					}
					
					if($ok == 1)
						$res .= '<option value="'.$arbo['id'].'">'.$arbo['name'].'</option>';
				}
			}
			
			$res .= '</select>';
			$res .= '</td>';
						
			for( $i = 1 ; $i <= $profondeur_max ; $i++ )
				$res .= '<td id="rubrique_move_range_'.$i.'_content"></td>';
			
			$res .= '</tr></table>';
			
			$histo_arbo = array();
			foreach($ARBO['parent'] as $parent => $list_arbo)
			{
				$res .= '<input type="hidden" id="liste_rubrique_move_'.$parent.'_count" value="'.sizeof($list_arbo).'"></input>';
				$count = 0;
				foreach($list_arbo as $arbo)
				{				
					$res .= '<input type="hidden" id="liste_rubrique_move_'.$parent.'_'.$count.'_id" value="'.$arbo['id'].'"></input>';				
					$res .= '<input type="hidden" id="liste_rubrique_move_'.$parent.'_'.$count.'_name" value="'.$arbo['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_move_details_'.$arbo['id'].'_name" value="'.$arbo['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_move_details_'.$arbo['id'].'_code" value="'.$arbo['code'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_move_details_'.$arbo['id'].'_backcolor" value="'.$arbo['backcolor'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_move_details_'.$arbo['id'].'_fontcolor" value="'.$arbo['fontcolor'].'"></input>';
					
					$count ++;
				}
			}
			
			echo $res;
		break;
		case 'valide_panel_move_rubrique' :
			mysql_query('update arborescence
							set
								arborescence_parent = "'.$_POST['rubrique'].'"
							where
								arborescence_id = "'.$_POST['id'].'"');
			echo mysql_error();
		break;
		default :
			
		break;
	}
	exit();
}

?>