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
<div>
<?php

//$Id$

$elements = array( _('Customers') => array( 'url' => 'index.php?q=1', 'roles' => 'manager,accounting,employee' ),
                   _('Targets') => array( 'url' => 'index.php?q=2', 'roles' => 'manager,accounting,employee' ),
                  _('Add company') => array( 'url' => 'fiche_prospect.php?action=_new', 'roles' => 'manager' ),
                  _('Billing') => array( 'url' => 'facturation.php', 'roles' => 'manager,accounting' )
                 );
$User = new User();
foreach ($elements as $elname=>$data) {
  if ($User->isAuthorized($_SESSION['id_user'], $data['roles'])) {
    $on = '/imgs/boutons/'.urlencode(base64_encode($elname.":on")).'.png';
    array_push($_SESSION['preload_images'], $on);
    $off = '/imgs/boutons/'.urlencode(base64_encode($elname.":off")).'.png';
    printf( '<a class="bouton" href="%s"><img onMouseOver="this.src=\'%s\';" onMouseOut="this.src=\'%s\';" src="%s" border=0 /></a>',
             $data['url'], $on, $off, $off);
  }
}

?>
</div>
