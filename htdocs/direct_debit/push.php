<?php
/*
 Copyright (C) 2004-2011 NBI SARL, ISVTEC SARL

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
include("../inc/main.php");

$roles = 'manager,employee';
include("nav.php");

$Invoice = new Facture();

if(!isset($_GET['id']) or !is_numeric($_GET['id']) or
  !$Invoice->exists($_GET['id'])) {
  echo "Invalid invoice id";
  exit(1);
}

$facture = $Invoice->getInfos($_GET['id']);

if($facture->is_paye) {
  echo "Invoice has already been paid";
  exit(1);
}

// Plan the invoice to be debited
mysql_query(
  "INSERT INTO direct_debit_row ".
  "SET invoice_id = $_GET[id], ".
  "    state='todo'")
      or die(mysql_error());

// Flag invoice as paid
$Invoice->setPaid($_GET['id']);

header("Location: ../prospection/edit_facture.php?id_facture=$_GET[id]");
exit;

?>
