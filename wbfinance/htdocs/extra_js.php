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
<?php

// $Id$

foreach ($extra_js as $js) {
  printf('<script type="text/javascript" src="%s"></script>'."\n", $js);
}

?>
