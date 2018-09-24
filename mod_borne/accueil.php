<?php

include('../include/main.php');

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
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
		<title>Lanterne magique - Bienvenue</title>
		<link rel="stylesheet" type="text/css" href="../include/main.css" />
		<script type="text/javascript" src="../include/main.js"></script>		
		<style>
			body { overflow-y:hidden;overflow-x:hidden;background-color:#eee;cursor:default; }
			#body { height:100%; }
			#accueil { background-color:white;width:550px;height:550px;margin-top:25px; }
			#img_title { position:absolute;margin-top:20px;margin-left:140px;max-width:260px;max-height:140px; }
			#img_button_searchbytheme { position:absolute;width:150px;margin-top:265px;margin-left:20px;cursor:pointer; }
			#img_button_searchbyproduit { position:absolute;width:150px;margin-top:125px;margin-left:285px;cursor:pointer; }
			#img_button_searchbypromo { position:absolute;width:150px;margin-top:250px;margin-left:385px;cursor:pointer; }
			#img_button_searchbytxt { position:absolute;width:150px;margin-left:125px;margin-top:150px;cursor:pointer; }
			#img_button_addphoto { position:absolute;width:100px;margin-left:300px;margin-top:350px;cursor:pointer; }
			#img_button_printlist { position:absolute;width:125px;margin-left:170px;margin-top:320px;cursor:pointer;visibility:hidden; }
			#img_button_printlist_disabled  { position:absolute;width:125px;margin-left:170px;margin-top:320px;visibility:hidden; }
			#img_logo_lanterne { cursor:pointer;position:absolute;margin-left:30px;margin-top:425px;border:outset 4px #aaa;max-height:110px;max-width:110px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
				-khtml-border-radius: 5px;
				border-radius: 5px;}
			#img_logo_intermarche { cursor:pointer;position:absolute;margin-left:390px;margin-top:450px;border:outset 4px #aaa;max-width:150px;max-height:140px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
				-khtml-border-radius: 5px;
				border-radius: 5px;}
			.grey_box { background-color:#eee;width:150px;height:80px;position:absolute; }
			#grey_box1 { margin-top:220px; }
			#grey_box2 { margin-top:155px;margin-left:405px;height:60px; }
			#grey_box3 { margin-top:385px;margin-left:350px;width:250px;height:40px; }
			#fiche_texte_background { width:100%;height:100%;position:absolute;background-color:#333;display:none;top:0;filter:alpha(opacity=50);opacity:0.5; }	
			.texte { text-align:-moz-left;text-align:left;border:solid 5px #555;padding:10px;background-color:white;font-family:calibri;}
		</style>
		<script>
			function createRequestObject()
			{
				var http;
				if(window.XMLHttpRequest)
				{ // Mozilla, Safari, ...
					http = new XMLHttpRequest();
				}
				else if(window.ActiveXObject)
				{ // Internet Explorer
					http = new ActiveXObject("Microsoft.XMLHTTP");
				}
				return http;
			}	
		
			function go_to( page )
			{
				var http = createRequestObject();
				http.open('POST', 'recette.php', true);
				http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				http.onreadystatechange = function(){
					if(http.readyState == 4)
					{
						if(http.status == 200)
						{
							if(http.responseText != '')
								alert(http.responseText);
								
							window.location = page;
						}
					}
				};
				var data='do=trace_log_page';
				data += '&page=' + page;
0
				http.send(data);
			}
			
			function show_texte(id)
			{
				document.getElementById('fiche_texte_background').style.display = 'block';
				document.getElementById('texte_'+id).style.display = 'block';
				
				time = 30;
				setTimeout("chrono()", 1000);
			}
			
			function hide_fiche_texte()
			{
				document.getElementById('texte_lanterne').style.display = 'none';
				document.getElementById('texte_intermarche').style.display = 'none';
				document.getElementById('fiche_texte_background').style.display = 'none';
			}
			
			function print_list()
			{
				window.location = 'recette.php?do=print_list';
			}
			
			var ttl_time_conf = <?php echo $TTL; ?>;
			var ttl_time = ttl_time_conf;
			var time = 30;
			function chrono()
			{
				time--;
				if(time==0)
					hide_fiche_texte();
					
				ttl_time--;
				if(ttl_time==0)
					go_back_home(true);
					
				setTimeout("chrono()", 1000);
			}
		
			window.onload = function()
			{
				get_produit_selected( 0 );
			
				chrono();
			}
		</script>
	</head>
	<body>
		<div id="body" align="center" class="no_print">	
			<div id="accueil" align="left">
				<!--<div class="grey_box" id="grey_box1"></div>
				<div class="grey_box" id="grey_box2"></div>		
				<div class="grey_box" id="grey_box3"></div>-->
				<img id="img_title" src="<?php echo $CONTENU['logo_accueil']; ?>"></img>
				<img id="img_button_searchbytheme" onclick="go_to('recette_front.php');" src="../images/accueil_button_searchbytheme.png"></img>
				<img id="img_button_searchbyproduit" onclick="go_to('produit_front.php');" src="../images/accueil_button_searchbyproduit.png"></img>
				<img id="img_button_searchbypromo" onclick="go_to('produit_promo_front.php');" src="../images/accueil_button_searchbypromo.png"></img>
				<img id="img_button_searchbytxt" onclick="go_to('recherche_par_txt_front.php');" src="../images/accueil_button_searchbytxt.png"></img>
				<img id="img_button_addphoto" onclick="go_to('recette_photo_front.php');" src="../images/accueil_button_addphoto.png"></img>
				<img id="img_button_printlist" onclick="print_list();" src="../images/accueil_button_printlist.png"></img>
				<img id="img_button_printlist_disabled" src="../images/accueil_button_printlist_disabled.png"></img>
				<img id="img_logo_lanterne" onclick="show_texte('lanterne');" src="<?php echo $CONTENU['logo_lanterne']; ?>"></img>
				<img id="img_logo_intermarche" onclick="show_texte('intermarche');" src="<?php echo $CONTENU['logo_partenaire']; ?>"></img>
				<div class="noprint" id="produit_selected_content" align="center" style="position:absolute;margin-left:-200px;"></div>
			</div>
			<div id="fiche_texte_background" onclick="hide_fiche_texte();"></div>
			<div style="position:absolute;width:100%;top:0px;" align="center" onclick="hide_fiche_texte();">
				<div class="texte" id="texte_lanterne" onclick="hide_fiche_texte();" style="width:210px;margin-top:225px;font-size:16px;font-weight:bold;display:none;">
				</div>
				<div class="texte" id="texte_intermarche" onclick="hide_fiche_texte();" style="width:700px;margin-top:10px;font-size:10px;display:none;">
				</div>
			</div>
		</div>
		<?php include('fiche_recette_content.php'); ?>
	</body>
</html>
<script>
	document.getElementById('texte_lanterne').innerHTML = "<?php echo utf8_encode($CONTENU['text_lanterne']); ?>";
	document.getElementById('texte_intermarche').innerHTML = "<?php echo utf8_encode($CONTENU['text_partenaire']); ?>";
</script>
