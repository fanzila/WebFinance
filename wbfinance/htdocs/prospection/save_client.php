<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php

include("../inc/main.php");

global $User;
$user = $User->getInfos();
if (!$User->isAuthorized("admin,manager")) {
  $_SESSION['message'] = _('You are not allowed to modify this information');
  header("Location: fiche_prospect.php?id=".$_POST['id_client']);
  die();
}
// print_r($User->getInfos());
// die();

if ($_GET['action'] == "delete") {

  $Client = new Client($_GET['id']);

  if($Client->exists()){

    $q = mysql_query("SELECT id_facture FROM webfinance_invoices WHERE id_client=".$_GET['id']) or wf_mysqldie();
    $clause= "WHERE (";

    while(list($id_inv) = mysql_fetch_array($q) )
      $clause .= " id_invoice=$id_inv OR";

    $clause = preg_replace('/OR$/',") AND type<>'real'",$clause);

    if(mysql_num_rows($q)>0)
      mysql_query("DELETE FROM webfinance_transactions $clause") or wf_mysqldie();
    mysql_free_result($q);

    mysql_query("DELETE FROM webfinance_clients WHERE id_client=".$_GET['id']) or wf_mysqldie();

    $_SESSION['message'] = _('The company and related objects have been deleted');

  }else
    $_SESSION['message'] = _("This client doesn't exist");

  header("Location: /prospection/");
  die();

 }

extract($_POST);

$emails = "";

$email = array_unique($email);


foreach($email as $mail){
  if(check_email($mail))
    $emails .= $mail.",";
}
$emails = preg_replace('/,$/', '', $emails);


$q = sprintf("UPDATE webfinance_clients SET nom='%s',addr1='%s',addr2='%s',addr3='%s',cp='%s',ville='%s',pays='%s',
                                tel='%s',fax='%s',email='%s',vat_number='%s',siren='%s', id_company_type='%d'
              WHERE id_client=%d",

             $nom, $addr1, $addr2, $addr3, $cp, $ville, $pays, $tel, $fax, $emails, $vat_number, $siren, $id_company_type,
             $id_client );

mysql_query($q) or wf_mysqldie();

$_SESSION['message'] = _('Update custumer');
logmessage(_('Update custumer')." client:$id_client ($nom)");

header("Location: fiche_prospect.php?id=$id_client&onglet=".$focused_onglet);

?>
