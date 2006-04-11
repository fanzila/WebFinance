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
} elseif (($GLOBALS['HTTP_SERVER_VARS']['REDIRECT_STATUS'] == "404") && (preg_match("!/imgs/boutons/([^\.]+).png$!", $GLOBALS['HTTP_SERVER_VARS']['REDIRECT_URL'], $matches))) {
  // Génération des images dynamiquement.
  header("Location: /cgi-bin/button.cgi?data=".urlencode($matches[1]));
  die();
} else {
  require("top.php");
  ?>
<h1>404 : Page innexistante</h1>

Lien suivi depuis : <?= $GLOBALS['_SERVER']['HTTP_REFERER'] ?><br/>
URI demandée : <?= $GLOBALS['_SERVER']['REDIRECT_URL'] ?>
  <?php
  require("bottom.php");
}

?>
