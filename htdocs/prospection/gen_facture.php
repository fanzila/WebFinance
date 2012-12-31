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
//
// $Id: gen_facture.php 532 2007-06-14 10:39:19Z thierry $
// Génère un PDF pour une facture

require("../inc/main.php");

# If user is not logged, generate a PDF with an explicit error message
if( !isset($_SESSION['id_user']) || $_SESSION['id_user'] < 1 ) {
	$pdf = new FPDF('P', 'mm', 'A4');
	$pdf->SetMargins(10, 10, 10);
	$pdf->SetDisplayMode('fullwidth');
	$pdf->SetAutoPageBreak(true);
	$pdf->AddPage();

	$pdf->SetFont('Arial','',12);
	$pdf->Cell(190, 20, _("You are not authenticated"));

	$pdf->SetSubject(_('Error'));
	$pdf->SetTitle(_('Error'));

	$pdf->Output(_("Error").".pdf", "I");

	die();
}

# Check if the invoice id is defined
if(!isset($_GET['id']) or !is_numeric($_GET['id']))
	die(_("Error: Missing invoice id"));

$docs = false;
if($_GET['docs'] == 1 ) $docs = true;

$invoice = new Facture;
$filename = $invoice->generatePDF($_GET['id'], false, $target = 'file', $docs);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($filename).'"');

readfile($filename);
unlink($filename);

// vim: fileencoding=latin1

?>
