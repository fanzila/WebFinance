<?php 
// 
// This file is part of « Backoffice NBI »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<?php
// $Id$

include("../inc/backoffice.php");
include("../top.php");
include("nav.php");

$Facture = new Facture();
$total_ca_ht = 0;

$where_clause = "1";
if ($_GET['id_client']!="") {
  $where_clause .= " AND id_client=".$_GET['id_client'];
}
if ($_GET['mois'] != "") {
  $where_clause .= " AND date_format(date_facture, '%Y%m')='".$_GET['mois']."'"; 
}
if ($_GET['type']!="") {
  switch ($_GET['type']) {
    case "unpaid" : $where_clause .= " AND is_paye=0"; break;
    case "paid" : $where_clause .= " AND is_paye=2"; break;
  }
}

?>

<table border="0" cellspacing="5" cellpadding="0">
<tr valign="top"><td>

<table border=0 cellspacing=0 cellpadding=3 style="border: solid 1px black;">
<tr align=center class=row_header> 
  <td></td>
  <td>Date</td>
  <td colspan="2">Numéro</td>
  <td>Client</td>
  <td>HT</td>
  <td>TTC</td>
  <td></td>
</tr>
<?php
$total_ca_ht = 0;
$result = mysql_query("SELECT id_facture FROM facture WHERE type_doc='facture' AND num_facture!='' AND date_facture<=now() AND ".$where_clause." ORDER BY date_facture DESC") or die(mysql_error());
$mois = array();
while (list($id_facture) = mysql_fetch_array($result)) {
  $count++;
  $fa = $Facture->getInfos($id_facture);
  $class = ($count%2 == 0)?"even":"odd";
  $icon = $fa->is_paye?"paid":"not_paid";
  $total_ca_ht += $fa->total_ht;
  $mois[$fa->mois_facture]++;

   $description = "";
   $result2 = mysql_query("SELECT description FROM facture_ligne WHERE id_facture=".$fa->id_facture);
   while (list($desc) = mysql_fetch_array($result2)) {
     $desc = preg_replace("/\r\n/", " ", $desc);
     $desc = preg_replace("/\"/", "", $desc);
     $desc = preg_replace("/\'/", "", $desc);
     $description .= $desc."<br/>";
   }
   mysql_free_result($result2);

  print <<<EOF
<tr onmouseover="return escape('$description');" class="row_$class">
  <td>$count</td>
  <td>$fa->nice_date_facture</td>
  <td><img src="/imgs/icons/$icon.gif" alt="$icon" /></td>
  <td>FA$fa->num_facture</td>
  <td><a href="fiche_prospect.php?id=$fa->id_client">$fa->nom_client</a></td>
  <td>$fa->nice_total_ht&euro;</td>
  <td>$fa->nice_total_ttc&euro;</td>
  <td><a href="gen_facture.php?id=$fa->id_facture"><img src="/imgs/icons/pdf.gif" alt="Chopper" /></a>
      <a href="edit_facture.php?id_facture=$fa->id_facture"><img src="/imgs/icons/edit.gif" alt="Modifier" /></a></td>
</tr>
EOF;
}
?>
</table>
<br/>

</td><td>

<form action="facturation.php" method="get">
<table border="0" cellspacing="0" cellpadding="3" style="border: solid 1px black;">
<tr align="center" class="row_header"> 
  <td colspan="2">Filtre simple</td>
</tr>
<tr>
  <td>Total CA</td>
  <td>
    <b><?= number_format($total_ca_ht, 2, ',', ' ') ?>&euro; HT</b> / 
    <b><?= number_format(1.196*$total_ca_ht, 2, ',', ' ') ?>&euro; TTC</b>
  </td>
</tr>
<tr>
  <td>Client</td>
  <td>
    <select style="width: 200px;" name="id_client" onchange="this.form.submit();" >
      <option value="">Tous</option>
      <?php
      $result = mysql_query("SELECT c.id_client,c.nom 
                             FROM client as c, facture as f
                             WHERE f.id_client=c.id_client
                             GROUP BY c.id_client
                             ORDER BY nom");
      while (list($id, $nom) = mysql_fetch_array($result)) {
        printf('<option value="%d"%s>%s</option>'."\n", $id, ($id==$_GET['id_client'])?" selected":"", $nom);
      }
      ?>
    </select>
  </td>
</tr>
<tr>
  <td>Type</td>
  <td>
    <select name="type" onchange="this.form.submit();" >
    <?php
    $choices = array(
                 "Toutes" => "",
                 "Impayées" => "unpaid",
                 "Payées" => "paid",
               );
    foreach ($choices as $n=>$v) {
      printf('<option value="%s"%s>%s</option>', $v, ($v==$_GET['type'])?" selected":"", $n);
    }
    ?>
    </select>
  </td>
</tr>
<tr>
  <td>Mois</td>
  <td>
    <select name="mois" onchange="this.form.submit();" >
    <option value="">Tous</option>
    <?php
    ksort($mois);
    foreach ($mois as $n=>$v) {
      printf('<option value="%s"%s>%s (%d factures)</option>', $n, ($n==$_GET['mois'])?" selected":"", $n, $v);
    }
    ?>
    </select>
  </td>
</tr>
</table>
</form>

</td></tr>
</table>

<?php

include("../bottom.php");

?>
