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
include("../inc/main.php");
include("../inc/Encryption.php");
$roles = 'manager,employee';
include("../top.php");
include("nav.php");

$Invoice = new Facture();
$invoice = $Invoice->getInfos($_GET['id_invoice']);
$client = new Client($invoice->id_client);
$societe = GetCompanyInfo();
	
if(empty($client->email)) {
	echo "<br /><h2>This client has no email... :-(</h2><br />You must add an email for this client before.";
	include("../bottom.php");
	exit;
}

if(isset($_GET['action']) AND $_GET['action'] == 'send') { 

	$link = $Invoice->SendPaymentRequest($_GET['id_invoice']);
	echo "<br /><h2>Invoice and payment link has been sent.</h2><br />FYI Payment link: <a href=\"$link\">$link</a>";
	include("../bottom.php");
	exit;
	
}

?>

<br/>
<h2>Request a PayPal payment</h2>
<br/>
<form action="paypal.php" onsubmit="return confirm('Are you sure you want to process the payment request?')">
<table width="300" border="0" cellspacing="0" cellpadding="2">
<tr><td>Type</td><td><?=$invoice->type_doc?></td></tr>
<tr><td>N°</td><td><?=$invoice->num_facture?></td></tr>
<tr><td>Amount</td><td><?=$invoice->nice_total_ttc?> EUR</td></tr>
<tr><td>Email</td><td><?=$client->email?></td></tr>
<tr><td colspan="2">
	<input type="hidden" name="id_invoice" value="<?=$_GET['id_invoice']?>">
	<input type="hidden" name="action" value="send"><br />
	An invoice and a payment link will be sent to the client by email.<br /><br />
	<input style="width: 150px; height: 40px;" class="bordered" type="submit" name="send" value="Process payment request"></td></tr>
</table>
</form>	
<?
include("../bottom.php");
?>
