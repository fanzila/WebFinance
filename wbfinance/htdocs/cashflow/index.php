<?php
//
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php

// $Id$

require("../inc/main.php");
$title = _('Cashflow');
$roles="manager,accounting";
require("../top.php");
require("nav.php");

extract($_GET);

$Invoice = new Facture();
$User = new User();

$user = $User->getInfos($_SESSION['id_user']);

// Number of transaction to show on one page
if(is_numeric($User->prefs->transactions_per_page) and $User->prefs->transactions_per_page>0 )
  $transactions_per_page = $User->prefs->transactions_per_page;
else
  $transactions_per_page = 50;

$view="";
if(isset($_GET['view'])){
  $view=$_GET['view'];
 }

$page=0;
if(isset($filter,$filter['page'])){
  $page=$filter['page'];
 }

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

function check(){
  if(!document.form.chk.checked){
    alert("Choose a transaction");
    return false;
  }else{
    return true;
  }
}

var specific_shown = null;

function updateCheck(id) {
  checkbox = document.getElementById('chk_'+id);
  f_action = document.getElementById('action_form');
  sel = f_action.selected_transactions.value;
  var regexp = ','+id+','; // This produces an INTENDED double coma !
  sel = sel.replace(regexp, '')
  if (checkbox.checked) {
    sel += ','+id+',';
  }
  f_action.selected_transactions.value = sel;
}
function changeAction(sel) {
  if (specific_shown)
    specific_shown.style.display = 'none';
  specific_options = document.getElementById( 'action_' + sel.options[sel.selectedIndex].value );
  if (specific_options) {
    specific_options.style.display = 'block';
    specific_shown = specific_options;
  }
}

function submitAction(f) {

  f.selected_transactions.value = '';
  check_form = document.getElementById('checkboxes');
  for (i=0 ; i<check_form.elements.length ; i++) {
    el = check_form.elements[i];

    if (m = el.id.match(/chk_(.*)/)) { // is a checkbox
      if (el.checked) { // is checked
        f.selected_transactions.value = f.selected_transactions.value + m[1]+',';
      }
    }
  }
  f.selected_transactions.value = f.selected_transactions.value.replace(/,$/, '');

  if (f.selected_transactions.value == '') {
    alert('<?= _('You must select at least one transaction to apply an action !!') ?>');
    return false;
  }

  f.submit();
}

function checkAll(c) {
  for (i=0 ; i<c.form.elements.length ; i++) {
    el = c.form.elements[i];

    if (m = el.id.match(/chk_(.*)/)) {
      // if needed, m[1] is id_transaction
      el.checked = c.checked;
    }
  }
}
</script>

<?


// Find the categories names and colors
$categories = array();
$result = WFO::SQL("SELECT id,name,color FROM webfinance_categories ORDER BY id");
while ($cat = mysql_fetch_assoc($result)) {
  array_push($categories, $cat);
}
mysql_free_result($result);

//echo "<pre/>";
//print_r($_GET);

// Setup the default filter if none is given


if (isset($filter['shown_cat']) AND ( (!count($filter['shown_cat'])) || ($filter['shown_cat']['check_all'] == "on")  )) {
  $result = WFO::SQL("SELECT id FROM webfinance_categories");

  while (list($id) = mysql_fetch_array($result))
    $filter['shown_cat'][$id] = "on";

  mysql_free_result($result);
 }

// Calculate balance for each transaction
$w="";

if (isset($filter['id_account']) AND ($filter['id_account'] != 0) ) {
  $w = "WHERE id_account=".$filter['id_account']." " ;
 }
if(isset($filter['shown_type']) AND count($filter['shown_type'])){
  foreach($filter['shown_type'] as $type=>$value){
    $w .= " AND type='$type' ";
  }
 }

$w = preg_replace('/^\sAND\s/',' WHERE ',$w);

$req=WFO::SQL("SELECT id, amount, id_account, exchange_rate FROM webfinance_transactions $w  ORDER BY date");
$balance_yesterday=0;
$balance_lines=array();
while ($row=mysql_fetch_assoc($req)) {
  if(empty($row['exchange_rate']))
    $row['exchange_rate']=1;

  $balance_lines[$row['id']]=$balance_yesterday+($row['amount']/$row['exchange_rate']);
  $balance_yesterday+=$row['amount']/$row['exchange_rate'];
}
mysql_free_result($req);

