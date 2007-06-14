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
// $Id$

require("../inc/main.php");
array_push($extra_js, '/js/onglets.js');
$title = _('Test TabStrip');
require("../top.php");
require("../inc/TabStrip.php");

$tab = new TabStrip(3);
$tab->addTab("Onglet 1", "Contenu onglet 1", "test");
$tab->addTab("Onglet 2", "Contenu onglet 2", "test");
$tab->addTab("A very very long title", "Shal not break the layout", "test");
$tab->realise();

$Revision = '$Revision$';
require("../bottom.php");
?>
