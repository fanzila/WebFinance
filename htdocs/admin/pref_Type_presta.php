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
<script type="text/javascript">
function confirmDeleteType(id, txt) {
  if (confirm(txt)) {
    window.location = 'save_preferences.php?action=type_presta_delete&id='+id;
  }
}
</script>

<form action="save_preferences.php" id="main_form" method="post">
<input type="hidden" name="action" value="type_presta"/>
<table border="0" cellspacing="0" cellpadding="3">
<?php

$result = mysql_query("SELECT id_type_presta, nom
                       FROM webfinance_type_presta
                       ORDER BY nom") or wf_mysqldie();
while ($c = mysql_fetch_assoc($result)) {
  extract($c);

  $txt=_("Do you really want to delete it?");
  print <<<EOF
<tr class="row_even">
  <td><input type="text" name="cat[$id_type_presta][nom]" value="$nom" style="width: 350px;" /></td>
  <td align="center"><a href="javascript:confirmDeleteType($id_type_presta,'$txt');"><img src="/imgs/icons/delete.gif" /></a>
</tr>
EOF;
}

?>
<tr style="background: #ceffce;">
  <td colspan="2"><input type="text" name="cat[new][nom]" value="" style="width: 350px;" /></td>
</tr>
<tr class="row_even">
  <td style="text-align: center;" colspan="3"><input type="submit" value="<?= _('Save') ?>" /></td>
</table>

</form>
