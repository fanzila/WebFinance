<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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
?>
<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php
// $Id: facturation.php 551 2007-08-02 05:16:27Z gassla $

include("../inc/main.php");
$title = _("Billing");
$roles = "manager,employee,accounting";
include("../top.php");
include("nav.php");

$listing_limit = 300;
$Facture = new Facture();
$total_ca_ht = 0;

$where_clause = "1";
if (isset($_GET['id_client']) && $_GET['id_client']!="") {
  $where_clause .= " AND f.id_client=".$_GET['id_client'];
}
if (isset($_GET['mois']) && $_GET['mois'] != "") {
  $where_clause .= " AND date_format(f.date_facture, '%Y%m')='".$_GET['mois']."'";
}
if (isset($_GET['type']) && $_GET['type']!="") {
  switch ($_GET['type']) {
    case "unpaid" : $where_clause .= " AND f.is_paye=0 AND is_abandoned = 0 "; break;
	case "unpaid_late" : $where_clause .= " AND f.is_paye=0 AND is_abandoned = 0 AND date_facture<=now() - INTERVAL 34 DAY "; break;
    case "paid" : $where_clause .= " AND f.is_paye=1 "; break;
  }
}

$GLOBALS['_SERVER']['QUERY_STRING'] = preg_replace("/sort=\w+\\&*+/", "", $GLOBALS['_SERVER']['QUERY_STRING']);

if(!isset($_GET['sort']))
  $_GET['sort']='date';

switch ($_GET['sort']) {
  case "num" : $order_clause = "f.num_facture DESC"; break;
  case "client" : $order_clause = "c.nom"; break;
  case "montant_ttc" :
  case "montant_ht" : $order_clause = "f.id_client"; break;
  case "date" :
  default : $order_clause = "f.date_facture DESC";
}

?>

<table border="0" cellspacing="5" cellpadding="0">
<tr valign="top"><td>

<table border=0 cellspacing=0 cellpadding=3 style="border: solid 1px black;">
<tr align=center class=row_header>
  <td></td>
  <td><a href="?sort=date&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Invoice date') ?></a></td>
  <td colspan="2"><a href="?sort=num&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Invoice #') ?></a></td>
  <td><a href="?sort=client&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Client') ?></a></td>
  <td><a href="?sort=montant_ht&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('HT') ?></a></td>
  <td><a href="?sort=montant_ttc&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('TTC') ?></a></td>
  <td></td>
</tr>
<?php
$total_ca_ht = 0;
$total_ca_ttc = 0;
$result = mysql_query("SELECT f.id_facture
                       FROM webfinance_invoices f , webfinance_clients as c
                       WHERE type_doc='facture'
                       AND f.num_facture!=''
                       AND f.id_client=c.id_client
                       AND f.date_facture<=now()
                       AND ".$where_clause."
                       ORDER BY $order_clause LIMIT $listing_limit") or wf_mysqldie();
$mois = array();
while (list($id_facture) = mysql_fetch_array($result)) {
  $count++;
  $fa = $Facture->getInfos($id_facture);
  $class = ($count%2 == 0)?"even":"odd";
  $icon = $fa->is_paye?"paid":"not_paid";
  $total_ca_ht += $fa->total_ht;
  $total_ca_ttc += $fa->total_ttc;
  $mois[$fa->mois_facture]++;

   $description = "";
   $result2 = mysql_query("SELECT description FROM webfinance_invoice_rows WHERE id_facture=".$fa->id_facture);
   while (list($desc) = mysql_fetch_array($result2)) {
     $desc = preg_replace("/\r\n/", " ", $desc);
     $desc = preg_replace("/\"/", "", $desc);
     $desc = preg_replace("/\'/", "", $desc);
     $description .= $desc."<br/>";
   }
   mysql_free_result($result2);

  print <<<EOF
<tr onMouseOut="UnTip();" onmouseover="Tip('$description');" class="row_$class">
  <td>$count</td>
  <td>$fa->nice_date_facture</td>
  <td><img src="/imgs/icons/$icon.gif" alt="$icon" /></td>
  <td>FA$fa->num_facture</td>
  <td><a href="fiche_prospect.php?id=$fa->id_client">$fa->nom_client</a></td>
  <td>$fa->nice_total_ht&euro;</td>
  <td>$fa->nice_total_ttc&euro;</td>
  <td><a href="gen_facture.php?id=$fa->id_facture"><img src="/imgs/icons/pdf.gif" alt="Chopper" /></a>
      <a href="edit_facture.php?id_facture=$fa->id_facture"><img src="/imgs/icons/edit.gif" alt="Modifier" /></a></td>
</tr>
EOF;
}
?>
</table>
<br/>

</td><td>

<form action="facturation.php" method="get">
<table border="0" cellspacing="0" cellpadding="3" style="border: solid 1px black;">
<tr align="center" class="row_header">
  <td colspan="2">Filtre simple</td>
</tr>
<tr>
  <td>Total CA</td>
  <td>
    <b><?= number_format($total_ca_ht, 2, ',', ' ') ?>&euro; HT</b> /
    <b><?= number_format($total_ca_ttc, 2, ',', ' ') ?>&euro; TTC</b>
  </td>
</tr>
<tr>
  <td>Client</td>
  <td>
    <select style="width: 200px;" name="id_client" onchange="this.form.submit();" >
      <option value="">Tous</option>
      <?php
      $result = mysql_query("SELECT c.id_client,c.nom
                             FROM webfinance_clients as c, webfinance_invoices as f
                             WHERE f.id_client=c.id_client
                             GROUP BY c.id_client
                             ORDER BY nom");
      while (list($id, $nom) = mysql_fetch_array($result)) {
        printf('<option value="%d"%s>%s</option>'."\n", $id, ($id==$_GET['id_client'])?" selected":"", $nom);
      }
      ?>
    </select>
  </td>
</tr>
<tr>
  <td>Type</td>
  <td>
    <select name="type" onchange="this.form.submit();" >
    <?php
    $choices = array(
                 "Toutes" => "",
                 "Impayées" => "unpaid",
				 "Impayées en retard" => "unpaid_late",
                 "Payées" => "paid",
               );
    foreach ($choices as $n=>$v) {
      printf('<option value="%s"%s>%s</option>', $v, ($v==$_GET['type'])?" selected":"", $n);
    }
    ?>
    </select>
  </td>
</tr>
<tr>
  <td>Mois</td>
  <td>
    <select name="mois" onchange="this.form.submit();" >
    <option value="">Tous</option>
    <?php
    ksort($mois);
    foreach ($mois as $n=>$v) {
      printf('<option value="%s"%s>%s (%d factures)</option>', $n, ($n==$_GET['mois'])?" selected":"", $n, $v);
    }
    ?>
    </select>
  </td>
</tr>
</table>
</form>

</td></tr>
</table>
(LIMIT <?=$listing_limit?>)

<?php

$Revision = '$Revision: 551 $';
include("../bottom.php");

?>
