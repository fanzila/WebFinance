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
<?include "../inc/main.php" ?>
<?php

//$Id$

$Facture = new Facture();
if (is_numeric($_POST['id_facture'])) {
  $facture = $Facture->getInfos($_POST['id_facture']);
}

function update_ca() {
  global $facture;

  // FIXME : fucking mysql triggers somewhen ?
  mysql_query("UPDATE webfinance_clients SET ca_total_ht=0 where ca_total_ht!=0");
  $result = mysql_query("SELECT f.id_client as id_client,round(sum(fl.qtt*fl.prix_ht),0) as ca_total_ht
                          FROM webfinance_invoice_rows as fl, webfinance_invoices as f
                          WHERE fl.id_facture=f.id_facture
                          AND f.type_doc='facture'
                          GROUP BY f.id_client") or die(mysql_error());
  while ($ca = mysql_fetch_object($result)) {
    $q = sprintf("UPDATE webfinance_clients SET ca_total_ht='%.2f' WHERE id_client=%d",
                 $ca->ca_total_ht, $ca->id_client );
    mysql_query($q) or die(mysql_error());

  }
  mysql_free_result($result);

  mysql_query("UPDATE webfinance_clients SET ca_total_ht_year=0 where ca_total_ht_year!=0");
  $result = mysql_query("SELECT f.id_client as id_client,round(sum(fl.qtt*fl.prix_ht),0) as ca_total_ht_year
                          FROM webfinance_invoice_rows as fl, webfinance_invoices as f
                          WHERE fl.id_facture=f.id_facture
                          AND f.type_doc='facture'
                          AND f.date_facture>=date_sub(now(), INTERVAL 1 YEAR)
                          GROUP BY f.id_client") or die(mysql_error());
  while ($ca = mysql_fetch_object($result)) {
    $q = sprintf("UPDATE webfinance_clients SET ca_total_ht_year='%.2f' WHERE id_client=%d",
                 $ca->ca_total_ht_year, $ca->id_client );
    mysql_query($q) or die(mysql_error());

  }
  mysql_free_result($result);

  // TOTAL DU HT
  mysql_query("UPDATE webfinance_clients SET total_du_ht=0");
  $result = mysql_query("SELECT sum(prix_ht*qtt) as total_du_ht, f.id_client
                         FROM webfinance_invoice_rows fl, webfinance_invoices f
                         WHERE f.is_paye=0
                         AND f.type_doc='facture'
                         AND f.date_facture<=now()
                         AND f.id_facture=fl.id_facture
                         GROUP BY f.id_client") or die(mysql_error());
  while ($du = mysql_fetch_object($result)) {
    $q = sprintf("UPDATE webfinance_clients SET total_du_ht='%.2f' WHERE id_client=%d", $du->total_du_ht, $du->id_client );
    mysql_query($q) or die(mysql_error());
  }
}

function regenerate($id) {
  mysql_query("UPDATE webfinance_invoices SET date_generated=NULL,facture_file=NULL where id_facture=$id");
}

function renum() {
  $result = mysql_query("SELECT id_facture FROM webfinance_invoice_rows");
  while (list($id_facture) = mysql_fetch_array($result)) {
    $count = 1;
    $result2 = mysql_query("SELECT id_facture_ligne FROM webfinance_invoice_rows WHERE id_facture=$id_facture ORDER BY ordre");
    while (list($id_facture_ligne) = mysql_fetch_array($result2)) {
      mysql_query("UPDATE webfinance_invoice_rows SET ordre=$count WHERE id_facture_ligne=$id_facture_ligne");
      $count += 2;
    }
    mysql_free_result($result2);
  }
  mysql_free_result($result);
}

if ($GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'] == "POST") {
  $action = $_POST['action'];
} else {
  $action = $_GET['action'];
}

if ($action == "save_facture") {
  // save_facture
  // Enregistrement d'une facture existante
#  print "<pre>";
#  print_r($_POST);
  extract($_POST);

  // Enregistrement des paramètres facture
  preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $date_facture, $matches);
  $date_facture = $matches[3]."-".$matches[2]."-".$matches[1];

  if (($facture->is_envoye == 0) && ($is_envoye == "on")) {
    $result = mysql_query("SELECT count(*) FROM webfinance_invoices WHERE num_facture!='' AND id_facture!=".$facture->id_facture ." AND year(date_facture)=year('".$facture->date_facture."')") or die(mysql_error());
    list($nb) = mysql_fetch_array($result);
    mysql_free_result($result);

    $nb++;
    $nb = sprintf("%04d", $nb);
    $num_facture = strftime("%y-$nb", $facture->timestamp_date_facture);
  }

  $q = sprintf("UPDATE webfinance_invoices SET type_paiement='%s',is_paye=%d,%s ref_contrat='%s', extra_top='%s', extra_bottom='%s', accompte='%s', date_facture='%s', type_doc='%s', commentaire='%s', id_type_presta=%d, id_compte=%d, is_envoye=%d, num_facture='%s'
                WHERE id_facture='%d'",
               $type_paiement, ($is_paye == "on")?1:0, ($is_paye == "on")?"date_paiement=now(),":"", $ref_contrat, $extra_top, $extra_bottom, $accompte, $date_facture, $type_doc, $commentaire, $id_type_presta, $id_compte, ($is_envoye=="on")?1:0, $num_facture,
               $id_facture);
  mysql_query($q) or die(mysql_error());
  logmessage("Enregistrement de la facture fa:".$_POST['id_facture']);

  if ((is_numeric($_POST['prix_ht_new'])) && (is_numeric($_POST['qtt_new'])) && ($_POST['prix_ht_new'] > 0) && !empty($_POST['line_new'])) {
    // Enregistrement d'une nouvelle ligne de facturation pour une facture.
    $q = sprintf("INSERT INTO webfinance_invoice_rows (id_facture,description,prix_ht,qtt) VALUES(%d, '%s', '%s', '%s')",
                 $_POST['id_facture'], $_POST['line_new'], $_POST['prix_ht_new'], $_POST['qtt_new'] );
    $result = mysql_query($q) or die(mysql_error());
    mysql_query("UPDATE webfinance_invoices SET date_generated=NULL WHERE id_facture=".$_POST['id_facture']);
  }

  // Enregistrement des lignes existantes
  foreach ($_POST as $k=>$v) {
    if (preg_match("/^line_([0-9]+)$/", $k, $matches)) {
      $q = sprintf("UPDATE webfinance_invoice_rows SET description='%s', prix_ht='%s', qtt='%s' WHERE id_facture_ligne=%d",
                   $_POST['line_'.$matches[1]],
                   $_POST['prix_ht_'.$matches[1]],
                   $_POST['qtt_'.$matches[1]],
                   $matches[1] );
      mysql_query($q) or die(mysql_error());
    }
  }

  if (preg_match("/^raise:([0-9]+)$/", $_POST['raise_lower'], $matches)) {
    mysql_query("UPDATE webfinance_invoice_rows SET ordre=ordre-3 WHERE id_facture_ligne=".$matches[1]) or die(mysql_error());
    renum();
  }
  if (preg_match("/^lower:([0-9]+)$/", $_POST['raise_lower'], $matches)) {
    mysql_query("UPDATE webfinance_invoice_rows SET ordre=ordre+3 WHERE id_facture_ligne=".$matches[1]) or die(mysql_error());
    renum();
  }
  if (preg_match("/^delete:([0-9]+)$/", $_POST['raise_lower'], $matches)) {
    mysql_query("DELETE FROM webfinance_invoice_rows WHERE id_facture_ligne=".$matches[1]) or die(mysql_error());
    renum();
  }

  $q = sprintf("UPDATE webfinance_clients SET vat_number='%s' WHERE id_client=%d", $vat_number, $facture->id_client);
  mysql_query($q);

  update_ca();
  regenerate($_POST['id_facture']);
  header("Location: edit_facture.php?id_facture=".$_POST['id_facture']);
} elseif ($action == "delete_facture") {
  // delete_facture
  // Suppression d'une facture
  $result = mysql_query("SELECT id_client FROM webfinance_invoices WHERE id_facture=".$_GET['id_facture']);
  list($id_client) = mysql_fetch_array($result);
  mysql_free_result($result);

  logmessage("Suppression d'une facture pour client:$id_client");

  mysql_query("DELETE FROM webfinance_invoices WHERE id_facture=".$_GET['id_facture']);
  mysql_query("DELETE FROM webfinance_invoice_rows WHERE id_facture=".$_GET['id_facture']);

  update_ca();
  header("Location: fiche_prospect.php?id=$id_client");
} elseif ($action == "duplicate") {
  extract($_GET);

  $result = mysql_query("SELECT id_client FROM webfinance_invoices WHERE id_facture=$id");
  list($id_client) = mysql_fetch_array($result);
  mysql_free_result($result);

  mysql_query("INSERT INTO webfinance_invoices (id_client,date_created,date_facture) VALUES($id_client, now(), now())") or die(mysql_error());
  $result = mysql_query("SELECT id_facture FROM webfinance_invoices WHERE id_client=$id_client AND date_sub(now(), INTERVAL 2 SECOND)<date_created");
  list($id_new_facture) = mysql_fetch_array($result);
  mysql_free_result($result);

  // On recopie les données de la facture
  mysql_query("UPDATE webfinance_invoices as f1, webfinance_invoices as f2
               SET
                 f1.commentaire=f2.commentaire,
                 f1.type_paiement=f2.type_paiement,
                 f1.ref_contrat=f2.ref_contrat,
                 f1.extra_top=f2.extra_top,
                 f1.extra_bottom=f2.extra_bottom,
                 f1.id_type_presta=f2.id_type_presta
               WHERE f1.id_facture=$id_new_facture
                 AND f2.id_facture=$id") or die(mysql_error());

  mysql_query("INSERT INTO webfinance_invoice_rows (id_facture,description,qtt,ordre,prix_ht) SELECT $id_new_facture,description,qtt,ordre,prix_ht FROM webfinance_invoice_rows WHERE id_facture=$id") or die(mysql_error());

  header("Location: edit_facture.php?id_facture=$id_new_facture");
  die();
} else {
  die("Don't know what to do when asked to $action an invoice");
}


?>
