<?php
/*
 Copyright (C) 2004-2012 NBI SARL, ISVTEC SARL

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

include("../inc/main.php");

$roles = 'manager,employee';
include("../top.php");
include("nav.php");

?>

<h1>Todo</h1>

<br />

<table border="1">

<?
$q = "SELECT s.id_suivi, s.message, ts.name as type_suivi, c.nom, s.id_objet,
             c.id_client, UNIX_TIMESTAMP(s.date_added) as ts_date_added,
             u.first_name, u.last_name
      FROM webfinance_suivi s
      JOIN webfinance_type_suivi ts ON ts.id_type_suivi = s.type_suivi
      JOIN webfinance_clients c ON c.id_client = s.id_objet
      JOIN webfinance_users u ON s.added_by = u.id_user
      WHERE s.done = 0
      ORDER BY s.date_added DESC";

$result = mysql_query($q)
  or die($q." ".mysql_error());

$count = 1;
while ($log = mysql_fetch_object($result)) {
  $class = ($count%2)?"even":"odd";
  $date = strftime("%e %b %y", $log->ts_date_added);
  $date = preg_replace("/([^0-9])0/", '\\1', $date); // year >= 2000 this app is not expected to still exist in y3K :)
  $txt_msg = nl2br($log->message);

  print <<<EOF
<tr class="$class" valign="top">
  <td nowrap align="center"><b>$date</b></td>
  <td><a href="fiche_prospect.php?id=$log->id_client">$log->nom</a></td>
  <td>$log->first_name&nbsp;$log->last_name</td>
  <td>$txt_msg</td>
  <td><a href="update_suivi.php?id=$log->id_suivi&action=done&company_id=$log->id_objet"><font color="red">todo</font></a> </td>
  <td nowrap class="type_suivi_$log->id_type_suivi">$log->type_suivi</td>
</tr>
EOF;

}
mysql_free_result($result);

?>
</table>

<?
include("../bottom.php");

?>