// ---------------------------------------------------------------------------------------------------------------------
// Check filter data coherence :
if ( isset($filter) AND ( !preg_match("!^[0-9.-]+$!", $filter['amount']) ) ) {
  $filter['amount'] = "";  // Search by amount must be numeric
}

if (isset($filter) AND  preg_match("!^([0-9.,]+)-([0-9.,]+)$!", $filter['amount'], $foo)) {
  if ($foo[1] > $foo[2]) {
    $filter['amount'] = $foo[2]."-".$foo[1]; // Special blondes check, invert amount range
  }
}

// If no date range is specified use "current month"
$days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
if ((strftime("%Y") % 4 == 0) && (strftime("%Y") % 100 != 0)) { // Leap year
  $days_in_month[1] = 29;
}

if(isset($filter)){

  #si la date start est vide on utilise la plus ancienne date d'une transaction
  if( ( isset($filter['start_date']) AND empty($filter['start_date']) ) OR ( !isset($filter['start_date']) ) ){
    $result = WFO::SQL("SELECT UNIX_TIMESTAMP(MIN(date)) as ts_start_date FROM webfinance_transactions");
    list($ts_start_date) = mysql_fetch_array($result);
    if(empty($ts_start_date)){
      $ts_start_date=mktime();
    }
    $filter['start_date'] = strftime("%d/%m/%Y",$ts_start_date);
  }

  #si la date end est vide on utilise la plus récente date d'une transaction
  if( ( isset($filter['end_date']) AND empty($filter['end_date']) ) OR ( !isset($filter['end_date']) ) ){
    $result = WFO::SQL("SELECT UNIX_TIMESTAMP(MAX(date)) as ts_end_date FROM webfinance_transactions");
    list($ts_end_date) = mysql_fetch_array($result);
    if(empty($ts_end_date)){
      $ts_end_date=mktime();
    }
    $filter['end_date'] = strftime("%d/%m/%Y",$ts_end_date);
  }

  preg_match( "!([0-9]{2})/([0-9]{2})/([0-9]{4})!", $filter['start_date'],$foo);
  $ts_start_date = mktime( 0,0,0, $foo['2'], $foo['1'], $foo['3']);
  preg_match( "!([0-9]{2})/([0-9]{2})/([0-9]{4})!", $filter['end_date'],$foo);
  $ts_end_date = mktime( 0,0,0, $foo['2'], $foo['1'], $foo['3']);

 }else{
  $result = WFO::SQL("SELECT UNIX_TIMESTAMP(MIN(date)) as ts_start_date , UNIX_TIMESTAMP(MAX(date)) as ts_end_date FROM webfinance_transactions");
  list($ts_start_date,$ts_end_date) = mysql_fetch_array($result);
  if(empty($ts_start_date)){
    $ts_start_date=mktime();
  }
  if(empty($ts_end_date)){
    $ts_end_date=mktime();
  }
  $filter['start_date'] = strftime("%d/%m/%Y",$ts_start_date);
  $filter['end_date'] = strftime("%d/%m/%Y",$ts_end_date);
 }

// end date must be after begin date. If not reverse them
if ($ts_start_date > $ts_end_date) {
  // Reverse : switch timestamps and formated dates
  list($filter['start_date'],$filter['end_date']) = array($filter['end_date'],$filter['start_date']);
  list($ts_start_date,$ts_end_date) = array($ts_end_date,$ts_start_date);
}

// End check filter data coherence
// ---------------------------------------------------------------------------------------------------------------------

$old_query_string = $GLOBALS['_SERVER']['QUERY_STRING']; // FIXME : Better than pass the big $filter around by get we sould store it in the session.
$GLOBALS['_SERVER']['QUERY_STRING'] = preg_replace("/sort=\w*\\&*+/", "", $GLOBALS['_SERVER']['QUERY_STRING']);

// print "-".$GLOBALS['_SERVER']['QUERY_STRING']."--";

?>

