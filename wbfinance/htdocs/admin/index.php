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
function confirmDeleteRole(id) {
  if (confirm('Voulez-vous vraiment supprimer cet role ?')) {
    window.location = 'save_roles.php?action=delete&id='+id;
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
$result = mysql_query("SELECT first_name,last_name,id_user,email,login,date_format(last_login,'%d/%m/%Y') as nice_last_login
                       FROM webfinance_users ORDER by last_login DESC");
$count=1;
while ($user = mysql_fetch_object($result)) {
  $rowclass = ($count%2)==0?"odd":"even";
  print <<<EOF
<tr class="row_$rowclass">
  <td style="text-align: center">$user->login</td>
  <td>$user->first_name $user->last_name</td>
  <td><a href="mailto:$user->email">$user->email</a></td>
  <td>$user->nice_last_login</td>
  <td>
    <a href="javascript:confirmDeleteUser($user->id_user);"><img src="/imgs/icons/delete.png" alt="<?= _('Delete')?>" /></a>
    <a href="fiche_user.php?id=$user->id_user"><img src="/imgs/icons/edit.png" alt="Modifier" /></a>
  </td>
</tr>
EOF;
  $count++;
}
mysql_free_result($result);


$help_rights=addslashes("Les valeurs possible sont: client,manager,accounting,employee.");

?>
</table><br/>
<a href="fiche_user.php?id=-1"><?= _('Créer un utilisateur') ?></a>

<p>
<h2>Roles</h2>

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
  <td><input type="text" name="cat[$id_role][name]" value="$name" style="width: 80px;" /></td>
  <td><input type="text" name="cat[$id_role][description]" value="$description" style="width: 200px;" /></td>
  <td align="center"><a href="javascript:confirmDeleteRole($id_role);"><img src="/imgs/icons/delete.gif" /></a>
</tr>
EOF;
}

?>
<tr style="background: #ceffce;">
  <td><input type="text" name="cat[new][name]" value="" style="width: 80px;" /></td>
  <td><input type="text" name="cat[new][description]" value="" style="width: 200px;" /></td>
  <td></td>
</tr>
<tr class="row_even">
  <td style="text-align: center;" colspan="3"><input type="submit" value="<?= _('Save') ?>" /></td>
</table>

</form>

</p>


<?php

$Revision = '$Revision$';
include("../bottom.php");

?>
