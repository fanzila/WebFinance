<?php
// $Id$

$help_rights=addslashes("Les valeurs possible sont: client,manager,accounting,employee.");

?>
<script type="text/javascript">
function confirmDeleteRole(id) {
  if (confirm('Voulez-vous vraiment supprimer cet role ?')) {
    window.location = 'save_roles.php?action=delete&id='+id;
  }
}
</script>

<form action="save_roles.php" id="main_form" method="post">

<table border="0" cellspacing="0" cellpadding="3" class="framed">
<tr style="text-align: center;" class="row_header">
  <td><?= _('Name') ?><img class="help_icon" src="/imgs/icons/help.png" onmouseover="return escape('<?= $help_rights ?>');" /> </td>
  <td><?= _('Description') ?></td>
  <td><?= _('Actions') ?></td>
</tr>
<?php

$result = mysql_query("SELECT id_role, name, description
                       FROM webfinance_roles
                       ORDER BY name") or wf_mysqldie();
while ($c = mysql_fetch_assoc($result)) {
  extract($c);

  print <<<EOF
<tr class="row_even">
  <td><input type="text" name="cat[$id_role][name]" value="$name" style="width: 100px;" /></td>
  <td><input type="text" name="cat[$id_role][description]" value="$description" style="width: 300px;" /></td>
  <td align="center"><a href="javascript:confirmDeleteRole($id_role);"><img src="/imgs/icons/delete.gif" /></a>
</tr>
EOF;
}

?>
<tr style="background: #ceffce;">
  <td><input type="text" name="cat[new][name]" value="" style="width: 100px;" /></td>
  <td><input type="text" name="cat[new][description]" value="" style="width: 300px;" /></td>
  <td></td>
</tr>
<tr class="row_even">
  <td style="text-align: center;" colspan="3"><input type="submit" value="<?= _('Save') ?>" /></td>
</table>

</form>
