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
// This is a indocPopup : see inpage_popup.js
include( "../inc/main.php" );
include("../top_popup.php");

if ((isset($_GET['id_client'])) && (!preg_match("/^[0-9]+$/", $_GET['id_client']))) {
  die("Wrong parameters");
} else {
}
if (isset($_GET['id_personne'])) {
  $action = "save";

  $result = mysql_query("SELECT id_personne,id_user,nom,prenom,email,tel,mobile,client,fonction,note FROM webfinance_personne WHERE id_personne=".$_GET['id_personne'])
    or wf_mysqldie();
  $personne = mysql_fetch_object($result);
  mysql_free_result($result);

  $User=new User();
  $user=$User->getInfo($personne->id_user);
  $personne->login=$user->login;

} else {
  $action = "create";
  $personne = new stdClass();
  $personne->client = $_GET['id_client'];
  $personne->id_user = -1;
  $personne->login="";

  $title = "Ajout d'un contact pour $nom_client";
}
?>
<form id="main_form" action="save_contact.php" method="post">
<input type="hidden" name="action" value="<?= $action ?>">
<input type="hidden" name="client" value="<?= $personne->client ?>">
<input type="hidden" name="id_personne" value="<?= $personne->id_personne ?>">
<input type="hidden" name="id_user" value="<?= $personne->id_user ?>">

<table align="center" border="0" cellspacing="5" cellpadding="0">
<tr>
  <td width="50">Nom</td><td><input type="text" style="width: 145px;" name="nom" value="<?= $personne->nom ?>" /></td>
</tr>
<tr>
  <td>Prénom</td><td><input type="text" style="width: 145px;" name="prenom" value="<?= $personne->prenom ?>" /></td>
</tr>
<tr>
  <td>Fonction</td><td><input type="text" style="width: 145px;" name="fonction" value="<?= $personne->fonction ?>" /></td>
</tr>
<?
  if( !($User->exists($personne->id_user)) ){
?>
<tr>
  <td width="50"><?=_('Login')?></td><td><input type="text" width: 145px;" name="login" value="<?= $personne->login ?>" /></td>
</tr>
<tr>
 <td width='50'><?=_('Password')?></td><td><input type='password' style='width: 145px;' name='password' /></td>
</tr>
<tr>
  <td width="70"><?=_('Send info')?></td><td><input type="checkbox" name="send_info"/></td>
</tr>
<?
  }else{
 ?>
<tr>
  <td width="50"><?=_('Login')?></td><td width="145"><?= $personne->login ?></td>
</tr>
<?
  }
?>
<tr>
  <td colspan="2"><input class="email" type="text" size="20" name="email" value="<?= $personne->email ?>" /></td>
</tr>
<tr>
<td colspan="2">
<input type="text" class="tel" name="tel" value="<?= $personne->tel ?>" /><input type="text" class="gsm" name="mobile" value="<?= $personne->mobile ?>" />
</td>
</tr>
<tr>
<td colspan="2">
<textarea style="width: 200px; height: 50px;" name="note"><?= $personne->note ?></textarea>
</td>
</tr>
<tr>
<td colspan="2">
  <input style="width: 97px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="submit_button" onclick="return checkForm(this.form);" type="submit" value="Enregistrer" />
  <input style="width: 97px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="delete_button" type="button" onclick="confirmDelete(this.form);" value="Supprimer" />
</td>
</tr>
</form>

<script language="javascript">

function checkTel(input) {
  orig = input.value;

  function test_func() {
    alert('in test func');
  }

  input.value = input.value.replace(/[^0-9]/g, "");
  if (input.value.length < 10) {
    alert('Les numéros de téléphone s\'écrivent sur 10 chiffres !');
    input.select();
    input.focus();
    return false;
  }

  input.value = input.value.substring(0,2)+' '+input.value.substring(2,4)+' '+input.value.substring(4,6)+' '+input.value.substring(6,8)+' '+input.value.substring(8,10);

  return true;
}

function checkForm(f) {
  if ((f.tel.value != '') && (! checkTel(f.tel))) {
    return false;
  }
  if ((f.mobile.value != '') && (! checkTel(f.mobile))) {
    return false;
  }
  if ((f.nom.value == '') && (f.prenom.value == '')) {
    alert('Il faut au moins un nom ou un prénom');
    return false;
  }
  if (f.login.value == '') {
    alert('login is required!');
    return false;
  }

  return true;
}
function confirmDelete(f) {
  if (confirm('Voulez-vous vraiment supprimer '+f.prenom.value+' '+f.nom.value+' ?')) {
    f.action.value = "delete";
    f.submit();
  }
}
function focusNom() {
  f = document.getElementById('main_form');
  f.nom.select();
  f.nom.focus();
}

focusNom();
</script>

<script src="/js/inpage_popup.js"></script>

</body>
</html>
