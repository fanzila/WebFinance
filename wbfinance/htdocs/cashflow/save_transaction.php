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

if(isset($_GET) && array_key_exists('action' , $_GET) ){

    switch ($_GET['action']){
      case 'file':
	$file = new File();
	$file->getFile($_GET['id_file']);
	break;
    }
    die();
  }

extract($_POST);

if (is_array($_POST['action'])) {
  $File = new File();
  foreach (explode(',', $selected_transactions) as $id_transaction) {
    $q = "";
    switch ($action['type']) {
      case "delete":
	$q = "DELETE FROM webfinance_transactions WHERE id=$id_transaction";
	$File->deleteAllFiles($id_transaction,'transaction');
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
  $back=$GLOBALS['_SERVER']['HTTP_REFERER'];
  header("Location: $back?".$query);
  die();
 }

if($_POST['action']=="update_invoices" ){

  if(is_array($_POST['invoices'])){
    foreach($_POST['invoices'] as $id_invoice){
      mysql_query("UPDATE webfinance_invoices SET is_paye=1,date_paiement=STR_TO_DATE('".$date_tr[$id_invoice]."', '%d/%m/%Y') WHERE id_facture=$id_invoice")
	or wf_mysqldie();
      mysql_query("UPDATE webfinance_transactions SET id_invoice=$id_invoice WHERE id=".$id_tr[$id_invoice]) or wf_mysqldie();
    }
    $_SESSION['message']= _('Invoice(s) and transaction(s) updated');
  }

  header("Location: ./");
  die();
}

$amount = preg_replace("!,!", ".", $amount);
$amount = preg_replace("! +!", "", $amount);

if(empty($date))
  $date=date('d/m/Y');

if($id_transaction>0){

  $q = sprintf("UPDATE webfinance_transactions SET ".
	       "id_category=%d, ".
	       "id_account=%d, ".
	       "id_invoice=%d, ".
	       "text='%s', ".
	       "amount='%s', ".
	       "exchange_rate='%s', ".
	       "type='%s', ".
	       "date=str_to_date('%s', '%%d/%%m/%%Y'), ".
	       "comment='%s' ".
	       "WHERE id=%d",
	       $id_category, $id_account, $id_invoice, $text, $amount, $exchange_rate, $type, $date, $comment, $id_transaction);
  mysql_query($q) or wf_mysqldie();

 }else{
  $q = sprintf("INSERT INTO webfinance_transactions SET ".
	       "id_category=%d, ".
	       "id_account=%d, ".
	       "text='%s', ".
	       "amount=%s, ".
	       "exchange_rate='%s', ".
	       "type='%s', ".
	       "date=str_to_date('%s', '%%d/%%m/%%Y'), ".
	       "comment='%s' ",
	       $id_category, $id_account, $text, $amount, $exchange_rate, $type, $date, $comment);

  mysql_query($q) or wf_mysqldie();
  $id_transaction = mysql_insert_id();

 }

$File = new File();

if(isset($file_del)){
  $files = $File->getFiles($id_transaction, 'transaction');
  foreach($files as $file){
    if(!array_key_exists($file->id_file , $file_del)){
      $File->deleteFile($file->id_file);
    }
  }
 }else{
  $File->deleteAllFiles($id_transaction, 'transaction');
 }

if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
  $File->addFile($_FILES['file'], $id_transaction ,'transaction');
 }

?>
<script>
popup = window.parent.document.getElementById('inpage_popup');
popup.style.display = 'none';
// Reload parent window to update contacts
filter = window.parent.document.getElementById('main_form');
filter.submit();
</script>
