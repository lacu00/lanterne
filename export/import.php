<?php

include('../include/main.php');

function traite_fichier($file,$type)
{	
	ini_set('memory_limit', '1024M');
	ini_set('max_execution_time',0);

	require_once '../include/PHPExcel.php';
	require_once '../include/PHPExcel/IOFactory.php';
	
	$file_content = array();
	$count = 0;
	
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($file);
	$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
	$objWorksheet = $objPHPExcel->getActiveSheet();
	foreach ($objWorksheet->getRowIterator() as $row)
	{
		$file_content[ $count ] = array();
	
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); 
		foreach ($cellIterator as $cell)
			$file_content[ $count ][] = $cell->getValue();
		
		$count ++;
	}

	$count_error = 0;
	switch($type)
	{
		case 'produit' :
			$ARBO = get_arbo( 'PRODUIT', false );
			
			$list_rubrique = '';
			foreach($ARBO['child'] as $key => $val)
			{
				if($list_rubrique != '')
					$list_rubrique .= ',';
				$list_rubrique .= $key;
			}
			if($list_rubrique != '')
				$list_rubrique = ' and produit_arborescence IN ('.$list_rubrique.') ';
				
			$ingredient = array();
			$result = mysql_query('select * from recette_ingredient 
											natural join produit 
									where
										recette_ingredient_status = 0
										and
										produit_id != 0');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if(!isset($ingredient[ $row['produit_code'] ]))
						$ingredient[ $row['produit_code'] ] = array();
					
					$ingredient[ $row['produit_code'] ][] = array(
																	'recette'		=>	$row['recette_id'],
																	'name'			=>	$row['recette_ingredient_name'],
																	'cat'			=>	$row['recette_ingredient_categorie'],
																	'cat_priorite'	=>	$row['recette_ingredient_categorie_priorite'],
																	'priorite'		=>	$row['recette_ingredient_priorite']
															);
				}
			}
			mysql_free_result($result);
		
			$list_produit = '';
			$result = mysql_query('select * from produit 
									where
										produit_status IN (0,2)
										'.$list_rubrique);
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if($list_produit != '')
						$list_produit .= ',';
					$list_produit .= $row['produit_id'];
				}
			}
			mysql_free_result($result);
			
			if($list_produit != '')
			{
				mysql_query('delete from produit where produit_id IN ('.$list_produit.')');
				mysql_query('delete from recette_ingredient where produit_id IN ('.$list_produit.')');
				echo mysql_error();
			}
			
			mysql_query('delete from recette_ingredient where produit_id != 0 and produit_id NOT IN (select produit_id from produit where 1)');
			echo mysql_error();
			
			$arbo = array();
			foreach($ARBO['child'] as $key => $val)
				$arbo[ $val['code'] ] = $key;
				
			$code = array();
		
			foreach($file_content as $count => $cells)
			{
				if($count > 0)
				{
					$error = 0;
					
					$status = "0";
					if($cells[2] != 'oui')
						$status = "2";
						
					$rubrique = 0;
					if(isset($arbo[ $cells[7] ]))
						$rubrique = $arbo[ $cells[7] ];
					else
					{
						echo "<br />ERREUR : Ligne ".$count." la rubrique indiqu&eacute;e n'est pas r&eacute;f&eacute;renc&eacute;e";
						$error ++;
					}
					
					if($cells[0] == '')
					{
						echo "<br />ERREUR : Ligne ".$count." nom de produit vide";
						$error ++;
					}
					
					if($cells[1] == '')
					{
						echo "<br />ERREUR : Ligne ".$count." code produit vide";
						$error ++;
					}
					
					if(isset($code[ $cells[1] ]))
					{
						echo "<br />ERREUR : Ligne ".$count." code produit d&eacute;j&agrave; utilis&eacute; (ligne ".$code[ $cells[1] ].")";
						$error ++;
					}
					
					if($error == 0)
					{
						$produit_id = 0;
						$sql = 'insert into produit
										(
											produit_name,
											produit_code,
											produit_status,
											produit_priorite,
											produit_poids,
											produit_volume,
											produit_image,
											produit_arborescence,
											produit_promo,
											produit_description,
											produit_date_maj
										)
										values
										(
											"'.$cells[0].'",
											"'.$cells[1].'",
											"'.$status.'",
											"'.$cells[3].'",
											"'.$cells[4].'",
											"'.$cells[5].'",
											"'.$cells[6].'",
											"'.$rubrique.'",
											"'.$cells[8].'",
											"'.$cells[9].'",
											NOW()
										)';
						mysql_query( utf8_decode($sql) );
						echo mysql_error();
						$produit_id = mysql_insert_id();
						
						$code[ $cells[1] ] = $count;
						
						if(isset($ingredient[ $cells[1] ]) && $produit_id != 0)
						{
							foreach($ingredient[ $cells[1] ] as $val)
							{
								$sql = 'insert into recette_ingredient
												(
													recette_ingredient_priorite,
													recette_ingredient_categorie,
													recette_ingredient_categorie_priorite,
													recette_ingredient_name,
													recette_id,
													produit_id
												)
												values
												(
													"'.$val['priorite'].'",
													"'.$val['cat'].'",
													"'.$val['cat_priorite'].'",
													"'.$val['name'].'",
													"'.$val['recette'].'",
													"'.$produit_id.'"
												)';
								mysql_query( utf8_decode($sql) );
								echo mysql_error();
							}
						}
					}
					else
						$count_error += $error;
				}
			}
		break;
		case 'logo' :
			$recette = array();
			$result = mysql_query('select * from recette_logo  
										natural join logo
									where
										recette_logo_status = 0');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if(!isset($recette[ $row['logo_code'] ]))
						$recette[ $row['logo_code'] ] = array();
					
					$recette[ $row['logo_code'] ][] = array(
																	'recette'		=>	$row['recette_id'],
																	'priorite'		=>	$row['recette_logo_priorite']
															);
				}
			}
			mysql_free_result($result);
			
			$list_logo = '';
			$result = mysql_query('select * from logo 
									where
										logo_status IN (0,2)');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if($list_logo != '')
						$list_logo .= ',';
					$list_logo .= $row['logo_id'];
				}
			}
			mysql_free_result($result);
			
			if($list_logo != '')
			{
				mysql_query('delete from logo where logo_id IN ('.$list_logo.')');
				mysql_query('delete from recette_logo where logo_id IN ('.$list_logo.')');
				echo mysql_error();
			}
			
			mysql_query('delete from recette_logo where logo_id NOT IN (select logo_id from logo where 1)');
			echo mysql_error();
			
			$code = array();
		
			foreach($file_content as $count => $cells)
			{
				if($count > 0)
				{
					$error = 0;
					
					$status = "0";
					if($cells[2] != 'oui')
						$status = "2";
					
					if($cells[0] == '')
					{
						echo "<br />ERREUR : Ligne ".$count." nom de logo vide";
						$error ++;
					}
					
					if($cells[1] == '')
					{
						echo "<br />ERREUR : Ligne ".$count." code logo vide";
						$error ++;
					}
					
					if(isset($code[ $cells[1] ]))
					{
						echo "<br />ERREUR : Ligne ".$count." code logo d&eacute;j&agrave; utilis&eacute; (ligne ".$code[ $cells[1] ].")";
						$error ++;
					}
					
					if($error == 0)
					{
						$logo_id = 0;
						$sql = 'insert into logo
										(
											logo_name,
											logo_code,
											logo_status,
											logo_image,
											logo_url,
											logo_description,
											logo_date_maj
										)
										values
										(
											"'.$cells[0].'",
											"'.$cells[1].'",
											"'.$status.'",
											"'.$cells[3].'",
											"'.$cells[4].'",
											"'.$cells[5].'",
											NOW()
										)';
						mysql_query( utf8_decode($sql) );
						echo mysql_error();
						$logo_id = mysql_insert_id();
						
						$code[ $cells[1] ] = $count;
						
						if(isset($recette[ $cells[1] ]) && $logo_id != 0)
						{
							foreach($recette[ $cells[1] ] as $val)
							{
								$sql = 'insert into recette_logo
												(
													recette_logo_priorite,
													recette_id,
													logo_id,
													recette_logo_date_maj
												)
												values
												(
													"'.$val['priorite'].'",
													"'.$val['recette'].'",
													"'.$logo_id.'",
													NOW()
												)';
								mysql_query( utf8_decode($sql) );
								echo mysql_error();
							}
						}
					}
					else
						$count_error += $error;
				}
			}
		break;
		case 'coupon' :
			$recette = array();
			$result = mysql_query('select * from recette_coupon  
										natural join coupon
									where
										recette_coupon_status = 0');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if(!isset($recette[ $row['coupon_code'] ]))
						$recette[ $row['coupon_code'] ] = array();
					
					$recette[ $row['coupon_code'] ][] = array(
																	'recette'		=>	$row['recette_id'],
																	'priorite'		=>	$row['recette_coupon_priorite']
															);
				}
			}
			mysql_free_result($result);
			
			$list_coupon = '';
			$result = mysql_query('select * from coupon 
									where
										coupon_status IN (0,2)');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					if($list_coupon != '')
						$list_coupon .= ',';
					$list_coupon .= $row['coupon_id'];
				}
			}
			mysql_free_result($result);
			
			if($list_coupon != '')
			{
				mysql_query('delete from coupon where coupon_id IN ('.$list_coupon.')');
				mysql_query('delete from recette_coupon where coupon_id IN ('.$list_coupon.')');
				echo mysql_error();
			}
			
			mysql_query('delete from recette_coupon where coupon_id NOT IN (select coupon_id from coupon where 1)');
			echo mysql_error();
			
			$code = array();
			
			foreach($file_content as $count => $cells)
			{echo "<br>".$count;
				if($count > 0)
				{
					$error = 0;
					
					$status = "0";
					if($cells[2] != 'oui')
						$status = "2";
					
					if($cells[0] == '')
					{
						echo "<br />ERREUR : Ligne ".$count." nom du coupon vide";
						$error ++;
					}
					
					if($cells[1] == '')
					{
						echo "<br />ERREUR : Ligne ".$count." code coupon vide";
						$error ++;
					}
					
					if(isset($code[ $cells[1] ]))
					{
						echo "<br />ERREUR : Ligne ".$count." code coupon d&eacute;j&agrave; utilis&eacute; (ligne ".$code[ $cells[1] ].")";
						$error ++;
					}
					
					if($error == 0)
					{
						$coupon_id = 0;
						$sql = 'insert into coupon
										(
											coupon_name,
											coupon_code,
											coupon_status,
											coupon_image,
											coupon_url,
											coupon_description,
											coupon_date_maj
										)
										values
										(
											"'.$cells[0].'",
											"'.$cells[1].'",
											"'.$status.'",
											"'.$cells[3].'",
											"'.$cells[4].'",
											"'.$cells[5].'",
											NOW()
										)';
						mysql_query( utf8_decode($sql) );
						echo mysql_error();
						$coupon_id = mysql_insert_id();
						
						$code[ $cells[1] ] = $count;
						
						if(isset($recette[ $cells[1] ]) && $coupon_id != 0)
						{
							foreach($recette[ $cells[1] ] as $val)
							{
								$sql = 'insert into recette_coupon
												(
													recette_coupon_priorite,
													recette_id,
													coupon_id,
													recette_coupon_date_maj
												)
												values
												(
													"'.$val['priorite'].'",
													"'.$val['recette'].'",
													"'.$coupon_id.'",
													NOW()
												)';
								mysql_query( utf8_decode($sql) );
								echo mysql_error();
							}
						}
					}
					else
						$count_error += $error;
				}
			}
		break;
		case 'recette' :
			$ARBO = get_arbo( 'RECETTE', false );
			$ARBO_PRODUIT = get_arbo( 'PRODUIT', false );

			$logo = array();
			$result = mysql_query('select * from logo where logo_status IN (0,2)');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$logo[ $row['logo_code'] ] = $row['logo_id'];
				}
			}
			mysql_free_result($result);

			$coupon = array();
			$result = mysql_query('select * from coupon where coupon_status IN (0,2)');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$coupon[ $row['coupon_code'] ] = $row['coupon_id'];
				}
			}
			mysql_free_result($result);
			
			$list_rubrique = '';
			foreach($ARBO['child'] as $key => $val)
			{
				if($list_rubrique != '')
					$list_rubrique .= ',';
				$list_rubrique .= $key;
			}

			$list_rubrique_produit = '';
			foreach($ARBO_PRODUIT['child'] as $key => $val)
			{
				if($list_rubrique_produit != '')
					$list_rubrique_produit .= ',';
				$list_rubrique_produit .= $key;
			}
			
			$sql_rubrique = '';
			if($list_rubrique_produit != '')
				$sql_rubrique = ' and produit_arborescence IN ('.$list_rubrique_produit.') ';
				
			$produit = array();
			$result = mysql_query('select * from produit
									where
										produit_status IN (0,2)
										'.$sql_rubrique);
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$produit[ $row['produit_code'] ] = $row['produit_id'];
				}
			}
			mysql_free_result($result);
			
			mysql_query('delete from recette_logo where recette_id IN (select recette_id from recette where recette_status IN (0,2))');
			mysql_query('delete from recette_coupon where recette_id IN (select recette_id from recette where recette_status IN (0,2))');
			mysql_query('delete from recette_ingredient where recette_id IN (select recette_id from recette where recette_status IN (0,2))');
			mysql_query('delete from recette where recette_status IN (0,2)');
			echo mysql_error();
			
			$arbo = array();
			foreach($ARBO['child'] as $key => $val)
				$arbo[ $val['code'] ] = $key;
				
			$code = array();
		
			foreach($file_content as $count => $cells)
			{
				if($count > 0)
				{//echo "<br/><pre>";print_r($cells);
					$error = 0;
					
					$status = "0";
					if($cells[2] != 'oui')
						$status = "2";
						
					$rubrique = 0;
					if(isset($arbo[ $cells[4] ]))
						$rubrique = $arbo[ $cells[4] ];
					else
					{
						echo "<br />ERREUR : Liste recette -> Ligne ".$count." la rubrique indiqu&eacute;e n'est pas r&eacute;f&eacute;renc&eacute;e";
						$error ++;
					}
					
					if($cells[0] == '')
					{
						echo "<br />ERREUR : Liste recette -> Ligne ".$count." nom de recette vide";
						$error ++;
					}
					
					if($cells[1] == '')
					{
						echo "<br />ERREUR : Liste recette -> Ligne ".$count." code recette vide";
						$error ++;
					}
					
					if(isset($code[ $cells[1] ]))
					{
						echo "<br />ERREUR : Liste recette -> Ligne ".$count." code recette d&eacute;j&agrave; utilis&eacute; (ligne ".$code[ $cells[1] ]['ligne'].")";
						$error ++;
					}
					
					if($error == 0)
					{
						$recette_id = 0;
						$sql = 'insert into recette
										(
											recette_name,
											recette_code,
											recette_status,
											recette_priorite,
											recette_image,
											recette_description,
											recette_info_preparation,
											recette_info_cuisson,
											recette_info_nb_people,
											recette_info_people_type,
											recette_info_complementaire,
											recette_details_preparation,
											recette_date_maj,
											recette_arborescence,
											recette_credit
										)
										values
										(
											"'.$cells[0].'",
											"'.$cells[1].'",
											"'.$status.'",
											"'.$cells[3].'",
											"'.$cells[5].'",
											"'.$cells[12].'",
											"'.$cells[7].'",
											"'.$cells[8].'",
											"'.$cells[9].'",
											"'.$cells[10].'",
											"'.$cells[11].'",
											"'.$cells[6].'",
											NOW(),											
											"'.$rubrique.'",
											"'.$cells[13].'"
										)';
						mysql_query( utf8_decode($sql) );
						echo mysql_error();
						$recette_id = mysql_insert_id();
						
						$code[ $cells[1] ] = array(
													'id'	=>	$recette_id,
													'ligne'	=>	$count
												);
					}
					else
						$count_error += $error;
				}
			}
			
			$count = 0;
			$file_content = array();
			$objWorksheet = $objPHPExcel->setActiveSheetIndex(1);
			foreach ($objWorksheet->getRowIterator() as $row)
			{
				$file_content[ $count ] = array();
			
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false); 
				foreach ($cellIterator as $cell)
					$file_content[ $count ][] = $cell->getValue();
				
				$count ++;
			}
			
			foreach($file_content as $count => $cells)
			{
				if($count > 0)
				{
					$error = 0;
					
					$recette_id = 0;
					if(!isset($code[ $cells[0] ]))
					{
						echo "<br />ERREUR : Ingr&eacute;dients -> Ligne ".$count." code recette non reconnu";
						$error ++;
					}
					else
						$recette_id = $code[ $cells[0] ]['id'];
					
					if($cells[3] == '')
					{
						echo "<br />ERREUR : Ingr&eacute;dients -> Ligne ".$count." nom ingr&eacute;dient vide";
						$error ++;
					}
					
					$produit_id = 0;
					if($cells[5] != '')
					{
						if(!isset($produit[ $cells[5] ]))
						{
							echo "<br />ERREUR : Ingr&eacute;dients -> Ligne ".$count." code ingr&eacute;dient vide";
							$error ++;
						}
						else
							$produit_id = $produit[ $cells[5] ];
					}
					
					if($error == 0)
					{
						$sql = 'insert into recette_ingredient
										(
											recette_ingredient_priorite,
											recette_ingredient_categorie,
											recette_ingredient_categorie_priorite,
											recette_ingredient_name,
											recette_id,
											produit_id
										)
										values
										(
											"'.$cells[4].'",
											"'.$cells[1].'",
											"'.$cells[2].'",
											"'.$cells[3].'",
											"'.$recette_id.'",
											"'.$produit_id.'"
										)';
						mysql_query( utf8_decode($sql) );
						echo mysql_error();
					}
					else
						$count_error += $error;
				}
			}
			
			$count = 0;
			$file_content = array();
			$objWorksheet = $objPHPExcel->setActiveSheetIndex(2);
			foreach ($objWorksheet->getRowIterator() as $row)
			{
				$file_content[ $count ] = array();
			
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false); 
				foreach ($cellIterator as $cell)
					$file_content[ $count ][] = $cell->getValue();
				
				$count ++;
			}
			
			foreach($file_content as $count => $cells)
			{
				if($count > 0)
				{
					$error = 0;
					
					$recette_id = 0;
					if(!isset($code[ $cells[0] ]))
					{
						echo "<br />ERREUR : Logos -> Ligne ".$count." code recette non reconnu";
						$error ++;
					}
					else
						$recette_id = $code[ $cells[0] ]['id'];
					
					$logo_id = 0;
					if(!isset($logo[ $cells[1] ]))
					{
						echo "<br />ERREUR : Logos -> Ligne ".$count." code logo non reconnu";
						$error ++;
					}
					else
						$logo_id = $logo[ $cells[1] ];
					
					if($error == 0)
					{
						$sql = 'insert into recette_logo
										(
											recette_id,
											logo_id,
											recette_logo_date_maj,
											recette_logo_priorite
										)
										values
										(
											"'.$recette_id.'",
											"'.$logo_id.'",
											NOW(),
											"'.$cells[2].'"
										)';
						mysql_query( utf8_decode($sql) );
						echo mysql_error();
					}
					else
						$count_error += $error;
				}
			}
			
			$count = 0;
			$file_content = array();
			$objWorksheet = $objPHPExcel->setActiveSheetIndex(3);
			foreach ($objWorksheet->getRowIterator() as $row)
			{
				$file_content[ $count ] = array();
			
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false); 
				foreach ($cellIterator as $cell)
					$file_content[ $count ][] = $cell->getValue();
				
				$count ++;
			}
			
			foreach($file_content as $count => $cells)
			{
				if($count > 0)
				{
					$error = 0;
					
					$recette_id = 0;
					if(!isset($code[ $cells[0] ]))
					{
						echo "<br />ERREUR : Coupons -> Ligne ".$count." code recette non reconnu";
						$error ++;
					}
					else
						$recette_id = $code[ $cells[0] ]['id'];
					
					$coupon_id = 0;
					if(!isset($coupon[ $cells[1] ]))
					{
						echo "<br />ERREUR : Coupons -> Ligne ".$count." code coupon non reconnu";
						$error ++;
					}
					else
						$coupon_id = $coupon[ $cells[1] ];
					
					if($error == 0)
					{
						$sql = 'insert into recette_coupon
										(
											recette_id,
											coupon_id,
											recette_coupon_date_maj,
											recette_coupon_priorite
										)
										values
										(
											"'.$recette_id.'",
											"'.$coupon_id.'",
											NOW(),
											"'.$cells[2].'"
										)';
						mysql_query( utf8_decode($sql) );
						echo mysql_error();
					}
					else
						$count_error += $error;
				}
			}
		break;
	}
	
	if($count_error == 0)
		echo '<script>if(confirm("Voulez-vous fermer cette fenetre ?")){window.close();}</script>';
}

