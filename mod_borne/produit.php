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
			$running_count = 0;
			$limit = -1;
			
			if($_POST['page'] == "0" && $_POST['parent'] != "0")
			{
				$result = mysql_query('select * from arborescence where arborescence_id = "'.$_POST['parent'].'"');
				echo mysql_error();
				if(mysql_num_rows($result)>0)
				{
					$row = mysql_fetch_assoc($result);
					trace_log( 'RUBRIQUE' , $row['arborescence_code'] , 'Navigation par '.$_POST['type'] );
				}
				mysql_free_result($result);
			}
			
			$PATH_IMAGES = '../ftp_images/rubriques/';
			
			$rubrique_name = 'Accueil';
			$rubrique_font = 'black';
			$rubrique_back = 'white';
			
			$nb_elem = $BORNE_NB_ELEM_COL * $BORNE_NB_ELEM_LIG;
			
			$ARBO = get_arbo( $_POST['type'] , false );
			
			$PWD = array();
			if(isset($ARBO['child'][ $_POST['parent'] ]))
			{
				$parent = $ARBO['child'][ $_POST['parent'] ]['id']; 
				while($parent != 0)
				{
					$PWD[] = array( 'id' => $ARBO['child'][ $parent ]['id'] , 'name' => $ARBO['child'][ $parent ]['name'] , 'back' => $ARBO['child'][ $parent ]['backcolor'] , 'font' => $ARBO['child'][ $parent ]['fontcolor'] );
					$parent = $ARBO['child'][ $parent ]['parent']; 
				}
			}
			$PWD[] = array( 'id' => 0 , 'name' => 'Accueil' , 'back' => 'white' , 'font' => 'black' );
			
			$ELEM = array();
			$LIST_ID = array();
			if(isset($ARBO['child'][ $_POST['parent'] ]))
			{
				$rubrique_name = $ARBO['child'][ $_POST['parent'] ]['name'];
				$rubrique_font = $ARBO['child'][ $_POST['parent'] ]['fontcolor'];
				$rubrique_back = $ARBO['child'][ $_POST['parent'] ]['backcolor'];
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
				$list_id = ' and '.strtolower($_POST['type']).'_arborescence IN ('.$list_id.') ';				
			
			$title = '';
			switch($_POST['type'])
			{
				case 'RECETTE' : $title = 'Recherche par th&egrave;mes '; break;
				case 'PRODUIT' : $title = 'Recherche par produits '; break;
			}
			
			$res .= '<div class="content_header">'.$title.' | Sous-rubriques :</div>';
			$res .= '<div><table><tr>';
			for($i = sizeof($PWD) - 1 ; $i >= 0 ; $i -- )
			{
				if($i < sizeof($PWD) - 1)
					$res .= '<td><div style="font-weight:bold;cursor:default;color:#aaa;font-size:20px;"> - </div></td>';
				$res .= '<td><div onclick="get_rubrique(\''.$PWD[$i]['id'].'\',0);" class="fiche_rubrique_name" style="cursor:pointer;background-color:'.$PWD[$i]['back'].'"><table><tr><td><img src="../images/folder_go.png"></img></td><td style="color:'.$PWD[$i]['font'].'">'.$PWD[$i]['name'].'</td></tr></table></div></td>';
			}
			$res .= '</tr></table></div>';
			$res .= '<div align="center">';
			$res .= '<table id="table_produits" style="width:100%;">';
			if(isset($ARBO['parent'][ $_POST['parent'] ]))
			{				
				foreach($ARBO['parent'][ $_POST['parent'] ] as $id => $arbo)
				{
					if($count >= $_POST['page']*$nb_elem && $count < ($_POST['page']*$nb_elem + $nb_elem))
					{
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
						if($arbo['image'] != '' && file_exists($PATH_IMAGES.$arbo['image']))
							$image = $PATH_IMAGES.$arbo['image'];
						
						$res .= '<td valign="top">';
						$res .= '<div align="center" style="margin-top:10px;">';
						$res .= '<div class="fiche_produit" onclick="get_rubrique(\''.$arbo['id'].'\',0);">';
						$res .= '<img class="product_img" src="'.$image.'"></img>';
						$res .= '<div class="fiche_produit_name" style="color:'.$arbo['fontcolor'].';background-color:'.$arbo['backcolor'].'"><img src="../images/arrow_sub.png"></img> '.$arbo['name'].'</div>';
						$res .= '</div>';
						$res .= '</div>';
						$res .= '</td>';
						
						$running_count ++;
					}
					$count ++;
				}
			}

			if($count > 0)
			{
				$current_page = floor($count / $nb_elem) + 1;
				$max_elem_page = $current_page * $nb_elem;

				while($count < $max_elem_page)
				{
					if($count >= $_POST['page']*$nb_elem && $count < ($_POST['page']*$nb_elem + $nb_elem))
					{
						if($running_count == 0)
						{
							$res .= '<tr>';
						}
						else
						{
							if($running_count == $nb_elem / 3)
							{
								$res .= '</tr><tr>';
								$running_count = 0;
							}
						}
						
						$res .= '<td></td>';
						
						$running_count ++;
					}
					$count ++;
				}
			}
			
			switch($_POST['type'])
			{
				case 'PRODUIT' :
					$PATH_IMAGES = '../ftp_images/produits/';
					
					$PRODUITS = array();
					$result = mysql_query('select *,
												if(produit_priorite = 0,987654321,produit_priorite) as priorite
											from produit
											where
												produit_status = 0
												'.$list_id.'
												and produit_priorite > 0
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
					
					$ELEM = $PRODUITS;
			
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
							$res .= '<div class="fiche_produit" onclick="select_produit(\''.$id.'\',\'produit\',\''.$_POST['parent'].'\');">';
							$res .= '<img class="product_img" src="'.$image.'"></img>';
							$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/basket'.(($produit['volume']==0)?'_error':'').'.png"></img> '.utf8_encode($produit['name']).'</div>';
							$res .= '</div>';
							$res .= '</div>';
							$res .= '</td>';
							
							$running_count ++;
						}
						$count ++;
					}
				break;
				case 'RECETTE' :
					$PATH_IMAGES = '../ftp_images/recettes/';
					
					$RECETTE_ERROR = array();
					//POUR SUPPRIMER LES PICTOGRAMMES ?
					/*$result = mysql_query('select recette_id
										from 
											recette_ingredient
											left join produit on produit.produit_id = recette_ingredient.produit_id
										where
											recette_ingredient_status = 0
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
					mysql_free_result($result);*/					
	
					$RECETTES = array();
					$result = mysql_query('select *,
												if(recette_priorite = 0,987654321,recette_priorite) as priorite
											from recette
											where
												recette_status = 0
												'.$list_id.'
												and recette_priorite > 0
											order by
												priorite,
												recette_name');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{
							$RECETTES[ $row['recette_id'] ] = array(
																	'name'			=>	$row['recette_name'],
																	'code'			=>	$row['recette_code'],
																	'status'		=>	$row['recette_status'],
																	'priorite'		=>	$row['recette_priorite'],
																	'image'			=>	$row['recette_image'],
																	'description'	=>	$row['recette_description'],
																	'arborescence'	=>	$row['recette_arborescence']
																);
						}
					}
					mysql_free_result($result);
					
					$ELEM = $RECETTES;
					
					foreach($RECETTES as $id => $recette)
					{
						if($count >= $_POST['page']*$nb_elem && $count < ($_POST['page']*$nb_elem + $nb_elem))
						{
							$font = '#aaa';
							$back = 'black';
							if(isset($ARBO['child'][ $recette['arborescence'] ]))
							{
								$font = $ARBO['child'][ $recette['arborescence'] ]['fontcolor'];
								$back = $ARBO['child'][ $recette['arborescence'] ]['backcolor'];
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
							if($recette['image'] != '' && file_exists($PATH_IMAGES.$recette['image']))
								$image = $PATH_IMAGES.$recette['image'];
							
							$res .= '<td valign="top">';
							$res .= '<div align="center" style="margin-top:10px;">';
							$res .= '<div class="fiche_produit" style="height:100%;" onclick="show_fiche_recette(\''.$id.'\');">';
							$res .= '<img class="product_img" src="'.$image.'"></img>';
							$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/script'.((isset($RECETTE_ERROR[$id]))?'_error':'').'.png"></img> '.utf8_encode($recette['name']).'</div>';
							$res .= '</div>';
							$res .= '</div>';
							$res .= '</td>';
							
							$running_count ++;
						}
						$count ++;
					}
				break;
			}
			
			if($running_count > 0)
				$res .= '</tr>';
			$res .= '</table>';
			$res .= '</div>';
			
			$old = "-1";
			if($_POST['parent'] != '0')
				$old = $ARBO['child'][ $_POST['parent'] ]['parent'];
				
			$max_page = 1;
			if(ceil($count / $nb_elem) > 1)
				$max_page = ceil($count / $nb_elem);

			$s = (sizeof($ELEM)>1)?'s':'';

			$res .= '<input type="hidden" id="current_rubrique" value="'.$_POST['parent'].'"></input>';
			$res .= '<input type="hidden" id="current_page" value="'.$_POST['page'].'"></input>';
			$res .= '<input type="hidden" id="max_page" value="'.$max_page.'"></input>';			
			$res .= '<input type="hidden" id="rubrique_content_old_parent" value="'.$old.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_name" value="'.($rubrique_name).'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_font" value="'.$rubrique_font.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_back" value="'.$rubrique_back.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_count" value="'.sizeof($ELEM).' '.strtolower($_POST['type']).$s.' trouv&eacute;'.(($_POST['type']=='RECETTE')?'e':'').$s.'"></input>';
			$res .= '<input type="hidden" id="nb_rubrique_count" value="'.$count.'"></input>';
			
			echo $res;
		break;	
		case 'get_produit' :
			$res = '';
			
			$nb_elem = $BORNE_NB_ELEM_COL * $BORNE_NB_ELEM_LIG;
		
			$ARBO = get_arbo( 'PRODUIT' , false );
			
			$PWD = array();
			if(isset($ARBO['child'][ $_POST['parent'] ]))
			{
				$parent = $ARBO['child'][ $_POST['parent'] ]['id']; 
				while($parent != 0)
				{
					$PWD[] = array( 'id' => $ARBO['child'][ $parent ]['id'] , 'name' => $ARBO['child'][ $parent ]['name'] , 'back' => $ARBO['child'][ $parent ]['backcolor'] , 'font' => $ARBO['child'][ $parent ]['fontcolor'] );
					$parent = $ARBO['child'][ $parent ]['parent']; 
				}
			}
			$PWD[] = array( 'id' => 0 , 'name' => 'Accueil' , 'back' => 'white' , 'font' => 'black' );
			
			$parent = $_POST['parent'];
			if($_POST['list_produit'] != '')
				$parent = 0;
			
			$rubrique_name = 'Accueil';
			$rubrique_font = 'black';
			$rubrique_back = 'white';
			
			$LIST_ID = array();
			if(isset($ARBO['child'][ $parent ]))
			{
				$rubrique_name = $ARBO['child'][ $parent ]['name'];
				$rubrique_font = $ARBO['child'][ $parent ]['fontcolor'];
				$rubrique_back = $ARBO['child'][ $parent ]['backcolor'];
				$LIST_ID[ $parent ] = $ARBO['child'][ $parent ]['name'];
			}
			$LIST_ID = get_child_list( $ARBO, $parent, $LIST_ID );
			
			$list_id = '';
			foreach($LIST_ID as $id => $name)
			{
				if($list_id != '')
					$list_id .= ',';
				$list_id .= $id;
			}
			if($list_id != '')
				$list_id = ' and produit_arborescence IN ('.$list_id.') ';
				
			$list_produit = '';
			if($_POST['list_produit'] != '')
				$list_produit .= ' and produit_id IN ('.$_POST['list_produit'].') ';
				
			$PRODUITS = array();
			$result = mysql_query('select *,
										if(produit_priorite = 0,987654321,produit_priorite) as priorite
									from produit
									where
										produit_status = 0
										'.$list_id.'
										'.$list_produit.'
										and produit_priorite > 0
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
			if($_POST['list_produit'] == '')
				$res .= '<div class="content_header">D&eacutetails des produits :</div>';
			else
				$res .= '<div class="content_header">On peut encore associer un des produits suivants :</div>';
			$res .= '<div><table><tr>';
			for($i = sizeof($PWD) - 1 ; $i >= 0 ; $i -- )
			{
				if($i < sizeof($PWD) - 1)
					$res .= '<td><div style="font-weight:bold;cursor:default;color:#aaa;font-size:20px;"> - </div></td>';
				$res .= '<td><div onclick="get_rubrique(\''.$PWD[$i]['id'].'\',0);" class="fiche_rubrique_name" style="cursor:pointer;background-color:'.$PWD[$i]['back'].'"><table><tr><td><img src="../images/folder_go.png"></img></td><td style="color:'.$PWD[$i]['font'].'">'.utf8_decode($PWD[$i]['name']).'</td></tr></table></div></td>';
			}
			$res .= '</tr></table></div>';
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
					$res .= '<div class="fiche_produit" onclick="select_produit(\''.$id.'\',\'produit\',\''.$_POST['parent'].'\');">';
					$res .= '<img class="product_img" src="'.$image.'"></img>';
					//$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/basket'.(($produit['volume']==0)?'_error':'').'.png"></img> '.apply_dictionary($produit['name']).'</div>'; // >>>> AVEC PICTOGRAMME
					$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/basket.png"></img> '.apply_dictionary($produit['name']).'</div>';
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
				$res .= '<div align="center">';
				$res .= '<div class="fiche_produit" onclick="time=10;select_produit(\''.$produit['id'].'\',\'random\',\'0\');">';
				$res .= '<img class="product_img" src="'.$image.'"></img>';
				$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/basket'.(($produit['volume']==0)?'_error':'').'.png"></img> '.apply_dictionary($produit['name']).'</div>';
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
			
			$info_recettes = array();
			$result = mysql_query('select * from recette
									join arborescence on recette.recette_arborescence = arborescence.arborescence_id
									where
										recette_status = 0
										and arborescence_status = 0
										and arborescence_type = "RECETTE"
									');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$info_recettes[ $row['recette_id'] ] = array(
																	'name'	=>	$row['recette_name'],
																	'font'	=>	$row['arborescence_fontcolor'],
																	'back'	=>	$row['arborescence_backcolor']
																);
				}
			}
			mysql_free_result($result);
			
			if(!isset($_SESSION['produit_selected']))
				$_SESSION['produit_selected'] = array();
			
			$produits = $_SESSION['produit_selected'];
			
			if(!isset($_SESSION['recette_selected']))
				$_SESSION['recette_selected'] = array();
				
			$recettes = $_SESSION['recette_selected'];
			
			if(sizeof($produits)>0)
			{
				$res .= '<table>';
				foreach($produits as $key => $val)
				{
					if(isset($info_produits[ $key ]))
					{
						$res .= '<tr>';
						$res .= '<td class="produit_selected_name">'.apply_dictionary($info_produits[ $key ]['name']).'</td>';
						$res .= '<td><img style="cursor:pointer;" class="tick" src="../images/tick.png" onclick="delete_selected_produit(\''.$key.'\');"></img></td>';
						$res .= '</tr>';
					
						if($list_id != '')
							$list_id .= ',';
						$list_id .= $key;
						$count ++;
					}
				}
				$res .= '</table>';
			}
				
			$title = '';//'Aucun produit s&eacute;lectionn&eacute;';
			if($count > 0)
			{
				$title = 'Mes produits s&eacute;lectionn&eacute;s';
				
				$result = mysql_query('select * from recette natural join recette_ingredient
										where
											recette_status = 0
											and
											recette_ingredient_status != 1
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
			
			if(sizeof($produits)>0)
				$res = '<div class="selected_title" id="produit_selected_title">'.$title.'</div>'.$res;
			
			$go_to_recette = '';
			if($nb_recette == 1 || ($nb_recette >= 1 && $_POST['refresh']==2))
				$go_to_recette = $list_recette;

			$res .= '<input type="hidden" id="go_to_recette" value="'.$go_to_recette.'"></input>';
			$res .= '<input type="hidden" id="produit_selected_count" value="'.$count.'"></input>';
			
			$nb_produit = $count;
			$count = 0;
			
			$title = '';//'Aucune recette s&eacute;lectionn&eacute;e';
			if(sizeof($recettes) > 0)
			{
				$title = 'Mes recettes s&eacute;lectionn&eacute;es';
			
				$res .= '<div class="selected_title" id="recette_selected_title">'.$title.'</div>';
				
				$res .= '<table>';
				foreach($recettes as $key => $val)
				{
					if(isset($info_recettes[ $key ]))
					{
						$res .= '<tr>';
						$res .= '<td><div class="fiche_recette_name" onclick="show_fiche_recette(\''.$key.'\');" style="cursor:pointer;background-color:'.$info_recettes[ $key ]['back'].';color:'.$info_recettes[ $key ]['font'].';">'.apply_dictionary($info_recettes[ $key ]['name']).'</div></td>';
						$res .= '<td><img style="cursor:pointer;" class="tick" src="../images/tick.png" onclick="delete_selected_recette(\''.$key.'\');"></img></td>';
						$res .= '</tr>';
						
						$count ++;
					}
				}
				$res .= '</table>';
			}
			
			if($nb_produit > 0 || $count > 0)
			{
				$res .= '<div align="center" class="selected_title_clickable" style="cursor:pointer;" onclick="reset_list_selected(1);">
							<table>
								<tr>
									<td>
										<img src="../images/table_delete.png"></img>
									</td>
									<td class="selected_title">Effacer s&eacute;lection</td>
								</tr>
							</table>
						</div>';
			}
			
			$res .= '<input type="hidden" id="recette_selected_count" value="'.$count.'"></input>';
			
			echo utf8_encode($res);
		break;
		case 'reset_list_selected' :
			session_start();
			
			$_SESSION['produit_selected'] = array();
			$_SESSION['recette_selected'] = array();
		break;
		case 'select_produit' :
			session_start();
			
			$result = mysql_query('select * from produit where produit_id = "'.$_POST['id'].'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				$row = mysql_fetch_assoc($result);
				trace_log( 'PRODUIT' , $row['produit_code'] , '' );
			}
			mysql_free_result($result);
			
			if(!isset($_SESSION['produit_selected']))
				$_SESSION['produit_selected'] = array();
			
			if($_POST['id'] != "0")
			{
				if(!isset($_SESSION['produit_selected'][ $_POST['id'] ]))
					$_SESSION['produit_selected'][ $_POST['id'] ] = time();
			}
				
			$list_selected_produit = '-1';
			foreach($_SESSION['produit_selected'] as $key => $val)
			{
				if($list_selected_produit != '')
					$list_selected_produit .= ',';
				$list_selected_produit .= $key;
			}

			$ARBO = get_arbo( 'RECETTE' , false );
			
			$LIST_ID = array();
			$LIST_ID = get_child_list( $ARBO, 0, $LIST_ID );
			
			$list_id = '-1';
			foreach($LIST_ID as $id => $name)
			{
				if($list_id != '')
					$list_id .= ',';
				$list_id .= $id;
			}
			if($list_id != '')
				$list_id = ' and recette_arborescence IN ('.$list_id.') ';
				
			$list_recette = '';
			$result = mysql_query('select recette_id
									from recette
									where
										recette_status = 0
										'.$list_id.'
										and recette_priorite > 0');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if($list_recette != '')
						$list_recette .= ',';
					$list_recette .= $row['recette_id'];
				}
			}
			mysql_free_result($result);
			
			$ARBO = get_arbo( 'PRODUIT' , false );
			
			$LIST_ID = array();
			$LIST_ID = get_child_list( $ARBO, 0, $LIST_ID );
			
			$list_id = '-1';
			foreach($LIST_ID as $id => $name)
			{
				if($list_id != '')
					$list_id .= ',';
				$list_id .= $id;
			}
			if($list_id != '')
				$list_id = ' and produit_arborescence IN ('.$list_id.') ';
				
			$list_produit = array();
			$result = mysql_query('select produit_id
									from produit
									where
										produit_status = 0
										'.$list_id.'
										and produit_id NOT IN ('.$list_selected_produit.')
										and produit_priorite > 0');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$list_produit[ $row['produit_id'] ] = 1;
				}
			}
			mysql_free_result($result);		

			$RECETTE = array();
			$result = mysql_query('select * from recette_ingredient
									where
										recette_id IN ('.$list_recette.')
										and
										produit_id IN ('.$list_selected_produit.')');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if(!isset($RECETTE[ $row['recette_id'] ]))
						$RECETTE[ $row['recette_id'] ] = array();
					$RECETTE[ $row['recette_id'] ][ $row['produit_id'] ] = 1;
				}
			}
			mysql_free_result($result);
			
			$list_recette = '-1';
			foreach($RECETTE as $recette_key => $produit)
			{
				$ok = 0;
				foreach($produit as $produit_key => $val)
				{
					if(isset($_SESSION['produit_selected'][ $produit_key ]))
						$ok ++;
				}
				if($ok >= sizeof($_SESSION['produit_selected']))
					$list_recette .= ','.$recette_key;
			}

			$list_produit_found = '';			
			$result = mysql_query('select produit_id from recette_ingredient
									where
										recette_id IN ('.$list_recette.')
										and
										produit_id != "0"
										and
										produit_id NOT IN ('.$list_selected_produit.')');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if(isset($list_produit[ $row['produit_id'] ]))
					{
						if($list_produit_found != '')
							$list_produit_found .= ',';
						$list_produit_found .= $row['produit_id'];
					}
				}
			}
			mysql_free_result($result);
			
			echo '<input type="hidden" id="list_produit_found" value="'.$list_produit_found.'"></input>';
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
