<?php
include("../../../inc/main.php");
$title = _("Paybox");

extract($_GET);

//http://webfinance.dev.jexiste.org/payment/paybox/secure/?amount=2200&ref=1031153228456&trans=0&soletrans=0&auto=123&pbxtype=zeez&error=00
//"PBX_RETOUR" => "amount:M;ref:R;auto:A;trans:T;pbxtype:P;card:C;soletrans:S;error:E",

if(isset($ref) AND isset($auto)){
 $res = mysql_query("UPDATE webfinance_paybox SET ".
	       "state='ok' , ".
	       "autorisation='$auto' , ".
	       "transaction_id='$trans' , ".
	       "amount='$amount/100' ,".
	       "payment_type='$pbxtype' ,".
	       "card_type='$card' ,".
	       "transaction_sole_id='$soletrans' ,".
	       "error_code='$error' , ".
	       "date=NOW() ".
	       "WHERE reference='$ref'") or wf_mysqldie();

 if($res){
   $Invoice = new Facture();
   $res = mysql_query("SELECT id_invoice FROM webfinance_paybox WHERE reference='$ref'") or wf_mysqldie();
   list($id_invoice) = mysql_fetch_array($res);
   if($Invoice->exists($id_invoice)){
     mysql_query("UPDATE webfinance_invoices SET is_paye=1 WHERE id_facture=$id_invoice ") or wf_mysqldie();
     $invoice = $Invoice->getInfos($id_invoice);
     if($invoice->is_paye){
       $Invoice->updateTransaction($invoice->id_invoice,"real");
     }
   }
 }

 }
exit;


?>