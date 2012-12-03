#!/usr/bin/php -q
<?php
  /*
    Copyright (C) 2004-2011 NBI SARL, ISVTEC SARL

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

putenv('WF_DEFAULT_LANGUAGE=fr_FR');
require_once(dirname(__FILE__) . '/../htdocs/inc/main.php');
require_once(dirname(__FILE__) . '/../htdocs/inc/Facture.php');
require_once(dirname(__FILE__) . '/../htdocs/inc/Client.php');
require_once('/usr/share/php/libphp-phpmailer/class.phpmailer.php');

$send_mail_print_invoice = false;
$send_mail_direct_debit = false;
$attachments = array();
$Invoice = new Facture();

// Define French locale in order to generate French dates
setlocale(LC_TIME, "fr_FR.UTF8");

// Fetch periodic invoices where the deadline is over
$result = mysql_query('SELECT id_facture '.
          'FROM webfinance_invoices '.
          "WHERE period!='none' AND ".
          'periodic_next_deadline<=NOW() AND ' .
          "type_doc = 'facture'")
  or die(mysql_error());

if(mysql_num_rows($result)==0) {
  exit;
}

while(list($id_invoice) = mysql_fetch_row($result)) {
  // Fetch info from invoice
  $invoice = $Invoice->getInfos($id_invoice);

  // Calculate next deadline
  $next_deadline = $Invoice->nextDeadline($invoice->periodic_next_deadline,
                   $invoice->period);

  // Duplicate the invoice
  $id_new_invoice = $Invoice->duplicate($id_invoice);

  // Delete setup fees that only have to be paid once
  $query='DELETE FROM webfinance_invoice_rows '.
    "WHERE (description LIKE '%Frais d\'accès au service%' " .
    "OR description LIKE '%Frais d\'installation%') " .
    "AND id_facture=$id_new_invoice";
  mysql_query($query)
    or die("$query:" . mysql_error());

  // Add dynamic start date and deadline in invoice details
  $query='SELECT id_facture_ligne, description '.
    'FROM webfinance_invoice_rows ' .
    "WHERE id_facture=$id_new_invoice";
  $res=mysql_query($query)
    or die("$query:" . mysql_error());

  // Update dates in invoices description
  while($invoice_row = mysql_fetch_array($res)) {

    if(!preg_match('/ du \d{4}-\d{2}-\d{2} au \d{4}-\d{2}-\d{2}/',
        $invoice_row['description']))
      continue;

    $invoice_row['description'] = mysql_real_escape_string(
      preg_replace(
        '/ du (\d{4}-\d{2}-\d{2}) au (\d{4}-\d{2}-\d{2})/',
        " du $invoice->periodic_next_deadline au $next_deadline",
        $invoice_row['description']));

    // Update invoice date
    $query='UPDATE webfinance_invoice_rows '.
      "SET description='$invoice_row[description]'".
      "WHERE id_facture_ligne=$invoice_row[id_facture_ligne]";

    mysql_query($query)
      or die("$query:" . mysql_error());
  }

  // Manage invoice delivery
  switch ($invoice->delivery) {

    // Send invoice by email to the client
    case 'email':
	  if($invoice->payment_method == 'direct_debit') $Invoice->sendByEmail($id_new_invoice);
      break;

      // Send the invoice to me in order to print and send it to the client
    case 'postal':
      $send_mail_print_invoice=true;
      $attachments[] = $Invoice->generatePDF($id_new_invoice, true);
      $Invoice->setSent($id_new_invoice);
      break;
  }

  // Process direct debit invoices
  if($invoice->payment_method=='direct_debit') {
    $new_invoice = $Invoice->getInfos($id_new_invoice);

    $send_mail_direct_debit=true;
    $url="https://webfinance.isvtec.com/prospection/edit_facture.php?id_facture=$new_invoice->id_facture";

    # Set invoice as paid
    $Invoice->setPaid($id_new_invoice);

    # Plan the invoice to be debited
    mysql_query(
      'INSERT INTO direct_debit_row ' .
      "SET invoice_id = $id_new_invoice, ".
      "    state='todo'")
      or die(mysql_error());

  }

  // Process paypal invoices
  if($invoice->payment_method=='paypal') {
	$Invoice->SendPaymentRequest($id_new_invoice);
  }

  // Update deadline
  mysql_query('UPDATE webfinance_invoices '.
    "SET periodic_next_deadline='$next_deadline' " .
    "WHERE id_facture = $id_invoice")
    or die(mysql_error());

}

$mail = new PHPMailer();
$mail->CharSet = 'UTF-8';

if($send_mail_print_invoice) {
  $mail->From = 'administratif@isvtec.com';
  $mail->FromName = 'ISVTEC invoices';
  $mail->ClearAddresses();
  $mail->AddAddress('invoice-print@isvtec.com');
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
