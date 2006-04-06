<?php
//
// This file is part of Â« Backoffice NBI Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<?php

include_once("../inc/backoffice.php");
include("../top.php");
include("nav.php");

?>

<table style="border: solid 1px black;" width="100%" border="0" cellspacing="0" cellpadding="5">
<tr class="row_header">
 <td>Heure</td>
 <td>&Eacute;v&eacute;nements</Td>
 <td>Qui</td>
</tr>
<?php
$result = mysql_query("SELECT *,date_format(date,'%d/%m/%Y %k:%i') as nice_date FROM webcash_userlog ORDER BY date DESC");
$count=1;
while ($log = mysql_fetch_object($result)) {
  $class = ($count%2)==0?"odd":"even";
  $result2 = mysql_query("SELECT login FROM webcash_users WHERE id_user=".$log->id_user);
  list($login) = mysql_fetch_array($result2);
  mysql_free_result($result2);

  $message = parselogline($log->log);

  print <<<EOF
<tr class="row_$class">
  <td>$log->nice_date</td>
  <td>$message</td>
  <td>$login</td>
</tr>
EOF;
  $count++;
}
mysql_free_result($result);
?>
</table>

<?php
include("../bottom.php");
?>
