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

//$Id$

$elements = array( _('Customers') => array( 'url' => './?q=1', 'roles' => 'manager,accounting,employee' ),
                   _('Targets') => array( 'url' => './?q=2', 'roles' => 'manager,accounting,employee' ),
		   _('Add company') => array( 'url' => 'javascript:confirmAddCompany(\'Do you really want to add a new company?\');', 'roles' => 'manager,accounting' ),
                  _('Billing') => array( 'url' => 'facturation.php', 'roles' => 'manager,accounting' )
                 );
global $User;
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
