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
?>
<?php
include("../inc/main.php");
include("../inc/Encryption.php");

$societe = GetCompanyInfo();

//PAYPAL Vars
$paypal_params = array(
'paypal_url_form' 		=> 'https://www.paypal.com/cgi-bin/webscr', // dev 'https://www.sandbox.paypal.com/cgi-bin/webscr',
'paypal_url_return' 	=> $societe->wf_url.'/payment/return.php',
'paypal_url_cancel' 	=> $societe->wf_url.'/payment/cancel.php',
'paypal_url_notify'		=> $societe->wf_url.'/payment/paypal/ipn.php',
'paypal_email_account'	=> 'paypal@isvtec.com', //dev 'pierre_1353785552_biz@isvtec.com',
'id_payment_type' 		=> '2');

$converter = new Encryption;
$decoded = $converter->decode($_GET['id']);
$chain =  explode('|',$decoded);
$id_invoice = $chain[0];
$id_client = $chain[1];

if(!isset($id_client) OR !isset($id_invoice) ){
	echo "Missing arguments";
	exit;
}
if(!is_numeric($id_client) OR !is_numeric($id_invoice) ){
	echo "Wrong arguments";
	exit;
}

$Client = new Client();

# check client and invoice
if(!$Client->exists($id_client)){
	echo _("This client doesn't exist");
	exit;
}

$Invoice = new Facture() ;
if($Invoice->exists($id_invoice)){
	$inv = $Invoice->getInfos($id_invoice);
	if($inv->id_client != $id_client){
		echo _("This invoice isn't yours!");
		exit;
	}

	if($inv->is_paye > 0){
		echo _("This invoice is already paid.");
		exit;
	}

	$Client = new Client($id_client);
}

//insert the transation in the db
$ref_cmd = "WEBFINANCE;".random_int(10) ;
$r = mysql_query("INSERT INTO webfinance_payment SET id_invoice=$inv->id_facture, ".
	"email='".$Client->email."' , ".
	"reference='".$ref_cmd."' , ".
	"state='pending', ".
	"amount='$inv->nice_total_ttc' , ".
	"currency='EUR' , ".
	"id_payment_type='".$paypal_params['id_payment_type']."' , ".
	"date=NOW() ")
	or die('212'.mysql_error());
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="/css/themes/main/main.css" />
	<title><?= _("Invoice") ?> <?=$societe->raison_sociale?> : <?=$inv->num_facture?></title>
	
	<script type="text/javascript">
	<!--

	function validate_form()
	{
	    valid = true;

	    if ( document.paypalform.terms.checked == false )
	    {
	        alert ( '<?= _("You must accept the agreement to continue.") ?>' );
	        valid = false;
	    }
	    return valid;
	}

	//-->
	</script>
</head>
<body>
	<br /><br />
	<form action="<?=$paypal_params['paypal_url_form']?>" name="paypalform" method="post">
	<table width="500" align="center" border="0" cellspacing="5" cellpadding="5" style="border: 1px solid gray;">
		<tr>
			<td align="center"><?php

		$result = mysql_query("SELECT type_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='logo'") or die(mysql_error());
		$logo = mysql_fetch_object($result);

		if (mysql_num_rows($result)) {
			printf('<img src="data:image/png;base64,%s" /><br/>', $logo->value);
		}
		?></td>
	</tr>
	<tr>

		<td style="border-bottom: 1px solid silver;"><b><?= _("Invoice") ?> <?=$inv->num_facture?></b> <?= _("from the") ?>  <?=$inv->nice_date_facture?> <?= _("of") ?>  <?=$inv->nice_total_ht?> <?= _("EUR HT") ?> , <?=$inv->nice_total_ttc?> <?= _("EUR TTC") ?> 
		</td>
	</tr>
	<tr>
		<td style="border-bottom: 1px solid silver;">
			<b><?=$inv->nom_client?></b><br />
			<?=$inv->addr1?><br />
			<?=$inv->addr2?> <?=$inv->addr3?><br />
			<?=$inv->cp?> <?=$inv->ville?><br />
			<?=$inv->pays?></td>
		</tr>
		<tr>
			<td></td>
		</tr>
		<tr>
			<td><label for="valid"></label>
				<label for="contract"></label>
				<iframe style="border: 1px solid silver;" height="130" width="490" seamless="seamless" src="/payment/contract.php?info_client=<?=$inv->nom_client?><br><?=$inv->addr1?><br><?=$inv->addr2?> <?=$inv->addr3?><br><?=$inv->cp?> <?=$inv->ville?><br /><?=$inv->pays?>"></iframe> 
			</form></td>
		</tr>
		<tr>
			<td style="border-bottom: 1px solid silver;"><input type="checkbox" name="terms" value=""> <?= _("I accept the agreement") ?> </td>
		</tr>
		<tr>
			<td><?= _("Pay") ?> <?=$inv->nice_total_ttc?> <?= _("EUR TTC with:") ?> </td>
		</tr>
		<tr>
			<td align="center">
					<input type="image" src="/imgs/bt_paypal_pay.png" border="0" name="submit" onClick="validate_form();" alt="PayPal">
					<input type="hidden" name="amount" value="<?=$inv->nice_total_ttc?>">
					<input name="item_name" type="hidden" value="Paiement facture <?=$societe->raison_sociale?> <?=$inv->num_facture?>"> 
					<input name="cmd" type="hidden" value="_xclick"> 
					<input name="business" type="hidden" value="<? echo $paypal_params['paypal_email_account']; ?>"> 
					<input name="currency_code" type="hidden" value="EUR"> 
					<input name="custom" type="hidden" value="<?=$ref_cmd?>"> 
					<input name="return" type="hidden" value="<? echo $paypal_params['paypal_url_return']; ?>"> 
					<input name="cancel_return" type="hidden" value="<? echo $paypal_params['paypal_url_cancel']; ?>"> 
					<input name="notify_url" type="hidden" value="<? echo $paypal_params['paypal_url_notify']; ?>"> 
					<input name="no_note" type="hidden" value="1"> 
					<input name="no_shipping" type="hidden" value="1"> 
					<input name="last_name" type="hidden" value="<?=$inv->nom_client?>"> 
					<input name="first_name" type="hidden" value="<?=$inv->addr1?>"> 
					<input name="address1" type="hidden" value="<?=$inv->addr2?>"> 
					<input name="address2" type="hidden" value="<?=$inv->addr3?>"> 
					<input name="city" type="hidden" value="<?=$inv->ville?>"> 
					<input name="zip" type="hidden" value="<?=$inv->cp?>"> 
					<input name="country" type="hidden" value="<?=$inv->pays?>"> 
					<input name="email" type="hidden" value="<?=$Client->email?>"> 
					<input name="night_phone_a" type="hidden" value="<?=$Client->tel?>">
			</td>
		</tr>
	</table>
	</form>
</body>
</html> 
