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

if ($_POST['action'] == "create") {
  $q = sprintf("INSERT INTO webfinance_personne (nom,prenom,email,tel,mobile,client,fonction,date_created) VALUES('%s', '%s', '%s', '%s', '%s', %d, '%s', now())",
               $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['client'], $_POST['fonction'] );
  mysql_query($q) or nbi_mysqldie("Error inserting personne");
} elseif ($_POST['action'] == "save") {
  $q = sprintf("UPDATE webfinance_personne SET nom='%s',prenom='%s',email='%s',tel='%s',mobile='%s',fonction='%s',note='%s' WHERE id_personne=%d",
               $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel'], $_POST['mobile'], $_POST['fonction'], $_POST['note'], $_POST['id_personne']);

  mysql_query($q) or nbi_mysqldie("Saving person");
} elseif ($_POST['action'] == "delete") {
  mysql_query("DELETE FROM webfinance_personne WHERE id_personne=".$_POST['id_personne']);
} else {
  die("Don't know what to do with posted data");
}

?>
<script>
popup = window.parent.document.getElementById('inpage_popup');
popup.style.display = 'none';
// Reload parent window to update contacts
page = 'fiche_prospect.php?id=<?= $_POST['client'] ?>&onglet=contacts&foobar='+100*Math.random(); // Random to force reload
window.parent.location = page;
</script>