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

extract($_POST);

$fq="";

if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
  $file_type=addslashes($_FILES['file']['type']);
  $file_name=addslashes($_FILES['file']['name']);
  $file_blob = file_get_contents($_FILES['file']['tmp_name']);
  $file=addslashes($file_blob);
  $fq=sprintf("file='%s' , file_name='%s' , file_type='%s' , ",$file,$file_name,$file_type );
 }else if(!isset($file_del) OR $file_del!=1)
  $fq=sprintf("file='' , file_name='' , file_type='' , ");


$q = sprintf("UPDATE webfinance_transactions SET ".
	     "%s".
	     "id_category=%d, ".
	     "text='%s', ".
	     "amount=%s, ".
	     "type='%s', ".
	     "date='%s', ".
	     "comment='%s' ".
	     "WHERE id=%d",
             $fq, $id_category, $text, $amount, $type, $date, $comment, $id_transaction);
mysql_query($q) or die(mysql_error());

?>
<script>
popup = window.parent.document.getElementById('inpage_popup');
popup.style.display = 'none';
// Reload parent window to update contacts
</script>
