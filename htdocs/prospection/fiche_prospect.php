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

include("../inc/main.php");
$roles = "manager,employee,accounting";

$User = new User;
$User->getInfos();

if(isset($_GET['ctc'])) { 
	$num = removeSpace($_GET['num']);
	$id_client = $_GET['id'];
	echo "Calling $num ...";
	logmessage(_("Call contact: ")." $num", $id_client);
	
	try
	{
	  ini_set('default_socket_timeout', 60);
	  $soap = new SoapClient('https://www.ovh.com/soapi/soapi-re-1.3.wsdl');
	  $soap->telephonyClick2CallDo($User->prefs->ctc_ovh_login, $User->prefs->ctc_ovh_pass, $User->prefs->ctc_ovh_num, $num, $User->prefs->ctc_ovh_num);
	}
	catch(SoapFault $fault)
	{
	  echo $fault;
	}
	exit; 
}

if (isset($_GET['action']) && $_GET['action'] == '_new') {

  $client_name = 'Nouvelle Entreprise_' . time();
  mysql_query("INSERT INTO webfinance_clients (nom,date_created) VALUES('$client_name', now())")
    or die(mysql_error());

  $_GET['id'] = mysql_insert_id();

  $mantis_project = array(
    'name'       => $client_name,
    # private = 50 according to mantis/ticket/core/constant_inc.php
    'view_state' => array( 'id' => 50),
  );
  $mantis = new WebfinanceMantis;
  $mantis->createProject($_GET['id'], $mantis_project);

  # Create document directory
  $document = new WebfinanceDocument;
  $document_dir = $document->GetCompanyDirectory($_GET['id']);
  mkdir($document_dir)
    or die("Unable to create directory $document_dir");

  $_SESSION['message']= _('New customer created');
  logmessage(_('Create customer')." client:".$_GET['id'],$_GET['id']);
}

$Client = new Client($_GET['id']);
$title = $Client->nom;

array_push($extra_js, "/js/onglets.js");

if (!preg_match("/^[0-9]+$/", $_GET['id'])) {
  header("Location: /prospection/");
  die();
}

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

function ctc(num, id) {
	
    $(document).ready(function() {
	   	$(".show_hide").show();
		params = 'ctc=1&num=' + num + '&id=' + id;
		$(".slidingDiv").load("/prospection/fiche_prospect.php?" + params).slideToggle().delay(3000).fadeOut('slow')
    });    
};

</script>

<script type="text/javascript" language="javascript"
  src="/js/ask_confirmation.js"></script>

<script type="text/javascript">
var isModified = 0;
$(document).ready(function(){
        $("#main_form").change(function() {
                $("#submit_button").css("background", '#009f00');
                $("#submit_button").css("fontWeight", 'bold');
                $("#submit_button").css("color", 'white');

                $("#cancel_button").css("background", '#ff0000');
                $("#cancel_button").css("fontWeight", 'bold');
                $("#cancel_button").css("color", 'white');

                isModified = 1;
            }
        );
});


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

</script>

<?php
if(isset($_SESSION['message']) and !empty($_SESSION['message'])){
  echo $_SESSION['message'];
  unset($_SESSION['message']);
 }
?>

<form onchange="formChanged();" id="main_form" action="save_client.php" method="post">

<input type="hidden" name="focused_onglet" value="<?=(isset($_GET['focused_onglet']))?$_GET['focused_onglet']:'contacts' ?>" />
<input type="hidden" name="id_client" value="<?= $Client->id ?>" />
<input type="hidden" name="id_user" value="<?= $Client->id_user ?>" />

<table border="0" cellspacing="5" cellpadding="0" class="fiche_prospect">
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
$tab->includeTab(_('Billing'),"../inc/tab/fiche_prospect_billing.php","billing");
$tab->includeTab(_('Follow&nbsp;up'),"../inc/tab/fiche_prospect_log.php","followup");
$tab->includeTab(_('Miscellaneous'),"../inc/tab/fiche_prospect_other.php","other");
$tab->includeTab(_('Graphics'),"../inc/tab/fiche_prospect_graph.php","graph");
$tab->includeTab(_('Events'),"../inc/tab/fiche_prospect_event.php","event");
$tab->includeTab(_('Documents'),"../inc/tab/fiche_prospect_documents.php","documents");
$tab->includeTab(_('Contracts'),"../inc/tab/fiche_prospect_contracts.php","contracts");

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
$Revision = '$Revision: 557 $';
include("../bottom.php");
?>
