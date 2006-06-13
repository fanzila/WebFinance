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

$UserContact = new User();

$user_data=array(
		 "id_user"=>$_POST['id_user'],
		 "login"=>$_POST['login'],
		 "first_name"=>$_POST['prenom'],
		 "last_name"=>$_POST['nom'],
		 "password"=>$_POST['password'],
		 "email"=>$_POST['email'],
		 "role"=>array("client"),
		 "disabled"=>"off",
		 "admin"=>"off"
		 );

if ($_POST['action'] == "create") {

  if( empty($user_data['password']) )
    $user_data['password']=$UserContact->randomPass();

  $id_user = $UserContact->createUser($user_data);

  $_SESSION['tmp_message'] = $_SESSION['message'];

  $q = sprintf("INSERT INTO webfinance_personne (id_user,nom,prenom,email,tel,mobile,client,fonction,date_created) VALUES (%d,'%s', '%s', '%s', '%s', '%s', %d, '%s', now())",
	       $id_user,$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['client'], $_POST['fonction'] );

  mysql_query($q) or wf_mysqldie("Error inserting personne");

  $_SESSION['tmp_message'] .= "<br/>"._("Contact added");

  if(isset($_POST['send_info'])){
    $UserContact->sendInfo($id_user,$user_data['password']);
    $_SESSION['tmp_message'] .= "<br/>".$_SESSION['message'];
  }
  $_SESSION['message']=$_SESSION['tmp_message'];
  $_SESSION['tmp_message']="";
  logmessage(_('Add contact')." ".$_POST['nom']." ".$_POST['prenom']. " ( user:$id_user client:".$_POST['client'].")"  );

} elseif ($_POST['action'] == "save") {

  //  echo "<pre/>"; print_r($user_data); exit;

  $result = mysql_query("SELECT count(*) FROM webfinance_users WHERE id_user=".$_POST['id_user']) or wf_mysqldie();
  list($exists) = mysql_fetch_array($result);
  if($exists<1)
    $_POST['id_user'] = $UserContact->createUser($user_data);
  else
    $UserContact->saveData($user_data);

  $q = sprintf("UPDATE webfinance_personne SET id_user=%d, nom='%s',prenom='%s',email='%s',tel='%s',mobile='%s',fonction='%s',note='%s' WHERE id_personne=%d",
               $_POST['id_user'], $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['fonction'], $_POST['note'], $_POST['id_personne']);

  mysql_query($q) or wf_mysqldie("Saving person");

  $_SESSION['message'] .= " <br/>"._("Contact updated");

  $res=mysql_query("SELECT client,id_user FROM webfinance_personne WHERE id_personne=".$_POST['id_personne']);
  list($client)=mysql_fetch_array($res);

  logmessage(_('Update contact')." ".$_POST['nom']." ".$_POST['prenom']." (user:".$_POST['id_user']." client:$client)" );

} elseif ($_POST['action'] == "delete") {
  $UserContact->delete($_POST['id_user']);

  $res=mysql_query("SELECT nom, prenom, client FROM webfinance_personne WHERE id_personne=".$_POST['id_personne']);
  list($nom, $prenom,$client)=mysql_fetch_array($res);
  logmessage(_('Delete contact')." $nom $prenom client:$client " );

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
page = '/prospection/fiche_prospect.php?id=<?= $_POST['client'] ?>&onglet=contacts&foobar='+100*Math.random(); // Random to force reload
window.parent.location = page;
</script>
