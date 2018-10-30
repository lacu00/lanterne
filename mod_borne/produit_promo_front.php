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
			
			function get_produit_random(  )
			{
				var http = createRequestObject();
				http.open('POST', 'produit_promo.php', true);
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
			
			function get_rubrique( parent )
			{
				var http = createRequestObject();
				http.open('POST', 'produit_promo.php', true);
				http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				http.onreadystatechange = function(){
					if(http.readyState == 4)
					{
						if(http.status == 200)
						{
							document.getElementById('rubrique_content').innerHTML = http.responseText;
							
							get_produit( parent , 0 );
						}
					}
				};
				var data='do=get_rubrique';
				data += '&parent='+parent;
				data += '&type=PRODUIT';

				http.send(data);
			}
			
			function get_produit( parent, page )
			{
				if(parent == "0")
					document.getElementById('button_goback').style.visibility = 'hidden';
				else
					document.getElementById('button_goback').style.visibility = 'visible';
			
				var http = createRequestObject();
				http.open('POST', 'produit_promo.php', true);
				http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				http.onreadystatechange = function(){
					if(http.readyState == 4)
					{
						if(http.status == 200)
						{
							document.getElementById('liste_produit_content').innerHTML = http.responseText;
							
							document.getElementById('liste_recette_info_rubrique').innerHTML = document.getElementById('current_rubrique_name').value;
							document.getElementById('liste_recette_info_rubrique').style.color = document.getElementById('current_rubrique_font').value;
							document.getElementById('liste_recette_info_rubrique_bg').style.backgroundColor = document.getElementById('current_rubrique_back').value;
							document.getElementById('liste_recette_info_count').innerHTML = document.getElementById('current_rubrique_count').value;	
							
							var current_page = parseInt(document.getElementById('current_page').value) + 1;
							var max_page = parseInt(document.getElementById('max_page').value);
							document.getElementById('player_info_page').innerHTML = current_page + ' / ' + max_page;						
						}
					}
				};
				var data='do=get_produit';
				data += '&parent='+parent;
				data += '&page='+page;

				http.send(data);
			}
			
			function go_to_page(action)
			{
				ttl_time = ttl_time_conf;
				
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
				get_produit( document.getElementById('current_rubrique').value, current_page );
			}
			
			var ttl_time_conf = <?php echo $TTL; ?>;
			var ttl_time = ttl_time_conf;
			var time = 10;
			function chrono()
			{
				ttl_time--;
				if(ttl_time==0)
					go_back_home();
				time--;
				if(time==0)
				{
					get_produit_random();
					time = 10;
					setTimeout("chrono()", 1000);
				}
				else
				{
					setTimeout("chrono()", 1000);
				}
			}
		
			window.onload = function()
			{			
				document.getElementById('mainframe').style.height = (window.screen.height - 250) + "px";
			
				get_rubrique( 0 );
				
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
		<div id="select_produit_content" style="display:none;"></div>
		<div id="mainframe" class="noprint">
			<table style="width:100%;" cellspacing="0">
				<tr>
					<td valign="top">
						<img src="../images/home.png" id="button_home" onclick="go_back_home();"></img>
						<div id="produit_selected_content" align="center"></div>
					</td>
					<td valign="top">
						<img src="../images/goback.png" id="button_goback" onclick="go_to_previous_rubrique();"></img>
						<input type="hidden" id="prev_action" value=""></input>
					</td>
					<td valign="top" style="width:100%">
						<div align="center" id="rubrique_content" style="display:none;"></div>
						<div id="liste_produit">
							<div id="liste_produit_content"></div>
						</div>
					</td>
				</tr>
				<!--<tr>
					<td valign="top">
						<div id="produit_selected_content" align="center"></div>
					</td>-->
					<!--<td colspan="2" valign="top" style="width:100%">
						<table style="width:100%;">
							<tr>-->
								<!--<td>
									<div id="liste_produit_info" style="visibility:hidden;">
										<table>
											<tr>
												<td>
													<div class="borne_rubrique_name" style="cursor:default;margin-left:5px;" id="liste_recette_info_rubrique_bg">
														<div id="liste_recette_info_rubrique"></div>
														<div id="liste_recette_info_count"></div>
													</div>
												</td>
											</tr>
										</table>
									</div>
								</td>-->
							<!--</tr>
							<tr>
								<td>
								</td>
							</tr>
						</table>
					</td>-->
				<!--</tr>-->
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
