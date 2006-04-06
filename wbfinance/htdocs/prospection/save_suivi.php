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
<?include "../nbi/functions.php" ?>
<?php
connect();
session_start();
if (!nbi_is_logued()) {
  nbi_redirect("/login.php");
}
if ($GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'] != "POST") {
  die();
}
$user = nbi_get_user_info();

if ($_POST['action'] == "create") {
  $q = sprintf("INSERT INTO webcash_suivi (type_suivi,id_objet,message,date_added,added_by,rappel) VALUES(%d, %d, '%s', now(), %d, %s)",
               $_POST['type_suivi'], $_POST['id_client'], $_POST['message'], $user->id_user,
               ($_POST['deltadays']!=0)?"date_add(now(), INTERVAL ".$_POST['deltadays']." DAY)":"NULL");

  mysql_query($q) or nbi_mysqldie();
} elseif ($_POST['action'] == "save") {
} else {
  die("Don't know what to do with posted data");
}
nbi_redirect("index.php?file=fiche_prospect&id=".$_POST['id_client']);
?>
