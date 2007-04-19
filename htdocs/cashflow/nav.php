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

//$Id$

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
    $off = '/imgs/boutons/'.urlencode("$elname_off_".$User->prefs->theme).'.png';
    printf( '<a class="bouton" href="%s"><img onMouseOver="this.src=\'%s\';" onMouseOut="this.src=\'%s\';" src="%s" border=0 /></a>',
             $data['url'], $on, $off, $off);
  }
}

?>
</div>
