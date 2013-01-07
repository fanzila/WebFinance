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

$year = date('Y'); 
$month = date('m');

$select_gtr = "SELECT c.id_client, c.nom, r.description, r.qtt, f.id_facture
			FROM webfinance_invoices AS f 
			LEFT JOIN webfinance_invoice_rows AS r ON f.id_facture = r.id_facture 
			LEFT JOIN webfinance_clients AS c ON f.id_client = c.id_client ";

$where_gtr = " AND qtt > 0 
		AND f.id_client IN (SELECT id_facture 
			FROM webfinance_invoices 
			WHERE f.period <> 'none' 
			AND f.type_doc = 'facture')";

$req6 					= $select_gtr. " WHERE r.description LIKE '%GTR%'	AND r.description NOT LIKE '%Pas de GTR%' ".$where_gtr." GROUP BY c.id_client ORDER BY c.nom ASC";

$result = mysql_query($req6) or die("QUERY ERROR: $q ".mysql_error());

?>	
<br />
<br />
<table border="0" cellspacing="0" cellpadding="10" class="framed">
	<tr class="row_header" style="text-align: center;">
		<td>Clients</td><td>Services</td>
	</tr>
	<? while ($row = mysql_fetch_object($result)) { ?>
	<tr class="misc2">
		<td><a href="/prospection/fiche_prospect.php?onglet=billing&id=<?=$row->id_client?>"><?=$row->nom?></a></td><td><a href="/prospection/edit_facture.php?id_facture=<?=$row->id_facture?>"><?=$row->description?></a></td>	
	</tr>
	<? } ?>
</table>
<br/>
<br/>
<?
include("../bottom.php"); 
?>