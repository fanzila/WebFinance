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
?>
<div class="sousnav">
<?php

//$Id: nav.php 551 2007-08-02 05:16:27Z gassla $

$elements = array(
                  _('Transactions') => array('url'=>'./', 'roles'=>'manager,accounting'),
                  _('Graphics') => array('url'=>'graphs.php', 'roles'=>'manager,accounting'),
                  _('Graphics All') => array('url'=>'graphs_all_history.php', 'roles'=>'manager,accounting'),
                  _('Categories') => array('url' => 'categories.php', 'roles' => 'manager'),
                  _('Expenses') => array('url'=>'expenses.php', 'roles'=>'manager,accounting,employee'),
                  _('Import') => array('url'=>'import.php', 'roles'=>'manager'),
                  _('Paybox') => array('url'=>'paybox.php', 'roles'=>'manager,employee'),
                 );


foreach ($elements as $elname=>$data) {
  if ($User->isAuthorized($data['roles'])) {
    $on = '/imgs/boutons/'.urlencode($elname."_on_".$User->prefs->theme).'.png';
    array_push($_SESSION['preload_images'], $on);
    $off = '/imgs/boutons/'.urlencode($elname."_off_".$User->prefs->theme).'.png';
    printf( '<a class="bouton" href="%s"><img onMouseOver="this.src=\'%s\';" onMouseOut="this.src=\'%s\';" src="%s" border=0 /></a>',
             $data['url'], $on, $off, $off);
  }
}

?>
</div>
