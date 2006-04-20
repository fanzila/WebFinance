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

$User = new User();

if ($_GET['action'] == "delete") {
  $User->delete($_GET['id']);
  header("Location: index.php");
  die();
}

extract($_POST);
if (!preg_match("/^[0-9-]+$/", $id_user)) {
  header("Location: index.php");
  die();
}
if ($_POST['id_user'] > 0) {
  $User->saveData($_POST);
  header("Location: fiche_user.php?id=".$_POST['id_user']);
} else {
  $id = $User->createUser($_POST);
  header("Location: fiche_user.php?id=$id");
}


?>
