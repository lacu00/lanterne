<?php
	include('../include/main.php');
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
		<title>Lanterne magique - Bienvenue</title>
		<link rel="stylesheet" type="text/css" href="../include/main.css" />
		<script type="text/javascript" src="../include/main.js"></script>		
		<!--[if lte IE 9]>
			<script type="text/javascript" src="../include/roundies.js">
		</script><![endif]-->
		<style>
			body { overflow-y:hidden;overflow-x:hidden;background-color:#eee;cursor:default;font-family:calibri; }
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
			
			function get_rubrique( parent , page )
			{
				document.getElementById('view_type').value = 'rubrique';
			
				var http = createRequestObject();
				http.open('POST', 'produit.php', true);
				http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				http.onreadystatechange = function(){
					if(http.readyState == 4)
					{
						if(http.status == 200)
						{
							document.getElementById('rubrique_content').innerHTML = http.responseText;
							
							var current_page = parseInt(document.getElementById('current_page').value) + 1;
							var max_page = parseInt(document.getElementById('max_page').value);
							document.getElementById('player_info_page').innerHTML = current_page + ' / ' + max_page;

							document.getElementById('liste_recette_info_rubrique').innerHTML = document.getElementById('current_rubrique_name').value;
							document.getElementById('liste_recette_info_rubrique').style.color = document.getElementById('current_rubrique_font').value;
							document.getElementById('liste_recette_info_rubrique_bg').style.backgroundColor = document.getElementById('current_rubrique_back').value;					
							document.getElementById('liste_recette_info_count').innerHTML = document.getElementById('current_rubrique_count').value;
							
							document.getElementById('liste_recette_info_button_label').innerHTML = 'Voir les produits';
							document.getElementById('go_to_by_type_button').style.visibility = 'hidden';
							
							if(document.getElementById('nb_rubrique_count').value == "0")
							{
								document.getElementById('go_to_by_type_button').style.visibility = "hidden";
								go_to_by_type();
							}
						}
					}
				};
				var data='do=get_rubrique';
				data += '&parent='+parent;
				data += '&type=PRODUIT';
				data += '&page='+page;

				http.send(data);
			}
			
			function get_produit( parent, page, list_produit )
			{
				document.getElementById('view_type').value = 'produit';
				
				var http = createRequestObject();
				http.open('POST', 'produit.php', true);
				http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				http.onreadystatechange = function(){
					if(http.readyState == 4)
					{
						if(http.status == 200)
						{
							document.getElementById('rubrique_content').innerHTML = http.responseText;
							
							var current_page = parseInt(document.getElementById('current_page').value) + 1;
							var max_page = parseInt(document.getElementById('max_page').value);
							document.getElementById('player_info_page').innerHTML = current_page + ' / ' + max_page;

							document.getElementById('liste_recette_info_rubrique').innerHTML = document.getElementById('current_rubrique_name').value;
							document.getElementById('liste_recette_info_rubrique').style.color = document.getElementById('current_rubrique_font').value;
							document.getElementById('liste_recette_info_rubrique_bg').style.backgroundColor = document.getElementById('current_rubrique_back').value;					
							document.getElementById('liste_recette_info_count').innerHTML = document.getElementById('current_rubrique_count').value;
							
							document.getElementById('liste_recette_info_button_label').innerHTML = 'Retour aux rubriques';		
							document.getElementById('go_to_by_type_button').style.visibility = 'visible';
						}
					}
				};
				var data='do=get_produit';
				data += '&parent='+parent;
				data += '&page='+page;
				data += '&list_produit='+list_produit;

				http.send(data);
			}
			
			function get_produit_random(  )
			{
				var http = createRequestObject();
				http.open('POST', 'produit.php', true);
				http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				http.onreadystatechange = function(){
					if(http.readyState == 4)
					{
						if(http.status == 200)
						{
							document.getElementById('liste_produit_random_content').innerHTML = http.responseText;						
						}
					}
				};
				var data='do=get_produit_random';
				
				http.send(data);
			}
			
			function go_to_by_type()
			{
				ttl_time = <?php echo $TTL; ?>;
			
				switch(document.getElementById('view_type').value)
				{
					case 'produit' :
						get_rubrique( document.getElementById('current_rubrique').value , 0 )
					break;
					case 'rubrique' :
						get_produit( document.getElementById('current_rubrique').value , 0 , '' );
					break;
				}
			}
			
			function go_to_page(action)
			{
				ttl_time = <?php echo $TTL; ?>;
			
				var current_page = parseInt(document.getElementById('current_page').value);
				var max_page = parseInt(document.getElementById('max_page').value);
				switch(action)
				{
					case 'first' :
						current_page = 0;
					break;
					case 'previous' :
						current_page --;
						if(current_page < 0)
							current_page = 0;
					break;
					case 'next' :
						current_page ++;
						if(current_page >= max_page - 1)
							current_page = max_page - 1;
					break;
					case 'last' :
						current_page = max_page - 1;
					break;
				}
				
				switch(document.getElementById('view_type').value)
				{
					case 'rubrique' :
						get_rubrique( document.getElementById('current_rubrique').value , current_page )
					break;
					case 'produit' :
						var list_produit = '';
						try{
							list_produit = document.getElementById('list_produit_found').value;
						}catch(e){}
						get_produit( document.getElementById('current_rubrique').value, current_page , list_produit );
					break;
				}	
			}
			
			var ttl_time_conf = <?php echo $TTL; ?>;
			var ttl_time = ttl_time_conf;
			var time = 10;
			function chrono()
			{
				ttl_time--;
				if(ttl_time==0)
					go_back_home(true);
				time--;
				if(time==0)
				{
					get_produit_random();
					time = 10;
				}
				setTimeout("chrono()", 1000);
			}
		
			window.onload = function()
			{
				document.getElementById('mainframe').style.height = (window.screen.height - 250) + "px";
				
				try{
					DD_roundies.addRule('.borne_rubrique_name,.fiche_produit,.fiche_produit_name,#produit_selected_result', '5px');
					DD_roundies.addRule('.player_button', '7px');
				}catch(e){}
				
				get_produit_selected( 0 );
				document.getElementById('player').style.left = (window.screen.width/2) - 125;
				
				get_produit_random();
				chrono();
			}
		</script>
	</head>
	<body>		
		<input type="hidden" id="view_type"></input>
		
		<div id="select_produit_content" style="display:none;">
			<?php
				if(isset($_GET['list_produit']) && $_GET['list_produit'] != '')
					echo '<input id="list_produit_found" type="hidden" value="'.$_GET['list_produit'].'"></input>';
			?>
		</div>
		<div id="mainframe" class="noprint">
			<table style="width:100%;" cellspacing="0">
				<tr>
					<td valign="top">
						<table>
							<tr>
								<td style="width:100px;">
									<img src="../images/home.png" id="button_home" onclick="go_back_home(false);"></img>
								</td>
								<td align="left">
									<img style="visibility:hidden;" src="../images/goback.png" id="button_goback" onclick="go_to_previous_rubrique();"></img>
									<input type="hidden" id="prev_action" value=""></input>
								</td>
							</tr>
							<tr>
								<td colspan="2" valign="top" style="width:100%;display:none;" align="center">
									<div class="borne_rubrique_name" style="cursor:default;margin-left:5px;" id="liste_recette_info_rubrique_bg">
										<div id="liste_recette_info_rubrique" style="text-align:-moz-center;text-align:center;"></div>
										<div id="liste_recette_info_count"></div>
									</div>
									<div class="player_button" onclick="go_to_by_type();" style="margin-top:20px;" id="go_to_by_type_button">
										<table>
											<tr>
												<td class="player_button_label" id="liste_recette_info_button_label"></td>
												<td>
													<img src="../images/player_next.png"></img>
												</td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div id="produit_selected_content" align="center"></div>
								</td>
							</tr>
						</table>
					</td>						
					<td style="width:100%;" valign="top">
						<div align="center" id="rubrique_content"></div>
						<div id="liste_produit_content"></div>
					</td>	
					<td>
						<table style="visibility:hidden;">
							<tr>
								<td>
									<img src="../images/home.png"></img>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div class="noprint" id="liste_produit_random">
			<div id="liste_produit_random_content" class="random_footer"></div>
			<div id="player" align="center">
				<table>
					<tr>
						<td>
							<div class="player_button" onclick="go_to_page('first');">
								<img src="../images/player_first.png"></img>
							</div>
						</td>
						<td>
							<div class="player_button" onclick="go_to_page('previous');">
								<img src="../images/player_previous.png"></img>
							</div>
						</td>
						<td>
							<div id="player_info_page"></div>
						</td>
						<td>
							<div class="player_button" onclick="go_to_page('next');">
								<img src="../images/player_next.png"></img>
							</div>
						</td>
						<td>
							<div class="player_button" onclick="go_to_page('last');">
								<img src="../images/player_last.png"></img>
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php include('fiche_recette_content.php'); ?>
	</body>
</html>
<?php
	if(isset($_GET['list_produit']) && $_GET['list_produit'] != '')
		echo '<script>get_produit( 0 , 0 , "'.$_GET['list_produit'].'");</script>';
	else
		echo '<script>get_rubrique( 0 , 0 );</script>';
?>
