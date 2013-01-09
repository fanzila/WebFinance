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
        );

        function __construct() {

          $res = mysql_query(
            'select type_pref, value '.
            'from webfinance_pref '.
            "where type_pref in ('mantis_login', 'mantis_password', 'mantis_api_url')")
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
		$list[313] = 0;		//custemail project
		$list[320] = 0;		//galaxya project
		$list[327] = 0;		//GOM project		
		$list[295] = 96;	//double project for Apocop (Nespresso)

		return $list;
	}

	function fetchBillingInformation($year, $month) {

		$mantisid = self::mantisIdToIdClient();

		$dataparam = date_parse_from_format('d-m-Y', "01-$month-$year");
		$startDate = mktime(0, 0, 0, $dataparam['month'], $dataparam['day'], $dataparam['year']);
		$endDate = strtotime("+1 month", $startDate); 
		
		// Select the Mantis MySQL database
		if(!mysql_select_db(self::$_database))
			throw new Exception(mysql_error());

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

			$billing[$webfinance_project_id][$row['id']] =
			array(
				'description'           => $description,
				'quantity'              => $row['time'] / 60,
				'price'                 => $price,
				'mantis_project_name'   => $row['project_name'],
				'id_client'			  	=> $webfinance_project_id,
				'time'                  => $row['time'],
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

	function createAndSendInvoice($id_client, $prix_ht, $items) {
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
		$prix_ht, 1, $id_facture);
		$result = mysql_query($q)
                  or die(mysql_error());
		mysql_query("UPDATE webfinance_invoices SET date_generated=NULL WHERE id_facture=".$id_facture) or die(mysql_error());

		if($payment_method == 'direct_debit') { 
			// Plan the invoice to be debited if needed

			if ($prix_ht > 0)
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

		if ($prix_ht == 0)
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

          mysql_query('update webfinance_clients '.
            "set id_mantis = $project_id ".
            "where id_client = $client_id")
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

}
?>