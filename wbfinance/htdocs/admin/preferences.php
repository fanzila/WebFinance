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
