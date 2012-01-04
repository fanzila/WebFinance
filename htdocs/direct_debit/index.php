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

$roles = 'manager,employee';
include("../top.php");
include("nav.php");

$Invoice = new Facture();

echo '<h1>Pending direct debits</h1>';

$res = mysql_query(
  'SELECT invoice_id '.
  'FROM direct_debit_row '.
  "WHERE state='todo'")
  or die(mysql_error());
?>

<table border="1">
 <tr>
  <th>Company</th>
  <th>Reference</th>
  <th>Date</th>
  <th>Amount excl. VAT</th>
  <th>Amount incl. VAT</th>
 </tr>

<?
$total_ht  = 0;
$total_ttc = 0;
while ($invoice = mysql_fetch_assoc($res)) {
  $info = $Invoice->getInfos($invoice['invoice_id']);
  echo "<tr> <td> $info->nom_client </td>";
  echo "<td> <a href=\"../prospection/edit_facture.php?id_facture=$invoice[invoice_id]\">$info->num_facture</a> </td>";
  echo "<td> $info->nice_date_facture </td>";
  echo "<td> $info->nice_total_ht &euro; </td>";
  echo "<td> $info->nice_total_ttc &euro; </td>";
  echo "</tr>";

  $total_ht  += $info->nice_total_ht;
  $total_ttc += $info->nice_total_ttc;
}
?>

<tr>
  <td></td>
  <td></td>
  <td align="right"> <b>TOTAL</b> </td>
  <td> <?=$total_ht?> &euro; </td>
  <td> <?=$total_ttc?> &euro; </td>
</tr>
</table>

<form action="process.php" onsubmit="return confirm('Are you sure you want to process the direct debit?')">
  <input type="submit" name="debit" value="Mark invoices as debited">
</form>

<h1>Previous debits</h1>

<?
$res = mysql_query(
  'SELECT id, date '.
  'FROM direct_debit')
  or die(mysql_error());

while ($debit = mysql_fetch_assoc($res)) {
  echo "<a href=\"detail.php?id=$debit[id]\">$debit[date]</a> <br/>";
}

include("../bottom.php");

?>
