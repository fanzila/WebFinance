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

session_start();
header("Content-type: text/javascript");

$image_tab = "var preloadedImgs = new Array();\n";
$count = 0;
foreach ($_SESSION['preload_images'] as $preload) {
  $image_tab .= "preloadedImgs[$count] = new Image; preloadedImgs[$count].src = '$preload';\n";
  $count++;
}
print $image_tab;

?>