<table border="0" cellspacing="5" cellpadding="0" width="100%">
<tr style="vertical-align: top;">
  <td width="100%">
    <?php // Transaction listing ?>
    <form id="checkboxes" name="checkboxes" action="save_transaction.php" method="post"> <? // This form does not submit !! It's only here to allow apearance of checkboxes ?>
     <input type="hidden" name="action" value="update_transactions"/>
    <table border="0" cellspacing="0" width="100%" cellpadding="3" class="framed">
      <tr style="text-align: center;" class="row_header">
        <td><input onmouseover="return escape('<?= _('Check and unchecks all transactions shown') ?>');" type="checkbox" onchange="checkAll(this);" /></td>
        <td></td>
        <td><a href="?sort=date&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Date') ?></a></td>
        <td><a href="?sort=category&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Category') ?></a>/<a href="?sort=color&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Color') ?></a></td>
        <td><a href="?sort=type&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Type') ?></a></td>
        <td><a href="?sort=desc&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Description') ?></a></td>
        <td><a href="?sort=amount&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Amount') ?></a></td>
        <td><?= _('Balance') ?></td>
      </tr>
     <?php

     // -------------------------------------------------------------------------------------------------------------
     // Begin where clause
     // Filter on type of transaction
  if( isset($filter['shown_cat']) && count($filter['shown_cat']) && !isset($filter['shown_cat']['check_all']) ) {

    $operator = "=";
    $clause = "OR";
    if(isset($filter['shown_cat']['invert']) AND $filter['shown_cat']['invert'] == "on" ){
      $operator = "<>";
      $clause = "AND";
    }

    $where_clause .= " (";

    foreach ($filter['shown_cat'] as $catid=>$dummy) {
      if(is_numeric($catid))
	$where_clause .= "id_category".$operator." ".$catid." ".$clause." ";
    }
    $where_clause = preg_replace("/ (OR|AND) $/", ")", $where_clause);

    //affichage
    if(isset($filter['shown_cat']['invert']) AND $filter['shown_cat']['invert'] == "on" ){
      $result = WFO::SQL("SELECT id FROM webfinance_categories");
      while (list($id) = mysql_fetch_array($result)) {
	if(isset($filter['shown_cat'][$id]) == "on")
	  unset($filter['shown_cat'][$id]);
	else
	  $filter['shown_cat'][$id] = "on";
      }
      mysql_free_result($result);
    }

  }

