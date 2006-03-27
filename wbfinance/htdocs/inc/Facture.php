<?php 
// 
// This file is part of Â« Backoffice NBI Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<?php
//$Id$ 

class Facture {
  function Facture() {
  }

  function _markForRebuild($id) {
    mysql_query("UPDATE facture SET date_generated=NULL,pdf_file='' WHERE id_facture=$id");
  }

  function addLigne($id_facture, $desc, $pu_ht, $qtt) {
    $desc = preg_replace("/\'/", "\\'", $desc);
    $result = mysql_query("INSERT INTO facture_ligne (date_creation, id_facture, description, pu_ht, qtt) VALUES(now(), $id_facture, '$desc', '$pu_ht', $qtt)") or die(mysql_error());
    $this->_markForRebuild($id_facture);
  }

  function getTotal($id_facture) {
    $result = mysql_query("SELECT sum(qtt*pu_ht) FROM facture_ligne WHERE id_facture=$id_facture") or die(mysql_error());
    list($total) = mysql_fetch_array($result);
    mysql_free_result($result);

    return $total;
  }

  function getInfos($id_facture) {
    if (!is_numeric($id_facture)) {
      die("Facture:getInfos no id");
    }
    $result = mysql_query("SELECT c.id_client as id_client,c.nom as nom_client, c.addr1, c.addr2, c.addr3, c.cp, c.ville, c.vat_number,
                                  date_format(f.date_created,'%d/%m/%Y') as nice_date_created, 
                                  date_format(f.date_paiement, '%d/%m/%Y') as nice_date_paiement,  
                                  date_format(f.date_facture, '%d/%m/%Y') as nice_date_facture,  
                                  date_format(f.date_facture, '%s') as timestamp_date_facture,  
                                  date_format(f.date_facture, '%Y%m') as mois_facture,
                                  date_sent<now() as is_sent, 
                                  f.type_paiement, f.is_paye, f.ref_contrat, f.extra_top, f.extra_bottom, f.num_facture, f.*
                           FROM client as c, facture as f 
                           WHERE f.id_client=c.id_client 
                           AND f.id_facture=$id_facture") or die(mysql_error());
    $facture = mysql_fetch_object($result);

    $result = mysql_query("SELECT id_facture_ligne,prix_ht,qtt,description FROM facture_ligne WHERE id_facture=$id_facture ORDER BY ordre");
    $facture->lignes = Array();
    $total = 0;
    $count = 0;
    while ($el = mysql_fetch_object($result)) {
      array_push($facture->lignes, $el);
      $total += $el->qtt * $el->prix_ht;
      $count++;
    }
    mysql_free_result($result);
    $facture->nb_lignes = $count;
    $facture->total_ht = $total;
    $facture->total_ttc = $total*1.196;
    $facture->nice_total_ht = sprintf("%.2f", $facture->total_ht);
    $facture->nice_total_ttc = sprintf("%.2f", $facture->total_ttc);
    $facture->immuable = $facture->is_paye || $facture->is_sent;

    $result = mysql_query("SELECT nom FROM client WHERE id_client=".$facture->id_client);
    list($facture->nom_client) = mysql_fetch_array($result);
    mysql_free_result($result);


    return $facture;
  }

  /** Marque chaque ligne d'une facture comme "payée" 
   */
  function setPaid($id_facture) {
    // Marque toutes les lignes comme "payées"
    mysql_query("UPDATE facture SET date_paiement=now(),is_payee=1 WHERE id_facture=$id_facture") or die(mysql_error());
  }


  /** Renvoie vrai si la facture est générée au format PDF
    */
  function hasPdf($id) {
    $result = mysql_query("SELECT pdf_file FROM facture WHERE id_facture=$id");
    list($file) = mysql_fetch_array($result);
    mysql_free_result($result);

    if (file_exists($file)) 
      return true;
    else 
      return false;
  }
}

?>
