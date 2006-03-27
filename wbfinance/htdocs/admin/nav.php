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
<div class="sousnav">
<?php

//$Id$

$elements = array('Users' => 'index.php',
                  'Événements' => 'events.php',
                  'Société' => 'societe.php',
                 );

foreach ($elements as $elname=>$url) {
  $on = '/imgs/boutons/'.urlencode(base64_encode($elname.":on")).'.png';
  array_push($_SESSION['preload_images'], $on);
  $off = '/imgs/boutons/'.urlencode(base64_encode($elname.":off")).'.png';
  print "<a class=\"bouton\" href=\"$url\"><img onMouseOver=\"this.src='$on';\" onMouseOut=\"this.src='$off';\" src=\"$off\" border=0 /></a> ";
}

?>
</div>
