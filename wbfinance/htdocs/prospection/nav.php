<?php 
// 
// This file is part of « Backoffice NBI »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<div>
<?php

//$Id$

$elements = array( 'Clients' => 'index.php?q=1',
                   'Prospects' => 'index.php?q=2',
                  'Ajout Entreprise' => 'fiche_prospect.php?action=_new',
                  'Facturation' => 'facturation.php',
                 );

foreach ($elements as $elname=>$url) {
  $on = '/imgs/boutons/'.urlencode(base64_encode($elname.":on")).'.png';
  array_push($_SESSION['preload_images'], $on);
  $off = '/imgs/boutons/'.urlencode(base64_encode($elname.":off")).'.png';
  printf( '<a class="bouton" href="%s"><img onMouseOver="this.src=\'%s\';" onMouseOut="this.src=\'%s\';" src="%s" border=0 /></a>',
           $url, $on, $off, $off);
}

?>
</div>
