<?php
// $Id$

require("../inc/main.php");
$title = _("Import Webcash");
$roles = 'manager,admin';
require("../top.php");
//require("nav.php");

$db_selected = mysql_select_db('webcash');
if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
}
echo "<pre/>";

//Import categories
$result = mysql_query("SELECT id, name, comment, color FROM webcash_categories") or die(mysql_error());

$nb_webcash = mysql_num_rows($result);
mysql_select_db('webfinance');
mysql_query("TRUNCATE TABLE webfinance_categories") or die(mysql_error());
while($webcash_categ = mysql_fetch_assoc($result)){
  // print_r($webcash_categ);
  $q="INSERT INTO webfinance_categories ".
    "( id , name , comment , re , plan_comptable , color ) ".
    "VALUES ".
    "(%d , '%s', '%s' ,  NULL , NULL , '%s')";
  $query = sprintf($q,$webcash_categ['id'], $webcash_categ['name'], $webcash_categ['comment'], $webcash_categ['color']);
  mysql_query($query) or die(mysql_error());
 }
mysql_free_result($result);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_categories") or die(mysql_error());
list($nb_wf)=mysql_fetch_array($q);
mysql_free_result($q);

echo "categories importation: ";
if($nb_webcash == $nb_wf){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";

//Import banks
mysql_select_db('webcash');
$result = mysql_query("SELECT id, name, short_name, phone, mail, comment FROM webcash_banks")
  or die(mysql_error());
$nb_webcash = mysql_num_rows($result);

mysql_select_db('webfinance');
mysql_query("DELETE FROM webfinance_pref WHERE type_pref='rib'") or die(mysql_error());
mysql_query("TRUNCATE TABLE webfinance_accounts") or die(mysql_error());
mysql_query("TRUNCATE TABLE webfinance_banks") or die(mysql_error());
while($webcash_banks = mysql_fetch_assoc($result)){
  //  print_r($webcash_banks);
  $rib = new stdClass();
  $rib->banque = $webcash_banks['name'];
  $rib->domiciliation = "";
  $rib->code_banque = "";
  $rib->code_guichet = "";
  $rib->compte = $webcash_banks['id'];
  $rib->clef = "";
  $rib->iban = "";
  $rib->swift = "";

  $rib = base64_encode(serialize($rib));
  mysql_query("INSERT INTO webfinance_pref (type_pref, value) VALUES('rib', '$rib')")
    or die(mysql_error());

  mysql_query(sprintf("INSERT INTO webfinance_banks SET id=%d, name='%s', short_name='%s', phone='%s', mail='%s', comment='%s'",
		      $webcash_banks['id'],
		      $webcash_banks['name'],
		      $webcash_banks['short_name'],
		      $webcash_banks['phone'],
		      $webcash_banks['mail'],
		      $webcash_banks['comment']))
    or die(mysql_error());
 }
mysql_free_result($result);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_pref WHERE type_pref='rib'") or die(mysql_error());
list($nb_wf1)=mysql_fetch_array($q);
mysql_free_result($q);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_banks") or die(mysql_error());
list($nb_wf2)=mysql_fetch_array($q);
mysql_free_result($q);

echo "banks importation: ";
if($nb_webcash == $nb_wf1 AND $nb_webcash == $nb_wf2){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";


//Import account
mysql_select_db('webcash');
$result = mysql_query("SELECT id, account_name, id_bank, id_user, account, comment, currency, country, type FROM webcash_accounts") or die(mysql_error());
$nb_webcash = mysql_num_rows($result);
mysql_select_db('webfinance');
mysql_query("TRUNCATE TABLE webfinance_accounts") or die(mysql_error());
  $q="INSERT INTO webfinance_accounts SET ".
    "id=%d, ".
    "account_name='%s', ".
    "id_bank=%d, ".
    "id_user=%d, ".
    "account='%s', ".
    "comment='%s', ".
    "currency='%s', ".
    "country='%s', ".
    "type='%s'";

while($webcash_acc = mysql_fetch_assoc($result)){
  // print_r($webcash_acc);
  $query = sprintf($q,
		   $webcash_acc['id'],
		   $webcash_acc['account_name'],
		   $webcash_acc['id_bank'],
		   $webcash_acc['id_user'],
		   $webcash_acc['account'],
		   $webcash_acc['comment'],
		   $webcash_acc['currency'],
		   $webcash_acc['country'],
		   $webcash_acc['type']);
  mysql_query($query) or die(mysql_error());
 }
mysql_free_result($result);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_accounts") or die(mysql_error());
list($nb_wf)=mysql_fetch_array($q);
mysql_free_result($q);

echo "accounts importation: ";
if($nb_webcash == $nb_wf){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";



//import transactions
mysql_select_db('webcash');
$result = mysql_query("SELECT id, id_account, id_categorie, text, amount, type, document, date, date_update, comment, file, file_type, file_name ".
		      "FROM webcash_operations")
  or die(mysql_error());
$nb_webcash = mysql_num_rows($result);

mysql_select_db('webfinance');
mysql_query("TRUNCATE TABLE webfinance_transactions") or die(mysql_error());
//mysql_query("ALTER TABLE webfinance_transactions DROP INDEX unique_transaction");
  $q="INSERT INTO webfinance_transactions SET ".
    "id=%d, ".
    "id_account=%d, ".
    "id_category=%d, ".
    "text='%s', ".
    "amount=%s, ".
    "type='%s', ".
    "document='%s', ".
    "date='%s', ".
    "date_update='%s', ".
    "comment='%s', ".
    "file='%s', ".
    "file_type='%s', ".
    "file_name='%s' ";

while($webcash_tr = mysql_fetch_assoc($result)){
  //  print_r($webcash_tr);
  if($webcash_tr['id_category']<1)
    $webcash_tr['id_category']=1;
  mysql_query(sprintf($q,
		      $webcash_tr['id'],
		      $webcash_tr['id_account'],
		      $webcash_tr['id_categorie'],
		      addslashes($webcash_tr['text']),
		      $webcash_tr['amount'],
		      $webcash_tr['type'],
		      $webcash_tr['document'],
		      $webcash_tr['date'],
		      $webcash_tr['date_update'],
		      $webcash_tr['comment'],
		      addslashes($webcash_tr['file']),
		      $webcash_tr['file_type'],
		      $webcash_tr['file_name']))
    or die(mysql_error());
}
mysql_free_result($result);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_transactions ") or die(mysql_error());
list($nb_wf)=mysql_fetch_array($q);
echo $nb_webcash." - ".$nb_wf."<br/>";

//mysql_query("ALTER TABLE webfinance_transactions ADD UNIQUE unique_transaction (id_account, amount, type, date)");

echo "transactions importation: ";
if($nb_webcash == $nb_wf){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";

//Import expenses
mysql_select_db('webcash');

$result = mysql_query("SELECT ".
		      "id, ".
		      "date, ".
		      "id_user, ".
		      "id_operation, ".
		      "comment, ".
		      "date_update ".
		      "FROM webcash_expenses")
  or die(mysql_error());
$nb_webcash = mysql_num_rows($result);

mysql_select_db('webfinance');
mysql_query("TRUNCATE TABLE webfinance_expense_details") or die(mysql_error());
mysql_query("TRUNCATE TABLE webfinance_expenses") or die(mysql_error());

  $q="INSERT INTO webfinance_expenses SET ".
    "id=%d, ".
    "date='%s', ".
    "id_user=%d, ".
    "id_transaction=%d, ".
    "comment='%s', ".
    "date_update='%s'";

while($webcash_exp = mysql_fetch_assoc($result)){
  //  print_r($webcash_exp);
  mysql_query(sprintf($q,
		      $webcash_exp['id'],
		      $webcash_exp['date'],
		      $webcash_exp['id_user'],
		      $webcash_exp['id_operation'],
		      $webcash_exp['comment'],
		      $webcash_exp['date_update']))
    or die(mysql_error());
}
mysql_free_result($result);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_expenses ") or die(mysql_error());
list($nb_wf)=mysql_fetch_array($q);

echo "expenses importation: ";
if($nb_webcash == $nb_wf){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";

//Import expenses_details
mysql_select_db('webcash');

$result = mysql_query("SELECT ".
		      "id, ".
		      "id_expense, ".
		      "comment, ".
		      "amount, ".
		      "file, ".
		      "file_type, ".
		      "file_name ".
		      "FROM webcash_expense_details")
  or die(mysql_error());
$nb_webcash = mysql_num_rows($result);

mysql_select_db('webfinance');

  $q="INSERT INTO webfinance_expenses SET ".
    "id=%d, ".
    "id_expense=%d, ".
    "comment='%s', ".
    "amount='%s', ".
    "file='%s', ".
    "file_type='%s', ".
    "file_name='%s'";

while($webcash_exp = mysql_fetch_assoc($result)){
  //  print_r($webcash_exp);
  mysql_query(sprintf($q,
		      $webcash_exp['id'],
		      $webcash_exp['id_expense'],
		      $webcash_exp['comment'],
		      $webcash_exp['amount'],
		      $webcash_exp['file'],
		      $webcash_exp['file_type'],
		      $webcash_exp['file_name']))
    or die(mysql_error());
}
mysql_free_result($result);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_expense_details ") or die(mysql_error());
list($nb_wf)=mysql_fetch_array($q);

echo "expenses details importation: ";
if($nb_webcash == $nb_wf){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";




$Revision = '$Revision$';
include("../bottom.php");

?>