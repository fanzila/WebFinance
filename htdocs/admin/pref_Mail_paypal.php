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

$result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_paypal_$mail_tpl_lang'") or wf_mysqldie();
list($data) = mysql_fetch_array($result);
$pref = unserialize(base64_decode($data));
?>
<?=$language_form?>
<form id="main_form" action="save_preferences.php" method="post">
<input type="hidden" name="action" value="mail_paypal_<?=$mail_tpl_lang?>" />
<input type="hidden" name="mail_tpl_lang" value="<?=$mail_tpl_lang?>" />
<table border="0" cellspacing="7" cellpadding="0">
<?
  $help = "My company: %%COMPANY%%".
   "<br/>Client: %%CLIENT_NAME%%".
   "<br/>Url Paypal: %%URL_PAYPAL%% ".
   "<br/>Invoice: %%NUM_INVOICE%%".
   "<br/>Amount: %%AMOUNT%%";
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
Bonjour,
Veuillez trouver ci-joint la facture numéro %%NUM_INVOICE%% de %%AMOUNT%% Euro
correspondant au devis accepté.

Pour la payer via Paypal, cliquez sur ce lien :
%%URL_PAYPAL%%

Notre équipe d’exploitation interviendra sur vos serveurs dans les délais
discutés avec nos équipes suivant votre paiement.

Pour visualiser et imprimer cette facture (au format PDF) vous pouvez utiliser
"Adobe Acrobat Reader" disponible à l'adresse suivante :
http://www.adobe.com/products/acrobat/readstep2.html

Pour toute question à propos de nos services ou notre société, n’hésitez
pas à nous contacter au +33 1 84 16 16 17 du lundi au vendredi de 9h à 19h. 

Cordialement,
L'équipe %%COMPANY%%.
<?
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
