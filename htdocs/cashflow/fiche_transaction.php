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
require("../top_popup.php");

$result = mysql_query("SELECT id, id_category, id_account, text, amount, type, date, comment, file_name , id_invoice,
                              unix_timestamp(date) as ts_date
                       FROM webfinance_transactions WHERE id=".$_GET['id'])
  or wf_mysqldie();

if(mysql_num_rows($result)>0){
  $transaction = mysql_fetch_object($result);
  mysql_free_result($result);
 }else{
  $transaction = new stdClass();
  $transaction->id=-1;
  $transaction->id_category=0;
  $transaction->id_account=0;
  $transaction->id_invoice=0;
  $transaction->text="";
  $transaction->amount=0;
  $transaction->type="real";
  // $transaction->date=date("Y-m-d");
  // $transaction->comment="no comment"; <-- YEURK
  $transaction->file_name="";
 }

?>
<form id="main_form" method="post" action="save_transaction.php" enctype="multipart/form-data">
<input type="hidden" name="id_transaction" value="<?= $transaction->id ?>" />
<table width="200" border="0" cellspacing="0" cellpadding="3">
<tr>
  <td><?=_('Account')?></td>
  <td colspan="3"><select name="id_account" style="width: 210px;">
        <option value="0"><?= _('-- Select an account --') ?></option>
      <?php
      $result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='rib'");
      while (list($id_cpt,$cpt) = mysql_fetch_array($result)) {
        $cpt = unserialize(base64_decode($cpt));
        printf(_('<option value="%d"%s>%s #%s</option>')."\n", $id_cpt, ($transaction->id_account==$id_cpt)?" selected":"", $cpt->banque, $cpt->compte );
      }
      mysql_free_result($result);
      ?>
  </td>
</tr>
<tr>
  <td><?= _('Date') ?></td>
  <td colspan="3"><?= makeDateField('date', $transaction->ts_date); ?></td>
</tr>
<tr>
  <td><?= _('Category') ?></td>
 <td colspan="3">
  <select name="id_category"><?php
  $result = mysql_query("SELECT id,name FROM webfinance_categories ORDER BY name");
  while (list($id,$name) = mysql_fetch_array($result)) {
  printf('<option value="%d"%s>%s</option>', $id, ($id==$transaction->id_category)?" selected":"", $name );
 }
mysql_free_result($result);
?></select>
 </td>
</tr>
<tr>
  <td><?= _('Description') ?></td>
  <td colspan="3"><input type="text" style="width: 210px;" name="text" value="<?=$transaction->text ?>" /></td>
</tr>
<tr>
  <td><?= _('Amount') ?> :</td>
  <td>
    <input type="text" style="width: 80px;" name="amount" class="amount_field" value="<?= number_format($transaction->amount, 2, ',', ' '); ?>" />
  </td>
  <td width="100%">Type</td>
  <td>
    <select name="type">
      <option value="real" <? if("real"==$transaction->type) echo "selected"; ?> ><?= _('Real') ?></option>
      <option value="prevision" <? if("prevision"==$transaction->type) echo "selected"; ?> ><?= _('Prevision') ?></option>
      <option value="asap" <? if("asap"==$transaction->type) echo "selected";  ?> ><?= _('ASAP') ?></option>
    </select>
  </td>
</tr>
<tr style="vertical-align: top">
  <td nowrap><?= _('Comment') ?> :</td>
  <td colspan="3">
  <textarea style="width: 340px; height: 100px;" name="comment"><?=$transaction->comment?></textarea>
  </td>
</tr>
  <tr>
  <td><?= _('Invoice') ?> :</td>
  <td colspan="3">
 <select style="width: 300px;" name="id_invoice">
  <option value="0">-- related invoice --</option>
<?
  $res = mysql_query("SELECT id_facture as id, ".
		   "num_facture as num, ".
		     "ref_contrat as ref, ".
		     "date_format(date_facture, '%d/%m/%Y') as nice_date_facture ".
		     "FROM webfinance_invoices ".
		     "ORDER BY date_facture")
    or wf_mysqldie();

   while($inv=mysql_fetch_assoc($res)){

     $result = mysql_query("SELECT prix_ht,qtt FROM webfinance_invoice_rows WHERE id_facture=".$inv['id'])
       or wf_mysqldie();

     $lignes = array();
     $total = 0;

     while ($el = mysql_fetch_object($result)) {
       array_push($lignes, $el);
       $total += $el->qtt * $el->prix_ht;
     }
     mysql_free_result($result);
     $total_ht = $total;
     $total_ttc = $total*1.196;
     $nice_total_ht = sprintf("%.2f", $total_ht);
     $nice_total_ttc = sprintf("%.2f", $total_ttc);

     printf(_(' <option value="%d"%s>%s (%sEuro %s #%s)</option>')."\n", $inv['id'], ($inv['id']==$transaction->id_invoice)?" selected":"", $inv['ref'] ,$nice_total_ttc ,$inv['nice_date_facture'], $inv['num'] );
   }
?>
 </select>
  </td>
</tr>
<tr>
  <td><?= _('File') ?> :<br/>
  <? if(!empty($transaction->file_name)){
  ?>
       <input checked='checked' name='file_del' value='1' type='checkbox' />&nbsp<a href='file.php?action=file&type=transactions&id=<?=$transaction->id ?>'><?=$transaction->file_name ?></a><br/>
 <? }?>
  </td>
 <td colspan="3"><input type="file" name="file" /></td>
</tr>
<tr>
  <td colspan="4" style="text-align: center">
    <input id="submit_button" type="submit" value="<?=_('Save') ?>" />
  </td>
</tr>
</table>


<?php
$Revision = '$Revision$';
require("../bottom_popup.php");
?>
