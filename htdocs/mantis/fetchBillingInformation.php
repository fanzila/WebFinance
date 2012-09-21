<?php

  /*
   * Copyright (C) 2012 Cyril Bouthors <cyril@bouthors.org>
   *
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.
   *
   * This program is distributed in the hope that it will be useful, but
   * WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
   * Public License for more details.
   *
   * You should have received a copy of the GNU General Public License along
   * with this program. If not, see <http://www.gnu.org/licenses/>.
   *
   */

require_once('../inc/main.php');
$title = _('Mantis');
$roles='manager,accounting,employee';
require_once('../top.php');
require_once('../../lib/WebfinanceMantis.php');
?>

<script type="text/javascript" language="javascript"
  src="/js/ask_confirmation.js"></script>

<?

mysql_connect('localhost', 'root')
    or die(mysql_error());

mysql_query('SET character_set_results = utf8')
  or die(mysql_error());

mysql_select_db('mantis')
  or die(mysql_error());

$mantis = new WebfinanceMantis;

?>

<form action="fetchBillingInformation.php">
  <select name="month">
  <?
  $year = ( isset($_GET['year']) ? $_GET['year'] : strftime('%Y', time()));
  $month = ( isset($_GET['month']) ? $_GET['month'] : strftime('%m', time()));

  for($i=1; $i<=12; $i++) {
    $month_name = strftime('%B', mktime(0, 0, 0, $i));
    $selected='';
    if($i == $month)
      $selected='selected';
    echo "<option value=\"$i\" $selected> $month_name </option>\n";
  }
  ?>
  </select>

  <select name="year">
  <?
  for($i=2050; $i>=2012; $i--) {
    $selected='';

    if($i == $year)
      $selected='selected';

    echo "<option value=\"$i\" $selected> $i </option>\n";
  }
  ?>
  </select>

  <input type="submit" value="Search">

</form>

<table border="1">

 <tr>
  <th>Client</th>
  <th>Description</th>
  <th>Time</th>
  <th>Price</th>
 </tr>

<?

$date_start = "$year-$month-01";
$date_end = "$year-" . ($month + 1) . "-01";

// Print preview
foreach($mantis->fetchBillingInformation($date_start, $date_end)
  as $webfinance_id => $billing) {

  $total = 0;

  foreach($billing as $ticket_number => $ticket) {
    $url_ticket =
      "https://www.isvtec.com/infogerance/ticket/view.php?id=$ticket_number";

    $url_webfinance = '/prospection/fiche_prospect.php?onglet=biling&id=' .
      $mantis2webfinance[$ticket['mantis_project_id']];

    $price = round($ticket['price'] * $ticket['quantity'], 2);

    $time_human_readable = sprintf('%dh%02d',
                           floor(abs($ticket['time']) / 60),
                           abs($ticket['time']) % 60);

    echo "<tr>\n  <td> <a href=\"$url_webfinance\">$ticket[mantis_project_name]</a></td>\n";

    if($ticket_number == 0)
      echo "  <td> $ticket[mantis_ticket_summary] </td>\n";
    else
      echo "  <td> <a href=\"$url_ticket\"".
      ">$ticket[mantis_ticket_summary]</a> </td>\n";

    echo "  <td align=\"right\"> $time_human_readable</td>\n";
    echo "  <td align=\"right\"> $price&euro; </td>\n";
    echo "</tr>\n";

    $total += $ticket['time'];
  }

  $total_time_client_human_readable = sprintf('%dh%02d',
                                      floor($total / 60),
                                      $total % 60);

  $total_price = round($total * $ticket['price'] / 60, 2);

  echo "<tr> <td></td> <td align=\"right\"><b>TOTAL INVOICED</b></td> ".
  "<td align=\"right\"><b>$total_time_client_human_readable</b></td> ".
    "<td align=\"right\"><b>$total_price&euro;</b></td></tr>\n";
}

?>

</table>

<form action="sendInvoices.php" method="POST">
  <input type="submit" name="send_invoices" value="Send invoices to clients"
onclick="return ask_confirmation('<?= _('Do you really want to send the invoices to clients?') ?>')">
  <input type="hidden" name="month" value="<?=$month;?>" />
  <input type="hidden" name="year" value="<?=$year;?>" />

</form>

</body>
</html>
