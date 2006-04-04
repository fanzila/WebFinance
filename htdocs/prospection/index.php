<?php 
// 
// This file is part of « Backoffice NBI »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<?php
// $Id$

include("../inc/backoffice.php");
include("../top.php");
include("nav.php");

// House keeping : lister les factures inpayées et marquer les clients qui en ont.
// FIXME : should go in save_facture.php
mysql_query("UPDATE client SET has_unpaid=false,has_devis=false");
$result = mysql_query("select c.id_client,count(*) as has_unpaid 
                       FROM facture as f,client as c 
                       WHERE f.is_paye=0 
                       AND f.type_doc='facture' 
                       AND f.date_facture<=now() 
                       AND f.id_client=c.id_client 
                       group by c.id_client") or die(mysql_error());
while (list($id_client) = mysql_fetch_array($result)) {
  mysql_query("UPDATE client SET has_unpaid=true WHERE id_client=$id_client");
}
$result = mysql_query("SELECT c.id_client,count(*) as has_unpaid from facture as f,client as c WHERE f.is_paye=0 AND f.type_doc='devis' AND f.id_client=c.id_client group by c.id_client");
while (list($id_client) = mysql_fetch_array($result)) {
  mysql_query("UPDATE client SET has_devis=true WHERE id_client=$id_client");
}

// Filtres et tris
$where_clause = "1";
if (isset($_GET['q']) && ($_GET['q']!=0)) { $where_clause = "te.id_type_entreprise=".$_GET['q']; }

if (preg_match("/[a-zA-Z ]+/", $_GET['namelike'])) {
  $where_clause .= " AND c.nom LIKE '%".$_GET['namelike']."%'";
}
$where_clause .= " AND c.id_type_entreprise=te.id_type_entreprise ";

$GLOBALS['_SERVER']['QUERY_STRING'] = preg_replace("/sort=\w+\\&*+/", "", $GLOBALS['_SERVER']['QUERY_STRING']);

?>
<table border="0" cellspacing="0" cellpadding="0">
<tr valign="top"><td rowspan="2">

<table border="0" width="500" cellspacing=0 cellpadding=3 style="border: solid 1px black; float: left; margin: 10px;">
<tr class="row_header" style="text-align: center;"> 
  <td><a href="?sort=du&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>">&euro</a></td>
  <td width="200"><a href="?sort=nom&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>">Raison sociale</a></td>
  <td><a href="?sort=ca_total_ht&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>">CA &euro; HT</a></td>
  <td><a href="?sort=ca_total_ht_year&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>">CA 1 an</a></td>
  <td><a href="?sort=total_du_ht&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>">Encours</a></td>
</tr>
<?php

$critere = "has_unpaid desc,has_devis desc, c.nom"; 
if ((!isset($_GET['sort'])) && (isset($User->prefs->tri_entreprise))) {
  $_GET['sort'] = $User->prefs->tri_entreprise;
}
switch ($_GET['sort']) { 
  case "nom" : $critere = "c.nom"; break;
  case "du" : $critere = "has_unpaid desc,has_devis desc, c.nom"; break;
  case "total_du_ht" : $critere = "total_du_ht DESC, has_devis desc, c.nom"; break;
  case "ca_total_ht" : $critere = "ca_total_ht desc,has_unpaid desc, c.nom"; break;
  case "ca_total_ht_year" : $critere = "ca_total_ht_year desc,has_unpaid desc, c.nom"; break;
}

$total_dehors = 0;
$result = mysql_query("SELECT *,c.nom as nom FROM client c,type_entreprise te WHERE $where_clause ORDER BY $critere") or die(mysql_error());
while ($client = mysql_fetch_object($result)) {
  $count++;

  $grand_total_ca_ht += $client->ca_total_ht;
  $grand_total_ca_ht_year += $client->ca_total_ht_year;
  $total_dehors += $client->total_du_ht;

  $client->total_du_ht = ($client->total_du_ht==0)?"-&nbsp;&nbsp;":number_format($client->total_du_ht, 0, ',', ' ')."&euro; HT";
  $client->ca_total_ht = ($client->ca_total_ht==0)?"-&nbsp;&nbsp;":number_format($client->ca_total_ht, 0, ',', ' ')."&euro; HT";
  $client->ca_total_ht_year = ($client->ca_total_ht_year==0)?"-&nbsp;&nbsp;":number_format($client->ca_total_ht_year, 0, ',', ' ')."&euro; HT";

  print "<tr align=center class=row_".(($count%2 == 0)?"even":"odd").">\n"
       ."  <td><img src=\"/imgs/icons/".(($client->has_unpaid)?"not_paid":(($client->has_devis)?"paid_orange":"paid")).".gif\" /></td>\n"
       ."  <td><a href=fiche_prospect.php?id=".$client->id_client.">".$client->nom."</a></td>\n"
       ."  <td style=\"text-align: right;\">".$client->ca_total_ht."</td>\n"
       ."  <td style=\"text-align: right;\">".$client->ca_total_ht_year."</td>\n"
       ."  <td style=\"text-align: right;\">".$client->total_du_ht."</td>\n"
       ."</tr>\n";
}
mysql_free_result($result);

// CA total sur l'année N-2
$result = mysql_query("SELECT sum(fl.qtt*prix_ht) 
                       FROM facture_ligne fl, facture f
                       WHERE f.id_facture=fl.id_facture
                       AND f.type_doc='facture'
                       AND year(f.date_facture) = year(now()) - 2") or die(mysql_error());
list($ca_total_ht_annee_nmoisun) = mysql_fetch_array($result);
mysql_free_result($result);

// CA total sur l'année N-1
$result = mysql_query("SELECT sum(fl.qtt*prix_ht) 
                       FROM facture_ligne fl, facture f
                       WHERE f.id_facture=fl.id_facture
                       AND f.type_doc='facture'
                       AND year(f.date_facture) = year(now()) - 1") or die(mysql_error());
list($ca_total_ht_annee_precedente) = mysql_fetch_array($result);
mysql_free_result($result);

// CA Total sur année en cours
$result = mysql_query("SELECT sum(fl.qtt*prix_ht) 
                       FROM facture_ligne fl, facture f
                       WHERE f.id_facture=fl.id_facture
                       AND f.type_doc='facture'
                       AND year(f.date_facture) = year(now())") or die(mysql_error());
list($ca_total_ht_annee_encours) = mysql_fetch_array($result);
mysql_free_result($result);

?>
</table>

</td><td height="100">

<table border=0 cellspacing=0 cellpadding=3 style="border: solid 1px black; float: left; margin: 10px; width: 300px;">
<tr>
  <td><b>CA Total <?= strftime("%Y", time()); ?></b></td>
  <td><?= number_format($ca_total_ht_annee_encours, 0, ',', ' ') ?>&euro; HT / <?= number_format($ca_total_ht_annee_encours*1.196, 0, ',', ' ') ?>&euro; TTC </td>
</tr>
<tr>
  <td><b>CA Total <?= strftime("%Y", time())-1; ?></b></td>
  <td><?= number_format($ca_total_ht_annee_precedente, 0, ',', ' ') ?>&euro; HT / <?= number_format($ca_total_ht_annee_precedente*1.196, 0, ',', ' ') ?>&euro; TTC </td>
</tr>
<tr>
  <td><b>CA 12 mois flottants</b></td>
  <td><?= number_format($grand_total_ca_ht_year, 0, ',', ' ') ?>&euro; HT / <?= number_format($grand_total_ca_ht_year*1.196, 0, ',', ' ') ?>&euro; TTC </td>
</tr>
<tr>
  <td><b>En attente de paiement</b></td>
  <td><a href="facturation.php?type=unpaid"><?= number_format($total_dehors, 0, ',', ' ') ?>&euro; HT / <?= number_format($total_dehors*1.196, 0, ',', ' ') ?>&euro; TTC</a></td>
</tr>
<tr>
  <td><b>Ne montrer que </b></td><td><form action="index.php" method="get">
  <input type="hidden" name="sort" value="<?= $_GET['sort'] ?>" />
  <input type="hidden" name="namelike" value="<?= $_GET['namelike'] ?>" />
  <select style="width: 150px;" onchange="this.form.submit();" name="q"><option value="0">Tous<?php
  $result = mysql_query("SELECT te.id_type_entreprise,te.nom,count(*) as nb FROM type_entreprise te, client c WHERE te.id_type_entreprise=c.id_type_entreprise group by te.id_type_entreprise");
  while ($s = mysql_fetch_object($result)) {
    printf('<option value="%s"%s>%s (%d fiches)</option>', $s->id_type_entreprise, ($s->id_type_entreprise==$_GET['q'])?" selected":"", $s->nom, $s->nb );
  }
  ?></select></form></td>
</tr>
<tr>
  <td><b>Nom contentant</b></td>
  <td>
    <form action="index.php" method="get">
    <input type="hidden" name="sort" value="<?= $_GET['sort'] ?>" />
    <input type="hidden" name="q" value="<?= $_GET['q'] ?>" />
    <input type="text" value="<?= $_GET['namelike'] ?>" name="namelike" style="width: 150px;" class="bordered" />
    </form>
  </td>
</tr>
</table>

</td>
</tr>

<tr>
<td style="width: 278px; float: left; border: solid 1px black; margin-left: 10px; padding: 10px;">
<b>CA &euro;HT sur quatre mois</b><br/>
<center>
<a href="/showca.php"><img src="/ca_mensuel.php?width=250&height=300&nb_months=4" alt="CA trimestre"/></a>
</center>
</td>

</tr>
</table>
<?php

include("../bottom.php");

?>
