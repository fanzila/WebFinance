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
if (is_array($_POST['action'])) {
  foreach (explode(',', $selected_transactions) as $id_transaction) {
    $q = "";
    switch ($action['type']) {
      case "delete":
	$q = "DELETE FROM webfinance_transactions WHERE id=$id_transaction";
	break;
      case "change_account" :
	$q = "UPDATE webfinance_transactions SET id_account=".$action['id_account']." WHERE id=$id_transaction";
	break;
      case "change_category" :
	$q = "UPDATE webfinance_transactions SET id_category=".$action['id_category']." WHERE id=$id_transaction";
	break;
      default: die('Woooops, don\'t know how to '.$action['type']);
    }
    mysql_query($q) or wf_mysqldie();
  }
  $back=$GLOBALS['_SERVER']['HTTP_REFERER'];
  header("Location: $back?".$query);
  die();
}

//echo "<pre/>";print_r($_POST);

if($_POST['action']=="update_transactions" AND is_array($_POST['categ'])){

  $types=$_POST['type'];
  foreach($_POST['categ'] as $id_tr=>$id_category){
    mysql_query("UPDATE webfinance_transactions SET id_category=$id_category,type='".$type[$id_tr]."' WHERE id=$id_tr")
      or wf_mysqldie();
  }
  header("Location: edit_transactions.php?".$query);
  die();
 }

if($_POST['action']=="update_invoices" AND is_array($_POST['invoices'])){

  foreach($_POST['invoices'] as $id_invoice){
    mysql_query("UPDATE webfinance_invoices SET is_paye=1,date_paiement=STR_TO_DATE('".$date_tr[$id_invoice]."', '%d/%m/%Y') WHERE id_facture=$id_invoice")
      or wf_mysqldie();
  }
  header("Location: index.php");
  die();
 }



$fq="";
if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
  $file_type=addslashes($_FILES['file']['type']);
  $file_name=addslashes($_FILES['file']['name']);
  $file_blob = file_get_contents($_FILES['file']['tmp_name']);
  $file=addslashes($file_blob);
  $fq=sprintf("file='%s' , file_name='%s' , file_type='%s' , ",$file,$file_name,$file_type );
 }else if(!isset($file_del) OR $file_del!=1)
  $fq=sprintf("file='' , file_name='' , file_type='' , ");


$amount = preg_replace("!,!", ".", $amount);
$amount = preg_replace("! +!", "", $amount);
if($id_transaction>0){
  $q = sprintf("UPDATE webfinance_transactions SET ".
	       "%s".
	       "id_category=%d, ".
	       "id_account=%d, ".
	       "id_invoice=%d, ".
	       "text='%s', ".
	       "amount='%s', ".
	       "type='%s', ".
	       "date=str_to_date('%s', '%%d/%%m/%%Y'), ".
	       "comment='%s' ".
	       "WHERE id=%d",
	       $fq, $id_category, $id_account, $id_invoice, $text, $amount, $type, $date, $comment, $id_transaction);
 }else{
  $q = sprintf("INSERT INTO webfinance_transactions SET ".
	       "%s".
	       "id_category=%d, ".
	       "id_account=%d, ".
	       "text='%s', ".
	       "amount=%s, ".
	       "type='%s', ".
	       "date=str_to_date('%s', '%%d/%%m/%%Y'), ".
	       "comment='%s' ",
	       $fq, $id_category, $id_account, $text, $amount, $type, $date, $comment);
 }

mysql_query($q) or wf_mysqldie();

?>
<script>
popup = window.parent.document.getElementById('inpage_popup');
popup.style.display = 'none';
// Reload parent window to update contacts
filter = window.parent.document.getElementById('main_form');
filter.submit();
</script>
