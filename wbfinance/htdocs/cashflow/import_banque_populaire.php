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
// $Id$

require_once("../inc/main.php");

extract($_FILES['csv']);

$id_account=$_POST['id_account'];

$fp = fopen($tmp_name, "r") or die("Can't open CSV");

$l = fgets($fp); // En-têtes de colonnes

// Now parse the real entries format is :
$transactions = array();
$invalid_trs = array();

while(!feof($fp)) {
  $tmp = fgets($fp);
  $tmp = str_replace("\r","\n" , $tmp );
  $tmp_array=explode("\n",$tmp);
  foreach($tmp_array as $data){
    $l=explode(";",$data);

    if(isset($l[4]))
      $l[4] = preg_replace("/,/", ".", preg_replace("/ +/", "", $l[4]));

    if(count($l)==6 AND is_numeric($l[4])){
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
    }else if(!empty($data))
      $invalid_trs[]=$data;
  }
 }
fclose($fp);

if(count($invalid_trs)>0){
  printf('<b style="color: red;">%s:</b><br/>',_('Invalid CSV file format'));
  foreach($invalid_trs as $data)
    printf("%s<br/>",$data);

 }else{

    // Toutes les opérations sont dans $transactions, essayons maintenant de faire
    // quelque chose d'intelligent avec :
    //   - Pour chaque opération on va essayer de la faire rentrer dans une
    //     catégorie automatiquement en appliquant les regex de celle-ci
    //   - Pour chaque opération au crédit on va essayer de retrouver la facture
    //     correspondante, (ou les factures) et si on trouve on les marque payées.
    //   - Enfin chaque opération est stockée dans la table des opérations
    //     générale.

    $indic= false;
    print "<form action='save_transaction.php' method='post'>";
    print "<input type='hidden' name='action' value='update_invoices'>";

    //recherche de la cat�gorie unknow
    $id_default_category = 1;
    $result = mysql_query("SELECT COUNT(*) , id FROM webfinance_categories WHERE name RLIKE 'unknown' GROUP BY id") or wf_mysqldie();
    list($nb_matches,$id, $name) = mysql_fetch_array($result);
    if($nb_matches==1)
      $id_default_category = $id;

    foreach ($transactions as $op) {
      printf("Transaction de <b>%s&euro;</b> du <b>%s</b> intitulée <i>%s</i><div style=\"font-size: 10px; border-left: solid 4px #ceceff; margin-left: 10px; padding-left: 10px;\">\n",
	     $op->montant, $op->date, $op->desc );

      // Dans tous les cas on essaie de retrouver la catégorie de la transaction
      // automagiquement.

      //default id category

      $id_categorie = $id_default_category;
      $result = mysql_query("SELECT COUNT(*) , id , name FROM webfinance_categories WHERE re IS NOT NULL AND '".addslashes($op->desc)."' RLIKE re GROUP BY id")
	or wf_mysqldie();
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
      $q = sprintf("INSERT INTO webfinance_transactions (text,id_account,amount,type,date, id_category, comment) ".
		   "VALUES('%s', %d, '%s', 'real', STR_TO_DATE('%s', '%%d/%%m/%%Y'), %d, '%s')",
		   $op->desc, $id_account, $op->montant , $op->date, $id_categorie ,"ref: ".$op->ref." ".$op->comment );
      mysql_query($q) or $erreur=1;
      $op->id = mysql_insert_id();

      if ($erreur) {
	$errstr = mysql_error();
	if (preg_match("/Duplicate entry.*for key/", $errstr)) {
	  print '<b style="color: red;">Cette transaction à déjà été importée !!</b><br/>';
	} else {
	  print '<b style="color: red;">Erreur lors de l\'insertion : '.mysql_error().'</b><br/>';
	}
      } else {
	print '<b style="color: green;">Import réussi</b><br/>';
      }

      if ($op->montant > 0) {
	if(compare_invoices_transaction($op))
	  $indic=true;
      } else {
	// S'il s'agit d'un débit, le lier à un fournisseur ? à un bon de commande ?
      }

      print "</div>";
    }

    print count($transactions)." "._('transactions found in the file').".<br/>";

    if($indic){
      $help =  _("Webfinance a d&eacute;tect&eacute; des factures qui peuvent avoir des relations avec les transaction import&eacute;es.<br/>".
		 "Ici, vous pouvez mettre &agrave; jour les factures et les transactions en les cochant puis en validant dans le bouton ci-dessous.");
      echo "<br/>".$help."<br/>";
      printf("<input type='submit' value='%s'>",_("Update"));
    }
    print "</form><br/>";

 }

unlink($tmp_name);

?>
