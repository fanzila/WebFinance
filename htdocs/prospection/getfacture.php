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
?>
<?php
// $Id$

include("../inc/dbconnect.php");

ini_set('session.gc_maxlifetime',3600);
session_cache_limiter("must-revalidate");
session_start();

if( !isset($_SESSION['id_user']) || $_SESSION['id_user'] < 1 ) {
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
