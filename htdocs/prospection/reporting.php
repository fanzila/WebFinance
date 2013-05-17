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

if(isset($_GET['disp_req'])) $disp_req = true;

function mysqlQuery($q) {
	$r = mysql_query($q)
          or die("QUERY ERROR: $q ".mysql_error());
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
		
$req3c = "SELECT f.date_created, f.num_facture, f.id_client, f.id_facture, 
		(SELECT ROUND(SUM(r.qtt*r.prix_ht)) AS total FROM webfinance_invoice_rows AS r where id_facture = f.id_facture) AS total  
		FROM webfinance_invoices AS f  
		WHERE f.is_envoye = 1 
		AND f.is_abandoned = 0 
		AND f.type_doc = 'devis' 
		ORDER BY f.date_created ASC";

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

$req5c = "SELECT c.id_client, c.nom, c.date_created, 
	(SELECT ROUND(SUM(r.qtt*r.prix_ht)) AS total 
	FROM webfinance_invoices AS f 
		LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
			WHERE f.type_doc = 'facture' 
			AND is_paye = 1 
			AND f.id_client = c.id_client) AS total 
	FROM webfinance_clients AS c 
	WHERE YEAR(c.date_created) = $year 
	AND id_company_type = 1
	ORDER BY total DESC";
	
$select_gtr = "SELECT count(*) AS total
			FROM webfinance_invoices AS f 
			LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
			LEFT JOIN webfinance_clients AS c ON f.id_client = c.id_client ";

$select_gtr_clt = "SELECT c.id_client, c.nom, r.qtt AS qty
			FROM webfinance_invoices AS f 
			LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
			LEFT JOIN webfinance_clients AS c ON f.id_client = c.id_client ";

$where_gtr = " AND qtt > 0 
		AND f.id_client IN (SELECT id_facture 
			FROM webfinance_invoices 
			WHERE f.period <> 'none' 
			AND f.type_doc = 'facture')";

$req6 					= $select_gtr. " WHERE r.description LIKE '%GTR%'	AND r.description NOT LIKE '%Pas de GTR%' ".$where_gtr;
$req6_gtr4				= $select_gtr ." WHERE r.description LIKE '%GTR%' AND description LIKE '%bureau%' AND r.description NOT LIKE '%Pas de GTR%' ".$where_gtr;
$req6_gtr4_gold 		= $select_gtr ." WHERE r.description LIKE '%GTR Gold%' ". $where_gtr;
$req6_gtr4_prems 		= $select_gtr ." WHERE r.description LIKE '%GTR Premium%' ". $where_gtr;
$req6_gtr4_plus1 		= $select_gtr ." WHERE r.description LIKE '%GTR h+1%'". $where_gtr;

$req6_assu 				= $select_gtr ." WHERE r.description LIKE '%ption%ssuran%'". $where_gtr;
$req6_redon				= $select_gtr ." WHERE r.description LIKE '%ption%edondance%'". $where_gtr;
$req6_amaz 				= $select_gtr ." WHERE r.description LIKE '%mazon%'". $where_gtr;

$req6_clt 				= $select_gtr_clt ." WHERE r.description LIKE '%GTR%'	AND r.description NOT LIKE '%Pas de GTR%' ".$where_gtr;
$req6_gtr4_clt			= $select_gtr_clt ." WHERE r.description LIKE '%GTR%' AND description LIKE '%bureau%' AND r.description NOT LIKE '%Pas de GTR%' ".$where_gtr;
$req6_gtr4_gold_clt 	= $select_gtr_clt ." WHERE r.description LIKE '%GTR Gold%' ". $where_gtr;
$req6_gtr4_prems_clt 	= $select_gtr_clt ." WHERE r.description LIKE '%GTR Premium%' ". $where_gtr;
$req6_gtr4_plus1_clt 	= $select_gtr_clt ." WHERE r.description LIKE '%GTR h+1%'". $where_gtr;

$req6_assu_clt 			= $select_gtr_clt ." WHERE r.description LIKE '%ption%ssuran%'". $where_gtr;
$req6_redon_clt 		= $select_gtr_clt ." WHERE r.description LIKE '%ption%edondance%'". $where_gtr;
$req6_amaz_clt 			= $select_gtr_clt ." WHERE r.description LIKE '%mazon%'". $where_gtr;

$total4 			= mysqlQuery($req4)->total;
$total4_ponctu		= mysqlQuery($req4a)->total;
$recu4 				= $total4 - $total4_ponctu;
$perc_recu4 		= round(($recu4*100)/$total4);
$perc_ponctu4 		= round(($total4_ponctu*100)/$total4);

$total5 			= mysqlQuery($req5)->total;
$total5_ponctu 		= mysqlQuery($req5a)->total;
$recu5	 			= $total5 - $total5_ponctu;
$perc_recu5			= round(($recu5*100)/$total5);
$perc_ponctu5 		= round(($total5_ponctu*100)/$total5);

$total6 			= mysqlQuery($req6)->total;
$total6_gtr 		= mysqlQuery($req6_gtr4)->total;
$total6_gtr_per 	= round(($total6_gtr*100)/$total6,2);
$total6_gtr4g		= mysqlQuery($req6_gtr4_gold)->total;
$total6_gtr4g_per	= round(($total6_gtr4g*100)/$total6,2);
$total6_gtrprem 	= mysqlQuery($req6_gtr4_prems)->total;
$total6_gtrprem_per	= round(($total6_gtrprem*100)/$total6,2);
$total6_plus1		= mysqlQuery($req6_gtr4_plus1)->total;
$total6_plus1_per	= round(($total6_plus1*100)/$total6,2);

$total6_assu		= mysqlQuery($req6_assu)->total;
$total6_redon		= mysqlQuery($req6_redon)->total;
$total6_amaz		= mysqlQuery($req6_amaz)->total;

if(isset($_GET['popup'])) { 

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
			<td>Amount</td>
		</tr>
		<?
		$Facture = new Facture();
		$result = mysql_query($req3c)
                   or die("QUERY ERROR: $q ".mysql_error());
		while ($row = mysql_fetch_object($result)) {
			$info_facture = $Facture->getInfos($row->id_facture)
	?>	
		<tr>
			<td><a href="/prospection/edit_facture.php?id_facture=<?=$row->id_facture?>">DE<?=$row->num_facture?></a></td>
			<td><?=$row->date_created?></td>
			<td><a href="/prospection/fiche_prospect.php?onglet=contacts&id=<?=$row->id_client?>"><?=$info_facture->nom_client?></a></td>
			<td><?=numberFormat($row->total)?></a></td>
		</tr>
		<?
		}
	}
	
	if($_GET['popup'] == 'new_client') {
		?>
		<tr class="row_header" style="text-align: center;">
			<td>Client</td>
			<td>Date de création</td>
			<td>CA HT</td>
		</tr>
		<?
			$result = mysql_query($req5c)
			  or die("QUERY ERROR: $q ".mysql_error());

			while ($row = mysql_fetch_object($result)) {
		?>		
		<tr>
			<td><a href="/prospection/fiche_prospect.php?onglet=contacts&id=<?=$row->id_client?>"><?=$row->nom?></a></td>
			<td><?=$row->date_created?></td>
			<td><?=numberFormat($row->total)?></td>
		</tr>
		
		<?
		}
	}
	
	if($_GET['popup'] == 'service_client') {
		?>
		<tr class="row_header" style="text-align: center;">
			<td>Client</td>
			<td>Services</td>
			<td>QTY</td>
		</tr>
		<?
		$srv	= $_GET['service'];
		$var	= 'req6_'.$srv.'_clt';
		$toreq	= $$var;
		
		$result = mysql_query($toreq)
                  or die("QUERY ERROR: $q ".mysql_error());
		while ($row = mysql_fetch_object($result)) {
	?>	
		<tr>
			<td><a href="/prospection/fiche_prospect.php?onglet=contacts&id=<?=$row->id_client?>"><?=$row->nom?></a></td>
			<td><?=strtoupper($srv)?></td>
			<td><?=$row->qty?></td>
			
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
?>
<br />
<form onsubmit="">
	Period: <select name="month">
<?php for ($lmonth = 01 ; $lmonth <= 12 ; $lmonth++) { ?>
	<option value="<?php echo $lmonth ?>" <? if($lmonth == $month) echo 'selected' ?>><?=strftime('%B', mktime(0, 0, 0, $lmonth)); ?></option>
	<?php } ?>	
</select>
<select name="year">
<?php for ($lyear = 2006 ; $lyear <= 2020 ; $lyear++) { ?>
	<option value="<?php echo $lyear ?>" <? if($lyear == $year) echo 'selected' ?>><?php echo $lyear; ?></option>
	<?php } ?>	
</select> <input style="width: 50px; height: 20px;" class="bordered" type="submit" name="send" value="Display"> <input type="checkbox" name="disp_req" value="1"> (show queries)
</form>
<br />
<br />
<table border="0" cellspacing="0" cellpadding="10" class="framed">
	<tr class="row_header" style="text-align: center;"><td colspan="3">Reporting</td></tr>
	<tr class="misc1">
		<td><!-- REQ1 -->1. CA par mois HT Facturé</td><td><?=numberFormat(mysqlQuery($req1)->total)?> €</td><td>CA par mois HT Facturé</td>
	</tr>
	<tr class="misc2">
		<td><!-- REQ2 -->2. CA sur opportunités gagnées</td><td><?=numberFormat(mysqlQuery($req2)->total)?>  €</td><td>CA HT facturé sur les opportunités gagnées ce mois (tout ce qui n est pas récurrent (nouveaux clients ou clients existants))</td>
	</tr>
	<tr class="misc1">
		<td><!-- REQ3 -->3. CA des devis envoyés mais pas transformés</td><td><?=numberFormat(mysqlQuery($req3)->total)?> € - <a href="/prospection/reporting.php?popup=devis_ip&month=<?=$month?>&year=<?=$year?>"><?=numberFormat(mysqlQuery($req3a)->total)?> devis</a></td><td>Indépendant d'une période (donc a un instant T) : CA des devis “Sent” mais pas transformés en Facture</td>
	</tr>
	<tr class="misc2">
		<td><!-- REQ4 -->4. CA annuel de l'année</td><td><?=numberFormat(mysqlQuery($req4)->total)?> € <br /><?=numberFormat($recu4)?> €, <?=$perc_recu4?>% récurrent <br /> <?=numberFormat($total4_ponctu)?> €, <?=$perc_ponctu4?>% ponctuel</td><td></td>
	</tr>
	<tr class="misc1">
		<td><!-- REQ5 -->5. CA des nouveaux clients de l'année</a>
		</td><td><?=numberFormat(mysqlQuery($req5)->total)?> € - <a href="/prospection/reporting.php?popup=new_client&month=<?=$month?>&year=<?=$year?>"><?=numberFormat(mysqlQuery($req5b)->total)?> nouveaux clients</a> <br /> <?=numberFormat($recu5)?> €, <?=$perc_recu5?>% récurrent <br /> <?=numberFormat($total5_ponctu)?> €, <?=$perc_ponctu5?>% ponctuel</td><td></td>
	</tr>

	<tr class="misc2">
		<td><!-- REQ6 -->6. Nombre de serveurs en infogérance avec GTR</td><td>
			<table width="300" border="0" cellspacing="0" cellpadding="0">
				<tr><td width="150">Total</td><td width="50"><?=$total6?></td><td width="100" align="right">100%</td></tr>
				<tr><td><a href="/prospection/reporting.php?popup=service_client&service=gtr4">GTR +4 HO</a></td><td><?=$total6_gtr?></td><td align="right"><?=$total6_gtr_per?>%</td></tr>
				<tr><td><a href="/prospection/reporting.php?popup=service_client&service=gtr4_gold">GTR +4 HNO Gold</a></td><td><?=$total6_gtr4g?></td><td align="right"><?=$total6_gtr4g_per?>%</td></tr>
				<tr><td><a href="/prospection/reporting.php?popup=service_client&service=gtr4_prems">GTR +2 HNO Premium</a></td><td><?=$total6_gtrprem?></td><td align="right"><?=$total6_gtrprem_per?>%</td></tr>
				<tr><td><a href="/prospection/reporting.php?popup=service_client&service=gtr4_plus1">GTR +1 HNO</a></td><td><?=$total6_plus1?></td><td align="right"><?=$total6_plus1_per?>%</td></tr>		
			</table>
		</td><td></td>
	</tr>

	<tr class="misc1">
		<td><!-- REQ6 -->6a. Nombre de serveurs en infogérance avec services</td><td>
			<table width="300" border="0" cellspacing="0" cellpadding="0">
				<tr><td><a href="/prospection/reporting.php?popup=service_client&service=assu">Assurance, garanties jusqu'à 150 000 €</a></td><td><?=$total6_assu?></td></tr>
				<tr><td><a href="/prospection/reporting.php?popup=service_client&service=redon">Redondance</a></td><td><?=$total6_redon?></td></tr>
				<tr><td><a href="/prospection/reporting.php?popup=service_client&service=amaz">Amazon Elastic Cloud</a></td><td><?=$total6_amaz?></td></tr>					
			</table>
		</td><td></td>	
	</tr>

	<tr class="misc2">
		<td>CA facturé en fonction du type de presta sur la periode</td>
		<td>
		  <table>

<?

$req = mysql_query("SELECT
    p.nom AS category_name,
    ROUND(SUM(ir.qtt*ir.prix_ht)) AS excl_taxes
FROM webfinance_invoices i
JOIN webfinance_type_presta p ON p.id_type_presta=i.id_type_presta
JOIN webfinance_invoice_rows ir ON ir.id_facture = i.id_facture
WHERE type_doc='facture'
  AND MONTH(i.date_paiement) = '$month'
  AND YEAR(i.date_paiement) = '$year'
GROUP by i.id_type_presta
ORDER BY excl_taxes DESC")
  or die(mysql_error());

while($row = mysql_fetch_array($req)) {
  echo "<tr> <td> $row[category_name] </td>";
  echo "<td align=\"right\"> $row[excl_taxes] &euro; </td> </tr>";
}

?>

		  </table>
		</td>
	</tr>
</table>
<br/>
<br/>
<?
include("../bottom.php"); 
?>