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

require_once("../inc/main.php");
must_login();

//$Id: save_facture.php 561 2007-08-02 09:15:44Z gassla $

$Facture = new Facture();
$id_facture=-1;

if (isset($_POST['id_facture']) && is_numeric($_POST['id_facture']))
  $id_facture=$_POST['id_facture'];
else if (isset($_GET['id_facture']) && is_numeric($_GET['id_facture']))
  $id_facture=$_GET['id_facture'];

if($id_facture>0)
  $facture = $Facture->getInfos($id_facture);

function regenerate($id) {
  mysql_query("UPDATE webfinance_invoices SET date_generated=NULL,facture_file=NULL where id_facture=$id") or wf_mysqldie();
}

function renum() {
  $result = mysql_query("SELECT id_facture FROM webfinance_invoice_rows") or wf_mysqldie();
  while (list($id_facture) = mysql_fetch_array($result)) {
    $count = 1;
    $result2 = mysql_query("SELECT id_facture_ligne FROM webfinance_invoice_rows WHERE id_facture=$id_facture ORDER BY ordre") or wf_mysqldie();
    while (list($id_facture_ligne) = mysql_fetch_array($result2)) {
      mysql_query("UPDATE webfinance_invoice_rows SET ordre=$count WHERE id_facture_ligne=$id_facture_ligne") or wf_mysqldie();
      $count += 2;
    }
    mysql_free_result($result2);
  }
  mysql_free_result($result);
}

$action='';

if(isset($_GET['action']))
	$action = $_GET['action'];

if(isset($_POST['action']))
	$action = $_POST['action'];

if ($action == "save_facture") {
	extract($_POST);

	if(!isset($id_compte)) {
		header("Location: ../admin/societe");
		exit;
	}

  if(!isset($is_envoye))
    $is_envoye='off';

  if(!isset($is_paye))
    $is_paye='off';

  // Enregistrement des paramÃ¨tres facture
  preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $date_facture, $ma);
  $date_facture = $ma[3]."/".$ma[2]."/".$ma[1];
  $date_facture_ts = mktime(0,0,0,$ma[2],$ma[1],$ma[3]);

  preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $date_paiement, $ma);
  $date_paiement_ts = mktime(0,0,0,$ma[2],$ma[1],$ma[3])+(86400*$type_prev);
  $date_paiement = date("Y/m/d",$date_paiement_ts );

  preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $date_sent, $ma);
  $date_sent = $ma[3]."/".$ma[2]."/".$ma[1];
  $date_sent_ts = mktime(0,0,0,$ma[2],$ma[1],$ma[3]);

  if( ($facture->is_sent == 0) && ($is_envoye == "on") && empty($num_facture) ) {
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

  // Generate periodic_next_deadline if needed
  if($periodic_next_deadline=='0000-00-00' and $period!='none') {
	  $periodic_next_deadline=$Facture->nextDeadline(date('Y-m-d'), $period);
  }

  $q = sprintf("UPDATE webfinance_invoices SET ".
               "id_client=%d, " .
               "type_paiement='%s', ".
               "is_paye=%d, ".
               "%s  ".
               "is_envoye=%d, ".
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
               "tax='%s', ".
               "exchange_rate='%s', ".
               "period='%s', ".
               "periodic_next_deadline='%s', ".
               "payment_method='%s', ".
               "delivery='%s' ".
               "%s ".
               "WHERE id_facture=%d",
               $id_client,
               $type_paiement,
               ($is_paye=="on")?1:0,
               ($is_paye=="on"  || $type_prev>0)?"date_paiement='$date_paiement', ":"date_paiement='$date_facture' , ",
               ($is_envoye=="on")?1:0,
               ($is_envoye=="on")?"date_sent='$date_sent', ":"date_sent='$date_facture' , ",
               $ref_contrat,
               $extra_top,
               $extra_bottom,
               WFO::stripMonetaryFormat($accompte),
               $date_facture,
               $type_doc,
               $commentaire,
               $id_type_presta,
               $id_compte,
               ($is_envoye=="on")?1:0,
               $tax,
               (empty($exchange_rate))?1:$exchange_rate,
               $period,
               $periodic_next_deadline,
               $payment_method,
               $delivery,
               ($dup_num_inv==0)?",num_facture='$num_facture' ":"" ,
               $id_facture);
  
  mysql_query($q)
	  or die(mysql_error());

  logmessage(_("Save invoice")." (#$num_facture) fa:".$_POST['id_facture']." client:$facture->id_client", $facture->id_client, $_POST['id_facture']);

  if(empty($_POST['prix_ht_new']))
	  $_POST['prix_ht_new']='0.0';

  if ((is_numeric($_POST['prix_ht_new'])) && (is_numeric($_POST['qtt_new'])) &&
	  !empty($_POST['prix_ht_new']) && !empty($_POST['line_new'])) {
    // Enregistrement d'une nouvelle ligne de facturation pour une facture.
    
    
    $q = sprintf("INSERT INTO webfinance_invoice_rows (id_facture,description,prix_ht,qtt,ordre) ".
                 "SELECT %d, '%s', %s, %s, MAX(ordre) + 1 ".
		 "FROM webfinance_invoice_rows ".
		 "WHERE id_facture=%d",
                 $_POST['id_facture'],
				 mysql_real_escape_string($_POST['line_new']),
		 $_POST['prix_ht_new'], $_POST['qtt_new'], $_POST['id_facture']);

    $result = mysql_query($q) or wf_mysqldie();
    mysql_query("UPDATE webfinance_invoices SET date_generated=NULL WHERE id_facture=".$_POST['id_facture']) or wf_mysqldie();
  }

  // Enregistrement des lignes existantes
  foreach ($_POST as $k=>$v) {
    if (preg_match("/^line_([0-9]+)$/", $k, $matches)) {
      $q = sprintf("UPDATE webfinance_invoice_rows SET description='%s', prix_ht='%s', qtt='%s' WHERE id_facture_ligne=%d",
                   mysql_real_escape_string($_POST['line_'.$matches[1]]),
                   str_replace(' ','',$_POST['prix_ht_'.$matches[1]]),
                   $_POST['qtt_'.$matches[1]],
                   $matches[1] );
      mysql_query($q) or die(mysql_error());
    }
  }


  $q = sprintf("UPDATE webfinance_clients SET vat_number='%s' WHERE id_client=%d", $vat_number, $facture->id_client);
  mysql_query($q);

  regenerate($_POST['id_facture']);

  if($dup_num_inv){
    $_SESSION['message'] = _('Duplicate invoice number')."<br/>"._('Invoice updated') ;
    $_SESSION['error'] = 1;
  } /* else */
    /* $_SESSION['message'] = _('Invoice updated'); */

  if($type_doc=="facture" || ($type_doc=="devis" &&  $is_paye=="on" )){
    $Facture->updateTransaction($_POST['id_facture'],$type_prev);
    /* $_SESSION['message'] .=  "<br>"._('Transaction updated'); */
  }

  header("Location: edit_facture.php?id_facture=".$_POST['id_facture']);
  exit;
}

