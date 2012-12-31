<?php
/*
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

$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_invoice_$mail_tpl_lang'") or wf_mysqldie();
list($data) = mysql_fetch_array($result);
$pref = unserialize(base64_decode($data));
?>
<?=$language_form?>
<form onchange="formChanged()" id="main_form" action="save_preferences.php" method="post">
<input type="hidden" name="action" value="mail_invoice_<?=$mail_tpl_lang?>" />
<input type="hidden" name="mail_tpl_lang" value="<?=$mail_tpl_lang?>" />
<table border="0" cellspacing="7" cellpadding="0">
	<tr>
  <td><?=_('Subject')?></td>
<?php
  $subject="Facture #%%NUM_INVOICE%% pour %%CLIENT_NAME%%";
 if(isset($pref->subject) AND !empty($pref->subject))
   $subject = stripslashes(utf8_decode($pref->subject));

?>
  <td>
   <input type="text" name="subject" style="width: 500px;" value="<?=$subject?>">
  </td>
</tr>
<?
  $help = "My company: %%COMPANY%%".
   "<br/>Client: %%CLIENT_NAME%%".
  "<br/>Url: %%URL_COMPANY%% ".
  "<br/>Login: %%LOGIN%% ".
  "<br/>Password: %%PASSWORD%%".
   "<br/>Invoice: %%NUM_INVOICE%%".
   "<br/>Delay: %%DELAY%%".
   "<br/>Amount: %%AMOUNT%%".
   "<br/>Bank: %%BANK%% ".
   "<br/>RIB: %%RIB%%";
?>
<tr>
   <td><?=_('Body')?><img src="/imgs/icons/help.png" onMouseOut="UnTip();" onmouseover="Tip('<?=$help?>');"/></td>
  <td>
<textarea name="body" style="width: 500px; height: 350px; border: solid 1px #ccc;">
<?
  if(isset($pref->body) AND !empty($pref->body) )
    echo stripslashes(utf8_decode($pref->body));
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

Nous vous rapellons que vous pouvez consulter et r&eacute;gler vos factures en ligne &agrave; cette adresse :

%%URL_COMPANY%%
Login : %%LOGIN%%
Mot de passe : %%PASSWORD%%

Cordialement,
L'&eacute;quipe %%COMPANY%%.
");
  }
?>
</textarea>
  </td>
</tr>
<tr>
  <td style="text-align: center;" colspan="2">
    <input type="submit" value="<?= _("Save") ?> <?=$mail_tpl_lang?> version" />
  </td>
</tr>
</table>
</form>
