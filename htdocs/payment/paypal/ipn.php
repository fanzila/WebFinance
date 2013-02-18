<?php
/**
*  PHP-PayPal-IPN Example
*
*  This shows a basic example of how to use the IpnListener() PHP class to 
*  implement a PayPal Instant Payment Notification (IPN) listener script.
*
*  For a more in depth tutorial, see my blog post:
*  http://www.micahcarrick.com/paypal-ipn-with-php.html
*
*  This code is available at github:
*  https://github.com/Quixotix/PHP-PayPal-IPN
*
*  @package    PHP-PayPal-IPN
*  @author     Micah Carrick
*  @copyright  (c) 2011 - Micah Carrick
*  @license    http://opensource.org/licenses/gpl-3.0.html
*/

/*
Since this script is executed on the back end between the PayPal server and this
script, you will want to log errors to a file or email. Do not try to use echo
or print--it will not work! 

Here I am turning on PHP error logging to a file called "ipn_errors.log". Make
sure your web server has permissions to write to that file. In a production 
environment it is better to have that log file outside of the web root.
*/

$paypal_params = array (
	'email' 	=> 'paypal@isvtec.com',
	'debug' 	=> false, 
	'log_error' => true
);

$tnx_state = 'cancel';

ini_set('log_errors', $paypal_params['log_error']);
ini_set('error_log', '../../../logs/ipn_errors.log');

// instantiate the IpnListener class
include('../../../lib/paypal/ipnlistener.php');
$listener = new IpnListener();

/*
When you are testing your IPN script you should be using a PayPal "Sandbox"
account: https://developer.paypal.com
When you are ready to go live change use_sandbox to false.
*/
$listener->use_sandbox = $paypal_params['debug'];

/*
By default the IpnListener object is going  going to post the data back to PayPal
using cURL over a secure SSL connection. This is the recommended way to post
the data back, however, some people may have connections problems using this
method. 

To post over standard HTTP connection, use:
$listener->use_ssl = false;

To post using the fsockopen() function rather than cURL, use:
$listener->use_curl = false;
*/

/*
The processIpn() method will encode the POST variables sent by PayPal and then
POST them back to the PayPal server. An exception will be thrown if there is 
a fatal error (cannot connect, your server is not configured properly, etc.).
Use a try/catch block to catch these fatal errors and log to the ipn_errors.log
file we setup at the top of this file.

The processIpn() method will send the raw data on 'php://input' to PayPal. You
can optionally pass the data to processIpn() yourself:
$verified = $listener->processIpn($my_post_data);
*/
try {
	$listener->requirePostMethod();
	$verified = $listener->processIpn();
} catch (Exception $e) {
	error_log($e->getMessage());
	exit(0);
}


/*
The processIpn() method returned true if the IPN was "VERIFIED" and false if it
was "INVALID".
*/
if ($verified) {

	require_once("../../inc/main.php");

	$req = mysql_query("SELECT email, amount, currency, id_invoice FROM webfinance_payment WHERE reference = '".$_POST['custom']."' AND state ='pending' AND id_payment_type = 2 ORDER BY id DESC LIMIT 1") 
		or die(error_log(mysql_error()));
	$paypal_return = mysql_fetch_array($req);
	$error = '';

	if(mysql_num_rows($req) < 1) { 
		
		$error .= "No initiated transaction for this IPN\n";
		
	} else {
		if($paypal_return['amount'] != $_POST['mc_gross']) $error .= "Invalid amount, expected: $paypal_return[amount] got: $_POST[payment_amount]\n";
		if($paypal_return['currency'] != $_POST['mc_currency']) $error .= "Invalid currency, expected: $paypal_return[currency] got: $_POST[mc_currency]\n";
		if($_POST['payment_status'] != 'Completed') $error .= "Invalid payment status: $_POST[payment_status]\n";
		if(!$paypal_params['debug']) if($_POST['test_ipn'] == 1) $error .= "No test payment are allowed test_ipn: $_POST[test_ipn]\n";

		$Facture = new Facture();
		$facture = $Facture->getInfos($paypal_return['id_invoice']);
		$societe = GetCompanyInfo();

		if($facture->is_paye > 0) $error .= "We received a paypal payment for invoice: $facture->num_facture but it has already been paid ?!\n";
		if($facture->is_abandoned > 0 ) $error .= "We received a paypal payment for invoice: $facture->num_facture but invoice got abandoned status?!\n";
	}

	if(!empty($error)) {

		mail($paypal_params['email'], 'PAYPAL WARINING - IPN PROCESSING ERROR', $error."\n\nDEBUG: \n\n". $listener->getTextReport());
		error_log($listener->getTextReport());

	//Transaction OK
	} else {

		//Update invoice
		$req_update_invoice = "UPDATE webfinance_invoices SET 
		payment_method	= 'paypal', 
		is_paye			= 1, 
		date_paiement	= NOW() 
		WHERE id_facture = ".$paypal_return[id_invoice];
		mysql_query($req_update_invoice) 
			or die(error_log($req_update_invoice.' '. mysql_error()));

		//Send email to staff
		mail($paypal_params['email'], "FA: #$facture->num_facture / $facture->nom_client has been paid with Paypal by $paypal_return[email]", "FYI:\n\n".$listener->getTextReport());

		//Send email to client
		$mails = array();
		$from = '';
		$fromname = '';
		$subject = '';
		$body = "Bonjour,
Veuillez trouver ci-joint la facture numéro #$facture->num_facture de $facture->nice_total_ttc Euro payée par Paypal, transaction numéro : $_POST[txn_id].
		
Pour visualiser et imprimer cette facture (au format PDF) vous pouvez utiliser \"Adobe Acrobat Reader\" disponible à l'adresse suivante :
http://www.adobe.com/products/acrobat/readstep2.html

Cordialement,
L'équipe $societe->raison_sociale.";
		
		if(!$Facture->sendByEmail($paypal_return['id_invoice'], $mails, $from, $fromname, $subject,
								  $body)) {
									
			mail($paypal_params['email'], 'PAYPAL WARNING - Invoice was not sent to client after payment', $listener->getTextReport());
			error_log('Invoice was not sent to client '.$listener->getTextReport());
	    }

		//Debug
		if($paypal_params['debug']) mail($paypal_params['email'], 'Verified IPN', $listener->getTextReport());
		if($paypal_params['debug']) error_log($listener->getTextReport());
		
		$tnx_state = 'ok';
	} 

	//update transaction 
	$req_update_payment = "UPDATE webfinance_payment SET 
	state			= '".$tnx_state."',
	transaction_id	= '".$_POST[txn_id]."',
	payment_fee		= $_POST[mc_fee], 	
	payment_date	= NOW()
	WHERE reference = '".$_POST['custom']."'";
	mysql_query($req_update_payment)
	    or die(error_log(mysql_error()));

} else {
	/*
	An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
	a good idea to have a developer or sys admin manually investigate any 
	invalid IPN.
	*/

	mail($paypal_params['email'], 'PAYPAL WARINING - INVALID IPN', $listener->getTextReport());
	error_log($listener->getTextReport());
}

?>
