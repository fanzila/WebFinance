<?php
//
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php
include("../inc/main.php");

if (!is_numeric($_GET['id_facture'])) {
  // CrÃ©ation de facture
  $tva = getTVA();

  mysql_query("INSERT INTO webfinance_invoices (date_created,date_facture,id_client,tax) values(now(), now(), ".$_GET['id_client'].",'$tva')") or wf_mysqldie();
  $id_facture=mysql_insert_id();
  $_SESSION['message'] = _('Invoice created');
  logmessage(_('Create invoice')." for client:".$_GET['id_client'] );
  header("Location: edit_facture.php?id_facture=".$id_facture);
  die();
}

$roles = 'manager,employee';
include("../top.php");
include("nav.php");
$Facture = new Facture();
$facture = $Facture->getInfos($_GET['id_facture']);
list($currency,$exchange) = getCurrency($facture->id_compte);
?>
<script type="text/javascript">
<?php
$lignes = 'var id_lignes = new Array(\'new\', ';
foreach ($facture->lignes as $l) {
  $lignes .= $l->id_facture_ligne.", ";
}
$lignes = preg_replace("/, $/", ");\n", $lignes);
print $lignes;


?>
function changedData(f) {
  <?php if ($facture->immuable) {
  // Le devis est validÃ© ou la facture est payÃ©e => pas possible de modifier ce doc
  ?>
  alert('Ce document ne peut Ãªtre modifiÃ©');
  window.location.reload(true);
  return;
  <?php } ?>

  document.getElementById('submit_button').style.background = '#009f00';
  document.getElementById('submit_button').style.fontWeight = 'bold';
  document.getElementById('submit_button').style.color = 'white';

  document.getElementById('cancel_button').style.background = '#ff0000';
  document.getElementById('cancel_button').style.fontWeight = 'bold';
  document.getElementById('cancel_button').style.color = 'white';
}

function submitForm(f) {
  <?php if ($facture->immuable) {
  // Le devis est validÃ© ou la facture est payÃ©e => pas possible de modifier ce doc
  ?>
  alert('Ce document ne peut Ãªtre modifiÃ©');
  return;
  <?php } ?>

  f.submit();
}

function number_format(v, precision, thousands, coma) {
  var entiere = Math.floor(v);
  var flotante = Math.floor(100*(v-Math.floor(v)));

  j = 10;
  for (i=0 ; i<=thousands ; i++)
    j = j*10;

  // if (flotante == 0) { flotante = '00'; }
  foo = ' '+flotante;
  v = entiere+coma+flotante;
  while (foo.length-2 <= thousands) {
    v += '0';
    foo += '0';
  }
  return v;
}

function updateTotal(tva) {
  changedData(null);
  var t = tva/100;
  var f = 1 + t;
  var total_ht = 0;

  for (i=0 ; i<id_lignes.length ; i++) {
    id = id_lignes[i];

    pu = document.getElementById('prix_ht_'+id);

    pu.value = pu.value.replace(/,/, '.');
    val = parseFloat(pu.value);
    if (val == pu.value) {
      qtt = document.getElementById('qtt_'+id);
      total = document.getElementById('total_'+id);
      total.value = qtt.value * val;
      total_ht += parseFloat(total.value);
    }
  }
  ht = document.getElementById('total_ht');
  ht.value = number_format(total_ht, 2, ' ', ',');
  ttc = document.getElementById('total_ttc');
  ttc.value = number_format(total_ht*f, 2, ' ', ',');
  tva = document.getElementById('tva');
  tva.value = number_format(total_ht*t, 2, ' ', ',');
}
function addQtt(id,tva) {
  qtt = document.getElementById('qtt_'+id);
  val = parseInt(qtt.value);
  val++;
  qtt.value = val;

  updateTotal(tva);
}
function subQtt(id,tva) {
  qtt = document.getElementById('qtt_'+id);
  val = parseInt(qtt.value);
  val--;
  if (val < 1) { val = 1; }
  qtt.value = val;

  updateTotal(tva);
}

var id_facture_ligne = 0;

function hoverLigne(i) {
  id_facture_ligne = i;
}

