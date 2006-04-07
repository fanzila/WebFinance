<?php 
// 
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php

// $Id$

require("../inc/main.php");
require("../top.php");
require("nav.php");

$help_regexp = addslashes("Lors d'un import de CSV, les regexp permettront de classer automatiquement les opération dans les bonnes catégories");

?>

<script type="text/javascript">
function confirmDelete(id) {
  if (confirm('Voulez-vous vraiment supprimer cette catégorie ?\n')) {
    window.location = 'save_categories.php?action=delete&id='+id;
  }
}
</script>

<form id="main_form" method="post">

<table border="0" cellspacing="0" cellpadding="0" class="listing">
<tr class="row_header">
  <td>Nom</td>
  <td>Classe</td>
  <td>Regexp<img src="/imgs/icons/help.gif" onmouseover="return escape('<?= $help_regexp ?>');" /></td>
  <td>Commentaire</td>
  <td></td>
</tr>
<?php
$result = mysql_query("SELECT id,name,comment,class,re
                       FROM webfinance_categories
                       ORDER BY name") or die(mysql_error());
while ($c = mysql_fetch_assoc($result)) {
  extract($c);
  print <<<EOF
<tr>
  <td><input type="text" name="name_$id" value="$name" style="width: 80px;" /></td>
  <td><input type="text" name="class_$id" value="$class" style="width: 50px;" /></td>
  <td><input type="text" name="regexp_$id" value="$regexp" style="width: 100px;" /></td>
  <td><input type="text" name="comment_$id" value="$comment" style="width: 200px;" /></td>
  <td><a href="javascript:confirmDelete($id);"><img src="/imgs/icons/delete.gif" /></a></td>
</tr>
EOF;
}
?>


<tr>
  <td style="text-align: center;" colspan="5"><input type="submit" value="Enregistrer" /></td>
</table>

</form>

<?php
$Revision = '$Revision$';
require("../bottom.php");
?>
