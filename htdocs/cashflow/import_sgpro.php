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

// $Id:

require_once("../inc/main.php");

// TEMP :

extract($_FILES['csv']);

$compte = new stdClass();
$compte->banque = "Société Générale";
$compte->code_banque = "30003";
$fp = fopen($tmp_name, "r") or die("Can't open CSV");

// Les 6 premières lignes de l'export CSV sont :
// SG %%DOMICILIATION%%
// 30003  %%CODE_GUICHET%%  %%NUM_COMPTE%%;%%INTITULE%%
// %%GARBAGE%%
// Solde au;06/04/2006
// Solde;XX XXX,XX;EUR

$l = fgets($fp);
preg_match("/^SG (.*)/", $l, $a);
$compte->domiciliation = $a[1];

$l = fgets($fp);
preg_match("/^30003 *([0-9]+)  ([0-9]+);(.*)$/", $l, $a);
$compte->code_guichet = $a[1];
$compte->compte = $a[2];
$compte->intitule = $a[3];

$l = fgets($fp); // Type de contrat ??
$a = fgetcsv($fp,100,';');
$compte->date_import = $a[1];
$a = fgetcsv($fp,100,';');
$compte->solde = $a[1];

foreach ($compte as $n=>$v) {
  $compte->$n = preg_replace("/ +$/", "", $v); // Trim spaces
}

// Ok on a toutes les informations sur le compte. On voit dans les préférences
// s'il existe. Sinon on l'y ajoute !

$result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='rib'");
$id_account = 0;
while (list($id, $value) = mysql_fetch_array($result)) {
  $thisaccount = unserialize(base64_decode($value));

  if (
      (strtoupper($thisaccount->domiciliation) == strtoupper($compte->domiciliation)) &&
      (strtoupper($thisaccount->code_guichet) == strtoupper($compte->code_guichet)) &&
      (strtoupper($thisaccount->compte) == strtoupper($compte->compte))
     ) {
    printf("<b>Cet import correspond au compte %s n°%s</b><br/>\n", $thisaccount->banque, $thisaccount->compte);
    $id_account = $id;
  }
}
mysql_free_result($result);
if ($id_account == 0) { // Compte innexistant dans webfinancne, on le crée
  printf("Le compte %s n°%s n'existe pas dans les préférences webfinance... Création<br/>",
         $compte->banque, $compte->numero );

  $q = sprintf("INSERT INTO webfinance_pref (type_pref,value) VALUES('rib', '%s')",
               base64_encode(serialize($compte)) );
  mysql_query($q) or wf_mysqldie();

  $result = mysql_query("SELECT id_pref FROM webfinance_pref WHERE owner=-1 AND type_pref='rib' AND date_modified>=DATE_SUB(NOW(), INTERVAL 2 SECOND)");
  list($id_account) = mysql_fetch_array($result);
}

$l = fgets($fp); // Empty line
$l = fgets($fp); // En-têtes de colonnes

// Now parse the real entries format is :
$operations = array();
while ($l = fgetcsv($fp,1000,';')) {
  if ($l[0] != "") {
    // This is a new line push the current in list and treat it
    if ($op) {
      $op->desc = preg_replace("! +!", " ", $op->desc); // trim superfluous spaces
      array_push($operations, $op);
    }

    $op = new stdClass();
    $op->date = $l[0];
    $op->desc = $l[1];
    $op->montant = ($l[2]!="")?$l[2]:$l[3]; // Débit ou crédit
    $op->date_valeur = $l[5];
    $op->categorie = $l[6];

  } else {
    // This is the continuation of the current line
    $op->desc .= $l[1];
  }
}

// Toutes les opérations sont dans $operations, essayons maintenant de faire
// quelque chose d'intelligen avec :
//   - Pour chaque opération on va essayer de la faire rentrer dans une
//     catégorie automatiquement en appliquant les regex de celle-ci
//   - Pour chaque opération au crédit on va essayer de retrouver la facture
//     correspondante, (ou les factures) et si on trouve on les marque payées.
//   - Enfin chaque opération est stockée dans la table des opérations
//     générale.

foreach ($operations as $op) {
  printf("Transaction de <b>%s&euro;</b> du <b>%s</b> intitulée <i>%s</i><div style=\"font-size: 10px; border-left: solid 4px #ceceff; margin-left: 10px; padding-left: 10px;\">\n", $op->montant, $op->date, $op->desc );

  if ($op->montant > 0) {
    //fonction de recherche de factures correspondantes
    compare_invoices_transaction($op);

  } else {
    // S'il s'agit d'un débit, le lier à un fournisseur ? à un bon de commande ?
  }

  // Dans tous les cas on essaie de retrouver la catégorie de la transaction
  // automagiquement.
  $id_categorie = 1;
  $result = mysql_query("SELECT COUNT(*),id,name
                         FROM webfinance_categories
                         WHERE re IS NOT NULL
                         AND '".addslashes($op->desc)."' RLIKE re
                         GROUP BY id") or wf_mysqldie();
  list($nb_matches,$id, $name) = mysql_fetch_array($result);
  switch ($nb_matches) {
    case 0 : print "<b style=\"color: orange;\">Aucune catégorie ne correspond, à vous de classer cette transaction</b><br/>";
             break;
    case 1 : print "<b style=\"color: green;\">Correspondance avec la catégorie &laquo;&nbsp;$name&nbsp;&raquo;</b><br/>";
             $id_categorie = $id;
             break;
    default : print "<b style=\"color: orange;\">Plus d'une catégorie correspond, classement automatique impossible</b><br/>";

  }

  // Insertion de la transaction
  $erreur = 0;
  $q = sprintf("INSERT INTO webfinance_transactions (text,id_account,amount,type,date, id_category)
                VALUES('%s', %d, '%s', 'real', STR_TO_DATE('%s', '%%d/%%m/%%Y'), %d)",
                $op->desc, $id_account, preg_replace("/,/", ".", preg_replace("/ +/", "", $op->montant)), $op->date, $id_categorie );
  mysql_query($q) or $erreur=1;
  if ($erreur) {
    $errstr = mysql_error();
    if (preg_match("/Duplicate entry.*for key/", $errstr)) {
      print '<b style="color: red;">Cette transaction à déjà été importée !!</b>';
    } else {
      print '<b style="color: red;">Erreur lors de l\'insertion : '.mysql_error().'</b>';
    }
  } else {
    print '<b style="color: green;">Import réussi</b>';
  }

  print "</div>";
}

print count($operations)." opérations trouvées dans le fichier";

?>
