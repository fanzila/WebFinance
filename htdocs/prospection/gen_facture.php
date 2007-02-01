<?php
//
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$
// Génère un PDF pour une facture

require("../inc/main.php");
require("../inc/dbconnect.php");
require("/usr/share/fpdf/fpdf.php");

// Get my company info (address...)
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1");
if (mysql_num_rows($result) != 1) { die(_("You didn't setup your company address and name. <a href='../admin/societe'>Go to 'Admin' and 'My company'</a>")); }
list($value) = mysql_fetch_array($result);
mysql_free_result($result);
$societe = unserialize(base64_decode($value));
foreach ($societe as $n=>$v) {
  $societe->$n = preg_replace("/\xE2\x82\xAC/", "EUROSYMBOL", $societe->$n );
  $societe->$n = utf8_decode($societe->$n); // FPDF ne support pas l'UTF-8
  $societe->$n = preg_replace("/EUROSYMBOL/", chr(128), $societe->$n );
  $societe->$n = preg_replace("/\\\\EUR\\{([0-9.,]+)\\}/", "\\1 ".chr(128), $societe->$n );
}
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='logo' AND owner=-1");
if (mysql_num_rows($result) != 1) { die(_("You didn't setup the logo for your company. <a href='../admin/societe'>Go to 'Admin' and 'My company'</a>")); }
list($logo_data) = mysql_fetch_array($result);
$logo_data = base64_decode($logo_data);

// Save the logo to a temp file since fpdf cannot read from a var
$logo_tmp = fopen("/tmp/logo.png", "w");
fwrite($logo_tmp, $logo_data);
fclose($logo_tmp);

define('EURO',chr(128));

$Facture = new Facture();
if (is_numeric($_GET['id'])) {
  $facture = $Facture->getInfos($_GET['id']);

  foreach ($facture as $n=>$v) {
    if (!is_array($v)) {
      $facture->$n = preg_replace("/\xE2\x82\xAC/", "EUROSYMBOL", $facture->$n );
      $facture->$n = utf8_decode($facture->$n); // FPDF ne support pas l'UTF-8
      $facture->$n = preg_replace("/EUROSYMBOL/", chr(128), $facture->$n );
      $facture->$n = preg_replace("/\\\\EUR\\{([0-9.,]+)\\}/", "\\1 ".chr(128), $facture->$n );
    }
  }
}

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->SetDisplayMode('fullwidth');
$pdf->SetAutoPageBreak(true);
$pdf->AddPage();

// Logo
$pdf->Image("/tmp/logo.png", 90, 5, 25 );
$pdf->SetFont('Arial','',5);
$logo_size = getimagesize("/tmp/logo.png");
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
$pdf->Cell(60, 4, $societe->ville." le ".strftime("%d/%m/%Y", $facture->timestamp_date_facture));
$pdf->SetXY(10, 32+$logo_height);
$pdf->Cell(60, 4, "Code TVA ".$societe->raison_sociale." : ".$societe->tva_intracommunautaire);
$pdf->SetXY(10, 37+$logo_height);
$pdf->Cell(60, 4, "Votre Code TVA : ".$facture->vat_number);
$pdf->SetXY(10, 42+$logo_height);
$pdf->Cell(60, 4, $facture->ref_contrat);
$pdf->SetXY(10, 47+$logo_height);
$pdf->Cell(60, 4, $facture->extra_top);

// Lignes de facturation
$pdf->SetLineWidth(0.1);
$pdf->SetXY(10,80);
$pdf->SetFont('Arial', 'B', '10');
$pdf->Cell(110, 6, "Désignation", 1); // FIXME : gettext
$pdf->Cell(20, 6, "Quantité", 1, 0, "C" );
$pdf->Cell(30, 6, "Prix HT", 1, 0, "C" );
$pdf->Cell(30, 6, "Total", 1, 0, "C" );
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
$pdf->Cell(130, 6, "Paiement : ".$facture->type_paiement ); // FIXME : gettext
$pdf->Cell(30, 6, "Sous Total", "", 0, "R"); // FIXME : gettext
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, $total_ht)), "", 0, "R");
$pdf->Ln();

