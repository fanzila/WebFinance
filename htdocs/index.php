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
?>
<?
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id: index.php 552 2007-08-02 07:07:04Z gassla $

require_once("inc/main.php");
$title = _("Home");
$roles="manager,accounting,employee";
require_once("top.php");
?>
<table border="0" cellspacing="5" cellpadding="0" class="mosaique">
<tr>
  <td>
    Chiffre d\'affaires mensuel (18 derniers mois, total, incluant les impayés <a href="showca.php">plus</a>)<br/>
    <a href="showca.php"><img src="/graphs/ca_mensuel.php?width=600&height=250&nb_months=18&grid=0" /></a>
  </td>
  <td width="350">
    Facturé mais non encore payé<br/>
    <a href="/prospection/facturation.php?type=unpaid"><img width="350" height="250" alt="Factures impayées" src="/graphs/factures_impayees.php?width=350&height=250" /></a>
  </td>
</tr>
<tr valign="top">
  <td>
  </td>
  <td width="250">
    100 derniers évènements (<a href="/admin/events.php"><?=_('show all')?></a>)
    <div style="overflow: auto; height: 250px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="5">
    <?php
       $query= "SELECT id_userlog,log,wul.date,wul.id_user,wu.login,wi.id_facture,wi.num_facture,wc.id_client," .
               "       wc.nom as nom_client, date_format(wul.date,'%d/%m/%y %k:%i') as nice_date, " .
               "       document.filename, document.id AS id_document ".
               "FROM webfinance_userlog wul ".
               "JOIN webfinance_users wu on (wu.id_user = wul.id_user)  " .
               "LEFT JOIN webfinance_invoices wi on wul.id_facture = wi.id_facture " .
               "LEFT JOIN webfinance_clients wc on wul.id_client = wc.id_client " .
               "LEFT JOIN document on (wc.id_client = document.id_client)  " .
               "ORDER BY wul.date DESC limit 100";

       $result = mysql_query($query)
         or die(mysql_error());
       $count=1;
   
       while ($log = mysql_fetch_object($result)) {
           $class = ($count%2)==0?"odd":"even";

           $message = $log->log;
           $message = ((!empty($log->id_facture)) ? 
                          str_replace('fa:'.$log->id_facture, '<a href="/prospection/edit_facture.php?id_facture='.$log->id_facture.'">'
                          .$log->num_facture.'</a> <a href="/prospection/gen_facture.php?id='
                          .$log->id_facture.'"><img src="/imgs/icons/pdf.png" valign="bottom"></a>',$message) : $message);
          
           $message = ((!empty($log->login)) ?  
                           str_replace('user:'.$log->id_user,'<a href="/admin/fiche_user.php?id='.$log->id_user.'">'.$log->login.'</a>', $message) : $message);

           $message = ((!empty($log->nom_client)) ?
                           str_replace('client:'.$log->id_client,'<a href="/prospection/fiche_prospect.php?id='.$log->id_client.'">'.$log->nom_client.'</a>',$message) : $message);

           $message = ((!empty($log->filename)) ?
                           str_replace('doc:'.$log->id_document,'<a href="/prospection/document/download.php?id='.$log->id_document.'">'.$log->filename.'</a>',$message) : $message);
                       
         
      print <<<EOF
    <tr class="row_$class">
      <td style="border:none;" nowrap>$log->nice_date</td>
      <td style="border:none;">$message</td>
      <td style="border:none;">$log->login</td>
    </tr>
EOF;
      $count++;
    }
    mysql_free_result($result);
    ?>
    </table>

  </td>
  <!--
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
  -->
</tr>
</table>

<?php
$Revision = '$Revision: 552 $';
include("bottom.php");
?>
