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

include("inc/main.php");

if (isset($_SERVER['REDIRECT_STATUS']) and $_SERVER['REDIRECT_STATUS'] == "404"
	and preg_match("/.html$/", $_SERVER['REDIRECT_URL'])) {
  $new_loc = preg_replace("/(\w+)\.html/", "index.php?file=\\1.html",
						  $_SERVER['REDIRECT_URL']);
  header("Location: $new_loc");
  exit;
}

if (isset($_SERVER['REDIRECT_STATUS']) and $_SERVER['REDIRECT_STATUS'] == "404"
	and preg_match("!/imgs/boutons/([^\.]+)_([^\.]+)_([^\.]+).png$!",
				   $_SERVER['REDIRECT_URL'], $matches)) {
	// Génération des images dynamiquement.
	header("Location: /imgs/boutons/generate.php?text=".$matches[1].'&style='.$matches[2].'&theme='.$matches[3]);
  exit;
}

require("top.php");
  ?>
<h1>404 : Page inexistante</h1>

Lien suivi depuis : <?= $_SERVER['HTTP_REFERER'] ?><br/>
URI demandée : <?= $_SERVER['REDIRECT_URL'] ?>
<?php
  $Revision = '$Revision: 531 $';
  require("bottom.php");

?>
