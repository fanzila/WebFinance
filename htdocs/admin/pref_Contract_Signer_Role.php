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

<script type="text/javascript" language="javascript"
  src="/js/ask_confirmation.js"></script>

<form action="save_Contract_Signer_Role.php" id="main_form" method="post">

<table border="0" cellspacing="0" cellpadding="3" class="framed">
<tr style="text-align: center;" class="row_header">
  <td><?= _('Role') ?> </td>
  <td></td>
</tr>
<?php

$result = mysql_query("SELECT
                         id,
                         role
                       FROM contract_signer_role
                       ORDER BY role")
    or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
?>

<tr class="row_even">
  <td>
    <input type="text" name="cat[<?=$row[id]?>][role]" value="<?=$row[role]?>" style="width: 200px;" />
  </td>
  <td align="center">
    <a href="save_Contract_Signer_Role.php?action=delete&id=<?=$row[id]?>" onclick="return ask_confirmation('Are you sure you want to delete this entry?')"><img src="/imgs/icons/delete.gif" /></a>
  </td>
</tr>

<?
}

?>
<tr style="background: #ceffce;">
  <td><input type="text" name="cat[new][role]" value="" style="width: 200px;" /></td>
  <td></td>
</tr>

<tr class="row_even">
  <td style="text-align: center;" colspan="4"><input type="submit" value="<?= _('Save') ?>" /></td>
</table>

</form>
