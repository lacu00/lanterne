function makeAWindow(anId, addOpt)
{
   var defaults = {
            id          : anId,
           // header      : true,
            contentEl   : anId,
           // html        : $('#' + anId).html(),
            closable    : true,
           // closeAction :'hide',
            modal       : true,
            width       : 400

        };
  return new Ext.Window(Ext.apply({}, addOpt, defaults));
}

function trim_str(str)
{
	return escape(str);
}

function is_int(input)
{    
	return (
				!isNaN(input)
				&&parseInt(input)==input
				&&input>=0
			);  
}

function select_produit(id,source,rubrique)
{
	var http = createRequestObject();
	http.open('POST', 'produit.php', true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if(http.readyState == 4)
		{
			if(http.status == 200)
			{
				document.getElementById('select_produit_content').innerHTML = http.responseText;
				if(document.getElementById('list_produit_found').value != '')
				{
					var url = window.location.href.split('/');
					url = url[ url.length - 1 ].split('?');
					switch( url[0] )					
					{
						case 'produit_front.php' :
							get_produit( rubrique , 0 , document.getElementById('list_produit_found').value );
						break;
						case 'produit_promo_front.php' :
						case 'recette_front.php' :
							window.location = "produit_front.php?list_produit="+document.getElementById('list_produit_found').value;
						break;
					}
				}
				get_produit_selected( 1 );
			}
		}
	};
	var data='do=select_produit';
	data += '&id='+id;
	data += '&source='+source;

	http.send(data);
}

function get_produit_selected( refresh )
{
	var http = createRequestObject();
	http.open('POST', 'produit.php', true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if(http.readyState == 4)
		{
			if(http.status == 200)
			{							
				document.getElementById('produit_selected_content').innerHTML = http.responseText;

				if(document.getElementById('produit_selected_count').value>0 || document.getElementById('recette_selected_count').value>0)
					document.getElementById('produit_selected_content').style.display = 'block';
				else
					document.getElementById('produit_selected_content').style.display = 'none';

				if( refresh == 1 || refresh == 2 )
				{						
					if(document.getElementById('go_to_recette').value != '')
						window.location = "recette_front.php?list_recette="+document.getElementById('go_to_recette').value;
				}
				if( refresh == 3 )
					select_produit(0,'produit_font.php',0);
				
				try{ //Uniquement pour la page d'accueil
					if(document.getElementById('recette_selected_count').value > 0)
					{
						document.getElementById('img_button_printlist').style.visibility = 'visible';
						document.getElementById('img_button_printlist_disabled').style.visibility = 'hidden';
					}
					else
					{
						document.getElementById('img_button_printlist').style.visibility = 'hidden';
						document.getElementById('img_button_printlist_disabled').style.visibility = 'visible';
					}
				}catch(e){}
			}
		}
	};
	var data = 'do=get_produit_selected';
	data += '&refresh='+refresh;
	
	var page = document.location.href.substring(document.location.href.lastIndexOf( "/" )+1 );
	data += '&current_page='+page.substring(0,17);

	http.send(data);
}

function reset_list_selected(refresh)
{
	var http = createRequestObject();
	http.open('POST', 'produit.php', true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if(http.readyState == 4)
		{
			if(http.status == 200)
			{
				if(http.responseText != '')
					alert(http.responseText);
					
				if(refresh == 1)
					get_produit_selected( 0 );
			}
		}
	};
	var data = 'do=reset_list_selected';

	http.send(data);
}

function delete_selected_produit(id)
{
	var http = createRequestObject();
	http.open('POST', 'produit.php', true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if(http.readyState == 4)
		{
			if(http.status == 200)
			{
				if(http.responseText != '')
					alert(http.responseText);
				
				var refresh = 0;
				if(window.location.search.substring(0,14)=='?list_recette=')
					refresh = 2;
				if(window.location.search.substring(0,14)=='?list_produit=')
					refresh = 3;
				get_produit_selected( refresh );
			}
		}
	};
	var data = 'do=delete_selected_produit';
	data += '&id='+id;

	http.send(data);
}

