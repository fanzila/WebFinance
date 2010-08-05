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
    $result = $this->SQL("INSERT INTO webfinance_invoice_rows (date_creation, id_facture, description, pu_ht, qtt) VALUES(now(), $id_facture, '$desc', '$pu_ht', $qtt)");
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
    $result = $this->SQL("SELECT c.id_client AS id_client, c.nom AS nom_client, c.addr1, c.addr2, c.addr3, c.cp, c.ville, c.vat_number, c.pays, f.periodic_next_deadline, f.delivery, f.payment_method,
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
                                  f.type_paiement, f.is_paye, f.ref_contrat, f.extra_top, f.extra_bottom, f.num_facture, f.period, f.tax, f.id_compte, f.exchange_rate, f.*
                           FROM webfinance_clients AS c, webfinance_invoices AS f
                           WHERE f.id_client=c.id_client
                           AND f.id_facture=$id_facture")
		or die(mysql_error());

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
		  or wf_mysqldie();

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
                 f1.exchange_rate=f2.exchange_rate
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

	  for($suffix=0; $suffix<=99; $suffix++) {
		  $invoice_number = sprintf('%d%.2d', $prefix, $suffix);

		  $result = $this->SQL("SELECT num_facture FROM webfinance_invoices WHERE num_facture='$invoice_number'")
			  or wf_mysqldie();

		  if(mysql_num_rows($result)==0)
			  break;
	  }

	  return $invoice_number;

  }

  function nextDeadline($current_deadline, $period) {
	  list($year, $month, $day) = explode('-', $current_deadline);

	  switch($period) {
		  case 'monthly':
			  return date('Y-m-d', mktime(0, 0, 0, $month+1, 1, $year));

		  case 'quarterly':
			  # Inspired by quarterByDate() as seen on
			  # http://php.net/manual/function.date.php
			  return date('Y-m-d',
						  mktime(0,0,0,((int) floor($month / 3.1) + 1)*3+1, 1,
								 $year));

		  case 'yearly':
			  return date('Y-m-d', mktime(0, 0, 0, 1, 1, $year+1));
	}
  }

  function generatePDF($id_invoice) {

	  if (!is_numeric($id_invoice))
		  die('$id_invoice not defined');

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
		  preg_replace("/[ ]/", "_", utf8_encode($facture->nom_client)). ".pdf";

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
	  $pdf->SetMargins(10, 10, 10);
	  $pdf->SetDisplayMode('fullwidth');
	  $pdf->SetAutoPageBreak(true);
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

	  // Adresse
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

		  $y_start = $pdf->getY();
		  $pdf->SetFont('Arial', '', '10');
		  $pdf->MultiCell(110, 6, $ligne->description, "LR"  );
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
	  $pdf->Cell(130, 6, utf8_decode(_("Payment"))." : ".$facture->type_paiement ); // FIXME : gettext
	  $pdf->Cell(30, 6, utf8_decode(_("Subtotal")), "", 0, "R"); // FIXME : gettext
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

	  // Extra data
	  $pdf->SetFont('Arial', '', '11');
	  $pdf->SetXY(10, 210);
	  $pdf->MultiCell(120, 6, $facture->extra_bottom, 0);

	  // RIB
	  $result = mysql_query('SELECT value ' .
							'FROM webfinance_pref '.
							'WHERE id_pref='.$facture->id_compte)
		  or wf_mysqldie();

	  list($cpt) = mysql_fetch_array($result);
	  mysql_free_result($result);

	  $cpt = unserialize(base64_decode($cpt));
	  if (!is_object($cpt)) {
		  die("compte Impossible de generer la facture. <a ".
			  "href='../admin/societe'>Vous devez saisir au moins un compte ".
			  "bancaire dans les options pour emettre des factures</a>");
	  }
	  foreach ($cpt as $n=>$v) {
		  $cpt->$n = utf8_decode($cpt->$n);
	  }

	  $pdf->SetFont('Arial', 'B', '10');
	  $pdf->SetXY(10, 250);
	  $pdf->Cell(160, 6, utf8_decode(_("Bank references"))." ", "LTR", 0, "C");
	  $pdf->Ln();

	  $pdf->SetFont('Arial', '', '10');
	  $pdf->Cell(35, 6, utf8_decode(_("Bank"))." : ", "L");
	  $pdf->Cell(125, 6, $cpt->banque, "R");
	  $pdf->Ln();
	  $pdf->Cell(35, 6, utf8_decode(_("Bank code"))." : ", "L");
	  $pdf->Cell(30, 6, $cpt->code_banque, "");
	  $pdf->Cell(25, 6, utf8_decode(_("BIC Key"))." : ", "");
	  $pdf->Cell(70, 6, $cpt->clef, "R");
	  $pdf->Ln();
	  $pdf->Cell(35, 6, utf8_decode(_("Office code"))." : ", "L");
	  $pdf->Cell(30, 6, $cpt->code_guichet, "");
	  $pdf->Cell(25, 6, "IBAN : ", "");
	  $pdf->Cell(70, 6, $cpt->iban, "R");
	  $pdf->Ln();
	  $pdf->Cell(35, 6, utf8_decode(_("Account number"))." : ", "LB");
	  $pdf->Cell(30, 6, $cpt->compte, "B");
	  $pdf->Cell(25, 6, "SWIFT/BIC : ", "B");
	  $pdf->Cell(70, 6, $cpt->swift, "BR");
	  $pdf->Ln();

	  $pdf->SetAuthor($societe->raison_sociale);
	  $pdf->SetCreator('Webfinance $Id: gen_facture.php 532 2007-06-14 10:39:19Z thierry $ Using FPDF');
	  $pdf->SetSubject(ucfirst($facture->type_doc).utf8_decode(_(' #'))." ".$facture->num_facture." ".utf8_decode(_("for"))." ".$facture->nom_client);
	  $pdf->SetTitle(ucfirst($facture->type_doc).utf8_decode(_(' #'))." ".$facture->num_facture);

	  $pdf->Output($filename, "F");
	  $pdf->Close();

	  // Delete temporary logo file
	  unlink($tempfile_logo);

	  return $filename;
  }

  // Only $id_compte is mandatory
  function sendByEmail($id_invoice, array $emails=array(), $from='',
					   $fromname='', $subject='', $body='') {

	  // Fetch company information
	  $result = mysql_query('SELECT value ' .
							'FROM webfinance_pref '.
							"WHERE type_pref='societe' AND owner=-1")
		  or wf_mysqldie();
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
		  or wf_mysqldie();

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
	  $result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_invoice'") or wf_mysqldie();
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
			  or die(mysql_error());

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
	  $filename = $facture->generatePDF($id_invoice);
	  $mail->AddAttachment($filename, basename($filename), 'base64',
						   'application/pdf');

	  // Send mail
	  $mail->Send();

	  // Remove attachment
	  unlink($filename);

	  // Set invoice as sent
	  mysql_query('UPDATE webfinance_invoices '.
				  'SET is_envoye=1 '.
				  "WHERE id_facture=$id_invoice")
		  or die(mysql_error());

	  // Log invoice as sent
	  logmessage(_("Send invoice")." #$invoice->num_facture fa:$id_invoice ".
				 "client:$invoice->id_client");

	  return true;
  }

}

?>
