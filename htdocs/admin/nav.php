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

$elements = array(_('Users') => array('url' => 'index.php', 'roles'=>'admin,manager'),
                  _('Events') => array('url' => 'events.php', 'roles'=>'admin,manager'),
                  _('My company') => array('url' => 'societe.php', 'roles'=>'manager,employee')
                 );

foreach ($elements as $elname=>$data) {
  if ($User->isAuthorized($data['roles'])) {
    $on = '/imgs/boutons/'.urlencode(base64_encode($elname.":on:".$User->prefs->theme)).'.png';
    array_push($_SESSION['preload_images'], $on);
    $off = '/imgs/boutons/'.urlencode(base64_encode($elname.":off:".$User->prefs->theme)).'.png';
    printf( '<a class="bouton" href="%s"><img onMouseOver="this.src=\'%s\';" onMouseOut="this.src=\'%s\';" src="%s" border=0 /></a>',
             $data['url'], $on, $off, $off);
  }
}

?>
</div>
