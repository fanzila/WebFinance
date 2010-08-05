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
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php

// $Id: save_societe.php 531 2007-06-13 12:32:31Z thierry $

require("../inc/main.php");
must_login();

// Enregistrement du logo if provided
if (file_exists($_FILES['logo']['tmp_name'])) {
  mysql_query("DELETE FROM webfinance_pref WHERE type_pref='logo'");
  $fp = fopen($_FILES['logo']['tmp_name'], "r");
  while (!feof($fp)) {
    $read = fread($fp, 4096);
    $data .= $read;
  }
  $data = base64_encode($data);
  mysql_query("INSERT INTO webfinance_pref (owner,value,type_pref) values(-1, '$data', 'logo')") or wf_mysqldie("Admin::saving logo");
}

// Enregistrement adresse et raison sociale
mysql_query("DELETE FROM webfinance_pref WHERE type_pref='societe'");

$data = new stdClass();
$data->raison_sociale = $_POST['raison_sociale'];
$data->tva_intracommunautaire = $_POST['tva_intracommunautaire'];
$data->siren = $_POST['siren'];
$data->addr1 = $_POST['addr1'];
$data->addr2 = $_POST['addr2'];
$data->addr3 = $_POST['addr3'];
$data->wf_url = $_POST['wf_url'];
$data->email = $_POST['email'];
$data->cp = $_POST['cp'];
$data->ville = stripslashes($_POST['ville']);
$data->date_creation = $_POST['date_creation'];
$data->invoice_top_line1 = stripslashes($_POST['invoice_top_line1']);
$data->invoice_top_line2 = stripslashes($_POST['invoice_top_line2']);

$data = base64_encode(serialize($data));
mysql_query("INSERT INTO webfinance_pref (type_pref, value) VALUES('societe', '$data');") or wf_mysqldie();

// Enregistrement compte(s) banquaire(s)
// mysql_query("DELETE FROM webfinance_pref WHERE type_pref='rib'");
foreach ($_POST as $n=>$v) {
  if (preg_match("/^banque_([0-9]+)$/", $n, $matches)) {
    $num = $matches[1];

    $rib = new stdClass();
    $rib->banque = $_POST['banque_'.$num];
    $rib->domiciliation = $_POST['domiciliation_'.$num];
    $rib->currency = $_POST['currency_'.$num];
    $rib->exchange = $_POST['exchange_'.$num];
    $rib->code_banque = $_POST['code_banque_'.$num];
    $rib->code_guichet = $_POST['code_guichet_'.$num];
    $rib->compte = $_POST['compte_'.$num];
    $rib->clef = $_POST['clef_'.$num];
    $rib->iban = $_POST['iban_'.$num];
    $rib->swift = $_POST['swift_'.$num];

    if(empty($rib->exchange))
      $rib->exchange=1;

    if ($rib->compte != "") {
      $rib = base64_encode(serialize($rib));
      mysql_query("UPDATE webfinance_pref SET value='$rib' WHERE type_pref='rib' AND id_pref=$num") or wf_mysqldie();
    } else {
      mysql_query("DELETE FROM webfinance_pref WHERE type_pref='rib' AND id_pref=$num") or wf_mysqldie();
    }
  }

}

if ($_POST['banque_new'] != "") {
  $rib = new stdClass();
  $rib->banque = $_POST['banque_new'];
  $rib->domiciliation = $_POST['domiciliation_new'];
  $rib->currency = $_POST['currency_new'];
  $rib->exchange = $_POST['exchange_new'];
  $rib->code_banque = $_POST['code_banque_new'];
  $rib->code_guichet = $_POST['code_guichet_new'];
  $rib->compte = $_POST['compte_new'];
  $rib->clef = $_POST['clef_new'];
  $rib->iban = $_POST['iban_new'];
  $rib->swift = $_POST['swift_new'];

  if(empty($rib->exchange))
    $rib->exchange=1;

  $rib = base64_encode(serialize($rib));
  mysql_query("INSERT INTO webfinance_pref (type_pref, value) VALUES('rib', '$rib')") or wf_mysqldie();
}

header("Location: societe.php");
exit;

?>
