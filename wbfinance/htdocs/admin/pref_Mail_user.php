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
    $subject=$pref->subject;
?>
  <td>
   <input type="text" name="subject" style="width: 500px;" value="<?=$subject?>">
  </td>
</tr>

<tr>
  <td><?=_('Body')?></td>
  <td>
<textarea name="body" style="width: 500px; height: 350px; border: solid 1px #ccc;">
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
  <td style="text-align: center;" colspan="2">
    <input type="submit" value="<?= _("Save") ?>" />
  </td>
</tr>
</table>
</form>
