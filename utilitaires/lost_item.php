<?php

include('../include/main.php');

$ARBO_RECETTE = get_arbo( 'RECETTE' );
$ARBO_PRODUIT = get_arbo( 'PRODUIT' );

$list_recette = array();
$list_recette = get_child_list( $ARBO_RECETTE, 0, $list_recette );

$list_produit = array();
$list_produit = get_child_list( $ARBO_PRODUIT, 0, $list_produit );

$id_recette = '';
foreach($list_recette as $key => $val)
{
	if($id_recette != '')
		$id_recette .= ',';
	$id_recette .= $key;
}

$id_produit = '';
foreach($list_produit as $key => $val)
{
	if($id_produit != '')
		$id_produit .= ',';
	$id_produit .= $key;
}

$res = '';
echo "<br /><br />Arborescence : ";
$result = mysql_query('select arborescence_id from arborescence
						where
							arborescence_status != 1
							and
							arborescence_id NOT IN ('.$id_produit.')
							and
							arborescence_id NOT IN ('.$id_recette.')');
echo mysql_error();
echo mysql_num_rows($result);
if(mysql_num_rows($result)>0)
{
	echo "<br />";
	while($row = mysql_fetch_assoc($result))
	{
		if($res != '')
			$res .= ',';
		$res .= $row['arborescence_id'];
	}
}
mysql_free_result($result);
echo $res;

$res = '';
echo "<br /><br />Produits : ";
$result = mysql_query('select produit_id from produit
						where
							produit_status != 1
							and
							produit_arborescence NOT IN ('.$id_produit.')');
echo mysql_error();
echo mysql_num_rows($result);
if(mysql_num_rows($result)>0)
{
	echo "<br />";
	while($row = mysql_fetch_assoc($result))
	{
		if($res != '')
			$res .= ',';
		$res .= $row['produit_id'];
	}
}
mysql_free_result($result);
echo $res;

$res = '';
echo "<br /><br />Recettes : ";
$result = mysql_query('select recette_id from recette
						where
							recette_status != 1
							and
							recette_arborescence NOT IN ('.$id_recette.')');
echo mysql_error();
echo mysql_num_rows($result);
if(mysql_num_rows($result)>0)
{
	echo "<br />";
	while($row = mysql_fetch_assoc($result))
	{
		if($res != '')
			$res .= ',';
		$res .= $row['recette_id'];
	}
}
mysql_free_result($result);
echo $res;

?>