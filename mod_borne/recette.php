<?php

include('../include/main.php');

$PATH_IMAGES .= 'recettes/';

if(isset($_GET['do']))
{
	switch($_GET['do'])
	{
		case 'print_list' :
			session_start();
			$res = '';
			$count = 0;
			$list_id = '';
			$recette = array();
			$nb_recette = 0;
			$list_recette = '';
			
			$ARBO = get_arbo( 'PRODUIT', false );
			
			$LIST_ID = array();
			if(isset($ARBO['child'][ 0 ]))
				$LIST_ID[ $_POST['id'] ] = $ARBO['child'][ 0 ]['name'];
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
			$sql = 'select *,
							if(produit_priorite = 0,987654321,produit_priorite) as priorite
						from produit
						where
							produit_status != "1"
							'.$list_id;
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
																	'code'		=>	$row['recette_code'],
																	'name'		=>	$row['recette_name'],
																	'font'		=>	$row['arborescence_fontcolor'],
																	'back'		=>	$row['arborescence_backcolor'],
																	'nb_people'	=>	$row['recette_info_nb_people']
																);
				}
			}
			mysql_free_result($result);
			
			$ingredients = array();
			$result = mysql_query('select * from recette_ingredient where recette_ingredient_status = 0 order by recette_ingredient_priorite');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if(!isset($ingredients[ $row['recette_id'] ]))
						$ingredients[ $row['recette_id'] ] = array();
					$ingredients[ $row['recette_id'] ][] = array(
						'produit'	=>	$row['produit_id'],
						'details'	=>	$row['recette_ingredient_name']
					);
				}
			}
			mysql_free_result($result);
			
			if(!isset($_SESSION['recette_selected']))
				$_SESSION['recette_selected'] = array();
				
			$recettes = $_SESSION['recette_selected'];
						
			$produits = array();
			
			if(sizeof($recettes) > 0)
			{
				$res .= "Ingrédients pour :\r\n";
				
				foreach($recettes as $key => $val)
				{
					if(isset($info_recettes[ $key ]))
					{
						trace_log('BORNE' , $info_recettes[ $key ]['code'] , 'Impression liste ingredients');
						
						$res .= "\r\n * ".$info_recettes[ $key ]['name'].' ('.(($info_recettes[ $key ]['nb_people']>0)?(($info_recettes[ $key ]['nb_people']>1)?$info_recettes[ $key ]['nb_people']." personnes":"1 personne"):"").")\r\n";
						
						if(isset($ingredients[ $key ]))
						{
							foreach($ingredients[ $key ] as $key => $val)
							{
								$product_name 		= $val['details'];
								$product_details 	= '';
								if(isset($PRODUITS[ $val['produit'] ]))
								{
									$product_details 	= $product_name;
									$product_name 		= $PRODUITS[ $val['produit'] ]['name'];	
								}
								
								if(!isset($produits[ $product_name ]))
									$produits[ $product_name ] = '';
								if($product_details != '')
								{
									if($produits[ $product_name ] != '')
										$produits[ $product_name ] .= ' + ';
									$produits[ $product_name ] .= $product_details;
								}
							}
						}
					}
				}
				$res .= "\r\n";
				
				if(sizeof($produits)>0)
				{
					$res .= "			---\r\n\r\n";
	
					foreach($produits as $key => $val)
					{
						$res .= ' - '.$key;
						if($val != '')
							$res .= ' ( '.$val.' )';
						$res .= "\r\n";
					}
				}
				
				$res .= "\r\n";
				$res .= date('d-m-Y');				
				switch(date('w'))
				{
					case 0 : $res .= ' DIMANCHE'; break;
					case 1 : $res .= ' LUNDI'; break;
					case 2 : $res .= ' MARDI'; break;
					case 3 : $res .= ' MERCREDI'; break;
					case 4 : $res .= ' JEUDI'; break;
					case 5 : $res .= ' VENDREDI'; break;
					case 6 : $res .= ' SAMEDI'; break;
				}
				$res .= "\r\n";
				$res .= 'MERCI ET À BIENTÔT';
			}
			
			$f = fopen('../export/export.txt','w');
			fwrite($f,$res);
			fclose($f);

			header("Content-disposition: attachment; filename=liste_ingredients.txt"); 
			header("Content-Type: text/plain"); 
			header("Content-Length: ".filesize( '../export/export.txt' )); 
			header("Pragma: no-cache"); 
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public"); 
			header("Expires: 0"); 
			readfile( '../export/export.txt' );
		break;
	}
	exit();
}

