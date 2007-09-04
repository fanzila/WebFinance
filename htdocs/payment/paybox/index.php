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
include("../../inc/main.php");
must_login();

if(!isset($_GET['id_client']) OR !isset($_GET['id_invoice']) ){
  echo "Missing arguments";
  exit;
 }
 if(!is_numeric($_GET['id_client']) OR !is_numeric($_GET['id_invoice']) ){
   echo "Wrong arguments";
   exit;
 }

$Client = new Client();

# check client and invoice

if(!$Client->exists($_GET['id_client'])){
  echo _("This client doesn't exist");
  exit;
   }

$Invoice = new Facture() ;

if($Invoice->exists($_GET['id_invoice'])){
  $inv = $Invoice->getInfos($_GET['id_invoice']);
  if($inv->id_client != $_GET['id_client']){
    echo _("This invoice isn't yours!");
    exit;
  }
  $Client = new Client($_GET['id_client']);
 }

#site
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1")
  or wf_mysqldie();
list($value) = mysql_fetch_array($result);
mysql_free_result($result);

$company = unserialize(base64_decode($value));

$site="webfinance.dev.jexiste.org";
if(!empty($company->wf_url) AND strlen($company->wf_url)>3){
  $site=preg_replace('/^http:\/\//i', '' ,$company->wf_url );
 }

$ref_cmd = "WEBFINANCE;".random_int(10) ;

$params = array(
		"PBX_MODE" => "1",
		"PBX_SITE" => "1999888",
		"PBX_RANG" => "99",
		"PBX_TOTAL" =>  $inv->nice_total_ttc*100, //1500 = 15€
		"PBX_DEVISE" => "978", //978 =  Eur, 840 = $ , 952 = CFA
		"PBX_CMD" => $ref_cmd,
		"PBX_PORTEUR" => $Client->email,
		"PBX_RETOUR" => "ref:R;auto:A;trans:T;montant:M;pbxtype:P;card:C;soletrans:S;error:E",
		"PBX_IDENTIFIANT" => "2",

		"PBX_EFFECTUE" => "http://$site/payment/paybox/ok.php",
		"PBX_REFUSE" => "http://$site/payment/paybox/deny.php",
		"PBX_ERROR" => "http://$site/payment/paybox/deny.php",
		"PBX_ANNULE" => "http://$site/payment/paybox/cancel.php",
		"PBX_LANGUAGE" => "FR",
	      );

$res = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='paybox'")
   or wf_mysqldie();
if(mysql_num_rows($res)>0){
  list($value) = mysql_fetch_array($res);
  $paybox = unserialize(base64_decode($value));
  $params['PBX_SITE']=$paybox->PBX_SITE;
  $params['PBX_RANG']=$paybox->PBX_RANG;
  $params['PBX_IDENTIFIANT']=$paybox->PBX_IDENTIFIANT;
 }

$args="";
foreach($params as $param=>$v){
  $args.=$param."=".$v."&";
}


//insert the transation in the db
$r = mysql_query("INSERT INTO webfinance_paybox SET id_invoice=$inv->id_facture, ".
		 "email='".$params['PBX_PORTEUR']."' , ".
		 "reference='".$params['PBX_CMD']."' , ".
		 "state='pending', ".
		 "amount='$inv->nice_total_ttc' , ".
		 "currency='".$params['PBX_DEVISE']."' , ".
		 "date=NOW() ")
  or wf_mysqldie();

header("Location: /cgi-bin/paybox/modulev2.cgi?$args");
exit;

?>