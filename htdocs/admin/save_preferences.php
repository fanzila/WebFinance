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

if($_POST['action']=="mail_user"){
  // Enregistrement adresse et raison sociale
  mysql_query("DELETE FROM webfinance_pref WHERE type_pref='mail_user'");

  $data = new stdClass();
  $data->body = $_POST['body'];

  $data = base64_encode(serialize($data));
  mysql_query("INSERT INTO webfinance_pref (type_pref, value) VALUES('mail_user', '$data')") or wf_mysqldie();

  $_SESSION['message']=_('Mail user info updated');
 }


header("Location: preferences.php");

?>
