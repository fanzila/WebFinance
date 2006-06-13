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

include("../inc/main.php");
$title = _("Administration");
$roles = "admin,manager,employee";
include("../top.php");
include("nav.php");
?>
<script type="text/javascript">
function confirmDeleteUser(id) {
  if (confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) {
    window.location = 'save_user.php?action=delete&id='+id;
  }
}
</script>

<?= $_SESSION['message']; $_SESSION['message']=""; ?>

<h2>Utilisateurs backoffice</h2>

<table border="0" cellspacing="0" cellpadding="5" class="framed">
<tr align=center class="row_header">
  <td>Login</td>
  <td>Nom</td>
  <td>Mail</td>
  <td>Last login</td>
  <td>Actions</td>
</tr>
<?php
$result = mysql_query("SELECT first_name,last_name,id_user,email,login, role, date_format(last_login,'%d/%m/%Y') as nice_last_login
                       FROM webfinance_users ORDER by last_login DESC");
$count=1;
while ($user = mysql_fetch_object($result)) {
  $rowclass = ($count%2)==0?"odd":"even";
  if($user->role!='client'){
  print <<<EOF
<tr class="row_$rowclass">
  <td style="text-align: center">$user->login</td>
  <td>$user->first_name $user->last_name</td>
  <td><a href="mailto:$user->email">$user->email</a></td>
  <td>$user->nice_last_login</td>
  <td>
    <a href="javascript:confirmDeleteUser($user->id_user);"><img src="/imgs/icons/delete.png" alt="<?= _('Delete')?>" /></a>
    <a href="#" onclick="inpagePopup(event, this, 280, 260, 'edit_user.php?id=$user->id_user');" ><img src="/imgs/icons/edit.png" alt="Modifier" /></a>
  </td>
</tr>
EOF;
  $count++;

  }
}
mysql_free_result($result);

?>
</table><br/>
<a href="#" onclick="inpagePopup(event, this, 280, 260, 'edit_user.php?id=-1');"><?= _('Add a user') ?></a>

<?php

$Revision = '$Revision$';
include("../bottom.php");

?>
