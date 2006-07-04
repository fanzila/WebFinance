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

$title = _("Categories");
require("../inc/main.php");
$roles = 'manager,accounting';
require("../top.php");
require("nav.php");

$help_regexp = addslashes("Lors d'un import de CSV, les regexp permettront de classer automatiquement les opération dans les bonnes catégories");
$help_pcg = addslashes("Numéro de référence dans le plan comptable général. Voir http://www.plancomptable.com/pc99/titre-IV/liste_des_comptes_sb.htm");

?>

<script type="text/javascript">
  function confirmDelete(id,sort) {
  if (confirm('<?= _('Voulez-vous vraiment supprimer cette catégorie ?') ?>')) {
    window.location = 'save_categories.php?action=delete&sort='+sort+'&id='+id;
  }
}
</script>

<form action="save_categories.php" id="main_form" method="post">

<table border="0" cellspacing="0" cellpadding="3" class="framed">
<tr style="text-align: center;" class="row_header">
  <td><a href="?sort=name"><?= _('Name') ?></a></td>
  <td><a href="?sort=re"><?= _('Regexp') ?> <img class="help_icon" src="/imgs/icons/help.png" onmouseover="return escape('<?= $help_regexp ?>');" /></a></td>
  <td><a href="?sort=comment"><?= _('Comment') ?></a></td>
  <td>PCG <img class="help_icon" src="/imgs/icons/help.png" onmouseover="return escape('<?= $help_pcg ?>');" /></td>
  <td><a href="?sort=color"><?= _('Color') ?></a></td>
  <td>&nbsp;</td>
</tr>
<?php

$order_clause = "color";
$sort = "color";
if (isset($_GET['sort'])) {
  $sort = $_GET['sort'];
  switch ($_GET['sort']) {
    case 'color' :
      // Sort by color is complicated since we store HTML colors like #ff0000

      $order_clause = "HEX(MID(color, 1,2)),HEX(MID(color,3,2)),HEX(MID(color,5,2))";
      break;
    default:
      $order_clause = $_GET['sort'];
  }
}

$result = mysql_query("SELECT id,name,comment,re,plan_comptable,color
                       FROM webfinance_categories
                       ORDER BY $order_clause") or wf_mysqldie();
$count=1;
while ($c = mysql_fetch_assoc($result)) {
  extract($c);

  $class = ($count++ %2 == 0)?"even":"odd";
    $color_picker = sprintf('<input type="hidden" name="cat[%d][color]" id="color_%d" value="%s"><div id="couleur_%d" onclick="inpagePopup(event, this, 260, 240, \'/inc/color_picker.php?sample=couleur_%d&input=color_%d\');" onmouseover="return escape(\'Cliquez pour modifier la couleur.<br/>Actuellement : %s\');" style="width: 40px; height: 16px; background: %s"></div>',
                            $id, $id, $color, $id, $id, $id, $color, $color );

  print <<<EOF
<tr class="row_$class">
  <td><input type="text" name="cat[$id][name]" value="$name" style="width: 130px;" /></td>
  <td><input type="text" name="cat[$id][re]" value="$re" style="width: 175px;" /></td>
  <td><input type="text" name="cat[$id][comment]" value="$comment" style="width: 175px;" /></td>
  <td><input type="text" name="cat[$id][plan_comptable]" value="$plan_comptable" style="text-align: center; width: 40px;" /></td>
  <td>$color_picker</td>
  <td><a href="javascript:confirmDelete($id,'$sort');"><img src="/imgs/icons/delete.gif" /></a> <a href="index.php?filter[shown_cat][$id]='on'"><img src="/imgs/icons/zoom.gif" /></a></td>
</tr>
EOF;
}

?>
<tr style="background: #ceffce;">
  <td><input type="text" name="cat[new][name]" value="" style="width: 130px;" /></td>
  <td><input type="text" name="cat[new][re]" value="" style="width: 175px;" /></td>
  <td><input type="text" name="cat[new][comment]" value="" style="width: 175px;" /></td>
  <td><input type="text" name="cat[new][plan_comptable]" value="" style="text-align: center; width: 40px;" /></td>
  <td></td>
  <td></td>
</tr>
<tr class="row_even">
  <td style="text-align: center;" colspan="6">
   <input type="hidden" name="sort" value="<?= $sort ?>" />
   <input type="submit" value=<?= _("Save") ?> />
  </td>
</tr>
</table>

</form>

<?php
$Revision = '$Revision$';
require("../bottom.php");
?>
