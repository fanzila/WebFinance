<?php
include("../../inc/main.php");
$title = _("Paybox");
$roles="manager,accounting,employee,client";
include("../../top.php");

//echo "<pre/>";
//print_r($_GET);

$Invoice = new Facture();

if(isset($_GET['ref'])){
  mysql_query("UPDATE webfinance_paybox SET state='cancel' WHERE reference='".$_GET['ref']."'") or wf_mysqldie();
  $_SESSION['message'] = _("The transaction is canceled");
  header("Location: ../../client/");

?>
  <span class="text">
    <? echo _("The transaction is canceled"); ?>
  </span>
<?
 }else{
?>
  <span class="text">
    <? echo _("Wrong arguments"); ?>
  </span>
<?
 }
?>
<br/>
<a href="../../client/"><?=_('Back')?></a>

<?php
$Revision = '$Revision$';
include("../../bottom.php");
?>
