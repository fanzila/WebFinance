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
$result = mysql_query("SELECT webfinance_invoices.id_client, email, nom ".
		      "FROM webfinance_clients LEFT JOIN webfinance_invoices ON (webfinance_clients.id_client = webfinance_invoices.id_client) ".
		      "WHERE id_facture=$id")
  or wf_mysqldie();
$client=mysql_fetch_assoc($result);
mysql_free_result($result);

if(preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-Za-z]{2,4}$/',$client['email']))
  $mails[$client['nom']]=$client['email'];
$result = mysql_query("SELECT email, nom, prenom FROM webfinance_personne WHERE client=".$client['id_client'])
  or wf_mysqldie();

while($person=mysql_fetch_assoc($result)){
  if(!in_array($person['email'],$mails) AND preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-Za-z]{2,4}$/',$person['email']))
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
  <table class="bordered" border="0" cellspacing="0" cellpadding="3" width="480">
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
   </td>
  </tr>
  <tr>
   <td><?=_('Subject')?></td>
   <td><input type="text" name="subject" style="width: 400px;" value="<?= ucfirst($invoice->type_doc)." n&deg; ".$invoice->num_facture." pour ".$invoice->nom_client ?>"></td>
  </tr>

<tr>
  <td>Body</td>
  <td><textarea name="body" style="width: 400px; height: 200px; border: solid 1px #ccc;"><?= _('Hello') ?>,</textarea></td>
</tr>
<tr>
<td style="text-align: center;" colspan="2">
  <input type="submit" value="<?= _('Send') ?>" onclick="return ask_confirmation('<?= _('Do you really want to send it?') ?>')">
</td>
</tr>
</table>
</form>

<?php
$Revision = '$Revision$';
require("../bottom.php");
?>
