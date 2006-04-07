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

include("inc/main.php");
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
