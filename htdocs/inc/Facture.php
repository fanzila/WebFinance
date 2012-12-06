<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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

require_once("WFO.php");
require_once(dirname(__FILE__)."/../inc/main.php");
require_once("/usr/share/php/libphp-phpmailer/class.phpmailer.php");
require_once("/usr/share/fpdf/fpdf.php");

class Facture extends WFO {
  function Facture() {
  }

  function _markForRebuild($id) {
    $this->SQL("UPDATE webfinance_invoices SET date_generated=NULL,pdf_file='' WHERE id_facture=$id");
  }

  function addLigne($id_facture, $desc, $pu_ht, $qtt) {
    $desc = preg_replace("/\'/", "\\'", $desc);

    # Fetch order
    $result = $this->SQL('select if(max(ordre) is NULL, 1, max(ordre + 1)) '.
              'from webfinance_invoice_rows '.
              "where id_facture = $id_facture");
    list($order) = mysql_fetch_array($result);

    $this->SQL('INSERT INTO webfinance_invoice_rows '.
      '(date_creation, id_facture, description, pu_ht, qtt, ordre) '.
      "VALUES(now(), $id_facture, '$desc', '$pu_ht', $qtt, $ordre)");
    $this->_markForRebuild($id_facture);
  }

  function getTotal($id_facture) {
    $result = $this->SQL("SELECT sum(qtt*pu_ht) FROM webfinance_invoice_rows WHERE id_facture=$id_facture");
    list($total) = mysql_fetch_array($result);
    mysql_free_result($result);

    return $total;
  }

  function exists($id_facture=null){
    if ($id_facture == "") { return 0; }
    $result = $this->SQL("SELECT count(*) FROM webfinance_invoices WHERE id_facture=$id_facture");
    list($exists) = mysql_fetch_array($result);
    return $exists;
  }


