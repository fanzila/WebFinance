<?php
/*
* Copyright (C) 2013 Cyril Bouthors <cyril@boutho.rs>
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

require_once('php-sepa-direct-debit/SEPASDD.php');

function GenerateSepa($debit_id, $debit_type = null) {

	// Check $debit_id
	if(defined($debit_id) and !is_numeric($debit_id))
		die('Invalid $debit_id');

	$sepa 					= GetSepaContent($debit_id, $debit_type);
	$company_rib			= GetCompanyMaiRIB();

	$nombre_virements		= 0;
	$montant_total			= 0;
	$montant_total_centimes = 0;
	$chaine_totale			= "";
	$nb_erreurs				= 0;
	$erreurs_details		= "";

	define('NUMERO_EMETTEUR', '484779');
	define('RAISON_SOCIALE', 'SARL ISVTEC'); 
	define('SIRET','44875254300034');
	define('CODE_BIC', $company_rib->swift);
	define('CODE_IBAN', $company_rib->iban);
	define('CODE_ICS', $company_rib->ics);
	define('REF_REMISE',date('dmy')); 
	define('LONGUEUR_IBAN','34');
	define('LONGUEUR_BIC','34');

	$config = array("name" => RAISON_SOCIALE,
	"IBAN" => preg_replace('/\s+/', '', CODE_IBAN),
	"BIC" => preg_replace('/\s+/', '', CODE_BIC),
	"batch" => "true",
	"creditor_id" => preg_replace('/\s+/', '', CODE_ICS),
	"currency" => "EUR"
	);

	try{
		$SEPASDD = new SEPASDD($config);
	}catch(Exception $e){
		echo $e->getMessage();
	}	

	foreach ($sepa as $line) {	

		// On recherche les éléments pouvant bloquer la génération du paiement
		if(empty($config['IBAN']) OR empty($config['BIC']) OR empty($config['creditor_id'])) {
			$nb_erreurs++;
			$nb_erreurs_ligne++;
			$erreurs_details .= " - Aucun IBAN et/ou BIC et/ou ICS enregistré(s) pour la société <a href=\"/prospection/fiche_prospect.php?id=$line[id]\">$line[name]</a>.<br />";
		}

		if(strlen($line['iban']) > LONGUEUR_IBAN OR empty($line['iban']) ) {
			$nb_erreurs++;
			$nb_erreurs_ligne++;
			$erreurs_details .= " - Le code IBAN de \"".$line['name']."\" permettant de générer un virement d'un montant de ".$line['montant']." EUR contient ".strlen($line['iban'])." caractères au lieu de ".LONGUEUR_IBAN." max. (valeur constatée : ".$line['iban']."). Cette ligne est abandonnée.<br />";
		}
		if(strlen($line['bic']) > LONGUEUR_BIC OR empty($line['bic']) ) {
			$nb_erreurs++;
			$nb_erreurs_ligne++;
			$erreurs_details .= " - Le code BIC de \"".$line['name']."\" permettant de générer un virement d'un montant de ".$line['montant']." EUR contient ".strlen($line['bic'])." caractères au lieu de ".LONGUEUR_BIC." max. (valeur constatée : ".$line['bic']."). Cette ligne est abandonnée.<br />";
		}

		$montant_debit	= $line['montant'];		
		$payment = array(
			"name"				=> $line['name'],
			"IBAN"				=> $line['iban'],
			"BIC"				=> $line['bic'],
			"amount"			=> "$montant_debit",
			"type"				=> $line['debit_type'],
			"collection_date"	=> date('Y-m-d'),
			"mandate_id"		=> $line['client_sepaid'],
			"mandate_date"		=> $line['mandat_date'],
			"description"		=> $line['ref']
			);

			try{
				$SEPASDD->addPayment($payment);
			}catch(Exception $e){
				echo $e->getMessage();
			}

	}

	if($nb_erreurs > 0) { // Des erreurs bloquantes ont été detectées
		?>
		<div class="mess_err">
			<?=$nb_erreurs?> erreur(s) bloquante(s) détectée(s) pour la génération du fichier de remise :<br />
			<?=$erreurs_details?><br />
			Remise d'ordre de virement abandonnée <!-- ' -->
		</div>
		<br /><br />
		<?
		return false;
	}

	try{
		$chaine_totale = $SEPASDD->save();
	}catch(Exception $e){
		echo $e->getMessage();
	}

	$res = mysql_query("UPDATE direct_debit SET type = 'SEPA' WHERE id = $debit_id") or die(mysql_error());

	$myFile = sys_get_temp_dir() . "/sepa-$debit_id-$debit_type.xml";
	$fh = fopen($myFile, 'w')
		or die("can't open file");
	fwrite($fh, $chaine_totale);
	fclose($fh);

	return $myFile;
	header("Content-type: text/xml; charset=utf-8");

	//echo $chaine_totale;
}

function GetSepaContent($debit_id, $debit_type_asked) {

	$Invoice = new Facture();
	$Client = new Client();

	$res = mysql_query("SELECT id, invoice_id FROM direct_debit_row WHERE debit_id = $debit_id") or die(mysql_error());

	$total_ttc		= 0;
	$invoice_client = array();

	while ($invoice = mysql_fetch_assoc($res)) {

		$info = $Invoice->getInfos($invoice['invoice_id']);
		$total[$info->nom_client]['TTC'] += $info->total_ttc;

		// On enlève les caractères génants
		$iban 			= str_replace(",", "", $info->iban);
		$bic 			= str_replace(",", "", $info->bic);
		$client_iban	= preg_replace('/\s+/', '', $iban);
		$client_bic		= preg_replace('/\s+/', '', $bic);
		$client_name	= iconv('utf-8', 'ascii//TRANSLIT', $info->nom_client);

		$mandat_date	= GetMandateDate($info->id_client);
		$debit_type		= GetDebitType($info->id_client, $debit_id);

		// On définit les variables de la remise de virement
		$ref_paiement = "F:".$info->num_facture;
		$Client->Client($info->id_client);

		// Formatage du montant
		$montant = round($info->total_ttc, 2);
		$montant_centimes = round($info->total_ttc * 100, 0);

		if(array_key_exists($info->id_client, $invoice_client)) { 			
			$invoice_client[$info->id_client]['montant'] = $invoice_client[$info->id_client]['montant']+$montant_centimes;
			$invoice_client[$info->id_client]['ref']	 = $invoice_client[$info->id_client]['ref']."-".$ref_paiement;
		} else { 

			if(isset($debit_type)) {
				if($debit_type == $debit_type_asked) {

					$invoice_client[$info->id_client] = array(
						'id'			=> $info->id_client, 
						'montant'		=> $montant_centimes, 
						'ref'			=> $ref_paiement, 
						'client_sepaid' => $Client->sepa_mndtid,
						'iban'			=> $client_iban,
						'bic'			=> $client_bic,
						'name'			=> $client_name,
						'mandat_date'	=> $mandat_date,
						'debit_type'	=> $debit_type
						);	
					}
				}
			}
		}

	return $invoice_client;
}

function GetDebitType($id_client, $debit_id) {
	$req_debit_type = mysql_query("SELECT IF(COUNT(*) > 0, 'RCUR', 'FRST')
	FROM direct_debit_row ddr
		JOIN direct_debit dd ON dd.id = ddr.debit_id
		JOIN webfinance_invoices i ON i.id_facture = ddr.invoice_id
		WHERE state='done'
		AND i.id_client = $id_client
		AND dd.type = 'SEPA'
		AND dd.id != $debit_id
		") or die(mysql_error());
	return mysql_result($req_debit_type, 0);
}

function GetDebitTypeInADebit($debit_id) {
	
	$req 		= mysql_query("SELECT id, invoice_id FROM direct_debit_row WHERE debit_id = $debit_id") or die(mysql_error());
	$res		= array();
	$Invoice	= new Facture();
	
	while ($row = mysql_fetch_assoc($req)) {		
		$info = $Invoice->getInfos($row['invoice_id']);
		$type = GetDebitType($info->id_client, $debit_id);
		if($type == 'RCUR') $res['rcur']++;
		if($type == 'FRST') $res['frst']++;
	}
	return $res;
}

function GetMandateDate($id_client) {
	$req_mandat_date = mysql_query("SELECT DATE_FORMAT(dd.date, '%Y-%c-%d') 
		FROM direct_debit_row ddr
		JOIN direct_debit dd ON dd.id = ddr.debit_id
		JOIN webfinance_invoices i ON i.id_facture = ddr.invoice_id
		WHERE state='done'
		AND i.id_client = $id_client
		AND dd.type = 'SEPA'
		ORDER BY dd.date
		LIMIT 1;
	") or die(mysql_error());

	$res = mysql_result($req_mandat_date, 0);
	if(empty($res)) $res = date('Y-m-d');
	return $res;
}

function stripAccents($string){
	return iconv('utf-8', 'ascii//TRANSLIT', $string);
	}

	function FormatBancaire ($data, $longueur_donnee, $caractere_defaut = " ", $cadrage_data = "left") {
		// Si le séparateur est vide, on renvoie une erreur pour ne pas faire de boucle infinie
		if($caractere_defaut == NULL) {
			return false;
		}

		// Si la longueur est plus longue qu'autorisée, on la tronque
		if(strlen($data) > $longueur_donnee) {
			$data = substr($data, 0, $longueur_donnee);
		}

		// Si la longueur est inférieure on la complète par le séparateur
		if(strlen($data) < $longueur_donnee) {
			// On positionne les variables
			$completeur = "";
			$longueur_manquante = $longueur_donnee - strlen($data);

			// On crée la chaîne qui va completer la valeur de "$data"
			for($i=0; $i < $longueur_manquante; $i++) {
				$completeur .= $caractere_defaut;
			}

			// On complète "$data" selon la valeur de "$cadrage_data"
			if($cadrage_data == "left") {
				$data = $data.$completeur;
			} elseif ($cadrage_data == "right") {
				$data = $completeur.$data;
			} else {
				return false;
			}
		}

	// On renvoie le résultat
	return $data;
}

function GetCompanyMaiRIB() {
	$result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE type_pref='rib' AND owner=-1 LIMIT 1");
	$count = 1;
	$currencies = array();
	while (list($id_pref,$value) = mysql_fetch_array($result)) {
		$compte = unserialize(base64_decode($value));

		// Check account number.
		//
		// Algorythm is : take the bank code (5 digits) + desk
		// code (5 digits) + account number (10 digits or letters). You get Ã  19 char
		// long "number" (which may contain letters). Replace letters in the following way :
		// A,J => 1 / B,K,S => 2 / C,L,T => 3 / D,M,U => 4 / E,N,V => 5, F,O,W => 6 /
		// G,P,X => 7 / H,Q,Y => 8 / I,R,Z => 9. Add 00 to the 19 char number.
		//   Checksum number  = 97 - ((21 digit number) % 97)
		//
		// PHP cannot do this calculus with normal functions (number is too big)
		// MySQL can. So we use a query for that.
		$bignum = $compte->code_banque.$compte->code_guichet.$compte->compte."00";
		$bignum = preg_replace("/[AJ]/", "1 ", $bignum);
		$bignum = preg_replace("/[BKS]/", "2 ", $bignum);
		$bignum = preg_replace("/[CLT]/", "3 ", $bignum);
		$bignum = preg_replace("/[DMU]/", "4 ", $bignum);
		$bignum = preg_replace("/[ENV]/", "5", $bignum);
		$bignum = preg_replace("/[FOW]/", "6 ", $bignum);
		$bignum = preg_replace("/[GPX]/", "7 ", $bignum);
		$bignum = preg_replace("/[HQY]/", "8 ", $bignum);
		$bignum = preg_replace("/[IRZ]/", "9", $bignum);

		$check_key = mysql_query("SELECT 97 - ($bignum % 97)") or print(mysql_error());
		list($key) = mysql_fetch_array($check_key);
		mysql_free_result($check_key);

		if ($key != $compte->clef) {
			$img = "not_paid";
			$hover_text = addslashes(sprintf(_('Checksum fail on account number. Check digits entered. With this account number checksum should be %d'), $key));
		}

		// End check account number
		return $compte;
	}
}
?>
