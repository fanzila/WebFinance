<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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
?>
<script type="text/javascript">
function confirmAddCompany(txt) {
  if (confirm(txt)) {
    window.location = 'fiche_prospect.php?action=_new';
  }
}
</script>

<div>
<?php

//$Id: nav.php 532 2007-06-14 10:39:19Z thierry $

$elements = array( _('Customers') => array( 'url' => './?sort=nom&q=0', 'roles' => 'manager,accounting,employee' ),
                   _('Reporting') => array( 'url' => 'reporting.php', 'roles' => 'manager,accounting,employee' ),
		   _('Add company') => array( 'url' => 'javascript:confirmAddCompany(\'Do you really want to add a new company?\');', 'roles' => 'manager,accounting' ),
                  _('Billing') => array( 'url' => 'facturation.php', 'roles' => 'manager,accounting' )
                 );
global $User;
foreach ($elements as $elname=>$data) {
  if ($User->isAuthorized($data['roles'])) {
    $on = '/imgs/boutons/'.str_replace('+', '%20', urlencode($elname."_on_".$User->prefs->theme)).'.png';
    array_push($_SESSION['preload_images'], $on);
	$off = '/imgs/boutons/'.str_replace('+', '%20', urlencode($elname."_off_".$User->prefs->theme)).'.png';
    printf( '<a class="bouton" href="%s"><img onMouseOver="this.src=\'%s\';" onMouseOut="this.src=\'%s\';" src="%s" border=0 /></a>',
             $data['url'], $on, $off, $off);
  }
}

?>
</div>