  function getInfos($id_facture) {
    if (!is_numeric($id_facture)) {
      die("Facture:getInfos no id");
    }
    $result = $this->SQL("SELECT c.id_client AS id_client, c.nom AS nom_client, c.addr1, c.addr2, c.addr3, c.rcs, c.capital, c.cp, c.ville, c.rib_titulaire, c.rib_banque, c.rib_code_banque, c.rib_code_guichet, c.rib_code_compte, c.rib_code_cle, c.vat_number, c.pays, f.periodic_next_deadline, f.type_doc, f.delivery, f.payment_method,
                                  date_format(f.date_created,'%d/%m/%Y') AS nice_date_created,
                                  date_format(f.date_paiement, '%d/%m/%Y') AS nice_date_paiement,
                                  date_format(f.date_sent, '%d/%m/%Y') AS nice_date_sent,
                                  date_format(f.date_facture, '%d/%m/%Y') AS nice_date_facture,
                                  unix_timestamp(f.date_facture) AS timestamp_date_facture,
                                  unix_timestamp(f.date_paiement) AS timestamp_date_paiement,
                                  unix_timestamp(f.date_sent) AS timestamp_date_sent,
                                  date_format(f.date_facture, '%Y%m') AS mois_facture,
                                  UPPER(LEFT(f.type_doc, 2)) AS code_type_doc,
                                  is_envoye AS is_sent,
                                  f.type_paiement, f.is_paye, f.is_abandoned, f.ref_contrat, f.extra_top, f.extra_bottom, f.num_facture, f.period, f.tax, f.id_compte, f.exchange_rate, f.*
                           FROM webfinance_clients AS c, webfinance_invoices AS f
                           WHERE f.id_client=c.id_client
                           AND f.id_facture=$id_facture")
		or die(mysql_error());

    if(mysql_num_rows($result)!=1) {
      error_log('Unknown invoice ' . $id_facture);
      exit(1);
    }

    $facture = mysql_fetch_object($result)
		or die(mysql_error());

    $result = $this->SQL("SELECT id_facture_ligne,prix_ht,qtt,description FROM webfinance_invoice_rows WHERE id_facture=$id_facture ORDER BY ordre")
		or die(mysql_error());

    $facture->lignes = Array();
    $total = 0;
    $count = 0;
    while ($el = mysql_fetch_object($result)) {
      array_push($facture->lignes, $el);
      $total += $el->qtt * $el->prix_ht;
      $count++;
    }
    mysql_free_result($result);
    $facture->taxe = $facture->tax;
    $facture->nb_lignes = $count;
    $facture->total_ht = $total;
    //$facture->total_ttc = $total*1.196;
    $facture->total_ttc = $total+($total*$facture->taxe)/100;
    $facture->nice_total_ht = sprintf("%.2f", $facture->total_ht);
    $facture->nice_total_ttc = sprintf("%.2f", $facture->total_ttc);
    // If an invoice has been send or is paid, then we SHANT CHANGE IT
    $facture->immuable = $facture->is_paye || $facture->is_sent;

    return $facture;
  }

  /** Marque une facture comme "payée"
   */
  function setPaid($id_facture) {
	  mysql_query('UPDATE webfinance_invoices '.
				  'SET date_paiement=NOW() ,is_paye=1 '.
				  "WHERE id_facture=$id_facture")
		  or die(mysql_error());
  }


  /** Renvoie vrai si la facture est générée au format PDF
    */
  function hasPdf($id) {
    $result = $this->SQL("SELECT pdf_file FROM webfinance_invoices WHERE id_facture=$id");
    list($file) = mysql_fetch_array($result);
    mysql_free_result($result);

    if (file_exists($file))
      return true;
    else
      return false;
  }

  function getTransactions($id_invoice){
    $trs=array();
    $q = $this->SQL("SELECT id_transaction , text FROM webfinance_transaction_invoice AS wf_tr_inv LEFT JOIN webfinance_transactions AS wf_tr ON (wf_tr_inv.id_transaction = wf_tr.id ) ".
		    "WHERE wf_tr_inv.id_invoice =$id_invoice");
    while(list($id_tr, $text) = mysql_fetch_array($q))
      $trs[$id_tr] = $text;
    return $trs;
  }

  function getTransactionsId($id_invoice){
    $trs=array();
    $q = $this->SQL("SELECT id_transaction FROM webfinance_transaction_invoice WHERE id_invoice =$id_invoice");
    while(list($id_tr) = mysql_fetch_array($q))
      $trs[] = $id_tr;
    return $trs;
  }


  function duplicate($id){

    if(is_numeric($id)){
      $result = $this->SQL("SELECT id_client FROM webfinance_invoices WHERE id_facture=$id");
      list($id_client) = mysql_fetch_array($result);
      mysql_free_result($result);

	  $num_facture=$this->generateInvoiceNumber();

	  $query="INSERT INTO webfinance_invoices (id_client,date_created,date_facture,num_facture) ".
		  "VALUES ($id_client, now(), now(), $num_facture)";
      $this->SQL($query)
		  or die(mysql_error());

      $id_new_facture = mysql_insert_id();

      // On recopie les données de la facture
      $this->SQL("UPDATE webfinance_invoices as f1, webfinance_invoices as f2
               SET
                 f1.commentaire=f2.commentaire,
                 f1.type_paiement=f2.type_paiement,
                 f1.ref_contrat=f2.ref_contrat,
                 f1.extra_top=f2.extra_top,
                 f1.extra_bottom=f2.extra_bottom,
                 f1.id_type_presta=f2.id_type_presta,
                 f1.type_doc=f2.type_doc,
                 f1.id_compte=f2.id_compte,
                 f1.tax=f2.tax,
                 f1.exchange_rate=f2.exchange_rate,
                 f1.payment_method=f2.payment_method,
                 f1.delivery=f2.delivery
               WHERE f1.id_facture=$id_new_facture
                 AND f2.id_facture=$id");

      $this->SQL("INSERT INTO webfinance_invoice_rows (id_facture,description,qtt,ordre,prix_ht) SELECT $id_new_facture,description,qtt,ordre,prix_ht FROM webfinance_invoice_rows WHERE id_facture=$id");
      return $id_new_facture;
    }else{
      return 0;
    }
  }

  function updateTransaction($id_invoice, $type_prev=0){

    if (is_numeric($id_invoice)){
      $facture = $this->getInfos($id_invoice);

      if($facture->is_paye){
	$type="asap";
	$date_transaction=date("Y-m-d", $facture->timestamp_date_paiement );
      }else if($type_prev==1){
	$type="asap";
	$date_transaction=date("Y-m-d", mktime(0,0,0,date("m"),date("d")+1, date("Y")) );
      }else if($type_prev>1){
	$type="prevision";
	$date_transaction=date("Y-m-d", ($facture->timestamp_date_facture)+(86400*$type_prev) );
      }else{
	$type="prevision";
	if($facture->timestamp_date_facture < $facture->timestamp_date_paiement )
	  $date_transaction=date("Y-m-d",$facture->timestamp_date_paiement);
	else
	  $date_transaction=date("Y-m-d",$facture->timestamp_date_facture);
      }

      $result = $this->SQL("SELECT id_transaction as id , id_category , type ".
			   "FROM webfinance_transaction_invoice AS wf_tr_inv LEFT JOIN webfinance_transactions AS wf_tr ON (wf_tr_inv.id_transaction = wf_tr.id ) ".
			   "WHERE wf_tr_inv.id_invoice =$id_invoice");
      //$result=$this->SQL("SELECT id, id_category FROM webfinance_transactions WHERE id_invoice=$id_invoice" );

      $nb=mysql_num_rows($result);

      $text = "Num fact: $facture->num_facture, Ref contrat:  $facture->ref_contrat";
      $comment= "$facture->commentaire";
      $id_category=1;

      if($nb==1){
	list($id_tr, $id_category)=mysql_fetch_array($result);

	if($id_category<=1){

	  // Dans tous les cas on essaie de retrouver la catégorie de la transaction
	  // automagiquement.
	  $id_categorie = 1;
	  $result = $this->SQL("SELECT COUNT(*),id,name
                         FROM webfinance_categories
                         WHERE re IS NOT NULL
                         AND '".addslashes($comment." ".$text )."' RLIKE re
                         GROUP BY id");
	  list($nb_matches,$id, $name) = mysql_fetch_array($result);
	  if($nb_matches>0)
	    $id_category=$id;
	}

	//update
	$query = "UPDATE webfinance_transactions SET ".
	  "id_account=%d, ".
	  "id_category=%d, ".
	  "text='%s', ".
	  "amount='%s', ".
	  "type='$type', ".
	  "date='%s' ".
	  "WHERE id=%d";
	$q = sprintf($query, $facture->id_compte, $id_category, $text, preg_replace("!,!", ".", $facture->total_ttc),  $date_transaction, $id_tr );
	$this->SQL($q);


      }else if($nb<1){
	//insert
	$query = "INSERT INTO webfinance_transactions SET ".
	  "id_account=%d, ".
	  "id_category=%d, ".
	  "text='%s', ".
	  "amount='%s', ".
	  "type='$type', ".
	  "date='%s', ".
	  "comment='%s',".
    "id_invoice=%d";
	$q = sprintf($query, $facture->id_compte, $id_category, $text, preg_replace('!,!', '.', $facture->total_ttc), $date_transaction , $comment, $this->id );
	$this->SQL($q);
	$id_tr=mysql_insert_id();

	$query = $this->SQL("INSERT INTO webfinance_transaction_invoice SET id_transaction=$id_tr , id_invoice=$id_invoice ");

      }else{
	//multiple transactions
	if($facture->is_paye){
	  while(list($id_tr , $id_category , $type_tr) = mysql_fetch_array($result)){
	    if($type_tr == "prevision"){
	    $this->SQL("UPDATE webfinance_transactions SET type='asap' WHERE id=$id_tr ");
	    }
	  }
	}
	$id_tr=0;

      }
      mysql_free_result($result);
      return $id_tr;

    }
  }

  function generateInvoiceNumber() {
	  $prefix=date('Ymd');

	  for($suffix=0; $suffix<=999; $suffix++) {
		  $invoice_number = sprintf('%d%.2d', $prefix, $suffix);

		  $result = $this->SQL("SELECT num_facture FROM webfinance_invoices WHERE num_facture='$invoice_number'")
			  or die(mysql_error());

		  if(mysql_num_rows($result)==0)
			  break;
	  }

	  return $invoice_number;

  }

  function nextDeadline($current_deadline, $period) {
	  list($year, $month, $day) = explode('-', $current_deadline);

	  switch($period) {
		  case 'monthly':
			  return date('Y-m-d', mktime(0, 0, 0, $month+1, $day, $year));

		  case 'quarterly':
			  return date('Y-m-d', mktime(0, 0, 0, $month+3, $day, $year));

		  case 'yearly':
			  return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year+1));
	}
  }
	// If the $introduction_letter argument is true, then the PDF will contain
	// an additional page with an introduction letter. Default to false.
	function generatePDF($id_invoice, $introduction_letter = false,
          $target = 'file', $contract = false) {

	  if (!is_numeric($id_invoice)) {
            error_log('$id_invoice not defined');
            exit(1);
          }

	  // Get my company info (address...)
	  $result = mysql_query('SELECT value ' .
							'FROM webfinance_pref '.
							"WHERE type_pref='societe' AND owner=-1");

	  if (mysql_num_rows($result) != 1)
		  die(_("You didn't setup your company address and name. ".
				"<a href='../admin/societe'>Go to 'Admin' and " .
				"'My company'</a>"));

	  list($value) = mysql_fetch_array($result);
	  mysql_free_result($result);

	  $societe = unserialize(base64_decode($value));

	  foreach ($societe as $n=>$v) {
		  $societe->$n=preg_replace("/\xE2\x82\xAC/","EUROSYMBOL", $societe->$n);

		  // FPDF ne support pas l'UTF-8
		  $societe->$n = utf8_decode($societe->$n);

		  $societe->$n = preg_replace("/EUROSYMBOL/", chr(128), $societe->$n );

		  $societe->$n =
			  preg_replace("/\\\\EUR\\{([0-9.,]+)\\}/", "\\1 ".chr(128),
						   $societe->$n );
	  }

	  $result = mysql_query('SELECT value ' .
							'FROM webfinance_pref '.
							"WHERE type_pref='logo' AND owner=-1");

	  if (mysql_num_rows($result) != 1)
		  die(_("You didn't setup the logo for your company. ".
				"<a href='../admin/societe'>Go to 'Admin' and ".
				"'My company'</a>"));

	  list($logo_data) = mysql_fetch_array($result);
	  $logo_data = base64_decode($logo_data);

	  // Save the logo to a temp file since fpdf cannot read from a var
	  $tempfile_logo = tempnam(sys_get_temp_dir(), 'logo');
	  $logo_tmp = fopen($tempfile_logo, "w");
	  fwrite($logo_tmp, $logo_data);
	  fclose($logo_tmp);

	  if(!defined('EURO'))
		  define('EURO',chr(128));

	  $facture = Facture::getInfos($id_invoice);

	  // Generate PDF filename
	  $filename=sys_get_temp_dir() .'/'. ucfirst($facture->type_doc) . "_" .
		  $facture->num_facture . "_" .
		  preg_replace("/[ ]/", "_", $facture->nom_client). ".pdf";

	  foreach ($facture as $n=>$v) {
		  if (!is_array($v)) {
			  $facture->$n = preg_replace("/\xE2\x82\xAC/",
										  "EUROSYMBOL",
										  $facture->$n );
			  // FPDF ne support pas l'UTF-8
			  $facture->$n = utf8_decode($facture->$n);
			  $facture->$n = preg_replace("/EUROSYMBOL/", chr(128),$facture->$n);
			  $facture->$n =
				  preg_replace("/\\\\EUR\\{([0-9.,]+)\\}/", "\\1 ".chr(128),
							   $facture->$n );
		  }
	  }

	  $pdf = new FPDF('P', 'mm', 'A4');

	  // Address
	  $address = "$facture->nom_client\n";
	  for ($i=0 ; $i<3 ; $i++) {
		  $n = sprintf("addr%d", $i+1);
		  if ($facture->$n != "") {
			  $address .= $facture->$n . "\n";
		  }
	  }
	  $address .= "$facture->cp $facture->ville\n$facture->pays";
	
	  // Generate introduction letter, if needed
	  if($introduction_letter !== false) {
		  $pdf->AddPage();
		  $pdf->Image(dirname(__FILE__). '/../../lib/A4.png', 4, 4, 205);

		  $pdf->SetFont('Arial','',11);
		  $pdf->SetXY(115, 50);
		  $pdf->MultiCell(170, 5, $address);

		  // Date and city
		  $pdf->SetXY(20, 90);
		  $pdf->Cell(80, 4, utf8_decode(
						 'Paris, le ' . strftime("%e %B %Y", mktime())));

		  // Object
		  $pdf->SetFont('Arial','B',11);
		  $pdf->SetXY(20, 105);
		  if($facture->type_doc == 'devis') {
		  	$pdf->Cell(80, 4, utf8_decode(
						 'Objet: Devis ISVTEC infogérance serveurs informatiques'));
		  } else {
			  $pdf->Cell(80, 4, utf8_decode(
							 'Objet: Facture infogérance serveurs informatiques'));
		  }
		  // Greetings
		  $pdf->SetFont('Arial','',11);
		  $pdf->SetXY(40, 120);
		  $pdf->Cell(80, 4, utf8_decode('Madame, Monsieur,'));

		  // Main text
		  $pdf->SetXY(20, 135);
		  if($facture->type_doc == 'devis') {
		  	$pdf->MultiCell(170, 5, utf8_decode("Veuillez trouver ci-joint le devis N: ".$facture->num_facture.".
Pour commencer la prestation, merci de me retourner : 
 - le devis signé avec la mention 'bon pour accord'
 - le contrat 
 - l'autorisation de prélèvements en PJ
par email dans un premier temps puis par courrier au : 
ISVTEC, 14 avenue de l'Opéra, 75001 Paris.

Une fois le contrat et devis reçu par email, nous vous fournirons un accès à notre outil de ticketing qui vous permettra de déposer en sécurité les accès à votre serveur dont nous avons besoin pour démarrer la prestation.
N'hésitez pas à me recontacter si vous avez besoin d'informations complémentaires.

Veuillez agréer cher Client, l'expression de nos salutations les meilleures."));
		  } else {
		  	$pdf->MultiCell(170, 5, utf8_decode("Veuillez trouver ci-joint la dernière facture correspondant à l'infogérance et/ou hébergement des serveurs informatiques que vous avez bien voulu nous confier.

Cette facture sera prélevée de manière automatique sur le compte bancaire de votre société dans les jours qui viennent si vous nous avez fournis votre RIB. Merci de nous faire parvenir le règlement par chèque ou virement le cas échéant.

Espérant avoir répondu à vos attentes quant aux services fournis et vous remerciant de la confiance que vous avez bien voulu nous témoigner.

Veuillez agréer cher Client, l'expression de nos salutations les meilleures."));
      	  }

		  $pdf->SetXY(120, 230);
		  $pdf->Cell(80, 4, utf8_decode('L\'équipe ISVTEC'));

	  }
	// Generate contract, if needed
	if($contract !== false) {
		
		$pdf->AddPage();
		$pdf->Image(dirname(__FILE__). '/../../lib/A4_contract.png', 4, 4, 205);
		$pdf->SetFont('Arial','B',14);
		$pdf->SetXY(70, 40);
		$pdf->MultiCell(0, 0, utf8_decode('CONTRAT INFOGÉRANCE'));
		$pdf->SetFont('Arial','',11);
		$pdf->SetXY(70, 46);
		$pdf->MultiCell(0, 0, utf8_decode('Dernière mise à jour : 15. nov. 2011'));
		$pdf->SetXY(20, 60);
		$pdf->MultiCell(170, 5, utf8_decode("
ENTRE :

La société ISVTEC SARL, au capital de 7 500 EUR dont le siège social se situe au 14 avenue de l'Opéra, 75001 Paris, immatriculée sous le numéro RCS 448 752 543, représentée par Cyril Bouthors, gérant

Ci-après dénommée « ISVTEC »

D'UNE PART

ET

La société ").$facture->nom_client.utf8_decode(", au capital de ".$facture->capital." dont le siège social se situe au ").$facture->addr2." ".$facture->addr3.utf8_decode(", immatriculée sous le numéro RCS ".$facture->rcs.", représentée par ").$facture->addr1.utf8_decode(", gérant

Ci-après dénommée « le Client ».

D'AUTRE PART
		
PRÉAMBULE-OBJET DU CONTRAT :
		
ISVTEC est une société de services et d'ingénierie informatique spécialisée dans le traitement des données informatiques en réseau.

Grâce à son savoir-faire et son expérience, elle conçoit des réseaux IP complexes, héberge des données, architecture des systèmes et réseaux haute disponibilité et développe des logiciels.

ISVTEC travaille avec des logiciels libres comme Linux, Apache, MySQL, PostgreSQL, PHP, Python ou Perl, et assure des formations à ces technologies.

Les parties se sont rapprochées pour déterminer les conditions de leur collaboration.
	
IL EST CONVENU ET ARRÊTÉ CE QUI SUIT
		
Article 1 - Documents contractuels
		
Seul le présent contrat fera foi au titre des conventions acceptées entre les parties. Aucun autre document, sauf stipulation expresse, ne sera pris en considération.
"));

$pdf->SetFont('Arial','',8);
$pdf->SetXY(162, 270);
$pdf->MultiCell(170, 5, utf8_decode("Page 1/7"));

$pdf->SetFont('Arial','',11);
$pdf->AddPage();
$pdf->Image(dirname(__FILE__). '/../../lib/A4_contract.png', 4, 4, 205);
$pdf->SetXY(20, 45);
$pdf->MultiCell(170, 5, utf8_decode("Article 2 - Objet du contrat

Par le présent contrat, ISVTEC garantit au Client, par l'intermédiaire de ses ingénieurs, les prestations de service présentées ci-dessous, assurant le bon fonctionnement des équipements informatiques et systèmes décrits dans ce contrat.

Article 3 - Intervention sur problème

ISVTEC s'engage à intervenir sur tout dysfonctionnement des systèmes pris en charge par le présent contrat. ISVTEC ne saurait être tenu responsable des dysfonctionnements, pannes et pertes de données qui pourraient résulter de fausses manipulations ou d'actions faites par le Client de son propre chef.

Tout incident devra être signalé par le client afin de permettre l'ouverture d'un ticket d'incident, marquant le début de la prise en compte des délais ouvrant droit à une éventuelle application des conditions de la Garantie de Temps de Rétablissement. 

Article 4 - Maintenance à distance

En collaboration avec ISVTEC, le Client rendra possible l'accès à distance au site afin de permettre les interventions à distance sur les incidents, la surveillance de l'état général des services, l'installation des mises à jour de sécurité, la sauvegarde, la redondance, l'optimisation du serveur, la définition d'une politique de sécurité, la mise en place d'un firewall et le nettoyage des configurations antérieures.

Article 5 - Vérifications périodiques

ISVTEC interviendra à distance périodiquement sur les systèmes pris en charge par le présent contrat afin de vérifier l'état général des équipements informatiques et systèmes, et de voir avec le Client les évolutions nécessaires. Tout déplacement sur site fera l'objet d'un devis séparé.

Article 6 - Sauvegardes

ISVTEC mettra en place une sauvegarde journalière des données importantes des systèmes pris en charge par le présent contrat et surveillera de manière régulière l'état de la sauvegarde, la possibilité de restaurer les données et la pertinence de la liste des données sauvegardées.

Article 7 - Surveillance

ISVTEC mettra en place un système de surveillance de l'état des services permettant d'être alerté automatiquement d'éventuels dysfonctionnements.
"));

$pdf->SetFont('Arial','',8);
$pdf->SetXY(162, 270);
$pdf->MultiCell(170, 5, utf8_decode("Page 2/7"));

$pdf->SetFont('Arial','',11);
$pdf->AddPage();
$pdf->Image(dirname(__FILE__). '/../../lib/A4_contract.png', 4, 4, 205);
$pdf->SetXY(20, 45);
$pdf->MultiCell(170, 5, utf8_decode("Article 8 - Garantie de Temps de Rétablissement (GTR)

ISVTEC fournit un service après-vente à délai de rétablissement garanti, dénommé 'Option Garantie du Temps de Rétablissement' (ou « Option GTR »).

Le service GTR comprend trois options :

 - « Option GTR Premium » : rétablissement en moins de 2 heures, 7 jours sur 7 et 24 heures sur 24, quels que soient le jour et l'heure de la signalisation
 - « Option GTR Gold » : rétablissement en moins de 4 heures, 7 jours sur 7 et 24 heures sur 24, quels que soient le jour et l'heure de la signalisation
 - « Option GTR Standard » : rétablissement en moins de 4 heures, pour toute signalisation déposée pendant les jours et heures ouvrables, de 9 à 19 heures, du lundi au vendredi inclus. En dehors de ces horaires, le rétablissement est différé au premier jour ouvrable suivant avant 12 heures. Cette option est incluse en standard dans notre contrat.

L'option choisie est précisée dans le bon de commande et est souscrite pour l'ensemble des services couverts par le présent contrat.

La garantie couvre toute interruption totale ou partielle des services ou tout défaut permanent constaté et mesuré par ISVTEC pendant une période d'observation de quinze minutes à condition toutefois que l'interruption ou le défaut provienne d'un élément du service et des équipements informatiques exploités sous la responsabilité d'ISVTEC. 

En cas de problèmes de fonctionnement différents de ceux précisés ci-dessus, une observation d'une durée de 24 heures est effectuée. Cette période d'observation est exclue du champ d'application du service GTR.

En cas de manquement à cet engagement de la part d'ISVTEC, un taux de pénalité (détaillé dans le tableau ci-dessous) sera opposable à ISVTEC, qui devra en répercuter le montant, sous forme d'avoir sur facture, au Client. 

Ne peuvent être pris en compte pour le calcul des pénalités les temps d'indisponibilité ayant pour cadre une maintenance programmée, pour laquelle le client aura été prévenu au moins 7 jours à l'avance, ainsi que les opérations de mise à jour de sécurité qui doivent intervenir au plus vite dans le but d'assurer la sécurité des services concernés par le présent contrat.

Le montant total de l'avoir consenti au titre des pénalités SLA d'un mois ne pourra pas excéder 100 % de la redevance mensuelle acquittée par le client.
"));
$pdf->SetFont('Arial','',8);
$pdf->SetXY(162, 270);
$pdf->MultiCell(170, 5, utf8_decode("Page 3/7"));

$pdf->SetFont('Arial','',11);
$pdf->AddPage();
$pdf->Image(dirname(__FILE__). '/../../lib/A4_contract.png', 4, 4, 205);
$pdf->SetXY(20, 45);
$pdf->MultiCell(170, 5, utf8_decode("Temps de rétablissement -> Pénalité 
 > GTR + 4 h -> 100 %    
 > GTR + 3 h -> 80 %     
 > GTR + 2 h -> 60 %     
 > GTR + 1 h -> 40 %     
 > GTR          -> 20 %     

Article 9 - Redondance multi-serveur

ISVTEC fournit en option un service de redondance des services informatiques du Client en synchronisant de manière transparente et en temps réel la totalité du contenu du serveur informatique principal sur un deuxième serveur informatique similaire.

À tout instant, si le serveur principal connaît une quelconque défaillance, le service pourra alors être basculé dans un court délai sur le serveur secondaire.

ISVTEC s'engage à avertir le Client en cas de bascule d'un serveur à l'autre bien que celle-ci soit entièrement transparente pour le Client.

Cette option demande une configuration particulière des serveurs lors de l'installation.

Article 10 - Documentation technique

À l'issue de l'installation des systèmes, nécessaires pour mener à bien sa mission d'infogérance comme énoncée précédemment  (sauvegarde, surveillance, redondance, etc.), ISVTEC remettra au Client une documentation technique décrivant l'ensemble des systèmes mis en place.

Article 11 - Infogérance ponctuelle

L'infogérance ponctuelle est facturée à l'heure, indivisible et non reportable, pour une mission précise.

ISVTEC communiquera à l'avance la durée estimée de l'intervention en fonction de la mission confiée par le Client.

Le Client aura la possibilité de confier à ISVTEC l'une de ces missions dans le cadre de l'infogérance ponctuelle :

 - Installation ou mise à jour d'un logiciel, d'un serveur de messagerie, d'un serveur web, d'une application métier...
 - Sécurisation d'un serveur, installation d'un firewall...
 - Prise en charge d'un incident technique.
 - Mise en place d'une politique de sauvegarde.
 - Développements spécifiques divers (scripts de maintenance...)
"));
$pdf->SetFont('Arial','',8);
$pdf->SetXY(162, 270);
$pdf->MultiCell(170, 5, utf8_decode("Page 4/7"));

$pdf->SetFont('Arial','',11);
$pdf->AddPage();
$pdf->Image(dirname(__FILE__). '/../../lib/A4_contract.png', 4, 4, 205);
$pdf->SetXY(20, 45);
$pdf->MultiCell(170, 5, utf8_decode("Cette liste, fournie à titre d'exemple, n'est pas exhaustive.

Ce contrat inclut pour chaque serveur 15 minutes par mois non reportables de taches d'administration courante à la demande du Client. Toute demande d'infogérance ponctuelle au-delà de cette durée fera l'objet d'un bon de commande séparé.

Article 12 - Équipements informatiques couverts

Le Client est seul responsable des éventuelles défaillances matérielles qui pourraient survenir sur les équipements informatiques (disque, serveur, équipement réseau...). Le cas échéant, ISVTEC pourra proposer au Client un devis pour le remplacement de ces équipements informatiques. Les équipements informatiques pris en compte au titre du présent contrat sont les équipements précisés dans le devis ou bon de commande.

Article 13 - Services couverts

Par services s'entendent les applications et programmes utilisés au jour le jour par le Client sur ses serveurs informatiques. Les services pris en compte au titre du présent contrat sont : l'ensemble des services web et des bases de données en fonction sur le serveur informatique.

Article 14 - Tarification

Le présent contrat est proposé dans les conditions financières telles que décrites sur le site Internet http://www.isvtec.com

Tous les tarifs s'entendent Hors Taxes, sont payables mensuellement à réception de la facture par prélèvement automatique et s'entendent pour l'infogérance d'un unique serveur informatique. Le nombre de serveurs sera précisé dans le bon de commande.

Article 15 - Durée

Le présent contrat est conclu pour une durée d'un an, renouvelable par tacite reconduction à chaque date anniversaire. Les parties peuvent mettre fin à ce contrat en le dénonçant par lettre recommandée 15 jours avant l'échéance.

Article 16 - Résiliation pour manquement d'une partie à ses obligations

En cas de non-respect par l'une ou l'autre des parties de ses obligations au titre du présent contrat, celui-ci pourra être résilié au gré de la partie lésée. Il est expressément entendu que cette résiliation aura lieu de plein droit 15 jours après la réception d'une mise en demeure de s'exécuter, restée, en tout ou partie, sans effet. La mise en demeure pourra être notifiée par lettre recommandée avec demande d'avis de réception ou tout acte extrajudiciaire.
"));
$pdf->SetFont('Arial','',8);
$pdf->SetXY(162, 270);
$pdf->MultiCell(170, 5, utf8_decode("Page 5/7"));

$pdf->SetFont('Arial','',11);
$pdf->AddPage();
$pdf->Image(dirname(__FILE__). '/../../lib/A4_contract.png', 4, 4, 205);
$pdf->SetXY(20, 45);
$pdf->MultiCell(170, 5, utf8_decode("Article 17 - Circulation du contrat

Le présent contrat est conclu en considération de la personne du Client, le Client s'interdit en conséquence de transférer à titre onéreux ou gratuit le présent contrat. Néanmoins, ISVTEC se réserve le droit de transférer tout ou partie de son activité à un tiers en ce compris le transfert du présent contrat. ISVTEC se réserve également la possibilité de confier à un tiers l'exécution de tout ou partie de ses obligations contractuelles.

Article 18 - Accessoires

Aucune fourniture ou service complémentaire n'est implicitement compris. ISVTEC peut offrir au Client des services complémentaires qui feront l'objet de contrats distincts.

Article 19 - Modification intégralité

Le présent contrat ne pourra être modifié que par voie d'avenant signé des deux parties. Il représente l'intégralité des engagements existant entre les parties. Il remplace et annule tout engagement oral ou écrit antérieur relatif à l'objet du présent contrat.

Article 20 - Force majeure

En aucun cas, la responsabilité de l'une ou l'autre des parties ne pourra être engagée en cas d'événement de force majeure l'ayant empêché d'exécuter ses obligations ou rendant l'exécution lesdites obligations déraisonnablement onéreuse. De façon expresse, sont considérés comme cas de force majeure, outre ceux habituellement retenus par la Jurisprudence française : les grèves totales ou partielles, lock-out, intempéries, épidémies, blocage des moyens de transport ou d'approvisionnement pour quelque raison que ce soit, tremblement de terre, incendie, tempête, inondation, dégât des eaux, raz de marré, restrictions gouvernementales ou légales, modification légale ou réglementaire des formes de commercialisation, blocage total ou partiel des moyens de télécommunications et des communications y compris les réseaux et les services des télécommunications, et tous autres cas indépendants de la volonté expresse des parties et empêchant l'exécution normale du contrat. La survenance d'un cas de force majeure suspendra, dans un premier temps, de plein droit l'exécution du contrat. Si au-delà d'une période de deux mois, l'obligation reste suspendue du fait d'un cas de force majeure, le contrat sera résilié automatiquement et de plein droit sauf accord express des parties.

Article 21 - Tolérances

Il est formellement convenu que toute tolérance ou renonciation de l'une des parties, dans l'application de tout ou partie des engagements prévus au présent contrat, quelles qu'en aient pu être la fréquence et la durée, ne saurait valoir modification du présent contrat, ni générer un droit quelconque.
"));
$pdf->SetFont('Arial','',8);
$pdf->SetXY(162, 270);
$pdf->MultiCell(170, 5, utf8_decode("Page 6/7"));

$pdf->SetFont('Arial','',11);
$pdf->AddPage();
$pdf->Image(dirname(__FILE__). '/../../lib/A4_contract.png', 4, 4, 205);
$pdf->SetXY(20, 45);
$pdf->MultiCell(170, 5, utf8_decode("
Article 22 - Invalidité partielle

La nullité ou l'inapplicabilité de l'une quelconque des stipulations du présent contrat n'emportera pas nullité des autres stipulations qui conserveront toute leur force et leur portée. Cependant, les parties pourront d'un commun accord, convenir de remplacer la ou les stipulations invalidées.

Article 23 - Droit applicable - Langue du contrat

Le présent contrat est soumis au droit français, à l'exclusion de toute autre législation. En cas de rédaction du présent contrat en plusieurs langues, seule la version française fera foi.

Article 24 - Différends

En vue de trouver ensemble une solution à tout litige qui surviendrait dans l'exécution du présent contrat, les contractants conviennent de se réunir dans les 8 jours à compter de la réception d'une lettre recommandée avec demande d'avis de réception, notifiée par l'une des deux parties. Si au terme d'un délai de 15 jours à compter de cette réunion, les parties n'arrivaient pas à se mettre d'accord sur un compromis ou une solution, le litige serait alors soumis à la compétence juridictionnelle désignée ci-après. Pour tout litige découlant de l'exécution du présent contrat la partie la plus diligente saisira les Tribunaux compétents. TOUT LITIGE RELATIF À LA CONCLUSION, L'INTERPRÉTATION, L'EXÉCUTION OU LA CESSATION DU PRÉSENT CONTRAT SERA SOUMIS AU TRIBUNAL DE PARIS EXCLUSIVEMENT COMPÉTENT, Y COMPRIS EN RÉFÉRÉ, NONOBSTANT L'APPEL EN GARANTIE OU LA PLURALITÉ DE DÉFENDEURS. Au cas où l'une des parties n'exécuterait pas cette sentence, elle supporterait tous les frais occasionnés par la décision d'exequatur.

	Fait en deux exemplaires à Paris, le ".strftime("%e %B %Y", mktime())."		


		Cyril BOUTHORS					                                                                             ").$facture->addr1.utf8_decode("
		     Gérant						    

"));

$pdf->SetFont('Arial','',8);
$pdf->SetXY(160, 270);
$pdf->MultiCell(170, 5, utf8_decode("Page 7/7"));

$pdf->AddPage();
$pdf->Image(dirname(__FILE__). '/../../lib/auto_automatique.png', 4, 4, 205);
	}

	  $pdf->SetMargins(10, 10, 10);
	  $pdf->SetDisplayMode('fullwidth');
	  $pdf->AddPage();

	  // Logo
	  $pdf->Image($tempfile_logo, 90, 5, 25, 0, 'PNG');
	  $pdf->SetFont('Arial','',5);
	  $logo_size = getimagesize($tempfile_logo);
	  $logo_height=$logo_size[1]*25/$logo_size[0];
	  $pdf->SetXY(10,$logo_height+5);
	  $pdf->Cell(190, 5, $societe->invoice_top_line1, 0, 0, "C");
	  $pdf->SetLineWidth(0.3);
	  $pdf->SetXY(10,$logo_height+8);
	  $pdf->Cell(190, 5, $societe->invoice_top_line2, "B", 0, "C");

	  // Address
	  $pdf->SetFont('Arial','B',11);
	  $pdf->SetXY(115, 50);
	  $pdf->Cell(80,5, $facture->nom_client, 0, 0 );
	  $pdf->SetFont('Arial','',11);
	  $y = 54;
	  for ($i=0 ; $i<3 ; $i++) {
		  $n = sprintf("addr%d", $i+1);
		  if ($facture->$n != "") {
			  $pdf->SetXY(115, $y);
			  $pdf->Cell(80,5, $facture->$n, 0, 0 );
			  $y += 5;
		  }
	  }
	  $pdf->SetXY(115, $y);
	  $pdf->Cell(80, 4, $facture->cp." ".$facture->ville, 0, 0 );
	  $pdf->SetXY(115, $y+5);
	  $pdf->Cell(80, 4, $facture->pays, 0, 0 );


	  // Donnees factures
	  $pdf->SetXY(10, 19+$logo_height);
	  $pdf->SetFont('Arial','B',14);
	  $pdf->Cell(60, 4, ucfirst($facture->type_doc).utf8_decode(_(' #')).$facture->num_facture);
	  $pdf->SetFont('Arial','',9);
	  $pdf->SetXY(10, 27+$logo_height);
	  $pdf->Cell(60, 4, $societe->ville." ".utf8_decode(_("on"))." ".strftime("%d/%m/%Y", $facture->timestamp_date_facture));
	  $pdf->SetXY(10, 32+$logo_height);
	  $pdf->Cell(60, 4, utf8_decode(_("VAT code"))." ".$societe->raison_sociale." : ".$societe->tva_intracommunautaire);
	  $pdf->SetXY(10, 37+$logo_height);
	  $pdf->Cell(60, 4, utf8_decode(_("Your VAT code"))." : ".$facture->vat_number);
	  $pdf->SetXY(10, 42+$logo_height);
	  $pdf->Cell(60, 4, $facture->ref_contrat);
	  $pdf->SetXY(10, 47+$logo_height);
	  $pdf->Cell(60, 4, $facture->extra_top);

	  // Lignes de facturation
	  $pdf->SetLineWidth(0.1);
	  $pdf->SetXY(10,80);
	  $pdf->SetFont('Arial', 'B', '10');
	  $pdf->Cell(110, 6, utf8_decode(_("Designation")), 1);
	  $pdf->Cell(20, 6, utf8_decode(_("Quantity")), 1, 0, "C" );
	  $pdf->Cell(30, 6, utf8_decode(_("VAT excl.")), 1, 0, "C" );
	  $pdf->Cell(30, 6,utf8_decode( _("Total")), 1, 0, "C" );
	  $pdf->Ln();

	  $total_ht = 0;

	  foreach ($facture->lignes as $ligne ) {
		  foreach( $ligne as $n=>$v) {
			  $ligne->$n = preg_replace("/\xE2\x82\xAC/", "EUROSYMBOL", $ligne->$n );
			  $ligne->$n = utf8_decode($ligne->$n);
			  $ligne->$n = preg_replace("/EUROSYMBOL/", chr(128), $ligne->$n );
		  }

		  setlocale(LC_TIME, "fr_FR.UTF8");
		  // Replace dates like YYYY-MM-DD with nice expanded date
		  $ligne->description = preg_replace_callback(
			  '/\d{4}-\d{2}-\d{2}/',
			  create_function(
				  '$matches',
				  'return utf8_decode(strftime("%e %B %Y", strtotime($matches[0])));'
				  ),
			  $ligne->description);

		  $y_start = $pdf->getY();
		  $pdf->SetFont('Arial', '', '10');
		  $pdf->MultiCell(110, 6, $ligne->description, "LR", 'L');
		  $x = $pdf->getX();
		  $y = $pdf->getY();
		  $pdf->setXY(120, $y_start);
		  $pdf->Cell(20, $y - $y_start, $ligne->qtt, "LR", 0, "C" );
		  $pdf->Cell(30, $y - $y_start, preg_replace("/\./", ",", sprintf("%.2f".EURO, $ligne->prix_ht)), "LR", 0, "R"  );
		  $pdf->Cell(30, $y - $y_start, preg_replace("/\./", ",", sprintf("%.2f".EURO, $ligne->prix_ht * $ligne->qtt)), "LR", 0, "R" );


		  $total_ht += $ligne->prix_ht * $ligne->qtt;
		  $pdf->Ln();

		  $pdf->Cell(110, 2, "", "LR");
		  $pdf->Cell(20, 2, "", "LR");
		  $pdf->Cell(30, 2, "", "LR");
		  $pdf->Cell(30, 2, "", "LR");
		  $pdf->Ln();
	  }

	  $y_fin = $pdf->getY();
	  if ($y < 190) {
		  $pdf->Cell(110, 190 - $y, "", "LRB", 0, "C" );
		  $pdf->Cell(20, 190 - $y, "", "LRB", 0, "C" );
		  $pdf->Cell(30, 190 - $y, "", "LRB", 0, "C" );
		  $pdf->Cell(30, 190 - $y, "", "LRB", 0, "C" );
		  $pdf->Ln();
	  }

	  // Total HT
	  $pdf->SetFont('Arial', '', '11');
	  $pdf->Cell(130, 6, utf8_decode(_("Payment"))." : ".$facture->type_paiement );
	  $pdf->Cell(30, 6, utf8_decode(_("Subtotal")), "", 0, "R");
	  $pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, $total_ht)), "", 0, "R");
	  $pdf->Ln();

	  // TVA
	  $pdf->Cell(130, 6,  "" );
	  $pdf->Cell(30, 6, utf8_decode(_("VAT"))." ".str_replace('.', ',',$facture->taxe)."%", "", 0, "R");
	  $pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, ($facture->taxe/100)*$total_ht)), "", 0, "R");
	  $pdf->Ln();

	  // Total TTC
	  $pdf->Cell(130, 6,  "" );
	  $pdf->Cell(30, 6, utf8_decode(_("Total")), "", 0, "R");
	  $pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, (1+($facture->taxe/100))*$total_ht)), "", 0, "R");
	  $pdf->Ln();

	  // Accompte
	  $pdf->Cell(130, 6,  "" );
	  $pdf->Cell(30, 6, utf8_decode(_("Versed deposit")), "", 0, "R");
	  $pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, $facture->accompte )), "", 0, "R");
	  $pdf->Ln();

	  // Solde à régler
	  $pdf->SetFont('Arial', 'B', '11');
	  $pdf->Cell(130, 6,  "" );
	  $pdf->Cell(30, 6, utf8_decode(_("Amount due")), "", 0, "R");
	  $pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO,(1+($facture->taxe/100))*$total_ht - $facture->accompte )), "", 0, "R");
	  $pdf->Ln();

	  $pdf->Ln();
	  $pdf->SetFont('Arial', '', '8');
	  $pdf->MultiCell(190, 4, utf8_decode("Escompte non pratiqué par l'entreprise. En cas de défaut de paiement à la date d'exigibilité de la facture, le débiteur s'engage à payer à titre de clause pénale et conformément aux dispositions de l'article 1226 du Code Civil, une majoration de 15% en sus du principal sans qu'une mise en demeure ne soit nécessaire."));

	  // Extra data
	  $pdf->SetFont('Arial', '', '10');

	  if(!empty($facture->extra_bottom)) {
	    $pdf->Ln(10);
	    $pdf->MultiCell(120, 6, $facture->extra_bottom, 0);
	  }

	  // RIB
	  $result = mysql_query('SELECT value ' .
							'FROM webfinance_pref '.
							'WHERE id_pref='.$facture->id_compte)
		  or die(mysql_error());

	  list($cpt) = mysql_fetch_array($result);
	  mysql_free_result($result);

	  $cpt = unserialize(base64_decode($cpt));
	  if (!is_object($cpt)) {
		  echo "compte Impossible de generer la facture. <a ".
			  "href='../admin/societe'>Vous devez saisir au moins un compte ".
			  "bancaire dans les options pour emettre des factures</a>";
                  exit(1);
	  }
	  foreach ($cpt as $n=>$v) {
		  $cpt->$n = utf8_decode($cpt->$n);
	  }

	  $pdf->SetFont('Arial', 'B', '10');
	  $pdf->Ln();
	  $pdf->Cell(160, 6, utf8_decode(_("Bank references"))." ", "LTR", 0, "C");
	  $pdf->Ln();

	  $pdf->SetFont('Arial', '', '8');
	  $pdf->Cell(35, 6, utf8_decode(_("Bank"))." : ", "L");
	  $pdf->Cell(125, 6, $cpt->banque, "R");
	  $pdf->Ln();
	  $pdf->Cell(35, 6, utf8_decode(_("RIB"))." : ", "L");
	  $pdf->Cell(125, 6, $cpt->code_banque. ' ' . $cpt->code_guichet . ' ' .$cpt->compte. ' '. $cpt->clef, "R");
	  $pdf->Ln();
	  $pdf->Cell(35, 6, "IBAN : ", "L");
	  $pdf->Cell(125, 6, $cpt->iban, "R");
	  $pdf->Ln();
	  $pdf->Cell(35, 6, "SWIFT/BIC : ", "LB");
	  $pdf->Cell(125, 6, $cpt->swift, "BR");
	  $pdf->Ln();
	
	  $pdf->SetAuthor($societe->raison_sociale);
	  $pdf->SetCreator('Webfinance $Id: gen_facture.php 532 2012-11-10 10:32:19Z pierre $ Using FPDF');
	  $pdf->SetSubject(ucfirst($facture->type_doc).utf8_decode(_(' #'))." ".$facture->num_facture." ".utf8_decode(_("for"))." ".$facture->nom_client);
	  $pdf->SetTitle(ucfirst($facture->type_doc).utf8_decode(_(' #'))." ".$facture->num_facture);

          if($target == 'file')
            $pdf->Output($filename, 'F');
          else
            $pdf->Output($filename, 'I');
	  $pdf->Close();

	  // Delete temporary logo file
	  unlink($tempfile_logo);

	  return $filename;
  }

  // Only $id_compte is mandatory
  function sendByEmail($id_invoice, array $emails=array(), $from='',
					   $fromname='', $subject='', $body='', $introduction_letter=false, $contract=false) {

	  // Fetch company information
	  $result = mysql_query('SELECT value ' .
							'FROM webfinance_pref '.
							"WHERE type_pref='societe' AND owner=-1")
		  or die('sendByEmail-950'.mysql_error());
	  list($value) = mysql_fetch_array($result);
	  mysql_free_result($result);
	  $societe = unserialize(base64_decode($value));

	  // Fetch invoice information
	  $invoice = Facture::getInfos($id_invoice);

	  // Fetch client information
	  $Client = new Client($invoice->id_client);

	  // Fetch bank account information
	  $result = mysql_query('SELECT value ' .
							'FROM webfinance_pref ' .
							"WHERE id_pref=".$invoice->id_compte)
		  or die('sendByEmail-965'.mysql_error());

	  list($cpt) = mysql_fetch_array($result);
	  mysql_free_result($result);
	  $cpt = unserialize(base64_decode($cpt));
	  if (!is_object($cpt)) {
		  die("Impossible de generer la facture. Vous devez saisir au ".
			  "moins un compte bancaire dans les options pour emettre des ".
			  "factures");
	  }
	  foreach ($cpt as $n=>$v)
		  $cpt->$n = utf8_decode($cpt->$n);

	  // Fetch preference information
	  $result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_invoice'") 
		or die('sendByEmail-980'.mysql_error());
	  list($data) = mysql_fetch_array($result);
	  $pref = unserialize(base64_decode($data));

	  if(empty($from))
		  $from = $societe->email;

	  if(empty($fromname))
		  $fromname = $societe->raison_sociale;

	  if(empty($emails)) {
		  $emails = explode(',',$Client->email);
	  }

	  if(empty($subject))
		  $subject = ucfirst($invoice->type_doc)." #".$invoice->num_facture.
			  " pour ".$invoice->nom_client;

	  if(empty($body)) {

		  // Delay
		  $delay='';
		  $result = mysql_query("SELECT date_format(date, '%d/%m/%Y') ".
								'FROM webfinance_transactions '.
								"WHERE id_invoice=$invoice->id_facture ".
								'ORDER BY date '.
								'DESC')
			  or die('sendByEmail-1006'.mysql_error());

		  if(mysql_num_rows($result)==1){
			  list($tr_date) = mysql_fetch_array($result);
			  $delay=_('payable avant le')." $tr_date" ;
		  }
		  mysql_free_result($result);

		  $patterns=array(
			  '/%%LOGIN%%/',
			  '/%%PASSWORD%%/',
			  '/%%URL_COMPANY%%/' ,
			  '/%%NUM_INVOICE%%/' ,
			  '/%%CLIENT_NAME%%/',
			  '/%%DELAY%%/',
			  '/%%AMOUNT%%/',
			  '/%%BANK%%/',
			  '/%%RIB%%/',
			  '/%%COMPANY%%/',
			  );

		  $replacements=array(
			  $Client->login,
			  $Client->password,
			  $societe->wf_url,
			  $invoice->num_facture ,
			  $invoice->nom_client,
			  $delay,
			  $invoice->nice_total_ttc,
			  $cpt->banque,
			  $cpt->code_banque." ".$cpt->code_guichet." ".$cpt->compte." ".$cpt->clef." ",
			  $societe->raison_sociale
			  );

		  $body = stripslashes(
			  preg_replace($patterns, $replacements,
						   stripslashes(utf8_decode($pref->body))));
	  }

	  $mail = new PHPMailer();
	  $mail->CharSet = 'UTF-8';

	foreach($emails as $email)
	
	  $mail->AddAddress($email);	
	  $mail->From = $from;
	  $mail->FromName = $fromname;
	  $mail->Subject = $subject;
	  $mail->Body = $body;
	  $mail->WordWrap = 80;

	  //attach the invoice file
	  $facture = new Facture;
	  $filename = $facture->generatePDF($id_invoice, $introduction_letter, $target = 'file', $contract);
	  $mail->AddAttachment($filename, basename($filename), 'base64',
						   'application/pdf');

	  // Send mail
	  $mail->Send();

	  // Remove attachment
	  unlink($filename);

	  Facture::setSent($id_invoice);

	  // Log invoice as sent
	  logmessage(_("Send invoice")." #$invoice->num_facture fa:$id_invoice ".
				 "client:$invoice->id_client", $invoice->id_client, $invoice->id_facture);

	  return true;
  }


  /** Marque une facture comme "envoyée"
   */
  function setSent($id_facture) {
	  mysql_query('UPDATE webfinance_invoices '.
				  'SET is_envoye=1, date_sent=NOW() '.
				  "WHERE id_facture=$id_facture")
		  or die('setSent-1086'.mysql_error());
  }

  /**
    * Create a new invoice.
    *
    * @param invoice array. The array defining the new invoice to create.
    *
    * Example:
    *
    * \code
    * Array
    * (
    *     [client_id] => 32
    *     [rows]      => Array
    *     (
    *         [0]         => Array
    *         (
    *             [description] => foo bar
    *             [price]       => 32.12
    *             [quantity]    => 3
    *         )
    *         [1]         => Array
    *         (
    *             [description] => foo bar
    *             [price]       => 32.12
    *             [quantity]    => 3
    *         )
    *     )
    * )
    * \endcode
    *
    * @return invoice_id int. The invoice ID.
    *
    */
  function create(array $invoice = array()) {
    $InvoiceNumber = self::generateInvoiceNumber();

    $vat = getTVA();

    mysql_query('INSERT INTO webfinance_invoices '.
      'SET date_created = NOW(), '.
      '    date_facture = NOW(), '.
      "    id_client    = $invoice[client_id], ".
      "    tax          = $vat, " .
      "    num_facture  = $InvoiceNumber")
      or die(mysql_error());

    $invoiceId = mysql_insert_id();

    $ordre = 1;
    foreach($invoice['rows'] as $row) {
      $row['description'] = mysql_real_escape_string($row['description']);

      mysql_query('INSERT INTO webfinance_invoice_rows '.
        "SET id_facture    = $invoiceId, ".
        "    description   = \"$row[description]\", " .
        "    prix_ht       = $row[price], " .
        "    qtt           = $row[quantity], ".
        "    ordre         = $ordre")
        or die(mysql_error());

      $ordre++;
    }

    logmessage(_('Create invoice').' for client:'. $invoice['client_id'],
      $invoice['client_id']);

    return $invoiceId;
  }

	function SendPaymentRequest($id_invoice, $mode='paypal') {

		$Invoice = new Facture();
		$invoice = $Invoice->getInfos($id_invoice);
		$client = new Client($invoice->id_client);
		$societe = GetCompanyInfo();

		$varlink = $id_invoice.'|'.$invoice->id_client;
		$converter = new Encryption;
		$encoded_varlink = $converter->encode($varlink);
		$link = $societe->wf_url."/payment/?id=$encoded_varlink";
		$mails = array();
		$from = '';
		$fromname = '';
		$subject = '';
		$body = "Bonjour,
Veuillez trouver ci-joint la facture numéro #$invoice->num_facture de $invoice->nice_total_ttc Euro correspondant au devis accepté.

Pour la payer via Paypal, cliquez sur ce lien : $link

Notre équipe d’exploitation interviendra sur vos serveurs dans les délais discutés avec nos équipes suivant votre paiement.

Pour visualiser et imprimer cette facture (au format PDF) vous pouvez utiliser \"Adobe Acrobat Reader\" disponible à l'adresse suivante :
http://www.adobe.com/products/acrobat/readstep2.html

Pour toute question à propos de nos services ou notre société, n’hésitez pas à nous contacter au +33 1 84 16 16 17 du lundi au vendredi de 9h à 19h. 

Cordialement,
L'équipe $societe->raison_sociale.";

		if($Invoice->sendByEmail($id_invoice, $mails, $from, $fromname, $subject,
		$body)) {
			$_SESSION['message'] = _('Invoice was not sent');
			$_SESSION['error'] = 1;
			return $link; 
		} else { 
			echo _("Invoice was not sent");
			die();
		} 
	}
}

?>
