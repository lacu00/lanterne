<?php

include('../include/main.php');

$PATH_IMAGES .= 'recettes/';

function clean_recette()
{
	mysql_query('delete from recette_coupon
					where
						coupon_id NOT IN ( select coupon_id from coupon where 1)');
						
	mysql_query('delete from recette_logo
					where
						logo_id NOT IN ( select logo_id from logo where 1)');
						
	echo mysql_error();
}

function get_recette_content_logo_coupon($id, $type)
{
	$res = '';
	$count_elem = 0;
	
	$ELEM = get_list_elem($type);
	
	$result = mysql_query('select * from
								recette_'.$type.'
								natural join '.$type.'
							where
								recette_id = '.$id.'
								and
								recette_'.$type.'_status != 1
							order by
								recette_'.$type.'_priorite');
	echo mysql_error();
	$max_elem = mysql_num_rows($result);
	if($max_elem > 0)
	{
		$res .= '<table><tr>';
		while($row = mysql_fetch_assoc($result))
		{		
			$res .= '<td>';
			$res .= '<div class="new-thumb-wrap" onmouseover="survole_toolbar(\'visible\',\'toolbar_recette_'.$type.'_'.$row['recette_'.$type.'_id'].'\')" onmouseout="survole_toolbar(\'hidden\',\'toolbar_recette_'.$type.'_'.$row['recette_'.$type.'_id'].'\')">';
			$res .= '<img style="max-height:60px;max-width:80px;" src="'.$ELEM[ $row[$type.'_id'] ]['image'].'" title="'.$ELEM[ $row[$type.'_id'] ]['name'].'"></img>';
			$res .= '<div id="toolbar_recette_'.$type.'_'.$row['recette_'.$type.'_id'].'" align="center" class="toolbar_button" style="visibility:hidden;"><table><tr>';
			if($count_elem > 0)
				$res .= '<td><img src="../images/arrow_left.png" onclick="move_logo_coupon(\''.$type.'\',\''.$row['recette_'.$type.'_id'].'\',\'up\');" title="Augmenter la priorit&eacute; d\'affichage du '.$type.'" ></img></td>';
			$res .= '<td><img src="../images/trash.png" title="Supprimer le '.$type.'" onclick="delete_logo_coupon(\''.$type.'\',\''.$row['recette_'.$type.'_id'].'\')"></img></td>';
			if($count_elem < $max_elem - 1)
				$res .= '<td><img src="../images/arrow_right.png" onclick="move_logo_coupon(\''.$type.'\',\''.$row['recette_'.$type.'_id'].'\',\'down\');" title="Diminuer la priorit&eacute; d\'affichage du '.$type.'"></img></td>';
			$res .= '</tr></table></div>';
			$res .= '</div>';
			$res .= '</td>';
			
			$count_elem ++;
		}
		$res .= '</tr></table>';
	}
	mysql_free_result($result);
	
	return utf8_encode($res);
}

function get_recette_content_ingredient($id)
{
	$res = '';
	
	$old_cat = '';
	$res .= '<table>';
	$result = mysql_query('select *,
								if(recette_ingredient_priorite = 0,987654321,recette_ingredient_priorite) as ingredient_priorite,
								if(recette_ingredient_categorie_priorite = 0,987654321,recette_ingredient_categorie_priorite) as categorie_priorite
							from 
								recette_ingredient
								left join produit on produit.produit_id = recette_ingredient.produit_id
							where
								recette_ingredient_status != 1
								and
								recette_id = "'.$id.'"
							order by
								categorie_priorite,
								recette_ingredient_categorie,
								ingredient_priorite');
	echo mysql_error();
	if(mysql_num_rows($result)>0)
	{
		while($row = mysql_fetch_assoc($result))
		{				
			if($row['recette_ingredient_categorie'] != $old_cat)
			{
				$res .= '<tr><td class="ingredient_categorie" colspan="2">'.$row['recette_ingredient_categorie'].'</td></tr>';
				$old_cat = $row['recette_ingredient_categorie'];
			}
			$res .= '<tr>';
			$res .= '<td id="list_recette_ingredient_'.$row['recette_ingredient_id'].'_row" onmouseover="survole_list_recette_ingredient(\'over\',\''.$row['recette_ingredient_id'].'\');" onmouseout="survole_list_recette_ingredient(\'out\',\''.$row['recette_ingredient_id'].'\');" class="ingredient_details" onclick="edit_panel_recette_ingredient(\'edit\',\''.$row['recette_ingredient_id'].'\')">'.$row['recette_ingredient_name'].'</td>';
			if($row['produit_name'] != '')
				$res .= '<td class="ingredient_produit">( '.$row['produit_name'].' )</td>';
			$res .= '<input type="hidden" id="list_recette_ingredient_'.$row['recette_ingredient_id'].'_name" value="'.$row['recette_ingredient_name'].'"></input>';
			$res .= '<input type="hidden" id="list_recette_ingredient_'.$row['recette_ingredient_id'].'_categorie" value="'.$row['recette_ingredient_categorie'].'"></input>';
			$res .= '<input type="hidden" id="list_recette_ingredient_'.$row['recette_ingredient_id'].'_categorie_priorite" value="'.$row['recette_ingredient_categorie_priorite'].'"></input>';
			$res .= '<input type="hidden" id="list_recette_ingredient_'.$row['recette_ingredient_id'].'_priorite" value="'.$row['recette_ingredient_priorite'].'"></input>';	
			$res .= '<input type="hidden" id="list_recette_ingredient_'.$row['recette_ingredient_id'].'_produit" value="'.$row['produit_id'].'"></input>';
			$res .= '</tr>';
		}
	}
	mysql_free_result($result);
	$res .= '</table>';
	
	return utf8_encode($res);
}

if(isset($_GET['do']))
{
	switch($_GET['do'])
	{		
		case 'get_list_image' :
			$dir = $PATH_IMAGES;
			
			$IMG = array();
			$result = mysql_query('select recette_image
									from
										recette
									where
										recette_status != 1');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$IMG[ $row['recette_image'] ] = 1;
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
		case 'get_pick_up_content' :
			$images = array();	
			$path_images = '../ftp_images/';
			switch($_GET['type'])
			{
				case 'logo' :
					$path_images .= 'logos/';
				break;
				case 'coupon' :
					$path_images .= 'coupons/';
				break;
				case 'produit' :
					$path_images .= 'produits/';
				break;
			}
			
			switch($_GET['type'])
			{
				case 'logo' :
				case 'coupon' :
					$result = mysql_query('select * from '.$_GET['type'].'
											where
												'.$_GET['type'].'_status != 1
											order by
												'.$_GET['type'].'_name');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{		
							$image = '../images/no_photo.png';
							if($row[ $_GET['type'].'_image' ] != '' && file_exists($path_images.utf8_encode($row[ $_GET['type'].'_image' ])))
								$image = $path_images.utf8_encode($row[ $_GET['type'].'_image' ]);
							
							$images[] = array(
											'name' => utf8_encode(strtolower($row[ $_GET['type'].'_name' ])),
											'filename'	=>	utf8_encode($row[ $_GET['type'].'_name' ]),
											'size' => '',
											'lastmod' => '', 
											'url' => $row[ $_GET['type'].'_id' ],
											'thumb_url' => $image
										);
						}
					}
					mysql_free_result($result);
				break;
				case 'produit' :
					$ARBO = get_arbo( 'PRODUIT', false );
			
					$LIST_ID = array();
					if(isset($ARBO['child'][ $_GET['arbo'] ]))
						$LIST_ID[ $_GET['arbo'] ] = $ARBO['child'][ $_GET['arbo'] ]['name'];
					$LIST_ID = get_child_list( $ARBO, $_GET['arbo'], $LIST_ID );
					
					$list_id = '';
					foreach($LIST_ID as $id => $name)
					{
						if($list_id != '')
							$list_id .= ',';
						$list_id .= $id;
					}
					if($list_id != '')
						$list_id = ' and produit_arborescence IN ('.$list_id.') ';
					
					$PRODUITS = array();
					$result = mysql_query('select *,
												if(produit_priorite = 0,987654321,produit_priorite) as priorite
											from produit
											where
												produit_status != 1
												'.$list_id.'
											order by
												priorite,
												produit_name');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{
							$image = '../images/no_photo.png';
							if($row['produit_image'] != '' && file_exists($path_images.utf8_encode($row['produit_image'])))
								$image = $path_images.utf8_encode($row['produit_image']);
							
							$images[] = array(
											'name' => utf8_encode(strtolower($row['produit_name'])),
											'filename'	=>	utf8_encode($row['produit_name']),
											'size' => '',
											'lastmod' => '', 
											'url' => $row['produit_id'],
											'thumb_url' => $image
										);
						}
					}
					mysql_free_result($result);
				break;
			}
			
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
		case 'prepare_rubrique_select' :
			$res = '';
			
			$ARBO = get_arbo( strtoupper($_POST['type']), false );
			$profondeur_max = get_profondeur_max( $ARBO['child'] );
			
			switch($_POST['type'])
			{
				case 'recette' :
					$default_value = 'Toutes les recettes';
					$id_tag = '';
				break;
				case 'produit' :
					$default_value = 'Tous les produits';
					$id_tag = '_produit';
					$PATH_IMAGES = str_replace('recettes','produits',$PATH_IMAGES);
				break;
			}
			if($_POST['sub'] != '')
			{
				$default_value = 'Choisir une rubrique';
				$id_tag .= $_POST['sub'];
			}
			
			$res .= '<input type="hidden" id="path_images'.$id_tag.'" value="'.$PATH_IMAGES.'"></input>';
			$res .= '<table><tr>';
			$res .= '<td id="rubrique_recette'.$id_tag.'_range_0_content">';
			$res .= '<input type="hidden" id="select_rubrique_recette'.$id_tag.'_value" value="0"></input>';
			$res .= '<select class="toolbar_select" id="select_rubrique_recette'.$id_tag.'_range_0" onchange="select_rubrique_recette(\''.$id_tag.'\',\'0\');">';
			$res .= '<option value="0">'.$default_value.'</option>';
			
			if(isset($ARBO['parent'][0]))
			{
				foreach($ARBO['parent'][0] as $id => $arbo)
				{
					$res .= '<option value="'.$arbo['id'].'">'.$arbo['name'].'</option>';
				}
			}
			
			$res .= '</select>';
			$res .= '</td>';
						
			for( $i = 1 ; $i <= $profondeur_max ; $i++ )
				$res .= '<td id="rubrique_recette'.$id_tag.'_range_'.$i.'_content"></td>';
			
			$res .= '</tr></table>';
			
			$histo_arbo = array();
			foreach($ARBO['parent'] as $parent => $list_arbo)
			{
				$res .= '<input type="hidden" id="liste_rubrique_recette'.$id_tag.'_'.$parent.'_count" value="'.sizeof($list_arbo).'"></input>';
				$count = 0;
				foreach($list_arbo as $arbo)
				{				
					$res .= '<input type="hidden" id="liste_rubrique_recette'.$id_tag.'_'.$parent.'_'.$count.'_id" value="'.$arbo['id'].'"></input>';				
					$res .= '<input type="hidden" id="liste_rubrique_recette'.$id_tag.'_'.$parent.'_'.$count.'_name" value="'.$arbo['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_recette'.$id_tag.'_details_'.$arbo['id'].'_name" value="'.$arbo['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_recette'.$id_tag.'_details_'.$arbo['id'].'_code" value="'.$arbo['code'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_recette'.$id_tag.'_details_'.$arbo['id'].'_backcolor" value="'.$arbo['backcolor'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_recette'.$id_tag.'_details_'.$arbo['id'].'_fontcolor" value="'.$arbo['fontcolor'].'"></input>';
					$count ++;
				}
			}
					
			echo $res;
		break;
		case 'get_liste_recette' :
			clean_recette();

			$res = '';
			
			$nb_elem = $BOUTIQUE_NB_ELEM_COL * $BOUTIQUE_NB_ELEM_LIG;
			$page = $_POST['page'];
		
			$ARBO = get_arbo( 'RECETTE', false );
			
			$LIST_ID = array();
			if(isset($ARBO['child'][ $_POST['id'] ]))
				$LIST_ID[ $_POST['id'] ] = $ARBO['child'][ $_POST['id'] ]['name'];
			$LIST_ID = get_child_list( $ARBO, $_POST['id'], $LIST_ID );
			
			$list_id = '';
			foreach($LIST_ID as $id => $name)
			{
				if($list_id != '')
					$list_id .= ',';
				$list_id .= $id;
			}
			if($list_id != '')
				$list_id = ' and recette_arborescence IN ('.$list_id.') ';
				
			$_POST['search'] = utf8_decode($_POST['search']);				
				
			$sql_search = '';	
			if(isset($_POST['search']) && $_POST['search']!='')
			{
				$sql_search = ' and (
										recette_name LIKE "%'.$_POST['search'].'%"
										or
										recette_code LIKE "%'.$_POST['search'].'%"
									)';
			}
			
			$sql_order = '';
			switch($_POST['sort'])
			{
				case 'priorite' :					
					$sql_order = ' priorite '.$_POST['sort_type'].' , recette_name '.$_POST['sort_type'];
				break;
				case 'name' :
					$sql_order = ' recette_name '.$_POST['sort_type'].' , priorite '.$_POST['sort_type'];
				break;
			}
			
			$RECETTE_ERROR = array();
			$result = mysql_query('select recette_id
								from 
									recette_ingredient
									left join produit on produit.produit_id = recette_ingredient.produit_id
								where
									recette_ingredient_status != 1
									and
									(
										produit_volume = 0
										or
										produit_status = 2
									)');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$RECETTE_ERROR[ $row['recette_id'] ] = 1;
				}
			}
			mysql_free_result($result);
			
			$RECETTE = array();
			$sql = 'select *,
							if(recette_priorite = 0,987654321,recette_priorite) as priorite
						from recette
					where
						recette_status != 1
						'.$list_id.'
						'.$sql_search.'
					order by
						'.$sql_order;
			$result = mysql_query($sql);
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$image = '';
					if($row['recette_image'] != '' && file_exists($PATH_IMAGES.$row['recette_image']))
						$image = $row['recette_image'];		
					
					if($_POST['filtre_photo'] == "0" || ($_POST['filtre_photo'] == "1" && $image == ''))
					{
						$RECETTE[ $row['recette_id'] ] = array(
																'name'			=>	$row['recette_name'],
																'code'			=>	$row['recette_code'],
																'status'		=>	$row['recette_status'],
																'priorite'		=>	$row['recette_priorite'],
																'image'			=>	$image,
																'description'	=>	$row['recette_description'],
																'arborescence'	=>	$row['recette_arborescence'],
																'credit'		=>	$row['recette_credit'],
																
																'info_preparation'		=>	$row['recette_info_preparation'],
																'info_cuisson'			=>	$row['recette_info_cuisson'],
																'info_nb_people'		=>	$row['recette_info_nb_people'],
																'info_people_type'		=>	$row['recette_info_people_type'],
																'info_complementaire'	=>	$row['recette_info_complementaire'],
																'details_preparation'	=>	$row['recette_details_preparation']
															);
					}
				}
			}
			
			if(md5($sql) != $_POST['sql'])
				$page = 0;
			
			$res .= '<input type="hidden" id="toolbar_info_current_sql" value="'.md5($sql).'"></input>';
			$res .= '<input type="hidden" id="toolbar_info_nb_result" value="'.sizeof($RECETTE).' recette'.((sizeof($RECETTE)>1)?'s':'').'"></input>';
			
			$max_page = 1;
			if(ceil(sizeof($RECETTE) / $nb_elem) > 1)
				$max_page = ceil(sizeof($RECETTE) / $nb_elem);
			if($page >= $max_page)
				$page = $max_page - 1;
				
			$res .= '<input type="hidden" id="toolbar_info_current_page" value="'.$page.'"></input>';
			$res .= '<input type="hidden" id="toolbar_info_max_page" value="'.$max_page.'"';	
								
			mysql_free_result($result);

			$count = 0;
			$run_count = 0;
			$recette_count = 0;
			$res .= '<div>';
			$res .= '<table class="table_produits">';
			foreach($RECETTE as $id => $recette)
			{
				if($count >= $page*$nb_elem && $count < ($page*$nb_elem + $nb_elem))
				{
					if($recette_count == 0)
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
						
					$etat_recette = '';
					$icon_recette = '';
					if($recette['status']=="2")
					{
						$etat_recette = ' style="color:red;" title="Recette d&eacute;sactiv&eacute;e" ';
						$icon_recette = '<td><img src="../images/delete.png" style="cursor:default;"></img></td>';
					}
					
					if($recette['image'] == '')
						$image = '../images/no_photo.png';
					else
						$image = $PATH_IMAGES.$recette['image'];
					
					$res .= '<td onclick="select_recette(\''.$recette_count.'\');">';
					$res .= '<div align="center"><img id="liste_recette_'.$recette_count.'_select_img" class="product_img" src="'.$image.'"></img></div>';
					$res .= '<div><table class="table_button" '.$etat_recette.'><tr>';
					$res .= $icon_recette;
						
					$res .= '<td class="product_name">'.((isset($RECETTE_ERROR[$id]))?'<img title="Recette contenant des produits en rupture de stock / d&eacute;sactiv&eacute;s" style="cursor:default" src="../images/script_error.png"></img>':'').$recette['name'].' ('.$recette['priorite'].')</td>';
					$res .= '<td><button onclick="edit_panel_recette(\'edit\',\''.$id.'\');select_recette(\''.$recette_count.'\');" title="modifier la recette"><img src="../images/wrench.png"></img></button></td>';
					//$res .= '<td><button onclick="delete_panel_produit(\''.$id.'\');select_produits(\''.$recette_count.'\');" title="Supprimer le produit"><img src="../images/trash.png"></image></td>';
					$res .= '</tr>';
					$res .= '<tr>';
					$res .= '<td colspan="3" class="product_arbo">'.utf8_decode($ARBO['child'][ $recette['arborescence'] ]['name']).'</td>';
					$res .= '</tr>';
					$res .= '</table></div>';
					
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_status" value="'.$recette['status'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_name" value="'.$recette['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_code" value="'.$recette['code'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_priorite" value="'.$recette['priorite'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_description" value="'.$recette['description'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_image" value="'.$recette['image'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_arborescence" value="'.$recette['arborescence'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_credit" value="'.$recette['credit'].'"></input>';
					
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_info_preparation" value="'.$recette['info_preparation'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_info_cuisson" value="'.$recette['info_cuisson'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_info_nb_people" value="'.$recette['info_nb_people'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_info_people_type" value="'.$recette['info_people_type'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_info_complementaire" value="'.$recette['info_complementaire'].'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$id.'_details_preparation" value="'.$recette['details_preparation'].'"></input>';
					
					$res .= '<input type="hidden" id="liste_recette_'.$recette_count.'_id" value="'.$id.'"></input>';
					$res .= '<input type="hidden" id="liste_recette_'.$recette_count.'_selected" value="0"></input>';
					
					$res .= '</td>';
					
					$run_count ++;
					$recette_count ++;
				}
				$count ++;
			}
			if($recette_count > 0)
				$res .= '</tr>';
			$res .= '</table>';
			$res .= '</div>';
			
			$res .= '<input type="hidden" id="liste_recette_count" value="'.$recette_count.'"></input>';
			
			echo utf8_encode($res);
		break;
		case 'valide_panel_recette' :
			$result = mysql_query('select recette_id
									from
										recette
									where
										recette_status != "1"
										and
										recette_id != "'.$_POST['id'].'"
										and
										recette_code = "'.trim_str($_POST['code']).'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
				echo 1;
			else
				echo 0;
			mysql_free_result($result);			
		break;
		case 'save_panel_recette' :
			switch($_POST['action'])
			{
				case 'new' :
					mysql_query('insert into recette
									(
										recette_name,
										recette_code,
										recette_status,
										recette_image,
										recette_priorite,
										recette_description,
										recette_date_maj,
										recette_arborescence,
										recette_credit,
										
										recette_info_preparation,
										recette_info_cuisson,
										recette_info_nb_people,
										recette_info_people_type,
										recette_info_complementaire,
										recette_details_preparation
									)
									values
									(
										"'.trim_str($_POST['name']).'",
										"'.trim_str($_POST['code']).'",
										"'.$_POST['status'].'",
										"'.$_POST['image'].'",
										"'.$_POST['priorite'].'",
										"'.trim_str($_POST['description']).'",
										NOW(),
										"'.$_POST['arbo'].'",
										"'.trim_str($_POST['credit']).'",
										
										"'.trim_str($_POST['info_preparation']).'",
										"'.trim_str($_POST['info_cuisson']).'",
										"'.trim_str($_POST['info_nb_people']).'",
										"'.trim_str($_POST['info_people_type']).'",
										"'.trim_str($_POST['info_complementaire']).'",
										"'.trim_str($_POST['details_preparation']).'"
									)');
				break;
				case 'edit' :
					mysql_query('update recette
									set
										recette_name = "'.trim_str($_POST['name']).'",
										recette_code = "'.trim_str($_POST['code']).'",
										recette_status = "'.$_POST['status'].'",
										recette_image = "'.$_POST['image'].'",
										recette_priorite = "'.$_POST['priorite'].'",
										recette_description = "'.trim_str($_POST['description']).'",
										recette_date_maj = NOW(),
										recette_arborescence = "'.$_POST['arbo'].'",
										recette_credit = "'.trim_str($_POST['credit']).'",
										
										recette_info_preparation = "'.trim_str($_POST['info_preparation']).'",
										recette_info_cuisson = "'.trim_str($_POST['info_cuisson']).'",
										recette_info_nb_people = "'.trim_str($_POST['info_nb_people']).'",
										recette_info_people_type = "'.trim_str($_POST['info_people_type']).'",
										recette_info_complementaire = "'.trim_str($_POST['info_complementaire']).'",
										recette_details_preparation = "'.trim_str($_POST['details_preparation']).'"
									where
										recette_id = "'.$_POST['id'].'"');
				break;
			}
			echo mysql_error();
		break;
		case 'save_panel_recette_ingredient' :
			if($_POST['categorie']=='')
				$_POST['categorie_priorite'] = 0;
				
			switch($_POST['action'])
			{
				case 'new' :
					mysql_query('insert into recette_ingredient
									(
										recette_ingredient_name,
										recette_ingredient_categorie,
										recette_ingredient_categorie_priorite,
										recette_ingredient_priorite,
										recette_id,
										produit_id
									)
									values
									(
										"'.trim_str($_POST['name']).'",
										"'.trim_str($_POST['categorie']).'",
										"'.$_POST['categorie_priorite'].'",
										"'.$_POST['priorite'].'",
										"'.$_POST['recette'].'",
										"'.$_POST['produit'].'"
									)');
				break;
				case 'edit' :
					mysql_query('update recette_ingredient
									set
										recette_ingredient_name = "'.trim_str($_POST['name']).'",
										recette_ingredient_categorie = "'.trim_str($_POST['categorie']).'",
										recette_ingredient_categorie_priorite = "'.$_POST['categorie_priorite'].'",
										recette_ingredient_priorite = "'.$_POST['priorite'].'",
										produit_id = "'.$_POST['produit'].'"
									where
										recette_ingredient_id = "'.$_POST['id'].'"');
				break;
			}
			echo mysql_error();
			
			if($_POST['categorie'] != '')
			{
				mysql_query('update recette_ingredient set recette_ingredient_categorie_priorite = "'.$_POST['categorie_priorite'].'"
								where 
									recette_ingredient_categorie = "'.trim_str($_POST['categorie']).'"
									and
									recette_id = "'.$_POST['recette'].'"');
				echo mysql_error();
			}
		break;
		case 'delete_liste_recette' :
			mysql_query('update recette
							set
								recette_status = 1
							where
								recette_id IN ('.$_POST['id'].')');
			echo mysql_error();
		break;
		case 'add_new_logo_coupon' :
			$max_prio = 0;
			$result = mysql_query('select max(recette_'.$_POST['type'].'_priorite) as max_prio
									from recette_'.$_POST['type'].'
									where recette_'.$_POST['type'].'_status = 0
									and
										recette_id = "'.$_POST['id'].'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				$row = mysql_fetch_assoc($result);
				$max_prio = $row['max_prio'];				
			}
			mysql_free_result($result);
			
			$max_prio++;
			mysql_query('insert into recette_'.$_POST['type'].'
							(
								recette_'.$_POST['type'].'_priorite,
								recette_'.$_POST['type'].'_date_maj,
								recette_id,
								'.$_POST['type'].'_id
							)
							values
							(
								"'.$max_prio.'",
								NOW(),
								"'.$_POST['id'].'",
								"'.$_POST['select'].'"
							)');
			echo mysql_error();
		break;		
		case 'get_recette_content' :
			$res = '';
			
			switch($_POST['type'])
			{
				case 'logo' :
				case 'coupon' :
					$res .= get_recette_content_logo_coupon($_POST['id'],$_POST['type']);
				break;
				case 'ingredient' :
					$res .= get_recette_content_ingredient($_POST['id']);
				break;
			}
			
			echo $res;
		break;
		case 'delete_logo_coupon' :
			mysql_query('delete from recette_'.$_POST['type'].'
							where
								recette_'.$_POST['type'].'_id = "'.$_POST['id'].'"');
			echo mysql_error();
		break;
		case 'move_logo_coupon' :
			$elem = array(); 
		
			$result = mysql_query('select 
										recette_'.$_POST['type'].'_id as id
									from
										recette_'.$_POST['type'].'
									where
										recette_id = "'.$_POST['recette'].'"
										and
										recette_'.$_POST['type'].'_status = 0
									order by
										recette_'.$_POST['type'].'_priorite');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$elem[] = array(
										'id'		=>	$row['id']
									);
				}
				
				foreach($elem as $key => $val)
				{
					if($val['id'] == $_POST['id'])
					{
						switch($_POST['action'])
						{
							case 'up' :
								if(isset($elem[ $key - 1 ]))
								{
									$elem[ $key ] = $elem[ $key - 1 ];
									$elem[ $key - 1 ] = $val;
								}
							break;
							case 'down' :
								if(isset($elem[ $key + 1 ]))
								{
									$elem[ $key ] = $elem[ $key + 1 ];
									$elem[ $key + 1 ] = $val;
								}
							break;
						}
					}
				}
				
				foreach($elem as $key => $val)
				{
					mysql_query('update recette_'.$_POST['type'].'
									set
										recette_'.$_POST['type'].'_priorite = "'.($key+1).'"
									where
										recette_'.$_POST['type'].'_id = "'.$val['id'].'"');
					echo mysql_error();
				}
			}
			mysql_free_result($result);
		break;
		case 'delete_panel_recette_ingredient' :
			mysql_query('delete from recette_ingredient
							where
								recette_ingredient_id = "'.$_POST['id'].'"');
			echo mysql_error();
		break;
		case 'valide_panel_move_recette' :
			mysql_query('update recette set
							recette_arborescence = "'.$_POST['rubrique'].'"
						where
							recette_id IN ('.$_POST['liste'].')');
			echo mysql_error();
		break;
		default :
			
		break;
	}
	exit();
}

?>