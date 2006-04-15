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

define('EURO',chr(128));

$Facture = new Facture();
if (is_numeric($_GET['id'])) {
  $facture = $Facture->getInfos($_GET['id']);
   
  foreach ($facture as $n=>$v) {
    $facture->$n = preg_replace("/\xE2\x82\xAC/", "EUROSYMBOL", $facture->$n );
    $facture->$n = utf8_decode($facture->$n); // FPDF ne support pas l'UTF-8
    $facture->$n = preg_replace("/EUROSYMBOL/", chr(128), $facture->$n );
    $facture->$n = preg_replace("/\\\\EUR\\{([0-9.,]+)\\}/", "\\1 ".chr(128), $facture->$n );
  }
}

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(10, 10, 10);
$pdf->SetDisplayMode('fullwidth');
$pdf->SetAutoPageBreak(true);
$pdf->AddPage();

// Logo
$pdf->Image("logo_nbi.jpg", 90, 5, 25 );
$pdf->SetFont('Arial','',5);
$pdf->SetXY(10,14);
$pdf->Cell(190, 5, "NBI - SARL au capital de 15000".EURO." - 3 Allée Berlioz 94800 Villejuif - RCS Créteil 451 605 380", 0, 0, "C");
$pdf->SetLineWidth(0.3);
$pdf->SetXY(10,17);
$pdf->Cell(190, 5, "Téléphone 0872 49 38 27 - Fax 01 46 87 21 99 - http://www.nbi.fr/ - contact@nbi.fr", "B", 0, "C");

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

// Donnees factures
$pdf->SetXY(10, 27);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(60, 4, "Facture n° ".$facture->num_facture);
$pdf->SetFont('Arial','',9);
$pdf->SetXY(10, 40);
$pdf->Cell(60, 4, "Villejuif le ".strftime("%d/%m/%Y", $facture->ts_date_facture));
$pdf->SetXY(10, 45);
$pdf->Cell(60, 4, "Code TVA NBI : FR 80 451 605 380");
$pdf->SetXY(10, 50);
$pdf->Cell(60, 4, "Votre Code TVA : ".$facture->vat_number);
$pdf->SetXY(10, 55);
$pdf->Cell(60, 4, $facture->ref_contrat);
$pdf->SetXY(10, 60);
$pdf->Cell(60, 4, $facture->extra_top);

// Lignes de facturation
$pdf->SetLineWidth(0.1);
$pdf->SetXY(10,80);
$pdf->SetFont('Arial', 'B', '10');
$pdf->Cell(110, 6, "Désignation", 1);
$pdf->Cell(20, 6, "Quantité", 1, 0, "C" );
$pdf->Cell(30, 6, "Prix HT", 1, 0, "C" );
$pdf->Cell(30, 6, "Total", 1, 0, "C" );
$pdf->Ln();

$total_ht = 0;

$result = mysql_query("SELECT * FROM webfinance_invoice_rows WHERE id_facture=".$facture->id_facture." ORDER BY ordre");
while ($ligne = mysql_fetch_object($result)) {
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
mysql_free_result($result);

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
$pdf->Cell(130, 6, "Paiement : ".$facture->type_paiement );
$pdf->Cell(30, 6, "Sous Total", "", 0, "R");
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, $total_ht)), "", 0, "R");
$pdf->Ln();

// TVA
$pdf->Cell(130, 6,  "" );
$pdf->Cell(30, 6, "TVA 19,6%", "", 0, "R");
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, 0.196*$total_ht)), "", 0, "R");
$pdf->Ln();

// Total TTC
$pdf->Cell(130, 6,  "" );
$pdf->Cell(30, 6, "Total", "", 0, "R");
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO, 1.196*$total_ht)), "", 0, "R");
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
$pdf->Cell(30, 6, preg_replace("/\./", ",", sprintf("%.2f".EURO,1.196*$total_ht - $facture->accompte )), "", 0, "R");
$pdf->Ln();

// Extra data
$pdf->SetFont('Arial', '', '11');
$pdf->SetXY(10, 210);
$pdf->MultiCell(120, 6, $facture->extra_bottom, 0);

// RIB
$result = mysql_query("SELECT value FROM webfinance_pref WHERE id_pref=".$facture->id_compte) or die(mysql_error());
list($cpt) = mysql_fetch_array($result);
mysql_free_result($result);
$cpt = unserialize(base64_decode($cpt));
if (!is_object($cpt)) { die("Impossible de generer la facture. Vous devez saisir au moins un compte bancaire dans les options pour emettre des factures"); }
foreach ($cpt as $n=>$v) {
  $cpt->$n = utf8_decode($cpt->$n);
}

$pdf->SetFont('Arial', 'B', '10');
$pdf->SetXY(10, 250);
$pdf->Cell(145, 6, "Référence Bancaires ", "LTR", 0, "C");
$pdf->Ln();

$pdf->SetFont('Arial', '', '10');
$pdf->Cell(35, 6, "Banque : ", "L");
$pdf->Cell(110, 6, $cpt->banque, "R");
$pdf->Ln();
$pdf->Cell(35, 6, "Code banque : ", "L");
$pdf->Cell(30, 6, $cpt->code_banque, "");
$pdf->Cell(25, 6, "Clef RIB : ", "");
$pdf->Cell(55, 6, $cpt->clef, "R");
$pdf->Ln();
$pdf->Cell(35, 6, "Code guichet : ", "L");
$pdf->Cell(30, 6, $cpt->code_guichet, "");
$pdf->Cell(25, 6, "IBAN : ", "");
$pdf->Cell(55, 6, $cpt->iban, "R");
$pdf->Ln();
$pdf->Cell(35, 6, "Numéro de compte : ", "LB");
$pdf->Cell(30, 6, $cpt->compte, "B");
$pdf->Cell(25, 6, "SWIFT/BIC : ", "B");
$pdf->Cell(55, 6, $cpt->swift, "BR");
$pdf->Ln();

$pdf->SetAuthor("NBI SARL");
$pdf->SetCreator("Webfinance $Id$ Using FPDF");
$pdf->SetSubject("Facture n° ".$facture->num_facture." pour ".$facture->nom_client);
$pdf->SetTitle("Facture n° ".$facture->num_facture);
$pdf->Output("Facture_".$facture->num_facture."_".preg_replace("/[ ]/", "_", $facture->nom_client).".pdf", "D");

// vim: fileencoding=latin1

?>