function delete_selected_recette(id)
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
				get_produit_selected( 0 );
			}
		}
	};
	var data = 'do=delete_selected_recette';
	data += '&id='+id;

	http.send(data);
}

function go_to_previous_rubrique()
{
	if(document.getElementById('prev_action').value != '')
		window.location = document.getElementById('prev_action').value;

	if(document.getElementById('rubrique_content_old_parent').value != "-1")
		get_rubrique( document.getElementById('rubrique_content_old_parent').value , 0 , 0 );
}

function view_recette_list( list_recette , prev_action )
{
	var target_url = "recette_front.php?list_recette="+list_recette;
	if(prev_action != '')
		target_url += "&prev_action="+prev_action;		
		
	window.location = target_url;
}

function go_back_home(reset_list)
{
	if(reset_list)
		reset_list_selected(0);

	window.location = "accueil.php";
}

function ajax_search_name(event,type)
{
	hide_ajax_search_panel();
	
	var chCode = /*('charCode' in event) ? */event.keyCode /*: event.keyCode*/;
    if(chCode == 13)
	{
		switch(type)
		{
			case 'logo' : 		get_liste_logo();		break;
			case 'coupon' : 	get_liste_coupon();		break;
			case 'produit' :	get_liste_produits();	break;
			case 'recette' :	get_liste_recette();	break;
		}
		return false;
	}

	document.getElementById('ajax_search_type').value = type;
	var input_id = "search_"+type;
	if(document.getElementById(input_id).value != '')
	{		
		var httpAjax = createRequestObject();
		httpAjax.open('POST', '../include/main.php', true);
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
		var dataAjax = 'do=ajax_search';
		dataAjax += '&val='+document.getElementById(input_id).value;
		dataAjax += '&type='+type;	

		httpAjax.send(dataAjax);	
	}
}

function hide_ajax_search_panel()
{
	document.getElementById("ajax_search_panel").style.visibility = "hidden";
}

function survole_ajax_search_result(id)
{
	document.getElementById("ajax_search_line_"+id).style.backgroundColor = "#C8C7D1";
}

function quitte_ajax_search_result(id)
{
	document.getElementById("ajax_search_line_"+id).style.backgroundColor = "#eee";
}

function select_ajax_search_line(id)
{
	var type = document.getElementById("ajax_search_type").value;

	document.getElementById("search_"+type).value = document.getElementById("ajax_search_line_"+id+"_name").value;
	
	switch(type)
	{
		case 'logo' : 		get_liste_logo();		break;
		case 'coupon' : 	get_liste_coupon();		break;
		case 'produit' :	get_liste_produits();	break;
		case 'recette' :	get_liste_recette();	break;		
	}
	
	hide_ajax_search_panel();
}

function trace_log(type,code,details)
{
	var httpAjax = createRequestObject();
	httpAjax.open('POST', '../include/main.php', true);
	httpAjax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	httpAjax.onreadystatechange = function(){
		if(httpAjax.readyState == 4)
		{
			if(httpAjax.status == 200)
			{
				if(httpAjax.responseText != '')
					alert(httpAjax.responseText);
			}
		}
	}
	var dataAjax = 'do=trace_log';
	dataAjax += '&type='+type;
	dataAjax += '&code='+code;	
	dataAjax += '&details='+details;

	httpAjax.send(dataAjax);
}

function print_recette()
{
	ttl_time = ttl_time_conf;

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
			}
		}
	};
	var data='do=print_recette';
	data += '&id='+document.getElementById('fiche_recette_id').value;

	http.send(data);
}

