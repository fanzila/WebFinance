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

include("inc/main.php");

if (($GLOBALS['HTTP_SERVER_VARS']['REDIRECT_STATUS'] == "404") && (preg_match("/.html$/", $GLOBALS['HTTP_SERVER_VARS']['REDIRECT_URL']))) {
  $new_loc = preg_replace("/(\w+)\.html/", "index.php?file=\\1.html", $GLOBALS['HTTP_SERVER_VARS']['REDIRECT_URL']);
  header("Location: $new_loc");
} elseif (($GLOBALS['HTTP_SERVER_VARS']['REDIRECT_STATUS'] == "404") && (preg_match("!/imgs/boutons/([^\.]+)_([^\.]+)_([^\.]+).png$!", $GLOBALS['HTTP_SERVER_VARS']['REDIRECT_URL'], $matches))) {
  // Génération des images dynamiquement.
  header("Location: /imgs/boutons/generate.php?text=".$matches[1].'&style='.$matches[2].'&theme='.$matches[3]);
  exit;
} else {
  require("top.php");
  ?>
<h1>404 : Page inexistante</h1>

Lien suivi depuis : <?= $GLOBALS['_SERVER']['HTTP_REFERER'] ?><br/>
URI demandée : <?= $GLOBALS['_SERVER']['REDIRECT_URL'] ?>
<?php
  $Revision = '$Revision: 531 $';
  require("bottom.php");
}

?>
