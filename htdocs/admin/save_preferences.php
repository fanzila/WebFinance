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

require("../inc/main.php");
must_login();

if(preg_match('/^mail_/',$_POST['action']) ){
  // Enregistrement adresse et raison sociale
  mysql_query("DELETE FROM webfinance_pref WHERE type_pref='".$_POST['action']."'");

  $data = new stdClass();
  $data->body = utf8_encode($_POST['body']);
  $data->subject = utf8_encode($_POST['subject']);

  $data = base64_encode(serialize($data));
  mysql_query("INSERT INTO webfinance_pref (type_pref, value) VALUES ('".$_POST['action']."', '$data')") or wf_mysqldie();

  $_SESSION['message']=_('Preference saved');

  if(preg_match('/invoice/i',$_POST['action']))
    header("Location: preferences.php?tab=Mail_invoice");
  else
    header("Location: preferences.php?tab=Mail_user");
  die();
 }

//type presta
//echo "<pre/>";
//print_r($_POST);

if ($_GET['action'] == "type_presta_delete") {
  mysql_query("DELETE FROM webfinance_type_presta WHERE id_type_presta=".$_GET['id']);
  $_SESSION['message'] = _('Type presta deleted');
  header("Location: preferences.php?tab=Type_presta");
  die();
}

if($_POST['action'] == "type_presta"){
  foreach ($_POST['cat'] as $id=>$data) {
    if ($id == "new") {
      if ($data['nom'] != "") {
	$q = "INSERT INTO webfinance_type_presta ";
	$f = "(";
	$values = "VALUES(";
	foreach ($data as $n=>$v) {
	  $f .= sprintf("%s,", $n);
	  $values .= sprintf("'%s',", $v);
	}
	$f = preg_replace("!,$!", ") ", $f);
	$values = preg_replace("!,$!", ") ", $values);
	$q .= $f.$values;
      }
    } else {
      $q = "UPDATE webfinance_type_presta SET ";
      foreach ($data as $n=>$v) {
	$q .= sprintf("%s='%s',", $n, $v);
      }
      $q = preg_replace("!,$!", " WHERE id_type_presta=$id", $q);
    }
    //    echo $q; exit;
    mysql_query($q) or wf_mysqldie();
  }

  header("Location: preferences.php?tab=Type_presta");
  die();
 }

?>
