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

echo $_SESSION['message'];
$_SESSION['message'] = "";

?>

<table border="0" cellspacing="5" cellpadding="0" >
<tr style="vertical-align: top;">
  <!-- left -->
  <td>
    <table border=0 cellspacing=0 cellpadding=3 style="border: solid 1px black;" width="750">
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
  $clause="";

$q = "SELECT id_paybox, id_invoice, email, reference, state, amount, currency , ".
  "autorisation, transaction_id as trans, payment_type, card_type, transaction_sole_id, error_code, date, UNIX_TIMESTAMP(date) as ts_date  ".
  "FROM webfinance_paybox $clause ORDER BY date ";

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

   ?>
     <tr onmouseover="return escape('<?=$description?>');" align="center" class="<?=$class?>">
      <td ><?
	printf('<a href="../prospection/edit_facture.php?id_facture=%d" >#%s</a>', $tr->id_invoice ,$facture->num_facture);
        ?>
      </td>
      <td><?=$tr->trans?></td>
      <td><?=$tr->state?></td>
      <td><?=$tr->payement_type?> <?=$tr->card_type?></td>
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
  <table border="0" cellspacing="0" cellpadding="3" style="border: solid 1px black;" width="300">
    <tr align="center" class="row_header">
       <td colspan="2"><?= _('Simple Filter')?></td>
    </tr>
  </table>
 </td>
</tr>
</table>
<?php

$Revision = '$Revision$';
include("../bottom.php");

?>
