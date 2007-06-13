<?php
/*
   This file is part of Webfinance.

    Webfinance is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Webfinance is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Webfinance; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
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
must_login();

if ($_GET['action'] == "cancel") {
  $_POST="";
  $_GET="";
 }else{

  $User = new User();

  if ($_GET['action'] == "delete") {
    $User->delete($_GET['id']);
    header("Location: index.php");
    die();
  }

  extract($_POST);
  if (!preg_match("/^[0-9-]+$/", $id_user)) {
    //  header("Location: index.php");
    die();
  }
  if ($_POST['id_user'] > 0) {
    $User->saveData($_POST);
    //  header("Location: fiche_user.php?id=".$_POST['id_user']);
  } else {
    $User->createUser($_POST);
    //  header("Location: fiche_user.php?id=$id");
  }

 }
?>
<script>
popup = window.parent.document.getElementById('inpage_popup');
popup.style.display = 'none';
// Reload parent window to update contacts
page = '/admin/index.php?foobar='+100*Math.random(); // Random to force reload
window.parent.location = page;
</script>
