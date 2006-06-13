<?php
//
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$
//
// Real importing is done in do_import.php + import_*.php

$title = _("Send Invoice");
require("../inc/main.php");
$roles = 'manager,admin';
require("../top.php");
require("nav.php");

?>
<script type="text/javascript">

function ask_confirmation(txt) {
  resultat = confirm(txt);
  if(resultat=="1"){
      return true;
  } else {
      return false;
  }
}
</script>

<?php
extract($_GET);
$mails=array();

//Récupérer les adresses mails:
$result = mysql_query("SELECT webfinance_invoices.id_client as id_client, email, nom ".
		      "FROM webfinance_clients LEFT JOIN webfinance_invoices ON (webfinance_clients.id_client = webfinance_invoices.id_client) ".
		      "WHERE id_facture=$id")
  or wf_mysqldie();
$client=mysql_fetch_assoc($result);
mysql_free_result($result);

$Client = new Client($client['id_client']);

if(!empty($client['email'])){
  $emails = explode(',',$client['email']);
  $i = 1;
  foreach($emails as $email){
    $mails[$i." - ".$client['nom']] = $email;
    $i++;
  }
 }

$result = mysql_query("SELECT email, nom, prenom FROM webfinance_personne WHERE client=".$client['id_client'])
  or wf_mysqldie();

while($person=mysql_fetch_assoc($result)){
  if( !in_array($person['email'],$mails) AND check_email($person['email']) )
      $mails[$person['prenom']." ".$person['nom']] = $person['email'];
 }
mysql_free_result($result);

//récupérer les info sur la société
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1")
  or wf_mysqldie();
list($value) = mysql_fetch_array($result);
mysql_free_result($result);
$societe = unserialize(base64_decode($value));

//récupération des infos sur la facture
$Facture = new Facture();
$invoice = $Facture->getInfos($id);


?>

<form id="main_form" action="save_facture.php" method="post">
  <input type="hidden" name="action" value="send">
  <input type="hidden" name="id" value="<?= $id ?>">
  <table class="bordered" border="0" cellspacing="0" cellpadding="3" width="500">
  <tr>
    <td>From</td>
    <td>
  <input type="text" name="from_name" style="width: 190px;" value="<?=$societe->raison_sociale?>"/>&nbsp;
  <input type="text" name="from" style="width: 190px;" value="<?=$societe->email?>"/>
    </td>
  <tr>
  <tr>
   <td><?=_('Recipient')?></td>
   <td>
  <?
  if(count($mails)<1)
    echo _("You must add a mail address!");
  else{
    foreach($mails as $name => $mail)
      printf("<input type='checkbox' name='mails[]' checked value='%s' >%s < %s ><br/>",$mail, $name, $mail );
  }
  ?>
    <input type='text' name='mails2' style='width: 400px;'>
    <img src="/imgs/icons/help.png" onmouseover="return escape('Adresses mails s&eacute;par&eacute;es par des virgules<br/>exemple: toto@exemple.com,foo@example.com');">
   </td>
  </tr>
<?php
  $filename=ucfirst($invoice->type_doc)."_".$invoice->num_facture."_".preg_replace("/[ ]/", "_", $invoice->nom_client).".pdf";
  $path="/tmp/".$filename;
?>
  <tr>
  <td></td>
  <td><img src='/imgs/icons/attachment.png'><a href="gen_facture.php?id=<?=$invoice->id_facture?>"><?=$filename?></a></td>
  </tr>
  <tr><td colspan='2'><hr/></td></tr>
<?php
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_invoice'") or wf_mysqldie();
list($data) = mysql_fetch_array($result);
$pref = unserialize(base64_decode($data));

// RIB
$result = mysql_query("SELECT value FROM webfinance_pref WHERE id_pref=".$invoice->id_compte) or wf_mysqldie();
list($cpt) = mysql_fetch_array($result);
mysql_free_result($result);
$cpt = unserialize(base64_decode($cpt));
if (!is_object($cpt)) {
  die("Impossible de generer la facture. Vous devez saisir au moins un compte bancaire dans les options pour emettre des factures");
}
foreach ($cpt as $n=>$v) {
  $cpt->$n = utf8_decode($cpt->$n);
}

