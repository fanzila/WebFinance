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

global $Client, $User;
?>
<br/>

    <div style="overflow: auto; width: 700px; height: 550px;">
    <table width=100% border=0 cellspacing=0 cellpadding=1>
      <?php
        // Affichage des factures existantes pour ce client
        // Affichage par année, avec une séparation lisible
			    $q="SELECT YEAR(f.date_facture) as annee, ".
			    "SUM( IF(f.type_doc='facture', ".
			    "(fl.qtt*fl.prix_ht)/f.exchange_rate, 0)) as ca_ht_total, ".
			    "SUM( IF(f.type_doc='facture', IF(f.is_paye=0, (fl.qtt*fl.prix_ht)/f.exchange_rate, 0), 0)) as du_ht_total ".
			    "FROM webfinance_invoices f LEFT JOIN webfinance_invoice_rows fl ON f.id_facture=fl.id_facture ".
			    "WHERE f.id_client=$Client->id ".
			    "GROUP BY YEAR(date_facture) ".
			    "ORDER BY f.date_facture DESC";

        $result = mysql_query($q) or wf_mysqldie();

        $Facture = new Facture();
        while ($year = mysql_fetch_object($result)) {
          printf('<tr><td style="border-bottom: solid 1px #777;" colspan="5"><b style="font-size: 16px;">%s</b> - <b><i>Encours %s&euro; HT</i></b> - <i>%s&euro; HT</i></td></tr>',
		 $year->annee, number_format($year->du_ht_total, 2, ',', ' '), number_format($year->ca_ht_total, 2, ',', ' '));

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

	     list($currency,$exchange)=getCurrency($facture->id_compte);

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
                       <td class="euro" nowrap>%s %s HT</td>
                       <td class="euro" nowrap>%s %s TTC</td>
                       <td width="100%%" style="text-align: right;" nowrap><img src="/imgs/icons/%s" alt=""><a href="edit_facture.php?id_facture=%d"><img src="/imgs/icons/edit.png" border="0"></a>%s</td>
                     </tr>',
		    $description,
		    $facture->nice_date_facture, // FIXME : nice_date = option dans partie admin heritee par tous les objets penser 6 pour 2006
		    $facture->code_type_doc, $facture->num_facture,
		    number_format($facture->total_ht, 2, ',', ' '), $currency,
		    number_format($facture->total_ttc, 2, ',', ' '), // FIXME : Taux de TVA par facture
		    $currency,
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
<?php
    if($User->hasRole("manager",$_SESSION['id_user']) || $User->hasRole("employee",$_SESSION['id_user']) ){
      printf("<center><a href=\"edit_facture.php?id_facture=new&id_client=%d\" onclick=\"return ask_confirmation('%s');\" >%s</a></center>" ,
	     $Client->id , _('Confirm ?') , _('Create invoice/estimate'));
    }
?> 
