<?php
//
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php
// $Id$

include("../inc/main.php");
$roles = 'admin,manager';
include("../top.php");
include("nav.php");

$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1");
list($value) = mysql_fetch_array($result);
mysql_free_result($result);

$societe = unserialize(base64_decode($value));
?>

<h1><?=_('My Company')?></h1>

<form id="main_form" action="save_societe.php" method="post" enctype="multipart/form-data">
<table class="bordered" border="0" cellspacing="5" cellpadding="0">
<tr>
  <td>Raison sociale</td>
  <td><input style="width: 200px;" type="text" name="raison_sociale" value="<?= $societe->raison_sociale ?>" /></td>
</tr>
<tr>
  <td>TVA intracommunautaire</td>
  <td><input style="width: 200px;" type="text" name="tva_intracommunautaire" value="<?= $societe->tva_intracommunautaire ?>" /></td>
</tr>
<tr>
  <td>Siren</td>
  <td><input style="width: 200px;" type="text" name="siren" value="<?= $societe->siren ?>" /></td>
</tr>
<tr>
  <td valign="top" rowspan="3">Adresse</td>
  <td><input style="width: 200px;" type="text" name="addr1" value="<?= $societe->addr1 ?>" /></td>
</tr>
<tr>
  <td><input style="width: 200px;" type="text" name="addr2" value="<?= $societe->addr2 ?>" /></td>
</tr>
<tr>
  <td><input style="width: 200px;" type="text" name="addr3" value="<?= $societe->addr3 ?>" /></td>
</tr>
<tr>
  <td><?= _('WF url') ?></td>
  <td><input style="width: 200px;" type="text" name="wf_url" value="<?= $societe->wf_url ?>" /></td>
</tr>
<tr>
  <td><?= _('Email') ?></td>
  <td><input style="width: 200px;" type="text" name="email" value="<?= $societe->email ?>" /></td>
</tr>
<tr>
  <td>CP/Ville</td>
  <td>
    <input type="text" style="width: 50px;" name="cp" value="<?= $societe->cp ?>" />
    <input type="text" style="width: 80px;" name="ville" value="<?= $societe->ville ?>" />
  </td>
</tr>
<tr>
  <td>Date de cr&eacute;ation</td>
  <td><input style="width: 137px;" type="text" name="date_creation" value="<?= $societe->date_creation ?>" />
</tr>
<tr>
  <td colspan="2">
  Invoice top line <br/>
  <input style="width: 400px;" type="text" name="invoice_top_line1" value="<?= $societe->invoice_top_line1 ?>" /><br/>
  <input style="width: 400px;" type="text" name="invoice_top_line2" value="<?= $societe->invoice_top_line2 ?>" />
  </td>
</tr>
</table>

<h1>Logo</h1>

<?php

$result = mysql_query("SELECT type_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='logo'") or wf_die("My company::fetching logo");
$logo = mysql_fetch_object($result);

if (mysql_num_rows($result)) {
  printf('Logo actuel : <br/><img src="data:image/png;base64,%s" /><br/>', $logo->value);
}

?>
Changer le logo <input type="file" name="logo" /> <b>(ONLY PNG)</b>

  <h1><?=_('Bank Accounts')?></h1>
<table style="text-align: center;" class="framed" cellspacing="0" cellpadding="3">
<tr class="row_header">
  <td></td>
  <td><?=_('Bank')?></td>
  <td>Domiciliation</td>
  <td><?=_('Currency')?></td>
  <td>Code banque</td>
  <td>Code guichet</td>
  <td>N&deg; compte</td>
  <td>Clef</td>
  <td>IBAN</td>
  <td>SWIFT/BIC</td>
