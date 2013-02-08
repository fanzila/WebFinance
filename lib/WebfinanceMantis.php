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

class WebfinanceMantis {

	static private $_database = 'mantis';
	static private $_login = '';
	static private $_password = '';
        static private $_soapclient = null;
        static private $_support_type_not_invoiced = array
          (
            'Administratif' => true,
            'Forfait Prépayé' => true,
            'Infogérance - Inclus' => true,
            'ISVTEC - Interne' => true,

            // To be removed on Feb 2013
            'Kernel: mise à jour de sécurité' => true,
            'Kernel: crash, reboot et analyse' => true,
            'OS: mise à jour de sécurité' => true,
            'MySQL: mise à jour de sécurité' => true,
            'MySQL: optimisation de base' => true,
            'MySQL: sauvegardes' => true,
            'MySQL: restaurations (dans la limite 1/mois)' => true,
            'Virtualisation: fonctionnalité et mise à jour' => true,
            'Sauvegarde des fichiers, données et configurations' => true,
            'Redondance machine virtuelle' => true,
            'Serveur HTTP, FTP, mail: maintenance et mise à jour' => true,
            "Analyse des causes d'un piratage" => true,
            "Réparation suite à piratage d'une brique sous notre responsabilité" => true,
            'Monitoring' => true,
	    'Forfait prépayé' => true,
            'Commercial' => true,
        );

        function __construct() {

          $res = mysql_query(
            'SELECT type_pref, value '.
            'FROM webfinance_pref '.
            "WHERE type_pref IN ('mantis_login', 'mantis_password', 'mantis_api_url')")
            or die(mysql_error());

          while ($row = mysql_fetch_assoc($res))
            $mantis[$row['type_pref']] = $row['value'];

          if(isset($mantis['mantis_login'], $mantis['mantis_password'],
              $mantis['mantis_api_url']))
            {
              $this->_login    = $mantis['mantis_login'];
              $this->_password = $mantis['mantis_password'];
              $this->_soapclient = new SoapClient(null, array(
                                     'location' => $mantis['mantis_api_url'],
                                     'uri'      => 'ns1',
                                   ));
            }
        }

	function mantisIdToIdClient() {  

		$query = "SELECT id_client, id_mantis ".
                  "FROM webfinance.webfinance_clients ".
                  "WHERE id_mantis != 0";

		$result = mysql_query($query)
                  or die(mysql_error());

		$list = array();

		while($row = mysql_fetch_assoc($result))
                  $list[$row['id_mantis']] = $row['id_client'];

		// Special case
		$list[338] = 0;		//ISVTEC project
		$list[387] = 0;		//ISVTEC project
		$list[381] = 0;		//ISVTEC project
		$list[305] = 0;		//NIPLEX project
		$list[313] = 0;		//custemail project
		$list[327] = 0;		//GOM project		
		$list[295] = 96;	//double project for Apocope (Nespresso)

		return $list;
	}

