<?php 
// 
// This file is part of « Webfinance »
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
require("../top.php");
require("nav.php");

// Find the categories names and colors 
$categories = array();
$result = mysql_query("SELECT id,name,color FROM webfinance_categories ORDER BY id");
while ($cat = mysql_fetch_assoc($result)) {
  array_push($categories, $cat);
}
mysql_free_result($result);


// Setup the default filter if none is given
extract($_GET);

if ((!count($filter['shown_cat'])) || ($filter['shown_cat']['check_all'] == "on")) {
  $result = mysql_query("SELECT id FROM webfinance_categories");
  while (list($id) = mysql_fetch_array($result)) {
    $filter['shown_cat'][$id] = "on";
  }
  mysql_free_result($result);

  unset($filter['shown_cat']['check_all'] );
}

// Calculate balance for each transaction
$req=mysql_query('SELECT id, amount FROM webfinance_transactions ORDER BY date') or die(mysql_error());
$balance_yesterday=0;
$balance_lines=array();
while ($row=mysql_fetch_assoc($req)) {
  $balance_lines[$row['id']]=$balance_yesterday+$row['amount'];
  $balance_yesterday+=$row['amount'];
}

// ---------------------------------------------------------------------------------------------------------------------
// Check filter data coherence :
if (!preg_match("!^[0-9.-]+$!", $filter['amount'])) { $filter['amount'] = ""; }  // Search by amount must be numeric

if (preg_match("!^([0-9.,]+)-([0-9.,]+)$!", $filter['amount'], $foo)) {
  if ($foo[1] > $foo[2]) {
    $filter['amount'] = $foo[2]."-".$foo[1]; // Special blondes check, invert amount range
  }
}

// If no date range is specified use "current month"
$days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
if ((strftime("%Y") % 4 == 0) && (strftime("%Y") % 100 != 0)) { // Leap year
  $days_in_month[1] = 29;
}

if ($filter['start_date'] == "") { $filter['start_date'] = strftime("01/%m/%Y"); }
if ($filter['end_date'] == "") { $filter['end_date'] = strftime($days_in_month[strftime("%m")-1]."/%m/%Y"); }

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

// End check filter data coherence
// ---------------------------------------------------------------------------------------------------------------------

?>

<table border="0" cellspacing="5" cellpadding="0" width="100%">
<tr style="vertical-align: top;">
  <td rowspan="2" width="100%">
    <?php // Transaction listing ?>
    <table border="0" cellspacing="0" width="800" cellpadding="3" class="framed">
      <tr style="text-align: center;" class="row_header">
        <td></td>
        <td><?= _('Date') ?></td>
        <td><?= _('Category') ?></td>
        <td><?= _('Type') ?></td>
        <td><?= _('Description') ?></td>
        <td><?= _('Amount') ?></td>
        <td><?= _('Balance') ?></td>
      </tr>
     <?php

     // Filter on type of transaction
     if (count($filter['shown_cat'])) {
       $where_clause .= " (";
       foreach ($filter['shown_cat'] as $catid=>$dummy) {
         $where_clause .= "id_category=$catid OR ";
       }
       $where_clause = preg_replace("/ OR $/", ")", $where_clause);
     }
     
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

     $q = "SELECT t.id,t.amount,t.date,UNIX_TIMESTAMP(t.date) as ts_date,c.name,t.type,t.text,t.comment,c.color,t.id_category,t.id_account
           FROM webfinance_transactions AS t LEFT JOIN webfinance_categories AS c ON t.id_category=c.id
           HAVING $where_clause
           ORDER BY t.date";
