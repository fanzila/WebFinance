<?php 
// 
// This file is part of « Backoffice NBI »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<?php

// $Id$
include("../inc/backoffice.php");
include("../top.php");
include("nav.php");

if (!is_numeric($_GET['id'])) {
  header("Location: index.php");
}

$User = new User();
$user = $User->getInfos($_GET['id']);
if ($_GET['id'] == -1) { $user->id_user = -1; }

?>

<h2>Modification d'un utilisateur</h2><br/>

<script type="text/javascript">
function checkForm(f) {
  f.submit();
}
</script>

<div style="background: #ffcece"><?= $_SESSION['message']; $_SESSION['message']=""; ?></div>

<form name="userdata" action="save_user.php" method="post">
<input type="hidden" name="id_user" value="<?= $user->id_user ?>" />
<table border="0" cellspacing="0" cellpadding="3">
<tr>
  <td>Prénom</td><td><input class="border" type="text" size="20" name="first_name" value="<?=$user->first_name ?>" /></td>
  <td>Nom</td><td><input class="border" type="text" size="20" name="last_name" value="<?=$user->last_name ?>" /></td>
</tr>
<tr>
  <td>Login</td><td><input class="border" type="text" size="20" name="login" value="<?=$user->login ?>" /></td>
  <td>Email</td><td><input class="border" type="text" size="50" name="email" value="<?=$user->email ?>" /></td>
</tr>
<tr>
  <td valign="top" colspan="3">
    <input type="checkbox" name="disabled" <?= ($user->disabled)?"checked":"" ?> /> Compte désactivé<br/>
    <input type="checkbox" name="admin" <?= ($user->admin)?"checked":"" ?> /> Administrateur<br/>
  </td>
  <td>
    Créé le <?=$user->nice_creation_date?><br/>
    Modifié le <?=$user->nice_modification_date?>
  </td>
</tr>
<tr>
  <td colspan="4" style="text-align: center;">
    <img onclick="checkForm(document.forms['userdata']);" src="<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:off")).'.png' ?>" onmouseover="this.src='<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:on")).'.png' ?>';" onmouseout="this.src='<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:off")).'.png' ?>';" />
    <img onclick="window.location='index.php'" src="<?= '/imgs/boutons/'.urlencode(base64_encode("Annuler:off")).'.png' ?>" onmouseover="this.src='<?= '/imgs/boutons/'.urlencode(base64_encode("Annuler:on")).'.png' ?>';" onmouseout="this.src='<?= '/imgs/boutons/'.urlencode(base64_encode("Annuler:off")).'.png' ?>';" />
  </td>
</tr>
</table>
</form>


<?php
include("../bottom.php");
?>