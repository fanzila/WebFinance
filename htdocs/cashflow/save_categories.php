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

// $Id: save_categories.php 531 2007-06-13 12:32:31Z thierry $

require("../inc/main.php");
must_login();

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
  mysql_query($q) or wf_mysqldie();
  $_SESSION['message'] = _('Categories updated');
}

header("Location: categories.php?sort=".$_POST['sort']);


?>
