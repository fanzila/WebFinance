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

require("../inc/main.php");
$roles = 'manager,admin';
require("../top.php");
require("nav.php");

function compare_invoices_transaction($op){
  $indic=false;
  $amount=str_replace(',','.',$op->montant);
  $min = ($amount/1.196)-0.1;
  $max = ($amount/1.196)+0.1;
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
    "1.196*SUM(qtt * prix_ht) as total_facture ".
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
    "BETWEEN %s AND %s ".
    "GROUP BY id_facture";

  $query = sprintf($q,$min, $max);

  $result =  mysql_query($query) or wf_mysqldie();

  if(mysql_num_rows($result)<1){
    print "<b style=\"color: red;\">Impossible de trouver la facture correspondante à ce virement ! Incohérence dans les factures ou paiement erroné !</b><br/>";
  }else{
    while($invoice = mysql_fetch_assoc($result)){
      //print_r($invoice);
      if ( $is_paye < 1 ) {
	print "<b style=\"color: green;\">La facture correspondante à ce virement a été trouvée, elle est marquée « payée »</b><br/>";
	printf("<input type='hidden' name='date_tr[%d]' value='%s'>",$invoice['id_facture'],$op->date);
	printf("<input type='checkbox' name='invoices[]'  value='%d' >",$invoice['id_facture']);
	printf("#%s : %s : %s&euro; : %s <br/>", $invoice['num_facture'],$invoice['ref_contrat'],round($invoice['total_facture'],3), strftime($invoice['date_facture']) ) ;
	$indic=true;
      }
    }
  }
  return $indic;
}

if (preg_match("!\.!", $_POST['filtre'])) { die("Wrong filter"); } // file traversal

print "<h1>Import de données bancaires</h1>";

if (!file_exists($_FILES['csv']['tmp_name'])) {
  die("Pas de fichier reçu");
}

if (!file_exists($_POST['format'])) {
  die("Import impossible !! Veuillez choisir le type de fichier dans le <a href=\"import.php\">formulaire d'import</a>");
}

// Do import

extract($_FILES['csv']);

print "Fichier envoyé : $name<br/>";
print "Type mime : $type<br/><br/>";

print "<h2>Analyse des lignes :</h2>";

require($_POST['format']);

?>
