<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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
?>
<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$
//
// THIS CODE IS BEYOND OBFUSCATED. FOR THE BRAVE SOUL ONLY
// YOU HAVE BEEN WARNED
//
//
include("../inc/main.php");
$roles = "manager,employee,accounting";

if ($_GET['action'] == '_new') {
  mysql_query("INSERT INTO webfinance_clients (nom,date_created) VALUES('Nouvelle Entreprise', now())") or wf_mysqldie();
//$result = mysql_query("SELECT id_client FROM webfinance_clients WHERE date_sub(now(), INTERVAL 1 SECOND)<=date_created");
//list($_GET['id']) = mysql_fetch_array($result);
  $_GET['id'] = mysql_insert_id();
  $_SESSION['message']= _('New customer created');
  logmessage(_('Create customer')." client:".$_GET['id'] );
}

if (!preg_match("/^[0-9]+$/", $_GET['id'])) {
  header("Location: /prospection/");
  die();
}

$Client = new Client($_GET['id']);
$title = $Client->nom;

array_push($extra_js, "/js/onglets.js");

$User = new User;
$User->getInfos();
// Onglet affiché par défaut
if (isset($_GET['onglet'])) {
  $shown_tab = $_GET['onglet'];
} elseif (isset($User->prefs->default_onglet_fiche_contact)) {
  $shown_tab = $User->prefs->default_onglet_fiche_contact;
} else {
  $shown_tab = 'contacts';
}

include("../top.php");
include("nav.php");
?>

<script type="text/javascript">
var isModified = 0;

function formChanged() {
  f = document.getElementById('main_form');

  f.submit_button.style.background = '#009f00';
  f.submit_button.style.fontWeight = 'bold';
  f.submit_button.style.color = 'white';

  f.cancel_button.style.background = '#ff0000';
  f.cancel_button.style.fontWeight = 'bold';
  f.cancel_button.style.color = 'white';

  isModified = 1;
}

function submitForm(f) {
  if (!isModified) return;

  if (f.nom.value == '') {
    alert('Le client doit avoir un nom');
    return false;
  }

  f.submit();
}

function confirmDelete(id) {
  if (confirm('Voulez-vous vraiment supprimer ce client, tous ses contacts,\n toutes les factures/devis associés et toutes les transactions?!')) {
    window.location = 'save_client.php?action=delete&id='+id;
  }
}

var onglet_shown='<?= $shown_tab ?>';

function confirmSendInfo(id_client,txt) {
  if (confirm(txt)) {
    window.location = 'save_client.php?action=send_info&id='+id_client;
  }
}

function ask_confirmation(txt) {
  resultat = confirm(txt);
  if(resultat=="1"){
      return true;
  } else {
      return false;
  }
}

</script>

<?= $_SESSION['message']; unset($_SESSION['message']); ?>

<form onchange="formChanged();" id="main_form" action="save_client.php" method="post">

<input type="hidden" name="focused_onglet" value="<?= $_GET['focused_onglet'] ?>" />
<input type="hidden" name="id_client" value="<?= $Client->id ?>" />
<input type="hidden" name="id_user" value="<?= $Client->id_user ?>" />

<table width="740" border="0" cellspacing="5" cellpadding="0" class="fiche_prospect">
<tr>
  <td width="100%"><input type="text" name="nom" value="<?= preg_replace('/"/', '\\"', $Client->nom) ?>" style="font-size: 18px; font-weight: bold; width: 510px; border-top: none; border-left: none; border-right: none;" /><br/></td>
  <td nowrap>
<?php
    if($User->hasRole("manager",$_SESSION['id_user']) || $User->hasRole("employee",$_SESSION['id_user']) ){
?>
    <input style="width: 75px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="submit_button" onclick="submitForm(this.form);" type="button" value="<?= _('Save') ?>" />
    <input style="width: 75px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="cancel_button" type="button" onclick="window.location='fiche_prospect.php?id=<?= $facture->id_client ?>';" value="<?= _('Cancel') ?>" />
    <input style="width: 75px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="delete_button" type="button" onclick="confirmDelete(<?= $Client->id ?>);" value="<?= _('Delete') ?>" />
<?
    }
?>
  </td>
</tr>
</table>

<?php // DEBUT ONGLET
// Creation du TabStrip
$tab = new TabStrip();
$tab->includeTab(_('Contacts'),"../inc/tab/fiche_prospect_contacts.php","contacts");
$tab->includeTab(_('Billing'),"../inc/tab/fiche_prospect_biling.php","biling");
$tab->includeTab(_('Flollow&nbsp;up'),"../inc/tab/fiche_prospect_log.php","log");
$tab->includeTab(_('Miscellaneous'),"../inc/tab/fiche_prospect_other.php","other");
$tab->includeTab(_('Graphics'),"../inc/tab/fiche_prospect_graph.php","graph");
$tab->includeTab(_('Events'),"../inc/tab/fiche_prospect_event.php","event");

if (isset($_GET['tab']))
  $tab->setFocusedTab($_GET['tab']);
$tab->realise();

// FIN ONGLET ?>

</td>
</tr>
</table>

</form>

<script>
focusOnglet('<?= $shown_tab ?>');
</script>


<?php
$Revision = '$Revision$';
include("../bottom.php");
?>
