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

function GenerateCfonb($debit_id = null) {
  if(!is_numeric($debit_id))
    die('Invalid $debit_id');

	/**
	* On définit les variables
	*/
	$nombre_virements = 0;
	$montant_total = 0;
	$montant_total_centimes = 0;
	$chaine_totale = "";
	$nb_erreurs = 0;
	$erreurs_details = "";

	/**
	* Enregistrement et données de la remise d'ordres de virement
	* Enregistrement "Emetteur"
	*/
	$ligne = "";
	$ligne .= FormatBancaire("03", 2); // Code enregistrement - Constante à "03" (2 caractères)
	$ligne .= FormatBancaire(PRELEVEMENT_TYPE, 2); // Code opération - Virements ordinaires (2 caractères)
	$ligne .= FormatBancaire("", 8); // Zone réservée (8 caractères)
	$ligne .= FormatBancaire(NUMERO_EMETTEUR, 6); // Numéro d'émetteur ou d'identification (6 caractères)
	$ligne .= FormatBancaire("", 1); // Code CCD : inutile dans notre cas (1 caractères)
	$ligne .= FormatBancaire("", 6); // Zone réservée (6 caractères)
	$ligne .= FormatBancaire(date('dm').substr(date('y'), -1), 5); // Date (JJMMA) (5 caractères)
	$ligne .= FormatBancaire(stripAccents(RAISON_SOCIALE), 24); // Nom ou raison sociale du donneur d'ordre (24 caractères)
	$ligne .= FormatBancaire(stripAccents(REF_REMISE), 11); // Référence de la remise (7 caractères)
	$ligne .= FormatBancaire("", 15); // Zone réservée (17 caractères)
	$ligne .= FormatBancaire("E", 1); // Code monnaie - Constante à "E" (1 caractères)
	$ligne .= FormatBancaire("", 5); // Zone réservée (5 caractères)
	$ligne .= FormatBancaire(CODE_GUICHET_SOCIETE, 5); // Code guichet de la banque du conneur d'ordre (5 caractères)
	$ligne .= FormatBancaire(NUMERO_COMPTE_SOCIETE, 11); // Numéro de compte du donneur d'ordre (11 caractères)
	$ligne .= FormatBancaire("", 47); // Zone réservée (31 caractères)
	$ligne .= FormatBancaire(CODE_BANQUE, 5); // Code établissement de la banque du donneur d'ordre (5 caractères)
	//	$ligne .= FormatBancaire(SIRET, 16); // Identifiant du donneur d'ordre (16 caractères)
	//	$ligne .= FormatBancaire("", 31); // Zone réservée (31 caractères)
	$ligne .= FormatBancaire("", 6); // Zone réservée (6 caractères)

	// On vérifie l'intégrité de la chaîne
	if(strlen($ligne) != LONGUEUR_ENREGISTREMENT) {
		$nb_erreurs++;
		$erreurs_details .= " - La première ligne \"Emetteur\" contient ".strlen($ligne)." caractères au lieu de ".LONGUEUR_ENREGISTREMENT.". La remise d'ordres de virement ne peut être poursuivie.<br />";
	}

	// On complète la chaîne totale
	$chaine_totale .= $ligne."\n";

	$Invoice = new Facture();

	$res = mysql_query(
	'SELECT id, invoice_id '.
		'FROM direct_debit_row '.
		"WHERE debit_id = $debit_id")
		or die(mysql_error());

	$total_ttc = 0;

	while ($invoice = mysql_fetch_assoc($res)) {
		$info = $Invoice->getInfos($invoice['invoice_id']);
		$total[$info->nom_client]['TTC'] += $info->total_ttc;

		// On positionne le nombre d'erreurs de cette ligne à zéro
		$nb_erreurs_ligne = 0;

		// On définit les variables de la remise de virement
		$ref_paiement = "F:".$info->num_facture;

		// Formatage du montant
		$montant = round($info->total_ttc, 2);
		$montant_centimes = round($info->total_ttc * 100, 0);

		// On enlève les caractères génants
		$rib_titulaire = str_replace(",", "", $info->rib_titulaire);
		$rib_banque = str_replace(",", "", $info->rib_banque);

		// On recherche les éléments pouvant bloquer la génération du paiement
		if(strlen($info->rib_code_banque) != LONGUEUR_CODE_BANQUE) {
			$nb_erreurs++;
			$nb_erreurs_ligne++;
			$erreurs_details .= " - Le code banque de \"".$info->nom_client."\" permettant de générer un virement d'un montant de ".$montant." EUR contient ".strlen($info->rib_code_banque)." caractères au lieu de ".LONGUEUR_CODE_BANQUE." (valeur constatée : ".$info->rib_code_banque."). Cette ligne est abandonnée.<br />";
		}
		if(strlen($info->rib_code_guichet) != LONGUEUR_CODE_GUICHET) {
			$nb_erreurs++;
			$nb_erreurs_ligne++;
			$erreurs_details .= " - Le code guichet de \"".$info->nom_client."\" permettant de générer un virement d'un montant de ".$montant." EUR contient ".strlen($info->rib_code_guichet)." caractères au lieu de ".LONGUEUR_CODE_GUICHET." (valeur constatée : ".$info->rib_code_guichet."). Cette ligne est abandonnée.<br />";
		}
		if(strlen($info->rib_code_compte) != LONGUEUR_NUMERO_COMPTE) {
			$nb_erreurs++;
			$nb_erreurs_ligne++;
			$erreurs_details .= " - Le numéro de compte de \"".$info->nom_client."\" permettant de générer un virement d'un montant de ".$montant." EUR contient ".strlen($info->rib_code_compte)." caractères au lieu de ".LONGUEUR_NUMERO_COMPTE." (valeur constatée : ".$info->rib_compte."). Cette ligne est abandonnée.<br />";
		}

		/**
		* Enregistrement et données de la remise d'ordres de virement
		* Enregistrement "Destinataire"
		*/
		
		$ligne = "";
		$ligne .= FormatBancaire("06", 2); // Code d'enregistrement - Constante à "06" (2 caractères)
		$ligne .= FormatBancaire(CODE_OPERATION, 2); // Code opération (2 caractères)
		$ligne .= FormatBancaire("", 8); // Zone réservée (8 caractères)
		$ligne .= FormatBancaire(NUMERO_EMETTEUR, 6); // Numéro d'émetteur (6 caractères)
		$ligne .= FormatBancaire($ref_paiement, 12); // Référence (12 caractères)
		$ligne .= FormatBancaire(stripAccents($info->nom_client), 24); // Nom/Raison sociale du bénéficaire (24 caractères)
		$ligne .= FormatBancaire(stripAccents($info->rib_banque), 20); // Domicialiation : facultatif (24 caractères)
		$ligne .= FormatBancaire("", 12); // Déclaration à la balance des paiements : ??????? (8 caractères)
		$ligne .= FormatBancaire($info->rib_code_guichet, 5); // Code guichet bénéficiaire (5 caractères)
		$ligne .= FormatBancaire($info->rib_code_compte, 11); // Numéro de compte bénéficiaire (11 caractères)
		$ligne .= FormatBancaire($montant_centimes, 16, "0", "right"); // Montant (16 caractères)
		$ligne .= FormatBancaire($ref_paiement, 31); // Libellé (31 caractères)
		$ligne .= FormatBancaire($info->rib_code_banque, 5); // Code établissement bénéficiaire (5 caractères)
		$ligne .= FormatBancaire("", 6); // Zone réservée (6 caractères)

		// On vérifie l'intégrité de la chaîne
		if(strlen($ligne) != LONGUEUR_ENREGISTREMENT) {
			$nb_erreurs++;
			$nb_erreurs_ligne++;
			$erreurs_details .= " - La ligne de virement de \"".$info->nom_client."\" d'un montant de ".$montant." EUR contient ".strlen($ligne)." caractères au lieu de ".LONGUEUR_ENREGISTREMENT.". Cette ligne est abandonnée.<br />";
		}

		if($nb_erreurs_ligne == 0) { // Si cette ligne n'a pas générée d'erreur
			// On compte le nombre de virements à effectuer
			$nombre_virements++;

			// On additionne le montant total
			$montant_total += $montant;

			// On complèté la chaîne totale
			$chaine_totale .= $ligne."\n";

			$montant_total_centimes += $montant_centimes;
		} 

	}

	// On calcule le montant total en centimes
	//$montant_total_centimes = round($total_ttc * 100, 0);

	/**
	* Enregistrement et données de la remise d'ordres de virement
	* Enregistrement "Total"
	*/
	$ligne = "";
	$ligne .= FormatBancaire("08", 2); // Code enregistrement - constante à "08" (2 caractères)
	$ligne .= FormatBancaire(CODE_OPERATION, 2); // Code opération (2 caractères)
	$ligne .= FormatBancaire("", 8); // Zone réservée (8 caractères)
	$ligne .= FormatBancaire(NUMERO_EMETTEUR, 6); // Numéro d'émetteur (6 caractères)
	$ligne .= FormatBancaire("", 84); // Zone réservée (12 caractères)
	$ligne .= FormatBancaire($montant_total_centimes, 16, "0", "right"); // Montant de la remise (16 caractères)
	$ligne .= FormatBancaire("", 42); // Zone réservée (31 caractères)

	// On vérifie l'intégrité de la chaîne
	if(strlen($ligne) != LONGUEUR_ENREGISTREMENT) {
		$nb_erreurs++;
		$erreurs_details .= " - La dernière ligne \"Total\" contient ".strlen($ligne)." caractères au lieu de ".LONGUEUR_ENREGISTREMENT.". La remise d'ordres de virement ne peut être poursuivie.<br />";
	}

	// On complète la chaîne totale
	$chaine_totale .= $ligne."\n";

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


        $myFile = sys_get_temp_dir() . "/cfonb-$debit_id.txt";
        $fh = fopen($myFile, 'w')
          or die("can't open file");
        fwrite($fh, $chaine_totale);
        fclose($fh);

        header('Content-Type: application/octet-stream');

        if (preg_match('/MSIE 5.5/', $_ENV['HTTP_USER_AGENT']) || preg_match('/MSIE 6.0/', $_ENV['HTTP_USER_AGENT'])){ 
          header('Content-Disposition: filename = "'.$myFile.'"'); 
        } else { 
          header('Content-Disposition: attachment; filename = "'.$myFile.'"'); 
        }
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($myFile));
        readfile($myFile);
        unlink($myFile);
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
