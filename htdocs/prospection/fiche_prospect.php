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
  logmessage(_('Create custumer')." client:".$_GET['id'] );
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
  if (confirm('Voulez-vous vraiment supprimer ce client, tous ses contacts\net toutes les factures/devis associés ?!')) {
    window.location = 'save_client.php?action=delete&id='+id;
  }
}
var onglet_shown='<?= $shown_tab ?>';

</script>

<?= $_SESSION['message']; $_SESSION['message']=""; ?>

<form onchange="formChanged();" id="main_form" action="save_client.php" method="post">

<input type="hidden" name="focused_onglet" value="<?= $_GET['focused_onglet'] ?>" />
<input type="hidden" name="id_client" value="<?= $Client->id ?>" />

<table width="740" border="0" cellspacing="5" cellpadding="0" class="fiche_prospect">
<tr>
  <td width="100%"><input type="text" name="nom" value="<?= preg_replace('/"/', '\\"', $Client->nom) ?>" style="font-size: 18px; font-weight: bold; width: 510px; border-top: none; border-left: none; border-right: none;" /><br/></td>
  <td nowrap>
    <input style="width: 75px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="submit_button" onclick="submitForm(this.form);" type="button" value="<?= _('Save') ?>" />
    <input style="width: 75px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="cancel_button" type="button" onclick="window.location='fiche_prospect.php?id=<?= $facture->id_client ?>';" value="<?= _('Cancel') ?>" />
    <input style="width: 75px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="delete_button" type="button" onclick="confirmDelete(<?= $Client->id ?>);" value="<?= _('Delete') ?>" />
  </td>
</tr>
</table>

<table width="740" border="0" cellspacing="0" cellpadding="0" class="fiche_prospect">
<tr class="onglets">
  <td id="handle_contacts" onclick="focusOnglet('contacts');"><?= _('Contacts') ?></td>
  <td id="handle_facturation" onclick="focusOnglet('facturation');"><?= _('Billing') ?></td>
  <td id="handle_log" onclick="focusOnglet('log');"><?= _('Flollow&nbsp;up') ?></td>
  <td id="handle_other" onclick="focusOnglet('other');"><?= _('Miscellaneous') ?></td>
  <td id="handle_graph" onclick="focusOnglet('graph');"><?= _('Graphics') ?></td>
  <td id="handle_event" onclick="focusOnglet('event');"><?= _('Events') ?></td>
  <td style="background: none;" width="100%"></td>
</tr>
<tr style="vertical-align: top;">
<td colspan="7" class="onglet_holder">

<div id="tab_contacts" style="display: none;">

  <table border="0" width="100%"><tr valign="top"><td>
  <b><?= _('Address :') ?></b><br/>
  <input type="text" name="addr1" value="<?= preg_replace('/"/', '\\"', $Client->addr1) ?>" style="color: #666; width: 200px" /><br/>
  <input type="text" name="addr2" value="<?= preg_replace('/"/', '\\"', $Client->addr2) ?>" style="color: #666; width: 200px" /><br/>
  <input type="text" name="addr3" value="<?= preg_replace('/"/', '\\"', $Client->addr3) ?>" style="color: #666; width: 200px" /><br/>
  <input type="text" name="cp" value="<?= preg_replace('/"/', '\\"', $Client->cp) ?>" style="text-align: center; color: #666; width: 48px" /><input type="text" name="ville" value="<?= $Client->ville ?>" style="color: #666; width: 148px" /><br/>
  <input type="text" name="pays" value="<?= preg_replace('/"/', '\\"', $Client->pays) ?>" style="color: #666; width: 80px; text-align: center;" /><br/>
  <b><?= _('Phone and URL :') ?></b><br/>
  <input type="text" name="tel" value="<?= addslashes($Client->tel) ?>" class="tel" /><input type="text" name="fax" value="<?= $Client->fax ?>" class="fax" /><br/>
  <input type="text" name="email" value="<?= addslashes($Client->email) ?>" class="email" /><br/>
  <input type="text" name="web" value="<?= addslashes($Client->web) ?>" class="web" /><br/>
  <br/>
  <?= $Client->link_societe ?>
  </td><td width="100%">

  <b><?= _('Contacts :') ?></b><br/>
  <?include "contact_entreprise.php" ?>
  <div style="text-align: center;"><a href="#" onclick="inpagePopup(event, this, 240, 300, 'edit_contact.php?id=_new&id_client=<?= $Client->id ?>');"><?= _('Add a new contact') ?></a></div>
  </td>

  </table>
