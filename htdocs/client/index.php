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
// $Id: index.php 531 2007-06-13 12:32:31Z thierry $

include("../inc/main.php");
$title = _("Client");
$roles="manager,accounting,employee,client";
include("../top.php");

$client_role="client";
$is_client=false;

$User = new User();

$user =  $User->getInfo();
$roles = explode(",",$user->role);

$Facture = new Facture();
$total_ca_ht = 0;

$where_clause = "1";
if (isset($_GET['id_client']) AND $_GET['id_client']!="") {
  $where_clause .= " AND webfinance_invoices.id_client=".$_GET['id_client'];
}
if (isset($_GET['mois']) AND $_GET['mois'] != "") {
  $where_clause .= " AND date_format(date_facture, '%Y%m')='".$_GET['mois']."'";
}
if (isset($_GET['type']) AND $_GET['type']!="") {
  switch ($_GET['type']) {
    case "unpaid" : $where_clause .= " AND is_paye=0"; break;
    case "paid" : $where_clause .= " AND is_paye=1"; break;
  }
}

if(!isset($_GET['sort']))
  $_GET['sort']="date";

$w_clause="";
 if(count($roles)==1 AND in_array($client_role,$roles) ) {
   $is_client=true;
   $w_clause=" AND webfinance_clients.id_user=$user->id_user ";
   $where_clause .= $w_clause;
  }

$GLOBALS['_SERVER']['QUERY_STRING'] = preg_replace("/sort=\w+\\&*+/", "", $GLOBALS['_SERVER']['QUERY_STRING']);

switch ($_GET['sort']) {
  case "num" : $order_clause = "webfinance_invoices.num_facture DESC"; break;
  case "client" : $order_clause = "webfinance_clients.nom"; break;
  case "montant_ttc" :
  case "montant_ht" : $order_clause = "webfinance_invoices.id_client"; break;
  case "date" :
  default : $order_clause = "webfinance_invoices.date_facture DESC";
}


?>

<table width="100%" cellspacing="5" cellpadding="0">
<tr valign="top"><td width="60%">

<table width="100%" cellspacing=0 cellpadding=3 style="border: solid 1px black;">
<tr align="center" class="row_header">
  <td></td>
  <td><a href="?sort=date&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Invoice date') ?></a></td>
  <td colspan="2"><a href="?sort=num&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Invoice #') ?></a></td>
  <td><a href="?sort=client&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('Client') ?></a></td>
  <td><a href="?sort=montant_ht&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('HT') ?></a></td>
  <td><a href="?sort=montant_ttc&<?= $GLOBALS['_SERVER']['QUERY_STRING'] ?>"><?= _('TTC') ?></a></td>
  <td></td>
</tr>
<?php
$total_ca_ht = 0;

$q="SELECT webfinance_invoices.id_facture ".
  "FROM webfinance_users, webfinance_clients, webfinance_invoices ".
  "WHERE ".
  "( ".
  "webfinance_clients.id_user = webfinance_users.id_user ".
  "AND webfinance_clients.id_client = webfinance_invoices.id_client ".
  " ) ".
  "AND webfinance_invoices.type_doc='facture' ".
  "AND webfinance_invoices.num_facture!='' ".
  "AND webfinance_invoices.date_facture<=now() ".
  "AND $where_clause ".
  "ORDER BY $order_clause ";

$result = mysql_query($q) or wf_mysqldie();

