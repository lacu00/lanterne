<?php

include('../include/main.php');

$PATH_IMAGES .= 'recettes/';

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
					trace_log( 'RUBRIQUE' , $row['arborescence_code'] , 'Navigation par PHOTO' );
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
		case 'get_recette' :
			$res = '';
			
			$nb_elem = $BORNE_NB_ELEM_PER_PAGE;
		
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
					$image = '';
					if($row['recette_image'] != '' && file_exists($PATH_IMAGES.$row['recette_image']))
						$image = $row['recette_image'];		
					
					if($image == '')
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
			}
			mysql_free_result($result);
			
			$count = 0;
			$running_count = 0;
			
			$res .= '<div class="content_header">D&eacute;tails des recettes sans illustration :</div>';
			
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
						if($running_count == $nb_elem / 3)
						{
							$res .= '</tr><tr>';
							$running_count = 0;
						}
					}	
					
					$image = '../images/no_photo.png';
					if($recette['image'] != '' && file_exists($PATH_IMAGES.$recette['image']))
						$image = $PATH_IMAGES.$recette['image'];
					
					$res .= '<td>';
					$res .= '<div align="center" style="margin-top:10px;">';
					$res .= '<div class="fiche_produit" style="height:100%;" onclick="click_recette_photo(\''.$id.'\');">';
					$res .= '<img class="product_img" src="'.$image.'"></img>';
					$res .= '<div class="fiche_produit_name" style="color:'.$font.';background-color:'.$back.'"><img src="../images/script'.((isset($RECETTE_ERROR[$id]))?'_error':'').'.png"></img> '.$recette['name'].'</div>';
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
			$res .= '<input type="hidden" id="current_rubrique_name" value="'.utf8_decode($rubrique_name).'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_font" value="'.$rubrique_font.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_back" value="'.$rubrique_back.'"></input>';
			$res .= '<input type="hidden" id="current_rubrique_count" value="'.sizeof($RECETTES).' recette'.$s.' trouv&eacute;e'.$s.'"></input>';
			$res .= '<input type="hidden" id="current_page" value="'.$_POST['page'].'"></input>';
			$res .= '<input type="hidden" id="max_page" value="'.$max_page.'"></input>';
			
			echo utf8_encode($res);
		break;	
		case 'click_recette_photo' :
			$result = mysql_query('select * from recette
									where
										recette_id = "'.$_POST['id'].'"');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{					
					trace_log( 'PHOTO' , $row['recette_code'] , 'Selection recette' );
				}
			}
			mysql_free_result($result);
		break;
	}
}

?>