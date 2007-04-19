<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//

require_once("inc/main.php");
?>
<div class="nav">
<?php

//$Id$

$elements = array(_('Home') => array( 'url' => '/', 'roles' => 'any' ),
                  _('My invoices') => array( 'url' => '/client/', 'roles' => 'client' ),
                  _('Companies') => array( 'url' => '/prospection/?q=1', 'roles' => 'manager,employee,accounting' ),
                  _('Cashflow') => array( 'url' => '/cashflow/', 'roles' => 'manager,accounting' ),
                  _('My account') => array( 'url' => '/moncompte/', 'roles' => 'any' ),
                  _('Administration') => array( 'url' => '/admin/', 'roles' => 'manager,admin' ),
                  _('Logout') => array( 'url' => '/logout.php', 'roles' => 'any' ),
                 );
$User= new User();
$User->getInfos();
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