$num_factures = array();
$count=0;
while (list($id_facture) = mysql_fetch_array($result)) {
  $count++;
  $fa = $Facture->getInfos($id_facture);
  $class = ($count%2 == 0)?"even":"odd";
  $icon = $fa->is_paye?"paid":"not_paid";
  $total_ca_ht += $fa->total_ht;
  if(isset($mois[$fa->mois_facture]))
    $mois[$fa->mois_facture]++;
  else
    $mois[$fa->mois_facture]=1;

   $description = "";
   $result2 = mysql_query("SELECT description FROM webfinance_invoice_rows WHERE id_facture=".$fa->id_facture) or wf_mysqldie();
   while (list($desc) = mysql_fetch_array($result2)) {
     $desc = preg_replace("/\r\n/", " ", $desc);
     $desc = preg_replace("/\"/", "", $desc);
     $desc = preg_replace("/\'/", "", $desc);
     $description .= $desc."<br/>";
   }
   if(!($fa->is_paye)){
     $description .= "<i>"._('Veuillez cliquer sur le bouton rouge pour payer la facture par CB.')."</i>";
   }
   mysql_free_result($result2);

?>
<tr onMouseOut="UnTip();" onmouseover="Tip('<?=$description?>');" class="row_<?=$class?>">
  <td><?=$count?></td>
  <td><?=$fa->nice_date_facture?></td>
  <td>
<?
   if(empty($fa->is_paye))
     printf('<a href="../payment/paybox/?id_invoice=%d&id_client=%d"><img src="/imgs/icons/%s.gif" alt="%s" /></a>',$id_facture,$fa->id_client,$icon,$icon);
   else
     printf('<img src="/imgs/icons/%s.gif" alt="%s" />',$icon,$icon);
?>
  </td>
  <td>FA<?=$fa->num_facture?></td>
  <td>
<?
   if(!$is_client)
     printf("<a href='../prospection/fiche_prospect.php?id=%d '>%s</a>",$fa->id_client,$fa->nom_client);
   else
     echo $fa->nom_client;
?>
  </td>
  <td align="right"><?=$fa->nice_total_ht?> &euro;</td>
  <td align="right"><?=$fa->nice_total_ttc?> &euro;</td>
  <td>
     <a href="../prospection/gen_facture.php?id=<?=$fa->id_facture?>"><img src="/imgs/icons/pdf.gif" alt="Chopper" /></a>
<?
 if(!$is_client){
   echo "<a href='../prospection/edit_facture.php?id_facture=$fa->id_facture'><img src='/imgs/icons/edit.gif' alt='Modifier' /></a>";
 }

   $num_factures[$id_facture]=$fa->num_facture;

   if(!($fa->is_paye)){
     echo "";
   }

?>
  </td>
</tr>
<?
}
?>
</table>
<br/>

</td><td>

<form  method="get">
<table cellspacing="0" cellpadding="3" style="border: solid 1px black;" width="100%">
<tr align="center" class="row_header">
  <td colspan="2"><?= _('Filter')?></td>
</tr>
<tr>
  <td>Total CA</td>
  <td>
    <b><?= number_format($total_ca_ht, 2, ',', ' ') ?> &euro; HT</b> /
    <b><?= number_format(1.196*$total_ca_ht, 2, ',', ' ') ?> &euro; TTC</b>
  </td>
</tr>
<tr>
  <td>Client</td>
  <td>
    <select style="width: 200px;" name="id_client" onchange="this.form.submit();" >
     <option value=""><?= _('All')?></option>
<?php

$q="SELECT DISTINCT webfinance_clients.id_client, webfinance_clients.nom ".
  "FROM webfinance_clients, webfinance_invoices ".
  "WHERE ".
  "webfinance_clients.id_client = webfinance_invoices.id_client ".
  "AND webfinance_invoices.type_doc='facture' ".
  " $w_clause ".
  "ORDER BY webfinance_clients.nom ASC";

 $result = mysql_query($q) or wf_mysqldie();

     while (list($id, $nom) = mysql_fetch_array($result)) {
	  printf('<option value="%d"%s>%s</option>'."\n", $id, ($id==$_GET['id_client'])?" selected":"", $nom);
	}
      ?>
    </select>
  </td>
</tr>
<tr>
  <td colspan="2">
  <table width="100%">
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
    <td><?= _('Month') ?></td>
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
  </td>
</tr>
</table>
</form>
<br/>

<table border="0" cellspacing="0" cellpadding="3" style="border: solid 1px black;" width="100%">
<tr align="center">
  <td><?= _('Events')?></td>
</tr>
<tr>
  <td>
      <div style="overflow: auto; height: 250px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="5">
    <?php
      $w_clause="";
      	if(count($num_factures)>0){
	  $w_clause .= " AND ( ";
	  $tmp=array();
	  foreach($num_factures as $id_facture=>$num_facture){
	    $tmp[] .=" log RLIKE '#$num_facture' ";
	    #$tmp[] .= " log RLIKE 'fa:$id_facture' ";
	  }
          $w_clause .= implode(" OR ", $tmp);
	  $w_clause .=") ";
         }
	$q="SELECT id_userlog,log,date,id_user,date_format(date,'%d/%m/%Y %k:%i') as nice_date ".
	  "FROM webfinance_userlog ".
	  "WHERE log RLIKE 'fa:' $w_clause ".
	  "ORDER BY date DESC";
	$result = mysql_query($q) or wf_mysqldie();

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

</tr>
</table>

</td></tr>
</table>

<?php

$Revision = '$Revision: 531 $';
include("../bottom.php");

?>
