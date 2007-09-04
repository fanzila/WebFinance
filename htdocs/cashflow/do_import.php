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
// $Id: do_import.php 531 2007-06-13 12:32:31Z thierry $

require("../inc/main.php");
$roles = 'manager,admin';
require("../top.php");
require("nav.php");


function compare_invoices_transaction($op){
  $indic=false;
  $amount=str_replace(',','.',$op->montant);

  $tva = getTVA();
  $f = 1 + ($tva/100) ;

  $min = ( $amount/$f )-0.1;
  $max = ( $amount/$f )+0.1;

  // S'il s'agit d'un crédit, tenter de retrouver la facture correspondante

  //    $q = "SELECT id_facture, is_paye, date_facture, num_facture, ref_contrat, total_facture_ht, 1.196*total_facture_ht as total_facture FROM wf_view_invoices ".
  //  "WHERE total_facture_ht>=%s AND total_facture_ht<=%s ";

  $q = "SELECT ".
    "wf_in.id_facture, ".
    "is_paye, ".
    "date_facture, ".
    "num_facture, ".
    "ref_contrat, ".
    "SUM(qtt * prix_ht) as total_facture_ht, ".
    "$f*SUM(qtt * prix_ht) as total_facture ".
    "FROM webfinance_invoices wf_in , webfinance_invoice_rows wf_in_rows ".
    "WHERE wf_in_rows.id_facture=wf_in.id_facture ".
    "AND ".
    "( ".
    "SELECT SUM( qtt * prix_ht ) as total_facture_ht ".
    "FROM webfinance_invoice_rows, webfinance_invoices ".
    "WHERE webfinance_invoice_rows.id_facture = webfinance_invoices.id_facture ".
    "AND webfinance_invoice_rows.id_facture=wf_in.id_facture ".
    "GROUP BY webfinance_invoice_rows.id_facture ".
    ") ".
    "BETWEEN '%s' AND '%s' ".
    "GROUP BY id_facture";

  $query = sprintf($q,$min, $max);

  $result =  mysql_query($query) or wf_mysqldie();

  if(mysql_num_rows($result)<1){
    print "<b style=\"color: red;\">Impossible de trouver la facture correspondante à ce virement ! Incohérence dans les factures ou paiement erroné !</b><br/>";
  }else{
    while($invoice = mysql_fetch_assoc($result)){
      //print_r($invoice);
      if ( $invoice['is_paye'] < 1 ) {
	printf("<b style=\"color: green;\">%s</b><br/>",_('The related invoice is found, it\'s unpaid!'));
	printf("<input type='hidden' name='date_tr[%d]' value='%s'>",$invoice['id_facture'],$op->date);
	printf("<input type='hidden' name='id_tr[%d]' value='%s'>",$invoice['id_facture'],$op->id);
	printf("<input type='checkbox' name='invoices[]'  value='%d' >",$invoice['id_facture']);
	$indic=true;
      }else{
	printf("<b style=\"color: green;\">%s</b><br/>",_('The related invoice is found, it\'s paid!'));
      }
      printf("<a href='../prospection/edit_facture.php?id_facture=%d' target='_blank' ><span style='background-color: rgb(255, 102, 102);'>#%s : %s : %s&euro; : %s </span></a><br/>",
	     $invoice['id_facture'],$invoice['num_facture'],$invoice['ref_contrat'],round($invoice['total_facture'],3), strftime($invoice['date_facture']) ) ;
    }
  }
  return $indic;
}

if (preg_match("!\.!", $_POST['filtre'])) { die("Wrong filter"); } // file traversal

print "<h1>Import de données bancaires</h1>";

if (!file_exists($_FILES['csv']['tmp_name'])) {
  die(_('File not uploaded!'));
}

if (!file_exists($_POST['format'])) {
  die("Import impossible !! Veuillez choisir le type de fichier dans le <a href=\"import.php\">formulaire d'import</a>");
}

// Do import

extract($_FILES['csv']);

print _('File sent')." : $name<br/>";
print "Type mime : $type<br/><br/>";

print "<h2>Analyse des lignes :</h2>";


require($_POST['format']);

$Revision = '$Revision: 531 $';
require("../bottom.php");

?>
