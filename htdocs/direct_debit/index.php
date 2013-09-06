<?php
/*
 Copyright (C) 2004-2011 NBI SARL, ISVTEC SARL

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
include("../inc/main.php");
must_login();

$roles = 'manager,employee';
include("../top.php");
include("nav.php");

$Invoice = new Facture();

$res = mysql_query(
  'SELECT id, invoice_id '.
  'FROM direct_debit_row '.
  "WHERE state='todo'")
  or die(mysql_error());
?>

<h1>Pending direct debits</h1>

<h2>Details</h2>

<table width="100%" border="0" cellspacing="0" cellpadding="5">
<tr class="row_header">
  <th>Company</th>
  <th>Reference</th>
  <th>Date</th>
  <th>Amount excl. VAT</th>
  <th>Amount incl. VAT</th>
  <th></th>
 </tr>

<?
$total_ht  = 0;
$total_ttc = 0;
$count = 0;
while ($invoice = mysql_fetch_assoc($res)) {
  $info = $Invoice->getInfos($invoice['invoice_id']);
  $total[$info->nom_client]['HT'] += $info->total_ht;
  $total[$info->nom_client]['TTC'] += $info->total_ttc;
  echo "<tr> <td> $info->nom_client </td>";
  echo "<td> <a href=\"../prospection/edit_facture.php?id_facture=$invoice[invoice_id]\">$info->num_facture</a> </td>";
  echo "<td> $info->nice_date_facture </td>";
  echo "<td align=right> $info->nice_total_ht &euro; </td>";
  echo "<td align=right> $info->nice_total_ttc &euro; </td>";
  echo "<td> <a href=\"del.php?id=$invoice[invoice_id]\">Del</a> </td>";
  echo "</tr>";

  $total_ht  += $info->total_ht;
  $total_ttc += $info->total_ttc;
  $count++; 
}
?>

<tr class="row_header">
  <td><b>Total debit(s): <?=$count?></b></td>
  <td></td>
  <td align="right"> <b>TOTAL</b> </td>
  <td align="right"> <?=sprintf("%.2f", $total_ht);?> &euro; </td>
  <td align="right"> <b><?=sprintf("%.2f", $total_ttc);?> &euro; </b></td>
</tr>
</table>
<br/>
<hr>
<br/>
<h2>Summary</h2>

<table width="100%" border="0" cellspacing="0" cellpadding="5">
<tr class="row_header">
  <th>Company</th>
  <th>Amount excl. VAT</th>
  <th>Amount incl. VAT</th>
 </tr>

<?
  ksort($total);
  foreach($total as $company => $amount) { ?>
<tr>
  <td> <?=$company?> </td>
  <td align="right"> <?=sprintf("%.2f", $amount['HT']);?> &euro; </td>
  <td align="right"> <?=sprintf("%.2f", $amount['TTC']);?> &euro; </td>
</tr>
<?  } ?>

<tr class="row_header">
  <td align="right"> <b>TOTAL</b> </td>
  <td align="right"> <?=sprintf("%.2f", $total_ht);?> &euro; </td>
  <td align="right"> <?=sprintf("%.2f", $total_ttc);?> &euro; </td>
</tr>

</table>
<br>
<form action="process.php" onsubmit="return confirm('Are you sure you want to process the direct debit?')">
  <input style="width: 150px; height: 40px;" class="bordered" type="submit" name="debit" value="Mark invoices as debited">
</form>

<br/>
<hr>
<br/>

<h1>Previous debits</h1>

<table width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr class="row_header">
    <th>Date</th>
    <th>Invoices</th>
    <th>Excl. taxes</th>
    <th>Inc. taxes</th>
    <th>Download</th>
    </tr>
<?
$res = mysql_query('
  SELECT
    d.id,
    d.date,
    COUNT(DISTINCT(dr.id)) AS total_invoices,
    ROUND(SUM(ir.qtt*ir.prix_ht), 2) AS HT,
    ROUND(SUM(ir.qtt*ir.prix_ht)*(100+i.tax)/100, 2) AS TTC
  FROM direct_debit d
  JOIN direct_debit_row dr ON dr.debit_id = d.id
  JOIN webfinance_invoices i ON i.id_facture = dr.invoice_id
  JOIN webfinance_invoice_rows ir ON ir.id_facture = i.id_facture
  GROUP BY d.id
  ORDER BY d.date DESC
')
  or die(mysql_error());

while ($debit = mysql_fetch_assoc($res)) {
  echo "<tr><td> <a href=\"detail.php?id=$debit[id]\">$debit[date]</a> </td>\n";
  echo "    <td> $debit[total_invoices] </td>\n";
  echo "    <td align=\"right\"> $debit[HT] &euro;</td>\n";
  echo "    <td align=\"right\"> $debit[TTC] &euro; </td>\n";
  echo "    <td><a href=\"sepa.php?debit_id=$debit[id]\">SEPA</a> </td>\n";
  echo "</tr>\n";
}

echo '</table>';

include("../bottom.php");

?>
