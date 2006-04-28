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
$roles = 'any';
$title = _('Graphics');
array_push($extra_js, '/js/onglets.js');
require("../top.php");

$tab = new TabStrip();
global $User;
$User->getInfos();

print "<form>";

if ($User->isAuthorized('accounting,manager')) {
  $cashflow = <<<EOF
<img alt="cashflow" src="cashflow.php?width=850&height=400" width="850" height="400" />
EOF;
  $tab->addTab(_('Cashflow'), $cashflow);
}

if ($User->isAuthorized('accounting,eployee,manager')) {
  // FIXME : ici un formulaire pour utiliser le paramètre limit_clients (pour
  // "détasser" les petits clients, ne pas faire apparaître les gros). 
  $clientincome = <<<EOF
<img id="clients_income" alt="clients_income" src="clients_income.php?width=850&height=400" width="850" height="400" />
EOF;
  $tab->addTab(_('Client income'), $clientincome);
}

$tab->realise();

print "</form>";
$Revision = '$Revision$';
require("../bottom.php");
?>
