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

function date_params($_GET){
  $filter['end_date']="";
  $filter['start_date']="";

  $ts_start_date="";
  $ts_end_date="";

  extract($_GET);

  // If no date range is specified use "current month"
  $days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
  if ((strftime("%Y") % 4 == 0) && (strftime("%Y") % 100 != 0)) { // Leap year
    $days_in_month[1] = 29;
  }

  if (isset($filter) && ($filter['start_date'] != "") && ($filter['end_date'] == "")) {
    // If start_date is given and NOT end_date then we show transaction between
    // start_date and current date.
    $filter['end_date'] = strftime("%d/%m/%Y");
  }
  if (isset($filter) && ($filter['start_date'] == "") && ($filter['end_date'] != "")) {
    // If end_date is given and NOT start_date then we show transaction from the
    // start of the company to end_date
    $result = WFO::SQL("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1");
    list($value) = mysql_fetch_array($result);
    mysql_free_result($result);
    $company = unserialize(base64_decode($value));

    $filter['start_date'] = $company->date_creation;
  }
  if (isset($filter) && $filter['start_date'] == "")
    $filter['start_date'] = strftime("01/%m/%Y");

  if ($filter['end_date'] == "")
    $filter['end_date'] = strftime($days_in_month[strftime("%m")-1]."/%m/%Y");

  preg_match( "!([0-9]{2})/([0-9]{2})/([0-9]{4})!", $filter['start_date'],$foo);
  $ts_start_date = mktime( 0,0,0, $foo['2'], $foo['1'], $foo['3']);
  preg_match( "!([0-9]{2})/([0-9]{2})/([0-9]{4})!", $filter['end_date'],$foo);
  $ts_end_date = mktime( 0,0,0, $foo['2'], $foo['1'], $foo['3']);

  // end date must be after begin date. If not reverse them
  if ($ts_start_date > $ts_end_date) {
    // Reverse : switch timestamps and formated dates
    $foo = $filter['start_date'];
    $filter['start_date'] = $filter['end_date'];
    $filter['end_date'] = $foo;

    $foo = $ts_start_date;
    $ts_start_date = $ts_end_date;
    $ts_end_date = $foo;
  }
  return array($ts_start_date,$ts_end_date,$filter['start_date'],$filter['end_date']);
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
  $ts_start_date="";
  $ts_end_date="";

$text="";
$state="";
$where_clause = "";

$ok="";
$nok="";
$pending="";
$deny="";
$cancel="";

list($ts_start_date, $ts_end_date, $filter['start_date'],$filter['end_date']) = date_params($_GET);

// Filter on dates
if (($ts_start_date != 0) && ($ts_end_date != 0)) {
  $where_clause .= " AND (unix_timestamp(date)>=$ts_start_date AND unix_timestamp(date)<=$ts_end_date) ";
 }


if(isset($_GET['f']['chk'])){
  $chk=$_GET['f']['chk'];
  $where_clause .= " AND (";
  foreach($chk as $st=>$v){
    $$st=$v;
    $where_clause .= " state='$st' OR";
  }
  $where_clause = preg_replace('/OR$/',') ',$where_clause);

 }else{
  $ok="on";
  $nok="on";
  $pending="on";
  $deny="on";
  $cancel="on";
 }

if (isset($_GET['text']) AND $_GET['text']!="") {
  $text=trim($_GET['text']);
  $where_clause .= " AND ( reference RLIKE '$text' ".
    "OR autorisation RLIKE '$text' ".
    "OR amount RLIKE '$text' ".
    "OR email RLIKE '$text' ) ";
}

// if (isset($_GET['state']) AND $_GET['state']!="") {
//   $state=$_GET['state'];
//   $where_clause .= " AND state='$state' ";
// }

$where_clause = preg_replace('/^\ AND/',' WHERE ',$where_clause);

$q = "SELECT id_paybox, id_invoice, email, reference, state, amount, currency , ".
  "autorisation, transaction_id as trans, payment_type, card_type, transaction_sole_id, error_code, date, UNIX_TIMESTAMP(date) as ts_date  ".
  "FROM webfinance_paybox $where_clause ORDER BY date DESC";


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
  $current_month = utf8_encode($current_month);
  if(!empty($prev_date)){
    if(date("m",$prev_date)!=date("m",$tr->ts_date))
      echo "<tr class=\"row_even\"><td colspan='8' align='center'><b>".$current_month."</b></td></tr>";
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
<form method="get" onchange="this.submit();">
  <table border="0" cellspacing="0" cellpadding="3" style="border: solid 1px black;" width="250">
    <tr align="center" class="row_header">
       <td colspan="2"><?= _('Filter')?></td>
    </tr>
<tr>
  <td nowrap><b><?= _('Start date :') ?></b></td>
  <td><?php makeDateField("filter[start_date]", $ts_start_date, 1, 'start_date_criteria', 'width: 114px'); ?>
   <img src="/imgs/icons/delete.gif"
  onmouseover="return escape('<?= _('Click to suppress this filter criteria') ?>');"
  onclick="fld = document.getElementById('start_date_criteria'); fld.value = ''; fld.form.submit();" />
  </td>
</tr>
  <tr class="row_even">
  <td nowrap><b><?= _('End date :') ?></b></td>
  <td><?php makeDateField("filter[end_date]", $ts_end_date, 1, 'end_date_criteria', 'width: 114px'); ?>
   <img src="/imgs/icons/delete.gif"
  onmouseover="return escape('<?= _('Click to suppress this filter criteria') ?>');"
  onclick="fld = document.getElementById('end_date_criteria'); fld.value = ''; fld.form.submit();" />
  </td>
  </tr>

  <tr>
  <td colspan="2">
  <?
  printf('<input type="checkbox" name="f[chk][ok]" %s /><b>%s</b>&nbsp;', ($ok)?"checked":"" ,_('ok'));
  printf('<input type="checkbox" name="f[chk][pending]" %s /><b>%s</b>&nbsp;', ($pending)?"checked":"" ,_('pending'));
  printf('<input type="checkbox" name="f[chk][cancel]" %s /><b>%s</b>&nbsp;', ($cancel)?"checked":"" ,_('cancel'));
  printf('<input type="checkbox" name="f[chk][deny]" %s /><b>%s</b>&nbsp;', ($deny)?"checked":"" ,_('deny'));
  printf('<input type="checkbox" name="f[chk][nok]" %s /><b>%s</b>', ($nok)?"checked":"" ,_('nok'));
?>
  </td>
 </tr>
    <tr>
  <td><b><?=_('Text')?></b></td>
  <td>
  <input style="text-align: center; width: 180px;" type="text" name="text" value="<?=$text?>" />
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

    <?=(WF_DEBUG)?$q:""?>

<?php
$Revision = '$Revision$';
include("../bottom.php");
?>
