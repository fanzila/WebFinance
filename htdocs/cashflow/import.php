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
// $Id: import.php 531 2007-06-13 12:32:31Z thierry $
//
// Real importing is done in do_import.php + import_*.php

require("../inc/main.php");
$title = _("Import");
$roles = 'manager,admin';
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
  <td><?=_('Account :')?></td>
  <td><select name="id_account" style="width: 250px;">
  <?php
  $result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='rib'") or wf_mysqldie();
if(mysql_num_rows($result)>1){
  printf("<option value='-1'>%s</option>",_('-- Select an account --'));
 }

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
	<td>Format du fichier</td><td><select name="format"><option value="import_none.php">-- <?=_('Select')?> --</option><?php
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
$Revision = '$Revision: 531 $';
require("../bottom.php");
?>
