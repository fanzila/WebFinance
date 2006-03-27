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

include("inc/backoffice.php");
$title = "Session";
include("top.php");

if ($_GET['delete']) {
  unset($_SESSION[$_GET['delete']]);
  header("Location: session.php");
}

print "<pre>";

?>
<form action="session.php" method="post">'

<?php
foreach ($_SESSION as $n=>$v) {
  print "<a href=\"?delete=$n\"><input type=\"text\" name=\"$n\" value=\"$n\"/></a> : ";
  print_r($_SESSION[$n]);
  print "\n";
}
print "</pre>";

include("bottom.php");

?>
