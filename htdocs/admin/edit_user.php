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
?>
<?php
# $Id: edit_user.php 551 2007-08-02 05:16:27Z gassla $
require("../inc/main.php");
include("../top_popup.php");

if (!is_numeric($_GET['id'])) {
  header("Location: index.php");
}

$User = new User();
$user = $User->getInfos($_GET['id']);
if ($_GET['id'] == -1) { $user->id_user = -1; }

?>

<script type="text/javascript">
function checkForm(f) {
  f.submit();
}
function confirmCancel(txt) {
  if(confirm(txt)){
    window.location = '/admin/save_user.php?action=cancel';
  }
}

</script>
<?php
$first_name=(isset($user->first_name))?$user->first_name:'';
$last_name=(isset($user->last_name))?$user->last_name:'';
$login=(isset($user->login))?$user->login:'';
$email=(isset($user->email))?$user->email:'';
?>


<form name="user_data" action="save_user.php" method="post">
<input type="hidden" name="id_user" value="<?= $user->id_user ?>" />
<table border="0" cellspacing="4" cellpadding="0">
<tr>
  <td><?=_('First name')?></td><td><input type="text" size="25" name="first_name" value="<?=$first_name ?>" /></td>
</tr>
<tr>
  <td><?=_('Last name')?></td><td><input type="text" size="25" name="last_name" value="<?=$last_name ?>" /></td>
</tr>

<tr>
  <td>Login</td><td><input type="text" size="20" name="login" value="<?=$login ?>" /></td>
</tr>

<tr>
  <td><?=_('Password')?></td><td><input type="password" size="20" name="password"/></td>
</tr>

<tr>
  <td>Email</td><td><input type="text" size="25" name="email" value="<?=$email ?>" /></td>
</tr>

<tr>
  <td>Compte désactivé</td>
  <td><input type="checkbox" name="disabled" <?= (isset($user->disabled) and $user->disabled)?"checked":"" ?> /></td>
</tr>

<tr>
  <td>Créé le </td>
  <td><?=(isset($user->nice_creation_date))?$user->nice_creation_date:'' ?></td>
</tr>

<tr>
  <td>Modifié le </td>
  <td><?=(isset($user->nice_modification_date))?$user->nice_modification_date:'' ?></td>
</tr>

 <tr>
 <td colspan="2">
<?
   $result=mysql_query("SELECT id_role, name FROM webfinance_roles") or wf_mysqldie();
   while($role=mysql_fetch_assoc($result)){
     if($role['name']!="client")
       printf("<input type='checkbox' name='role[]' %s value='%s' >%s",($User->hasRole($role['name'] , $user->id_user )>0)?"checked":"",$role['name'],$role['name'] );
   }
?>
 </td>
 </tr>
 <tr>
  <td style="text-align: center;">
  <input style="width: 97px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="submit_button" onclick="return checkForm(this.form);" type="submit" value="<?=_('Save')?>" />
  </td>
  <td style="text-align: center;">
  <input style="width: 97px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="cancel_button" type="button" onclick="confirmCancel('<?=_('Confirm ?')?>');" value="<?=_('Cancel')?>" />
  </td>
</tr>
</table>
</form>

</script>
<script src="../js/inpage_popup.js">
</script>
