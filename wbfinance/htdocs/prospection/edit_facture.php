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
include("../inc/main.php");

if (!is_numeric($_GET['id_facture'])) {
  // Création de facture
  mysql_query("INSERT INTO webfinance_invoices (date_created,date_facture,id_client) values(now(), now(), ".$_GET['id_client'].")") or die(mysql_error());
  $result = mysql_query("SELECT id_facture FROM webfinance_invoices WHERE id_client=".$_GET['id_client']." AND date_sub(now(), INTERVAL 1 SECOND)<=date_created");
  list($id_facture) = mysql_fetch_array($result);
  header("Location: edit_facture.php?id_facture=".$id_facture);
  die();
}

include("../top.php");
include("nav.php");
$Facture = new Facture();
$facture = $Facture->getInfos($_GET['id_facture']);
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
  // Le devis est validé ou la facture est payée => pas possible de modifier ce doc
  ?>
  alert('Ce document ne peut être modifié');
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
  // Le devis est validé ou la facture est payée => pas possible de modifier ce doc
  ?>
  alert('Ce document ne peut être modifié');
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

function updateTotal() {
  changedData(null);
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
  ttc.value = number_format(total_ht*1.196, 2, ' ', ',');
  tva = document.getElementById('tva');
  tva.value = number_format(total_ht*0.196, 2, ' ', ',');
}
function addQtt(id) {
  qtt = document.getElementById('qtt_'+id);
  val = parseInt(qtt.value);
  val++;
  qtt.value = val;

  updateTotal();
}
function subQtt(id) {
  qtt = document.getElementById('qtt_'+id);
  val = parseInt(qtt.value);
  val--;
  if (val < 1) { val = 1; }
  qtt.value = val;

  updateTotal();
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

</script>

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
    <tr><td width="100"><?= ucfirst($facture->type_doc) ?> n°</td><td><input type="text" style="width:55px; text-align: center;" name="num_facture" value="<?= $facture->num_facture ?>" /><img src="/imgs/icons/help.png" onmouseover="return escape('Le numéro de facture est généré automatiquement lorsqu\'on marque la facture comme envoyée.<br/><br/>On peut forcer ce numéro arbitrairement mais souvenez-vous que la loi française oblige les numéros de facture à être séquenciels (pas de trous ni de YYYYMMDD');" /></td></tr>
    <tr><td>Date <?= $facture->type_doc ?></td><td><input style="text-align: center; width: 80px;" type="text" name="date_facture" value="<?= $facture->nice_date_facture ?>" /></td></tr>
    <tr><td>Code TVA Client</td><td><input style="width: 110px;" type="text" name="vat_number" value="<?= $facture->vat_number ?>" /></td></tr>
    <tr><td>Ref Contrat</td><td><input style="width: 200px;" type="text" name="ref_contrat" value="<?= $facture->ref_contrat ?>" /></td></tr>
    <tr><td>Paiement</td><td><input type="text" style="width: 200px;" name="type_paiement" value="<?=$facture->type_paiement ?>" /></td></tr>
    <tr><td>Accompte </td><td><input type="text" style="width: 50px; text-align: center;" name="accompte" value="<?= number_format($facture->accompte, 2, ' ', ','); ?>" />&euro; TTC</td></tr>
    <tr><td colspan="2">
    <textarea style="width: 300px; height: 40px" name="extra_top"><?= $facture->extra_top ?></textarea>
    </table>
  </td>

  <td valign="top">
  <?// Adresse = 5 champs valeur par défaut = addr client si modifié => adresse facturation affectée ?>
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
        <select name="id_type_presta" style="width: 100px;">
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
	<td>Prevision</td>
	<td>
	<select name="type_prev">
	  <option value="1"><?=_('asap')?></option>
	  <option value="7"><?= _('+ 1 week')?></option>
	  <option value="14"><?=_('+ 2 weeks')?></option>
	  <option value="30"><?=_('+ 1 month')?></option>
	</select>
	</td>
    </tr>
    <tr>
      <td nowrap>Compte</td>
      <td>
        <select name="id_compte" style="width: 100px;">
        <?php
        $result = mysql_query("SELECT id_pref,value FROM webfinance_pref WHERE type_pref='rib'") or die(mysql_error());
        while ($cpt = mysql_fetch_object($result)) {
          $data = unserialize(base64_decode($cpt->value));
          printf('<option value="%d"%s>%s</option>', $cpt->id_pref, ($cpt->id_pref==$facture->id_compte)?" selected":"", $data->banque );
        }
        mysql_free_result($result);
        ?>
        </select>
      </td>
    </tr>
    <tr>
      <?php
      if ($facture->type_doc == "devis") { // CAS DEVIS  ?>
      <td colspan="2">
        <input type="checkbox" name="is_paye" <?= $facture->is_paye?"checked":"" ?> />&nbsp;Devis Accepté
        <?= ($facture->date_paiement!="")?"le ".$facture->nice_date_paiement:"" ?>
      </td>
      <?php } elseif ($facture->type_doc == "facture") { // CAS FACTURE ?>
      <td colspan="2">
        <input type="checkbox" name="is_envoye" <?= $facture->is_envoye?"checked":"" ?> />&nbsp;Facture Envoyée<br/>
        <input type="checkbox" name="is_paye" <?= $facture->is_paye?"checked":"" ?> />&nbsp;Facture Payée
        <?= ($facture->date_paiement!="")?"le ".$facture->nice_date_paiement:"" ?><br/>
      </td>
      <?php } // FIN CAS FACTURE/DEVIS ?>
    </tr>
    <tr>
      <td colspan="2">
        <input type="checkbox" name="is_comptabilise" <?= $facture->is_comptabilise?"checked":"" ?> />&nbsp;Comptabilisé
        <?= ($facture->date_comptabilise!="")?"le ".$facture->nice_date_comptabilise:"" ?>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        Commentaire : <br/>
        <textarea style="width: 200px;" name="commentaire"><?= $facture->commentaire ?></textarea>
        <?= ($facture->date_comptabilise!="")?"le ".$facture->nice_date_comptabilise:"" ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="liens_boutons">
      <a href="fiche_prospect.php?id=<?= $facture->id_client ?>">Retour fiche client</a><br/>
      <a href="save_facture.php?id=<?= $facture->id_facture ?>&action=duplicate">Dupliquer <?= $facture->type_doc ?></a><br/>
      <?php
        printf('<a href="gen_facture.php?id=%d">PDF</a><br/>', $facture->id_facture);
        if (! $facture->immuable)
          printf('<a href="save_facture.php?id_facture=%d&action=delete_facture">Supprimer</a><br/>', $facture->id_facture);
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
  <td>Quantité</td>
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
            <a href="javascript:addQtt('$l->id_facture_ligne');"><img style="padding: 0px; margin: 0px;" src="/imgs/icons/plus.gif"/></a><br/>
            <a href="javascript:subQtt('$l->id_facture_ligne');"><img style="padding: 0px; margin: 0px;" src="/imgs/icons/moins.gif" onclick="subQtt()" /></a>
          </td>
      </tr>
    </table>
  </td>
  <td nowrap style="text-align: center;"><input type="text" onkeyup="updateTotal();" name="prix_ht_$l->id_facture_ligne" id="prix_ht_$l->id_facture_ligne" style="width: 50px; text-align: center" value="$prix" />&euro; HT</td>
  <td nowrap><input type="text" id="total_$l->id_facture_ligne" name="total_$l->id_facture_ligne" onfocus="blur();" style="background: none; border: none; width: 40px; text-align: right" value="$total_ligne" />&nbsp;&euro; HT</td>
</tr>
EOF;
}
if (! $facture->immuable) { // Début ajout une ligne
?>

<tr style="background: #cecece">
  <td></td>
  <td><textarea style="height: 30px; width: 300px;" name="line_new"></textarea></td>
  <td align="center">
    <table border="0" cellspacing="0" cellpadding="0">
      <tr><td><input type="text" id="qtt_new" name="qtt_new" style="width: 40px; text-align: center" value="1" /></td>
          <td style="line-height: 9px;">
            <a href="javascript:addQtt('new');"><img style="padding: 0px; margin: 0px;" src="/imgs/icons/plus.gif"/></a><br/>
            <a href="javascript:subQtt('new');"><img style="padding: 0px; margin: 0px;" src="/imgs/icons/moins.gif" onclick="subQtt()" /></a>
          </td>
      </tr>
    </table>
  </td>
  <td nowrap style="text-align: center;"><input type="text" onkeyup="updateTotal();" name="prix_ht_new" id="prix_ht_new" style="width: 50px; text-align: center" value="0" />&nbsp;&euro; HT</td>
  <td nowrap><input type="text" id="total_new" name="total_new" onfocus="blur();" style="background: none; border: none; width: 40px; text-align: right" value="0" />&nbsp;&euro; HT</td>
</tr>
<?php
} // Fin ajout une ligne
?>

<tr><td colspan="4" style="text-align: right;">Total HT<input onfocus="blur();" type="text" name="total_ht" id="total_ht" style="text-align:right; border: none; width: 70px;" value="<?= $facture->nice_total_ht ?>" />&euro;</td></tr>
<tr><td colspan="4" style="text-align: right;">TVA<input onfocus="blur();" type="text" id="tva" style="text-align:right; border: none; width: 70px;" value="<?= number_format($facture->total_ttc - $facture->total_ht,2, ',', ' '); ?>" />&euro;</td></tr>
<tr><td colspan="4" style="text-align: right;"><b>Total</b><input onfocus="blur();" type="text" id="total_ttc" style="font-weight: bold; text-align:right; border: none; width: 70px;" value="<?= number_format($facture->total_ttc,2, ',', ' '); ?>" /><b>&euro;</b></td></tr>
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
include("../bottom.php");
?>
