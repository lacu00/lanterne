<?php

include('../include/main.php');

if(isset($_POST['do']))
{
	switch($_POST['do'])
	{		
		case 'get_content' :
			$res = '';
			
			$res .= '<input type="hidden" id="content_conf_path_images" value="'.$PATH_IMAGES.'"></input>';
			
			$res .= '<input type="hidden" id="content_conf_bdd_host" value="'.$DB_HOST.'"></input>';
			$res .= '<input type="hidden" id="content_conf_bdd_login" value="'.$DB_LOGIN.'"></input>';
			$res .= '<input type="hidden" id="content_conf_bdd_mdp" value="'.$DB_MDP.'"></input>';
			$res .= '<input type="hidden" id="content_conf_bdd_name" value="'.$DB_NAME.'"></input>';
			
			$res .= '<input type="hidden" id="content_conf_borne_ttl" value="'.$TTL.'"></input>';
			$res .= '<input type="hidden" id="content_conf_borne_lig" value="'.$BORNE_NB_ELEM_LIG.'"></input>';
			$res .= '<input type="hidden" id="content_conf_borne_col" value="'.$BORNE_NB_ELEM_COL.'"></input>';
			$res .= '<input type="hidden" id="content_conf_borne_rdm" value="'.$BORNE_NB_ELEM_RDM.'"></input>';
			
			$res .= '<input type="hidden" id="content_conf_boutique_lig" value="'.$BOUTIQUE_NB_ELEM_LIG.'"></input>';
			$res .= '<input type="hidden" id="content_conf_boutique_col" value="'.$BOUTIQUE_NB_ELEM_COL.'"></input>';
			
			$result = mysql_query('select * from contenu where contenu_id = 1');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$res .= '<input type="hidden" id="content_logo_accueil" value="'.$row['contenu_logo_accueil'].'"></input>';
					$res .= '<input type="hidden" id="content_logo_lanterne" value="'.$row['contenu_logo_lanterne'].'"></input>';
					$res .= '<input type="hidden" id="content_text_lanterne" value="'.$row['contenu_text_lanterne'].'"></input>';
					$res .= '<input type="hidden" id="content_logo_partenaire" value="'.$row['contenu_logo_partenaire'].'"></input>';
					$res .= '<input type="hidden" id="content_text_partenaire" value="'.$row['contenu_text_partenaire'].'"></input>';
				}
			}
			mysql_free_result($result);
			
			echo utf8_encode($res);
		break;
		case 'save_content' :
			mysql_query('update contenu
							set
								contenu_logo_accueil = "'.trim_str($_POST['logo_accueil']).'",
								contenu_logo_lanterne = "'.trim_str($_POST['logo_lanterne']).'",
								contenu_text_lanterne = "'.trim_str($_POST['text_lanterne']).'",
								contenu_logo_partenaire = "'.trim_str($_POST['logo_partenaire']).'",
								contenu_text_partenaire = "'.trim_str($_POST['text_partenaire']).'"
							where
								contenu_id = 1');
			echo mysql_error();
			
			$f = fopen('../db.php','w');
			fwrite($f,"<?php\r\n");
			
			fwrite($f,'$DB_HOST='.'"'.$_POST['conf_bdd_host'].'";'."\r\n");
			fwrite($f,'$DB_LOGIN='.'"'.$_POST['conf_bdd_login'].'";'."\r\n");
			fwrite($f,'$DB_MDP='.'"'.$_POST['conf_bdd_mdp'].'";'."\r\n");
			fwrite($f,'$DB_NAME='.'"'.$_POST['conf_bdd_name'].'";'."\r\n");
			
			fwrite($f,'$PATH_IMAGES='.'"'.$_POST['conf_path_images'].'";'."\r\n");
			
			fwrite($f,'$BORNE_NB_ELEM_COL='.'"'.$_POST['conf_borne_col'].'";'."\r\n");
			fwrite($f,'$BORNE_NB_ELEM_LIG='.'"'.$_POST['conf_borne_lig'].'";'."\r\n");
			fwrite($f,'$BORNE_NB_ELEM_RDM='.'"'.$_POST['conf_borne_rdm'].'";'."\r\n");
			
			fwrite($f,'$BOUTIQUE_NB_ELEM_COL='.'"'.$_POST['conf_boutique_col'].'";'."\r\n");
			fwrite($f,'$BOUTIQUE_NB_ELEM_LIG='.'"'.$_POST['conf_boutique_lig'].'";'."\r\n");
			
			fwrite($f,'$TTL='.'"'.$_POST['conf_borne_ttl'].'";'."\r\n");
			
			fwrite($f,'?>');

			fclose($f);
		break;
	}
}
	
?>