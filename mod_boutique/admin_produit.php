<?php

include('../include/main.php');

$PATH_IMAGES .= 'produits/';

if(isset($_GET['do']))
{
	switch($_GET['do'])
	{		
		case 'get_list_image' :
			$dir = $PATH_IMAGES;
			
			$IMG = array();
			$result = mysql_query('select produit_image
									from
										produit
									where
										produit_status = "0"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$IMG[ $row['produit_image'] ] = 1;
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
		case 'prepare_rubrique_select' :
			$res = '';
			
			$ARBO = get_arbo( 'PRODUIT', false );
			$profondeur_max = get_profondeur_max( $ARBO['child'] );
			
			if($_POST['type']=='')
				$res .= '<input type="hidden" id="path_images" value="'.$PATH_IMAGES.'"></input>';
			$res .= '<table><tr>';
			$res .= '<td id="rubrique_produit'.$_POST['type'].'_range_0_content">';
			$res .= '<input type="hidden" id="select_rubrique_produit'.$_POST['type'].'_value" value="0"></input>';
			$res .= '<select class="toolbar_select" id="select_rubrique_produit'.$_POST['type'].'_range_0" onchange="select_rubrique_produit(\''.$_POST['type'].'\',\'0\');">';
			$res .= '<option value="0">'.(($_POST['type']!='')?'Choisir une rubrique':'Tous les produits').'</option>';
			
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
				$res .= '<td id="rubrique_produit'.$_POST['type'].'_range_'.$i.'_content"></td>';
			
			$res .= '</tr></table>';
			
			$histo_arbo = array();
			foreach($ARBO['parent'] as $parent => $list_arbo)
			{
				$res .= '<input type="hidden" id="liste_rubrique_produit'.$_POST['type'].'_'.$parent.'_count" value="'.sizeof($list_arbo).'"></input>';
				$count = 0;
				foreach($list_arbo as $arbo)
				{				
					$res .= '<input type="hidden" id="liste_rubrique_produit'.$_POST['type'].'_'.$parent.'_'.$count.'_id" value="'.$arbo['id'].'"></input>';				
					$res .= '<input type="hidden" id="liste_rubrique_produit'.$_POST['type'].'_'.$parent.'_'.$count.'_name" value="'.$arbo['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_produit'.$_POST['type'].'_details_'.$arbo['id'].'_name" value="'.$arbo['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_produit'.$_POST['type'].'_details_'.$arbo['id'].'_code" value="'.$arbo['code'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_produit'.$_POST['type'].'_details_'.$arbo['id'].'_backcolor" value="'.$arbo['backcolor'].'"></input>';
					$res .= '<input type="hidden" id="liste_rubrique_produit'.$_POST['type'].'_details_'.$arbo['id'].'_fontcolor" value="'.$arbo['fontcolor'].'"></input>';
					
					$count ++;
				}
			}
			
			echo $res;
		break;
		case 'get_liste_produits' :
			$res = '';
		
			$ARBO = get_arbo( 'PRODUIT', false );
			
			$nb_elem = $BOUTIQUE_NB_ELEM_COL * $BOUTIQUE_NB_ELEM_LIG;
			$page = $_POST['page'];
			
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
				$list_id = ' and produit_arborescence IN ('.$list_id.') ';
				
			$_POST['search'] = utf8_decode($_POST['search']);				
				
			$sql_search = '';	
			if(isset($_POST['search']) && $_POST['search']!='')
			{
				$sql_search = ' and (
										produit_name LIKE "%'.$_POST['search'].'%"
										or
										produit_code LIKE "%'.$_POST['search'].'%"
									)';
			}
			
			$sql_order = '';
			switch($_POST['sort'])
			{
				case 'priorite' :					
					$sql_order = ' priorite '.$_POST['sort_type'].' , produit_name '.$_POST['sort_type'];
				break;
				case 'name' :
					$sql_order = ' produit_name '.$_POST['sort_type'].' , priorite '.$_POST['sort_type'];
				break;
			}
			
			$PRODUITS = array();
			$sql = 'select *,
							if(produit_priorite = 0,987654321,produit_priorite) as priorite
						from produit
						where
							produit_status != "1"
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
					$PRODUITS[ $row['produit_id'] ] = array(
															'name'			=>	$row['produit_name'],
															'code'			=>	$row['produit_code'],
															'status'		=>	$row['produit_status'],
															'priorite'		=>	$row['produit_priorite'],
															'volume'		=>	$row['produit_volume'],
															'poids'			=>	$row['produit_poids'],
															'image'			=>	$row['produit_image'],
															'description'	=>	$row['produit_description'],
															'arborescence'	=>	$row['produit_arborescence'],
															'promo'			=>  $row['produit_promo']
														);
				}
			}
			
			if(md5($sql) != $_POST['sql'])
				$page = 0;
			
			$res .= '<input type="hidden" id="toolbar_info_current_sql" value="'.md5($sql).'"></input>';
			$res .= '<input type="hidden" id="toolbar_info_nb_result" value="'.mysql_num_rows($result).' produit'.((mysql_num_rows($result)>1)?'s':'').'"></input>';
			
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
			$produit_count = 0;
			$res .= '<div>';
			$res .= '<table class="table_produits">';
			foreach($PRODUITS as $id => $produit)
			{
				if($count >= $page*$nb_elem && $count < ($page*$nb_elem + $nb_elem))
				{
					if($produit_count == 0)
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
						
					$etat_produit = '';
					$icon_produit = '';
					if($produit['status']=="2")
					{
						$etat_produit = ' style="color:red;" title="Produit d&eacute;sactiv&eacute;" ';
						$icon_produit = '<td><img src="../images/delete.png" style="cursor:default;"></img></td>';
					}
					else if($produit['volume']=="0")
					{
						$etat_produit = ' style="color:orange;" title="Produit en rupture de stock" ';
						$icon_produit = '<td><img src="../images/basket_error.png" style="cursor:default;"></img></td>';
					}
					if($produit['promo']=="1")
						$icon_produit .= '<td><img style="cursor:default;" src="../images/money.png" title="Produit en promotion"></img></td>';
						
					$image = '../images/no_photo.png';
					if($produit['image'] != '' && file_exists($PATH_IMAGES.$produit['image']))
						$image = $PATH_IMAGES.$produit['image'];
					
					$res .= '<td onclick="select_produits(\''.$produit_count.'\');">';
					$res .= '<div align="center"><img id="liste_produits_'.$produit_count.'_select_img" class="product_img" src="'.$image.'"></img></div>';
					$res .= '<div><table class="table_button" '.$etat_produit.'><tr>';
					$res .= $icon_produit;
						
					$res .= '<td class="product_name">'.$produit['name'].' ('.$produit['priorite'].')</td>';
					$res .= '<td><button onclick="edit_panel_produit(\'edit\',\''.$id.'\');select_produits(\''.$produit_count.'\');" title="modifier le produit"><img src="../images/wrench.png"></img></button></td>';
					//$res .= '<td><button onclick="delete_panel_produit(\''.$id.'\');select_produits(\''.$produit_count.'\');" title="Supprimer le produit"><img src="../images/trash.png"></image></td>';
					$res .= '</tr>';
					$res .= '<tr>';
					$res .= '<td colspan="3" class="product_arbo">'.utf8_decode($ARBO['child'][ $produit['arborescence'] ]['name']).'</td>';
					$res .= '</tr>';
					$res .= '</table></div>';
					
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_status" value="'.$produit['status'].'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_name" value="'.$produit['name'].'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_code" value="'.$produit['code'].'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_priorite" value="'.$produit['priorite'].'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_volume" value="'.$produit['volume'].'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_poids" value="'.$produit['poids'].'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_image" value="'.$produit['image'].'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_arborescence" value="'.$produit['arborescence'].'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_description" value="'.$produit['description'].'"></input>';	
					$res .= '<input type="hidden" id="liste_produits_'.$id.'_promo" value="'.$produit['promo'].'"></input>';	
					
					$res .= '<input type="hidden" id="liste_produits_'.$produit_count.'_id" value="'.$id.'"></input>';
					$res .= '<input type="hidden" id="liste_produits_'.$produit_count.'_selected" value="0"></input>';
					
					$res .= '</td>';
					
					$run_count ++;
					$produit_count ++;
				}
				$count ++;
			}
			if($produit_count > 0)
				$res .= '</tr>';
			$res .= '</table>';
			$res .= '</div>';
			
			$res .= '<input type="hidden" id="liste_produits_count" value="'.$produit_count.'"></input>';
			
			echo utf8_encode($res);
		break;
		case 'valide_panel_produit' :
			$result = mysql_query('select produit_id
									from
										produit
									where
										produit_status != "1"
										and
										produit_id != "'.$_POST['id'].'"
										and
										produit_code = "'.trim_str($_POST['code']).'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
				echo "1";
			else
				echo "0";
			mysql_free_result($result);			
		break;
		case 'save_panel_produit' :
			switch($_POST['action'])
			{
				case 'new' :
					mysql_query('insert into produit
									(
										produit_name,
										produit_code,
										produit_status,
										produit_volume,
										produit_poids,
										produit_image,
										produit_priorite,
										produit_description,
										produit_date_maj,
										produit_arborescence,
										produit_promo
									)
									values
									(
										"'.trim_str($_POST['name']).'",
										"'.trim_str($_POST['code']).'",
										"'.$_POST['status'].'",
										"'.$_POST['volume'].'",
										"'.$_POST['poids'].'",
										"'.$_POST['image'].'",
										"'.$_POST['priorite'].'",
										"'.trim_str($_POST['description']).'",
										NOW(),
										"'.$_POST['arbo'].'",
										"'.$_POST['promo'].'"
									)');
				break;
				case 'edit' :
					mysql_query('update produit
									set
										produit_name = "'.trim_str($_POST['name']).'",
										produit_code = "'.trim_str($_POST['code']).'",
										produit_status = "'.$_POST['status'].'",
										produit_volume = "'.$_POST['volume'].'",
										produit_poids = "'.$_POST['poids'].'",
										produit_image = "'.$_POST['image'].'",
										produit_priorite = "'.$_POST['priorite'].'",
										produit_description = "'.trim_str($_POST['description']).'",
										produit_date_maj = NOW(),
										produit_arborescence = "'.$_POST['arbo'].'",
										produit_promo = "'.$_POST['promo'].'"
									where
										produit_id = "'.$_POST['id'].'"');
				break;
			}
			echo mysql_error();
		break;
		case 'delete_liste_produits' :
			mysql_query('update produit
							set
								produit_status = "1"
							where
								produit_id IN ('.$_POST['id'].')');
			echo mysql_error();
		break;
		case 'get_produit_info' :
			$res = '';
		
			$PRODUIT = array(
								'id'	=>	0,
								'img'	=>	'',
								'name'	=>	'Aucun produit associ&eacute;'
							);
				
			if($_POST['id'] != "0")
			{
				$result = mysql_query('select * from produit
										where
											produit_status = 0
											and
											produit_id = "'.$_POST['id'].'"');
				echo mysql_error();
				if(mysql_num_rows($result)>0)
				{
					while($row = mysql_fetch_assoc($result))
					{
						$PRODUIT['id']		=	$row['produit_id'];
						$PRODUIT['img']		=	$PATH_IMAGES.$row['produit_image'];
						$PRODUIT['name']	=	$row['produit_name'];
					}
				}
				mysql_free_result($result);
			}
			
			$res .= '<input type="hidden" id="produit_info_id" value="'.$PRODUIT['id'].'"></input>';
			$res .= '<input type="hidden" id="produit_info_img" value="'.$PRODUIT['img'].'"></input>';
			$res .= '<input type="hidden" id="produit_info_name" value="'.$PRODUIT['name'].'"></input>';
			
			echo utf8_encode($res);			
		break;	
		case 'valide_panel_move_produit' :
			mysql_query('update produit
							set
								produit_arborescence = "'.$_POST['rubrique'].'"
							where
								produit_id IN ('.$_POST['liste'].')');
			echo mysql_error();
		break;
		default :
			
		break;
	}
	exit();
}

?>