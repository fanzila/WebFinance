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

include("../inc/backoffice.php");

if ($_GET['action'] == "delete") {
  mysql_query("DELETE FROM client WHERE id_client=".$_GET['id']) or die(mysql_error());
  $_SESSION['message'] = "Le client/prospect, tous ses contacts factures et devis ont été supprimés";
  header("Location: /prospection/");
  die();
}

extract($_POST);
$q = sprintf("UPDATE client SET nom='%s',addr1='%s',addr2='%s',addr3='%s',cp='%s',ville='%s',pays='%s',
                                tel='%s',fax='%s',email='%s',vat_number='%s',siren='%s', state='%s'
              WHERE id_client=%d",

             $nom, $addr1, $addr2, $addr3, $cp, $ville, $pays, $tel, $fax, $email, $vat_number, $siren, $state,
             $id_client );

mysql_query($q) or die(mysql_error());

header("Location: fiche_prospect.php?id=$id_client");

?>
