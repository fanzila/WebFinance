<?
$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_user'") or wf_mysqldie();
list($data) = mysql_fetch_array($result);
$pref = unserialize(base64_decode($data));
?>
<form id="main_form" action="save_preferences.php" method="post">
<input type="hidden" name="action" value="mail_user" />
<table border="0" cellspacing="7" cellpadding="0">
<tr>
  <td><?=_('Subject')?></td>
<?php
  $subject="%%COMPANY%%: "._('your account informations');
if(isset($pref->subject))
  $subject = stripslashes(utf8_decode($pref->subject));
?>
  <td>
   <input type="text" name="subject" style="width: 500px;" value="<?=$subject?>">
  </td>
</tr>
<?
  $help = "My company: %%COMPANY%%".
  "<br/>Firstname: %%FIRST_NAME%% ".
  "<br/>Lastname: %%LAST_NAME%% ".
  "<br/>Login: %%LOGIN%% ".
  "<br/>Password: %%PASSWORD%%";
?>

<tr>
  <td><?=_('Body')?><img src="/imgs/icons/help.png" onmouseover="return escape('<?=$help?>');"/></td>
  <td>
<textarea name="body" style="width: 500px; height: 350px; border: solid 1px #ccc;">
<?
  if(isset($pref->body) AND !empty($pref->body) ){
    echo stripslashes(utf8_decode($pref->body));
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
  <td style="text-align: center;" colspan="2">
    <input type="submit" value="<?= _("Save") ?>" />
  </td>
</tr>
</table>
</form>