if(isset($_POST['do']))
{
	switch($_POST['do'])
	{
		case 'trace_log_page' :
			$page = '';
			switch($_POST['page'])
			{
				case 'recette_front.php' :
					$page = 'THEME';
				break;
				case 'produit_front.php' :
					$page = 'PRODUIT';
				break;
				case 'produit_promo_front.php' :
					$page = 'PROMOTION';
				break;
			}
			
			trace_log('BORNE',$page,'Utilisation de la borne par un utilisateur');
		break;
		case 'get_recette' :
			$res = '';
			
			$nb_elem = $BORNE_NB_ELEM_COL * $BORNE_NB_ELEM_LIG;
		
			$ARBO = get_arbo( 'RECETTE' , false );
			
			$rubrique_name = 'Accueil';
			$rubrique_font = 'black';
			$rubrique_back = 'white';
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
				$list_id = ' and recette_arborescence IN ('.$list_id.') ';
			
			$list_recette = '';
			if($_POST['list_recette']!='')
			{
				$list_id = '';
				$list_recette .= ' and recette_id IN ('.$_POST['list_recette'].') ';
			}
			
			$RECETTE_ERROR = array();
			$result = mysql_query('select recette_id
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
			mysql_free_result($result);	
				
			$RECETTES = array();
			$result = mysql_query('select *,
										if(recette_priorite = 0,987654321,recette_priorite) as priorite
									from recette
									where
										recette_status = 0
										'.$list_id.'
										'.$list_recette.'
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
			
			$count = 0;
			$running_count = 0;
			
			if($list_recette == '')
				$res .= '<div class="content_header">D&eacute;tails des recettes :</div>';
			else
				$res .= '<div class="content_header">Recettes que vous pourrez r&eacute;aliser avec vos produits :</div>';
			
			if($_POST['list_recette']!='')
			{			
				$PWD[] = array( 'id' => 0 , 'name' => 'Accueil' , 'back' => 'white' , 'font' => 'black' );
				$res .= '<div><table><tr>';
				for($i = sizeof($PWD) - 1 ; $i >= 0 ; $i -- )
				{
					if($i < sizeof($PWD) - 1)
						$res .= '<td><div style="font-weight:bold;cursor:default;color:#aaa;font-size:20px;"> - </div></td>';
					$res .= '<td><div onclick="get_rubrique(\''.$PWD[$i]['id'].'\',0);" class="fiche_rubrique_name" style="cursor:pointer;background-color:'.$PWD[$i]['back'].'"><table><tr><td><img src="../images/folder_go.png"></img></td><td style="color:'.$PWD[$i]['font'].'">'.utf8_decode($PWD[$i]['name']).'</td></tr></table></div></td>';
				}
				$res .= '</tr></table></div>';
			}
			
			$res .= '<div align="center">';
			$res .= '<table id="table_recettes" style="width:100%;">';
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
					$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/script'.((isset($RECETTE_ERROR[$id]))?'_error':'').'.png"></img> '.apply_dictionary($recette['name']).'</div>'; //<<<< AVEC PICTOGRAMME RECETTE
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
			if(ceil(sizeof($RECETTES) / $nb_elem) > 1)
				$max_page = ceil(sizeof($RECETTES) / $nb_elem);
				
			$old = "-1";
			if($_POST['parent'] != '0')
				$old = $ARBO['child'][ $_POST['parent'] ]['parent'];

			$s = (sizeof($RECETTES)>1)?'s':'';
			$res .= '<input type="hidden" id="current_rubrique" value="'.$_POST['parent'].'"></input>';
			$res .= '<input type="hidden" id="rubrique_content_old_parent" value="'.$old.'"></input>';
			$res .= '<input type="hidden" id="current_list_recette" value="'.$_POST['list_recette'].'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_name" value="'.utf8_decode($rubrique_name).'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_font" value="'.$rubrique_font.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_back" value="'.$rubrique_back.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_count" value="'.sizeof($RECETTES).' recette'.$s.' trouv&eacute;e'.$s.'"></input>';
			$res .= '<input type="hidden" id="current_page" value="'.$_POST['page'].'"></input>';
			$res .= '<input type="hidden" id="max_page" value="'.$max_page.'"></input>';
			
			echo utf8_encode($res);
		break;
		case 'get_recette_random' :
			$res = '';
			
			$ARBO = get_arbo( 'RECETTE' , false );
			
			$rubrique_name = 'Accueil';
			
			$LIST_ID = array();
			if(isset($ARBO['child'][ 0 ]))
			{
				$rubrique_name = $ARBO['child'][ $_POST['parent'] ]['name'];
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
				$list_id = ' and recette_arborescence IN ('.$list_id.') ';
				
			$RECETTE_ERROR = array();
			$result = mysql_query('select recette_id
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
			mysql_free_result($result);	
				
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
					$RECETTES[] = array(
											'id'			=>	$row['recette_id'],
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
			
			$list_recette = array();
			while(sizeof($list_recette) < $BORNE_NB_ELEM_RDM && sizeof($list_recette) < sizeof($RECETTES))
			{
				$random = rand(0,sizeof($RECETTES) - 1);
				if(!isset( $list_recette[ $random ] ))
					$list_recette[ $random ] = $RECETTES[ $random ];
			}
			
			$res .= '<div align="center">';
			$res .= '<table id="table_recettes" style="width:100%;"><tr>';
			foreach($list_recette as $recette)
			{					
				$image = '../images/no_photo.png';
				if($recette['image'] != '' && file_exists($PATH_IMAGES.$recette['image']))
					$image = $PATH_IMAGES.$recette['image'];
					
				$font = '#aaa';
				$back = 'black';
				if(isset($ARBO['child'][ $recette['arborescence'] ]))
				{
					$font = $ARBO['child'][ $recette['arborescence'] ]['fontcolor'];
					$back = $ARBO['child'][ $recette['arborescence'] ]['backcolor'];
				}
				
				$res .= '<td valign="top">';
				$res .= '<div align="center" style="margin-top:10px;">';
				$res .= '<div class="fiche_produit" style="height:100%;background-color:#ccc;" onclick="show_fiche_recette(\''.$recette['id'].'\');">';
				$res .= '<img class="product_img" src="'.$image.'"></img>';
				$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/script'.((isset($RECETTE_ERROR[$recette['id']]))?'_error':'').'.png"></img> '.apply_dictionary($recette['name']).'</div>'; //<<<< AVEC PICTOGRAMME 
				$res .= '</div>';
				$res .= '</div>';
				$res .= '</td>';
			}
			$res .= '</tr></table>';
			$res .= '</div>';
			
			echo utf8_encode($res);
		break;
		case 'print_recette' :
			$result = mysql_query('select * from recette
									where
										recette_id = "'.$_POST['id'].'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{					
					trace_log( 'IMPRESSION RECETTE' , $row['recette_code'] , '' );
					
					$logo = '';
					$result_logo = mysql_query('select * from logo natural join recette_logo where
													recette_logo_status = 0
													and
													logo_status = 0
													and
													recette_id = "'.$_POST['id'].'"
												order by
													recette_logo_priorite
													');
					echo mysql_error();
					if(mysql_num_rows($result_logo)>0)
					{
						while($row_logo = mysql_fetch_assoc($result_logo))
						{
							trace_log( 'IMPRESSION LOGO' , $row_logo['logo_code'] , 'Recette : '.$row['recette_code'] );
						}
					}
					mysql_free_result($result_logo);
					
					$coupon = '';
					$result_coupon = mysql_query('select *
													from coupon natural join recette_coupon 
												where
													recette_coupon_status = 0
													and
													coupon_status = 0
													and
													recette_id = "'.$_POST['id'].'"
												order by
													recette_coupon_priorite
													');
					echo mysql_error();
					if(mysql_num_rows($result_coupon)>0)
					{
						while($row_coupon = mysql_fetch_assoc($result_coupon))
						{
							trace_log( 'IMPRESSION COUPON' , $row_coupon['coupon_code'] , 'Recette : '.$row['recette_code'] );
						}
					}
					mysql_free_result($result_coupon);
				}
			}
			mysql_free_result($result);
		break;
		case 'get_content_recette' :
			$res = '';
			
			$ARBO = get_arbo( 'RECETTE' , false );
			
			$result = mysql_query('select * from recette
									where
										recette_id = "'.$_POST['id'].'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$rubrique_name = '';
					$rubrique_fontcolor = '';
					$rubrique_backcolor = '';
					
					trace_log( 'RECETTE' , $row['recette_code'] , '' );
					
					if(isset($ARBO['child'][ $row['recette_arborescence'] ]))
					{
						$rubrique_name = $ARBO['child'][ $row['recette_arborescence'] ]['name'];
						$rubrique_fontcolor = $ARBO['child'][ $row['recette_arborescence'] ]['fontcolor'];
						$rubrique_backcolor = $ARBO['child'][ $row['recette_arborescence'] ]['backcolor'];
					}
					
					$info_people = 'Ingr&eacute;dients pour '.$row['recette_info_nb_people'].' '.$row['recette_info_people_type'].' :';
					
					$info_preparation = '';
					if($row['recette_info_preparation'] != '')
						$info_preparation = 'Pr&eacute;paration : '.$row['recette_info_preparation'];
					
					if($row['recette_info_cuisson'] != '')
					{
						if($info_preparation != '')
							$info_preparation .= ' - ';
						$info_preparation .= 'Cuisson : '.$row['recette_info_cuisson'];
					}
					
					if($row['recette_info_complementaire'] != '')
					{
						if($info_preparation != '')
							$info_preparation .= ' - ';
						$info_preparation .= $row['recette_info_complementaire'];
					}
					
					$logo = '';
					$result_logo = mysql_query('select * from logo natural join recette_logo where
													recette_logo_status = 0
													and
													logo_status = 0
													and
													recette_id = "'.$_POST['id'].'"
												order by
													recette_logo_priorite
													');
					echo mysql_error();
					if(mysql_num_rows($result_logo)>0)
					{
						$logo .= '<table cellspacing="0"><tr>';
						while($row_logo = mysql_fetch_assoc($result_logo))
						{
							trace_log( 'LOGO' , $row_logo['logo_code'] , 'Recette : '.$row['recette_code'] );
						
							$logo_href = '';							
							if($row_logo['logo_url']!='')
							{
								if(substr(strtolower($row_logo['logo_url']),0,4)!='http')
									$row_logo['logo_url'] = 'http://'.$row_logo['logo_url'];
								$logo_href = 'onclick="trace_log(\'CLIC_LOGO\',\''.$row_logo['logo_code'].'\',\'Recette : '.$row['recette_code'].'\');open_url_timing(\''.$row_logo['logo_url'].'\');"';
							}
							
							$image = '../images/no_photo.png';
							if($row_logo['logo_image'] != '' && file_exists('../ftp_images/logos/'.$row_logo['logo_image']))
								$image = '../ftp_images/logos/'.$row_logo['logo_image'];
							
							$logo .= '<td><img class="recette_logo" '.$logo_href.' src="'.$image.'" '.(($logo_href!='')?'title="'.$row_logo['logo_url'].'"':'').' style="'.(($logo_href!='')?'cursor:pointer;':'').'max-height:75px;max-width:100px;margin-left:10px;"></img></td>';
						}
						$logo .= '</tr></table>';
					}
					mysql_free_result($result_logo);
					
					$coupon = '';
					$result_coupon = mysql_query('select *
													from coupon natural join recette_coupon 
												where
													recette_coupon_status = 0
													and
													coupon_status = 0
													and
													recette_id = "'.$_POST['id'].'"
												order by
													recette_coupon_priorite
													');
					echo mysql_error();
					if(mysql_num_rows($result_coupon)>0)
					{
						$coupon .= '<table cellspacing="0"><tr>';
						while($row_coupon = mysql_fetch_assoc($result_coupon))
						{
							trace_log( 'COUPON' , $row_coupon['coupon_code'] , 'Recette : '.$row['recette_code'] );
						
							$image = '../images/no_photo.png';
							if($row_coupon['coupon_image'] != '' && file_exists('../ftp_images/coupons/'.$row_coupon['coupon_image']))
								$image = '../ftp_images/coupons/'.$row_coupon['coupon_image'];
								
							$coupon .= '<td><img src="'.$image.'" style="max-height:75px;max-width:100px;margin-left:10px;"></img></td>';
						}
						$coupon .= '</tr></table>';
					}
					mysql_free_result($result_coupon);
					
					$produit = '';
					$PRODUITS = array();
					$result_produit = mysql_query('select *
														from produit
													where
														produit_status = 0');
					echo mysql_error();
					if(mysql_num_rows($result_produit)>0)
					{
						while($row_produit = mysql_fetch_assoc($result_produit))
						{
							$PRODUITS[ $row_produit['produit_id'] ] = array(
																		'name'			=>	$row_produit['produit_name'],
																		'code'			=>	$row_produit['produit_code'],
																		'status'		=>	$row_produit['produit_status'],
																		'priorite'		=>	$row_produit['produit_priorite'],
																		'volume'		=>	$row_produit['produit_volume'],
																		'poids'			=>	$row_produit['produit_poids'],
																		'image'			=>	$row_produit['produit_image'],
																		'description'	=>	$row_produit['produit_description'],
																		'arborescence'	=>	$row_produit['produit_arborescence']
																	);
						}
					}
					mysql_free_result($result_produit);
					$result_produit = mysql_query('select *,
														if(recette_ingredient_priorite = 0,987654321,recette_ingredient_priorite) as ingredient_priorite,
														if(recette_ingredient_categorie_priorite = 0,987654321,recette_ingredient_categorie_priorite) as categorie_priorite
													from recette_ingredient
														left join produit on recette_ingredient.produit_id = produit.produit_id
													where
														recette_id = "'.$_POST['id'].'"
														and
														recette_ingredient_status = 0
													order by
														recette_ingredient_categorie_priorite,
														recette_ingredient_categorie,
														ingredient_priorite');
					echo mysql_error();
					if(mysql_num_rows($result_produit)>0)
					{
						$old_cat = '';
						$produit .= '<table>';
						while($row_produit = mysql_fetch_assoc($result_produit))
						{
							if($row_produit['recette_ingredient_categorie'] != $old_cat)
							{
								$produit .= '<tr><td colspan="2" style="padding-left:30px;color:#333;font-size:13px;font-weight:bold;text-decoration:underline;">'.$row_produit['recette_ingredient_categorie'].'</td></tr>';
								$old_cat = $row_produit['recette_ingredient_categorie'];
							}
							
							if($row_produit['produit_id'] != "0" && isset($PRODUITS[ $row_produit['produit_id'] ]))
							{
								$produit .= '<tr style="cursor:pointer;" onclick="select_produit(\''.$row_produit['produit_id'].'\',\'recette\',\'0\');">';
								$produit .= '<td rowspan="2"><img style="max-width:40px;max-height:30px;background-color:white;border:solid 1px #aaa;" src="../ftp_images/produits/'.$PRODUITS[ $row_produit['produit_id'] ]['image'].'"</td>';
								$produit .= '<td style="font-weight:bold;font-size:15px;color:darkblue;text-decoration:underline;">'.apply_dictionary($row_produit['recette_ingredient_name']).'</td>';
								$produit .= '</tr><tr>';
								//$produit .= '<td style="font-style:italic;font-size:13px;">'.$PRODUITS[ $row_produit['produit_id'] ]['name'].'</td>';
							}
							else
							{
								$produit .= '<tr><td></td><td style="font-weight:bold;font-size:15px;">'.$row_produit['recette_ingredient_name'].'</td>';
							}
							$produit .= '</tr>';
						}
						$produit .= '</table>';
					}
					mysql_free_result($result_produit);
					
					$image = '../images/no_photo.png';
					if($row['recette_image'] != '' && file_exists($PATH_IMAGES.$row['recette_image']))
						$image = $PATH_IMAGES.$row['recette_image'];
						
					//$row['recette_details_preparation'] = str_replace('<FONT ','<F ',$row['recette_details_preparation']);
				
					$res .= '<input type="hidden" id="fiche_recette_name" value="'.apply_dictionary($row['recette_name']).'"></input>';
					$res .= '<input type="hidden" id="fiche_recette_subname" value="'.apply_dictionary($row['recette_subtitle']).'"></input>';
					$res .= '<input type="hidden" id="fiche_recette_img" value="'.$image.'"></input>';					
					$res .= '<input type="hidden" id="fiche_recette_rubrique_name" value="'.utf8_decode($rubrique_name).'"></input>';
					$res .= '<input type="hidden" id="fiche_recette_rubrique_fontcolor" value="'.$rubrique_fontcolor.'"></input>';
					$res .= '<input type="hidden" id="fiche_recette_rubrique_backcolor" value="'.$rubrique_backcolor.'"></input>';
					$res .= '<input type="hidden" id="fiche_recette_info_people" value="'.apply_dictionary($info_people).'"></input>';
					$res .= '<input type="hidden" id="fiche_recette_info_preparation" value="'.apply_dictionary($info_preparation).'"></input>';
					$res .= '<input type="hidden" id="fiche_recette_details_preparation" value="'.apply_dictionary($row['recette_details_preparation']).'"></input>';
					$res .= '<div style="display:none" id="fiche_recette_logo">'.$logo.'</div>';
					$res .= '<div style="display:none" id="fiche_recette_coupon">'.$coupon.'</div>';
					$res .= '<div style="display:none" id="fiche_recette_produit">'.$produit.'</div>';
					$res .= '<div style="display:none" id="fiche_recette_credit">'.apply_dictionary($row['recette_credit']).'</div>';
				}
			}
			mysql_free_result($result);
			
			echo utf8_encode($res);
		break;
		case 'select_recette' :
			session_start();
			
			$result = mysql_query('select * from recette where recette_id = "'.$_POST['id'].'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				$row = mysql_fetch_assoc($result);
				trace_log( 'RECETTE' , $row['recette_code'] , 'Selection recette' );
			}
			mysql_free_result($result);
			
			if(!isset($_SESSION['recette_selected']))
				$_SESSION['recette_selected'] = array();
				
			if(!isset($_SESSION['recette_selected'][ $_POST['id'] ]))
				$_SESSION['recette_selected'][ $_POST['id'] ] = time();
		break;
		case 'delete_selected_recette' :
			session_start();
			
			if(!isset($_SESSION['recette_selected']))
				$_SESSION['recette_selected'] = array();
				
			$new_session = array();
			foreach($_SESSION['recette_selected'] as $key => $val)
			{
				if($key != $_POST['id'])
					$new_session[ $key ] = $val;
			}
			
			$_SESSION['recette_selected'] = $new_session;
		break;		
		default :		
		break;
	}
}

?>
