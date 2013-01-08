<?php

/*
* Copyright (C) 2012-2013 Cyril Bouthors <cyril@bouthors.org>
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
must_login();
setlocale(LC_NUMERIC, "en_US.UTF8");
?>

<script type="text/javascript" language="javascript"
src="/js/ask_confirmation.js"></script>

<?

mysql_connect('localhost', 'root')
	or die(mysql_error());

mysql_query('SET character_set_results = utf8')
	or die(mysql_error());

$mantis = new WebfinanceMantis;

mysql_select_db('mantis')
	or die(mysql_error());

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
for($i=2020; $i>=2009; $i--) {
	$selected='';

	if($i == $year)
		$selected='selected';

	echo "<option value=\"$i\" $selected> $i </option>\n";
}
?>
</select>

<input type="submit" value="Search">

</form>
<br /><br />
<table width="100%" border="0" cellspacing="0" cellpadding="5">
<tr class="row_header">
		<th>Client</th>
		<th>Support type</th>
		<th>Description</th>
		<th>Time</th>
		<th>Invoiced&nbsp;time</th>
		<th>Price</th>
		<th>Result</th>
	</tr>

	<?
	$action = false;
	if(isset($_GET['action'])) { 
		if($_GET['action'] == 'send') {
			$action = 'send';
		}
	}
	
	$total_end = 0;
	// Print preview
	foreach($mantis->fetchBillingInformation($year, $month)
	as $webfinance_id => $billing) {

		$total = 0;
		$description = '';
		
		foreach($billing as $ticket_number => $ticket) {

			$url_ticket =
				"https://www.isvtec.com/infogerance/ticket/view.php?id=$ticket_number";

			$url_webfinance = '/prospection/fiche_prospect.php?onglet=billing&id='.$ticket['id_client'];

			$price = round($ticket['price'] * $ticket['quantity'], 2);

                        # Convert time as human readable
			$time_human_readable = sprintf('%dh%02d',
                                               floor(abs($ticket['time']) / 60),
                                               abs($ticket['time']) % 60);

			$invoiced_time_human_readable = sprintf('%dh%02d',
                                               floor(abs($ticket['invoiced_time']) / 60),
                                               abs($ticket['invoiced_time']) % 60);

                        # Define background color based on invoice
                        $color='white';
                        if(isset($ticket['invoiced']) and !$ticket['invoiced']) {
                          $color='red';
                        }

			echo "<tr>\n<td> <a href=\"$url_webfinance\">$ticket[mantis_project_name]</a></td>\n";

                        echo "  <td bgcolor=\"$color\"> $ticket[support_type] </td>\n";

			if($ticket_number == 0)
				echo "  <td> $ticket[mantis_ticket_summary] </td>\n";
			else
				echo "  <td> <a href=\"$url_ticket\"".
				">$ticket[mantis_ticket_summary] #$ticket_number</a> </td>\n";

			echo "  <td align=\"right\"> $time_human_readable</td>\n";
			echo "  <td align=\"right\"> $invoiced_time_human_readable</td>\n";

			$color='white';
			if($price==0)
				$color='red';
			echo "  <td bgcolor=\"$color\" align=\"right\"> $price&euro; </td>\n";

			echo "</tr>\n";

			$type = ' (inclus dans le forfait)';
                        if($ticket['invoiced']) {
				$total += $ticket['time'];
				$type = '';
			}

			if($ticket_number>0)
				$description.= "Ticket #$ticket_number: ";

			$description .= "$ticket[mantis_ticket_summary], $time_human_readable$type, ${price} €\n";
		}

		// echo "<tr><td><pre>$description </pre> </td> </tr>";

		$total_end = $total+$total_end; 
		$total_time_client_human_readable_end = sprintf('%dh%02d',
		floor($total_end / 60),
		$total_end % 60);
		
		if($total_price > 0) 
			$total_price_end =  $total_price+$total_price_end; 
		
		$total_time_client_human_readable = sprintf('%dh%02d',
		floor($total / 60),
		$total % 60);

		$total_price = round($total * $ticket['price'] / 60, 2);

		echo "<tr> <td colspan=\"3\"></td> <td align=\"right\"><b>TOTAL</b></td> ".
		"<td align=\"right\"><b>$total_time_client_human_readable</b></td> ".
		"<td align=\"right\"><b>$total_price&euro;</b></td>\n" .
		"<td align=\"right\"><b>";
		if($total_price > 1 && $action == 'send') { if($mantis->createAndSendInvoice($ticket['id_client'], $total_price, $description)) { echo 'Sent'; } }
		echo "</b></td></tr>\n";
	}

	?>
<tr style="background:#dddddd;" align="right"><td colspan="6" align="right"><b>Total:</b> <?=$total_price_end?>&euro; - <?=$total_time_client_human_readable_end?></td></tr>
</table>
<br />
<font color="red"><blink><b>> A générer UNE seule fois par mois <</b></blink></font><br />
<form action="fetchBillingInformation.php?month=<?=$month?>&year=<?=$year?>&action=send" method="POST">
	<input type="submit" name="send_invoices" value="Send invoices to clients"
	onclick="return ask_confirmation('<?= _('Do you really want to send the invoices to clients?') ?>')">
	<input type="hidden" name="month" value="<?=$month;?>" />
	<input type="hidden" name="year" value="<?=$year;?>" />

</form>
<br />
<br />
</body>
</html>