if(isset($filter['shown_type']) && count($filter['shown_type'])){
       $where_clause .= " AND (";
       foreach($filter['shown_type'] as $type_name=>$dummy){
	 $where_clause .= " type='$type_name' OR ";
       }
       $where_clause = preg_replace("/ OR $/", ")", $where_clause);
     }

     $limit_clause = sprintf(" LIMIT %d,%d", $page*$transactions_per_page, $transactions_per_page );

     // Filter on dates
     if (($ts_start_date != 0) && ($ts_end_date != 0)) {
       $where_clause .= " AND (unix_timestamp(date)>=$ts_start_date AND unix_timestamp(date)<=$ts_end_date) ";
     }

     // Filter on account
     if ($filter['id_account'] != 0) {
       $where_clause .= " AND id_account=".$filter['id_account'];
     }

     // Filter on text
     if ($filter['textsearch'] != "") {
       $where_clause .= " AND (text LIKE '%".$filter['textsearch']."%' OR comment LIKE '%".$filter['textsearch']."%')";
     }

     // Filter on amount
     if ($filter['amount'] != "") {
       $filter['amount'] = preg_replace("!,!", ".", $filter['amount']); // Decimal dot can be coma for european users

       if (preg_match("!([0-9.]+)-([0-9.]+)!", $filter['amount'], $matches)) {
         // Interval
         $where_clause .= " AND (abs(amount) >= ".$matches[1]." AND abs(amount) <= ".$matches[2].") ";
       } else {
         // One amount
         $where_clause .= " AND (abs(amount*1.10) >= ".$filter['amount']." AND abs(amount*0.9) <= ".$filter['amount'].") ";
       }
     }

     $where_clause = preg_replace("/^ AND /", "", $where_clause);

     // End where clause
     // -------------------------------------------------------------------------------------------------------------

     // -------------------------------------------------------------------------------------------------------------
     // BEGIN order clause
     switch ($_GET['sort']) {
       case "category" : $order_clause = "c.name, t.date DESC"; break;
       case "color" : $order_clause = "HEX(MID(c.color, 1,2)),HEX(MID(c.color,3,2)),HEX(MID(c.color,5,2))"; break;
       case "amount" : $order_clause = "abs(t.amount) DESC"; break;
       case "type" : $order_clause = "t.type,t.date DESC "; break;
       case "desc" : $order_clause = "t.text,t.comment "; break;
       case "date" :
       default : $order_clause = "t.date DESC";
     }
     // END order clause
     // -------------------------------------------------------------------------------------------------------------


     $q = "SELECT t.id,t.amount, t.exchange_rate ,t.date,UNIX_TIMESTAMP(t.date) as ts_date, c.name,t.type,t.text,t.comment,c.color,t.id_category,t.file_name,t.id_account,t.id_invoice
           FROM webfinance_transactions AS t LEFT JOIN webfinance_categories AS c ON t.id_category=c.id
           HAVING $where_clause
           ORDER BY $order_clause";
     // Get number of total pages for this filter :
     $result = WFO::SQL($q);
     $nb_transactions = mysql_num_rows($result);
     mysql_free_result($result);

     $q .= $limit_clause;
     $result = WFO::SQL($q);

     $filter_base = sprintf("sort=%d&filter[start_date]=%s&filter[end_date]=%s&filter[textsearch]=%s&filter[amount]=%s&view=%s",
                            $_GET['sort'], $filter['start_date'], $filter['end_date'], $filter['textsearch'], $filter['amount'], $view);
     $result = WFO::SQL($q);
     $total_shown = 0;
     $count = 1;
     $prev_date="";
     $cur_date=$ts_start_date;

     while ($tr = mysql_fetch_object($result)) {

       //id des factures liées
       $id_invoices = array();
       $result_invoices = mysql_query("SELECT id_invoice as id , num_facture , ref_contrat ".
				      "FROM webfinance_transaction_invoice AS wf_tr_inv LEFT JOIN webfinance_invoices AS wf_inv ON (wf_tr_inv.id_invoice = wf_inv.id_facture) ".
				      "WHERE wf_tr_inv.id_transaction=".$tr->id)
	 or wf_mysqldie();
       while($invoice_obj = mysql_fetch_object($result_invoices)){
	 $id_invoices[] = $invoice_obj;
       }
       mysql_free_result($result_invoices);

       //currency
       list($currency,$ex)=getCurrency($tr->id_account);

       if(empty($tr->exchange_rate))
	 $tr->exchange_rate=1;

       //sï¿½parer les mois
       $current_month=ucfirst(strftime("%B %Y",$tr->ts_date));
       if(!empty($prev_date)){
	 if(date("m",$prev_date)!=date("m",$tr->ts_date))
	   echo "<tr class=\"row_even\"><td colspan='8' align='center'><b>$current_month</b></td></tr>";
       }else{
	 echo "<tr class=\"row_even\"><td colspan='8' align='center'><b>$current_month</b></td></tr>";
	 $cur_date=$tr->ts_date;
       }

       $prev_date=$tr->ts_date;

       $total_shown += ($tr->amount/$tr->exchange_rate);

       $fmt_date = strftime("%d/%m/%Y", $tr->ts_date); // Formated date (localized)
       $fmt_amount = number_format($tr->amount, 2, ',', ' '); // Formated amount
       $amount_color = ($tr->amount > 0)?"#e0ffe0":"#ffe0e0";

       $balance = $balance_lines[$tr->id];
       $balance_color = ($balance > 0)?"#e0ffe0":"#ffe0e0";
       $fmt_balance = number_format($balance, 2, ',', ' '); // Formated balance

       $help_edit = addslashes(_('Click to modify this transaction'));

       $class = ($count%2)?"row_odd":"row_even";

       if($tr->type=="prevision" AND $tr->ts_date<mktime(23,59,59,date("m"),date("d")-1,date("Y")))
	 $class = "row_error";


       $file ="";
       $File = new FileTransaction();
       $files = $File->getFiles($tr->id);
       foreach($files as $file_object ){
	 $file .= sprintf("<a href='save_transaction?action=file&id_file=%d' title='%s'><img src='/imgs/icons/attachment.png'/></a>",$file_object->id_file, $file_object->name);
       }

       if(isset($view) AND $view=="edit" AND $User->hasRole("manager",$_SESSION['id_user'])){

?>
  <input type="hidden" name="query" value="?view=edit&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>" />

<tr class="<?=$class?>">
  <td>
	 <input type="checkbox" id="chk_<?=$tr->id?>" name="chk[]" onchange="updateCheck(<?=$tr->id?>);" value="<?=$tr->id?>"/>
  </td>
  <td>
<?php
	   if($User->hasRole("manager", $_SESSION['id_user'] )){
	     printf("<img src=\"/imgs/icons/edit.gif\" onmouseover=\"return escape('%s');\" onclick=\"inpagePopup(event, this, 450, 500, 'fiche_transaction.php?id=%d');\" />" ,$help_edit , $tr->id );
	   }else{
	     echo "&nbsp;";
	   }
?>
  </td>
  <td><?=$fmt_date?></td>
  <td style="background: <?= $tr->color ?>; text-align: center;" nowrap>
   <select name='categ[<?= $tr->id ?>]'>
<?
     foreach($categories as $categ ){
	 printf("<option value='%d' %s>%s</option>",$categ['id'],($tr->id_category==$categ['id'])?"selected":"", $categ['name']);
       }
?>
    </select>
  </td>

  <td style="text-align: center;">
   <select name='type[<?= $tr->id ?>]'>
      <option value="real" <? if("real"==$tr->type) echo "selected"; ?> ><?= _('Real') ?></option>
      <option value="prevision" <? if("prevision"==$tr->type) echo "selected"; ?> ><?= _('Prevision') ?></option>
      <option value="asap" <? if("asap"==$tr->type) echo "selected";  ?> ><?= _('ASAP') ?></option>
   </select>
  </td>
  <td width="100%" style="font-size: 10px;">
    <?=$tr->text?><br/>
    <i><?=$tr->comment?></i>&nbsp;<?=$file?>&nbsp;<a href="expenses.php?id_transaction=<?=$tr->id?>">[expenses]</a>
<?php
 foreach($id_invoices as $invoice_obj){
  printf('<a href="../prospection/edit_facture.php?id_facture=%d" ><img style="width: 12px; height: 12px;" onmouseover="return escape(\'%s\');" src="/imgs/icons/invoice-edit.png" /></a> ',
	 $invoice_obj->id , $invoice_obj->num_facture ."<br/>".$invoice_obj->ref_contrat);
 }
?>
  </td>
  <td style="text-align: right; font-weight: bold; background: $amount_color" nowrap><?=$fmt_amount?> <?=$currency?></td>
      <td style="text-align: right; background: <?=$balance_color?>;" nowrap><?=$fmt_balance?> &euro;</td>
</tr>

<?


     }else{

	 $globals_query_string=$GLOBALS['_SERVER']['QUERY_STRING'];

print <<<EOF
 <input type="hidden" name="query" value="$globals_query_string" />

<tr class="$class">
  <td>
	 <input type="checkbox" id="chk_$tr->id" name="chk[]" onchange="updateCheck($tr->id);" value="$tr->id"/>
  </td>
  <td>
EOF;

 if($User->hasRole("manager", $_SESSION['id_user'] )){
   printf("<img src=\"/imgs/icons/edit.gif\" onmouseover=\"return escape('%s');\" onclick=\"inpagePopup(event, this, 450, 500, 'fiche_transaction.php?id=%d');\" />" ,$help_edit , $tr->id );
 }else{
   echo "&nbsp;";
 }

print <<<EOF
  </td>
  <td>$fmt_date</td>
  <td style="background: $tr->color; text-align: center;" nowrap><a href="?$filter_base&filter[shown_cat][$tr->id_category]='on'">$tr->name</a></td>
EOF;
?>
  <td style="text-align: center;">
<?
     printf("%s%s%s", ($tr->type!="real")?"<span style='background-color: rgb(255, 255, 102);'>":"" , $tr->type , ($tr->type!="real")?"</span>":"" );
?>
  </td>
  <td width="100%" style="font-size: 9px;"><?= $tr->text ?><br/><i><?= $tr->comment ?></i>&nbsp;<?=$file?>&nbsp;<a href="expenses.php?id_transaction=<?=$tr->id?>">[expenses]</a>
<?
 foreach($id_invoices as $invoice_obj){
  printf('<a href="../prospection/edit_facture.php?id_facture=%d" ><img style="width: 12px; height: 12px;" onmouseover="return escape(\'%s\');" src="/imgs/icons/invoice-edit.png" /></a> ',
	 $invoice_obj->id , $invoice_obj->num_facture ."<br/>".$invoice_obj->ref_contrat);
 }
print <<<EOF
         </td>
  <td style="text-align: right; font-weight: bold; background: $amount_color" nowrap>$fmt_amount $currency</td>
  <td style="text-align: right; background: $balance_color;" nowrap>$fmt_balance &euro;</td>
</tr>
EOF;

       }

       $count++;
     }
     ?>
     <tr class="row_even">
       <td colspan="3">
