<?php
/*
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
// $Id$
?>
<?php

require("../inc/main.php");
require("../inc/barGraph.php");

must_login();

if (is_numeric($_GET['width']))
  $width = $_GET['width'];
else
  $width = 700;

if (is_numeric($_GET['height']))
  $height = $_GET['height'];
else
  $height = 300;

global $User;

$bar = new barGraph($width, $height, $User->prefs->graphgrid);
$bar->setFont($GLOBALS['_SERVER']['DOCUMENT_ROOT']."/css/themes/".$User->prefs->theme."/buttonfont.ttf");
$result = mysql_query("SELECT sum(fl.prix_ht*fl.qtt) as total, count(f.id_facture) as nb_factures,
                               date_format(f.date_facture, '%Y%m') as groupme, date_format(f.date_facture, '%m/%y') as mois
                       FROM webfinance_invoices as f, webfinance_invoice_rows as fl
                       WHERE fl.id_facture=f.id_facture
                       AND f.is_paye=0
                       AND f.type_doc = 'facture'
                       GROUP BY groupme") or wf_mysqldie();
$bar->setBarColor(255, 92, 92) ;
while ($billed = mysql_fetch_object($result)) {
  $billed->total = sprintf("%d", $billed->total);
  $bar->addValue($billed->total, $billed->mois, preg_replace("/\./", ",", sprintf("%.1f", $billed->total/1000))."K\xe2\x82\xac");
}

$bar->realise();

?>
