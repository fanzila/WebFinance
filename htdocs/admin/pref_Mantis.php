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

$res = mysql_query(
  'select type_pref, value '.
  'from webfinance_pref '.
  "where type_pref in ('mantis_login', 'mantis_password', 'mantis_api_url')")
    or die(mysql_error());

$mantis = array(
  'mantis_login'    => '',
  'mantis_password' => '',
  'mantis_api_url'  => 'http://localhost/mantis/api/soap/mantisconnect.php',
);

while ($row = mysql_fetch_assoc($res))
  $mantis[$row['type_pref']] = $row['value'];

?>
<form action="save_mantis.php" method="post">

<table border="0" cellspacing="0" cellpadding="3" class="framed">

<tr style="text-align: center;" class="row_header">
  <td><?= _('Variable') ?></td>
  <td><?= _('Value') ?></td>
</tr>

  <tr class="row_even">
   <td>Login</td>
   <td><input type="text" name="login" value="<?=$mantis[mantis_login]?>" /></td>
  </tr>

  <tr class="row_even">
   <td>Password</td>
   <td><input type="password" name="password" value="<?=$mantis[mantis_password]?>" /></td>
  </tr>

  <tr class="row_even">
   <td>API URL</td>
   <td><input type="text" name="api_url" value="<?=$mantis[mantis_api_url]?>" /></td>
  </tr>

<tr class="row_even">
  <td style="text-align: center;" colspan="3"><input type="submit" value="<?= _('Save') ?>" /></td>
</table>

</form>
