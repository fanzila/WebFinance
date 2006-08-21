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

include("../inc/dbconnect.php");

ini_set('session.gc_maxlifetime',3600);
session_cache_limiter("must-revalidate");
session_start();
if (! ($_SESSION['id_user'] > 0)) {
  header("Location: /login.php");
  die();
}

$result = mysql_query("SELECT facture_file FROM webfinance_invoices WHERE id_facture=".$_GET['id']);
list($file) = mysql_fetch_array($result);

if (file_exists($file)) {
  header("Content-Type: application/pdf");
  header("Content-Disposition: attachment; filename=".basename($file));
  header("Content-Length: ".filesize($file));

  $fp = fopen($file, "r");
  while (!feof($fp)) {
    $buff = fread($fp, 4096);
    print $buff;
  }
  fclose($fp);
} else {
  die("Fichier incorrect");
}

?>
