<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//

// $Id$

require("../inc/main.php");
must_login();

if ($_GET['action'] == "delete") {
  mysql_query("DELETE FROM webfinance_roles WHERE id_role=".$_GET['id']);
  $_SESSION['message'] = _('Role deleted');
  header("Location: preferences.php?tab=Role");
}

foreach ($_POST['cat'] as $id=>$data) {
  if ($id == "new") {
    if ($data['name'] != "") {
      $q = "INSERT INTO webfinance_roles ";
      $f = "(";
      $values = "VALUES(";
      foreach ($data as $n=>$v) {
        $f .= sprintf("%s,", $n);
        $values .= sprintf("'%s',", $v);
      }
      $f = preg_replace("!,$!", ") ", $f);
      $values = preg_replace("!,$!", ") ", $values);
      $q .= $f.$values;
      $_SESSION['message'] = _('Role added');
    }
  } else {
    $q = "UPDATE webfinance_roles SET ";
    foreach ($data as $n=>$v) {
      $q .= sprintf("%s='%s',", $n, $v);
    }
    $q = preg_replace("!,$!", " WHERE id_role=$id", $q);
    $_SESSION['message'] = _('Role updated');
  }
  //  echo $q;
  mysql_query($q) or wf_mysqldie();
}

header("Location: preferences.php?tab=Role");


?>
