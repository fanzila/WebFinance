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
// $Id$

include("../inc/main.php");
$title = _("Preferences");
$roles = "admin,manager,employee";
include("../top.php");
include("nav.php");
?>
<?= $_SESSION['message']; $_SESSION['message'] = ""; ?>

<h2><?=_('Send invoice') ?></h2>
<?
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_invoice'") or wf_mysqldie();
list($data) = mysql_fetch_array($result);
$pref = unserialize(base64_decode($data));
?>
<form id="main_form" action="save_preferences.php" method="post">
<input type="hidden" name="action" value="mail_invoice" />
<table border="0" cellspacing="7" cellpadding="0">
<tr>
<?php
  $subject="Invoice #%%NUM_INVOICE%%";
  if(isset($pref->subject))
    $subject=$pref->subject;
?>
  <td>
   <input type="text" name="subject" style="width: 500px;" value="<?=$subject?>">
  </td>
</tr>
<tr>
  <td>
<textarea name="body" style="width: 500px; height: 250px; border: solid 1px #ccc;">
<?
  if(isset($pref->body) AND !empty($pref->body) )
    echo stripslashes($pref->body);
  else{

    print("Bonjour,

Veuillez trouver ci-joint la facture num&eacute;ro %%NUM_INVOICE%% %%DELAY%%.

Le montant &agrave; payer au terme de cette facture est de %%AMOUNT%% Euro.
Nous acceptons les r&egrave;glements :
&nbsp;- par Pr&eacute;l&egrave;vement automatique
&nbsp;- par Virement sur le compte %%BANK%% / %%RIB%%
&nbsp;- par Ch&egrave;que &agrave; l'ordre de %%COMPANY%% &agrave; l'adresse en en-t&ecirc;te de facture

Pour visualiser et imprimer cette facture (au format PDF) vous pouvez utiliser \"Adobe Acrobat Reader\" disponible &agrave; l'adresse suivante :
http://www.adobe.com/products/acrobat/readstep2.html

Cordialement,
L'&eacute;quipe %%COMPANY%%.
");
  }
?>
</textarea>
  </td>
</tr>
<tr>
  <td style="text-align: center;">
    <input type="submit" value="<?= _("Save") ?>" />
  </td>
</tr>
</table>
</form>


<h2><?=_('Send user info') ?></h2>
<?
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_user'") or wf_mysqldie();
list($data) = mysql_fetch_array($result);
$pref = unserialize(base64_decode($data));
?>
<form id="main_form" action="save_preferences.php" method="post">
<input type="hidden" name="action" value="mail_user" />
<table border="0" cellspacing="7" cellpadding="0">
<tr>
<?php
  $subject="%%COMPANY%%: "._('your account informations');
if(isset($pref->subject))
    $subject=$pref->subject;
?>
  <td>
   <input type="text" name="subject" style="width: 500px;" value="<?=$subject?>">
  </td>
</tr>

<tr>
  <td>
<textarea name="body" style="width: 500px; height: 250px; border: solid 1px #ccc;">
<?
  if(isset($pref->body) AND !empty($pref->body) ){
    echo $pref->body;
  }else{
?>
You receive this mail because you have an account ...
Name: %%FIRST_NAME%% %%LAST_NAME%%
User name: %%LOGIN%%
Password: %%PASSWORD%%

--
%%COMPANY%%
<?
  }
?>
</textarea>
  </td>
</tr>
<tr>
  <td style="text-align: center;">
    <input type="submit" value="<?= _("Save") ?>" />
  </td>
</tr>
</table>
</form>
