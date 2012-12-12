<?php
/*
Copyright (C) 2004-2011 NBI SARL, ISVTEC SARL

This file is part of Webfinance.

Webfinance is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Webfinance is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Webfinance; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
include("../inc/main.php");
$title = _("Reporting");
$roles = 'manager,employee';
include("../top.php");
include("nav.php");

function mysqlQuery($q) {
	$r = mysql_query($q) or die("QUERY ERROR: $q ".mysql_error());
	return mysql_fetch_object($r);
}

function numberFormat($n) {
	return number_format( $n, 0 , '.' , ' ' );
}

$year = date('Y'); 
$month = date('m');
if(isset($_GET['year'])) $year = $_GET['year'];
if(isset($_GET['month'])) $month = $_GET['month'];

$recurrent_invoice_rows = array( 
	"%Infogérance, supervision, sauvegarde%",
	"%Infogérance serveur, du%",
	"%Administration, supervision, backup%",
	"%Accès au panel d\'administration pane%", 
	"%Hébergement d\'un serveur%",
	"%Location et hébergement%", 
	"%Hébergement d\'une machine virtuelle%", 
	"%Accès au panel d\'administration%", 
	"%Option « GTR%", 
	"%Hébergement VM du%", 
	"%Location d\'un serveur%", 
	"%hébergement d\'une VM adaptée%", 
	"%Location serveur OVH%", 
	"%Option « Redondance multi%", 
	"%du%au%"
	);	

$select_sum = "SELECT ROUND(SUM(r.qtt*r.prix_ht)) AS total ";

$recurrence = '';
foreach( $recurrent_invoice_rows as $var => $value ) {
	$or = 'OR'; if($var == 0) $or = '';
	$recurrence.="		$or description LIKE '".$value."' \n";
}

$req1 = $select_sum . " 
		FROM webfinance_invoices AS f 
		LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
		WHERE MONTH(f.date_paiement) = $month 
		AND YEAR(f.date_paiement) = $year 
		AND f.is_paye = 1 
		AND f.type_doc = 'facture'";

$req2 = $select_sum . "
		FROM webfinance_invoices AS f 
		LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
		WHERE MONTH(f.date_paiement) = $month 
		AND YEAR(f.date_paiement) = $year 
		AND f.is_paye = 1
		AND f.type_doc = 'facture'
		AND r.id_facture NOT IN (SELECT id_facture FROM webfinance_invoice_rows 
		WHERE ". $recurrence . ")";

$req3 = $select_sum . "
		FROM webfinance_invoices AS f 
		LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
		WHERE f.is_envoye = 1 
		AND f.is_abandoned = 0 
		AND f.type_doc = 'devis'";

$req3a = "SELECT COUNT(*) AS total
		FROM webfinance_invoices AS f  
		WHERE f.is_envoye = 1 
		AND f.is_abandoned = 0 
		AND f.type_doc = 'devis'";

$req3b = "SELECT id_facture 
		FROM webfinance_invoices AS f  
		WHERE f.is_envoye = 1 
		AND f.is_abandoned = 0 
		AND f.type_doc = 'devis'";
		
$req3c = "SELECT date_created, num_facture, id_facture, id_client
		FROM webfinance_invoices AS f  
		WHERE f.is_envoye = 1 
		AND f.is_abandoned = 0 
		AND f.type_doc = 'devis'";

$req4 = $select_sum . "
		FROM webfinance_invoices AS f 
		LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
		WHERE YEAR(f.date_paiement) = $year 
		AND f.is_paye = 1 
		AND f.type_doc = 'facture'";

$req4a = $select_sum . "
		FROM webfinance_invoices AS f 
		LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
		WHERE YEAR(f.date_paiement) = $year 
		AND f.is_paye = 1
		AND f.type_doc = 'facture'
		AND r.id_facture NOT IN (SELECT id_facture FROM webfinance_invoice_rows 
		WHERE ". $recurrence . ")";

$req5 = $select_sum . "
		FROM webfinance_invoices AS f 
		LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
		LEFT JOIN webfinance_clients AS c ON f.id_client = c.id_client 
		WHERE YEAR(c.date_created) = $year 
		AND f.is_paye = 1 
		AND f.type_doc = 'facture'";

$req5a = $select_sum . "
		FROM webfinance_invoices AS f 
		LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture
		LEFT JOIN webfinance_clients AS c ON f.id_client = c.id_client 
		WHERE YEAR(c.date_created) = $year 
		AND f.is_paye = 1 
		AND f.type_doc = 'facture'
		AND r.id_facture NOT IN (SELECT id_facture FROM webfinance_invoice_rows 
		WHERE ". $recurrence . ")";

$req5b = "SELECT COUNT(*) AS total
	FROM webfinance_clients AS c  
	WHERE YEAR(c.date_created) = $year";

$req5c = "SELECT id_client, nom, date_created
	FROM webfinance_clients AS c  
	WHERE YEAR(c.date_created) = $year";
	
if(isset($_GET['popup'])) { 
	
$Facture = new Facture();

?>	
	<br /><br />
	<table border="0" cellspacing="0" cellpadding="10" class="framed">
	<?
	if($_GET['popup'] == 'devis_ip') {
		?>
		<tr class="row_header" style="text-align: center;">
			<td>N devis</td>
			<td>Date de création</td>
			<td>Client</td>
		</tr>
		<?
		$result = mysql_query($req3c);
		while ($row = mysql_fetch_object($result)) {
			$info_facture = $Facture->getInfos($row->id_facture)
	?>	
		<tr>
			<td><a href="/prospection/edit_facture.php?id_facture=<?=$row->id_facture?>">DE<?=$row->num_facture?></a></td>
			<td><?=$row->date_created?></td>
			<td><a href="/prospection/fiche_prospect.php?onglet=contacts&id=<?=$row->id_client?>"><?=$info_facture->nom_client?></a></td>
		</tr>
		<?
		}
	}
	
	if($_GET['popup'] == 'new_client') {
		?>
		<tr class="row_header" style="text-align: center;">
			<td>Client</td>
			<td>Date de création</td>
		</tr>
		<?
			$result = mysql_query($req5c);
			while ($row = mysql_fetch_object($result)) {
		?>		
		<tr>
			<td><a href="/prospection/fiche_prospect.php?onglet=contacts&id=<?=$row->id_client?>"><?=$row->nom?></a></td>
			<td><?=$row->date_created?></td>
		</tr>
		
		<?
		}
	}
	?>
	</table>
	<?
	include("../bottom.php"); 
	exit;
}

$total4 = mysqlQuery($req4)->total;
$total4_ponctu = mysqlQuery($req4a)->total;
$recu4 = $total4 - $total4_ponctu;
$perc_recu4 = round(($recu4*100)/$total4);
$perc_ponctu4 = round(($total4_ponctu*100)/$total4);

$total5 = mysqlQuery($req5)->total;
$total5_ponctu = mysqlQuery($req5a)->total;
$recu5 = $total5 - $total5_ponctu;
$perc_recu5 = round(($recu5*100)/$total5);
$perc_ponctu5 = round(($total5_ponctu*100)/$total5);
?>
<br />
<form onsubmit="">
	Period: <select name="month">
<?php for ($lmonth = 01 ; $lmonth <= 12 ; $lmonth++) { ?>
	<option value="<?php echo $lmonth ?>" <? if($lmonth == $month) echo 'selected' ?>><?php echo $lmonth; ?></option>
	<?php } ?>	
</select>
<select name="year">
<?php for ($lyear = 2006 ; $lyear <= 2020 ; $lyear++) { ?>
	<option value="<?php echo $lyear ?>" <? if($lyear == $year) echo 'selected' ?>><?php echo $lyear; ?></option>
	<?php } ?>	
</select> <input style="width: 50px; height: 20px;" class="bordered" type="submit" name="send" value="Display"> <input type="checkbox" name="req" value="1"> (show queries)
</form>
<br />
<br />
<table border="0" cellspacing="0" cellpadding="10" class="framed">
	<tr class="row_header" style="text-align: center;"><td colspan="3">Reporting</td></tr>
	<tr class="misc1">
		<td><!-- REQ1 -->1. CA par mois HT Facturé</th><td><?=numberFormat(mysqlQuery($req1)->total)?> €</td><td>CA par mois HT Facturé</td>
	</tr>
	<tr class="misc2">
		<td><!-- REQ2 -->2. CA sur opportunités gagnées</th><td><?=numberFormat(mysqlQuery($req2)->total)?>  €</td><td>CA HT facturé sur les opportunités gagnées ce mois (tout ce qui n est pas récurrent (nouveaux clients ou clients existants))</td>
	</tr>
	<tr class="misc1">
		<td><!-- REQ3 -->3. CA des devis envoyé mais pas transformé</th><td><?=numberFormat(mysqlQuery($req3)->total)?> € - <a href="/prospection/reporting.php?popup=devis_ip&month=<?=$month?>&year=<?=$year?>"><?=numberFormat(mysqlQuery($req3a)->total)?> devis</a></td><td>Indépendant d'une période (donc a un instant T) : CA des devis “Sent” mais pas transformés en Facture</td>
	</tr>
	<tr class="misc2">
		<td><!-- REQ4 -->4. CA annuel de l'année</th><td><?=numberFormat(mysqlQuery($req4)->total)?> € <br /><?=numberFormat($recu4)?> €, <?=$perc_recu4?>% récurrent <br /> <?=numberFormat($total4_ponctu)?> €, <?=$perc_ponctu4?>% ponctuel</td><td></td>
	</tr>
	<tr class="misc1">
		<td><!-- REQ5 -->5. CA des nouveaux clients de l'année</a>
		</th><td><?=numberFormat(mysqlQuery($req5)->total)?> € - <a href="/prospection/reporting.php?popup=new_client&month=<?=$month?>&year=<?=$year?>"><?=numberFormat(mysqlQuery($req5b)->total)?> nouveaux clients</a> <br /> <?=numberFormat($recu5)?> €, <?=$perc_recu5?>% récurrent <br /> <?=numberFormat($total5_ponctu)?> €, <?=$perc_ponctu5?>% ponctuel</td><td></td>
	</tr>
</table>
<? if(isset($_GET['req'])) { ?>
<pre>
REQ1
<?=$req1?>

REQ2
<?=$req2?>

REQ3
<?=$req3?>

<?=$req3a?>

<?=$req3b?>

<?=$req3c?>

REQ4
<?=$req4?>

<?=$req4a?>

REQ5
<?=$req5?>

<?=$req5a?>

<?=$req5b?>

<?=$req5c?>
</pre>
<? } ?>
<br/>
<br/>
<?
include("../bottom.php"); 
?>
