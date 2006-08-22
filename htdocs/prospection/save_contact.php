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
must_login();

if ($GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'] != "POST") {
  die();
}

if ($_POST['action'] == "create") {

  $_SESSION['tmp_message'] = $_SESSION['message'];

  $q = sprintf("INSERT INTO webfinance_personne (nom,prenom,email,tel,mobile,client,fonction,date_created,note) VALUES ('%s', '%s', '%s', '%s', '%s', %d, '%s', now(),'%s')",
	       $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['client'], $_POST['fonction'], $_POST['note'] );

  mysql_query($q) or wf_mysqldie("Error inserting personne");

  $_SESSION['message'] = _("Contact added");

  logmessage(_('Add contact')." ".$_POST['nom']." ".$_POST['prenom']. " ( client:".$_POST['client'].")"  );

} elseif ($_POST['action'] == "save") {

  //  echo "<pre/>"; print_r($user_data); exit;

  $q = sprintf("UPDATE webfinance_personne SET nom='%s',prenom='%s',email='%s',tel='%s',mobile='%s',fonction='%s',note='%s' WHERE id_personne=%d",
               $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['fonction'], $_POST['note'], $_POST['id_personne']);

  mysql_query($q) or wf_mysqldie("Saving person");

  $_SESSION['message'] = _("Contact updated");

  $res=mysql_query("SELECT client FROM webfinance_personne WHERE id_personne=".$_POST['id_personne']);
  list($client)=mysql_fetch_array($res);

  logmessage(_('Update contact')." ".$_POST['nom']." ".$_POST['prenom']." ( client:$client)" );

} elseif ($_POST['action'] == "delete") {
  $res=mysql_query("SELECT nom, prenom, client FROM webfinance_personne WHERE id_personne=".$_POST['id_personne']);
  list($nom, $prenom,$client)=mysql_fetch_array($res);
  logmessage(_('Delete contact')." $nom $prenom client:$client " );

  mysql_query("DELETE FROM webfinance_personne WHERE id_personne=".$_POST['id_personne']);

  $_SESSION['message'] = _("Contact deleted");

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
