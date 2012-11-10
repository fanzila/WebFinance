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

class WebfinanceMantis {

	static private $_mantis_database = 'mantis';

	function mantisIdToIdClient() {  

		mysql_select_db('webfinance');
		$query = "SELECT id_client, id_mantis FROM webfinance_clients";
		$result = mysql_query($query) or die(mysql_error());
		$list = array();
		while($row = mysql_fetch_assoc($result))
		{
			$list[$row['id_mantis']] = $row['id_client']; 
		}

		//special case
		$list[338] = 0;		//ISVTEC project
		$list[387] = 0;		//ISVTEC project
		$list[381] = 0;		//ISVTEC project
		$list[305] = 0;		//NIPLEX project
		$list[295] = 96;	//double project for Apocop (Nespresso)

		return $list;
	}

	function fetchBillingInformation($start_date, $end_date) {

		$mantisid = self::mantisIdToIdClient();

		// Select the Mantis MySQL database
		if(!mysql_select_db(self::$_mantis_database))
			throw new Exception(mysql_error());

		$start_date = mysql_real_escape_string($start_date) . ' 00:00:00';
		$end_date   = mysql_real_escape_string($end_date)   . ' 00:00:00';

		$res = mysql_query('SELECT bug.id, bug.summary, user.realname AS client, '.
			'  project.name AS project_name, ' .
			'  SUM(bugnote.time_tracking) AS time, bug.date_submitted, ' .
			'  handler.realname AS handler, project.id AS project_id '.
			'FROM mantis_bug_table bug '.
			'JOIN mantis_bugnote_table bugnote ON bug.id = bugnote.bug_id '.
			'JOIN mantis_project_table project ON bug.project_id = project.id '.
			'JOIN mantis_user_table user ON user.id = bug.reporter_id '.
			'JOIN mantis_user_table handler ON handler.id = bug.handler_id '.
			'WHERE '.
			"  bugnote.last_modified BETWEEN UNIX_TIMESTAMP('$start_date') ".
			"    AND UNIX_TIMESTAMP('$end_date') ".
			'GROUP BY bugnote.bug_id '.
			'ORDER BY project.id')
			or die(mysql_error());

		$billing = array();

		setlocale(LC_TIME, 'fr_FR');

		// Prepare billing information
		while($row = mysql_fetch_assoc($res)) {

			$webfinance_project_id = $mantisid[$row['project_id']];

			if(!isset($webfinance_project_id))
				die("Unable to fetch information for project $row[project_name] ".
				"(Please add the mantis id: $row[project_id] to client: $row[project_name])");

			// Skip internal, non billable projects
			if($webfinance_project_id == 0)
				continue;

			$time = sprintf('%dh%02d', floor($row['time'] / 60), $row['time'] % 60);

			$description = sprintf("%s d'infogérance ponctuelle.\n" .
				"Traitement du ticket #%d ouvert le %s: %s",
			$time,
			$row['id'],
			strftime('%x', $row['date_submitted']),
			$row['summary']);


			if(!isset($billing[$webfinance_project_id]))
				$billing[$webfinance_project_id] = array();

			$billing[$webfinance_project_id][$row['id']] =
			array(
				'description'           => $description,
				'quantity'              => $row['time'] / 60,
				'price'                 => 55,
				'mantis_project_name'   => $row['project_name'],
				'id_client'			  => $webfinance_project_id,
				'time'                  => $row['time'],
				'mantis_ticket_summary' => $row['summary'],
				'mantis_project_id'     => $row['project_id'],
			);

			// Process total time
			if(!isset($total_time[$webfinance_project_id]))
				$total_time[$webfinance_project_id] = 0;

			$total_time[$webfinance_project_id] += $row['time'];
		}

		// Process total time
		foreach($total_time as $webfinance_project_id => $time) {

			$time_to_deduce = 15;
			if($time < 15)
				$time_to_deduce = $time;

			$description =
				"Déduction de l'infogérance ponctuelle comprise dans le contrat";

			$billing[$webfinance_project_id][0] = array(
				'description'           => $description,
				'mantis_ticket_summary' => $description,
				'quantity'              => - $time_to_deduce / 60,
				'time'                  => - $time_to_deduce,
				'id_client'			    => $webfinance_project_id,
				'price'                 => 55,
				'mantis_project_name'   => '',
				'mantis_project_id'     => $row['project_id'],
			);
		}

		// Select the Webfinance MySQL database
		if (!mysql_select_db(WF_SQL_BASE))
			throw new Exception(mysql_error());

		return $billing;
	}

	function createAndSendInvoice($id_client, $prix_ht, $items) {
		
		$month = date('m/Y');
		$description = "Infogérance ponctuelle $month
Détails des tickets ci-dessous : \n$items";
		
		// Create invoice
		$Facture = new Facture();
		$invoice = array(
			'client_id' => $id_client,
			'rows'      => array(),
		);

		$id_facture = $Facture->create($invoice);
		
		// Get invoice payement and delivery type 
		$res = mysql_query("SELECT payment_method,delivery  FROM webfinance_invoices WHERE id_client = $id_client AND type_doc = 'facture' AND is_envoye = 1 ORDER BY id_facture DESC LIMIT 1")
			or wf_mysqldie();
		if(mysql_num_rows($res) > 0) {
			$type_payment_res = mysql_fetch_array($res);
			$payment_method = $type_payment_res['payment_method'];
			$delivery_method = $type_payment_res['delivery'];
		} else {
			$payment_method = 'unknown';
			$delivery_method = 'email';
		}

		// Get id_compte
		$result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE type_pref='rib' LIMIT 1") or die(mysql_error());
		$cpt = mysql_fetch_object($result);
		$id_compte = $cpt->id_pref;

		// Input facture paremeters
		$q = sprintf("UPDATE webfinance_invoices SET ".
			"is_paye=%d, ".
			"is_envoye=%d, ".
			"ref_contrat='%s', ".
			"payment_method='%s', ".
			"id_compte='%s' ".
			"WHERE id_facture=%d",
		0,
		0,
		'INFOGERANCE',
		$payment_method,
		$id_compte,
		$id_facture);
		mysql_query($q) or die(mysql_error());
		
		// Add service rows to invoice
		$q = sprintf("INSERT INTO webfinance_invoice_rows (id_facture,description,prix_ht,qtt,ordre) ".
			"SELECT %d, '%s', %s, %s, MAX(ordre) + 1 ".
			"FROM webfinance_invoice_rows ".
			"WHERE id_facture=%d",
		$id_facture,
		mysql_real_escape_string($description),
		$prix_ht, 1, $id_facture);
		$result = mysql_query($q) or die(mysql_error());
		mysql_query("UPDATE webfinance_invoices SET date_generated=NULL WHERE id_facture=".$id_facture) or die(mysql_error());

		if($payment_method == 'direct_debit') { 
			// Plan the invoice to be debited if needed
			mysql_query(
			"INSERT INTO direct_debit_row ".
				"SET invoice_id = $id_facture, ".
				"    state='todo'")
				or die(mysql_error());
			// Flag invoice as paid 
			$Facture->setPaid($id_facture);
		}

		// Manage invoice delivery and send by email to client 
		if($delivery_method == 'email') {
			$Facture->sendByEmail($id_facture);
		} elseif ($delivery_method == 'postal') {
			$send_mail_print_invoice=true;
			$attachments[] = $Facture->generatePDF($id_facture, true);
			$Facture->setSent($id_facture);
		}
		
		return true;
	}

}

?>
