<?php
// $Id$
?>
<?php
require("../inc/main.php");
$title = _('Cashflow - Paybox');
$roles="manager,accounting,employee";
require("../top.php");
require("nav.php");

$User = new User();
$user = $User->getInfos($_SESSION['id_user']);

if(!isset($User->prefs->lang) or empty($User->prefs->lang)){
  $User->prefs->lang="fr_FR";
 }

setlocale(LC_TIME, $User->prefs->lang);

if(isset($_SESSION['message'])){
  echo $_SESSION['message'];
  $_SESSION['message'] = "";
 }

?>

<table border="0" cellspacing="5" cellpadding="0" >
<tr style="vertical-align: top;">
  <!-- left -->
  <td>
    <table border=0 cellspacing=0 cellpadding=3 style="border: solid 1px black;" width="800">
     <tr align="center" class="row_header">
      <td><?=_('Invoice')?></td>
      <td><?=_('Transaction')?></td>
      <td><?=_('State')?></td>
      <td><?=_('Card')?></td>
      <td>Date</td>
      <td><?=_('Hour')?></td>
      <td>Reference</td>
      <td>Email Porteur</td>
      <td>Autorisation</td>
      <td><?=_('Amount')?></td>
     <tr>
   <?
  $text="";
$state="";
$where_clause = "";

if (isset($_GET['text']) AND $_GET['text']!="") {
  $text=trim($_GET['text']);
  $where_clause .= " AND ( reference RLIKE '$text' ".
    "OR autorisation RLIKE '$text' ".
    "OR amount RLIKE '$text' ".
    "OR email RLIKE '$text' ) ";
}

if (isset($_GET['state']) AND $_GET['state']!="") {
  $state=$_GET['state'];
  $where_clause .= " AND state='$state' ";
}

$where_clause = preg_replace('/^\ AND/',' WHERE ',$where_clause);

$q = "SELECT id_paybox, id_invoice, email, reference, state, amount, currency , ".
  "autorisation, transaction_id as trans, payment_type, card_type, transaction_sole_id, error_code, date, UNIX_TIMESTAMP(date) as ts_date  ".
  "FROM webfinance_paybox $where_clause ORDER BY date DESC";

//echo $q;
$trs = mysql_query($q) or wf_mysqldie();

$count=1;
$Invoice = new Facture();

while($tr = mysql_fetch_object($trs)){
  $class = ($count%2)?"row_odd":"row_even";

  //invoice description
  $facture = $Invoice->getInfos($tr->id_invoice);

  list($currency,$exchange)=getCurrency($facture->id_compte);

  // Récupération du texte des lignes facturées pour afficher en infobulle.
  $description = "<b>".strftime('%x',$facture->timestamp_date_facture)."</b><br/>";
  foreach ($facture->lignes as $l) {
    $l->description = preg_replace("/\r\n/", " ", $l->description);
      $l->description = preg_replace("/\"/", "", $l->description);
      $l->description = preg_replace("/\'/", "", $l->description);
      $description .= $l->description."<br/>";
  }

  //s�parer les mois
  $current_month=ucfirst(strftime("%B %Y",$tr->ts_date));
  if(!empty($prev_date)){
    if(date("m",$prev_date)!=date("m",$tr->ts_date))
      echo "<tr class=\"row_even\"><td colspan='8' align='center'><b>$current_month</b></td></tr>";
  }else{
    echo "<tr class=\"row_even\"><td colspan='8' align='center'><b>$current_month</b></td></tr>";
	 $cur_date=$tr->ts_date;
  }

  $prev_date=$tr->ts_date;

   ?>
     <tr onmouseover="return escape('<?=$description?>');" align="center" class="<?=$class?>">
      <td ><?
	printf('<a href="../prospection/edit_facture.php?id_facture=%d" >#%s</a>', $tr->id_invoice ,$facture->num_facture);
        ?>
      </td>
      <td><?=$tr->trans?></td>
      <td><?=$tr->state?></td>
	  <td><?=$tr->payment_type?>&nbsp;<?=$tr->card_type?></td>
      <td><?=strftime('%x',$tr->ts_date)?></td>
      <td><?=strftime('%R',$tr->ts_date)?></td>
      <td><?=$tr->reference?></td>
      <td><?=str_replace(',','<br/>',$tr->email)?></td>
      <td><?=$tr->trans?></td>
	<td><?=format_price($tr->amount)?></td>
     <tr>
   <?
	$count++;
 }
    ?>
   </table>
 </td>
  <!-- right -->
 <td>
<form method="get">
  <table border="0" cellspacing="0" cellpadding="3" style="border: solid 1px black;" width="200">
    <tr align="center" class="row_header">
       <td colspan="2"><?= _('Filter')?></td>
    </tr>
    <tr>
  <td><?=_('State')?></td>
  <td>
  <select style="width: 150px;" name="state" onchange="this.form.submit();" >
   <option value="" ><?=_('All')?></option>
   <option value="ok" <?=($state=="ok")?"selected":"" ?>><?=_('Ok')?></option>
   <option value="deny" <?=($state=="deny")?"selected":"" ?>><?=_('Deny')?></option>
   <option value="cancel" <?=($state=="cancel")?"selected":"" ?>><?=_('Cancel')?></option>
   <option value="pending" <?=($state=="pending")?"selected":"" ?>><?=_('Pending')?></option>
   <option value="nok" <?=($state=="nok")?"selected":"" ?>><?=_('Nok')?></option>
  </select>
  </td>
    </tr>
    <tr>
       <td>Text</td>
  <td>
  <input style="text-align: center; width: 150px;" type="text" name="text" value="<?=$text?>" />
  </td>
    </tr>
  <tr>
    <td colspan="2" style="text-align: center;" >
     <input type="submit" value="<?=_('Search')?>" />
    </td>
  </tr>
  </table>
</form>
 </td>
</tr>
</table>

<?php
$Revision = '$Revision$';
include("../bottom.php");
?>
