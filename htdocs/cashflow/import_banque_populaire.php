<?php

// $Id:

require_once("../inc/main.php");

// TEMP :

extract($_FILES['csv']);

$id_account=$_POST['id_account'];

$fp = fopen($tmp_name, "r") or die("Can't open CSV");

$l = fgets($fp); // En-têtes de colonnes

// Now parse the real entries format is :
$transactions = array();

while(!feof($fp)) {
  $tmp = fgets($fp);
  $tmp = str_replace("\r","\n" , $tmp );
  $tmp_array=explode("\n",$tmp);
  foreach($tmp_array as $data){
    $l=explode(";",$data);
    if(count($l)>5){
      $op = new stdClass();
      $op->date = $l[0];
      $op->desc = $l[1];
      $op->ref = $l[2];
      $op->date_valeur = $l[3];
      $op->montant = $l[4]; // Débit ou crédit
      $op->comment = $l[5];
      if($op){
	$op->desc = preg_replace("! +!", " ", $op->desc); // trim superfluous spaces
	array_push($transactions, $op);
      }
    }
  }
 }


// Toutes les opérations sont dans $transactions, essayons maintenant de faire
// quelque chose d'intelligen avec :
//   - Pour chaque opération on va essayer de la faire rentrer dans une
//     catégorie automatiquement en appliquant les regex de celle-ci
//   - Pour chaque opération au crédit on va essayer de retrouver la facture
//     correspondante, (ou les factures) et si on trouve on les marque payées.
//   - Enfin chaque opération est stockée dans la table des opérations
//     générale.

foreach ($transactions as $op) {
  printf("Transaction de <b>%s&euro;</b> du <b>%s</b> intitulée <i>%s</i><div style=\"font-size: 10px; border-left: solid 4px #ceceff; margin-left: 10px; padding-left: 10px;\">\n", $op->montant, $op->date, $op->desc );

  if ($op->montant > 0) {
    // S'il s'agit d'un crédit, tenter de retrouver la facture correspondante
    if (preg_match("/virement/i", $op->categorie)) {
      // Virement ! Facile, le montant doit être exact !
      $result = mysql_query("SELECT f.id_facture, f.is_paye, count(*), 1.196*SUM(fl.qtt*fl.prix_ht) as total_facture
                             FROM webfinance_invoices as f,
                                  webfinance_invoice_rows as fl
                             WHERE fl.id_facture=f.id_facture
                             GROUP BY f.id_facture
                             HAVING total_facture='$op->montant'")
	or die(mysql_error());
      $a = mysql_fetch_array($result);
      if (($a[2] == 1) && ($a[2] == 0)) {
        print "<b style=\"color: green;\">La facture correspondante à ce virement à été trouvée, elle est marquée « payée »</b><br/>";
        // Une seule facture correspond, et elle n'est pas marquée payée, on la marque payée.
        mysql_query("UPDATE webfinance_invoices SET is_paye=1,date_paiement=STR_TO_DATE('$op->date', '%d/%m/%Y') WHERE id_facture=".$a[0]);
      } else {
        print "<b style=\"color: red;\">Impossible de trouver la facture correspondante à ce virement ! Incohérence dans les factures ou paiement erroné !</b><br/>";
      }
    } elseif (preg_match("/remises de cheques/i", $op->categorie))  {
      preg_match("/DE ([0-9]+) CHQ/", $op->desc, $matches);
      $nb_cheques = $matches[1];
      printf("Rechercher $nb_cheques factures totalisant $op->montant&euro;<br/>\n");
      // FIXME
    } else {
      // FIXME : Dodo time.
    }
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
                         GROUP BY id") or die(mysql_error());
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
  $q = sprintf("INSERT INTO webfinance_transactions (text,id_account,amount,type,date, id_category, comment)
                VALUES('%s', %d, '%s', 'real', STR_TO_DATE('%s', '%%d/%%m/%%Y'), %d, '%s')",
	       $op->desc, $id_account, preg_replace("/,/", ".", preg_replace("/ +/", "", $op->montant)), $op->date, $id_categorie ,"ref: ".$op->ref." ".$op->comment );
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

print count($transactions)." opérations trouvées dans le fichier";

?>
