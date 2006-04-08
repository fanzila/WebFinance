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
  $_SESSION['message'] = "Catégorie supprimée";
  header("Location: categories.php");
}

foreach ($_POST['cat'] as $id=>$data) {
  $q = "UPDATE webfinance_categories SET ";
  foreach ($data as $n=>$v) {
    $q .= sprintf("%s='%s',", $n, $v);
  }
  $q = preg_replace("!,$!", "WHERE id=$id", $q);
  mysql_query($q) or die(mysql_error());
}

header("Location: categories.php");


?>
