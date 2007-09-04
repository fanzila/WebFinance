<?php
/*
   This file is part of Webfinance.

    Webfinance is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Webfinance is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Webfinance; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
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


// $Id: expenses.php 531 2007-06-13 12:32:31Z thierry $

  //echo "<pre/>";
  //print_r($_POST);
  //print_r($_GET);


if(isset($_POST['action']) AND $_POST['action']=="select"){
  if(isset($_POST['id_transaction']) AND is_numeric($_POST['id_transaction']))
    header("Location: expenses.php?id_transaction=".$_POST['id_transaction']);
  else if(isset($_GET['id_transaction']) AND is_numeric($_GET['id_transaction']))
    header("Location: expenses.php?id_transaction=".$_GET['id_transaction']);
 }


$title = _("Expenses");
require("../inc/main.php");
$roles = 'manager,accounting,employee';
require("../top.php");
require("nav.php");

?>
<script type="text/javascript">
  function confirmDelete(id,id_tr) {
  if (confirm('<?= _('Voulez-vous vraiment supprimer cette catégorie ?') ?>')) {
    window.location = 'save_expenses.php?action=delete&id='+id+'&id_transaction='+id_tr;
  }
}
</script>


<table border="0" cellspacing="0" cellpadding="3" width="650" class="framed">


  <input type="hidden" name="action" value="select"/>
  <tr style="text-align: center;" class="row_header">
   <td width="80"><?= _('Transaction') ?></td>
   <td colspan="3">
  <form action="expenses.php" id="main_form" onchange="this.submit();">
 	<select class="form" name="id_transaction">
  <option> ------------------ <?= _('Select a transaction') ?> ------------------ </option>
  <?
  $result = mysql_query("SELECT id, date, text FROM webfinance_transactions ORDER BY date DESC") or wf_mysqldie();
while($transaction = mysql_fetch_assoc($result)){
  $len=80;
  $text=$transaction['text'];
  if (strlen($transaction['text']) >=$len) {
    $text = substr($text,0,$len)." ...";
  }
 ?>
  <option value="<?=$transaction['id']?>" <? if($_GET['id_transaction']==$transaction['id']) { echo "selected"; } ?>>
       <?=$transaction['date']?> : <?=$text?>
  </option>
 <?
  }
mysql_free_result($result);

?>
</select>
</form>
</td>
</tr>

<?

if(isset($_GET['id_transaction']) AND is_numeric($_GET['id_transaction'])){
  $id_transaction=$_GET['id_transaction'];

?>

<form action="save_expenses.php" id="main_form" enctype="multipart/form-data" method="post">
   <input type="hidden" name="id_transaction" value="<?= $id_transaction ?>"/>
    <tr style="text-align: center;" class="row_header">
    <td><?= _('Amount') ?></td>
    <td><?= _('Comment') ?></td>
    <td><?= _('File') ?></td>
    <td>&nbsp;</td>
    </tr>
<?php

  if(isset($id_transaction) AND is_numeric($id_transaction)){

    $result = mysql_query("SELECT id, amount, SUM(amount) as sum,  comment, file_name FROM webfinance_expenses WHERE id_transaction=$id_transaction GROUP BY id ")
      or die(mysql_error());

    $count=1;
    while ($exp = mysql_fetch_assoc($result)) {
      extract($exp);

      $class = ($count++ %2 == 0)?"even":"odd";
      if(strlen($file_name)>0 ){
	$link="<a href='file.php?action=file&type=expenses&id=$id' title='$file_name'>&nbsp;<img src='/imgs/icons/attachment.png'></a><input checked='checked' name='file_del[$id]' value='1' type='checkbox'' />";
      }


  print <<<EOF
<tr class="row_$class">
  <td><input type="text" name="exp[$id][amount]" value="$amount" style="width: 80px; text-align: right;" /></td>
  <td><input type="text" name="exp[$id][comment]" value="$comment" style="width: 300px;" /></td>
  <td><input type="file" name="exp[$id][file]" /></td>
  <td><a href="javascript:confirmDelete($id,$id_transaction);"><img src="/imgs/icons/delete.gif" /></a>$link</td>
</tr>
EOF;

    }

  }
?>

<tr style="background: #ceffce;">
  <td><input type="text" name="exp[new][amount]" value="" style="width: 80px; text-align: right;" /></td>
  <td><input type="text" name="exp[new][comment]" value="" style="width: 300px;" /></td>
  <td><input type="file" name="exp[new][file]" value="" /></td>
  <td></td>
</tr>
<tr class="row_even">
  <td style="text-align: center;" colspan="4"><input type="submit" value=<?= _("Save") ?> /></td>
</tr>
</form>
<?
   }
?>

</table>


<?php
$Revision = '$Revision: 531 $';
require("../bottom.php");
?>
