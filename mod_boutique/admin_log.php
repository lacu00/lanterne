<?php

include('../include/main.php');

if(isset($_GET['do']))
{
	switch($_GET['do'])
	{
		case 'export_liste' :
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
			
			$FILENAME = 'compteurs';
			
			$excel->getActiveSheet()->setTitle(utf8_encode('Compteurs')); 

			$header = array(
				'Date',
				'Heure',
				'Type',
				'Code objet',
				'Détails',
				'IP'
			);

			foreach($header as $key => $val)
			{
				$header[$key] = utf8_encode($header[$key]);
				$excel->getActiveSheet()->getColumnDimension(getIndex($key,-1))->setWidth(17); 
			}
			$excel->getActiveSheet()->fromArray($header,null,'A1');
			$excel->getActiveSheet()->getStyle('A1:'.getIndex(sizeof($header)-1,1))->applyFromArray( $style_header ); 
			
			$count = 2;
			$date_deb = explode('/',$_GET['date_deb']);
			$date_fin = explode('/',$_GET['date_fin']);
			
			$result = mysql_query('select * from log where date(log_date) >= "'.$date_deb[2].'-'.$date_deb[1].'-'.$date_deb[0].'" and date(log_date) <= "'.$date_fin[2].'-'.$date_fin[1].'-'.$date_fin[0].'" order by log_date DESC');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$date = explode(' ',$row['log_date']);
					$d = explode('-',$date[0]);
					$h = $date[1];
					
					foreach($row as $key => $val)
						$row[ $key ] = utf8_encode($val);
						
					$printf = array(
										$d[2].'/'.$d[1].'/'.$d[0],
										$h,
										$row['log_type'],
										$row['log_item_code'],
										$row['log_details'],
										$row['log_ip']
									);
					
					$excel->getActiveSheet()->fromArray($printf,null,'A'.$count);
					
					$count ++;
				}
			}
			mysql_free_result($result);
			
			$excel->getActiveSheet()->setAutoFilter('A1:'.getIndex(sizeof($header)-1,1)); 
			
			//$excelWriter = new PHPExcel_Writer_Excel2007($excel);
			$excelWriter = new PHPExcel_Writer_Excel5($excel);
			$excelWriter->save( '../export/export.xls' );

			header("Content-disposition: attachment; filename=".$FILENAME.".xls"); 
			header("Content-Type: application/vnd.ms-excel"); 
			header("Content-Length: ".filesize( '../export/export.xls' )); 
			header("Pragma: no-cache"); 
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public"); 
			header("Expires: 0"); 
			readfile( '../export/export.xls' );

			//unlink( '../export/export.xls' );		
		break;
	}

	exit();
}

if(isset($_POST['do']))
{
	switch($_POST['do'])
	{
		case 'refresh' :
			$res = '';

			$res .= '<table cellspacing="0"><tr>';
			$res .= '<td><div class="td" style="font-weight:bold;">Date</div></td>';
			$res .= '<td><div class="td" style="font-weight:bold;">Heure</div></td>';
			$res .= '<td><div class="td" style="font-weight:bold;">Type</div></td>';
			$res .= '<td><div class="td" style="font-weight:bold;">Code objet</div></td>';
			$res .= '<td><div class="td" style="font-weight:bold;">D&eacute;tails</div></td>';	
			$res .= '<td><div class="td" style="font-weight:bold;">IP</div></td>';				
			$res .= '</tr>';
			
			$date_deb = explode('/',$_POST['date_deb']);
			$date_fin = explode('/',$_POST['date_fin']);
			
			$result = mysql_query('select * from log where date(log_date) >= "'.$date_deb[2].'-'.$date_deb[1].'-'.$date_deb[0].'" and date(log_date) <= "'.$date_fin[2].'-'.$date_fin[1].'-'.$date_fin[0].'" order by log_date DESC');
			echo mysql_error();
			if(mysql_num_rows($result)>0)
			{
				while($row = mysql_fetch_assoc($result))
				{
					$date = explode(' ',$row['log_date']);
					$d = explode('-',$date[0]);
					$h = $date[1];
					
					if($row['log_details']=='')
						$row['log_details'] = '-';
						
					if($row['log_item_code']=='')
						$row['log_item_code'] = '-';
						
					if($row['log_ip']=='')
						$row['log_ip'] = '-';
				
					$res .= '<tr>';
					$res .= '<td><div class="td">'.$d[2].'/'.$d[1].'/'.$d[0].'</div></td>';
					$res .= '<td><div class="td">'.$h.'</div></td>';
					$res .= '<td><div class="td">'.$row['log_type'].'</div></td>';
					$res .= '<td><div class="td">'.$row['log_item_code'].'</div></td>';
					$res .= '<td><div class="td">'.$row['log_details'].'</div></td>';	
					$res .= '<td><div class="td">'.$row['log_ip'].'</div></td>';						
					$res .= '</tr>';
				}
			}
			mysql_free_result($result);
			
			$res .= '</table>';
			
			echo utf8_encode($res);
		break;
	}
}

?>