</tr>
<?php
$result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE type_pref='rib' AND owner=-1");
$count = 1;
while (list($id_pref,$value) = mysql_fetch_array($result)) {
  $compte = unserialize(base64_decode($value));

  // Check account number.
  //
  // Algorythm is : take the bank code (5 digits) + desk
  // code (5 digits) + account number (10 digits or letters). You get Ã  19 char
  // long "number" (which may contain letters). Replace letters in the following way :
  // A,J => 1 / B,K,S => 2 / C,L,T => 3 / D,M,U => 4 / E,N,V => 5, F,O,W => 6 /
  // G,P,X => 7 / H,Q,Y => 8 / I,R,Z => 9. Add 00 to the 19 char number.
  //   Checksum number  = 97 - ((21 digit number) % 97)
  //
  // PHP cannot do this calculus with normal functions (number is too big)
  // MySQL can. So we use a query for that.
  $bignum = $compte->code_banque.$compte->code_guichet.$compte->compte."00";
  $bignum = preg_replace("/[AJ]/", "1 ", $bignum);
  $bignum = preg_replace("/[BKS]/", "2 ", $bignum);
  $bignum = preg_replace("/[CLT]/", "3 ", $bignum);
  $bignum = preg_replace("/[DMU]/", "4 ", $bignum);
  $bignum = preg_replace("/[ENV]/", "5", $bignum);
  $bignum = preg_replace("/[FOW]/", "6 ", $bignum);
  $bignum = preg_replace("/[GPX]/", "7 ", $bignum);
  $bignum = preg_replace("/[HQY]/", "8 ", $bignum);
  $bignum = preg_replace("/[IRZ]/", "9", $bignum);

  $check_key = mysql_query("SELECT 97 - ($bignum % 97)") or print(mysql_error());
  list($key) = mysql_fetch_array($check_key);
  mysql_free_result($check_key);

  if ($key == $compte->clef) {
    $img = "paid";
    $hover_text = addslashes(_('The filled account number seems coherent with the check key'));
  } else {
    $img = "not_paid";
    $hover_text = addslashes(sprintf(_('Checksum fail on account number. Check digits entered. With this account number checksum should be %d'), $key));
  }
  $check_img = sprintf('<img src="/imgs/icons/%s.gif" onmouseover="return escape(\'%s\');" />',
                       $img, $hover_text );


  // End check account number

  print <<<EOF
<tr>
  <td>$check_img</td>
  <td><input style="width: 100px; text-align: center;" type="text" name="banque_$id_pref" value="$compte->banque" /></td>
  <td><input style="width: 100px; text-align: center;" type="text" name="domiciliation_$id_pref" value="$compte->domiciliation" /></td>
  <td><input style="width: 30px; text-align: center;" type="text" name="currency_$id_pref" value="$compte->currency" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="code_banque_$id_pref" value="$compte->code_banque" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="code_guichet_$id_pref" value="$compte->code_guichet" /></td>
  <td><input style="width: 90px; text-align: center;" type="text" name="compte_$id_pref" value="$compte->compte" /></td>
  <td><input style="width: 20px; text-align: center;" type="text" name="clef_$id_pref" value="$compte->clef" /></td>
  <td><input style="width: 80px; text-align: center;" type="text" name="iban_$id_pref" value="$compte->iban" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="swift_$id_pref" value="$compte->swift" /></td>
</tr>
EOF;
  $count++;
}
?>
<tr style="background: #ceffce;">
  <td></td>
  <td><input style="width: 100px; text-align: center;" type="text" name="banque_new" value="" /></td>
  <td><input style="width: 100px; text-align: center;" type="text" name="domiciliation_new" value="" /></td>
  <td><input style="width: 30px; text-align: center;" type="text" name="currency_new" value="€" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="code_banque_new" value="" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="code_guichet_new" value="" /></td>
  <td><input style="width: 90px; text-align: center;" type="text" name="compte_new" value="" /></td>
  <td><input style="width: 20px; text-align: center;" type="text" name="clef_new" value="" /></td>
  <td><input style="width: 80px; text-align: center;" type="text" name="iban_new" value="" /></td>
  <td><input style="width: 50px; text-align: center;" type="text" name="swift_new" value="" /></td>
</tr>
</table>
<i>Nota : pour supprimer un compte banquaire, positionner le num&eacute;ro de compte &agrave; vide</i><br/>
  <input type="submit" value=<?= _("Save") ?> />
</form>

<?php

$Revision = '$Revision$';
include("../bottom.php");

?>