<?
       if($User->hasRole("manager",$_SESSION['id_user']) && $count>1){
	 if($view=="edit")
	   printf('<input type="submit" value="%s" /><input type="button" onclick="window.location=\'?%s\'" value="%s" />',
         _('Save'),preg_replace('/=edit/i','', $old_query_string), _('Normal view'));
	 else
	   printf('<input type="button" onclick="window.location=\'?%s&view=edit\';" value="%s" />',preg_replace('/&view*&/i','&', $old_query_string), _('Stow view'));
       }
?>
       </td>
       <td colspan="3" style="text-align: right; font-weight: bold;"><?= _('Total amount of shown transactions') ?></td>
       <td nowrap style="text-align: right; font-weight: bold;"><?= number_format($total_shown, 2, ',', ' ') ?> &euro;</td>
     </tr>
    </table>
    </form> <? // End of checkboxes form ?>
<?
       if($User->hasRole("manager",$_SESSION['id_user'] )){
	 printf("<a href=\"\" onClick=\"inpagePopup(event, this, 450, 500, 'fiche_transaction.php?id=-1');return false\">%s</a>" , _('Add a transaction'));
       }
?>
  </td>

  <td><?php // Begin of right column ?>
    <?php // Filter ?>
    <form id="main_form" onchange="this.submit();" method="get">
    <input type="hidden" name="sort" value="<?= $_GET['sort'] ?>" />
    <input type="hidden" name="view" value="<?= $view ?>" />
    <input type="hidden" name="page" value="<?= $page ?>" />
    <input type="hidden" name="filter[page]" value="<?= $page ?>" />
    <table border="0" cellspacing="0" cellpadding="3" width="310" class="framed">
    <tr class="row_header">
      <td colspan="2" style="text-align: center"><?= _('Filter') ?></td>
    </tr>

    <tr class="row_even">
     <td><b><?=_('Month')?></b></td>
     <td>
