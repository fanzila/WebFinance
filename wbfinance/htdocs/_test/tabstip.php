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
