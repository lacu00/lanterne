<?php

include('../include/main.php');

$PATH_IMAGES .= 'produits/';

if(isset($_POST['do']))
{
	switch($_POST['do'])
	{
		case 'get_rubrique' :
			$res = '';
			$count = 0;
			$limit = -1;
			
			if($_POST['parent'] != "0")
			{
				$result = mysql_query('select * from arborescence where arborescence_id = "'.$_POST['parent'].'"');
				echo mysql_error();
				if(mysql_num_rows($result)>0)
				{
					$row = mysql_fetch_assoc($result);
					trace_log( 'RUBRIQUE' , $row['arborescence_code'] , 'Navigation par PROMO' );
				}
				mysql_free_result($result);
			}
			
			$ARBO = get_arbo( $_POST['type'] , false );
			
			if(isset($ARBO['parent'][ $_POST['parent'] ]))
			{
				$nb_rubrique = sizeof($ARBO['parent'][ $_POST['parent'] ]);
				if($nb_rubrique > 12)
					$limit = floor($nb_rubrique/2);
				$res .= '<table><tr>';
				foreach($ARBO['parent'][ $_POST['parent'] ] as $id => $arbo)
				{
					if($count == $limit)
						$res .= '</tr></table><table><tr>';
					$res .= '<td class="borne_rubrique">';
					$res .= '<div class="borne_rubrique_name" onclick="get_rubrique(\''.$arbo['id'].'\',0);" style="background-color:'.$arbo['backcolor'].';color:'.$arbo['fontcolor'].';"><img src="../images/arrow_sub.png"></img> '.$arbo['name'].'</div>';
					$res .= '</td>';
					
					$count ++;
				}
				$res .= '</tr></table>';
			}
			
			$old = "-1";
			if($_POST['parent'] != '0')
				$old = $ARBO['child'][ $_POST['parent'] ]['parent'];
			
			$res .= '<input type="hidden" id="rubrique_content_old_parent" value="'.$old.'"></input>';
			
			echo $res;
		break;	
		case 'get_produit' :
			$res = '';
			
			//$nb_elem = $BORNE_NB_ELEM_PER_PAGE;
			$nb_elem = $BORNE_NB_ELEM_COL * $BORNE_NB_ELEM_LIG;
		
			$ARBO = get_arbo( 'PRODUIT' , false );
			
			$rubrique_name = 'Accueil';
			$rubrique_back = 'white';
			$rubrique_font = 'black';
			
			$LIST_ID = array();
			if(isset($ARBO['child'][ $_POST['parent'] ]))
			{
				$rubrique_name = $ARBO['child'][ $_POST['parent'] ]['name'];
				$rubrique_back = $ARBO['child'][ $_POST['parent'] ]['backcolor'];
				$rubrique_font = $ARBO['child'][ $_POST['parent'] ]['fontcolor'];
				$LIST_ID[ $_POST['parent'] ] = $ARBO['child'][ $_POST['parent'] ]['name'];
			}
			$LIST_ID = get_child_list( $ARBO, $_POST['parent'], $LIST_ID );
			
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
										produit_status = 0
										'.$list_id.'
										and produit_priorite > 0
										and produit_promo = 1
									order by
										priorite,
										produit_name');
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
															'arborescence'	=>	$row['produit_arborescence']
														);
				}
			}
			mysql_free_result($result);
			
			$count = 0;
			$running_count = 0;
			$res .= '<div align="center">';
			$res .= '<table id="table_produits" style="width:100%;">';
			foreach($PRODUITS as $id => $produit)
			{
				if($count >= $_POST['page']*$nb_elem && $count < ($_POST['page']*$nb_elem + $nb_elem))
				{
					$font = '#aaa';
					$back = 'black';
					if(isset($ARBO['child'][ $produit['arborescence'] ]))
					{
						$font = $ARBO['child'][ $produit['arborescence'] ]['fontcolor'];
						$back = $ARBO['child'][ $produit['arborescence'] ]['backcolor'];
					}
				
					if($running_count == 0)
					{
						$res .= '<tr>';
					}
					else
					{
						if($running_count == $nb_elem / $BORNE_NB_ELEM_LIG)
						{
							$res .= '</tr><tr>';
							$running_count = 0;
						}
					}
					
					$image = '../images/no_photo.png';
					if($produit['image'] != '' && file_exists($PATH_IMAGES.$produit['image']))
						$image = $PATH_IMAGES.$produit['image'];
					
					$res .= '<td valign="top">';
					$res .= '<div align="center" style="margin-top:10px;">';
					$res .= '<div class="fiche_produit" onclick="select_produit(\''.$id.'\',\'promo\',\'0\');">';
					$res .= '<img class="product_img" src="'.$image.'"></img>';
					$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/basket'.(($produit['volume']==0)?'_error':'').'.png"></img> '.$produit['name'].'</div>';
					$res .= '</div>';
					$res .= '</div>';
					$res .= '</td>';
					
					$running_count ++;
				}
				$count ++;
			}
			if($running_count > 0)
				$res .= '</tr>';
			$res .= '</table>';
			$res .= '</div>';
			
			$max_page = 1;
			if(ceil(sizeof($PRODUITS) / $nb_elem) > 1)
				$max_page = ceil(sizeof($PRODUITS) / $nb_elem);
				
			$old = "-1";
			if($_POST['parent'] != '0')
				$old = $ARBO['child'][ $_POST['parent'] ]['parent'];
			
			$s = (sizeof($PRODUITS)>1)?'s':'';
			$res .= '<input type="hidden" id="current_rubrique" value="'.$_POST['parent'].'"></input>';
			$res .= '<input type="hidden" id="rubrique_content_old_parent" value="'.$old.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_name" value="'.utf8_decode($rubrique_name).'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_font" value="'.$rubrique_font.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_back" value="'.$rubrique_back.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_count" value="'.sizeof($PRODUITS).' produit'.$s.' trouv&eacute;'.$s.'"></input>';
			$res .= '<input type="hidden" id="current_page" value="'.$_POST['page'].'"></input>';
			$res .= '<input type="hidden" id="max_page" value="'.$max_page.'"></input>';

			echo utf8_encode($res);
		break;
		case 'get_produit_random' :
			$res = '';
		
			$ARBO = get_arbo( 'PRODUIT' , false );
			
			$rubrique_name = 'Accueil';
			
			$LIST_ID = array();
			if(isset($ARBO['child'][ 0 ]))
			{
				$rubrique_name = $ARBO['child'][ 0 ]['name'];
				$LIST_ID[ $_POST['parent'] ] = $ARBO['child'][ 0 ]['name'];
			}
			$LIST_ID = get_child_list( $ARBO, 0, $LIST_ID );
			
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
										produit_status = 0
										'.$list_id.'
										and produit_priorite > 0
										and produit_promo = 1
									order by
										priorite,
										produit_name');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$PRODUITS[] = array(
											'id'			=>	$row['produit_id'],
											'name'			=>	$row['produit_name'],
											'code'			=>	$row['produit_code'],
											'status'		=>	$row['produit_status'],
											'priorite'		=>	$row['produit_priorite'],
											'volume'		=>	$row['produit_volume'],
											'poids'			=>	$row['produit_poids'],
											'image'			=>	$row['produit_image'],
											'description'	=>	$row['produit_description'],
											'arborescence'	=>	$row['produit_arborescence']
										);
				}
			}
			mysql_free_result($result);
			
			$list_produit = array();
			while(sizeof($list_produit) < $BORNE_NB_ELEM_RDM && sizeof($list_produit) < sizeof($PRODUITS))
			{
				$random = rand(0,sizeof($PRODUITS) - 1);
				if(!isset( $list_produit[ $random ] ))
					$list_produit[ $random ] = $PRODUITS[ $random ];
			}
			
			$res .= '<div align="center">';
			$res .= '<table id="table_produits" style="width:100%;"><tr>';
			foreach($list_produit as $produit)
			{
				$font = '#aaa';
				$back = 'black';
				if(isset($ARBO['child'][ $produit['arborescence'] ]))
				{
					$font = $ARBO['child'][ $produit['arborescence'] ]['fontcolor'];
					$back = $ARBO['child'][ $produit['arborescence'] ]['backcolor'];
				}
				
				$image = '../images/no_photo.png';
				if($produit['image'] != '' && file_exists($PATH_IMAGES.$produit['image']))
					$image = $PATH_IMAGES.$produit['image'];
				
				$res .= '<td valign="top">';
				$res .= '<div align="center" style="margin-top:10px;">';
				$res .= '<div class="fiche_produit" style="background-color:#ccc;" onclick="time=10;select_produit(\''.$produit['id'].'\',\'random\');">';
				$res .= '<img class="product_img" src="'.$image.'"></img>';
				$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/basket'.(($produit['volume']==0)?'_error':'').'.png"></img> '.$produit['name'].'</div>';
				$res .= '</div>';
				$res .= '</div>';
				$res .= '</td>';
			}
			$res .= '</tr></table>';
			$res .= '</div>';

			echo utf8_encode($res);
		break;		
		case 'get_produit_selected' :
			session_start();
			$res = '';
			$count = 0;
			$list_id = '';
			$recette = array();
			$nb_recette = 0;
			$list_recette = '';
			
			$info_produits = array();
			$result = mysql_query('select * from produit 
									where
										produit_status = 0
									');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$info_produits[ $row['produit_id'] ] = array(
																	'name'	=>	$row['produit_name']
																);
				}
			}
			mysql_free_result($result);
			
			if(!isset($_SESSION['produit_selected']))
				$_SESSION['produit_selected'] = array();
			
			$produits = $_SESSION['produit_selected'];
			$res .= '<table>';
			foreach($produits as $key => $val)
			{
				if(isset($info_produits[ $key ]))
				{
					$res .= '<tr>';
					$res .= '<td class="produit_selected_name">'.$info_produits[ $key ]['name'].'</td>';
					$res .= '<td><img style="cursor:pointer;" src="../images/tick.png" onclick="delete_selected_produit(\''.$key.'\');"></img></td>';
					$res .= '</tr>';
				
					if($list_id != '')
						$list_id .= ',';
					$list_id .= $key;
					$count ++;
				}
			}
			$res .= '</table>';
			
			$title = 'Aucun produit s&eacute;lectionn&eacute;';
			if($count > 0)
			{
				$title = 'Mes produits s&eacute;lectionn&eacute;s';
				
				$result = mysql_query('select * from recette natural join recette_ingredient
										where
											recette_status = 0
											and
											recette_ingredient_status = 0
											and
											produit_id IN ('.$list_id.')');
				echo mysql_error();
				if(mysql_num_rows($result)>0)
				{
					while($row = mysql_fetch_assoc($result))
					{
						if(!isset($recette[ $row['recette_id'] ]))
							$recette[ $row['recette_id'] ] = array();
						$recette[ $row['recette_id'] ][ $row['produit_id'] ] = 1;
					}
				}
				mysql_free_result($result);
				
				foreach($recette as $key => $val)
				{
					if(sizeof($val) == $count)
					{
						$nb_recette ++;
						if($list_recette != '')
							$list_recette .= ',';
						$list_recette .= $key;
					}
				}
				
				if($nb_recette > 0)
				{
					$res .= '<div id="produit_selected_result" onclick="view_recette_list(\''.$list_recette.'\',\'\')">';
					$res .= $nb_recette.' recette'.(($nb_recette>1)?'s':'').' trouv&eacute;e'.(($nb_recette>1)?'s':'');
					$res .= '<img src="../images/search.png" style="margin-left:5px;"></img>';
					$res .= '</div>';
				}
			}
			
			$res = '<div id="produit_selected_title">'.$title.'</div>'.$res;
			
			echo utf8_encode($res);
		break;
		case 'select_produit' :
			session_start();
			
			if(!isset($_SESSION['produit_selected']) || $_POST['source'] == 'recette')
				$_SESSION['produit_selected'] = array();
				
			if(!isset($_SESSION['produit_selected'][ $_POST['id'] ]))
				$_SESSION['produit_selected'][ $_POST['id'] ] = time();
		break;
		case 'delete_selected_produit' :
			session_start();
			
			if(!isset($_SESSION['produit_selected']))
				$_SESSION['produit_selected'] = array();
				
			$new_session = array();
			foreach($_SESSION['produit_selected'] as $key => $val)
			{
				if($key != $_POST['id'])
					$new_session[ $key ] = $val;
			}
			
			$_SESSION['produit_selected'] = $new_session;
		break;
	}
}

?>