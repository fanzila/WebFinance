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

include("../inc/main.php");
$title = _("Client");
$roles=array("manager","accounting","employee","client");
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
    case "paid" : $where_clause .= " AND is_paye=2"; break;
  }
}

$w_clause="";
if( !$user->isAdmin AND in_array($client_role,$roles) ){
  $is_client=true;
  $w_clause= " AND ( webfinance_clients.email LIKE '$user->email' OR webfinance_personne.email LIKE '$user->email')";
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

<table border="0" cellspacing="5" cellpadding="0">
<tr valign="top"><td>

<table border=0 cellspacing=0 cellpadding=3 style="border: solid 1px black;">
<tr align=center class=row_header>
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
  "FROM webfinance_invoices, webfinance_clients ".
  "LEFT JOIN webfinance_personne ON webfinance_clients.id_client = webfinance_personne.client ".
  "WHERE webfinance_invoices.id_client = webfinance_clients.id_client ".
  "AND type_doc='facture' ".
  "AND num_facture!='' ".
  "AND date_facture<=now() ".
  "AND ".$where_clause." ".
  "ORDER BY $order_clause ";

$result = mysql_query($q) or wf_mysqldie();

$mois = array();
$num_factures = array();
while (list($id_facture) = mysql_fetch_array($result)) {
  $count++;
  $fa = $Facture->getInfos($id_facture);
  $class = ($count%2 == 0)?"even":"odd";
  $icon = $fa->is_paye?"paid":"not_paid";
  $total_ca_ht += $fa->total_ht;
  $mois[$fa->mois_facture]++;

   $description = "";
   $result2 = mysql_query("SELECT description FROM webfinance_invoice_rows WHERE id_facture=".$fa->id_facture);
   while (list($desc) = mysql_fetch_array($result2)) {
     $desc = preg_replace("/\r\n/", " ", $desc);
     $desc = preg_replace("/\"/", "", $desc);
     $desc = preg_replace("/\'/", "", $desc);
     $description .= $desc."<br/>";
   }
   mysql_free_result($result2);

?>
<tr onmouseover="return escape('<?=$description?>');" class="row_$class">
  <td><?=$count?></td>
  <td><?=$fa->nice_date_facture?></td>
  <td><img src="/imgs/icons/<?=$icon?>.gif" alt="<?=$icon?>" /></td>
  <td>FA<?=$fa->num_facture?></td>
  <td><a href="../prospection/fiche_prospect.php?id=<?=$fa->id_client?>"><?=$fa->nom_client?></a></td>
  <td><?=$fa->nice_total_ht?>&euro;</td>
  <td><?=$fa->nice_total_ttc?>&euro;</td>
  <td>
     <a href="../prospection/gen_facture.php?id=<?=$fa->id_facture?>"><img src="/imgs/icons/pdf.gif" alt="Chopper" /></a>
<?
 if(!$is_client){
   echo "<a href='../prospection/edit_facture.php?id_facture=$fa->id_facture'><img src='/imgs/icons/edit.gif' alt='Modifier' /></a>";
 }

   $num_factures[$id_facture]=$fa->num_facture;

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
<table border="0" cellspacing="0" cellpadding="3" style="border: solid 1px black;" width="350">
<tr align="center" class="row_header">
  <td colspan="2"><?= _('Simple Filter')?></td>
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
     <option value=""><?= _('All')?></option>
<?php

 $q="SELECT webfinance_clients.id_client, webfinance_clients.nom ".
     "FROM webfinance_invoices, webfinance_clients ".
     "LEFT JOIN webfinance_personne ON webfinance_clients.id_client = webfinance_personne.client ".
     "WHERE webfinance_invoices.id_client = webfinance_clients.id_client ".$w_clause;

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

<table border="0" cellspacing="0" cellpadding="3" style="border: solid 1px black;" width="350">
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

include("../bottom.php");

?>
