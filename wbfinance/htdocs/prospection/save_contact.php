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

include("../inc/main.php");

if ($GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'] != "POST") {
  die();
}

$User = new User();

$user_data=array(
		 "id_user"=>$_POST['id_user'],
		 "login"=>$_POST['login'],
		 "first_name"=>$_POST['prenom'],
		 "last_name"=>$_POST['nom'],
		 "passwd"=>$_POST['passwd'],
		 "email"=>$_POST['email'],
		 "role"=>array("client"),
		 "disabled"=>"off",
		 "admin"=>"off"
		 );


if ($_POST['action'] == "create") {

  if( empty($user_data['passwd']) )
    $user_data['passwd']=$User->randomPass();

  $id_user = $User->createUser($user_data);

  $_SESSION['tmp_message'] = $_SESSION['message'];

  $q = sprintf("INSERT INTO webfinance_personne (id_user,nom,prenom,email,tel,mobile,client,fonction,date_created) VALUES (%d,'%s', '%s', '%s', '%s', '%s', %d, '%s', now())",
	       $id_user,$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['client'], $_POST['fonction'] );

  mysql_query($q) or wf_mysqldie("Error inserting personne");

  $_SESSION['tmp_message'] .= "<br/>"._("Contact added");

  if(isset($_POST['send_info'])){
    $User->sendInfo($id_user,$user_data['passwd']);
    $_SESSION['tmp_message'] .= "<br/>".$_SESSION['message'];
  }
  $_SESSION['message']=$_SESSION['tmp_message'];
  $_SESSION['tmp_message']="";

} elseif ($_POST['action'] == "save") {

  $result = mysql_query("SELECT count(id_user) FROM webfinance_users WHERE id_user=".$_POST['id_user']) or wf_mysqldie();
  list($exists) = mysql_fetch_array($result);
  if(!$exists)
    $_POST['id_user'] = $User->createUser($user_data);
  else
    $User->saveData($user_data);

  $q = sprintf("UPDATE webfinance_personne SET id_user=%d, nom='%s',prenom='%s',email='%s',tel='%s',mobile='%s',fonction='%s',note='%s' WHERE id_personne=%d",
               $_POST['id_user'], $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['fonction'], $_POST['note'], $_POST['id_personne']);

  mysql_query($q) or wf_mysqldie("Saving person");

  $_SESSION['message'] .= " <br/>"._("Contact updated");

} elseif ($_POST['action'] == "delete") {
  $User->delete($_POST['id_user']);
  mysql_query("DELETE FROM webfinance_personne WHERE id_personne=".$_POST['id_personne']);

  $_SESSION['message'] .= " <br/>"._("Contact deleted");

} else {
  die(_("Don't know what to do with posted data"));
}

?>
<script>
popup = window.parent.document.getElementById('inpage_popup');
popup.style.display = 'none';
// Reload parent window to update contacts
page = 'fiche_prospect.php?id=<?= $_POST['client'] ?>&onglet=contacts&foobar='+100*Math.random(); // Random to force reload
window.parent.location = page;
</script>
