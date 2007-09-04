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

// $Id: save_expenses.php 531 2007-06-13 12:32:31Z thierry $

require("../inc/main.php");
must_login();

//echo "<pre/>";
//print_r($_POST);
//print_r($_GET);
//print_r($_FILES);

if (isset($_GET['action']) AND $_GET['action'] == "delete") {
  mysql_query("DELETE FROM webfinance_expenses WHERE id=".$_GET['id']);
  $_SESSION['message'] = _('Expense deleted');
  header("Location: expenses.php?id_transaction=".$_GET['id_transaction']);
}


if(isset($_POST['exp'])){

  foreach ($_POST['exp'] as $id=>$data) {
    $q="";
    if ($id == "new") {
      if ($data['comment'] != "") {
	$q = "INSERT INTO webfinance_expenses ";
	$f = "(";
      $values = "VALUES(";
      foreach ($data as $n=>$v) {
        $f .= sprintf("%s,", $n);
        $values .= sprintf("'%s',", $v);
      }
      $f .= "id_transaction,";
      $values .= $_POST['id_transaction'].",";

      if(is_uploaded_file($_FILES['exp']['tmp_name']['new']['file'])) {
	$file_name=addslashes($_FILES['exp']['name']['new']['file']);
	$file_type=addslashes($_FILES['exp']['type']['new']['file']);
	$file_blob = file_get_contents($_FILES['exp']['tmp_name']['new']['file']);
	$file=addslashes($file_blob);

	//	$file = addslashes(file_get_contents($_FILES['exp']['tmp_name']['new']['file']));
	$f .= "file, file_name, file_type,";
	$values .= sprintf("'%s', '%s', '%s' ,",$file,$file_name,$file_type );
      }

      $f = preg_replace("!,$!", ") ", $f);
      $values = preg_replace("!,$!", ") ", $values);
      $q .= $f.$values;
      }
    } else {
    $q = "UPDATE webfinance_expenses SET ";
    foreach ($data as $n=>$v) {
      $q .= sprintf("%s='%s',", $n, $v);
    }
    $q .= "id_transaction=".$_POST['id_transaction'].",";

      if(is_uploaded_file($_FILES['exp']['tmp_name'][$id]['file'])) {
	$file_name=addslashes($_FILES['exp']['name'][$id]['file']);
	$file_type=addslashes($_FILES['exp']['type'][$id]['file']);
	$file_blob = file_get_contents($_FILES['exp']['tmp_name'][$id]['file']);
	$file=addslashes($file_blob);
 	$q .= sprintf("file='%s' , file_name='%s', file_type='%s' ,",$file,$file_name,$file_type );
       }else if(!isset($_POST['file_del'][$id]) OR $_POST['file_del'][$id]!=1 ){
 	$q .= "file='' , file_name='', file_type='',";
       }

    $q = preg_replace("!,$!", " WHERE id=$id", $q);
    }
    //echo $q;
    if($q != "")
      mysql_query($q) or die(mysql_error());

  }

header("Location: expenses.php?id_transaction=".$_POST['id_transaction']);

 }


?>
