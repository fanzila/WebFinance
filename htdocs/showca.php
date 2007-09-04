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
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id: showca.php 531 2007-06-13 12:32:31Z thierry $
//
// Affiche le graph de CA en grand et un formulaire pour choisir ce que l'on
// affiche
//

include("inc/main.php");
$roles='manager,accounting,employee';
include("top.php");

extract($_GET);
if (!isset($width)) { $width = 800; }
if (!isset($height)) { $height = 300; }
if (!isset($nb_months)) { $nb_months = 24; }

?>

<h1><?= _('Activity graphics') ?></h1>
<br/>
Afficher :
<form onchange="this.submit();" action="showca.php" method="get">
<select name="nb_months">
<?php

// On récupère la date de création de la société et on calcule son age en mois.
$result = mysql_query("SELECT value FROM webfinance_pref WHERE owner=-1 AND type_pref='societe'");
list($data) = mysql_fetch_array($result);
mysql_free_result();
$data = unserialize(base64_decode($data));
preg_match("!(..)/(..)/(....)!", $data->date_creation, $matches);
$ts_start_company = mktime(0, 0, 0, $matches[2], $matches[1]-1, $matches[3]);
$max_months = round((time() - $ts_start_company) / (31*24*3600))+1;

$choices = array(
    "6 mois" => 6,
    "Un an" => 12,
    "18 mois" => 18,
    "Deux ans" => 24,
    "Trois ans" => 36,
    "Quatre ans" => 48,
    "Depuis la création" => $max_months
);

foreach ($choices as $n=>$v) {
  printf('<option value="%d"%s>%s</option>'."\n", $v, ($v==$nb_months)?" selected":"", $n);
}
?>
</select>
largeur image : <input class="bordered" style="width: 50px; text-align: center;" type="text" name="width" value="<?= $width ?>" />
hauteur image : <input class="bordered" style="width: 50px; text-align: center;" type="text" name="height" value="<?= $height ?>" />
<input type="checkbox" name="grid" <?= $grid?"checked":"" ?> /> Dessiner la grille
</form><br/>

<h2><?= _('Total Income per month') // FIXME : comment dit-on hors taxes en anglais ? ?></h2>
<img height="<?= $height ?>" width="<?= $width ?>" src="/graphs/ca_mensuel.php?width=<?= $width ?>&height=<?= $height ?>&nb_months=<?= $nb_months ?>&grid=<?= $grid?1:0 ?>" alt="Graphique" />
<h2><?= _('Total income per client') // FIXME : comment dit-on hors taxes en anglais ? ?></h2>
<img height="<?= $height ?>" width="<?= $width ?>" src="/graphs/clients_income.php?width=<?= $width ?>&height=<?= $height ?>&nb_months=<?= $nb_months ?>&grid=<?= $grid?1:0 ?>" alt="Graphique" />
<h2><?= _('Debt per client') // FIXME : comment dit-on hors taxes en anglais ? ?></h2>
<img height="<?= $height ?>" width="<?= $width ?>" src="/graphs/clients_debpt.php?width=<?= $width ?>&height=<?= $height ?>&nb_months=<?= $nb_months ?>&grid=<?= $grid?1:0 ?>" alt="Graphique" />
