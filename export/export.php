<?php

include('../include/main.php');

$xls_style = false;

if(isset($_GET['do']))
{
	switch($_GET['do'])
	{
		case 'export' :
			ini_set('memory_limit', '1024M');
			ini_set('max_execution_time',0);

			require_once '../include/PHPExcel.php';
			require_once '../include/PHPExcel/IOFactory.php';
			
			$excel = new PHPExcel();
			$excel->setActiveSheetIndex(0);

			$style_header = array( 
				'fill' => array( 
					'type' => PHPExcel_Style_Fill::FILL_SOLID, 
					'color' => array('rgb'=>'E1E0F7'), 
				), 
				'font' => array( 
					'bold' => true, 
				) 
			);
			
			switch($_GET['type'])
			{
				case 'produit' :
					$FILENAME = 'liste_produits';
				
					$ARBO = get_arbo( 'PRODUIT', false );
					
					$excel->getActiveSheet()->setTitle(utf8_encode('Liste produits')); 

					$header = array(
						'Nom',
						'Code',
						'Actif',
						'Priorité',
						'Poids',
						'Volume',
						'Image',
						'Rubrique',
						'Promotion',
						'Description'
					);
				
					foreach($header as $key => $val)
					{
						$header[$key] = utf8_encode($header[$key]);
						
						if($xls_style)
							$excel->getActiveSheet()->getColumnDimension(getIndex($key,-1))->setWidth(17); 
					}

					$excel->getActiveSheet()->fromArray($header,null,'A1');
					
					if($xls_style)
						$excel->getActiveSheet()->getStyle('A1:'.getIndex(sizeof($header)-1,1))->applyFromArray( $style_header ); 
				
					$list_rubrique = '';
					foreach($ARBO['child'] as $key => $val)
					{
						if($list_rubrique != '')
							$list_rubrique .= ',';
						$list_rubrique .= $key;
					}
					if($list_rubrique != '')
						$list_rubrique = ' and produit_arborescence IN ('.$list_rubrique.') ';
				
					$count = 2;
					$result = mysql_query('select * from produit
											where 
												produit_status IN (0,2)
												'.$list_rubrique.'
											order by produit_name');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{		
							foreach($row as $key => $val)
								$row[ $key ] = utf8_encode($val);
						
							$statut = 'oui';
							if($row['produit_status'] == "2")
								$statut = 'non';
								
							$rubrique = $ARBO['child'][ $row['produit_arborescence'] ]['code'];
							
							$promotion = 'non';
							if($row['produit_promo'] == "1")
								$promotion = 'oui';
								
							$description = $row['produit_description'];
							if($description == '&nbsp;' || $description == '<br>')
								$description = '';
							
							$printf = array(
												$row['produit_name'],
												$row['produit_code'],
												$statut,
												$row['produit_priorite'],
												$row['produit_poids'],
												$row['produit_volume'],
												$row['produit_image'],
												$rubrique,
												$promotion,
												$description
											);
							
							$excel->getActiveSheet()->fromArray($printf,null,'A'.$count);
							
							$count ++;
						}
					}
					mysql_free_result($result);
				break;
				case 'recette' :
					$FILENAME = 'liste_recette';
					
					$ARBO = get_arbo( 'RECETTE', false );
					$ARBO_PRODUIT = get_arbo( 'PRODUIT', false );
					
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
					
					$excel->setActiveSheetIndex(0);
					$excel->getActiveSheet()->setTitle(utf8_encode('Liste recette'));
					
					$header = array(
						'Nom',
						'Code',
						'Actif',
						'Priorité',		
						'Rubrique',
						'Image',
						'Préparation',
						'Temps préparation',
						'Temps cuisson',
						'Nombre personnes',
						'Type personne',
						'Info complémentaire',
						'Description',
						'Crédit'
					);
					
					foreach($header as $key => $val)
					{
						$header[$key] = utf8_encode($header[$key]);
						
						if($xls_style)
							$excel->getActiveSheet()->getColumnDimension(getIndex($key,-1))->setWidth(17); 
					}
						
					$excel->getActiveSheet()->fromArray($header,null,'A1');
					
					if($xls_style)
						$excel->getActiveSheet()->getStyle('A1:'.getIndex(sizeof($header)-1,1))->applyFromArray( $style_header ); 
					
					$sql_rubrique = '';
					if($list_rubrique != '')
						$sql_rubrique = ' and recette_arborescence IN ('.$list_rubrique.') ';
						
					$recette = array();
					$result = mysql_query('select * from recette where recette_status IN (0,2) '.$sql_rubrique.' order by recette_name');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{
							$recette[ $row['recette_id'] ] = $row;
						}
					}
					mysql_free_result($result);
					
					$count = 2;
					foreach($recette as $key => $val)
					{
						foreach($val as $utf_key => $utf_val)
							$val[ $utf_key ] = utf8_encode($utf_val);
						
						$status = 'oui';
						if($val['recette_status'] == "2")
							$status = 'non';
							
						$rubrique = $ARBO['child'][ $val['recette_arborescence'] ]['code'];
						
						$description = $val['recette_description'];
						if($description == '&nbsp;' || $description == '<br>')
							$description = '';
						
						$preparation = $val['recette_details_preparation'];
						if($preparation == '&nbsp;' || $preparation == '<br>')
							$preparation = '';
						
						$printf = array(
											$val['recette_name'],
											$val['recette_code'],
											$status,
											$val['recette_priorite'],
											$rubrique,
											$val['recette_image'],
											$preparation,
											$val['recette_info_preparation'],
											$val['recette_info_cuisson'],
											$val['recette_info_nb_people'],
											$val['recette_info_people_type'],
											$val['recette_info_complementaire'],
											$description,
											$val['recette_credit']
										);
						
						$excel->getActiveSheet()->fromArray($printf,null,'A'.$count);
						
						$count ++;
					}
					
					if($xls_style)
						$excel->getActiveSheet()->setAutoFilter('A1:'.getIndex(sizeof($header)-1,1)); 
					
					$excel->createSheet();
					$excel->setActiveSheetIndex(1);
					$excel->getActiveSheet()->setTitle(utf8_encode('Ingrédients'));
					
					$header = array(
						'Recette',
						'Catégorie',
						'Cat. Priorité',
						'Nom',
						'Priorité',
						'Produit associé'
					);
					
					foreach($header as $key => $val)
					{
						$header[$key] = utf8_encode($header[$key]);
						
						if($xls_style)
							$excel->getActiveSheet()->getColumnDimension(getIndex($key,-1))->setWidth(17); 
					}
					
					$excel->getActiveSheet()->fromArray($header,null,'A1');
					
					if($xls_style)
						$excel->getActiveSheet()->getStyle('A1:'.getIndex(sizeof($header)-1,1))->applyFromArray( $style_header ); 
					
					$sql_rubrique = '';
					if($list_rubrique_produit != '')
						$sql_rubrique = ' and produit_arborescence IN ('.$list_rubrique_produit.') ';
					
					$produit = array();
					$result = mysql_query('select * from produit where produit_status IN (0,2) '.$sql_rubrique);
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{
							$produit[ $row['produit_id'] ] = $row['produit_code'];
						}
					}
					mysql_free_result($result);
					
					$count = 2;
					$result = mysql_query('select *,
													if(recette_ingredient_priorite = 0,987654321,recette_ingredient_priorite) as ingredient_priorite,
													if(recette_ingredient_categorie_priorite = 0,987654321,recette_ingredient_categorie_priorite) as categorie_priorite
												from recette_ingredient 
												natural join recette
													where
														recette_ingredient_status = 0
														and
														recette_status IN (0,2) 
												order by 
													recette_name,
													categorie_priorite,
													ingredient_priorite');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{
							if(isset($recette[ $row['recette_id'] ]))
							{
								foreach($row as $utf_key => $utf_val)
									$row[ $utf_key ] = utf8_encode($utf_val);
							
								if($row['produit_id'] != "0" && !isset($produit[ $row['produit_id'] ]))
									continue;
									
								$produit_code = '';
								if(isset($produit[ $row['produit_id'] ]))
									$produit_code = $produit[ $row['produit_id'] ];
									
								$printf = array(
												$row['recette_code'],
												$row['recette_ingredient_categorie'],
												$row['recette_ingredient_categorie_priorite'],
												$row['recette_ingredient_name'],
												$row['recette_ingredient_priorite'],
												$produit_code
											);
						
								$excel->getActiveSheet()->fromArray($printf,null,'A'.$count);
								
								$count ++;
							}
						}
					}
					mysql_free_result($result);
					
					if($xls_style)
						$excel->getActiveSheet()->setAutoFilter('A1:'.getIndex(sizeof($header)-1,1)); 
					
					$excel->createSheet();
					$excel->setActiveSheetIndex(2);
					$excel->getActiveSheet()->setTitle(utf8_encode('Logos'));
					
					$header = array(
						'Recette',
						'Logo',
						'Priorité'
					);
					
					foreach($header as $key => $val)
					{
						$header[$key] = utf8_encode($header[$key]);
						
						if($xls_style)
							$excel->getActiveSheet()->getColumnDimension(getIndex($key,-1))->setWidth(17); 
					}
					
					$excel->getActiveSheet()->fromArray($header,null,'A1');
					
					if($xls_style)
						$excel->getActiveSheet()->getStyle('A1:'.getIndex(sizeof($header)-1,1))->applyFromArray( $style_header ); 
					
					$count = 2;
					$result = mysql_query('select * from recette_logo 
											natural join recette
											natural join logo
												where 
													recette_logo_status = 0
													and
													recette_status IN (0,2)
													and
													logo_status = 0
												order by
													recette_name,
													recette_logo_priorite
													');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{
							if(isset($recette[ $row['recette_id'] ]))
							{
								foreach($row as $utf_key => $utf_val)
									$row[ $utf_key ] = utf8_encode($utf_val);
									
								$printf = array(
													$row['recette_code'],
													$row['logo_code'],
													$row['recette_logo_priorite']
												);
							
								$excel->getActiveSheet()->fromArray($printf,null,'A'.$count);
								
								$count ++;
							}
						}
					}
					mysql_free_result($result);
					
					if($xls_style)
						$excel->getActiveSheet()->setAutoFilter('A1:'.getIndex(sizeof($header)-1,1)); 
					
					$excel->createSheet();
					$excel->setActiveSheetIndex(3);
					$excel->getActiveSheet()->setTitle(utf8_encode('Coupons'));
					
					$header = array(
						'Recette',
						'Coupon',
						'Priorité'
					);
					
					foreach($header as $key => $val)
					{
						$header[$key] = utf8_encode($header[$key]);
						
						if($xls_style)
							$excel->getActiveSheet()->getColumnDimension(getIndex($key,-1))->setWidth(17); 
					}
					
					$excel->getActiveSheet()->fromArray($header,null,'A1');
					
					if($xls_style)
						$excel->getActiveSheet()->getStyle('A1:'.getIndex(sizeof($header)-1,1))->applyFromArray( $style_header ); 
					
					$count = 2;
					$result = mysql_query('select * from recette_coupon 
											natural join recette
											natural join coupon
												where 
													recette_coupon_status = 0
													and
													recette_status IN (0,2)
													and
													coupon_status = 0
												order by
													recette_name,
													recette_coupon_priorite
													');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{
							if(isset($recette[ $row['recette_id'] ]))
							{
								foreach($row as $utf_key => $utf_val)
									$row[ $utf_key ] = utf8_encode($utf_val);
									
								$printf = array(
													$row['recette_code'],
													$row['coupon_code'],
													$row['recette_coupon_priorite']
												);
							
								$excel->getActiveSheet()->fromArray($printf,null,'A'.$count);
								
								$count ++;
							}
						}
					}
					mysql_free_result($result);
					
					if($xls_style)
						$excel->getActiveSheet()->setAutoFilter('A1:'.getIndex(sizeof($header)-1,1)); 
					
					$excel->setActiveSheetIndex(0);
				break;
				case 'logo' :
					$FILENAME = 'liste_logo';
					
					$excel->getActiveSheet()->setTitle(utf8_encode('Liste logo')); 

					$header = array(
						'Nom',
						'Code',
						'Actif',
						'Image',
						'Url',
						'Description'
					);

					foreach($header as $key => $val)
					{
						$header[$key] = utf8_encode($header[$key]);
						
						if($xls_style)
							$excel->getActiveSheet()->getColumnDimension(getIndex($key,-1))->setWidth(17); 
					}
					
					$excel->getActiveSheet()->fromArray($header,null,'A1');
					
					if($xls_style)
						$excel->getActiveSheet()->getStyle('A1:'.getIndex(sizeof($header)-1,1))->applyFromArray( $style_header ); 
				
					$count = 2;
					$result = mysql_query('select * from logo
											where 
												logo_status IN (0,2)
											order by logo_name');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{		
							foreach($row as $key => $val)
								$row[ $key ] = utf8_encode($val);
						
							$statut = 'oui';
							if($row['logo_status'] == "2")
								$statut = 'non';
								
							$description = $row['logo_description'];
							if($description == '&nbsp;' || $description == '<br>')
								$description = '';
							
							$printf = array(
												$row['logo_name'],
												$row['logo_code'],
												$statut,
												$row['logo_image'],
												$row['logo_url'],
												$description
											);
							
							$excel->getActiveSheet()->fromArray($printf,null,'A'.$count);
							
							$count ++;
						}
					}
					mysql_free_result($result);
				break;
				case 'coupon' :
					$FILENAME = 'liste_coupon';
					
					$excel->getActiveSheet()->setTitle(utf8_encode('Liste coupon')); 

					$header = array(
						'Nom',
						'Code',
						'Actif',
						'Image',
						'Url',
						'Description'
					);

					foreach($header as $key => $val)
					{
						$header[$key] = utf8_encode($header[$key]);
						
						if($xls_style)
							$excel->getActiveSheet()->getColumnDimension(getIndex($key,-1))->setWidth(17); 
					}
					
					$excel->getActiveSheet()->fromArray($header,null,'A1');
					
					if($xls_style)
						$excel->getActiveSheet()->getStyle('A1:'.getIndex(sizeof($header)-1,1))->applyFromArray( $style_header ); 
				
					$count = 2;
					$result = mysql_query('select * from coupon
											where 
												coupon_status IN (0,2)
											order by coupon_name');
					echo mysql_error();
					if(mysql_num_rows($result)>0)
					{
						while($row = mysql_fetch_assoc($result))
						{		
							foreach($row as $key => $val)
								$row[ $key ] = utf8_encode($val);
						
							$statut = 'oui';
							if($row['coupon_status'] == "2")
								$statut = 'non';
								
							$description = $row['coupon_description'];
							if($description == '&nbsp;' || $description == '<br>')
								$description = '';
							
							$printf = array(
												$row['coupon_name'],
												$row['coupon_code'],
												$statut,
												$row['coupon_image'],
												$row['coupon_url'],
												$description
											);
							
							$excel->getActiveSheet()->fromArray($printf,null,'A'.$count);
							
							$count ++;
						}
					}
					mysql_free_result($result);
				break;
			}
			
			if($_GET['type'] != 'recette' && $xls_style)
				$excel->getActiveSheet()->setAutoFilter('A1:'.getIndex(sizeof($header)-1,1)); 
			
			//$excelWriter = new PHPExcel_Writer_Excel2007($excel);
			$excelWriter = new PHPExcel_Writer_Excel5($excel);
			$excelWriter->save( 'export.xls' );

			header("Content-disposition: attachment; filename=".$FILENAME.".xls"); 
			header("Content-Type: application/vnd.ms-excel"); 
			header("Content-Length: ".filesize( 'export.xls' )); 
			header("Pragma: no-cache"); 
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public"); 
			header("Expires: 0"); 
			readfile( 'export.xls' );

			//unlink( 'export.xls' );			
		break;
	}
}

?>