<?

printf('<a class="pager_link" href="?%s&filter[start_date]=%s&filter[end_date]=%s%s%s%s"> << </a> ',
       $filter_base,
       date("d/m/Y",mktime(0,0,0,date("n",$cur_date)-1,1,date("Y",$cur_date) ) ) ,
       date("d/m/Y",mktime(0,0,0,date("n",$cur_date),0,date("Y",$cur_date) ) ) ,
       ($filter['shown_type']['real'])?"&filter[shown_type][real]=on":"" ,
       ($filter['shown_type']['prevision'])?"&filter[shown_type][prevision]=on":"" ,
       ($filter['shown_type']['asap'])?"&filter[shown_type][asap]=on":""
       ) ;

echo strftime("%B %Y",$cur_date);
printf('<a class="pager_link" href="?%s&filter[start_date]=%s&filter[end_date]=%s%s%s%s"> >> </a> ',
       $filter_base,
       date("d/m/Y",mktime(0,0,0,date("n",$cur_date)+1,1,date("Y",$cur_date) ) ) ,
       date("d/m/Y",mktime(0,0,0,date("n",$cur_date)+2,0,date("Y",$cur_date) ) ) ,
       ($filter['shown_type']['real'])?"&filter[shown_type][real]=on":"" ,
       ($filter['shown_type']['prevision'])?"&filter[shown_type][prevision]=on":"" ,
       ($filter['shown_type']['asap'])?"&filter[shown_type][asap]=on":""
  ) ;