//Company
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1");
if (mysql_num_rows($result) != 1) { die(_("You didn't setup your company address and name. Go to 'Admin' and 'My company'")); }
list($value) = mysql_fetch_array($result);
mysql_free_result($result);
$societe = unserialize(base64_decode($value));
foreach ($societe as $n=>$v) {
  $societe->$n = preg_replace("/\xE2\x82\xAC/", "EUROSYMBOL", $societe->$n );
  $societe->$n = utf8_decode($societe->$n); // FPDF ne support pas l'UTF-8
  $societe->$n = preg_replace("/EUROSYMBOL/", chr(128), $societe->$n );
  $societe->$n = preg_replace("/\\\\EUR\\{([0-9.,]+)\\}/", "\\1 ".chr(128), $societe->$n );
}

//delay
$delay="";
$result = mysql_query("SELECT id, date_format(date, '%d/%m/%Y'), UNIX_TIMESTAMP(date) FROM webfinance_transactions WHERE id_invoice=".$invoice->id_facture." ORDER BY date DESC") or wf_mysqldie();
if(mysql_num_rows($result)==1){
  list($id_tr,$tr_date,$tr_ts_date) = mysql_fetch_array($result);
  if($tr_ts_date>$invoice->timestamp_date_facture)
    $delay=_('payable avant le')." $tr_date" ;
 }
mysql_free_result($result);


$patterns=array(
		'/%%LOGIN%%/',
		'/%%PASSWORD%%/',
		'/%%URL_COMPANY%%/' ,
		'/%%NUM_INVOICE%%/' ,
		'/%%CLIENT_NAME%%/',
		'/%%DELAY%%/',
		'/%%AMOUNT%%/',
		'/%%BANK%%/',
		'/%%RIB%%/',
		'/%%COMPANY%%/',
		);
$replacements=array(
		    $Client->login,
		    $Client->password,
		    $societe->wf_url,
		    $invoice->num_facture ,
		    $invoice->nom_client,
		    $delay,
		    $invoice->nice_total_ttc,
		    $cpt->banque,
		    $cpt->code_banque." ".$cpt->code_guichet." ".$cpt->compte." ".$cpt->clef." ",
		    $societe->raison_sociale
		    );

if(isset($pref->subject) && !empty($pref->body)){
  $subject = preg_replace($patterns, $replacements, stripslashes(utf8_decode($pref->subject)) );
 }else
  $subject = ucfirst($invoice->type_doc)." #".$invoice->num_facture." pour ".$invoice->nom_client;

?>
  <tr>
   <td><?=_('Subject')?></td>
   <td>
     <input type="text" name="subject" style="width: 400px;" value="<?=$subject?>">
     <img src="/imgs/icons/help.png" onmouseover="return escape('Personnalisez le sujet et le corps de l\'email dans:<br/>Administration > Preferences');">
   </td>
  </tr>
<tr>
  <td>Body</td>
  <td>
<textarea name="body" style="width: 400px; height: 300px; border: solid 1px #ccc;">
<?
  if(isset($pref->body) AND !empty($pref->body) )
    echo stripslashes(preg_replace($patterns, $replacements, stripslashes(utf8_decode($pref->body)) ));
  else
    echo _('Hello').",";
?>
</textarea>
  </td>
</tr>
<tr>
 <td ><a href='edit_facture.php?id_facture=<?=$invoice->id_facture?>'><?= _('Back') ?></a></td>
 <td style="text-align: center;">
  <input type="submit" value="<?= _('Send') ?>" onclick="return ask_confirmation('<?= _('Do you really want to send it?') ?>')">
 </td>

</tr>
</table>
</form>

<?php
$Revision = '$Revision$';
require("../bottom.php");
?>
