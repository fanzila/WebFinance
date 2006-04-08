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

require("../inc/main.php");
require("../top.php");
require("nav.php");

?>
<script type="text/javascript">
function checkForm(f) {
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