function show_fiche_recette( id )
{
	document.getElementById('fiche_recette_id').value = id;

	time = 10;
	ttl_time = ttl_time_conf;

	document.getElementById('fiche_recette').style.display = 'block';

	var http = createRequestObject();
	http.open('POST', 'recette.php', true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if(http.readyState == 4)
		{
			if(http.status == 200)
			{
				document.getElementById('fiche_recette_content').innerHTML = http.responseText;
				
				document.getElementById('fiche_recette_content_name').innerHTML = document.getElementById('fiche_recette_name').value;
				var recette_font = '22px';
				if(document.getElementById('fiche_recette_name').value.length < 20)
				{
					recette_font = '35px';
				}
				else
				{
					if(document.getElementById('fiche_recette_name').value.length < 40)
					{
						recette_font = '30px';
					}
				}
				document.getElementById('fiche_recette_content_name').style.fontSize = recette_font;
				
				document.getElementById('fiche_recette_content_subname').innerHTML = document.getElementById('fiche_recette_subname').value;
				document.getElementById('fiche_recette_content_rubrique').innerHTML = document.getElementById('fiche_recette_rubrique_name').value;
				document.getElementById('fiche_recette_content_rubrique').style.color = document.getElementById('fiche_recette_rubrique_fontcolor').value;
				document.getElementById('fiche_recette_content_rubrique').style.backgroundColor = document.getElementById('fiche_recette_rubrique_backcolor').value;
				document.getElementById('fiche_recette_content_info_people').innerHTML = document.getElementById('fiche_recette_info_people').value;
				document.getElementById('fiche_recette_content_info_preparation').innerHTML = document.getElementById('fiche_recette_info_preparation').value;
				document.getElementById('fiche_recette_content_img').src = document.getElementById('fiche_recette_img').value;
				document.getElementById('fiche_recette_content_details').innerHTML = document.getElementById('fiche_recette_details_preparation').value;
				document.getElementById('fiche_recette_content_logo').innerHTML = document.getElementById('fiche_recette_logo').innerHTML;
				document.getElementById('fiche_recette_content_coupon').innerHTML = document.getElementById('fiche_recette_coupon').innerHTML;	
				document.getElementById('fiche_recette_content_produit').innerHTML = document.getElementById('fiche_recette_produit').innerHTML;
				document.getElementById('fiche_recette_content_credit').innerHTML = document.getElementById('fiche_recette_credit').innerHTML;
				
				document.getElementById('fiche_recette_content_name_print').innerHTML = document.getElementById('fiche_recette_name').value;
				document.getElementById('fiche_recette_content_name_print').style.fontSize = recette_font;
				document.getElementById('fiche_recette_content_subname_print').innerHTML = document.getElementById('fiche_recette_subname').value;
				document.getElementById('fiche_recette_content_rubrique_print').innerHTML = document.getElementById('fiche_recette_rubrique_name').value;
				//document.getElementById('fiche_recette_content_rubrique_print').style.color = document.getElementById('fiche_recette_rubrique_fontcolor').value;
				//document.getElementById('fiche_recette_content_rubrique_print').style.backgroundColor = document.getElementById('fiche_recette_rubrique_backcolor').value;
				document.getElementById('fiche_recette_content_info_people_print').innerHTML = document.getElementById('fiche_recette_info_people').value;
				document.getElementById('fiche_recette_content_info_preparation_print').innerHTML = document.getElementById('fiche_recette_info_preparation').value;
				document.getElementById('fiche_recette_content_img_print').src = document.getElementById('fiche_recette_img').value;
				document.getElementById('fiche_recette_content_details_print').innerHTML = document.getElementById('fiche_recette_details_preparation').value;
				document.getElementById('fiche_recette_content_logo_print').innerHTML = document.getElementById('fiche_recette_logo').innerHTML;
				document.getElementById('fiche_recette_content_coupon_print').innerHTML = document.getElementById('fiche_recette_coupon').innerHTML;	
				document.getElementById('fiche_recette_content_produit_print').innerHTML = document.getElementById('fiche_recette_produit').innerHTML;
				document.getElementById('fiche_recette_content_credit_print').innerHTML = document.getElementById('fiche_recette_credit').innerHTML;
				
				document.getElementById('fiche_recette_content').innerHTML = '';
				document.getElementById('fiche_recette_background').style.display = 'block';					
			}
		}
	};
	var data='do=get_content_recette';
	data += '&id='+id;

	http.send(data);
}

function hide_fiche_recette()
{
	document.getElementById('fiche_recette_background').style.display = 'none';
	document.getElementById('fiche_recette').style.display = 'none';
}

function open_url_timing(url)
{
	var win = window.open(url);
	
	setTimeout(function() {
		ttl_time = ttl_time_conf;
		win.close();
	}, 60000);
}