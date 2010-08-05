#!/usr/bin/php -q
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

require_once(dirname(__FILE__) . '/../htdocs/inc/dbconnect.php');
require_once(dirname(__FILE__) . '/../htdocs/inc/Facture.php');
require_once(dirname(__FILE__) . '/../htdocs/inc/Client.php');
require_once('/usr/share/php/libphp-phpmailer/class.phpmailer.php');

$send_mail_print_invoice=false;
$send_mail_direct_debit=false;
$recap_prelevement_auto="Information about automatic direct debit:\n\n";
$attachments=array();
$Invoice = new Facture();

# Define French locale in order to generate French dates
setlocale(LC_TIME, "fr_FR.UTF8");

# Fetch periodic invoices where the deadline is over
$result = mysql_query('SELECT id_facture '.
					  'FROM webfinance_invoices '.
					  "WHERE period!='none' AND periodic_next_deadline<=NOW()")
	or die(mysql_error());

if(mysql_num_rows($result)==0) {
	echo "Debug: no invoice to process\n";
	exit;
}

while(list($id_invoice) = mysql_fetch_row($result)){
	echo "Debug: Processing invoice id $id_invoice\n";

	# Fetch info from invoice
	$invoice = $Invoice->getInfos($id_invoice);

	echo "Debug: Client: $invoice->nom_client\n";

	# Calculate next deadline
	$next_deadline = $Invoice->nextDeadline($invoice->periodic_next_deadline,
											$invoice->period);

	// Duplicate the invoice
	$id_new_invoice = $Invoice->duplicate($id_invoice);
	echo "Debug: Invoice duplicated as ID $id_new_invoice\n";

	// Add dynamic start date and deadline in invoice details
	$deadline_human_readable=strftime("%e %B %Y", strtotime($next_deadline));
	$start_date_human_readable=strftime("%e %B %Y", strtotime(
											$invoice->periodic_next_deadline));
	$query='UPDATE webfinance_invoice_rows '.
		"SET description=CONCAT(description, ' du $start_date_human_readable au $deadline_human_readable') ".
		"WHERE id_facture=$id_new_invoice ".
		'ORDER BY ordre '.
		'LIMIT 1';
	mysql_query($query)
		or die("$query:" . mysql_error());

	switch ($invoice->delivery) {

		// Send invoice by email to the client
		case 'email':
			echo "Debug: Sending invoice by email\n";
			$Invoice->sendByEmail($id_new_invoice);
			break;

		// Send the invoice to me in order to print and send it to the client
		case 'postal':
			echo "Debug: Generating PDF\n";
			$send_mail_print_invoice=true;
			$attachments[] = $Invoice->generatePDF($id_new_invoice);
			break;
	}

	// Process direct debit invoices
	if($invoice->payment_method=='direct_debit') {
		$send_mail_direct_debit=true;
		$recap_prelevement_auto.="Client: $invoice->nom_client\n";
		$recap_prelevement_auto.="Amount incl. VAT: $invoice->nice_total_ttc ".
			"EUR\n\n";

		echo "Debug: Set invoice as paid by direct debit\n";
		$Invoice->setPaid($id_new_invoice);
	}

	// Update deadline
	echo "Debug: Update $invoice->period deadline from " .
		"$invoice->periodic_next_deadline to $next_deadline\n";
	
	mysql_query('UPDATE webfinance_invoices '.
				"SET periodic_next_deadline='$next_deadline' " .
				"WHERE id_facture = $id_invoice")
		or die(mysql_error());
}

$mail = new PHPMailer();
$mail->AddAddress('administratif@isvtec.com');
$mail->From = 'webfinance@isvtec.com';
$mail->FromName = 'Webfinance';

if($send_mail_direct_debit) {
	echo "Debug: Mail direct debit to process\n";
	$mail->Subject = 'Direct debit to process';
	$mail->Body = $recap_prelevement_auto;
	$mail->Send();
}

if($send_mail_print_invoice) {
	echo "Debug: Mail invoices to print and mail\n";
	$mail->Subject = 'Invoices to print and mail';
	$mail->Body = 'Print the invoices and send them to clients';

	foreach($attachments as $attachment)
		$mail->AddAttachment($attachment, basename($attachment), 'base64',
						   'application/pdf');

	$mail->Send();

	// Remove attachments
	foreach($attachments as $attachment)
		unlink($attachment);
}

?>
