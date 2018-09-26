<?php

include('../include/main.php');

$PATH_IMAGES .= 'produits/';

if(isset($_POST['do']))
{
	switch($_POST['do'])
	{
		case 'get_nb_recette' :
			$res = '';
			
			$ARBO = get_arbo( 'RECETTE' , false );
			
			$_POST['search'] = utf8_decode($_POST['search']);
			
			$LIST_ID = array();		
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
			// Desactivation du pictogramme KO
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
			
			$nb_recette = 0;
			$list_recette = '';
			$result = mysql_query('select *,
										if(recette_priorite = 0,987654321,recette_priorite) as priorite
									from recette
									where
										recette_name LIKE "%'.$_POST['search'].'%"
										and recette_status = 0
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
					$nb_recette ++;
					if($list_recette != '')
						$list_recette .= ',';
					$list_recette .= $row['recette_id'];
				}
			}
			mysql_free_result($result);
			
			if($nb_recette > 0)
			{
				$res .= '<div id="produit_selected_result" onclick="view_recette_list(\''.$list_recette.'\',\'recherche_par_txt_front.php?search='.$_POST['search'].'\')">';
				$res .= $nb_recette.' recette'.(($nb_recette>1)?'s':'').' trouv&eacute;e'.(($nb_recette>1)?'s':'');
				$res .= '<img src="../images/search.png" style="margin-left:5px;"></img>';
				$res .= '</div>';
			}
			
			echo utf8_encode($res);
		break;
		case 'ajax_search_par_txt' :
			$limit_search	= 10;
			$SQL_limite		= 100;
			$i = 0;

			$_POST['val'] = utf8_decode($_POST['val']);
			
			$ARBO = get_arbo( 'RECETTE' , false );
			
			$LIST_ID = array();		
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
			// Desactivation du pictogramme KO
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
			
			$nb_recette = 0;
			$list_recette = '';
			$result = mysql_query('select recette_name as name
									from recette
									where
										recette_name LIKE "%'.$_POST['val'].'%"
										and recette_status = 0
										'.$list_id.'
										and recette_priorite > 0
									order by
										recette_name
									LIMIT '.$SQL_limite);
			echo mysql_error();		
			
			$res = "";
			$nbRes = 0;

			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$res .= '<div onclick="select_ajax_search_line_par_txt(\''.$i.'\');" class="ajax_search_par_txt" id="ajax_search_line_'.$i.'" onmouseover="survole_ajax_search_result(\''.$i.'\')" onmouseout="quitte_ajax_search_result(\''.$i.'\')">';
					$res .= apply_dictionary($row['name']);
					$res .= '</div>';
					$res .= '<input type="hidden" value="'.($row['name']).'" id="ajax_search_line_'.$i.'_name"></input>';
					
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

?>
