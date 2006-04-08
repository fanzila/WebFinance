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
// $Id$

include("inc/main.php");
include("top.php");
$language='fr_FR';
require('inc/gettext.php');
?>

<table border="0" cellspacing="5" cellpadding="0" class="mosaique">
<tr>
  <td colspan="2">
    Chiffre d'affaire mensuel (18 derniers mois, total, incluant les impayés <a href="showca.php">plus</a>)<br/>
    <a href="showca.php"><img src="ca_mensuel.php?width=600&height=250&nb_months=18" /></a>
  </td>
  <td width="250">
    Facturé mais non encore payé<br/>
    <a href="/prospection/facturation.php?type=unpaid"><img width="250" height="250" alt="Factures impayées" src="factures_impayees.php?width=250&height=250" /></a>
  </td>
</tr>
<tr valign="top">
  <td width="350">
    100 derniers évènements (<a href="/admin/events.php"><?=_('show all')?></a>)
    <div style="overflow: auto; height: 250px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="5">
    <?php
    $result = mysql_query("SELECT *,date_format(date,'%d/%m/%Y %k:%i') as nice_date FROM webfinance_userlog ORDER BY date DESC LIMIT 100");
    $count=1;
    while ($log = mysql_fetch_object($result)) {
      $class = ($count%2)==0?"odd":"even";
      $result2 = mysql_query("SELECT login FROM webfinance_users WHERE id_user=".$log->id_user);
      list($login) = mysql_fetch_array($result2);
      mysql_free_result($result2);

      $message = parselogline($log->log);

      print <<<EOF
    <tr class="row_$class">
      <td style="border:none;" nowrap>$log->nice_date</td>
      <td style="border:none;">$message</td>
      <td style="border:none;">$login</td>
    </tr>
EOF;
      $count++;
    }
    mysql_free_result($result);
    ?>
    </table>

  </td>
  <td width="250">
    TODO
    <div style="height: 250px; overflow: auto;">
    <ul>
      <li>Migrer cette TODO dans <a href="http://ovh.isvtec.com/mantis/">Mantis</a></li>
      <li>Plein de bugfixes à faire sur IE
      <li>W3C compliance (someday)
      <li>Implémenter "calculer TVA encaissée"
      <li>Implémenter "TVA par ligne de facture"
      <li>Implémenter "saisir dépense" et "graph tréso"
      <li>Implémenter "envoyer facture par mail"
      <li>Implémenter "facture récurente"
      <li>Gestion DNS sites clients par backoffice
      <li>Moteur de recherche backoffice (cherche dans les contacts et factures entre autres)
      <li>Pannier global (contient entreprises et personnes...) + vue composite d'un pannier
      <li><strike>Supprimer les références à facture.num_facture et utiliser facture.id_facture à la place</strike></li>
      <li><strike>afficher "123,45 € HT" au lieu de "123,45€HT"</strike></li>
      <li><strike>Carnet d'adresse backoffice consultable en LDAP</strike></li>
      <li><strike>Implémenter "type de prestation facturée" pour graph CA par type d'activité</strike></li>
      <li><strike>Devis accepté = devis payé et "disparait"</strike></li>
      <li><strike>Interdire la modification de factures déjà payées ou envoyées</strike>
    </ul>
    </div>
  </td>
  <td style="border: none;" ></td>
</tr>
</table>