</div>

<div id="tab_facturation" style="display: none;">
    <br/>

    <div style="overflow: auto; width: 700px; height: 550px;">
    <table width=100% border=0 cellspacing=0 cellpadding=1>
      <?php
        // Affichage des factures existantes pour ce client
        // Affichage par année, avec une séparation lisible
			    $q="SELECT YEAR(f.date_facture) as annee, ".
			    "SUM( IF(f.type_doc='facture', ".
			    "fl.qtt*fl.prix_ht, 0)) as ca_ht_total, ".
			    "SUM( IF(f.type_doc='facture', IF(f.is_paye=0, fl.qtt*fl.prix_ht, 0), 0)) as du_ht_total ".
			    "FROM webfinance_invoices f LEFT JOIN webfinance_invoice_rows fl ON f.id_facture=fl.id_facture ".
			    "WHERE f.id_client=$Client->id ".
			    "GROUP BY YEAR(date_facture) ".
			    "ORDER BY f.date_facture DESC";

        $result = mysql_query($q) or wf_mysqldie();

        $Facture = new Facture();
        while ($year = mysql_fetch_object($result)) {
          printf('<tr><td style="border-bottom: solid 1px #777;" colspan="5"><b style="font-size: 16px;">%s</b> - <b><i>Encours %s&euro; HT</i></b> - <i>%s&euro; HT</i></td></tr>', $year->annee, number_format($year->du_ht_total, 2, ',', ' '), number_format($year->ca_ht_total, 2, ',', ' '));

          $q = "SELECT f.id_facture
                FROM webfinance_invoices as f
                WHERE f.id_client=".$Client->id."
                AND year(f.date_facture) = '".$year->annee."'
                ORDER BY f.date_facture DESC";
           $result2 = mysql_query($q) or die("$q: ".mysql_error());
           $count=0;
//            $total_du = 0;
           while (list($id_invoice) = mysql_fetch_array($result2)) {
             $facture = $Facture->getInfos($id_invoice);
             // Récupération du texte des lignes facturées pour afficher en infobulle.
             $description = "<b>".$facture->nice_date_facture."</b><br/>";
             foreach ($facture->lignes as $l) {
               $l->description = preg_replace("/\r\n/", " ", $l->description);
               $l->description = preg_replace("/\"/", "", $l->description);
               $l->description = preg_replace("/\'/", "", $l->description);
               $description .= $l->description."<br/>";
             }

//              if ((! $facture->is_paye) && ($facture->type_doc=="facture")) {
//                $total_du += $facture->total;
//              }
             $pdf = sprintf('&nbsp;<a href="gen_facture.php?id=%d"><img src="/imgs/icons/pdf.png" alt="FA" /></a>', $facture->id_facture);

             $icon = "";
             if ($facture->type_doc == "facture") {
               $icon = $facture->is_paye?"paid.gif":"not_paid.gif";
             } else {
               $icon = $facture->is_paye?"ok.gif":"";
             }
             printf('<tr class="facture_line" onmouseover="return escape(\'%s\');" valign=middle>
                       <td nowrap>%s</td>
                       <td>%s%s</td>
                       <td class="euro" nowrap>%s &euro; HT</td>
                       <td class="euro" nowrap>%s &euro; TTC</td>
                       <td width="100%%" style="text-align: right;" nowrap><img src="/imgs/icons/%s" alt=""><a href="edit_facture.php?id_facture=%d"><img src="/imgs/icons/edit.png" border="0"></a>%s</td>
                     </tr>',
                     $description,
                     $facture->nice_date_facture, // FIXME : nice_date = option dans partie admin heritee par tous les objets penser 6 pour 2006
                     $facture->code_type_doc, $facture->num_facture,
                     number_format($facture->total_ht, 2, ',', ' '),
                     number_format($facture->total_ttc, 2, ',', ' '), // FIXME : Taux de TVA par facture
                     $icon,
                     $facture->id_facture,
                     $pdf);
             $count++;
           }
           mysql_free_result($result2);
        }
        mysql_free_result($result);
      ?>
    </table>
    </div>
    <center><a href="edit_facture.php?id_facture=new&id_client=<?= $Client->id ?>">Créer facture/devis</a></center>
