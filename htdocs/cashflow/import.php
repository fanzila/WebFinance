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
//
// Real importing is done in do_import.php + import_*.php

$title = _("Import");
require("../inc/main.php");
require("../top.php");
require("nav.php");

?>
<script type="text/javascript">
function checkForm(f) {
  if(f.id_account.options[f.id_account.selectedIndex].value == '-1'){
    alert('Veuillez choisir un compte bancaire');
    return false;
  }
  if (f.csv.value == '') {
    alert('Veuillez choisir le fichier à importer');
    return false;
  }
  if (f.format.options[f.format.selectedIndex].value == 'import_none.php') {
    alert('Veuillez choisir le format du fichier fourni');
    return false;
  }

  return true;
}
</script>
<form onsubmit="return checkForm(this);" id="main_form" action="do_import.php" method="post" enctype="multipart/form-data">

<table class="bordered" border="0" cellspacing="0" cellpadding="3">
<tr>
  <td><?=_('Account')?></td>
  <td><select name="id_account" style="width: 150px;">
        <option value="-1"><?= _('-- Select an account --') ?></option>
      <?php
      $result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='rib'");
      while (list($id_cpt,$cpt) = mysql_fetch_array($result)) {
        $cpt = unserialize(base64_decode($cpt));
        printf(_('        <option value="%d"%s>%s #%s</option>')."\n", $id_cpt, ($filter['id_account']==$id_cpt)?" selected":"", $cpt->banque, $cpt->compte );
      }
      mysql_free_result($result);
      ?></td>
</tr>
<tr>
  <td>Fichier CSV</td><td><input type="file" name="csv" /></td>
</tr>
<tr>
  <td>Format du fichier</td><td><select name="format"><option value="import_none.php">-- Choisissez --</option><?php
    foreach (glob("import_*.php") as $filtre) {
      preg_match("/import_(.*).php$/", $filtre, $matches);

      printf('<option value="%s">%s</option>', $filtre, $matches[1]);
    }
    ?></select>
  </td>
</tr>
<tr>
<td style="text-align: center;" colspan="2">
  <input type="submit" value="Importer">
</td>
</tr>
</table>
</form>

<?php
$Revision = '$Revision$';
require("../bottom.php");
?>
