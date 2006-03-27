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
include("../inc/backoffice.php");

if ($GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'] != "POST") {
  die();
}

if ($_POST['action'] == "create") {
  $q = sprintf("INSERT INTO personne (nom,prenom,email,tel,mobile,client,fonction,date_created) VALUES('%s', '%s', '%s', '%s', '%s', %d, '%s', now())", 
               $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['client'], $_POST['fonction'] );
  mysql_query($q) or nbi_mysqldie("Error inserting personne");
} elseif ($_POST['action'] == "save") {
  $q = sprintf("UPDATE personne SET nom='%s',prenom='%s',email='%s',tel='%s',mobile='%s',fonction='%s',note='%s' WHERE id_personne=%d", 
               $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['fonction'], $_POST['note'], $_POST['id_personne']);

  mysql_query($q) or nbi_mysqldie("Saving person");
} elseif ($_POST['action'] == "delete") {
  mysql_query("DELETE FROM personne WHERE id_personne=".$_POST['id_personne']);
} else {
  die("Don't know what to do with posted data");
}

?>
<script>
popup = window.parent.document.getElementById('inpage_popup');
popup.style.display = 'none';
// Reload parent window to update contacts
page = window.parent.location + '&onglet=contacts&foobar='+100*Math.random(); // Random to force reload 
window.parent.location = page;
</script>