?>
     </td>
    </tr>
    <?php if ($nb_transactions/$transactions_per_page > 1) { ?>
    <tr class="row_even">
      <td><b>Page</b></td>
      <td>
       <?php

     $nb_page= ceil($nb_transactions/$transactions_per_page);
     $diz=1+floor($page/10);
     $start=0;
     $end=$nb_transactions/$transactions_per_page;
     if((10*$diz) < $nb_transactions/$transactions_per_page)
       $end=10*$diz;

     if($page>0)
       printf('<a class="pager_link" href="?%s&filter[page]=%d"><<</a>', $filter_base, $page-1 );
     if($page>9  ){
       printf('<a class="pager_link" href="?%s&filter[page]=%d">&nbsp;...</a>', $filter_base, ((floor($page/10))*10)-1   );
       $start=((floor($filter['page']/10))*10);
     }
	for ($i=$start ; $i<$end ; $i++) {
	   if ( $page == $i) {
		 printf("[%d]", $i+1);
	   } else {
	     printf('<a class="pager_link" href="?%s&filter[page]=%d">%d</a>', $filter_base, $i, $i+1 );
	   }
	   print("&nbsp;");
	}
     if($end < floor($nb_transactions/$transactions_per_page)  ){
       printf('<a class="pager_link" href="?%s&filter[page]=%d">...&nbsp;</a>', $filter_base, $end  );
     }

     if($page < floor($nb_transactions/$transactions_per_page) )
       printf('<a class="pager_link" href="?%s&filter[page]=%d">>></a>', $filter_base, $page+1 );
      ?>
      </td>

    </tr>
    <?php } ?>
    <tr class="row_even">
      <td><b><?= _('Account :') ?></b></td>
      <td><select name="filter[id_account]" style="width: 150px;">
        <option value="0"><?= _('-- All accounts --') ?></option>
      <?php
      $result = WFO::SQL("SELECT id_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='rib'");
      while (list($id_cpt,$cpt) = mysql_fetch_array($result)) {
        $cpt = unserialize(base64_decode($cpt));
        printf(_('        <option value="%d"%s>%s #%s</option>')."\n", $id_cpt, ($filter['id_account']==$id_cpt)?" selected":"", $cpt->banque, $cpt->compte );
      }
      mysql_free_result($result);
      ?></select></td>
    </tr>
    <tr class="row_even">
      <td nowrap><b><?= _('Amount') ?> <img class="help_icon" src="/imgs/icons/help.png" onmouseover="return escape('<?= _('Enter a number for 10% aproximated search, enter 100-200 to search transactions fromm 100&euro; to 200&euro; included') ?>');" /></b></td>
      <td><input style="text-align: center; width: 130px;" type="text" id="amount_criteria" name="filter[amount]" value="<?= $filter['amount'] ?>" /><img src="/imgs/icons/delete.gif" onmouseover="return escape('<?= _('Click to suppress this filter criteria') ?>');" onclick="fld = document.getElementById('amount_criteria'); fld.value = ''; fld.form.submit();" /></td>
    </tr>
    <tr class="row_even">
      <td nowrap><b><?= _('Text contains :') ?></b></td>
      <td><input style="text-align: center; width: 130px;" id="text_criteria" type="text" name="filter[textsearch]" value="<?= $filter['textsearch'] ?>" /><img src="/imgs/icons/delete.gif" onmouseover="return escape('<?= _('Click to suppress this filter criteria') ?>');" onclick="fld = document.getElementById('text_criteria'); fld.value = ''; fld.form.submit();" /></td>
    </tr>
    <tr class="row_even">
      <td nowrap><b><?= _('Start date :') ?></b></td>
      <td><?php makeDateField("filter[start_date]", $ts_start_date, 1, 'start_date_criteria', 'width: 114px'); ?><img src="/imgs/icons/delete.gif" onmouseover="return escape('<?= _('Click to suppress this filter criteria') ?>');" onclick="fld = document.getElementById('start_date_criteria'); fld.value = ''; fld.form.submit();" /></td>
    </tr>
    <tr class="row_even">
      <td nowrap><b><?= _('End date :') ?></b></td>
      <td><?php makeDateField("filter[end_date]", $ts_end_date, 1, 'end_date_criteria', 'width: 114px'); ?><img src="/imgs/icons/delete.gif" onmouseover="return escape('<?= _('Click to suppress this filter criteria') ?>');" onclick="fld = document.getElementById('end_date_criteria'); fld.value = ''; fld.form.submit();" /></td>
    </tr>

    <tr class="row_even">
      <td nowrap><b><?= _('Shown types :') ?></b></td>
      <td>
       <?
	printf('<input type="checkbox" name="filter[shown_type][real]" %s /><b>%s</b>', ($filter['shown_type']['real'])?"checked":"" ,_('real'));
	printf('<input type="checkbox" name="filter[shown_type][prevision]" %s /><b>%s</b>', ($filter['shown_type']['prevision'])?"checked":"" ,_('prev'));
	printf('<input type="checkbox" name="filter[shown_type][asap]" %s /><b>%s</b>', ($filter['shown_type']['asap'])?"checked":"" ,_('asap'));
       ?>
      </td>
    </tr>

    <tr class="row_even">
      <td nowrap><b><?= _('Shown categories :') ?></b></td>
      <td>
       <input type="checkbox" name="filter[shown_cat][check_all]" /><b><?= _('View all') ?>
       <input type="checkbox" name="filter[shown_cat][invert]" /><b><?= _('Invert') ?></b>
      </td>
    </tr>
    <tr class="row_even">
      <?php
      $count = 0;
      $result = WFO::SQL("SELECT id,name,color FROM webfinance_categories ORDER BY name");
      while ($cat = mysql_fetch_object($result)) {
        printf('<td nowrap><input type="checkbox" name="filter[shown_cat][%d]" %s>&nbsp;%s</td>', $cat->id, ($filter['shown_cat'][$cat->id])?"checked":"", $cat->name );
        $count++;
        if ($count % 2 == 0) {
          print "</tr>\n<tr class=\"row_even\">\n";
        }
      }
      mysql_free_result($result);
      ?>
    </tr>
    </table>
    </form>
  <?php
      //Actions on selected transactions
    if($User->hasRole("manager" , $_SESSION['id_user'])){
  ?>
  <br/>
  <form id="action_form" action="save_transaction.php" method="post">
  <input type="hidden" name="query" value="<?= $old_query_string ?>" />
  <input type="hidden" name="selected_transactions" value="" />

  <table border="0" cellspacing="0" cellpadding="2" width="310" class="framed">
  <tr class="row_header">
    <td style="text-align: center;" colspan="2"><?= _('Action on selected transactions') ?></td>
  </tr>
  <tr class="row_even">
    <td style="width: 90px;"><?= _('Action') ?></td>
    <td>
      <select onchange="changeAction(this);" name="action[type]" style="width: 200px;">
        <option value="delete"><?= _('Delete the selected transactions') ?></option>
        <option value="change_account"><?= _('Move to account...') ?></option>
        <option value="change_category"><?= _('Change category...') ?></option>
      </select>
  </tr>
  <tr class="row_even">
    <td colspan="2">
      <div id="action_change_account" style="display: none;">
        <div style="display: block; float: left; width: 90px;"><?= _('To account ') ?></div>&nbsp;<select name="action[id_account]" style="width: 150px;">
        <?php
        $result = WFO::SQL("SELECT id_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='rib'");
        while (list($id_cpt,$cpt) = mysql_fetch_array($result)) {
          $cpt = unserialize(base64_decode($cpt));
          printf(_('        <option value="%d"%s>%s #%s</option>')."\n", $id_cpt, ($filter['id_account']==$id_cpt)?" selected":"", $cpt->banque, $cpt->compte );
        }
        mysql_free_result($result);
        ?></select>
      </div>
      <div id="action_change_category" style="display: none;">
        <div style="display: block; float: left; width: 90px;"><?= _('Category is') ?></div>&nbsp;<select name="action[id_category]">
        <option value="1"><?= _('-- Choose --') ?></option>
        <?php
        $result = WFO::SQL("SELECT id,name,color FROM webfinance_categories ORDER BY name");
        while ($cat = mysql_fetch_object($result)) {
          printf('<option value="%d">%s</option>', $cat->id, $cat->name );
        }
        mysql_free_result($result);
        ?>
        </select>
      </div>
    </td>
  </tr>
  <tr class="row_even">
    <td colspan="2" style="text-align: center"><input type="button" onclick="submitAction(this.form);" value="<?= _('Apply this action') ?>" /></td>
  </tr>
  </table>
  </form>

<?php
	    }
      //END Actions on selected transactions
?>

  <div class="bordered" style="margin-top: 20px;" >
  <?php
    $small_image_url = sprintf("/graphs/cashflow.php?grid=0&width=300&height=200&legend=0&end_date=%s&start_date=%s&hidetitle=1&account=%d",
                               strftime("%Y-%m-%d", $ts_end_date),
                               strftime("%Y-%m-%d", $ts_start_date),
                               ($filter['id_account']==0)?"":$filter['id_account']
                              );
  ?>
  <img src="<?= $small_image_url ?>" onmouseover="return escape('<?= _('Cashflow over the selected period') ?>');" />
  </div>


  </td>
</tr>
</table>

<?php
// print "<pre>";
// print_r($filter);
// print "</pre>";
$Revision = '$Revision$';
require("../bottom.php");
?>
