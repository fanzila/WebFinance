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
require("../top.php");
require("nav.php");

function compare_invoices_transaction($op){

  // S'il s'agit d'un crédit, tenter de retrouver la facture correspondante
  $result = mysql_query("SELECT f.id_facture, f.is_paye, count(*), 1.196*SUM(fl.qtt*fl.prix_ht) as total_facture
                           FROM webfinance_invoices as f,
                                webfinance_invoice_rows as fl
                           WHERE fl.id_facture=f.id_facture
                           GROUP BY f.id_facture
                           HAVING total_facture='$op->montant'")
    or wf_mysqldie()
    $a = mysql_fetch_array($result);
    if (($a[2] == 1) && ($a[2] == 0)) {
      print "<b style=\"color: green;\">La facture correspondante à ce virement à été trouvée, elle est marquée « payée »</b><br/>";
      // Une seule facture correspond, et elle n'est pas marquée payée, on la marque payée.
      mysql_query("UPDATE webfinance_invoices SET is_paye=1,date_paiement=STR_TO_DATE('$op->date', '%d/%m/%Y') WHERE id_facture=".$a[0]);
    } else {
      print "<b style=\"color: red;\">Impossible de trouver la facture correspondante à ce virement ! Incohérence dans les factures ou paiement erroné !</b><br/>";
    }

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
