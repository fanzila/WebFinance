<?php
//
// This file is part of « Backoffice NBI »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<?php
// $Id$

include("../inc/backoffice.php");
include("../top.php");
include("nav.php");

$result = mysql_query("SELECT value FROM webcash_pref WHERE type_pref='societe' AND owner=-1");
list($value) = mysql_fetch_array($result);
mysql_free_result($result);

$societe = unserialize(base64_decode($value));
?>

<h1>Ma société</h1>

<form id="main_form" action="save_societe.php" method="post" enctype="multipart/form-data">
<table class="bordered" border="0" cellspacing="5" cellpadding="0">
<tr>
  <td>Raison sociale</td>
  <td><input type="text" name="raison_sociale" value="<?= $societe->raison_sociale ?>" /></td>
</tr>
<tr>
  <td>TVA intracommunautaire</td>
  <td><input type="text" name="tva_intracommunautaire" value="<?= $societe->tva_intracommunautaire ?>" /></td>
</tr>
<tr>
  <td>Siren</td>
  <td><input type="text" name="siren" value="<?= $societe->siren ?>" /></td>
</tr>
<tr>
  <td valign="top" rowspan="3">Adresse</td>
  <td><input type="text" name="addr1" value="<?= $societe->addr1 ?>" /></td>
</tr>
<tr>
  <td><input type="text" name="addr2" value="<?= $societe->addr2 ?>" /></td>
</tr>
<tr>
  <td><input type="text" name="addr3" value="<?= $societe->addr3 ?>" /></td>
</tr>
<tr>
  <td>Date de création</td>
  <td><input type="text" name="date_creation" value="<?= $societe->date_creation ?>" />
</tr>
</table>

<h1>Logo</h1>

<?php

$result = mysql_query("SELECT * FROM webcash_pref WHERE owner=-1 AND type_pref='logo'");
$logo = mysql_fetch_object($result);
mysql_free_result($result);

if ($logo->id_pref > 0) {
  printf('Logo actuel : <br/><img src="data:image/png;base64,%s" /><br/>', $logo->value);
}

?>
Changer le logo <input type="file" name="logo" /> <b>(ONLY PNG)</b>

<h1>Compte(s) banquaire(s)</h1>
<table style="text-align: center;" class="framed" cellspacing="0" cellpadding="4">
<tr class="row_header">
  <td>Banque</td>
  <td>Domiciliation</td>
  <td>Code banque</td>
  <td>Code guichet</td>
  <td>N° compte</td>
  <td>Clef</td>
  <td>IBAN</td>
  <td>SWIFT/BIC</td>
</tr>
<?php
$result = mysql_query("SELECT id_pref,value FROM webcash_pref WHERE type_pref='rib' AND owner=-1");
$count = 1;
while (list($id_pref,$value) = mysql_fetch_array($result)) {
  $compte = unserialize(base64_decode($value));
  print <<<EOF
<tr>
  <td><input style="width: 100px; text-align: center;" type="text" name="banque_$count" value="$compte->banque" /></td>
  <td><input style="width: 100px; text-align: center;" type="text" name="domiciliation_$count" value="$compte->domiciliation" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="code_banque_$count" value="$compte->code_banque" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="code_guichet_$count" value="$compte->code_guichet" /></td>
  <td><input style="width: 70px; text-align: center;" type="text" name="compte_$count" value="$compte->compte" /></td>
  <td><input style="width: 20px; text-align: center;" type="text" name="clef_$count" value="$compte->clef" /></td>
  <td><input style="width: 80px; text-align: center;" type="text" name="iban_$count" value="$compte->iban" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="swift_$count" value="$compte->swift" /></td>
</tr>
EOF;
  $count++;
}
?>
<tr style="background: #ceffce;">
  <td><input style="width: 100px; text-align: center;" type="text" name="banque_new" value="" /></td>
  <td><input style="width: 100px; text-align: center;" type="text" name="domiciliation_new" value="" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="code_banque_new" value="" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="code_guichet_new" value="" /></td>
  <td><input style="width: 70px; text-align: center;" type="text" name="compte_new" value="" /></td>
  <td><input style="width: 20px; text-align: center;" type="text" name="clef_new" value="" /></td>
  <td><input style="width: 80px; text-align: center;" type="text" name="iban_new" value="" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="swift_new" value="" /></td>
</tr>
</table>
<i>Nota : pour supprimer un compte banquaire, positionner le numéro de compte à vide</i><br/>
<input type="submit" value="Enregistrer" />
</form>

<?php

include("../bottom.php");

?>
