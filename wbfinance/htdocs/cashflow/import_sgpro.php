<?php

// $Id:

require_once("../inc/main.php");

extract($_FILES['csv']);
print "<pre>";

$compte = new stdClass();
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
$compte->compte = $a[1];
$compte->intitule = $a[2];

$l = fgets($fp); // Type de contrat ?? 
$a = fgetcsv($fp,100,';');
$compte->date_import = $a[1];
$a = fgetcsv($fp,100,';');
$compte->solde = $a[1];
print_r($compte);

$l = fgets($fp); // Empty line
$l = fgets($fp); // En-têtes de colonnes

// Now parse the real entries format is :
// DATE 
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
// quelque chose d'intéressant avec : 
//   - Pour chaque opération on va essayer de la faire rentrer dans une
//     catégorie automatiquement en appliquant les regex de celle-ci
//   - Pour chaque opération au crédit on va essayer de retrouver la facture
//     correspondante, (ou les factures) et si on trouve on les marque payées.
//   - Enfin chaque opération est stockée dans la table des opérations
//     générale.

foreach ($operations as $op) {
  if ($op->montant > 0) {
    // S'il s'agit d'un crédit, tenter de retrouver la facture correspondante
    if (preg_match("/virement/i", $op->categorie)) {
      // Virement ! Facile, le montant doit être exact !
      $result = mysql_query("SELECT f.id_facture, f.is_paye, count(*), 1.196*SUM(fl.qtt*fl.prix_ht) as total_facture
                             FROM webfinance_invoices as f,
                                  webfinance_invoice_rows as fl
                             WHERE fl.id_facture=f.id_facture
                             GROUP BY f.id_facture
                             HAVING total_facture='$op->montant'") or die(mysql_error());
      $a = mysql_fetch_array($result);
      if (($a[2] == 1) && ($a[2] == 0)) {
        // Une seule facture correspond, et elle n'est pas marquée payée, on la marque payée.
        mysql_query("UPDATE webfinance_invoices SET is_paye=1,date_paiement=DATE_PARSE('$op->date', '%d/%m/%Y') WHERE id_facture=".$a[0]);
      }
    } elseif (preg_match("/remises de cheques/i", $op->categorie))  {
      preg_match("/DE ([0-9]+) CHQ/", $op->desc, $matches);
      $nb_cheques = $matches[1];
      printf("Rechercher $nb_cheques pour un montant total de $op->montant\n");
    } else {
      // FIXME : Dodo time.
    }
  }
}



?>
