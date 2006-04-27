<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$
?>
<?php

#header("Content-type: text/plain");
require("../inc/main.php");
require("../inc/barGraph.php");

if (!is_numeric($_GET['id_client'])) {
  die("Argggl");
}

if (is_numeric($_GET['width']))
  $width = $_GET['width'];
else
  $width = 700;

if (is_numeric($_GET['height']))
  $height = $_GET['height'];
else
  $height = 300;

if (is_numeric($_GET['nb_months']))
  $nb_months = $_GET['nb_months'];
else
  $nb_months = 12;

global $User;

$bar = new barGraph($width, $height, $User->prefs->graphgrid);
$bar->setBarColor(103, 133, 195); # NBI blue
for ($i=$nb_months-1 ; $i>=0; $i--) {
  $result = mysql_query("SELECT date_format(date_sub(now(), INTERVAL $i MONTH), '%m/%y') as mois_shown, date_format(date_sub(now(), INTERVAL $i MONTH), '%Y%m') as mois");
  list($mois_shown, $mois) = mysql_fetch_array($result);
  mysql_free_result($result);

  $result = mysql_query("SELECT sum(fl.prix_ht*fl.qtt) as total, count(f.id_facture) as nb_factures,
                                 date_format(f.date_facture, '%Y%m') as groupme, date_format(f.date_facture, '%m/%y') as mois
                         FROM webfinance_invoices as f, webfinance_invoice_rows as fl
                         WHERE fl.id_facture=f.id_facture
                         AND f.type_doc = 'facture'
                         AND f.id_client=".$_GET['id_client']."
                         AND date_format(f.date_facture,'%Y%m') = '$mois' GROUP BY groupme") or wf_mysqldie();
  $billed = mysql_fetch_object($result);
  $billed->total = sprintf("%d", $billed->total);
  $bar->addValue($billed->total, $mois_shown, preg_replace("/\./", ",", sprintf("%.1f", $billed->total/1000))."K\xe2\x82\xac");
}

$bar->realise();

?>
