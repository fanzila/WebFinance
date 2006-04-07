<?php 
// 
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$
//
// Wrapper : should contain interface. Importing is done in import_*.php

if (preg_match("!\.!", $_POST['filtre'])) { die("Wrong filter"); } // file traversal

if (file_exists($_FILES['csv']['tmp_name'])) {
  // Do import

  extract($_FILES['csv']);

  require($_POST['format']);

  die();
  header("Location: /tresorerie/");
}


require("../inc/main.php");
require("../top.php");
require("nav.php");

?>
<form id="main_forma" method="post" enctype="multipart/form-data">
CSV : <input type="file" name="csv" /><br/>
Format : <select name="format">
<option value="about:blank">-- Choisissez --</option><?php
foreach (glob("import_*.php") as $filtre) {
  preg_match("/import_(.*).php$/", $filtre, $matches);

  printf('<option value="%s">%s</option>', $filtre, $matches[1]);
}
?>
</select>
<input type="submit" value="Importer">
</form>

<?php
$Revision = '$Revision$';
require("../bottom.php");
?>
