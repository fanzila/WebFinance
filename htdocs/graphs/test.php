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
<?php

// $Id$

require("../inc/main.php");
require("../top.php");

$width = 400;
$height= 300;

?>

<object width="<?= $width ?>" height="<?= $height ?>" id="graphca" title="Graphique de CA" type="image/svg+xml" data="svg_ca.php?width=<?= $width ?>&height=<?= $height ?>">
<img alt="Utilisez Mozilla !" id="graphcafallback" src="/imgs/no_svg.gif" align="right"/>
</object> 

<?php
$Revision = '$Revision$';
require("../bottom.php");
?>