if ($action == "delete_facture") {
  $id_client="";

  // delete_facture
  // Suppression d'une facture
  $Facture = new Facture();
  if (is_numeric($_GET['id_facture']) AND $Facture->exists($_GET['id_facture'])) {
    $facture = $Facture->getInfos($_GET['id_facture']);

    logmessage(_("Delete invoice")." #$facture->num_facture for client:$facture->id_client", $facture->id_client);
    $id_client=$facture->id_client;

    mysql_query("DELETE FROM webfinance_invoices WHERE id_facture=".$_GET['id_facture']) or wf_mysqldie();
    $_SESSION['message'] = _('Invoice deleted');
    //mysql_query("DELETE FROM webfinance_invoice_rows WHERE id_facture=".$_GET['id_facture']); <- ON DELETE CASCADE
    mysql_query("DELETE FROM webfinance_transactions WHERE id_invoice=".$_GET['id_facture']." AND type<>'real'") or wf_mysqldie();;
    $_SESSION['message'] .= "<br/>"._('Transaction deleted');

  }
  header("Location: fiche_prospect.php?onglet=biling&tab=biling&id=$id_client");
  exit;
}

if ($action == "duplicate") {
  extract($_GET);

  $Invoice = new Facture();
  $id_new_facture = $Invoice->duplicate($id);

  if($id_new_facture){
      logmessage("New invoice fa:$id_new_facture duplicated of fa:$id ", 'NULL', $id);
    $Invoice->updateTransaction($id_new_facture);
    $_SESSION['message'] = _("Invoice duplicated");
    header("Location: edit_facture.php?id_facture=$id_new_facture");
    die();
  } else {
    //Error;
    die("duplicate action failed");
  }
  die();

  }

if($action == "send"){

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

    $mail->Subject = stripslashes(utf8_decode($subject)) ;
    $mail->Body = stripslashes(utf8_decode($body)) ;

    $mail->WordWrap = 80;

    //générer la facture en pdf
    //$fp = fopen("http://".$_SERVER['SERVER_NAME']."/prospection/gen_facture.php?dest=file&id=$id","r");
    //fclose($fp);

    //attach the invoice file
    $file_name=ucfirst($invoice->type_doc)."_".$invoice->num_facture."_".preg_replace("/[ ]/", "_", $invoice->nom_client).".pdf";
    $path="/tmp/".$file_name;

    if(file_exists($path)){
      $mail->AddAttachment($path , $file_name,'base64','application/pdf');

      if(!$mail->Send()){
	$_SESSION['message'] = _('Invoice was not sent');
	$_SESSION['error'] = 1;
	echo _("Invoice was not sent");
	echo "Mailer Error: " . $mail->ErrorInfo;

      } else{
	/* $_SESSION['message'] = _('Invoice sent'); */
	//mettre à jour l'état de la facture, update sql
	mysql_query("UPDATE webfinance_invoices SET is_envoye=1 WHERE id_facture=$id ")
	  or wf_mysqldie();
	/* $_SESSION['message'] .= "<br/>"._('Invoice updated'); */

	logmessage(_("Send invoice")." #$invoice->num_facture fa:$id client:$invoice->id_client", $invoice->id_client,$id);
      }

      //delete the file generated
      unlink($path);

    }else{
      $_SESSION['message'] = _('Invoice file doesn\'t exist!');
      $_SESSION['message'] .= "<br/>"._('Invoice was not sent');
      $_SESSION['error'] = 1;
      echo _("The attachment doesn't exist!");
    }

    header("Location: edit_facture.php?id_facture=$id");
    die();

  }
  else {
	  echo _("Please add mail address!");
	  exit;
  }
}

  die("Don't know what to do when asked to $action an invoice");


?>
