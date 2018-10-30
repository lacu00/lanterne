<?php

//include('../include/main.php');

$PATH_IMAGES .= 'contenu/';

$CONTENU = array();
$result = mysql_query('select * from contenu where contenu_id = 1');
echo mysql_error();
if(mysql_num_rows($result)>0)
{
        while($row = mysql_fetch_assoc($result))
        {
                $CONTENU['logo_accueil'] = $PATH_IMAGES.$row['contenu_logo_accueil'];
                $CONTENU['logo_lanterne'] = $PATH_IMAGES.$row['contenu_logo_lanterne'];
                $CONTENU['text_lanterne'] = $row['contenu_text_lanterne'];
                $CONTENU['logo_partenaire'] = $PATH_IMAGES.$row['contenu_logo_partenaire'];
                $CONTENU['text_partenaire'] = $row['contenu_text_partenaire'];
        }
}
mysql_free_result($result);

?>
<style type="text/css">
	.img_logo_lanterne { position:absolute;top:15px;left:515px;max-height:110px;max-width:75px; }
	.img_logo_intermarche { position:absolute;top:10px;max-width:150px;max-height:75px; }
</style>
<style type="text/css" media="print">
	.noprint { display:none; }
	.yesprint { display:block; }
</style>
<div id="fiche_recette_structure_content">
	<div class="noprint" id="fiche_recette_background" onclick="hide_fiche_recette();"></div>	
	<div class="noprint" id="fiche_recette" style="position:absolute;top:10px;width:600px;background-color:white;height:600px;display:none;">
		<img src="../images/bg_recette.png" style="height:450px;width:600px;position:absolute;"></img>
		<img src="../images/fiche_bottom.png" style="position:absolute;top:450px;"></img>
		<img src="<?php echo $CONTENU['logo_partenaire']; ?>" class="img_logo_intermarche"></img>
		<img src="<?php echo $CONTENU['logo_lanterne']; ?>" class="img_logo_lanterne"></img>
		<table cellspacing="0" style="position:absolute;top:0px;">
			<tr>
				<td style="width:200px;" rowspan="2">					
					<div style="margin-left:-1px;text-align:-moz-right;text-align:right;background-color:white;font-size:20px;font-weight:bold;padding:5px;margin-top:75px;" id="fiche_recette_content_rubrique"></div>
				</td>
				<td style="width:300px;height:85px;text-align:-moz-center;text-align:center;color:#5C2222;font-weight:bold;line-height:20px;" id="fiche_recette_content_name"></td>
			</tr>
			<tr>
				<td style="height:30px;text-align:-moz-center;text-align:center;font-size:23px;font-weight:bold;color:#5C2222;" id="fiche_recette_content_subname"></td>
			</tr>
			<tr>
				<td style="height:30px;text-align:-moz-center;text-align:center;text-decoration:underline;font-size:13px;font-weight:bold;color:#333;" id="fiche_recette_content_info_people"></td>
				<td style="text-align:-moz-center;text-align:center;color:#916C6C;font-weight:bold;font-size:13px;" id="fiche_recette_content_info_preparation"></td>
			</tr>
		</table>
		<table cellspacing="0" style="position:absolute;top:145px;">
			<tr>
				<td rowspan="2" align="left" valign="top" style="width:250px;height:330px;">
					<div id="fiche_recette_content_produit" style="height:330px;width:250px;padding-left:10px;overflow:auto;"></div>
				</td>
				<td style="width:350px;">
					<img src="" style="max-height:75px;max-width:100px;margin-left:100px;" id="fiche_recette_content_img"></img>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<div class="sencha_textarea" style="height:210px;width:345px;overflow-x:hidden;overflow-y:scroll;padding-left:5px;padding-right:5px;" id="fiche_recette_content_details"></div>
				</td>
			</tr>
		</table>
		<table cellspacing="0" style="position:absolute;top:450px;left:260px;">
			<tr>
				<td>
					<img class="noprint" onclick="select_recette();" src="../images/accueil_button_addtolist.png" style="width:50px;cursor:pointer;"></img>
				</td>
				<td>
					<img class="noprint" onclick="document.getElementById('fiche_recette').style.display='none';document.getElementById('fiche_recette_print').style.display='block';window.print();document.getElementById('fiche_recette_print').style.display='none';document.getElementById('fiche_recette').style.display='block';print_recette();" src="../images/fiche_print.png" style="width:50px;cursor:pointer;"></img>
				</td>
				<td style="padding-left:10px;height:30px;width:200px;font-style:italic;font-size:13px;color:#333;" id="fiche_recette_content_credit"></td>
			</tr>
		</table>
		<table cellspacing="0" style="position:absolute;top:500px;">
			<tr>
				<td style="width:300px;height:100px;" align="left" id="fiche_recette_content_coupon"></td>
				<td style="width:300px;" align="right" id="fiche_recette_content_logo"></td>
			</tr>
		</table>
	</div>
	<!--IMPRESSION-->
	<div class="yesprint" id="fiche_recette_print" style="position:absolute;top:10px;width:600px;background-color:white;height:965px;display:none;">
		<img src="../images/bg_recette.png" style="height:815px;width:600px;position:absolute;"></img>
		<img src="../images/fiche_bottom.png" style="position:absolute;top:815px;"></img>			
		<img src="<?php echo $CONTENU['logo_partenaire']; ?>" class="img_logo_intermarche"></img>
		<img src="<?php echo $CONTENU['logo_lanterne']; ?>" class="img_logo_lanterne"></img>			
		<table cellspacing="0" style="position:absolute;top:0px;">
			<tr>
				<td style="width:200px;" rowspan="2">
					<div style="width:180px;margin-left:-1px;text-align:-moz-right;text-align:right;font-size:20px;font-weight:bold;padding:5px;position:absolute;margin-top:75px;" id="fiche_recette_content_rubrique_print"></div>
					<img style="margin-left:-1px;width:200px;height:40px;margin-top:75px;" src="../images/bg_color_rubrique.png"></img>
				</td>
				<td style="width:300px;height:85px;text-align:-moz-center;text-align:center;color:#5C2222;font-size:34px;font-weight:bold;" id="fiche_recette_content_name_print"></td>
			</tr>
			<tr>
				<td style="height:30px;text-align:-moz-center;text-align:center;font-size:23px;font-weight:bold;color:#5C2222;" id="fiche_recette_content_subname_print"></td>
			</tr>
			<tr>
				<td style="height:30px;text-align:-moz-center;text-align:center;text-decoration:underline;font-size:13px;font-weight:bold;color:#333;" id="fiche_recette_content_info_people_print"></td>
				<td style="text-align:-moz-center;text-align:center;color:#916C6C;font-weight:bold;font-size:13px;" id="fiche_recette_content_info_preparation_print"></td>
			</tr>
		</table>
		<table cellspacing="0" style="position:absolute;top:145px;">
			<tr>
				<td rowspan="2" align="left" valign="top" style="width:250px;height:695px;">
					<div id="fiche_recette_content_produit_print" style="height:695px;width:250px;padding-left:10px;overflow:hidden;"></div>
				</td>
				<td style="width:350px;">
					<img src="" style="max-height:75px;max-width:100px;margin-left:100px;" id="fiche_recette_content_img_print"></img>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<div class="sencha_textarea" style="height:580px;width:330px;overflow:hidden;padding-left:5px;padding-right:5px;" id="fiche_recette_content_details_print"></div>
				</td>
			</tr>
		</table>
		<span style="position:absolute;top:820px;left:300px;width:300px;height:30px;font-size:13px;color:#333;font-style:italic;" id="fiche_recette_content_credit_print"></span>
		<table cellspacing="0" style="position:absolute;top:865px;">
			<tr>
				<td style="width:300px;height:100px;" align="left" id="fiche_recette_content_coupon_print"></td>
				<td style="width:300px;" align="right" id="fiche_recette_content_logo_print"></td>
			</tr>
		</table>
	</div>
	<!--FIN IMPRESSION-->
	<input type="hidden" id="fiche_recette_id"></input>
	<div class="noprint" id="fiche_recette_content"></div>
</div>
<script>
	document.getElementById('fiche_recette').style.left = (window.screen.width/2 - 300);
</script>
