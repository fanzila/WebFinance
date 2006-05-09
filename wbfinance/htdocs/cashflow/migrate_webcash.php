<?php
// $Id$

require("../inc/main.php");
$title = _("Import Webcash");
$roles = 'manager,admin';
require("../top.php");
//require("nav.php");

$db_selected = mysql_select_db('webcash');
if (!$db_selected) {
    die ('Can\'t use WEBCASH DB : ' . mysql_error());
 }else{

?>

<script type="text/javascript">

function ask_confirmation(txt) {
  resultat = confirm(txt);
  if(resultat=="1"){
      return true;
  } else {
      return false;
  }
}
</script>

<form method="post">
<input type="hidden" value="migrate" name="action"/>
<input type="submit" value="migrate Webcash"  onclick="return ask_confirmation('Do you really want to migrate webcash?');"/>
</form>
<br/>
<?

 }


function color_hex($color_string){
  $rgb_array = array(
		     'white'          => array(255, 255, 255),
		     'snow'           => array(255, 250, 250),
		     'PeachPuff'      => array(255, 218, 185),
		     'ivory'          => array(255, 255, 240),
		     'lavender'       => array(230, 230, 250),
		     'black'          => array(  0,   0,   0),
		     'DimGrey'        => array(105, 105, 105),
		     'gray'           => array(190, 190, 190),
		     'grey'           => array(190, 190, 190),
		     'navy'           => array(  0,   0, 128),
		     'SlateBlue'      => array(106,  90, 205),
		     'blue'           => array(  0,   0, 255),
		     'SkyBlue'        => array(135, 206, 235),
		     'cyan'           => array(  0, 255, 255),
		     'DarkGreen'      => array(  0, 100,   0),
		     'green'          => array(  0, 255,   0),
		     'YellowGreen'    => array(154, 205,  50),
		     'yellow'         => array(255, 255,   0),
		     'orange'         => array(255, 165,   0),
		     'gold'           => array(255, 215,   0),
		     'peru'           => array(205, 133,  63),
		     'beige'          => array(245, 245, 220),
		     'wheat'          => array(245, 222, 179),
		     'tan'            => array(210, 180, 140),
		     'brown'          => array(165,  42,  42),
		     'salmon'         => array(250, 128, 114),
		     'red'            => array(255,   0,   0),
		     'pink'           => array(255, 192, 203),
		     'maroon'         => array(176,  48,  96),
		     'magenta'        => array(255,   0, 255),
		     'violet'         => array(238, 130, 238),
		     'plum'           => array(221, 160, 221),
		     'orchid'         => array(218, 112, 214),
		     'purple'         => array(160,  32, 240),
		     'azure1'         => array(240, 255, 255),
		     'aquamarine1'    => array(127, 255, 212)
		     );

  if(array_key_exists($color_string,$rgb_array))
    return $rgb_array[$color_string];
  else
    return array(255,255,255);

}