</div>

<div style="display: none;" id="tab_other">
  <?include "fiche_prospect_interne.php" ?>
</div>

<div style="display: none;" id="tab_log">

<table>
<?php
// Suivi

// Ajout d'un élément de suivi
$ts_select = '<select name="new_suivi_type">';
$result = mysql_query("SELECT id_type_suivi,name FROM webfinance_type_suivi ORDER BY name");
while (list($id,$ts) = mysql_fetch_array($result)) {
  $ts_select .= sprintf('<option value="%d">%s</option>', $id, $ts);
}
$ts_select .= "</select>";

print <<<EOF
<tr><td colspan="3">
$ts_select<br/>

<textarea name="new_suivi_comment" style="width: 600px; height: 90px; border: solid 1px #ccc;">
</textarea>

</td></tr>
EOF;

// Affichage de l'existant
$q = "SELECT s.id_suivi, s.message, ts.name as type_suivi,
             UNIX_TIMESTAMP(s.date_added) as ts_date_added
      FROM webfinance_suivi s, webfinance_type_suivi ts
      WHERE ts.id_type_suivi=s.type_suivi
      AND s.id_objet=".$Client->id."
      ORDER BY s.date_added DESC";

$result = mysql_query($q) or die($q." ".mysql_error());

$count = 1;
while ($log = mysql_fetch_object($result)) {
  $class = ($count%2)?"even":"odd";
  $date = strftime("%e %b %y", $log->ts_date_added);
  $date = preg_replace("/([^0-9])0/", '\\1', $date); // year >= 2000 this app is not expected to still exist in y3K :)
  print <<<EOF
<tr class="$class" valign="top">
  <td nowrap align="center"><b>$date</b></td>
  <td>$log->message</td>
  <td nowrap class="type_suivi_$log->id_type_suivi">$log->type_suivi</td>
</tr>
EOF;

}
mysql_free_result($result);

?>
</table>
</div>

<div style="text-align: center; display: none;" id="tab_graph">
<?php
$result = mysql_query("SELECT count(*) FROM webfinance_invoices WHERE id_client=".$_GET['id']);
list($has_invoices) = mysql_fetch_array($result);
mysql_free_result($result);
if ($has_invoices) {
?>
<img onmouseover="return escape('<?= ('Income by month for this client') ?>')" src="/graphs/client_income.php?nb_months=12&grid=1&width=720&height=250&id_client=<?= $_GET['id'] ?>" />
<img onmouseover="return escape('<?= ('Income by month for this client') ?>')" src="/graphs/client_debpt.php?nb_months=12&grid=1&width=720&height=250&id_client=<?= $_GET['id'] ?>" />
<?php
}
?>
</div>

<div style="text-align: center; display: none;" id="tab_event">
<table style="border: solid 1px black;" width="100%" border="0" cellspacing="0" cellpadding="5">
<tr class="row_header">
  <td><?=_('Hour') ?></td>
  <td><?= _('Events') ?></Td>
  <td><?= _('Who') ?></td>
</tr>
<?php
$result = mysql_query("SELECT id_userlog,log,date, wf_userlog.id_user,date_format(date,'%d/%m/%Y %k:%i') as nice_date, login ".
		      "FROM webfinance_userlog wf_userlog, webfinance_users wf_users WHERE wf_users.id_user=wf_userlog.id_user  ORDER BY date DESC")
  or wf_mysqldie();

$count=1;
while ($log = mysql_fetch_object($result)) {
  if(preg_match("/client:".$_GET['id']."/",$log->log)){
    $class = ($count%2)==0?"odd":"even";
    $message = parselogline($log->log);

    print <<<EOF
      <tr class="row_$class">
      <td>$log->nice_date</td>
      <td>$message</td>
      <td>$log->login</td>
      </tr>
EOF;

    $count++;
  }

}
mysql_free_result($result);
?>
</table>


</div>


<? // FIN ONGLETS ?>
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
