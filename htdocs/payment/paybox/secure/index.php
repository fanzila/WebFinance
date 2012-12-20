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
require("../../../inc/dbconnect.php");
require("../../../inc/Facture.php");

extract($_GET);

$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1") or die(mysql_error());
list($value) = mysql_fetch_array($result);
mysql_free_result($result);

$societe = unserialize(base64_decode($value));

//"PBX_RETOUR" => "montant:M;ref:R;auto:A;trans:T;pbxtype:P;card:C;soletrans:S;error:E",
//http://webfinance.dev.jexiste.org/payment/paybox/secure/?montant=14472&ref=1041153308810&auto=XXXXXX&trans=605964387&pbxtype=CARTE&card=CB&soletrans=608599999&error=00000

if(isset($ref,$auto,$montant) AND !empty($ref) AND !empty($auto) AND $montant>200){

  echo "test ref,auto,montant: passed <br/>";

  $res = mysql_query("SELECT id_invoice FROM webfinance_paybox WHERE reference='$ref' AND amount = $montant/100 ") or die(mysql_error());

  if(mysql_num_rows($res)==1){

    list($id_invoice) = mysql_fetch_array($res);

    echo "ref,auto,montant: exist <br/>";
//   mail($societe->email, "[DEBUG] WEBFINANCE Paybox passed", "This paybox updated : $ref $auto $montant ");

    $res = mysql_query("UPDATE webfinance_paybox SET ".
		       "state='ok' , ".
		       "autorisation='$auto' , ".
		       "transaction_id='$trans' , ".
		       "amount = $montant/100 ,".
		       "payment_type='$pbxtype' ,".
		       "card_type='$card' ,".
		       "transaction_sole_id='$soletrans' ,".
		       "error_code='$error' , ".
		       "date=NOW() ".
		       "WHERE reference='$ref'") or die(mysql_error());

    //maj de la facture
    mysql_query("UPDATE webfinance_invoices SET is_paye=1, date_paiement=NOW() WHERE id_facture=$id_invoice ") or die(mysql_error());

    //on met à jour le cashflow
    $Facture = new Facture();
    $Facture->updateTransaction($id_invoice);

  }else{
    echo "ref,auto,montant: don't exist <br/>";
    mail($societe->email, "[ALERT] WEBFINANCE Paybox ", "This reference doesn't exist : $ref $auto $montant");
  }

 }else{
  echo "ref,auto,montant: invalid <br/>";
  mail($societe->email, "[ALERT] CB transaction, REF or AUTORISATION not found", "CB transaction, REF or AUTORISATION or AMOUNT INVALID : $ref $auto $montant.");
 }


?>