	function fetchBillingInformation($year, $month, $mantis_project_id) {

		$mantisid = self::mantisIdToIdClient();

		$dataparam = date_parse_from_format('d-m-Y', "01-$month-$year");
		$startDate = mktime(0, 0, 0, $dataparam['month'], $dataparam['day'], $dataparam['year']);
		$endDate = strtotime("+1 month", $startDate); 
		
		// Select the Mantis MySQL database
		if(!mysql_select_db(self::$_database))
			throw new Exception(mysql_error());

                $where_mantis_project_id = '';
                if(isset($mantis_project_id))
                  $where_mantis_project_id = "AND bug.project_id = $mantis_project_id ";

		$req = 'SELECT bug.id, bug.summary, user.realname AS client, '.
			'  project.name AS project_name, ' .
			'  SUM(bugnote.time_tracking) AS time, bug.date_submitted, ' .
			'  handler.realname AS handler, project.id AS project_id, '.
			"  IF(custom_field_string.value IS NULL, 'À définir', custom_field_string.value) AS support_type ".
			'FROM mantis_bug_table bug '.
			'JOIN mantis_bugnote_table bugnote ON bug.id = bugnote.bug_id '.
			'JOIN mantis_project_table project ON bug.project_id = project.id '.
			'JOIN mantis_user_table user ON user.id = bug.reporter_id '.
			'LEFT JOIN mantis_user_table handler ON handler.id = bug.handler_id '.
			'LEFT JOIN mantis_custom_field_string_table custom_field_string ON custom_field_string.bug_id = bug.id '.
			'LEFT JOIN mantis_custom_field_table custom_field ON custom_field.id = custom_field_string.field_id '.
			"WHERE bugnote.date_submitted BETWEEN $startDate AND $endDate ".
			"AND (custom_field.name = 'Support type' OR custom_field.name IS NULL) ".
			"$where_mantis_project_id".
			'GROUP BY bugnote.bug_id '.
			'ORDER BY project.id';

		$res = mysql_query($req)
			or die(mysql_error());

		$billing = array();

		setlocale(LC_TIME, 'fr_FR.UTF8');

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

			$invoiced = true;
			$price = 55;
			$invoiced_time = $row['time'];
                        if(isset(self::$_support_type_not_invoiced[$row['support_type']]))
			{
				$invoiced = false;
				$price = 0;
				$invoiced_time = 0;
			}

                        $time_human_readable = sprintf('%dh%02d',
                                               floor(abs($row['time']) / 60),
                                               abs($row['time']) % 60);

			$billing[$webfinance_project_id][$row['id']] =
			array(
				'description'           => $description,
				'quantity'              => $row['time'] / 60,
				'price'                 => $price,
				'mantis_project_name'   => $row['project_name'],
				'id_client'             => $webfinance_project_id,
				'time'                  => $row['time'],
                                'time_human_readable'   => $time_human_readable,
				'invoiced_time'         => $invoiced_time,
				'mantis_ticket_summary' => $row['summary'],
				'mantis_project_id'     => $row['project_id'],
                                'support_type'          => $row['support_type'],
                                'invoiced'              => $invoiced,
			);
			
			// Process total time
			if(!isset($total_time[$webfinance_project_id]))
				$total_time[$webfinance_project_id] = 0;

			if($invoiced)
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
				'invoiced_time'         => - $time_to_deduce,
				'id_client'			    => $webfinance_project_id,
				'price'                 => $price,
				'mantis_project_name'   => '',
				'mantis_project_id'     => $row['project_id'],
				'invoiced'              => true,
			);
		}

		// Select the Webfinance MySQL database
		if (!mysql_select_db(WF_SQL_BASE))
			throw new Exception(mysql_error());

		return $billing;
	}

	function createAndSendInvoice($id_client, $prix_ht, $quantity, $items) {
		// Create invoice
		$Facture = new Facture();
		$invoice = array(
			'client_id' => $id_client,
			'rows'      => array(),
		);

		$id_facture = $Facture->create($invoice);
		
		// Get invoice payement and delivery type 
		$res = mysql_query(
                  'SELECT payment_method,delivery  '.
                  'FROM webfinance_invoices '.
                  "WHERE id_client = $id_client ".
                  "  AND type_doc = 'facture' " .
                  '  AND is_envoye = 1 '.
                  'ORDER BY id_facture DESC '.
                  'LIMIT 1')
			or die(mysql_error());
                $payment_method = 'unknown';
                $delivery_method = 'email';
		if(mysql_num_rows($res) > 0) {
			$type_payment_res = mysql_fetch_array($res);
			$payment_method = $type_payment_res['payment_method'];
			$delivery_method = $type_payment_res['delivery'];
		}

		// Get id_compte
		$result = mysql_query(
                  'SELECT id_pref,value '.
                  'FROM webfinance_pref '.
                  "WHERE type_pref='rib' ".
                  'LIMIT 1')
                  or die(mysql_error());
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
		mysql_query($q)
                  or die(mysql_error());
		
		// Add service rows to invoice
		$q = sprintf("INSERT INTO webfinance_invoice_rows (id_facture,description,prix_ht,qtt,ordre) ".
			"SELECT %d, '%s', %s, %s, if(max(ordre) is null, 1, max(ordre + 1)) ".
			"FROM webfinance_invoice_rows ".
			"WHERE id_facture=%d",
		$id_facture,
		mysql_real_escape_string($items),
		$prix_ht, $quantity, $id_facture);
		$result = mysql_query($q)
                  or die(mysql_error());
		mysql_query("UPDATE webfinance_invoices SET date_generated=NULL WHERE id_facture=".$id_facture) or die(mysql_error());

		if($payment_method == 'direct_debit') { 
			// Plan the invoice to be debited if needed

			if ($prix_ht*$quantity > 0)
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

		if ($prix_ht*$quantity == 0)
			$Facture->setPaid($id_facture);
		
		return true;
	}

        /**
         * Add a new project.
         *
         * @param Int client_id The Webfinance client id.
         *
         * @param Array $project A new ProjectData structure. See
         * mantis/api/soap/mc_project_api.php for more information
         *
         **/

        function createProject($client_id = 0, array $mantis_project = array())
        {
          if(empty($this->_soapclient))
            return true;

          $project_id = $this->_soapclient->mc_project_add($this->_login,
                        $this->_password, $mantis_project);

          mysql_query('UPDATE webfinance_clients '.
            "SET id_mantis = $project_id ".
            "WHERE id_client = $client_id")
            or die(mysql_error());
        }

        /**
         * Update project.
         *
         * @param integer $project_id A project's id
         * @param Array $mantis_project A new ProjectData structure
         *
         **/

        function updateProject($project_id = 0, array $mantis_project = array())
        {
          if(empty($this->_soapclient))
            return true;

          $this->_soapclient->mc_project_update($this->_login, $this->_password,
            $project_id, $mantis_project);
        }

        function createReport($year, $month, $id_client, $target = 'file')
        {
          global $pdf_title;

          require_once('InfogerancePdfReport.php');

          $client = new Client($id_client);

	  // Generate PDF filename
	  $filename = sys_get_temp_dir() .
            '/Rapport_infogerance_' . str_replace(' ', '_', $client->nom) .
            "_$year" . "_$month.pdf";

          $pdf_title = strftime("Rapport support professionnel $client->nom %B %Y", mktime(0, 0, 0, $month, 1, $year));

          $pdf = new InfogerancePdfReport();

          $pdf->AliasNbPages();
          $pdf->AddPage();
          $pdf->SetFont('Times','',12);

          $pdf->Write(5,utf8_decode(strftime("Voici le récapitulatif des interventions effectuées par le support professionnel ISVTEC au mois de %B %Y dans le cadre de votre contrat d'infogérance.\n\n", mktime(0, 0, 0, $month, 1, $year))));

          $pdf->Write(5,utf8_decode("Veuillez prendre note de l'entrée en vigueur de notre nouveau tarif horaire au 1er mars 2013 : 75") . chr(128) . utf8_decode(" HT.\n"));

          foreach($this->fetchBillingInformation($year, $month,
              $client->id_mantis) as $webfinance_id => $billing) {

            foreach($billing as $ticket_number => $ticket) {

              if($ticket_number == 0)
                continue;

              $url_ticket =
                "https://www.isvtec.com/infogerance/ticket/view.php?id=$ticket_number";

              $type = 'Inclus dans le forfait';
              if($ticket['invoiced'])
                $type = 'Hors périmètre de contrat';

              $pdf->SetFont('Times','B',12);
              $pdf->Write(5,utf8_decode("\n$ticket[mantis_ticket_summary]\n"));
              $pdf->SetFont('Times','',12);

              $pdf->Write(5,utf8_decode("Ticket #$ticket_number\n"),
                $url_ticket);

              $pdf->Write(5,
                utf8_decode("Temps passé: $ticket[time_human_readable]\n"));

              $pdf->Write(5,utf8_decode("$type\n"));
            }
          }

          if($target == 'file')
            $pdf->Output($filename, 'F');
          else
            $pdf->Output(basename($filename), 'I');

	  $pdf->Close();

          return($filename);
        }

}
?>