//[ ]      print "<pre>$q</pre>";

     $filter_base = sprintf("filter[start_date]=%s&filter[end_date]=%s&filter[textsearch]=%s&filter[amount]=%s", 
                            $filter[start_date], $filter[end_date], $filter[textsearch], $filter[amount] );
     $result = mysql_query($q) or die(mysql_error());
     $total_shown = 0;
     while ($tr = mysql_fetch_object($result)) {
       $total_shown += $tr->amount;

       $fmt_date = strftime("%d/%m/%Y", $tr->ts_date); // Formated date (localized)
       $fmt_amount = number_format($tr->amount, 2, ',', ' '); // Formated amount
       $amount_color = ($tr->amount > 0)?"#e0ffe0":"#ffe0e0";

       $balance = $balance_lines[$tr->id];
       $balance_color = ($balance > 0)?"#e0ffe0":"#ffe0e0";
       $fmt_balance = number_format($balance, 2, ',', ' '); // Formated balance
      
       $help_edit = addslashes(_('Click to modify this transaction'));
       print <<<EOF
<tr>
  <td><img src="/imgs/icons/edit.gif" onmouseover="return escape('$help_edit');" onclick="inpagePopup(event, this, 350, 350, 'fiche_transaction.php?id=$tr->id');" /></td>
  <td>$fmt_date</td>
  <td style="background: $tr->color; text-align: center;" nowrap><a href="?$filter_base&filter[shown_cat][$tr->id_category]='on'">$tr->name</a></td>
  <td style="text-align: center;">$tr->type</td>
  <td width="100%" style="font-size: 9px;">$tr->text<br/><i>$tr->comment</i></td>
  <td style="text-align: right; font-weight: bold; background: $amount_color" nowrap>$fmt_amount &euro;</td>
  <td style="text-align: right; background: $balance_color;" nowrap>$fmt_balance &euro;</td>
</tr>
EOF;
     }

     ?>
     <tr>
       <td colspan="5" style="text-align: right; font-weight: bold;"><?= _('Total amount of shown transactions') ?></td>
       <td nowrap style="text-align: right; font-weight: bold;"><?= number_format($total_shown, 2, ',', ' ') ?> &euro;</td>
       <td></td>
     </tr>
    </table>
  </td>
  <td>
    <?php // Filter ?>
    <form id="main_form" onchange="this.submit();" method="get">
    <table border="0" cellspacing="0" cellpadding="3" class="framed">
    <tr class="row_header">
      <td colspan="2" style="text-align: center"><?= _('Filter') ?></td>
    </tr>
    <tr>
      <td><b><?= _('Account :') ?></b></td>
      <td><select name="filter[id_account]" style="width: 150px;">
        <option value="0"><?= _('-- All accounts --') ?></option>
      <?php
      $result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='rib'");
      while (list($id_cpt,$cpt) = mysql_fetch_array($result)) {
        $cpt = unserialize(base64_decode($cpt));
        printf(_('        <option value="%d"%s>%s #%s</option>')."\n", $id_cpt, ($filter['id_account']==$id_cpt)?" selected":"", $cpt->banque, $cpt->compte );
      }
      mysql_free_result($result);
      ?></td>
    </tr>
    <tr>
      <td nowrap><b><?= _('Amount') ?> <img class="help_icon" src="/imgs/icons/help.png" onmouseover="return escape('<?= _('Enter a number for 10% aproximated search, enter 100-200 to search transactions fromm 100&euro; to 200&euro; included') ?>');" /></b></td>
      <td><input style="text-align: center; width: 130px;" type="text" id="amount_criteria" name="filter[amount]" value="<?= $filter['amount'] ?>" /><img src="/imgs/icons/delete.gif" onmouseover="return escape('<?= _('Click to suppress this filter criteria') ?>');" onclick="fld = document.getElementById('amount_criteria'); fld.value = ''; fld.form.submit();" /></td>
    </tr>
    <tr>
      <td nowrap><b><?= _('Text contains :') ?></b></td>
      <td><input style="text-align: center; width: 130px;" id="text_criteria" type="text" name="filter[textsearch]" value="<?= $filter['textsearch'] ?>" /><img src="/imgs/icons/delete.gif" onmouseover="return escape('<?= _('Click to suppress this filter criteria') ?>');" onclick="fld = document.getElementById('text_criteria'); fld.value = ''; fld.form.submit();" /></td>
    </tr>
    <tr>
      <td nowrap><b><?= _('Start date :') ?></b></td>
      <td><input style="text-align: center; width: 150px;" type="text" name="filter[start_date]" value="<?= $filter['start_date'] ?>" /></td>
    </tr>
    <tr>
      <td nowrap><b><?= _('End date :') ?></b></td>
      <td><input style="text-align: center; width: 150px;" type="text" name="filter[end_date]" value="<?= $filter['end_date'] ?>" /></td>
    </tr>
    <tr>
      <td nowrap><b><?= _('Shown categories :') ?></b></td>
      <td><input type="checkbox" name="filter[shown_cat][check_all]" /><b><?= _('View all') ?></b></td>
    </tr>
    <tr>
      <?php
      $count = 0;
      $result = mysql_query("SELECT id,name,color FROM webfinance_categories ORDER BY name");
      while ($cat = mysql_fetch_object($result)) {
        printf('<td nowrap><input type="checkbox" name="filter[shown_cat][%d]" %s>&nbsp;%s</td>', $cat->id, ($filter['shown_cat'][$cat->id])?"checked":"", $cat->name );
        $count++;
        if ($count % 2 == 0) {
          print "</tr>\n<tr>\n";
        }
      }
      mysql_free_result($result);
      ?>
    </tr>
    </table>
    </form>
  </td>
</tr>
<tr>
  <td>
  Camembert
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
