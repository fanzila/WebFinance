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
<?include "../inc/main.php" ?>
<?php

//$Id$
  //echo "<pre>"; print_r($_POST);
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
                          GROUP BY f.id_client") or wf_mysqldie();
  while ($ca = mysql_fetch_object($result)) {
    $q = sprintf("UPDATE webfinance_clients SET ca_total_ht='%.2f' WHERE id_client=%d",
                 $ca->ca_total_ht, $ca->id_client );
    mysql_query($q) or wf_mysqldie();

  }
  mysql_free_result($result);

  mysql_query("UPDATE webfinance_clients SET ca_total_ht_year=0 where ca_total_ht_year!=0");
  $result = mysql_query("SELECT f.id_client as id_client,round(sum(fl.qtt*fl.prix_ht),0) as ca_total_ht_year
                          FROM webfinance_invoice_rows as fl, webfinance_invoices as f
                          WHERE fl.id_facture=f.id_facture
                          AND f.type_doc='facture'
                          AND f.date_facture>=date_sub(now(), INTERVAL 1 YEAR)
                          GROUP BY f.id_client") or wf_mysqldie();
  while ($ca = mysql_fetch_object($result)) {
    $q = sprintf("UPDATE webfinance_clients SET ca_total_ht_year='%.2f' WHERE id_client=%d",
                 $ca->ca_total_ht_year, $ca->id_client );
    mysql_query($q) or wf_mysqldie();

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
                         GROUP BY f.id_client") or wf_mysqldie();
  while ($du = mysql_fetch_object($result)) {
    $q = sprintf("UPDATE webfinance_clients SET total_du_ht='%.2f' WHERE id_client=%d", $du->total_du_ht, $du->id_client );
    mysql_query($q) or wf_mysqldie();
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

  // Enregistrement des paramÃ¨tres facture
  preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $date_facture, $matches);
  $date_facture = $matches[3]."-".$matches[2]."-".$matches[1];

  if (($facture->is_envoye == 0) && ($is_envoye == "on")) {
    $result = mysql_query("SELECT count(*) FROM webfinance_invoices
                           WHERE num_facture!=''
                           AND year(date_facture)=year('".$facture->date_facture."')") or wf_mysqldie();
    list($nb) = mysql_fetch_array($result);
    mysql_free_result($result);

    $nb++;
    $nb = sprintf("%04d", $nb);
    $num_facture = strftime("%y-$nb", $facture->timestamp_date_facture);
  }
  //date prev
//   $date_prev=$facture->timestamp_date_facture+($_POST['type_prev'] * 86400 );
//   $date_prev=date("Y-m-d",$date_prev);

  $res = mysql_query("SELECT count(*) FROM webfinance_invoices WHERE num_facture='$num_facture' AND id_facture<>$id_facture ")
    or wf_mysqldie();
  list($dup_num_inv) = mysql_fetch_array($res);
  mysql_free_result($res);

  $q = sprintf("UPDATE webfinance_invoices SET ".
	       "type_paiement='%s', ".
	       "is_paye=%d, ".
	       "%s  ".
	       "ref_contrat='%s', ".
	       "extra_top='%s', ".
	       "extra_bottom='%s', ".
	       "accompte='%s', ".
	       "date_facture='%s', ".
	       "type_doc='%s', ".
	       "commentaire='%s', ".
	       "id_type_presta=%d, ".
	       "id_compte=%d, ".
	       "is_envoye=%d, ".
	       "period='%s' ".
	       "%s ".
	       "WHERE id_facture='%d'",
               $type_paiement,
	       ($is_paye == "on")?1:0,
	       ($is_paye == "on")?"date_paiement=now(), ":"date_paiement='$date_prev' , ",
	       $ref_contrat,
	       $extra_top,
	       $extra_bottom,
	       $accompte,
	       $date_facture,
	       $type_doc,
	       $commentaire,
	       $id_type_presta,
	       $id_compte,
	       ($is_envoye=="on")?1:0,
	       $period,
	       ($dup_num_inv==0)?",num_facture='$num_facture' ":"" ,
               $id_facture);

  mysql_query($q) or wf_mysqldie();

  logmessage(_("Save invoice")." (#$num_facture) fa:".$_POST['id_facture']." client:$facture->id_client");

  if ((is_numeric($_POST['prix_ht_new'])) && (is_numeric($_POST['qtt_new'])) && !empty($_POST['line_new'])) {
    // Enregistrement d'une nouvelle ligne de facturation pour une facture.
    $q = sprintf("INSERT INTO webfinance_invoice_rows (id_facture,description,prix_ht,qtt) VALUES(%d, '%s', '%s', '%s')",
                 $_POST['id_facture'], $_POST['line_new'], $_POST['prix_ht_new'], $_POST['qtt_new'] );
    $result = mysql_query($q) or wf_mysqldie();
    mysql_query("UPDATE webfinance_invoices SET date_generated=NULL WHERE id_facture=".$_POST['id_facture']) or wf_mysqldie();
  }

  // Enregistrement des lignes existantes
  foreach ($_POST as $k=>$v) {
    if (preg_match("/^line_([0-9]+)$/", $k, $matches)) {
      $q = sprintf("UPDATE webfinance_invoice_rows SET description='%s', prix_ht='%s', qtt='%s' WHERE id_facture_ligne=%d",
                   $_POST['line_'.$matches[1]],
                   str_replace(' ','',$_POST['prix_ht_'.$matches[1]]),
                   $_POST['qtt_'.$matches[1]],
                   $matches[1] );
      mysql_query($q) or wf_mysqldie();
    }
  }

  if (preg_match("/^raise:([0-9]+)$/", $_POST['raise_lower'], $matches)) {
    mysql_query("UPDATE webfinance_invoice_rows SET ordre=ordre-3 WHERE id_facture_ligne=".$matches[1]) or wf_mysqldie();
    renum();
  }
  if (preg_match("/^lower:([0-9]+)$/", $_POST['raise_lower'], $matches)) {
    mysql_query("UPDATE webfinance_invoice_rows SET ordre=ordre+3 WHERE id_facture_ligne=".$matches[1]) or wf_mysqldie();
    renum();
  }
  if (preg_match("/^delete:([0-9]+)$/", $_POST['raise_lower'], $matches)) {
    mysql_query("DELETE FROM webfinance_invoice_rows WHERE id_facture_ligne=".$matches[1]) or wf_mysqldie();
    renum();
  }

  $q = sprintf("UPDATE webfinance_clients SET vat_number='%s' WHERE id_client=%d", $vat_number, $facture->id_client);
  mysql_query($q);

  update_ca();
  regenerate($_POST['id_facture']);

  if($dup_num_inv)
    $_SESSION['message'] = _('Duplicate invoice number')."<br/>"._('Invoice updated') ;
  else
    $_SESSION['message'] = _('Invoice updated');

  $Facture->updateTransaction($_POST['id_facture'],$type_prev);

  $_SESSION['message'] .=  "<br>"._('Transaction updated');

  header("Location: edit_facture.php?id_facture=".$_POST['id_facture']);

} elseif ($action == "delete_facture") {
  $id_client="";

  // delete_facture
  // Suppression d'une facture
  $Facture = new Facture();
  if (is_numeric($_GET['id_facture']) AND $Facture->exists($_GET['id_facture'])) {
    $facture = $Facture->getInfos($_GET['id_facture']);

    logmessage(_("Delete invoice")." #$facture->num_facture for client:$facture->id_client");
    $id_client=$facture->id_client;

    mysql_query("DELETE FROM webfinance_invoices WHERE id_facture=".$_GET['id_facture']) or wf_mysqldie();
    $_SESSION['message'] = _('Invoice deleted');
    //mysql_query("DELETE FROM webfinance_invoice_rows WHERE id_facture=".$_GET['id_facture']); <- ON DELETE CASCADE
    mysql_query("DELETE FROM webfinance_transactions WHERE id_invoice=".$_GET['id_facture']." AND type<>'real'") or wf_mysqldie();;
    $_SESSION['message'] .= "<br/>"._('Transaction deleted');
    update_ca();

  }
  header("Location: fiche_prospect.php?id=$id_client");

} elseif ($action == "duplicate") {
  extract($_GET);

  $Invoice = new Facture();
  $id_new_facture = $Invoice->duplicate($id);

  if($id_new_facture){
    $Invoice->updateTransaction($id_new_facture);
    $_SESSION['message'] = _("Invoice duplicated");
    header("Location: edit_facture.php?id_facture=$id_new_facture");
    die();
  } else {
    //Error;
    die("duplicate action failed");
  }
  die();

  }else if($action == "send"){

  extract($_POST);
  require("/usr/share/php/libphp-phpmailer/class.phpmailer.php");

  $mail_addresses = explode(',',$mails2);
  $mails = array_merge($mail_addresses,$mails);

  if(count($mails)>0){

    //récupérer les info sur la société
    $result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1")
      or wf_mysqldie();
    list($value) = mysql_fetch_array($result);
    mysql_free_result($result);
    $societe = unserialize(base64_decode($value));

    //récupération des infos sur la facture
    $Facture = new Facture();
    $invoice = $Facture->getInfos($id);

    //compléter l'entête de l'email
    $mail = new PHPMailer();
    if(preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-Za-z]{2,4}$/',$from) )
      $mail->From = $from;
    else
      $mail->From = $societe->email;

    $mail->FromName = $from_name;

    foreach($mails as $address)
      $mail->AddAddress($address);

    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->WordWrap = 80;

    //générer la facture en pdf
    //$fp = fopen("http://".$_SERVER['SERVER_NAME']."/prospection/gen_facture.php?dest=file&id=$id","r");
    //fclose($fp);

    //attach the invoice file
    $file_name=ucfirst($invoice->type_doc)."_".$invoice->num_facture."_".preg_replace("/[ ]/", "_", $invoice->nom_client).".pdf";
    $path="/tmp/".$file_name;

    if(file_exists($path)){
      $mail->AddAttachment($path , $file_name);

      if(!$mail->Send()){
	$_SESSION['message'] = _('Invoice was not sent');
	echo _("Invoice was not sent");
	echo "Mailer Error: " . $mail->ErrorInfo;

      } else{
	$_SESSION['message'] = _('Invoice sent');
	//mettre à jour l'état de la facture, update sql
	mysql_query("UPDATE webfinance_invoices SET is_envoye=1")
	  or wf_mysqldie();
	$_SESSION['message'] .= "<br/>"._('Invoice updated');

	logmessage(_("Send invoice")." #$invoice->num_facture fa:$id client:$invoice->id_client");
      }

      //delete the file generated
      unlink($path);

    }else{
      $_SESSION['message'] = _('Invoice file doesn\'t exist!');
      $_SESSION['message'] .= "<br/>"._('Invoice was not sent');
      echo _("The attachment doesn't exist!");
    }

    header("Location: edit_facture.php?id_facture=$id");
    die();

  }else
    echo _("Please add mail address!");


 }else {
  die("Don't know what to do when asked to $action an invoice");
}


?>
