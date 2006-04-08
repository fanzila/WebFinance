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

require("../inc/main.php");
require("../top.php");
require("nav.php");

if (preg_match("!\.!", $_POST['filtre'])) { die("Wrong filter"); } // file traversal

print "<h1>Import de données bancaires</h1>";

if (!file_exists($_FILES['csv']['tmp_name'])) {
  die("Pas de fichier reçu");
}

if (!file_exists($_POST['format'])) {
  die("Import impossible !! Veuillez choisir le type de fichier dans le <a href=\"import.php\">formulaire d'import</a>");
}

// Do import

extract($_FILES['csv']);

print "Fichier envoyé : $name<br/>";
print "Type mime : $type<br/><br/>";

print "<h2>Analyse des lignes :</h2>";

require($_POST['format']);

?>
