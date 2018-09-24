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
			.key { height:60px;cursor:pointer; }
			.ajax_search_par_txt	{font-family:calibri;font-size:18px;cursor:default;width:400px;height:20px;padding:1px;overflow:hidden;text-overflow:ellipsis;background-color:#eee;color:#09024A;}
			.ajax_search_box_par_txt	{height:230px;}
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
			
			function keyboard_action(event,chCode)
			{
				ttl_time = <?php echo $TTL; ?>;
			
				var current_text = document.getElementById('search_text').value;
				
				if(chCode == '')
					chCode = /*('charCode' in event) ? */event.keyCode /*: event.keyCode*/;
				
				switch(chCode)
				{
					case 13 : //enter 
					break;
					case 8 :	
						 current_text = current_text.substring(0,current_text.length - 1);
					break;
					case 65 : 	current_text += 'A'; 	break;
					case 66 :	current_text += 'B'; 	break;
					case 67 :	current_text += 'C'; 	break;
					case 68 :	current_text += 'D'; 	break;
					case 69 :	current_text += 'E'; 	break;
					case 70 :	current_text += 'F'; 	break;
					case 71 :	current_text += 'G'; 	break;
					case 72 :	current_text += 'H'; 	break;
					case 73 :	current_text += 'I'; 	break;
					case 74 :	current_text += 'J'; 	break;
					case 75 :	current_text += 'K'; 	break;
					case 76 :	current_text += 'L'; 	break;
					case 77 :	current_text += 'M'; 	break;
					case 78 :	current_text += 'N'; 	break;
					case 79 :	current_text += 'O'; 	break;
					case 80 :	current_text += 'P'; 	break;
					case 81 :	current_text += 'Q'; 	break;
					case 82 :	current_text += 'R'; 	break;
					case 83 :	current_text += 'S'; 	break;
					case 84 :	current_text += 'T'; 	break;
					case 85 :	current_text += 'U'; 	break;
					case 86 :	current_text += 'V'; 	break;
					case 87 :	current_text += 'W'; 	break;
					case 88 :	current_text += 'X'; 	break;
					case 89 :	current_text += 'Y'; 	break;
					case 90 :	current_text += 'Z'; 	break;
					case 46 :	current_text = '';		break;
					case 32 :
						if(current_text != '' && current_text.substring(current_text.length - 1) != ' ' && current_text.substring(current_text.length - 1) != '-')
							current_text += ' ';	
					break;
					case 109 :	
					case 54 :	
						if(current_text != '' && current_text.substring(current_text.length - 1) != ' ' && current_text.substring(current_text.length - 1) != '-')
							current_text += '-';	
					break;
					default : 
					break;
				}

				document.getElementById('search_text').value = current_text;
				
				get_nb_recette();

				ajax_search_name_par_txt();
			}
			
			function ajax_search_name_par_txt()
			{
				hide_ajax_search_panel();
				
				if(document.getElementById('search_text').value != '')
				{		
					var httpAjax = createRequestObject();
					httpAjax.open('POST', 'recherche_par_txt.php', true);
					httpAjax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					httpAjax.onreadystatechange = function(){
						if(httpAjax.readyState == 4)
						{
							if(httpAjax.status == 200)
							{
								if(httpAjax.responseText != '')
								{
									document.getElementById("ajax_search_result").innerHTML = httpAjax.responseText;
									document.getElementById("ajax_search_panel").style.visibility = "visible";
								}
							}
						}
					}
					var dataAjax = 'do=ajax_search_par_txt';
					dataAjax += '&val='+document.getElementById('search_text').value;

					httpAjax.send(dataAjax);	
				}
			}
			
			function select_ajax_search_line_par_txt(id)
			{
				document.getElementById("search_text").value = document.getElementById("ajax_search_line_"+id+"_name").value;
				
				get_nb_recette();
				
				hide_ajax_search_panel();
			}
			
			function get_nb_recette()
			{
				document.getElementById('nb_recette_content').innerHTML = '';
			
				var search = document.getElementById('search_text').value;
				
				if(search == '')
					return false;
					
				var http = createRequestObject();
				http.open('POST', 'recherche_par_txt.php', true);
				http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				http.onreadystatechange = function(){
					if(http.readyState == 4)
					{
						if(http.status == 200)
						{
							document.getElementById('nb_recette_content').innerHTML = http.responseText;						
						}
					}
				};
				var data='do=get_nb_recette';
				data += '&search='+search;
				
				http.send(data);
			}
						
			var ttl_time = <?php echo $TTL; ?>;
			var time = 10;
			function chrono()
			{
				ttl_time--;
				if(ttl_time==0)
					go_back_home(true);
					
				setTimeout("chrono()", 1000);
			}
		
			window.onload = function()
			{			
				document.getElementById('mainframe').style.height = (window.screen.height - 250) + "px";
			
				try{
					DD_roundies.addRule('#search_text_div', '7px');
				}catch(e){}
			
				chrono();
				
				get_nb_recette();
			}
		</script>
	</head>
	<body onkeyup="keyboard_action(event,'');" onclick="hide_ajax_search_panel();">		
		<div id="select_produit_content" style="display:none;"></div>
		<div id="mainframe">
			<div id="toolbar">
				<table style="width:100%;" cellspacing="0">
					<tr valign="top">
						<td>
							<img src="../images/home.png" id="button_home" onclick="go_back_home(false);"></img>
						</td>
						<td align="center">
							<table>
								<tr valign="bottom">
									<div id="ajax_search_panel" style="visibility:hidden;" class="ajax_search_box_par_txt">
										<div id="ajax_search_result"></div>
									</div>
								</tr>
								<tr>
									<td>
										<div id="search_text_div">
											<input style="border:0;" id="search_text" readonly="read" type="text" value="<?php echo isset($_GET['search'])?$_GET['search']:''; ?>"></input>										
										</div>										
									</td>
									<td>
										<div style="width:200px;">
											<div align="center" id="nb_recette_content"></div>
										</div>
									</td>
								</tr>
							</table>
							<table>
								<tr>
									<td><img onclick="keyboard_action(null,65);" class="key" src="../images/A.png"></img></td>
									<td><img onclick="keyboard_action(null,90);" class="key" src="../images/Z.png"></img></td>
									<td><img onclick="keyboard_action(null,69);" class="key" src="../images/E.png"></img></td>
									<td><img onclick="keyboard_action(null,82);" class="key" src="../images/R.png"></img></td>
									<td><img onclick="keyboard_action(null,84);" class="key" src="../images/T.png"></img></td>
									<td><img onclick="keyboard_action(null,89);" class="key" src="../images/Y.png"></img></td>
									<td><img onclick="keyboard_action(null,85);" class="key" src="../images/U.png"></img></td>
									<td><img onclick="keyboard_action(null,73);" class="key" src="../images/I.png"></img></td>
									<td><img onclick="keyboard_action(null,79);" class="key" src="../images/O.png"></img></td>
									<td><img onclick="keyboard_action(null,80);" class="key" src="../images/P.png"></img></td>
								</tr>
								<tr>
									<td><img onclick="keyboard_action(null,81);" class="key" src="../images/Q.png"></img></td>
									<td><img onclick="keyboard_action(null,83);" class="key" src="../images/S.png"></img></td>
									<td><img onclick="keyboard_action(null,68);" class="key" src="../images/D.png"></img></td>
									<td><img onclick="keyboard_action(null,70);" class="key" src="../images/F.png"></img></td>
									<td><img onclick="keyboard_action(null,71);" class="key" src="../images/G.png"></img></td>
									<td><img onclick="keyboard_action(null,72);" class="key" src="../images/H.png"></img></td>
									<td><img onclick="keyboard_action(null,74);" class="key" src="../images/J.png"></img></td>
									<td><img onclick="keyboard_action(null,75);" class="key" src="../images/K.png"></img></td>
									<td><img onclick="keyboard_action(null,76);" class="key" src="../images/L.png"></img></td>
									<td><img onclick="keyboard_action(null,77);" class="key" src="../images/M.png"></img></td>
								</tr>
								<tr>
									<td colspan="2"></td>
									<td><img onclick="keyboard_action(null,87);" class="key" src="../images/W.png"></img></td>
									<td><img onclick="keyboard_action(null,88);" class="key" src="../images/X.png"></img></td>
									<td><img onclick="keyboard_action(null,67);" class="key" src="../images/C.png"></img></td>
									<td><img onclick="keyboard_action(null,86);" class="key" src="../images/V.png"></img></td>
									<td><img onclick="keyboard_action(null,66);" class="key" src="../images/B.png"></img></td>
									<td><img onclick="keyboard_action(null,78);" class="key" src="../images/N.png"></img></td>
									<td colspan="2"></td>
								</tr>
								<tr>
									<td colspan="2"><img onclick="keyboard_action(null,8);" class="key" src="../images/BACKSPACE.png"></img></td>
									<td><img onclick="keyboard_action(null,54);" class="key" src="../images/TIRET.png"></img></td>									
									<td colspan="6"><img onclick="keyboard_action(null,32);" class="key" src="../images/SPACE.png"></img></td>
									<td><img onclick="keyboard_action(null,46);" class="key" src="../images/DEL.png"></img></td>
								</tr>
							</table>
						</td>
					</tr>					
				</table>
			</div>
		</div>
	</body>
</html>