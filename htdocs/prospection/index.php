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
# $Id: index.php 551 2007-08-02 05:16:27Z gassla $

require("../inc/main.php");
$title = _("Companies");
$roles="manager,employee,accounting";
include("../top.php");
include("nav.php");

global $User;
$User->getInfos();

//msg
if(isset($_SESSION['message']) and !empty($_SESSION['message'])){
  echo $_SESSION['message'];
  $_SESSION['message']='';
 }

// House keeping : lister les factures inpayées et marquer les clients qui en ont.
// FIXME : should go in save_facture.php
mysql_query("UPDATE webfinance_clients SET has_unpaid=false,has_devis=false") or wf_mysqldie();
$result = mysql_query("select c.id_client,count(*) as has_unpaid
                       FROM webfinance_invoices as f,webfinance_clients as c
                       WHERE f.is_paye=0
                       AND f.type_doc='facture'
                       AND f.date_facture<=now()
                       AND f.id_client=c.id_client
                       group by c.id_client") or wf_mysqldie();
while (list($id_client) = mysql_fetch_array($result)) {
  mysql_query("UPDATE webfinance_clients SET has_unpaid=true WHERE id_client=$id_client");
}
$result = mysql_query("SELECT c.id_client,count(*) as has_unpaid from webfinance_invoices as f,webfinance_clients as c WHERE f.is_paye=0 AND f.type_doc='devis' AND f.id_client=c.id_client group by c.id_client");
while (list($id_client) = mysql_fetch_array($result)) {
  mysql_query("UPDATE webfinance_clients SET has_devis=true WHERE id_client=$id_client");
}

// Begin where clause
$where_clause = "1";
if (isset($_GET['q']) && ($_GET['q']!=0)) {
  $where_clause = "ct.id_company_type=".$_GET['q'];
}

if ( isset($_GET['namelike']) and preg_match("/[a-zA-Z ]+/", $_GET['namelike']) ) {
  $where_clause .= " AND c.nom LIKE '%".  mysql_real_escape_string($_GET['namelike'])."%'";
}
$where_clause .= " AND c.id_company_type=ct.id_company_type ";

$GLOBALS['_SERVER']['QUERY_STRING'] = preg_replace("/sort=\w+\\&*+/", "", $GLOBALS['_SERVER']['QUERY_STRING']);

$critere = "has_unpaid desc,has_devis desc, c.nom";
if( !isset($_GET['sort']) ) {
  $_GET['sort'] = 'nom';
  if( isset($User->prefs->tri_entreprise) )
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
// Find matching companies
$result = mysql_query("SELECT c.nom, c.id_client,ct.id_company_type
                       FROM webfinance_clients c,webfinance_company_types ct
                       WHERE $where_clause
                       ORDER BY $critere") or wf_mysqldie();

// Redirect to "fiche_prospect" if the user is searching a company and there is
// only one result
if(isset($_GET['namelike']) and mysql_num_rows($result) == 1) {
  $row = mysql_fetch_assoc($result);
  header("Location: fiche_prospect.php?onglet=biling&id=$row[id_client]");
  exit;
}

?>
<table border="0" cellspacing="0" cellpadding="0">
<tr valign="top"><td rowspan="2">

<table border="0" width="500" cellspacing=0 cellpadding=3 class="framed">
<tr class="row_header" style="text-align: center;">
  <td><a href="?sort=du&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>">&euro;</a></td>
  <td width="200"><a href="?sort=nom&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Company name') ?></a></td>
  <td><a href="?sort=ca_total_ht&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Total Income') ?></a></td>
  <td><a href="?sort=ca_total_ht_year&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Year Income') ?></a></td>
  <td><a href="?sort=total_du_ht&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Owed') ?></a></td>
</tr>
<?php

$client = new Client(1);
$grand_total_ca_ht=0;
$grand_total_ca_ht_year=0;
$count=0;
while ($found = mysql_fetch_object($result)) {
  $count++;

  $client->setId($found->id_client); // Populate $client with calculated values from Client object

  $grand_total_ca_ht += $client->ca_total_ht;
  $grand_total_ca_ht_year += $client->ca_total_ht_year;
  $total_dehors += $client->total_du_ht;

  $client->total_du_ht = ($client->total_du_ht==0)?"-&nbsp;&nbsp;":number_format($client->total_du_ht, 0, ',', ' ')."&euro; HT";
  $client->ca_total_ht = ($client->ca_total_ht==0)?"-&nbsp;&nbsp;":number_format($client->ca_total_ht, 0, ',', ' ')."&euro; HT";
  $client->ca_total_ht_year = ($client->ca_total_ht_year==0)?"-&nbsp;&nbsp;":number_format($client->ca_total_ht_year, 0, ',', ' ')."&euro; HT";

  print "<tr align=center class=row_".(($count%2 == 0)?"even":"odd").">\n"
    ."  <td><a href=fiche_prospect.php?onglet=biling&id=".$client->id_client."&onglet=facturation>"
    ."    <img src=\"/imgs/icons/".(($client->has_unpaid)?"not_paid":(($client->has_devis)?"paid_orange":"paid")).".gif\" /></a>\n"
    ."    <a href=edit_facture.php?id_facture=new&id_client=".$client->id_client."><img src=\"/imgs/icons/add.png\"></a></td>\n"
    ."  <td><a href=fiche_prospect.php?onglet=biling&id=".$client->id_client.">".$client->nom."</a>".
    "</td>\n"
    ."  <td style=\"text-align: right;\">".$client->ca_total_ht."</td>\n"
    ."  <td style=\"text-align: right;\">".$client->ca_total_ht_year."</td>\n"
    ."  <td style=\"text-align: right;\">".$client->total_du_ht."</td>\n"
    ."</tr>\n";
}
mysql_free_result($result);

// CA total sur l'année N-2
$result = mysql_query("SELECT sum(fl.qtt*prix_ht), sum((1+tax/100)*prix_ht*fl.qtt)
                       FROM webfinance_invoice_rows fl, webfinance_invoices f
                       WHERE f.id_facture=fl.id_facture
                       AND f.type_doc='facture'
                       AND year(f.date_facture) = year(now()) - 2") or wf_mysqldie();
list($ca_total_ht_annee_nmoisun,$ca_total_ttc_annee_nmoisun) = mysql_fetch_array($result);
mysql_free_result($result);

// CA total sur l'année N-1
$result = mysql_query("SELECT sum(fl.qtt*prix_ht), sum((1+tax/100)*prix_ht*fl.qtt)
                       FROM webfinance_invoice_rows fl, webfinance_invoices f
                       WHERE f.id_facture=fl.id_facture
                       AND f.type_doc='facture'
                       AND year(f.date_facture) = year(now()) - 1") or wf_mysqldie();
list($ca_total_ht_annee_precedente, $ca_total_ttc_annee_precedente) = mysql_fetch_array($result);
mysql_free_result($result);

// CA Total sur année en cours
$result = mysql_query("SELECT sum(fl.qtt*prix_ht), sum((1+tax/100)*prix_ht*fl.qtt)
                       FROM webfinance_invoice_rows fl, webfinance_invoices f
                       WHERE f.id_facture=fl.id_facture
                       AND f.type_doc='facture'
                       AND year(f.date_facture) = year(now())") or wf_mysqldie();
list($ca_total_ht_annee_encours,$ca_total_ttc_annee_encours) = mysql_fetch_array($result);
mysql_free_result($result);

// Trésorerie : total des transactions effectives
$result = mysql_query("SELECT sum(amount) FROM webfinance_transactions WHERE type='real'") or wf_mysqldie();
list($tresorerie_real) = mysql_fetch_array($result);
mysql_free_result($result);

// Même chose en prévisionnel
$result = mysql_query("SELECT sum(amount) FROM webfinance_transactions") or wf_mysqldie();
list($tresorerie_prev) = mysql_fetch_array($result);
mysql_free_result($result);

?>
</table>

</td><td style="vertical-align: top" height="100">

<table border=0 cellspacing=0 cellpadding=3 class="bordered" style="margin-left: 10px; width: 300px; background: white; color: black;">
<tr>
  <td><b><?= strftime(_("Total income %Y"), time()); ?></b></td>
  <td><?= number_format($ca_total_ht_annee_encours, 0, ',', ' ') ?>&euro; HT / <?= number_format($ca_total_ttc_annee_encours, 0, ',', ' ') ?>&euro; TTC </td>
</tr>
<tr>
  <td><b><?= strftime(_("Total income %Y"), time()-365*86400); ?></b></td>
  <td><?= number_format($ca_total_ht_annee_precedente, 0, ',', ' ') ?>&euro; HT / <?= number_format($ca_total_ttc_annee_precedente, 0, ',', ' ') ?>&euro; TTC </td>
</tr>
<tr>
  <td><b><?= _("Income 12 months") ?></b></td>
  <td><?= number_format($grand_total_ca_ht_year, 0, ',', ' ') ?>&euro; HT / <?= number_format($grand_total_ca_ht_year*1.196, 0, ',', ' ') ?>&euro; TTC </td>
</tr>
<tr>
  <td><b><?= _("Billed and unpaid") ?></b></td>
  <td><a href="facturation.php?type=unpaid"><?= number_format($total_dehors, 0, ',', ' ') ?>&euro; HT / <?= number_format($total_dehors*1.196, 0, ',', ' ') ?>&euro; TTC</a></td>
</tr>
<?php
if ($User->isAuthorized('manager,accounting')) {
?>
<tr>
  <td><b><?= _('Cash (real)') ?></b></td>
  <td><a href="/cashflow/"><?= number_format($tresorerie_real, 0, ',', ' ') ?>&euro;</a></td>
</tr>
<tr>
  <td><b><?= _('Cash (forecast)') ?></b></td>
  <td><a href="/cashflow/"><?= number_format($tresorerie_prev, 0, ',', ' ') ?>&euro;</a></td>
</tr>
<?php
}
?>
<tr>
  <td><b><?= _("Only show") ?></b></td><td><form action="index.php" method="get">
  <input type="hidden" name="sort" value="<?= $_GET['sort'] ?>" />
  <input type="hidden" name="namelike" value="<?=(isset($_GET['namelike'])?$_GET['namelike']:'')?>" />
  <select style="width: 150px;" onchange="this.form.submit();" name="q"><option value="0">Tous<?php
  $result = mysql_query("SELECT webfinance_company_types.id_company_type,webfinance_company_types.nom,count(distinct webfinance_clients.id_client) as nb
                         FROM webfinance_company_types
                         LEFT JOIN webfinance_clients ON webfinance_clients.id_company_type=webfinance_company_types.id_company_type
                         GROUP by webfinance_company_types.id_company_type");
  while ($s = mysql_fetch_object($result)) {
    printf('<option value="%s"%s>%s (%d fiches)</option>', $s->id_company_type, ($s->id_company_type==$_GET['q'])?" selected":"", $s->nom, $s->nb );
  }
  ?></select></form></td>
</tr>
<tr>
  <td><b><?= _("Name contains") ?></b></td>
  <td>
    <form action="index.php" method="get">
    <input type="hidden" name="sort" value="<?= $_GET['sort'] ?>" />
    <input type="hidden" name="q" value="<?= $_GET['q'] ?>" />
    <input type="text" value="<?=(isset($_GET['namelike'])?$_GET['namelike']:'')?>" name="namelike" style="width: 150px;" class="bordered" />
    </form>
  </td>
</tr>
</table>

</td>
</tr>

<tr style="vertical-align: top">
<td style="padding: 10px;">
<div class="bordered" style="width: 300px;">
<b>CA &euro;HT sur quatre mois</b><br/>
<center>
<a href="/showca.php"><img src="/graphs/ca_mensuel.php?width=250&height=300&nb_months=4" alt="CA sur 4 mois"/></a>
</center>
</div>
</td>

</tr>
</table>
<?php

$Revision = '$Revision: 551 $';
include("../bottom.php");

?>
