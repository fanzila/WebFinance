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

$help_rights=addslashes("Les valeurs possible sont: client,manager,accounting,employee.");

?>
<script type="text/javascript">
    function confirmDeleteRole(id,txt) {
  if (confirm(txt)) {
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
    $txt = _('Voulez-vous vraiment supprimer cet role ?');
$result = mysql_query("SELECT id_role, name, description
                       FROM webfinance_roles
                       ORDER BY name") or wf_mysqldie();
while ($c = mysql_fetch_assoc($result)) {
  extract($c);

  print <<<EOF
<tr class="row_even">
  <td><input type="text" name="cat[$id_role][name]" value="$name" style="width: 100px;" /></td>
  <td><input type="text" name="cat[$id_role][description]" value="$description" style="width: 300px;" /></td>
  <td align="center"><a href="javascript:confirmDeleteRole($id_role,'$txt');"><img src="/imgs/icons/delete.gif" /></a>
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