function raise_ligne() {
  f = document.getElementById('main_form');
  f.raise_lower.value = 'raise:'+id_facture_ligne;
  f.submit();
}

function lower_ligne() {
  f = document.getElementById('main_form');
  f.raise_lower.value = 'lower:'+id_facture_ligne;
  f.submit();
}

function del_ligne() {
  f = document.getElementById('main_form');
  f.raise_lower.value = 'delete:'+id_facture_ligne;
  f.submit();
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

<map name="facture_row_handle">
<!-- #$-:Image Map file created by GIMP Imagemap Plugin -->
<!-- #$-:GIMP Imagemap Plugin by Maurits Rijk -->
<!-- #$-:Please do not edit lines starting with "#$" -->
<!-- #$VERSION:2.0 -->
<!-- #$AUTHOR:Nicolas Bouthors -->
<area shape="rect" coords="0,0,16,16" alt="Up" href="javascript:raise_ligne();" />
<area shape="rect" coords="0,16,16,32" alt="Down" href="javascript:lower_ligne();" />
<area shape="rect" coords="0,32,16,48" alt="Supprimer" href="javascript:del_ligne();" />
</map>

<form id="main_form" onchange="changedData(this);" action="save_facture.php" method="post">
<h1><?= ucfirst($facture->type_doc) ?> <?= $facture->num_facture ?> <?= $facture->nom_client ?></h1>
<input type="hidden" name="action" value="save_facture" />
<input type="hidden" name="id_facture" value="<?= $facture->id_facture ?>" />
<input type="hidden" name="raise_lower" value="" />
<table class="facture" width="100%" border="0" cellspacing="0" cellpadding="3"><?=//Main Layout Table?>
<tr class="row_header">
  <td>Informations <?= $facture->type_doc ?></td>
  <td>Adresse de Facturation</td>
  <td>Informations Internes</td>
</tr>

<tr>
  <td width="300">
    <table width="300" border="0" cellspacing="0" cellpadding="2">
    <tr><td width="100"><?= ucfirst($facture->type_doc) ?> n&deg;</td><td><input type="text" style="width:85px; text-align: center;" name="num_facture" value="<?= $facture->num_facture ?>" /><img src="/imgs/icons/help.png" onmouseover="return escape('Le num&eacute;ro de facture est g&eacute;n&eacute;r&eacute; automatiquement lorsqu\'on marque la facture comme envoy&eacute;e.<br/><br/>On peut forcer ce num&eacute;ro arbitrairement mais souvenez-vous que la loi fran&ccedil;aise oblige les num&eacute;ros de facture &agrave; &ecirc;tre s&eacute;quenciels (pas de trous ni de YYYYMMDD');" /></td></tr>
    <tr><td>Date <?= $facture->type_doc ?></td><td><?php makeDateField('date_facture', $facture->timestamp_date_facture) ?></td></tr>
    <tr><td>Code TVA Client</td><td><input style="width: 110px;" type="text" name="vat_number" value="<?= $facture->vat_number ?>" /></td></tr>
    <tr><td>Ref Contrat</td><td><input style="width: 200px;" type="text" name="ref_contrat" value="<?= $facture->ref_contrat ?>" /></td></tr>
    <tr><td>Paiement</td><td><input type="text" style="width: 200px;" name="type_paiement" value="<?=$facture->type_paiement ?>" /></td></tr>
    <tr><td>Accompte </td><td><input type="text" style="width: 50px; text-align: center;" name="accompte" value="<?= WFO::makeMonetaryFormat($facture->accompte); ?>" /><?=$currency?> TTC</td></tr>
    <tr><td colspan="2">
    <textarea style="width: 300px; height: 40px" name="extra_top"><?= $facture->extra_top ?></textarea>
    </table>
  </td>

  <td valign="top">
  <?// Adresse = 5 champs valeur par dÃ©faut = addr client si modifiÃ© => adresse facturation affectÃ©e ?>
  <div style="width: 180px; border: dashed 1px #cecece; padding: 10px;">
  <b><?= $facture->nom_client ?></b><br/>
  <?= $facture->addr1 ?><br/>
  <?= $facture->addr2 ?><br/>
  <?= $facture->addr3 ?><br/>
  <?= $facture->cp ?> <?= $facture->ville ?>
  </div>
  </td>
  <td rowspan="2" valign="top">
    <table border="0" cellspacing="0" cellpadding="2">
    <tr>
      <td>Type doc</td>
      <td>
        <select name="type_doc">
        <?php
        foreach (array('devis', 'facture') as $type) {
          printf('<option value="%s"%s>%s</option>', $type, ($type==$facture->type_doc)?"selected":"", $type );
        }
        ?>
        </select>
      </td>
    </tr>
    <tr>
      <td nowrap>Type presta</td>
      <td>
        <select name="id_type_presta" style="width: 120px;">
        <?php
        $result = mysql_query("SELECT id_type_presta, nom FROM webfinance_type_presta ORDER BY nom");
        while (list($id, $type) = mysql_fetch_array($result)) {
          printf('<option value="%d"%s>%s</option>', $id, ($id==$facture->id_type_presta)?"selected":"", $type );
        }
        ?>
        </select>
      </td>
    </tr>

    <tr>
	<td><?=_('Periodicity')?></td>
	<td nowrap>
	<select name="period" style="width: 120px;">
	  <option value="none"><?=_("doesn't repeat")?></option>
	  <option value="end of month" <?= ($facture->period=="end of month")?"selected":"" ?>><?=_('end of month')?></option>
	  <option value="end of term" <?= ($facture->period=="end of term")?"selected":"" ?>  ><?= _('end of term')?></option>
	  <option value="end of year" <?= ($facture->period=="end of year")?"selected":"" ?>  ><?=_('end of year')?></option>
	</select>
	  <img src="/imgs/icons/help.png"
	      onmouseover="return escape('<?= addslashes(_('This option allows to periodically dupplicate an invoice')) ?>');" />

	</td>
    </tr>
    <tr>
	<td nowrap><?=_('Payment expected')?></td>
	<td>
	<select name="type_prev" style="width: 120px;">
	  <option value="0"><?=_('Invoice date')?></option>
	  <option value="1"><?=_('asap')?></option>
	  <option value="7"><?= _('+ 1 week')?></option>
	  <option value="14"><?=_('+ 2 weeks')?></option>
	  <option value="30"><?=_('+ 1 month')?></option>
	</select>
        <img src="/imgs/icons/help.png"
              onmouseover="return escape('Cette option permet de d&eacute;caler la date de la transaction par rapport &agrave; la date de la facture');" />
	</td>
    </tr>
    <tr>
      <td nowrap><?=_('Account')?></td>
      <td>
        <select name="id_compte" style="width: 120px;">
        <?php
        $result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE type_pref='rib'") or wf_mysqldie();
        while ($cpt = mysql_fetch_object($result)) {
          $data = unserialize(base64_decode($cpt->value));
          printf('<option value="%d"%s>%s</option>', $cpt->id_pref, ($cpt->id_pref==$facture->id_compte)?" selected":"", $data->banque );
        }
        mysql_free_result($result);
        ?>
        </select>
      </td>
    </tr>
<?php
	if($currency!="€"){
?>
  <tr>
    <td nowrap><?=_('Exchange rate')?>&nbsp;1&euro; :</td>
    <td><input style="width:80px; text-align: center;" type="text" name="exchange_rate" value="<?=$facture->exchange_rate?>"/><?=$currency?></td>
    </tr>
<?
	    }
?>
    <tr>
      <td nowrap><?=_('TVA')?></td>
      <td>
	  <input style="width:45px; text-align: center;" type="text" name="tax" value="<?=$facture->taxe?>"/>%
      </td>
    </tr>

      <?php
      if ($facture->type_doc == "devis") { // CAS DEVIS  ?>
      <tr>
      <td colspan="2">
        <input type="checkbox" name="is_paye" <?= $facture->is_paye?"checked":"" ?> />&nbsp;Devis AcceptÃ©
        <?= ($facture->date_paiement!="")?"le ".$facture->nice_date_paiement:"" ?>
      </td>
      </tr>
      <?php } elseif ($facture->type_doc == "facture") { // CAS FACTURE ?>
      </tr>
      <tr>
       <td><input type="checkbox" name="is_envoye" <?= $facture->is_envoye?"checked":"" ?> />&nbsp;<?=_('Sent')?></td>
       <td><?
          if(empty($facture->timestamp_date_sent)){
	    makeDateField('date_sent', mktime());
	  }else{
	    makeDateField('date_sent', $facture->timestamp_date_sent);
	  }
           ?>
       </td>
      </tr>

      <tr>
       <td><input type="checkbox" name="is_paye" <?=$facture->is_paye?"checked":""?> />&nbsp;<?=_('Paid')?></td>
       <td><?
	  if(empty($facture->timestamp_date_paiement) or $facture->timestamp_date_paiement < $facture->timestamp_date_facture){
	    makeDateField('date_paiement', mktime());
	  }else{
	    makeDateField('date_paiement', $facture->timestamp_date_paiement);
	  }
           ?>
       </td>

      <?php } // FIN CAS FACTURE/DEVIS ?>

<!--
    <tr>
      <td colspan="2">
        <input type="checkbox" name="is_comptabilise" <?= $facture->is_comptabilise?"checked":"" ?> />&nbsp;ComptabilisÃ©
        <?= ($facture->date_comptabilise!="")?"le ".$facture->nice_date_comptabilise:"" ?>
      </td>
    </tr>
-->
    <tr>
      <td colspan="2">
        Commentaire : <br/>
        <textarea style="width: 200px;" name="commentaire"><?= $facture->commentaire ?></textarea>
        <?= ($facture->date_comptabilise!="")?"le ".$facture->nice_date_comptabilise:"" ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="liens_boutons">
      <a href="fiche_prospect.php?id=<?= $facture->id_client ?>"><?=_('Retour fiche client')?></a><br/>
      <a href="edit_facture.php?id_facture=new&id_client=<?= $facture->id_client ?>"><?=_('Create a new')?></a><br/>
      <a href="save_facture.php?id=<?= $facture->id_facture ?>&action=duplicate"><?=_('Duplicate')?></a><br/>
      <a href="gen_facture.php?dest=file&id=<?= $facture->id_facture ?>"><?= _('Send') ?></a><br/>
<?php
      $tr_ids = $Facture->getTransactions($facture->id_facture);
      foreach($tr_ids as $id_tr=>$text){
	printf('<a href="#" title="%s" onclick="inpagePopup(event, this, 440, 350, \'../cashflow/fiche_transaction.php?id=%d\');" >%s #%d</a><br/>',$text, $id_tr, _('Transaction'),$id_tr);
      }
?>
      <?php
        printf('<a href="gen_facture.php?id=%d">PDF</a><br/>', $facture->id_facture);
        if (! $facture->immuable)
          printf('<a href="save_facture.php?id_facture=%d&action=delete_facture" onclick="return ask_confirmation(\'%s\');">%s</a><br/>', $facture->id_facture,_('Do you really want to delete it ?'), _('Delete'));
      ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="text-align: center;">
      <input style="width: 90px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="submit_button" onclick="submitForm(this.form);" type="button" value="<?=_('Save')?>" />
      <input style="width: 90px; background: #eee; color: #7f7f7f; border: solid 1px #aaa;" id="cancel_button" type="button" onclick="window.location='fiche_prospect.php?id=<?= $facture->id_client ?>';" value="<?=_('Cancel')?>" />
      </td>
    </tr>
    </table>
  </td>
</tr>

<tr>

<td colspan="2">

<table width="500" border="0" cellspacing="0" cellpadding="2">
<tr class="row_header" style="font-weight: bold; text-align: center;">
  <td></td>
  <td width="400">Description</td>
  <td><?=_('Quantity')?></td>
  <td style="text-align: center;">PU HT</td>
  <td>Total</td>
</tr>
<?php
// Afficher une ligne de tableau pour chaque ligne de facturation
foreach ($facture->lignes as $l) {
  $total_ligne = $l->qtt * $l->prix_ht;
  $prix = number_format($l->prix_ht, 2, '.', ' ');
  print <<<EOF
<tr onmouseover="hoverLigne($l->id_facture_ligne);">
  <td><img border="0" src="/imgs/icons/facture_row_handle.gif" alt="" usemap="#facture_row_handle" /></td>
  <td><textarea style="height: 50px; width: 300px;" name="line_$l->id_facture_ligne">$l->description</textarea></td>
  <td align="center">
    <table border="0" cellspacing="0" cellpadding="0">
      <tr><td><input type="text" id="qtt_$l->id_facture_ligne" name="qtt_$l->id_facture_ligne" style="width: 40px; text-align: center" value="$l->qtt" /></td>
          <td style="line-height: 9px;">
            <a href="javascript:addQtt('$l->id_facture_ligne','$facture->taxe');"><img style="padding: 0px; margin: 0px;" src="/imgs/icons/plus.gif"/></a><br/>
            <a href="javascript:subQtt('$l->id_facture_ligne','$facture->taxe');"><img style="padding: 0px; margin: 0px;" src="/imgs/icons/moins.gif" onclick="subQtt()" /></a>
          </td>
      </tr>
    </table>
  </td>
  <td nowrap style="text-align: center;"><input type="text" onkeyup="updateTotal('$facture->taxe');" name="prix_ht_$l->id_facture_ligne" id="prix_ht_$l->id_facture_ligne" style="width: 50px; text-align: center" value="$prix" />&nbsp;$currency HT</td>
  <td nowrap><input type="text" id="total_$l->id_facture_ligne" name="total_$l->id_facture_ligne" onfocus="blur();" style="background: none; border: none; width: 40px; text-align: right" value="$total_ligne" />&nbsp;$currency HT</td>
</tr>
EOF;
}
if (! $facture->immuable) { // DÃ©but ajout une ligne
?>

<tr style="background: #cecece">
  <td></td>
  <td><textarea style="height: 30px; width: 300px;" name="line_new"></textarea></td>
  <td align="center">
    <table border="0" cellspacing="0" cellpadding="0">
      <tr><td><input type="text" id="qtt_new" name="qtt_new" style="width: 40px; text-align: center" value="1" /></td>
          <td style="line-height: 9px;">
            <a href="javascript:addQtt('new','<?=$facture->taxe?>');"><img style="padding: 0px; margin: 0px;" src="/imgs/icons/plus.gif"/></a><br/>
            <a href="javascript:subQtt('new','<?=$facture->taxe?>');"><img style="padding: 0px; margin: 0px;" src="/imgs/icons/moins.gif" onclick="subQtt()" /></a>
          </td>
      </tr>
    </table>
  </td>
  <td nowrap style="text-align: center;"><input type="text" onkeyup="updateTotal('<?= $facture->taxe?>');" name="prix_ht_new" id="prix_ht_new" style="width: 50px; text-align: center" value="0" />&nbsp;<?=$currency?> HT</td>
  <td nowrap><input type="text" id="total_new" name="total_new" onfocus="blur();" style="background: none; border: none; width: 40px; text-align: right" value="0" />&nbsp;<?=$currency?> HT</td>
</tr>
<?php
} // Fin ajout une ligne
?>

<tr><td colspan="4" style="text-align: right;">Total HT<input onfocus="blur();" type="text" name="total_ht" id="total_ht" style="text-align:right; border: none; width: 70px;" value="<?= $facture->nice_total_ht ?>" /><?=$currency?></td></tr>
<tr><td colspan="4" style="text-align: right;">TVA<input onfocus="blur();" type="text" id="tva" style="text-align:right; border: none; width: 70px;" value="<?= number_format($facture->total_ttc - $facture->total_ht,2, ',', ' '); ?>" /><?=$currency?></td></tr>
<tr><td colspan="4" style="text-align: right;"><b>Total</b><input onfocus="blur();" type="text" id="total_ttc" style="font-weight: bold; text-align:right; border: none; width: 70px;" value="<?= number_format($facture->total_ttc,2, ',', ' '); ?>" /><b><?=$currency?></b></td></tr>
</table>

</tr>

<tr>
  <td colspan="2">
  <b>Extra bottom</b><br/>
  <textarea style="width: 500px; height: 50px;" name="extra_bottom"><?= $facture->extra_bottom ?></textarea><br/>
  </td>
</tr>
</table>

</form>
<pre>
<?php // print_r($facture); ?>
</pre>

<?php
$Revision = '$Revision$';
include("../bottom.php");
?>