if(isset($_POST['upload']) && $_FILES['userfile']['size'] > 0)
{
	$file_name = "import.xlsx";

	$fileName = $_FILES['userfile']['name'];
	$tmpName  = $_FILES['userfile']['tmp_name'];
	$fileSize = $_FILES['userfile']['size'];
	$fileType = $_FILES['userfile']['type'];

	move_uploaded_file($tmpName, $file_name);	
	
	traite_fichier($file_name,$_POST['type']);
	
	exit;
}

?>

<head>
<title>UPLOAD</title>
</head>
<body oncontextmenu="return false;" style="background-color:lightblue;font-family:Calibri;">

<div style="text-align:-moz-center;text-align:center;cursor:default;">Importer fichier XLS</div>
<form method="post" enctype="multipart/form-data">

<div align="center">

<input type="hidden" name="MAX_FILE_SIZE" value="1000000000000000"></input>
<input id="type_action_id" type="hidden" name="type_action" value=""></input>
<input name="userfile" type="file" id="userfile"></input>
<input type="hidden" name="type" value="<?php if(isset($_GET['type'])) echo $_GET['type']; ?>"></input>

</div>
<div align="center" style="margin-top:15px;">

<input name="upload" type="submit" id="upload" value="Valider"></input>
<div style="color:red;cursor:default;">Attention - Le contenu du fichier va remplacer le contenu actuel de la base de donn&eacute;es</div>
</div>

</form>
</body>