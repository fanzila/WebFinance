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
		<th>Support type</th>
		<th>Project / sub project</th>
		<th>Description</th>
		<th align="right">Time</th>
		<th>Invoiced&nbsp;time</th>
		<th>Price</th>
		<th>Report</th>
		<th>Result</th>
	</tr>

	<?
	$total_end = 0;
	$total_price_end = 0;
	$invoice_description = strftime(
	  "Support professionnel hors périmètre de contrat\nPériode: %B %Y",
	  mktime(0, 0, 0, $month, 1, $year));

	// Print preview
	foreach($mantis->fetchBillingInformation($year, $month)
          as $webfinance_id => $billing) {

          $url_webfinance = "/prospection/fiche_prospect.php?id=$webfinance_id";

          // Check that the client has an email address
          $client = new Client($webfinance_id);
          if(empty($client->email))
            die("Email address not set for client <a href=\"$url_webfinance\">$client->nom</a>");

		$total = 0;
		$description = strftime("Détails des interventions du support professionnel pour le mois de %B %Y : \n", mktime(0, 0, 0, $month, 1, $year));
		
		foreach($billing as $ticket_number => $ticket) {

			$url_ticket =
				"https://support.isvtec.com/view.php?id=$ticket_number";

			$price = round($ticket['price'] * $ticket['quantity'], 2);

                        # Convert time as human readable
			$invoiced_time_human_readable = sprintf('%dh%02d',
                                               floor(abs($ticket['invoiced_time']) / 60),
                                               abs($ticket['invoiced_time']) % 60);

                        # Define background color based on invoice
                        $color='white';
                        if(isset($ticket['invoiced']) and !$ticket['invoiced']) {
                          $color='red';
                        }

                        echo "<tr>\n  <td bgcolor=\"$color\"> $ticket[support_type] </td>\n";
                        echo "  <td> $ticket[mantis_project_name] ";

                        if(!empty($ticket['mantis_subproject_name']))
                          echo "/ $ticket[mantis_subproject_name]";
                        echo "</td>\n";

			if($ticket_number == 0)
				echo "  <td> $ticket[mantis_ticket_summary] </td>\n";
			else
				echo "  <td> <a href=\"$url_ticket\"".
				">$ticket[mantis_ticket_summary] #$ticket_number</a> </td>\n";

			echo "  <td align=\"right\"> $ticket[time_human_readable]</td>\n";
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

			$description .= "$ticket[mantis_ticket_summary], $time_human_readable$type\n";

			if(!empty($ticket['mantis_project_name']))
				$client_name = $ticket['mantis_project_name'];
		}

		$total_end += $total;
		$total_time_client_human_readable_end = sprintf('%dh%02d',
                                                        floor($total_end / 60),
                                                        $total_end % 60);

                $total_price_end += $total_price;
		
		$total_time_client_human_readable = sprintf('%dh%02d',
                                                    floor($total / 60),
                                                    $total % 60);

		$total_price = round(
                  round($total / 60, 2) * 75,
                  2);

		echo "<tr bgcolor=\"lightblue\"> <td colspan=\"2\"></td> <td align=\"right\"><b>TOTAL <a href=\"$url_webfinance&onglet=billing\">$client_name</a> </b></td> ".
		"<td align=\"right\">  </td>\n" .
		"<td align=\"right\"><b>$total_time_client_human_readable</b></td> ".
		"<td align=\"right\"><b>$total_price&euro;</b></td>\n" .
		"<td align=\"right\"><a href=\"report.php?id_client=$ticket[id_client]&year=$year&month=$month\">Rapport</a> <b>";

                if(isset($_POST['action']) && $_POST['action'] == 'send')
                {
                  // Send report by email
                  $mantis->sendReportByEmail($year, $month, $webfinance_id)
                    or die("Unable to send report for client ID $webfinance_id");

                  # Temp hack for 'Bayard Presse'
                  if($ticket['id_client'] == 552)
                    {
                      echo "</b></td></tr>\n";
                      continue;
                    }

                  // Send invoice by email
                    if($mantis->createAndSendInvoice(
                        $ticket['id_client'],
                        $total_price,
                        1,
                        $invoice_description))
                      echo 'Sent';
                }
		echo "</b></td></tr>\n";
	}

	?>
<tr style="background:#dddddd;" align="right"><td colspan="5" align="right"><b>Total:</b> <?=$total_price_end?>&euro; - <?=$total_time_client_human_readable_end?></td></tr>
</table>
<br />
<font color="red"><blink><b>> A générer UNE seule fois par mois <</b></blink></font><br />
<form action="fetchBillingInformation.php?month=<?=$month?>&year=<?=$year?>" method="POST">
	<input type="submit" name="send_invoices" value="Send invoices and reports to clients"
	onclick="return ask_confirmation('<?= _('Do you really want to send the invoices to clients?') ?>')">
	<input type="hidden" name="month" value="<?=$month;?>" />
	<input type="hidden" name="year" value="<?=$year;?>" />
	<input type="hidden" name="action" value="send" />
</form>
<br />
<br />
</body>
</html>