// TVA
$pdf->Cell(130, 6,  "" );
$pdf->Cell(30, 6, "TVA ".str_replace('.', ',',$facture->taxe)."%", "", 0, "R");
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, ($facture->taxe/100)*$total_ht)), "", 0, "R");
$pdf->Ln();

// Total TTC
$pdf->Cell(130, 6,  "" );
$pdf->Cell(30, 6, "Total", "", 0, "R");
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, (1+($facture->taxe/100))*$total_ht)), "", 0, "R");
$pdf->Ln();

// Accompte
$pdf->Cell(130, 6,  "" );
$pdf->Cell(30, 6, "Accompte versé", "", 0, "R");
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, $facture->accompte )), "", 0, "R");
$pdf->Ln();

// Solde à régler
$pdf->SetFont('Arial', 'B', '11');
$pdf->Cell(130, 6,  "" );
$pdf->Cell(30, 6, "Solde à régler", "", 0, "R");
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO,(1+($facture->taxe/100))*$total_ht - $facture->accompte )), "", 0, "R");
$pdf->Ln();

// Extra data
$pdf->SetFont('Arial', '', '11');
$pdf->SetXY(10, 210);
$pdf->MultiCell(120, 6, $facture->extra_bottom, 0);

// RIB
$result = mysql_query("SELECT value FROM webfinance_pref WHERE id_pref=".$facture->id_compte) or wf_mysqldie();
list($cpt) = mysql_fetch_array($result);
mysql_free_result($result);
$cpt = unserialize(base64_decode($cpt));
if (!is_object($cpt)) { die("Impossible de generer la facture. <a href='../admin/societe'>Vous devez saisir au moins un compte bancaire dans les options pour emettre des factures</a>"); }
foreach ($cpt as $n=>$v) {
  $cpt->$n = utf8_decode($cpt->$n);
}

$pdf->SetFont('Arial', 'B', '10');
$pdf->SetXY(10, 250);
$pdf->Cell(160, 6, "Références Bancaires ", "LTR", 0, "C");
$pdf->Ln();

$pdf->SetFont('Arial', '', '10');
$pdf->Cell(35, 6, "Banque : ", "L");
$pdf->Cell(125, 6, $cpt->banque, "R");
$pdf->Ln();
$pdf->Cell(35, 6, "Code banque : ", "L");
$pdf->Cell(30, 6, $cpt->code_banque, "");
$pdf->Cell(25, 6, "Clef RIB : ", "");
$pdf->Cell(70, 6, $cpt->clef, "R");
$pdf->Ln();
$pdf->Cell(35, 6, "Code guichet : ", "L");
$pdf->Cell(30, 6, $cpt->code_guichet, "");
$pdf->Cell(25, 6, "IBAN : ", "");
$pdf->Cell(70, 6, $cpt->iban, "R");
$pdf->Ln();
$pdf->Cell(35, 6, "Numéro de compte : ", "LB");
$pdf->Cell(30, 6, $cpt->compte, "B");
$pdf->Cell(25, 6, "SWIFT/BIC : ", "B");
$pdf->Cell(70, 6, $cpt->swift, "BR");
$pdf->Ln();

$pdf->SetAuthor($societe->raison_sociale);
$pdf->SetCreator("Webfinance $Id$ Using FPDF");
$pdf->SetSubject(ucfirst($facture->type_doc)." n° ".$facture->num_facture." pour ".$facture->nom_client);
$pdf->SetTitle(ucfirst($facture->type_doc)." n° ".$facture->num_facture);

if(isset($_GET['dest']) AND $_GET['dest']=="file"){
  $filename=ucfirst($facture->type_doc)."_".$facture->num_facture."_".preg_replace("/[ ]/", "_", $facture->nom_client).".pdf";
  $path="/tmp/".$filename;

  if(file_exists($path))
    unlink($path);

  $pdf->Output($path, "F");
  $pdf->Close();

  header("Location: send_facture.php?id=".$_GET['id']);

}else
  $pdf->Output(ucfirst($facture->type_doc)."_".$facture->num_facture."_".preg_replace("/[ ]/", "_", $facture->nom_client).".pdf", "I");

// Delete temporary logofile
unlink("/tmp/logo.png");

// vim: fileencoding=latin1

?>