if(isset($_POST['action']) AND $_POST['action']=="migrate"){

//Import categories
$result = mysql_query("SELECT id, name, comment, color FROM webcash_categories") or die(mysql_error());

$nb_webcash = mysql_num_rows($result);
mysql_select_db('webfinance');
mysql_query("TRUNCATE TABLE webfinance_categories") or die(mysql_error());
while($webcash_categ = mysql_fetch_assoc($result)){
  // print_r($webcash_categ);
  list($r, $g, $b) = color_hex($webcash_categ['color']);
  //  echo $r.":".$g.":".$b."<br/>";
  $color = "#".dechex($r)."".dechex($g)."".dechex($b);
  //echo $color."<br/>";

  $q="INSERT INTO webfinance_categories ".
    "( id , name , comment , re , plan_comptable , color ) ".
    "VALUES ".
    "(%d , '%s', '%s' ,  NULL , NULL , '%s')";
  $query = sprintf($q,
		   $webcash_categ['id'],
		   $webcash_categ['name'],
		   $webcash_categ['comment'],
		   $color);
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
$result = mysql_query("SELECT webcash_banks.id, name, account, phone, mail ".
		      "FROM webcash_banks LEFT JOIN webcash_accounts ON  webcash_banks.id=webcash_accounts.id_bank")
  or die(mysql_error());
$nb_webcash = mysql_num_rows($result);

mysql_select_db('webfinance');
mysql_query("DELETE FROM webfinance_pref WHERE type_pref='rib'") or die(mysql_error());
// mysql_query("TRUNCATE TABLE webfinance_accounts") or die(mysql_error());
// mysql_query("TRUNCATE TABLE webfinance_banks") or die(mysql_error());
while($webcash_banks = mysql_fetch_assoc($result)){
  //  print_r($webcash_banks);
  $rib = new stdClass();
  $rib->id = $webcash_banks['id'];
  $rib->banque = $webcash_banks['name'];
  $rib->domiciliation = "";
  $rib->code_banque = "";
  $rib->code_guichet = "";
  $rib->compte = $webcash_banks['account'];
  $rib->clef = "";
  $rib->iban = "";
  $rib->swift = "";

  $rib = base64_encode(serialize($rib));
  mysql_query("INSERT INTO webfinance_pref (id_pref, type_pref, value) VALUES(".$webcash_banks['id'].", 'rib', '$rib')")
    or die(mysql_error());

//   mysql_query(sprintf("INSERT INTO webfinance_banks SET id=%d, name='%s', short_name='%s', phone='%s', mail='%s', comment='%s'",
// 		      $webcash_banks['id'],
// 		      $webcash_banks['name'],
// 		      $webcash_banks['short_name'],
// 		      $webcash_banks['phone'],
// 		      $webcash_banks['mail'],
// 		      $webcash_banks['comment']))
//     or die(mysql_error());
 }
mysql_free_result($result);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_pref WHERE type_pref='rib'") or die(mysql_error());
list($nb_wf1)=mysql_fetch_array($q);
mysql_free_result($q);

// $q = mysql_query("SELECT COUNT(*) FROM webfinance_banks") or die(mysql_error());
// list($nb_wf2)=mysql_fetch_array($q);
// mysql_free_result($q);

echo "banks importation: ";
if($nb_webcash == $nb_wf1){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";

//import transactions
mysql_select_db('webcash');
$result = mysql_query("SELECT webcash_operations.id, id_categorie, text, amount, webcash_operations.type, document, date, date_update, webcash_operations.comment, file, file_type, file_name, id_bank ".
		      "FROM webcash_accounts LEFT JOIN webcash_operations ON webcash_accounts.id=webcash_operations.id_account  ")
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
		      $webcash_tr['id_bank'], //<- in webfinance, we don't use the webfinance_banks and webfinance_accounts tables
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


//mysql_query("ALTER TABLE webfinance_transactions ADD UNIQUE unique_transaction (id_account, amount, type, date)");

echo "transactions importation: ";
echo $nb_webcash." - ".$nb_wf." ";
if($nb_webcash == $nb_wf){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";

//Import expenses_details
mysql_select_db('webcash');

$result = mysql_query("SELECT COUNT(*) FROM webcash_expense_details") or die(mysql_error());
list($nb_webcash1)= mysql_fetch_array($result);

$result = mysql_query("SELECT * FROM webcash_expenses  LEFT JOIN webcash_expense_details ON  webcash_expenses.id=webcash_expense_details.id_expense")
  or die(mysql_error());
$nb_webcash2 = mysql_num_rows($result);



mysql_select_db('webfinance');
mysql_query("TRUNCATE TABLE webfinance_expenses") or die(mysql_error());


  $q="INSERT INTO webfinance_expenses SET ".
    "id=%d, ".
    "id_user=%d, ".
    "id_transaction=%d, ".
    "amount='%s', ".
    "comment='%s', ".
    "date_update='%s', ".
    "file='%s', ".
    "file_type='%s', ".
    "file_name='%s'";

while($webcash_exp = mysql_fetch_assoc($result)){
  //  print_r($webcash_exp);
  mysql_query(sprintf($q,
		      $webcash_exp['id'],
		      $webcash_exp['id_user'],
		      $webcash_exp['id_transaction'],
		      $webcash_exp['amount'],
		      $webcash_exp['comment'],
		      $webcash_exp['date_update'],
		      $webcash_exp['file'],
		      $webcash_exp['file_type'],
		      $webcash_exp['file_name']))
    or die(mysql_error());
}
mysql_free_result($result);

$q = mysql_query("SELECT COUNT(*) FROM webfinance_expenses ") or die(mysql_error());
list($nb_wf)=mysql_fetch_array($q);

echo "expenses importation: ";
if($nb_webcash1 == $nb_wf AND $nb_webcash2 == $nb_wf){
    echo "OK";
 }else{
  echo "FAILED";
  exit;
 }
echo "<br/>";


 }

$Revision = '$Revision$';
include("../bottom.php");

?>