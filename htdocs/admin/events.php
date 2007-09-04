<?php
/*
   This file is part of Webfinance.

    Webfinance is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Webfinance is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Webfinance; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
//
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php

include_once("../inc/main.php");
$roles = 'admin,manager';
include("../top.php");
include("nav.php");

?>

<table style="border: solid 1px black;" width="100%" border="0" cellspacing="0" cellpadding="5">
<tr class="row_header">
  <td><?=_('Hour') ?></td>
  <td><?= _('Events') ?></Td>
  <td><?= _('Who') ?></td>
</tr>
<?php
$result = mysql_query("SELECT id_userlog,log,date,id_user,date_format(date,'%d/%m/%Y %k:%i') as nice_date FROM webfinance_userlog ORDER BY date DESC");
$count=1;
while ($log = mysql_fetch_object($result)) {
  $class = ($count%2)==0?"odd":"even";
  $result2 = mysql_query("SELECT login FROM webfinance_users WHERE id_user=".$log->id_user);
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
$Revision = '$Revision: 531 $';
include("../bottom.php");
?>
