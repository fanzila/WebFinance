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

if ($_GET['action'] == "delete") {
  mysql_query("DELETE FROM webfinance_categories WHERE id=".$_GET['id']);
  if(mysql_affected_rows()==1)
    $_SESSION['message'] = _('Category deleted');
  else{
    $_SESSION['message'] = _('Category doesn\'t exist');
    $_SESSION['error'] = 1;
  }
  header("Location: categories.php?sort=".$_GET['sort']);
  exit;
}
//echo "<pre/>";
//print_r($_POST);

foreach ($_POST['cat'] as $id=>$data) {
  if ($id == "new") {
    if ($data['name'] != "") {
      $max_id = mysql_query("SELECT MAX(id) FROM webfinance_categories") or wf_mysqldie();
      list($max_id) = mysql_fetch_array($max_id);
      $q = sprintf("INSERT INTO webfinance_categories (id, name,re,comment,plan_comptable) ".
		  "VALUES (%d, '%s','%s','%s','%s')",
		   $max_id+1, $data['name'],$data['re'],$data['comment'],$data['plan_comptable']);
    }
  } else {
    $q = "UPDATE webfinance_categories SET ";
    foreach ($data as $n=>$v) {
      $q .= sprintf("%s='%s',", $n, $v);
    }
    $q = preg_replace("!,$!", " WHERE id=$id", $q);
  }
  echo $q."<br/>";
  mysql_query($q) or wf_mysqldie();
  $_SESSION['message'] = _('Categories updated');
}

header("Location: categories.php?sort=".$_POST['sort']);


?>
