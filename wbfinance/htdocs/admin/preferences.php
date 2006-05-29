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
   <input type="text" name="subject" style="width: 400px;" value="<?=$subject?>">
  </td>
</tr>

<tr>
  <td>
<textarea name="body" style="width: 400px; height: 150px; border: solid 1px #ccc;">
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
   <input type="text" name="subject" style="width: 400px;" value="<?=$subject?>">
  </td>
</tr>
<tr>
  <td>
<textarea name="body" style="width: 400px; height: 150px; border: solid 1px #ccc;">
<?
  if(isset($pref->body) AND !empty($pref->body) )
    echo $pref->body;
  else
    echo _('Hello').",\n\n This is the invoice #%%NUM_INVOICE%% for %%CLIENT_NAME%%.";
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
